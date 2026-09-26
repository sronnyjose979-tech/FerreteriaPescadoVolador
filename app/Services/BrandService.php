<?php

namespace App\Services;

use App\Models\Brand;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class BrandService
{
    public function crear(array $validated): Brand
    {
        // Verifica que el usuario tenga permiso para crear marcas
        Gate::authorize('create', Brand::class);

        return Brand::create($validated);
    }

    public function actualizar(Brand $brand, array $validated): Brand
    {
        // Verifica que el usuario tenga permiso para actualizar esta marca
        Gate::authorize('update', $brand);

        $brand->update($validated);

        return $brand;
    }

    public function eliminar(Brand $brand): void
    {
        // Verifica que el usuario tenga permiso para eliminar esta marca
        Gate::authorize('delete', $brand);

        DB::transaction(function () use ($brand) {
            $brand->delete();
        });
    }

    public function getById(int $id): Brand
    {
        // Busca la marca por su ID
        $brand = Brand::findOrFail($id);

        // Verifica que el usuario tenga permiso para ver esta marca
        Gate::authorize('view', $brand);

        return $brand;
    }

    public function listPaginated(array $filters): LengthAwarePaginator
    {
        // Verifica que el usuario tenga permiso para ver la lista de marcas
        Gate::authorize('viewAny', Brand::class);

        $perPage = (int) ($filters['per_page'] ?? 10);
        $perPage = max(1, min($perPage, 50));

        $sort = $filters['sort'] ?? 'id';
        $direction = $filters['direction'] ?? 'asc';

        $allowedSorts = [
            'brand_name',
            'id',
            'created_at'
        ];

        if (! in_array($sort, $allowedSorts, true)) {
            $sort = 'id';
        }

        if (! in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'asc';
        }

        $query = Brand::query();

        // Buscar por nombre de la marca
        if (! empty($filters['q'])) {
            $q = $filters['q'];

            $query->where('brand_name', 'like', "%{$q}%");
        }

        return $query
            ->orderBy($sort, $direction)
            ->paginate($perPage)
            ->withQueryString();
    }
}
