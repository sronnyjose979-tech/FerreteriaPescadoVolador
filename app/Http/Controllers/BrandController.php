<?php

namespace App\Http\Controllers;

use App\Http\Requests\Brand\StoreBrandRequest;
use App\Http\Requests\Brand\UpdateBrandRequest;
use App\Http\Resources\BrandResource;
use App\Models\Brand;
use App\Services\BrandService;
use Illuminate\Http\Request;
use Illuminate\Routing\Attributes\Controllers\Authorize;

class BrandController extends Controller
{
    public function __construct(protected BrandService $brand)
    {
        $this->brand = $brand;
    }

    #[Authorize('viewAny', brand::class)]
    public function index(Request $request)
    {
        $brand = $this->brand->listPaginated($request->all());
        return BrandResource::collection($brand);
    }

    #[Authorize('store', brand::class)]
    public function store(StoreBrandRequest $request, Brand $brand)
    {
        $brand = $this->brand->actualizar($brand, $request->validated());

        return response()->json($brand, 201);
    }

    #[Authorize('show', brand::class)]
    public function show(Brand $brand)
    {
        return new BrandResource($brand);
    }

    #[Authorize('update', brand::class)]
    public function update(UpdateBrandRequest $request, Brand $brand)
    {
        $brand = $this->brand->actualizar($brand, $request->validated());
        return new BrandResource($brand);
    }

    #[Authorize('delete', brand::class)]
    public function destroy(Brand $brand)
    {
        $this->brand->eliminar($brand);
        return response()->json(null, 204);
    }
}
