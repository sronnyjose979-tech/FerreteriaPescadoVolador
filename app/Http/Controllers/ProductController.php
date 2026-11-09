<?php

namespace App\Http\Controllers;

use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Attributes\Controllers\Authorize;
use Illuminate\Support\Facades\Validator;
use Picqer\Barcode\BarcodeGeneratorSVG;
use Picqer\Barcode\Exceptions\BarcodeException;

class ProductController extends Controller
{
    public function __construct(protected ProductService $product)
    {
        $this->product = $product;
    }

    #[Authorize('viewAny', Product::class)]
    public function index(Request $request)
    {
        $product = $this->product->listPaginated($request->all());

        return ProductResource::collection($product);
    }

    #[Authorize('viewAny', Product::class)]
    public function inventorySummary(): JsonResponse
    {
        return response()->json(['data' => $this->product->inventorySummary()]);
    }

    #[Authorize('viewAny', Product::class)]
    public function byBarcode(Request $request): ProductResource
    {
        parse_str((string) $request->server('QUERY_STRING'), $query);

        $validated = Validator::make($query, [
            'barcode' => ['required', 'string', 'max:100'],
        ])->validate();

        return new ProductResource($this->product->getByBarcode($validated['barcode']));
    }

    #[Authorize('view', 'product')]
    public function barcode(Product $product, BarcodeGeneratorSVG $generator): JsonResponse
    {
        if ($product->barcode === null || $product->barcode === '') {
            return response()->json(['message' => 'Este producto no tiene código de barras asignado.'], 422);
        }

        if (! preg_match('/\A[\x20-\x7E]+\z/', $product->barcode)) {
            return response()->json(['message' => 'El código existente no se puede representar en Code 128.'], 422);
        }

        try {
            $svg = $generator->getBarcode($product->barcode, $generator::TYPE_CODE_128, 2, 80);
        } catch (BarcodeException) {
            return response()->json(['message' => 'El código existente no se puede representar en Code 128.'], 422);
        }

        return response()->json(['data' => [
            'id' => $product->id,
            'nombre' => $product->name,
            'codigo_barras' => $product->barcode,
            'svg' => $svg,
        ]])->header('Cache-Control', 'private, no-store');
    }

    #[Authorize('create', Product::class)] // PERMISO PARA CREAR
    public function store(StoreProductRequest $request): JsonResponse
    {
        $product = $this->product->crear($request->validated());

        return (new ProductResource($product))
            ->response()
            ->setStatusCode(201)
            ->header('Location', route('products.show', $product));
    }

    #[Authorize('view', 'product')] // PERMISO PARA VER
    public function show(Product $product)
    {
        return new ProductResource($product);
    }

    #[Authorize('update', 'product')] // PERMISO PARA ACTUALIZAR
    public function update(UpdateProductRequest $request, Product $product)
    {
        $product = $this->product->actualizar($product, $request->validated());

        return new ProductResource($product);
    }

    #[Authorize('delete', 'product')] // PERMISO PARA BORRAR, VIENE DEL POLICY
    public function destroy(Product $product)
    {
        $this->product->eliminar($product);

        return response()->json(null, 204);
    }
}
