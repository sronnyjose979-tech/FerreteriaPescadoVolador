<?php

namespace App\Services;

use App\Exceptions\BusinessException;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class PurchaseItemService
{
    public function __construct(protected InventoryMovementService $inventory) {}

    /** @param array{id_purchase: string, product_id: int|string, quantity: int|string, unit_cost: int|float|string, subtotal?: int|float|string} $validated */
    public function crear(array $validated): PurchaseItem
    {
        // Verifica que el usuario tenga permiso para crear detalles de compra
        Gate::authorize('create', PurchaseItem::class);

        // El subtotal lo calcula el servicio, no lo recibe del cliente
        $validated['subtotal'] = $this->calcularSubtotal($validated);

        return DB::transaction(function () use ($validated) {
            $purchase = Purchase::lockForUpdate()->findOrFail($validated['id_purchase']);

            if ($purchase->usesReceiptFlow() && $purchase->purchase_status !== 'pendiente') {
                throw new BusinessException('No se pueden agregar detalles a una compra recibida.');
            }

            $item = PurchaseItem::create($validated);

            if ($purchase->purchase_status !== 'pendiente') {
                $product = Product::lockForUpdate()->find($validated['product_id']);

                if ($product) {
                    $this->inventory->registrarEntrada($product, $item, $item->quantity);
                    $item->received_at = now();
                    $item->save();
                }
            }

            return $item;
        });
    }

    public function actualizar(PurchaseItem $item, array $validated): PurchaseItem
    {
        // Verifica que el usuario tenga permiso para actualizar este detalle de compra
        Gate::authorize('update', $item);

        return DB::transaction(function () use ($item, $validated) {
            $purchase = Purchase::lockForUpdate()->findOrFail($item->id_purchase);
            $item = PurchaseItem::where('id_purchase', $purchase->id_purchase)->lockForUpdate()->findOrFail($item->id);
            $validated['subtotal'] = $this->calcularSubtotal(
                array_merge($item->only(['quantity', 'unit_cost']), $validated)
            );

            if (isset($validated['id_purchase']) && $validated['id_purchase'] !== $item->id_purchase) {
                throw new BusinessException('No se puede cambiar la compra de un detalle.');
            }

            if ($purchase->usesReceiptFlow() || ($purchase->purchase_status === 'pendiente' && ! $item->inventoryMovement()->exists())) {
                if ($item->received_at !== null) {
                    throw new BusinessException('No se puede modificar un detalle recibido.');
                }

                $item->update($validated);

                return $item;
            }

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
            $purchase = Purchase::lockForUpdate()->findOrFail($item->id_purchase);
            $item = PurchaseItem::where('id_purchase', $purchase->id_purchase)->lockForUpdate()->findOrFail($item->id);

            if ($purchase->usesReceiptFlow() || ($purchase->purchase_status === 'pendiente' && ! $item->inventoryMovement()->exists())) {
                if ($item->received_at !== null) {
                    throw new BusinessException('No se puede eliminar un detalle recibido.');
                }

                $item->delete();

                return;
            }

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

    /** @return LengthAwarePaginator<int, PurchaseItem> */
    public function pendingReceipts(string $barcode, int $perPage = 10): LengthAwarePaginator
    {
        Gate::authorize('receive', PurchaseItem::class);
        $start = Purchase::receivingStartsAt();

        if ($start === null) {
            throw new BusinessException('La recepción no está habilitada: falta la fecha de inicio.');
        }

        $product = app(ProductService::class)->getByBarcode($barcode);

        return PurchaseItem::query()->with('product')
            ->where('product_id', $product->id)
            ->whereNull('received_at')
            ->whereHas('purchase', fn ($query) => $query
                ->where('purchase_status', 'pendiente')->where('created_at', '>', $start))
            ->whereDoesntHave('inventoryMovement')
            ->orderBy('id')->paginate(max(1, min($perPage, 50)))->withQueryString();
    }

    public function receive(PurchaseItem $item, string $barcode, int $quantity): PurchaseItem
    {
        Gate::authorize('receive', PurchaseItem::class);

        return DB::transaction(function () use ($item, $barcode, $quantity): PurchaseItem {
            $purchase = Purchase::lockForUpdate()->findOrFail($item->id_purchase);
            $item = PurchaseItem::where('id_purchase', $purchase->id_purchase)->lockForUpdate()->findOrFail($item->id);

            if (! $purchase->usesReceiptFlow()) {
                throw new BusinessException('Esta compra está excluida de recepción hasta su conciliación.');
            }

            if ($item->received_at !== null || $item->inventoryMovement()->exists()) {
                throw new BusinessException('Este detalle ya fue recibido o tiene movimientos registrados.');
            }

            if ($purchase->purchase_status !== 'pendiente') {
                throw new BusinessException('La compra no está pendiente de recepción.');
            }

            $product = Product::lockForUpdate()->findOrFail($item->product_id);
            Gate::authorize('view', $product);

            if (! hash_equals((string) $product->barcode, $barcode)) {
                throw ValidationException::withMessages(['barcode' => 'El código no corresponde al producto del detalle seleccionado.']);
            }

            if ($quantity !== (int) $item->quantity) {
                throw ValidationException::withMessages(['quantity' => 'La cantidad recibida debe coincidir exactamente con la cantidad del detalle.']);
            }

            $this->inventory->registrarEntrada($product, $item, $quantity);
            $item->received_at = now();
            $item->save();

            if (! $purchase->purchaseItems()->whereNull('received_at')->exists()) {
                $purchase->update(['purchase_status' => 'recibida']);
            }

            return $item->load('product');
        });
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
