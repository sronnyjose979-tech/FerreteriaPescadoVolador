<?php

namespace App\Services; // esto es para indicar que esta clase pertenece a la carpeta Services

use App\Models\Brand; // esto es para indicar que esta clase pertenece a la carpeta Models

class BrandService // esta clase se encarga de manejar la logica de negocio de las marcas
{
    public function createBrand(array $validated)// aqui se va crear una marca, se recibe un array de datos validados
    {
        $brand = Brand::create($validated);

        return $brand;
    }
}
