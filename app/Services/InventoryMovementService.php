<?php

namespace App\Services;

use App\Exceptions\BusinessException;
use App\Models\InventoryMovement;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class InventoryMovementService
{
    public function crear(array $validated): InventoryMovement
    {
        return InventoryMovement::create($validated);
    }

    public function actualizar(
        InventoryMovement $inventoryMovement,
        array $validated
    ): InventoryMovement {
        $inventoryMovement->update($validated);

        return $inventoryMovement;
    }

    public function eliminar(InventoryMovement $inventoryMovement): void
    {
        DB::transaction(function () use ($inventoryMovement) {
            $inventoryMovement->delete();
        });
    }

    public function listPaginated(array $filters): LengthAwarePaginator
    {
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