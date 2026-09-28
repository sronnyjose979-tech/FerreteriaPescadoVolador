<?php

use App\Exceptions\BusinessException;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use App\Services\InventoryMovementService;
use App\Services\SaleItemService;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Gate;

function ventaDePrueba(): Sale
{
    return Sale::factory()->create(['user_id' => User::factory()->create()->id]);
}

test('registra la salida de inventario por la cantidad vendida y calcula el subtotal', function () {
    Gate::shouldReceive('authorize')->once()->with('create', SaleItem::class)->andReturn(Response::allow());
    $product = Product::factory()->create();
    $inventario = Mockery::mock(InventoryMovementService::class);
    $inventario->shouldReceive('registrarSalida')->once()
        ->with(Mockery::on(fn (Product $p) => $p->is($product)), Mockery::type(SaleItem::class), 3);

    $item = (new SaleItemService($inventario))->crear([
        'sale_id' => ventaDePrueba()->id,
        'product_id' => $product->id,
        'quantity' => 3,
        'unit_price' => 1500,
    ]);

    expect($item->subtotal)->toBe(4500.0);
    $this->assertDatabaseHas('sale_items', ['id' => $item->id, 'subtotal' => 4500]);
});

test('revierte el detalle cuando el inventario rechaza la salida', function () {
    Gate::shouldReceive('authorize')->once()->andReturn(Response::allow());
    $inventario = Mockery::mock(InventoryMovementService::class);
    $inventario->shouldReceive('registrarSalida')->once()->andThrow(new BusinessException('Stock insuficiente.'));

    expect(fn () => (new SaleItemService($inventario))->crear([
        'sale_id' => ventaDePrueba()->id,
        'product_id' => Product::factory()->create()->id,
        'quantity' => 3,
        'unit_price' => 1500,
    ]))->toThrow(BusinessException::class, 'Stock insuficiente.');

    $this->assertDatabaseCount('sale_items', 0);
});

test('rechaza un subtotal distinto de cantidad por precio sin tocar el inventario', function () {
    Gate::shouldReceive('authorize')->once()->andReturn(Response::allow());
    $inventario = Mockery::mock(InventoryMovementService::class);
    $inventario->shouldNotReceive('registrarSalida');

    expect(fn () => (new SaleItemService($inventario))->crear([
        'sale_id' => ventaDePrueba()->id,
        'product_id' => Product::factory()->create()->id,
        'quantity' => 3,
        'unit_price' => 1500,
        'subtotal' => 999,
    ]))->toThrow(BusinessException::class, 'El subtotal debe ser igual a cantidad por precio unitario.');

    $this->assertDatabaseCount('sale_items', 0);
});

test('acepta un subtotal que difiere en un centavo por redondeo', function () {
    Gate::shouldReceive('authorize')->once()->andReturn(Response::allow());
    $inventario = Mockery::mock(InventoryMovementService::class);
    $inventario->shouldReceive('registrarSalida')->once();

    $item = (new SaleItemService($inventario))->crear([
        'sale_id' => ventaDePrueba()->id,
        'product_id' => Product::factory()->create()->id,
        'quantity' => 3,
        'unit_price' => 33.33,
        'subtotal' => 100,
    ]);

    expect($item->subtotal)->toBe(99.99);
});

test('al cambiar la cantidad devuelve las unidades anteriores y descuenta las nuevas', function () {
    $product = Product::factory()->create();
    $item = SaleItem::factory()->create([
        'sale_id' => ventaDePrueba()->id,
        'product_id' => $product->id,
        'quantity' => 2,
        'unit_price' => 1000,
        'subtotal' => 2000,
    ]);
    Gate::shouldReceive('authorize')->once()->with('update', $item)->andReturn(Response::allow());
    $inventario = Mockery::mock(InventoryMovementService::class);
    $inventario->shouldReceive('registrarEntrada')->once()
        ->with(Mockery::on(fn (Product $p) => $p->is($product)), $item, 2)->ordered();
    $inventario->shouldReceive('registrarSalida')->once()
        ->with(Mockery::on(fn (Product $p) => $p->is($product)), $item, 5)->ordered();

    $actualizado = (new SaleItemService($inventario))->actualizar($item, ['quantity' => 5]);

    expect($actualizado->subtotal)->toBe(5000.0);
});

test('al eliminar un detalle devuelve las unidades con una entrada de inventario', function () {
    $product = Product::factory()->create();
    $item = SaleItem::factory()->create([
        'sale_id' => ventaDePrueba()->id,
        'product_id' => $product->id,
        'quantity' => 4,
    ]);
    Gate::shouldReceive('authorize')->once()->with('delete', $item)->andReturn(Response::allow());
    $inventario = Mockery::mock(InventoryMovementService::class);
    $inventario->shouldReceive('registrarEntrada')->once()
        ->with(Mockery::on(fn (Product $p) => $p->is($product)), $item, 4);

    (new SaleItemService($inventario))->eliminar($item);

    $this->assertModelMissing($item);
});
