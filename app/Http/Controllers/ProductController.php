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
