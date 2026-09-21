<?php

namespace App\Services;

use App\Models\SaleItem;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class SaleItemService
{
    public function crear(array $saleItem): SaleItem
    {
        return SaleItem::create($saleItem);
    }

    public function actualizar(SaleItem $saleItem, array $validated): SaleItem
    {
        $saleItem->update($validated);

        return $saleItem;
    }

    public function eliminar(SaleItem $saleItem): void
    {
        $saleItem->delete();
    }

    public function getById(int $id): SaleItem
    {
        return SaleItem::findOrFail($id);
    }

    public function listPaginated(array $filters): LengthAwarePaginator
    {
        $perPage = (int) ($filters['per_page'] ?? 10);
        $perPage = max(1, min($perPage, 50));

        $sort = $filters['sort'] ?? 'id';
        $direction = $filters['direction'] ?? 'asc';

        $allowedSorts = [
            'id',
            'sale_id',
            'product_id',
            'quantity',
            'unit_price',
            'subtotal',
            'created_at'
        ];

        if (! in_array($sort, $allowedSorts, true)) {
            $sort = 'id';
        }

        if (! in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'asc';
        }

        $query = SaleItem::query();

        if (! empty($filters['q'])) {
            $q = $filters['q'];

            $query->where(function ($w) use ($q) {
                $w->where('id', 'like', "%{$q}%")
                    ->orWhere('sale_id', 'like', "%{$q}%")
                    ->orWhere('product_id', 'like', "%{$q}%")
                    ->orWhere('quantity', 'like', "%{$q}%")
                    ->orWhere('unit_price', 'like', "%{$q}%")
                    ->orWhere('subtotal', 'like', "%{$q}%");
            });
        }

        return $query
            ->orderBy($sort, $direction)
            ->orderBy('id', 'asc')
            ->paginate($perPage)
            ->withQueryString();
    }
}
