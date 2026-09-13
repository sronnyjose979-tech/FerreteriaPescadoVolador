<?php

namespace App\Services; // esto es para indicar que esta clase pertenece a la carpeta Services

use App\Models\Unit; // esto es para indicar que esta clase pertenece a la carpeta Models

class UnitService // esta clase se encarga de manejar la logica de negocio de las unidades
{
    public function createUnit(array $validated)// aqui se va crear una unidad, se recibe un array de datos validados
    {
        $unit = Unit::create($validated);

        return $unit;
    }
}
