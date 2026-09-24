<?php

namespace App\Services;

use App\Models\Sale;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class SaleService
{
    public function crear(array $sale): Sale
    {
        return Sale::create($sale);
    }

    public function actualizar(Sale $sale, array $validated): Sale
    {
        $sale->update($validated);

        return $sale;
    }

    public function eliminar(Sale $sale): void
    {
        $sale->delete();
    }

    public function getById(int $id): Sale
    {
        return Sale::findOrFail($id);
    }

    public function listPaginated(array $filters): LengthAwarePaginator
    {
        $perPage = (int) ($filters['per_page'] ?? 10);
        $perPage = max(1, min($perPage, 50));

        $sort = $filters['sort'] ?? 'id';
        $direction = $filters['direction'] ?? 'asc';

        $allowedSorts = [
            'id',
            'user_id',
            'id_Customer',
            'sale_date',
            'total',
            'tax_amount',
            'discount',
            'status',
            'created_at'
        ];

        if (! in_array($sort, $allowedSorts, true)) {
            $sort = 'id';
        }

        if (! in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'asc';
        }

        $query = Sale::query();

        if (! empty($filters['q'])) {
            $q = $filters['q'];

            $query->where(function ($w) use ($q) {
                $w->where('id', 'like', "%{$q}%")
                    ->orWhere('user_id', 'like', "%{$q}%")
                    ->orWhere('id_Customer', 'like', "%{$q}%")
                   // ->orWhere('order_id', 'like', "%{$q}%")
                    ->orWhere('status', 'like', "%{$q}%");
            });
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query
            ->orderBy($sort, $direction)
            ->orderBy('id', 'asc')
            ->paginate($perPage)
            ->withQueryString();
    }
}
