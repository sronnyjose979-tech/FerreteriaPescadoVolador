<?php

use App\Exceptions\BusinessException;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use App\Services\InventoryMovementService;

function detalleDeVenta(Product $product): SaleItem
{
    return SaleItem::factory()->create([
        'sale_id' => Sale::factory()->create(['user_id' => User::factory()->create()->id])->id,
        'product_id' => $product->id,
    ]);
}

test('una salida descuenta el stock y guarda el saldo en el movimiento', function () {
    $this->actingAs($usuario = User::factory()->create());
    $product = Product::factory()->create(['stock_quantity' => 10]);
    $origen = detalleDeVenta($product);

    $movimiento = (new InventoryMovementService)->registrarSalida($product, $origen, 4);

    expect($product->fresh()->stock_quantity)->toBe(6);
    expect($movimiento->only(['type', 'quantity', 'stock_after', 'user_id']))->toBe([
        'type' => 'salida',
        'quantity' => 4,
        'stock_after' => 6,
        'user_id' => $usuario->id,
    ]);
    expect($origen->inventoryMovement->is($movimiento))->toBeTrue();
});

test('rechaza con 409 una salida mayor que el stock disponible', function () {
    $product = Product::factory()->create(['name' => 'Taladro', 'stock_quantity' => 3]);

    expect(fn () => (new InventoryMovementService)->registrarSalida($product, detalleDeVenta($product), 4))
        ->toThrow(function (BusinessException $e) {
            expect($e->getMessage())->toBe('Stock insuficiente para el producto Taladro: disponible 3, solicitado 4.');
            expect($e->statusCode)->toBe(409);
        });

    expect($product->fresh()->stock_quantity)->toBe(3);
    $this->assertDatabaseCount('inventory_movements', 0);
});

test('permite vender exactamente el stock disponible', function () {
    $product = Product::factory()->create(['stock_quantity' => 4]);

    (new InventoryMovementService)->registrarSalida($product, detalleDeVenta($product), 4);

    expect($product->fresh()->stock_quantity)->toBe(0);
});

test('una entrada suma el stock y registra el movimiento', function () {
    $product = Product::factory()->create(['stock_quantity' => 10]);

    $movimiento = (new InventoryMovementService)->registrarEntrada($product, detalleDeVenta($product), 5);

    expect($product->fresh()->stock_quantity)->toBe(15);
    expect($movimiento->only(['type', 'quantity', 'stock_after']))->toBe([
        'type' => 'entrada',
        'quantity' => 5,
        'stock_after' => 15,
    ]);
});
