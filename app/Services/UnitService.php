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
    // Funcion de la lista paginada (recibe filtros), por ejemplo el parametro array de filtros
    public function listPaginated(array $filters): LengthAwarePaginator
    {
        // El ?? significa que si existe es valor usalo pero sino usar el 10
        $perPage = (int) ($filters['per_page'] ?? 10);
        // Cuantos registros mostrar por pagina, minimo 1, maximo 50
        $perPage = max(1, min($perPage, 50));
        // En sort le decimos que columna usar para ordenar, en este caso id
        $sort = $filters['sort'] ?? 'id';
        // Por defecto esta en asc, que es ascendente
        $direction = $filters['direction'] ?? 'asc';

        // Estas son las columnas que permitimos ordenar
        $allowedSorts = [
            'id',
            'unit_name',
            'created_at'
        ];

        // Aqui pregunta en in_array que si X valor esta en el arreglo
        if (! in_array($sort, $allowedSorts, true)) {
            $sort = 'id';
        }
        // Aqui solo validamos si se ponen en ascendente o descendente
        if (! in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'asc';
        }
        // Aqui se crea la consulta para la tabla Unit
        $query = Unit::query();

        // El q significa la busqueda que vamos a hacer
        // Ejemplo: /units?q=kg
        if (! empty($filters['q'])) {
            // Aqui guardamos lo que buscamos
            $q = $filters['q'];
            // Estas son las condiciones de la consulta
            $query->where(function ($w) use ($q) {
                // Busca el valor de q dentro de id o unit_name
                $w->where('id', 'like', "%{$q}%")
                    ->orWhere('unit_name', 'like', "%{$q}%");
            });
        }

        // Aqui se devuelven los resultados de la busqueda
        return $query
            // Esto es como en SQL ORDER BY
            ->orderBy($sort, $direction)
            // En caso de empate, ordena por id ascendente
            ->orderBy('id', 'asc')
            // Aqui se aplica la paginacion
            ->paginate($perPage)
            // Mantiene los filtros en los enlaces de paginacion
            ->withQueryString();
    }
}
