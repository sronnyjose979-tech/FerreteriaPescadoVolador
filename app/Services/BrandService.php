<?php

namespace App\Services;

use App\Models\Brand;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class BrandService
{
    public function crear(array $validated): Brand
    {
        return Brand::create($validated);
    }

    public function actualizar(Brand $brand, array $validated): Brand
    {
        $brand->update($validated);

        return $brand;
    }

    public function eliminar(Brand $brand): void
    {
        DB::transaction(function () use ($brand) {
            $brand->delete();
        });
    }

    public function listPaginated(array $filters): LengthAwarePaginator
    {
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
