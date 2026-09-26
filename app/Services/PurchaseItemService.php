<?php

namespace App\Services;

use App\Exceptions\BusinessException;
use App\Models\Product;
use App\Models\PurchaseItem;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class PurchaseItemService
{
    public function crear(array $validated): PurchaseItem
    {
        // Verifica que el usuario tenga permiso para crear detalles de compra
        Gate::authorize('create', PurchaseItem::class);

        $expected = round($validated['quantity'] * $validated['unit_cost'], 2);
        $given = round((float) $validated['subtotal'], 2);

        if (abs($expected - $given) > 0.01) {
            throw new BusinessException('El subtotal debe ser igual a cantidad por costo unitario.', 422);
        }

        return DB::transaction(function () use ($validated) {
            $item = PurchaseItem::create($validated);

            $product = Product::lockForUpdate()->find($validated['product_id']);

            if ($product) {
                $product->increment('stock_quantity', $validated['quantity']);
            }

            return $item;
        });
    }

    public function actualizar(PurchaseItem $item, array $validated): PurchaseItem
    {
        // Verifica que el usuario tenga permiso para actualizar este detalle de compra
        Gate::authorize('update', $item);

        if (isset($validated['quantity'], $validated['unit_cost'], $validated['subtotal'])) {
            $expected = round($validated['quantity'] * $validated['unit_cost'], 2);
            $given = round((float) $validated['subtotal'], 2);

            if (abs($expected - $given) > 0.01) {
                throw new BusinessException('El subtotal debe ser igual a cantidad por costo unitario.', 422);
            }
        }

        return DB::transaction(function () use ($item, $validated) {
            $oldQuantity = $item->quantity;
            $oldProductId = $item->product_id;

            $item->update($validated);

            if ($oldProductId !== $item->product_id || $oldQuantity !== $item->quantity) {
                $oldProduct = Product::lockForUpdate()->find($oldProductId);

                if ($oldProduct) {
                    $oldProduct->decrement('stock_quantity', $oldQuantity);
                }

                $newProduct = Product::lockForUpdate()->find($item->product_id);

                if ($newProduct) {
                    $newProduct->increment('stock_quantity', $item->quantity);
                }
            }

            return $item;
        });
    }

    public function eliminar(PurchaseItem $item): void
    {
        // Verifica que el usuario tenga permiso para eliminar este detalle de compra
        Gate::authorize('delete', $item);

        DB::transaction(function () use ($item) {
            $product = Product::lockForUpdate()->find($item->product_id);

            if ($product) {
                $product->decrement('stock_quantity', $item->quantity);
            }

            $item->delete();
        });
    }

    public function getById(int $id): PurchaseItem
    {
        // Busca el detalle de compra por su ID
        $item = PurchaseItem::findOrFail($id);

        // Verifica que el usuario tenga permiso para ver este detalle de compra
        Gate::authorize('view', $item);

        return $item;
    }

    public function listPaginated(array $filters): LengthAwarePaginator
    {
        // Verifica que el usuario tenga permiso para ver la lista de detalles de compra
        Gate::authorize('viewAny', PurchaseItem::class);

        $perPage = (int) ($filters['per_page'] ?? 10);
        $perPage = max(1, min($perPage, 50));

        $sort = $filters['sort'] ?? 'id';
        $direction = $filters['direction'] ?? 'asc';

        $allowedSorts = [
            'quantity',
            'unit_cost',
            'subtotal',
            'id',
            'created_at',
        ];

        if (! in_array($sort, $allowedSorts, true)) {
            $sort = 'id';
        }

        if (! in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'asc';
        }

        $query = PurchaseItem::query()->with(['purchase', 'product']);

        if (! empty($filters['id_purchase'])) {
            $query->where('id_purchase', $filters['id_purchase']);
        }

        if (! empty($filters['product_id'])) {
            $query->where('product_id', $filters['product_id']);
        }

        return $query
            ->orderBy($sort, $direction)
            ->orderBy('id', 'asc')
            ->paginate($perPage)
            ->withQueryString();
    }
}
