<?php

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;

test('vender descuenta el stock y registra el movimiento de salida', function () {
    $admin = actingAsRole('admin');
    $product = Product::factory()->create(['stock_quantity' => 10]);
    $sale = Sale::factory()->create(['user_id' => $admin->id]);

    $response = $this->postJson('/api/sale-items', [
        'sale_id' => $sale->id,
        'product_id' => $product->id,
        'quantity' => 3,
        'unit_price' => 1500,
    ]);

    $id = $response->json('data.id');
    $response->assertCreated()
        ->assertHeader('Location', url("/api/sale-items/{$id}"))
        ->assertJsonPath('data.subtotal', 4500);
    expect($product->fresh()->stock_quantity)->toBe(7);
    $this->assertDatabaseHas('inventory_movements', [
        'product_id' => $product->id,
        'user_id' => $admin->id,
        'movementable_type' => SaleItem::class,
        'movementable_id' => $id,
        'type' => 'salida',
        'quantity' => 3,
        'stock_after' => 7,
    ]);
});

test('responde 409 al vender más unidades de las disponibles y no guarda el detalle', function () {
    $admin = actingAsRole('admin');
    $product = Product::factory()->create(['name' => 'Taladro', 'stock_quantity' => 2]);
    $sale = Sale::factory()->create(['user_id' => $admin->id]);

    $response = $this->postJson('/api/sale-items', [
        'sale_id' => $sale->id,
        'product_id' => $product->id,
        'quantity' => 3,
        'unit_price' => 1500,
    ]);

    $response->assertConflict()
        ->assertExactJson(['message' => 'Stock insuficiente para el producto Taladro: disponible 2, solicitado 3.']);
    $this->assertDatabaseCount('sale_items', 0);
    $this->assertDatabaseCount('inventory_movements', 0);
    expect($product->fresh()->stock_quantity)->toBe(2);
});

test('eliminar un detalle de venta devuelve las unidades al inventario', function () {
    $admin = actingAsRole('admin');
    $product = Product::factory()->create(['stock_quantity' => 6]);
    $saleItem = SaleItem::factory()->create([
        'sale_id' => Sale::factory()->create(['user_id' => $admin->id])->id,
        'product_id' => $product->id,
        'quantity' => 4,
    ]);

    $response = $this->deleteJson("/api/sale-items/{$saleItem->id}");

    $response->assertNoContent();
    $this->assertModelMissing($saleItem);
    expect($product->fresh()->stock_quantity)->toBe(10);
    $this->assertDatabaseHas('inventory_movements', [
        'product_id' => $product->id,
        'movementable_id' => $saleItem->id,
        'type' => 'entrada',
        'quantity' => 4,
        'stock_after' => 10,
    ]);
});
