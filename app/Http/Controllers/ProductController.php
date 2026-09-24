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
    public function __construct(protected ProductService $productService) {}
    
    #[Authorize('viewAny', Product::class)]
    public function index(Request $request)
    {
        $product = $this->productService->listPaginated($request->all());
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

    public function store(StoreProductRequest $request)
    {
        $product = $this->productService->createProduct($request->validated());

        return response()->json($product, 201)->header('Location', url("/api/products/{$product->id}"));
    }
    #[Authorize('view', 'product')]
    public function show(Product $product)
    {
        return $product->load(['Category', 'Brand', 'Unit']); //->findOrFail($product);
    }

    public function update(UpdateProductRequest $request, string $id)
    {
        $product = Product::findOrFail($id);

        return $this->productService->updateProduct($product, $request->validated());
    }

    public function destroy(string $id)
    {
        $product = Product::findOrFail($id);
        $this->productService->deleteProduct($product);

        return response()->json(null, 204);
    }
}
