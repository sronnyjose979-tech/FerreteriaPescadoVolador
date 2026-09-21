<?php

namespace App\Services; // esto es para indicar que esta clase pertenece a la carpeta Services

use App\Models\Unit; // esto es para indicar que esta clase pertenece a la carpeta Models
use Illuminate\Pagination\LengthAwarePaginator;

class UnitService // esta clase se encarga de manejar la logica de negocio de las unidades
{
    public function createUnit(array $validated) // aqui se va crear una unidad, se recibe un array de datos validados
    {
        $unit = Unit::create($validated);

        return $unit;
    }
    public function listPaginated(array $filters): LengthAwarePaginator
    {
        $perPage = (int) ($filters['per_page'] ?? 10);
        $perPage = max(1, min($perPage, 50));

        $sort = $filters['sort'] ?? 'id';
        $direction = $filters['direction'] ?? 'asc';

        // Columnas permitidas para ordenar
        $allowedSorts = ['unit_name', 'abbreviation', 'id', 'created_at'];
        if (! in_array($sort, $allowedSorts, true)) {
            $sort = 'id';
        }

        if (! in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'asc';
        }

        $query = Unit::query();

        // Buscador por nombre o abreviatura
        if (! empty($filters['q'])) {
            $q = $filters['q'];
            $query->where(function ($w) use ($q) {
                $w->where('unit_name', 'like', "%{$q}%")
                    ->orWhere('abbreviation', 'like', "%{$q}%");
            });
        }

        return $query->orderBy($sort, $direction)
            ->orderBy('id', 'asc')
            ->paginate($perPage)
            ->withQueryString();
    }
}
