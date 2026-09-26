<?php

namespace App\Services;

use App\Models\Sale;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class SaleService
{
    public function crear(array $sale): Sale
    {
        Gate::authorize('create', Sale::class);
        $sale['user_id'] = Auth::id(); //Con esto evitamos que un cajero le asigne la venta a otro usuario
        return Sale::create($sale);
    }

    public function actualizar(Sale $sale, array $validated): Sale
    {
        Gate::authorize('update', $sale);

        $sale->update($validated);

        return $sale;
    }

    public function eliminar(Sale $sale): void
    {
        Gate::authorize('delete', $sale);

        $sale->delete();
    }

    public function getById(int $id): Sale
    {
        $sale = Sale::findOrFail($id);

        Gate::authorize('view', $sale);

        return $sale;
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

        $user = Auth::user();

        if (!$user->roles->contains('name', 'admin')) {
            $query->where('user_id', $user->id);
        }

        if (! empty($filters['q'])) {
            $q = $filters['q'];

            $query->where(function ($w) use ($q) {
                $w->where('id', 'like', "%{$q}%")
                    ->orWhere('user_id', 'like', "%{$q}%")
                    ->orWhere('id_Customer', 'like', "%{$q}%")
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
