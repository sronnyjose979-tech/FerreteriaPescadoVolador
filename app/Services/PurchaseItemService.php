<?php

namespace App\Services;

use App\Exceptions\BusinessException;
use App\Models\Product;
use App\Models\PurchaseItem;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class PurchaseItemService
{
    public function crear(array $validated): PurchaseItem
    {
        $expected = round($validated['quantity'] * $validated['unit_cost'], 2);
        $given = round((float) $validated['subtotal'], 2);

        if (abs($expected - $given) > 0.01) {
            throw new BusinessException('El subtotal debe ser igual a cantidad por costo unitario.', 422);
        }

        return DB::transaction(function () use ($validated) {
            $item = PurchaseItem::create($validated);
            $product = Product::lockForUpdate()->find($validated['id_product']);
            if ($product) {
                $product->increment('stock_quantity', $validated['quantity']);
            }

            return $item;
        });
    }

    public function actualizar(PurchaseItem $item, array $validated): PurchaseItem
    {
        if (isset($validated['quantity'], $validated['unit_cost'], $validated['subtotal'])) {
            $expected = round($validated['quantity'] * $validated['unit_cost'], 2);
            $given = round((float) $validated['subtotal'], 2);
            if (abs($expected - $given) > 0.01) {
                throw new BusinessException('El subtotal debe ser igual a cantidad por costo unitario.', 422);
            }
        }

        return DB::transaction(function () use ($item, $validated) {
            $oldQuantity = $item->quantity;
            $oldProductId = $item->id_product;
            $item->update($validated);

            if ($oldProductId !== $item->id_product || $oldQuantity !== $item->quantity) {
                $oldProduct = Product::lockForUpdate()->find($oldProductId);
                if ($oldProduct) {
                    $oldProduct->decrement('stock_quantity', $oldQuantity);
                }
                $newProduct = Product::lockForUpdate()->find($item->id_product);
                if ($newProduct) {
                    $newProduct->increment('stock_quantity', $item->quantity);
                }
            }

            return $item;
        });
    }

    public function eliminar(PurchaseItem $item): void
    {
        DB::transaction(function () use ($item) {
            $product = Product::lockForUpdate()->find($item->id_product);
            if ($product) {
                $product->decrement('stock_quantity', $item->quantity);
            }
            $item->delete();
        });
    }

    public function listPaginated(array $filters): LengthAwarePaginator
    {
        $perPage = (int) ($filters['per_page'] ?? 10);
        $perPage = max(1, min($perPage, 50));
        $sort = $filters['sort'] ?? 'id';
        $direction = $filters['direction'] ?? 'asc';
        $allowedSorts = ['quantity', 'unit_cost', 'subtotal', 'id', 'created_at'];
        if (! in_array($sort, $allowedSorts, true)) {
            $sort = 'id';
        }
        if (! in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'asc';
        }

        $query = PurchaseItem::query()->with(['purchase', 'product']);

        if (! empty($filters['id_Purchase'])) {
            $query->where('id_Purchase', $filters['id_Purchase']);
        }

        if (! empty($filters['id_product'])) {
            $query->where('id_product', $filters['id_product']);
        }

        return $query->orderBy($sort, $direction)->orderBy('id', 'asc')->paginate($perPage)->withQueryString();
    }
}
