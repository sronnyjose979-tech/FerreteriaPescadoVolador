<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{

    public function __construct(protected ProductService $productService)// aqui se esta inyectando la clase ProductsService para poder usarla en los funciones de este controlador
    {
        $this->productService = $productService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $products = Product::active()
            ->when($request->has('low_stock'), fn($q) => $q->lowStock())
            ->with(['category', 'brand', 'unit'])
            ->paginate(10);

        return $products;
    }


    public function inventorySummary()
    {
        $report = Product::select(
            'categories.Category_name as category_name',
            DB::raw('COUNT(products.id) as total_products'),
            DB::raw('SUM(products.stock_quantity) as total_stock'),
            DB::raw('AVG(products.price) as average_price')
        )
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->groupBy('categories.id', 'categories.Category_name')
            ->get();

        return $report;
    }


    /**
     * Show the form for creating a new resource.
     */
    public function store(StoreProductRequest $request) //menos de 10 lineas de codigo, se esta usando el request para validar los datos
    {
        $validated = $request->validated();

        $validated = $request->validated();
        $product = $this->productService->createProduct($validated);

        return $product;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function show(string $id)
    {
        $product = Product::with(['category', 'brand', 'unit'])->findOrFail($id);

        return $product;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'category_id' => 'exists:categories,id',
            'brand_id' => 'exists:brands,id',
            'unit_id' => 'exists:units,id',
            'name' => 'string|max:200',
            'price' => 'numeric|min:0',
            'sku' => 'string|unique:products,sku,' . $id,
            'stock_quantity' => 'integer|min:0',
            'tax_rate' => 'nullable|numeric',
            'minimum_stock' => 'nullable|integer',
            'maximum_stock' => 'nullable|integer',
            'weight' => 'nullable|numeric',
            'image_url' => 'nullable|url',
            'is_active' => 'boolean'
        ]);

        $product->update($validated);

        return $product;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $product = Product::findOrFail($id);
        $product->delete();

        $success = true;

        return $success;
    }
}
