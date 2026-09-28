<?php

use App\Exceptions\BusinessException;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Supplier;
use App\Models\User;
use App\Services\InventoryMovementService;
use App\Services\PurchaseService;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Gate;

function datosDeCompra(): array
{
    return [
        'id_purchase' => 'PUR-500',
        'id_supplier' => Supplier::factory()->create()->id_supplier,
        'purchase_status' => 'pendiente',
    ];
}

test('confirma la compra, calcula el total con descuento y registra una entrada por detalle', function () {
    $this->actingAs($usuario = User::factory()->create());
    Gate::shouldReceive('authorize')->once()->with('create', Purchase::class)->andReturn(Response::allow());
    $martillo = Product::factory()->create();
    $taladro = Product::factory()->create();
    $inventario = Mockery::mock(InventoryMovementService::class);
    $inventario->shouldReceive('registrarEntrada')->once()
        ->with(Mockery::on(fn (Product $p) => $p->is($martillo)), Mockery::type(PurchaseItem::class), 2);
    $inventario->shouldReceive('registrarEntrada')->once()
        ->with(Mockery::on(fn (Product $p) => $p->is($taladro)), Mockery::type(PurchaseItem::class), 3);

    $purchase = (new PurchaseService($inventario))->crearConDetalle(datosDeCompra(), [
        ['product_id' => $martillo->id, 'quantity' => 2, 'unit_cost' => 3000],
        ['product_id' => $taladro->id, 'quantity' => 3, 'unit_cost' => 2000, 'subtotal' => 6000],
    ]);

    expect($purchase->purchase_total)->toBe('11400.00');
    expect($purchase->user_id)->toBe($usuario->id);
    expect($purchase->purchaseItems)->toHaveCount(2);
});

test('rechaza con 409 una compra sin detalle y no registra movimientos', function () {
    Gate::shouldReceive('authorize')->once()->andReturn(Response::allow());
    $inventario = Mockery::mock(InventoryMovementService::class);
    $inventario->shouldNotReceive('registrarEntrada');

    expect(fn () => (new PurchaseService($inventario))->crearConDetalle(datosDeCompra(), []))
        ->toThrow(function (BusinessException $e) {
            expect($e->getMessage())->toBe('No se puede confirmar una compra sin detalle.');
            expect($e->statusCode)->toBe(409);
        });

    $this->assertDatabaseCount('purchases', 0);
});

test('rechaza un detalle cuyo subtotal no coincide con cantidad por costo', function () {
    $this->actingAs(User::factory()->create());
    Gate::shouldReceive('authorize')->once()->andReturn(Response::allow());
    $inventario = Mockery::mock(InventoryMovementService::class);
    $inventario->shouldNotReceive('registrarEntrada');

    expect(fn () => (new PurchaseService($inventario))->crearConDetalle(datosDeCompra(), [
        ['product_id' => Product::factory()->create()->id, 'quantity' => 2, 'unit_cost' => 100, 'subtotal' => 999],
    ]))->toThrow(BusinessException::class, 'El subtotal no coincide con cantidad por costo unitario.');

    $this->assertDatabaseCount('purchases', 0);
});

test('acepta un subtotal y un total con un centavo de diferencia por redondeo', function () {
    $this->actingAs(User::factory()->create());
    Gate::shouldReceive('authorize')->once()->andReturn(Response::allow());
    $inventario = Mockery::mock(InventoryMovementService::class);
    $inventario->shouldReceive('registrarEntrada')->once();

    $purchase = (new PurchaseService($inventario))->crearConDetalle([...datosDeCompra(), 'purchase_total' => 100], [
        ['product_id' => Product::factory()->create()->id, 'quantity' => 3, 'unit_cost' => 33.33, 'subtotal' => 100],
    ]);

    expect($purchase->purchase_total)->toBe('99.99');
});

test('rechaza un total enviado que no descuenta el porcentaje correspondiente', function () {
    $this->actingAs(User::factory()->create());
    Gate::shouldReceive('authorize')->once()->andReturn(Response::allow());
    $inventario = Mockery::mock(InventoryMovementService::class);
    $inventario->shouldNotReceive('registrarEntrada');

    expect(fn () => (new PurchaseService($inventario))->crearConDetalle([...datosDeCompra(), 'purchase_total' => 12000], [
        ['product_id' => Product::factory()->create()->id, 'quantity' => 2, 'unit_cost' => 6000],
    ]))->toThrow(BusinessException::class, 'El total de la compra no coincide con la suma de detalles menos descuento.');

    $this->assertDatabaseCount('purchases', 0);
});

test('aplica el descuento según el umbral del subtotal', function (float $costo, string $total) {
    $this->actingAs(User::factory()->create());
    Gate::shouldReceive('authorize')->once()->andReturn(Response::allow());
    $inventario = Mockery::mock(InventoryMovementService::class);
    $inventario->shouldReceive('registrarEntrada')->once();

    $purchase = (new PurchaseService($inventario))->crearConDetalle(datosDeCompra(), [
        ['product_id' => Product::factory()->create()->id, 'quantity' => 1, 'unit_cost' => $costo],
    ]);

    expect($purchase->purchase_total)->toBe($total);
})->with([
    'justo debajo de 10 000, sin descuento' => [9999.99, '9999.99'],
    'exactamente 10 000, 5 %' => [10000, '9500.00'],
    'justo debajo de 50 000, 5 %' => [49999, '47499.05'],
    'exactamente 50 000, 10 %' => [50000, '45000.00'],
]);

test('no elimina una compra que tiene detalles', function () {
    $purchase = Purchase::factory()->create([
        'user_id' => User::factory()->create()->id,
        'id_supplier' => Supplier::factory()->create()->id_supplier,
    ]);
    PurchaseItem::factory()->create([
        'id_purchase' => $purchase->id_purchase,
        'product_id' => Product::factory()->create()->id,
    ]);
    Gate::shouldReceive('authorize')->once()->with('delete', $purchase)->andReturn(Response::allow());

    expect(fn () => (new PurchaseService(Mockery::mock(InventoryMovementService::class)))->eliminar($purchase))
        ->toThrow(BusinessException::class, 'No se puede eliminar la compra porque tiene detalles asociados.');

    $this->assertModelExists($purchase);
});
