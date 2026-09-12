<?php
namespace App\Services;
use App\Models\Brand;

class BrandService
{
    public function createBrand(array $validated)
    {
        $brand = Brand::create($validated);
        return $brand;
    }
}