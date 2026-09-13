<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBrandRequest;
use App\Models\Brand;
use App\Services\BrandService;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    public function __construct(protected BrandService $brandsService)// aqui se esta inyectando la clase BrandService para poder usarla en los funciones de este controlador
    {
        $this->brandsService = $brandsService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $brands = Brand::paginate(10);

        return $brands;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBrandRequest $request) // menos de 10 lineas de codigo, se esta usando el request para validar los datos
    {
        $validated = $request->validated();

        $validated = $request->validated();
        $brand = $this->brandsService->createBrand($validated);

        return $brand;
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $brand = Brand::findOrFail($id);

        return $brand;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $brand = Brand::findOrFail($id);

        $validated = $request->validate([
            'brand_name' => 'string|max:200',
        ]);

        $brand->update($validated);

        return $brand;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $brand = Brand::findOrFail($id);
        $brand->delete();

        $success = true;

        return $success;
    }
}
