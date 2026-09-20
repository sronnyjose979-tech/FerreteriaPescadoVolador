<?php

namespace App\Services;

use App\Models\Brand;
use Illuminate\Pagination\LengthAwarePaginator;

class BrandService
{
    public function createBrand(array $validated)
    {
        return Brand::create($validated);
    }

    public function listPaginated(array $filters): LengthAwarePaginator
    {
        $perPage = (int) ($filters['per_page'] ?? 10);
        $perPage = max(1, min($perPage, 50));
        $sort = $filters['sort'] ?? 'id';
        $direction = $filters['direction'] ?? 'asc';
        
        $allowedSorts = ['brand_name', 'id', 'created_at'];
        if (! in_array($sort, $allowedSorts, true)) {
            $sort = 'id';
        }

        $query = Brand::query();

        // Buscar por nombre de la marca
        if (! empty($filters['q'])) {
            $q = $filters['q'];
            $query->where('brand_name', 'like', "%{$q}%");
        }

        return $query->orderBy($sort, $direction)
                     ->paginate($perPage)
                     ->withQueryString();
    }
}