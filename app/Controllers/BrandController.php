<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBrandRequest;
use App\Http\Resources\BrandResource;
use App\Models\Brand;
use App\Services\BrandService;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    public function __construct(protected BrandService $brandsService)
    {
        $this->brandsService = $brandsService;
    }

    public function index(Request $request)
    {
         $brand = $this->brandsService->listPaginated($request->all());
         return BrandResource::collection($brand);
    }

    public function store(StoreBrandRequest $request)
    {
        $validated = $request->validated();
        $brand = $this->brandsService->createBrand($validated);

        return response()->json(new BrandResource($brand), 201);
    }

    public function show(Brand $brand)
    {
        return new BrandResource($brand);
    }

    public function update(Request $request, string $id)
    {
        $brand = Brand::findOrFail($id);

        $validated = $request->validate([
            'brand_name' => 'string|max:200',
        ]);

        $brand->update($validated);

        return new BrandResource($brand);
    }

    public function destroy(string $id)
    {
        $brand = Brand::findOrFail($id);
        $brand->delete();

        return response()->json(null, 204);
    }
}