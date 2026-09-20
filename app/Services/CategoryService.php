<?php

namespace App\Services; // esto es para indicar que esta clase pertenece a la carpeta Services

use App\Models\Category; // esto es para indicar que esta clase pertenece a la carpeta Models
use Illuminate\Pagination\LengthAwarePaginator;

class CategoryService // esta clase se encarga de manejar la logica de negocio de las categorias
{
    public function createCategory(array $validated)// aqui se va crear una categoria, se recibe un array de datos validados
    {
        $category = Category::create($validated);

        return $category;
    }
   public function listPaginated(array $filters): LengthAwarePaginator
    {
        $perPage = (int) ($filters['per_page'] ?? 10);
        $perPage = max(1, min($perPage, 50));

        $sort = $filters['sort'] ?? 'id';
        $direction = $filters['direction'] ?? 'asc';

        // Columnas permitidas para ordenar
        $allowedSorts = ['category_name', 'id', 'created_at'];
        if (! in_array($sort, $allowedSorts, true)) {
            $sort = 'id';
        }

        if (! in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'asc';
        }

        $query = Category::query();

        // Buscador por nombre de categoría o descripción
        if (! empty($filters['q'])) {
            $q = $filters['q'];
            $query->where(function ($w) use ($q) {
                $w->where('category_name', 'like', "%{$q}%")
                  ->orWhere('description', 'like', "%{$q}%");
            });
        }

        return $query->orderBy($sort, $direction)
                     ->orderBy('id', 'asc')
                     ->paginate($perPage)
                     ->withQueryString();
    }
}
