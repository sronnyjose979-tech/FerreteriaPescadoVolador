<?php

namespace App\Http\Controllers;

use App\Http\Requests\BrandsRequest;
use App\Models\Brand;
use App\Services\BrandService;
use Illuminate\Http\Request;

class BrandsController extends Controller
{
    public function __construct(protected BrandService $brandsService) {}
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $data = Brand::all();
        return $data;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(BrandsRequest $request)
    {
        $validated = $request->validated();

        $validated = $request->validated();
        $product = $this->brandsService->createBrand($validated);

        return $product;
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
