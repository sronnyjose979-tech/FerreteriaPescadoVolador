<?php

use App\Exceptions\BusinessException;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Supplier;
use App\Models\User;
use App\Services\InventoryMovementService;
use App\Services\PurchaseItemService;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Gate;

function compraAbierta(): Purchase
{
    return Purchase::factory()->create([
        'user_id' => User::factory()->create()->id,
        'id_supplier' => Supplier::factory()->create()->id_supplier,
    ]);
}

test('agrega el detalle, calcula el subtotal y registra la entrada de inventario', function () {
    Gate::shouldReceive('authorize')->once()->with('create', PurchaseItem::class)->andReturn(Response::allow());
    $product = Product::factory()->create();
    $inventario = Mockery::mock(InventoryMovementService::class);
    $inventario->shouldReceive('registrarEntrada')->once()
        ->with(Mockery::on(fn (Product $p) => $p->is($product)), Mockery::type(PurchaseItem::class), 3);

    $item = (new PurchaseItemService($inventario))->crear([
        'id_purchase' => compraAbierta()->id_purchase,
        'product_id' => $product->id,
        'quantity' => 3,
        'unit_cost' => 2500,
    ]);

    expect($item->subtotal)->toBe('7500.00');
});

test('rechaza con 409 un subtotal distinto de cantidad por costo sin registrar movimientos', function () {
    Gate::shouldReceive('authorize')->once()->andReturn(Response::allow());
    $inventario = Mockery::mock(InventoryMovementService::class);
    $inventario->shouldNotReceive('registrarEntrada');

    expect(fn () => (new PurchaseItemService($inventario))->crear([
        'id_purchase' => compraAbierta()->id_purchase,
        'product_id' => Product::factory()->create()->id,
        'quantity' => 2,
        'unit_cost' => 100,
        'subtotal' => 999,
    ]))->toThrow(function (BusinessException $e) {
        expect($e->getMessage())->toBe('El subtotal debe ser igual a cantidad por costo unitario.');
        expect($e->statusCode)->toBe(409);
    });

    $this->assertDatabaseCount('purchase_items', 0);
});

test('acepta un subtotal que difiere en un centavo por redondeo', function () {
    Gate::shouldReceive('authorize')->once()->andReturn(Response::allow());
    $inventario = Mockery::mock(InventoryMovementService::class);
    $inventario->shouldReceive('registrarEntrada')->once();

    $item = (new PurchaseItemService($inventario))->crear([
        'id_purchase' => compraAbierta()->id_purchase,
        'product_id' => Product::factory()->create()->id,
        'quantity' => 3,
        'unit_cost' => 33.33,
        'subtotal' => 100,
    ]);

    expect($item->subtotal)->toBe('99.99');
});

test('al cambiar solo la cantidad recalcula el subtotal y ajusta el inventario', function () {
    $product = Product::factory()->create();
    $item = PurchaseItem::factory()->create([
        'id_purchase' => compraAbierta()->id_purchase,
        'product_id' => $product->id,
        'quantity' => 2,
        'unit_cost' => 1000,
        'subtotal' => 2000,
    ]);
    Gate::shouldReceive('authorize')->once()->with('update', $item)->andReturn(Response::allow());
    $inventario = Mockery::mock(InventoryMovementService::class);
    $inventario->shouldReceive('registrarSalida')->once()
        ->with(Mockery::on(fn (Product $p) => $p->is($product)), $item, 2)->ordered();
    $inventario->shouldReceive('registrarEntrada')->once()
        ->with(Mockery::on(fn (Product $p) => $p->is($product)), $item, 4)->ordered();

    $actualizado = (new PurchaseItemService($inventario))->actualizar($item, ['quantity' => 4]);

    expect($actualizado->fresh()->subtotal)->toBe('4000.00');
});
