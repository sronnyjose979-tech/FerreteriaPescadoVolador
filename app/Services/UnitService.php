<?php

namespace App\Services;

use App\Models\Unit;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Gate;

class UnitService
{
    public function crear(array $validated): Unit
    {
        Gate::authorize('create', Unit::class);

        return Unit::create($validated);
    }

    public function actualizar(Unit $unit, array $validated): Unit
    {
        Gate::authorize('update', $unit);

        $unit->update($validated);

        return $unit;
    }

    public function eliminar(Unit $unit): void
    {
        Gate::authorize('delete', $unit);

        $unit->delete();
    }

    public function getById(int $id): Unit
    {
        $unit = Unit::findOrFail($id);

        Gate::authorize('view', $unit);

        return $unit;
    }

    public function listPaginated(array $filters): LengthAwarePaginator
    {
        Gate::authorize('viewAny', Unit::class);

        $perPage = (int) ($filters['per_page'] ?? 10);
        $perPage = max(1, min($perPage, 50));

        $sort = $filters['sort'] ?? 'id';
        $direction = $filters['direction'] ?? 'asc';

        $allowedSorts = [
            'id',
            'unit_name',
            'created_at'
        ];

        if (!in_array($sort, $allowedSorts, true)) {
            $sort = 'id';
        }

        if (!in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'asc';
        }

        $query = Unit::query();

        if (!empty($filters['q'])) {
            $q = $filters['q'];

            $query->where(function ($w) use ($q) {
                $w->where('id', 'like', "%{$q}%")
                    ->orWhere('unit_name', 'like', "%{$q}%");
            });
        }

        return $query
            ->orderBy($sort, $direction)
            ->orderBy('id', 'asc')
            ->paginate($perPage)
            ->withQueryString();
    }
}
