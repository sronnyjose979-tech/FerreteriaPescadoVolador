<?php

namespace App\Services;

use App\Models\InventoryMovement;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class InventoryMovementService
{
    public function crear(array $validated): InventoryMovement
    {
        // Verifica que el usuario tenga permiso para crear movimientos de inventario
        Gate::authorize('create', InventoryMovement::class);

        return InventoryMovement::create($validated);
    }

    public function actualizar(
        InventoryMovement $inventoryMovement,
        array $validated
    ): InventoryMovement {
        // Verifica que el usuario tenga permiso para actualizar este movimiento
        Gate::authorize('update', $inventoryMovement);

        $inventoryMovement->update($validated);

        return $inventoryMovement;
    }

    public function eliminar(InventoryMovement $inventoryMovement): void
    {
        // Verifica que el usuario tenga permiso para eliminar este movimiento
        Gate::authorize('delete', $inventoryMovement);

        DB::transaction(function () use ($inventoryMovement) {
            $inventoryMovement->delete();
        });
    }

    public function getById(int $id): InventoryMovement
    {
        // Busca el movimiento de inventario por su ID
        $inventoryMovement = InventoryMovement::findOrFail($id);

        // Verifica que el usuario tenga permiso para ver este movimiento
        Gate::authorize('view', $inventoryMovement);

        return $inventoryMovement;
    }

    public function listPaginated(array $filters): LengthAwarePaginator
    {
        // Verifica que el usuario tenga permiso para ver la lista de movimientos
        Gate::authorize('viewAny', InventoryMovement::class);

        $perPage = (int) ($filters['per_page'] ?? 10);
        $perPage = max(1, min($perPage, 50));

        $sort = $filters['sort'] ?? 'id';
        $direction = $filters['direction'] ?? 'asc';

        $allowedSorts = [
            'id',
            'product_id',
            'movementable_type',
            'movementable_id',
            'type',
            'quantity',
            'stock_after',
            'created_at'
        ];

        if (! in_array($sort, $allowedSorts, true)) {
            $sort = 'id';
        }

        if (! in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'asc';
        }

        $query = InventoryMovement::query()
            ->with('product');

        if (! empty($filters['product_id'])) {
            $query->where(
                'product_id',
                $filters['product_id']
            );
        }

        if (! empty($filters['movementable_type'])) {
            $query->where(
                'movementable_type',
                $filters['movementable_type']
            );
        }

        if (! empty($filters['movementable_id'])) {
            $query->where(
                'movementable_id',
                $filters['movementable_id']
            );
        }

        if (! empty($filters['type'])) {
            $query->where(
                'type',
                $filters['type']
            );
        }

        return $query
            ->orderBy($sort, $direction)
            ->orderBy('id', 'asc')
            ->paginate($perPage)
            ->withQueryString();
    }
}
