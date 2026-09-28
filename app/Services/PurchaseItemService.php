<?php

namespace App\Services;

use App\Exceptions\BusinessException;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class PurchaseItemService
{
    public function __construct(protected InventoryMovementService $inventory) {}

    public function crear(array $validated): PurchaseItem
    {
        // Verifica que el usuario tenga permiso para crear detalles de compra
        Gate::authorize('create', PurchaseItem::class);

        // El subtotal lo calcula el servicio, no lo recibe del cliente
        $validated['subtotal'] = $this->calcularSubtotal($validated);

        return DB::transaction(function () use ($validated) {
            $item = PurchaseItem::create($validated);

            $product = Product::lockForUpdate()->find($validated['product_id']);

            if ($product) {
                $this->inventory->registrarEntrada($product, $item, $item->quantity);
            }

            return $item;
        });
    }

    public function actualizar(PurchaseItem $item, array $validated): PurchaseItem
    {
        // Verifica que el usuario tenga permiso para actualizar este detalle de compra
        Gate::authorize('update', $item);

        $validated['subtotal'] = $this->calcularSubtotal(
            array_merge($item->only(['quantity', 'unit_cost']), $validated)
        );

        return DB::transaction(function () use ($item, $validated) {
            $oldQuantity = $item->quantity;
            $oldProductId = $item->product_id;

            $item->update($validated);

            if ($item->wasChanged(['product_id', 'quantity'])) {
                $oldProduct = Product::lockForUpdate()->find($oldProductId);

                if ($oldProduct) {
                    $this->inventory->registrarSalida($oldProduct, $item, $oldQuantity);
                }

                $newProduct = Product::lockForUpdate()->find($item->product_id);

                if ($newProduct) {
                    $this->inventory->registrarEntrada($newProduct, $item, $item->quantity);
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
                $this->inventory->registrarSalida($product, $item, $item->quantity);
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

    public function listByPurchase(Purchase $purchase, array $filters): LengthAwarePaginator
    {
        Gate::authorize('view', $purchase);

        $perPage = (int) ($filters['per_page'] ?? 10);
        $perPage = max(1, min($perPage, 50));

        return $purchase->purchaseItems()
            ->with('product')
            ->orderBy('id', 'asc')
            ->paginate($perPage)
            ->withQueryString();
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

    private function calcularSubtotal(array $datos): float
    {
        $expected = round($datos['quantity'] * $datos['unit_cost'], 2);

        if (isset($datos['subtotal'])) {
            $given = round((float) $datos['subtotal'], 2);

            if (abs(round($expected * 100) - round($given * 100)) > 1) {
                throw new BusinessException('El subtotal debe ser igual a cantidad por costo unitario.');
            }
        }

        return $expected;
    }
}
