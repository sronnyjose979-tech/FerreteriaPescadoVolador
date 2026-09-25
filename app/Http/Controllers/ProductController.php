<?php

namespace App\Http\Controllers;

use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\Request;
use Illuminate\Routing\Attributes\Controllers\Authorize;
use Illuminate\Support\Facades\DB;

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

    public function inventorySummary()
    {
        $report = Product::select(
            'categories.Category_name as category_name',
            DB::raw('COUNT(products.id) as total_products'),
            DB::raw('SUM(products.stock_quantity) as total_stock'),
            DB::raw('AVG(products.price) as average_price')
        )->join('categories', 'products.category_id', '=', 'categories.id')->groupBy('categories.id', 'categories.Category_name')->get();

        return $report;
    }

    #[Authorize('create', Product::class)] //PERMISO PARA CREAR
    public function store(StoreProductRequest $request)
    {
        $product = $this->product->crear($request->validated());
        return response()->json($product, 201);
    }

    #[Authorize('view', Product::class)] //PERMISO PARA VER
    public function show(Product $product)
    {
        return new ProductResource($product);
    }

    #[Authorize('update', Product::class)] //PERMISO PARA ACTUALIZAR
    public function update(UpdateProductRequest $request, Product $product)
    {
        $product = $this->product->actualizar($product, $request->validated());
        return new ProductResource($product);
    }
    #[Authorize('delete', Product::class)] //PERMISO PARA BORRAR, VIENE DEL POLICY
    public function destroy(Product $product)
    {
        $this->product->eliminar($product);
        return response()->json(null, 204);
    }
}
