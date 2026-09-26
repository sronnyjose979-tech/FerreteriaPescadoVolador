<?php

namespace App\Services;

use App\Exceptions\BusinessException;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class PurchaseService
{
    public function crear(array $purchase): Purchase
    {
        // Verifica que el usuario tenga permiso para crear compras
        Gate::authorize('create', Purchase::class);

        return Purchase::create($purchase);
    }

    public function crearConDetalle(array $purchaseData, array $items): Purchase
    {
        // Verifica que el usuario tenga permiso para crear compras
        Gate::authorize('create', Purchase::class);

        if (empty($items)) {
            throw new BusinessException('No se puede confirmar una compra sin detalle.', 422);
        }

        return DB::transaction(function () use ($purchaseData, $items) {
            $calculatedTotal = 0;

            foreach ($items as $item) {
                if (! isset($item['quantity'], $item['unit_cost'])) {
                    throw new BusinessException('Cada detalle debe incluir cantidad y costo unitario.', 422);
                }

                $expected = round($item['quantity'] * $item['unit_cost'], 2);

                $subtotal = isset($item['subtotal'])
                    ? round((float) $item['subtotal'], 2)
                    : $expected;

                if (abs($expected - $subtotal) > 0.01) {
                    throw new BusinessException('El subtotal no coincide con cantidad por costo unitario.', 422);
                }

                $calculatedTotal += $subtotal;
            }

            $discount = 0;

            if ($calculatedTotal >= 50000) {
                $discount = round($calculatedTotal * 0.10, 2);
            } elseif ($calculatedTotal >= 10000) {
                $discount = round($calculatedTotal * 0.05, 2);
            }

            $finalTotal = round($calculatedTotal - $discount, 2);

            if (
                isset($purchaseData['purchase_total'])
                && abs((float) $purchaseData['purchase_total'] - $finalTotal) > 0.01
            ) {
                throw new BusinessException(
                    'El total de la compra no coincide con la suma de detalles menos descuento.',
                    422
                );
            }

            $purchaseData['purchase_total'] = $finalTotal;

            $purchase = Purchase::create($purchaseData);

            foreach ($items as $item) {
                $subtotal = round($item['quantity'] * $item['unit_cost'], 2);

                PurchaseItem::create([
                    'id_purchase' => $purchase->id_purchase,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_cost' => $item['unit_cost'],
                    'subtotal' => $subtotal,
                ]);

                $product = Product::lockForUpdate()->find($item['product_id']);

                if ($product) {
                    $product->increment('stock_quantity', $item['quantity']);
                }
            }

            return $purchase->load('purchaseItems');
        });
    }

    public function actualizar(Purchase $purchase, array $validated): Purchase
    {
        // Verifica que el usuario tenga permiso para actualizar esta compra
        Gate::authorize('update', $purchase);

        $purchase->update($validated);

        return $purchase;
    }

    public function eliminar(Purchase $purchase): void
    {
        // Verifica que el usuario tenga permiso para eliminar esta compra
        Gate::authorize('delete', $purchase);

        if ($purchase->purchaseItems()->exists()) {
            throw new BusinessException(
                'No se puede eliminar la compra porque tiene detalles asociados.',
                409
            );
        }

        DB::transaction(function () use ($purchase) {
            $purchase->delete();
        });
    }

    public function getById(string $id): Purchase
    {
        // Busca la compra por su ID
        $purchase = Purchase::findOrFail($id);

        // Verifica que el usuario tenga permiso para ver esta compra
        Gate::authorize('view', $purchase);

        return $purchase;
    }

    public function listPaginated(array $filters): LengthAwarePaginator
    {
        // Verifica que el usuario tenga permiso para ver la lista de compras
        Gate::authorize('viewAny', Purchase::class);

        $perPage = (int) ($filters['per_page'] ?? 10);
        $perPage = max(1, min($perPage, 50));

        $sort = $filters['sort'] ?? 'id_purchase';
        $direction = $filters['direction'] ?? 'asc';

        $allowedSorts = [
            'id_purchase',
            'purchase_total',
            'purchase_status',
            'created_at',
        ];

        if (! in_array($sort, $allowedSorts, true)) {
            $sort = 'id_purchase';
        }

        if (! in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'asc';
        }

        $query = Purchase::query()->with(['supplier', 'purchaseItems']);

        if (! empty($filters['q'])) {
            $q = $filters['q'];

            $query->where(
                'id_purchase',
                'like',
                "%{$q}%"
            );
        }

        if (! empty($filters['id_supplier'])) {
            $query->where(
                'id_supplier',
                $filters['id_supplier']
            );
        }

        if (! empty($filters['purchase_status'])) {
            $query->where(
                'purchase_status',
                $filters['purchase_status']
            );
        }

        return $query
            ->orderBy($sort, $direction)
            ->orderBy('id_purchase', 'asc')
            ->paginate($perPage)
            ->withQueryString();
    }
}
