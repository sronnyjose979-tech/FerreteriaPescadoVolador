<?php

namespace App\Services; // esto es para indicar que esta clase pertenece a la carpeta Services

use App\Models\Category; // esto es para indicar que esta clase pertenece a la carpeta Models

class CategoryService // esta clase se encarga de manejar la logica de negocio de las categorias
{
    public function createCategory(array $validated)// aqui se va crear una categoria, se recibe un array de datos validados
    {
        $category = Category::create($validated);

        return $category;
    }
}
