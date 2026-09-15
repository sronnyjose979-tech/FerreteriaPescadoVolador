<?php

namespace App\Services;

use App\Exceptions\BusinessException;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class PurchaseService
{
    public function crear($purchase): Purchase
    {
        return Purchase::create($purchase);
    }

    public function crearConDetalle(array $purchaseData, array $items): Purchase
    {
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
                $subtotal = isset($item['subtotal']) ? round((float) $item['subtotal'], 2) : $expected;
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

            if (isset($purchaseData['Purchase_Total']) && abs((float) $purchaseData['Purchase_Total'] - $finalTotal) > 0.01) {
                throw new BusinessException('El total de la compra no coincide con la suma de detalles menos descuento.', 422);
            }

            $purchaseData['Purchase_Total'] = $finalTotal;

            $purchase = Purchase::create($purchaseData);

            foreach ($items as $item) {
                $subtotal = round($item['quantity'] * $item['unit_cost'], 2);
                PurchaseItem::create([
                    'id_Purchase' => $purchase->id_Purchase,
                    'id_product' => $item['id_product'],
                    'quantity' => $item['quantity'],
                    'unit_cost' => $item['unit_cost'],
                    'subtotal' => $subtotal,
                ]);

                $product = Product::lockForUpdate()->find($item['id_product']);
                if ($product) {
                    $product->increment('stock_quantity', $item['quantity']);
                }
            }

            return $purchase->load('purchaseItems');
        });
    }

    public function actualizar(Purchase $purchase, array $validated): Purchase
    {
        $purchase->update($validated);

        return $purchase;
    }

    public function eliminar(Purchase $purchase): void
    {
        if ($purchase->purchaseItems()->exists()) {
            throw new BusinessException('No se puede eliminar la compra porque tiene detalles asociados.', 409);
        }

        DB::transaction(function () use ($purchase) {
            $purchase->delete();
        });
    }

    public function listPaginated(array $filters): LengthAwarePaginator
    {
        $perPage = (int) ($filters['per_page'] ?? 10);
        $perPage = max(1, min($perPage, 50));
        $sort = $filters['sort'] ?? 'id_Purchase';
        $direction = $filters['direction'] ?? 'asc';
        $allowedSorts = ['id_Purchase', 'Purchase_Total', 'Purchase_status', 'created_at'];
        if (! in_array($sort, $allowedSorts, true)) {
            $sort = 'id_Purchase';
        }
        if (! in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'asc';
        }

        $query = Purchase::query()->with(['supplier', 'purchaseItems']);

        if (! empty($filters['q'])) {
            $q = $filters['q'];
            $query->where('id_Purchase', 'like', "%{$q}%");
        }

        if (! empty($filters['id_Supplier'])) {
            $query->where('id_Supplier', $filters['id_Supplier']);
        }

        if (! empty($filters['Purchase_status'])) {
            $query->where('Purchase_status', $filters['Purchase_status']);
        }

        return $query->orderBy($sort, $direction)->orderBy('id_Purchase', 'asc')->paginate($perPage)->withQueryString();
    }
}
