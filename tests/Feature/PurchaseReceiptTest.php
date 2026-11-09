<?php

use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Supplier;
use App\Models\User;
use App\Services\InventoryMovementService;
use Database\Seeders\PurchaseReceiptPermissionSeeder;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    config(['purchases.receiving_starts_at' => '2026-10-01 00:00:00']);
    $this->travelTo('2026-11-06 12:00:00');
});

function pendingPurchaseForReceipt(Product $product, int $quantity = 4): PurchaseItem
{
    $purchase = Purchase::factory()->create(['purchase_status' => 'pendiente', 'user_id' => User::factory()->create()->id, 'id_supplier' => Supplier::factory()->create()->id_supplier]);

    return PurchaseItem::factory()->create([
        'id_purchase' => $purchase->id_purchase, 'product_id' => $product->id, 'quantity' => $quantity,
    ]);
}

test('una compra pendiente nueva no aumenta existencias ni registra entradas', function () {
    actingAsRole('admin');
    $product = Product::factory()->create(['stock_quantity' => 10]);
    $supplier = Supplier::factory()->create();

    $this->postJson('/api/purchases', [
        'id_purchase' => 'PENDING-1', 'id_supplier' => $supplier->id_supplier,
        'purchase_status' => 'pendiente',
        'items' => [['product_id' => $product->id, 'quantity' => 4, 'unit_cost' => 100]],
    ])->assertCreated()->assertJsonPath('data.purchase_total', '400.00');

    expect($product->fresh()->stock_quantity)->toBe(10);
    expect(PurchaseItem::first()->received_at)->toBeNull();
    $this->assertDatabaseCount('inventory_movements', 0);
});

test('el bodeguero selecciona entre compras del mismo código y recibe cada detalle una sola vez', function () {
    $user = actingAsRole('bodeguero');
    $product = Product::factory()->create(['stock_quantity' => 10, 'barcode' => '000123']);
    $first = pendingPurchaseForReceipt($product);
    $second = pendingPurchaseForReceipt($product);

    $this->getJson('/api/purchase-receipts?barcode=000123')->assertOk()->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.id', $first->id)->assertJsonPath('data.1.id', $second->id)
        ->assertJsonMissingPath('data.0.unit_cost');
    $this->postJson("/api/purchase-items/{$second->id}/receive", ['barcode' => '000123', 'quantity' => 4])
        ->assertOk()->assertJsonPath('data.id', $second->id)
        ->assertJsonPath('data.received_at', '2026-11-06T12:00:00.000000Z');
    $this->postJson("/api/purchase-items/{$second->id}/receive", ['barcode' => '000123', 'quantity' => 4])
        ->assertConflict();

    expect($product->fresh()->stock_quantity)->toBe(14);
    expect($first->fresh()->received_at)->toBeNull();
    expect($second->purchase->fresh()->purchase_status)->toBe('recibida');
    $this->assertDatabaseCount('inventory_movements', 1);
    $this->assertDatabaseHas('inventory_movements', [
        'product_id' => $product->id, 'user_id' => $user->id, 'movementable_type' => PurchaseItem::class,
        'movementable_id' => $second->id, 'quantity' => 4, 'stock_after' => 14, 'type' => 'entrada',
    ]);
});

test('rechaza cantidades distintas sin cambiar stock ni kardex', function (int $quantity) {
    actingAsRole('bodeguero');
    $product = Product::factory()->create(['stock_quantity' => 10]);
    $item = pendingPurchaseForReceipt($product);

    $this->postJson("/api/purchase-items/{$item->id}/receive", ['barcode' => $product->barcode, 'quantity' => $quantity])
        ->assertUnprocessable()->assertJsonValidationErrors(['quantity' => 'La cantidad recibida debe coincidir exactamente con la cantidad del detalle.']);

    expect($product->fresh()->stock_quantity)->toBe(10);
    expect($item->fresh()->received_at)->toBeNull();
    $this->assertDatabaseCount('inventory_movements', 0);
})->with([3, 5]);

test('rechaza el código de otro producto sin afectar inventario', function () {
    actingAsRole('bodeguero');
    $product = Product::factory()->create(['barcode' => 'AbC', 'stock_quantity' => 10]);
    $item = pendingPurchaseForReceipt($product);

    $this->postJson("/api/purchase-items/{$item->id}/receive", ['barcode' => 'abc', 'quantity' => 4])
        ->assertUnprocessable()->assertJsonValidationErrors('barcode');

    expect($product->fresh()->stock_quantity)->toBe(10);
    $this->assertDatabaseCount('inventory_movements', 0);
});

test('mantiene la compra pendiente hasta recibir todos sus detalles', function () {
    actingAsRole('bodeguero');
    $product = Product::factory()->create();
    $first = pendingPurchaseForReceipt($product);
    $second = PurchaseItem::factory()->create(['id_purchase' => $first->id_purchase, 'product_id' => $product->id, 'quantity' => 2]);

    $this->postJson("/api/purchase-items/{$first->id}/receive", ['barcode' => $product->barcode, 'quantity' => 4])->assertOk();
    expect($first->purchase->fresh()->purchase_status)->toBe('pendiente');
    $this->postJson("/api/purchase-items/{$second->id}/receive", ['barcode' => $product->barcode, 'quantity' => 2])->assertOk();

    expect($first->purchase->fresh()->purchase_status)->toBe('recibida');
    $this->assertDatabaseCount('inventory_movements', 2);
});

test('excluye históricos incluso si no conservan un movimiento y no modifica sus datos', function () {
    actingAsRole('bodeguero');
    $product = Product::factory()->create(['stock_quantity' => 14]);
    $item = pendingPurchaseForReceipt($product);
    $item->purchase->forceFill(['created_at' => '2026-09-30 12:00:00'])->save();
    $before = $item->fresh()->getRawOriginal();

    $this->getJson('/api/purchase-receipts?barcode='.$product->barcode)->assertOk()->assertJsonCount(0, 'data');
    $this->postJson("/api/purchase-items/{$item->id}/receive", ['barcode' => $product->barcode, 'quantity' => 4])->assertConflict();

    expect($item->fresh()->getRawOriginal())->toBe($before);
    expect($product->fresh()->stock_quantity)->toBe(14);
    $this->assertDatabaseCount('inventory_movements', 0);
});

test('bloquea detalles con movimientos previos sin incrementar nuevamente', function () {
    actingAsRole('bodeguero');
    $product = Product::factory()->create(['stock_quantity' => 14]);
    $item = pendingPurchaseForReceipt($product);
    $movement = InventoryMovement::factory()->create([
        'product_id' => $product->id, 'movementable_type' => PurchaseItem::class, 'movementable_id' => $item->id,
    ]);
    $before = $movement->fresh()->getRawOriginal();

    $this->getJson('/api/purchase-receipts?barcode='.$product->barcode)->assertOk()->assertJsonCount(0, 'data');
    $this->postJson("/api/purchase-items/{$item->id}/receive", ['barcode' => $product->barcode, 'quantity' => 4])->assertConflict();

    expect($product->fresh()->stock_quantity)->toBe(14);
    expect($movement->fresh()->getRawOriginal())->toBe($before);
    $this->assertDatabaseCount('inventory_movements', 1);
});

test('deshabilita recepción si falta la configuración de corte o es inválida', function (?string $start) {
    actingAsRole('bodeguero');
    config(['purchases.receiving_starts_at' => $start]);
    $product = Product::factory()->create(['stock_quantity' => 10]);
    $item = pendingPurchaseForReceipt($product);

    $this->getJson('/api/purchase-receipts?barcode='.$product->barcode)->assertConflict();
    $this->postJson("/api/purchase-items/{$item->id}/receive", ['barcode' => $product->barcode, 'quantity' => 4])->assertConflict();

    expect($product->fresh()->stock_quantity)->toBe(10);
    $this->assertDatabaseCount('inventory_movements', 0);
})->with([null, '', 'invalid', '2026-02-31 00:00:00']);

test('revierte stock movimiento y marcador si falla la persistencia de recepción', function () {
    actingAsRole('bodeguero');
    $product = Product::factory()->create(['stock_quantity' => 10]);
    $item = pendingPurchaseForReceipt($product);
    $inventory = Mockery::mock(InventoryMovementService::class)->makePartial();
    $inventory->shouldReceive('registrarEntrada')->once()->andReturnUsing(function ($product, $item, $quantity) {
        (new InventoryMovementService)->registrarEntrada($product, $item, $quantity);
        throw new RuntimeException('Fallo simulado después del movimiento.');
    });
    $this->app->instance(InventoryMovementService::class, $inventory);

    $this->postJson("/api/purchase-items/{$item->id}/receive", ['barcode' => $product->barcode, 'quantity' => 4])->assertInternalServerError();

    expect($product->fresh()->stock_quantity)->toBe(10);
    expect($item->fresh()->received_at)->toBeNull();
    expect($item->purchase->fresh()->purchase_status)->toBe('pendiente');
    $this->assertDatabaseCount('inventory_movements', 0);
});

test('exige autenticación y permiso específico para recepción', function () {
    $this->getJson('/api/purchase-receipts?barcode=123')->assertUnauthorized();
    $this->postJson('/api/purchase-items/1/receive', [])->assertUnauthorized();
    actingAsRole('cajero');
    $this->getJson('/api/purchase-receipts?barcode=123')->assertForbidden();
    $item = pendingPurchaseForReceipt(Product::factory()->create());
    $this->postJson("/api/purchase-items/{$item->id}/receive", [])->assertForbidden();
});

test('exige habilidad del token sin conceder edición de compras al bodeguero', function () {
    $user = actingAsRole('bodeguero');
    $product = Product::factory()->create();
    $item = pendingPurchaseForReceipt($product);
    Sanctum::actingAs($user, ['view products']);
    $this->getJson('/api/purchase-receipts?barcode='.$product->barcode)->assertForbidden();
    $this->postJson("/api/purchase-items/{$item->id}/receive", ['barcode' => $product->barcode, 'quantity' => 4])->assertForbidden();
    Sanctum::actingAs($user, ['receive purchases']);
    $this->putJson('/api/purchases/'.$item->id_purchase, ['purchase_status' => 'recibida'])->assertForbidden();
    $this->putJson('/api/purchase-items/'.$item->id, ['quantity' => 99])->assertForbidden();
});

test('el permiso de recepción se puede instalar dos veces sin alterar otros permisos', function () {
    $user = actingAsRole('bodeguero');
    $this->seed(PurchaseReceiptPermissionSeeder::class);
    $this->seed(PurchaseReceiptPermissionSeeder::class);

    expect($user->fresh()->can('receive purchases'))->toBeTrue();
    expect($user->fresh()->can('update purchases'))->toBeFalse();
    expect($user->fresh()->can('update purchase-items'))->toBeFalse();
});

test('agregar editar y eliminar detalles pendientes no mueve stock', function () {
    actingAsRole('admin');
    $product = Product::factory()->create(['stock_quantity' => 10]);
    $existing = pendingPurchaseForReceipt($product);

    $response = $this->postJson('/api/purchase-items', [
        'id_purchase' => $existing->id_purchase, 'product_id' => $product->id, 'quantity' => 2, 'unit_cost' => 100,
    ])->assertCreated();
    $id = $response->json('data.ID Detalle');
    $this->putJson('/api/purchase-items/'.$id, ['quantity' => 3])->assertOk();
    $this->deleteJson('/api/purchase-items/'.$id)->assertNoContent();

    expect($product->fresh()->stock_quantity)->toBe(10);
    $this->assertDatabaseCount('inventory_movements', 0);
});

test('no permite editar eliminar ni reabrir un detalle recibido', function () {
    actingAsRole('admin');
    $product = Product::factory()->create(['stock_quantity' => 10]);
    $item = pendingPurchaseForReceipt($product);
    $this->postJson("/api/purchase-items/{$item->id}/receive", ['barcode' => $product->barcode, 'quantity' => 4])->assertOk();

    $this->putJson('/api/purchase-items/'.$item->id, ['quantity' => 99])->assertConflict();
    $this->deleteJson('/api/purchase-items/'.$item->id)->assertConflict();
    $this->putJson('/api/purchases/'.$item->id_purchase, ['purchase_status' => 'pendiente'])->assertConflict();
    $this->postJson('/api/purchase-items', [
        'id_purchase' => $item->id_purchase, 'product_id' => $product->id, 'quantity' => 1, 'unit_cost' => 100,
    ])->assertConflict();

    expect($product->fresh()->stock_quantity)->toBe(14);
    expect($item->fresh()->quantity)->toBe(4);
    $this->assertDatabaseCount('inventory_movements', 1);
});

test('el marcador impide repetir la recepción aunque se elimine el movimiento', function () {
    actingAsRole('admin');
    $product = Product::factory()->create(['stock_quantity' => 10]);
    $item = pendingPurchaseForReceipt($product);
    $this->postJson("/api/purchase-items/{$item->id}/receive", ['barcode' => $product->barcode, 'quantity' => 4])->assertOk();
    InventoryMovement::query()->delete();

    $this->postJson("/api/purchase-items/{$item->id}/receive", ['barcode' => $product->barcode, 'quantity' => 4])->assertConflict();

    expect($product->fresh()->stock_quantity)->toBe(14);
    expect($item->fresh()->received_at)->not->toBeNull();
});

test('rechaza recepción de compra no pendiente y de producto eliminado', function () {
    actingAsRole('bodeguero');
    $product = Product::factory()->create(['stock_quantity' => 10]);
    $item = pendingPurchaseForReceipt($product);
    $item->purchase->update(['purchase_status' => 'confirmada']);
    $this->postJson("/api/purchase-items/{$item->id}/receive", ['barcode' => $product->barcode, 'quantity' => 4])->assertConflict();
    $item->purchase->update(['purchase_status' => 'pendiente']);
    $product->delete();

    $this->postJson("/api/purchase-items/{$item->id}/receive", ['barcode' => $product->barcode, 'quantity' => 4])->assertNotFound();

    expect(Product::withTrashed()->find($product->id)->stock_quantity)->toBe(10);
    $this->assertDatabaseCount('inventory_movements', 0);
});

test('valida el contrato y no normaliza el código escaneado', function (array $payload, string $field) {
    actingAsRole('bodeguero');
    $product = Product::factory()->create(['barcode' => 'ABC', 'stock_quantity' => 10]);
    $item = pendingPurchaseForReceipt($product);

    $this->postJson("/api/purchase-items/{$item->id}/receive", $payload)->assertUnprocessable()->assertJsonValidationErrors($field);

    expect($product->fresh()->stock_quantity)->toBe(10);
    expect($item->fresh()->received_at)->toBeNull();
    $this->assertDatabaseCount('inventory_movements', 0);
})->with([
    'faltante' => [[], 'quantity'],
    'fracción' => [['barcode' => 'ABC', 'quantity' => 4.5], 'quantity'],
    'cero' => [['barcode' => 'ABC', 'quantity' => 0], 'quantity'],
    'espacio' => [['barcode' => 'ABC ', 'quantity' => 4], 'barcode'],
]);

test('no permite mover un detalle histórico a una compra nueva', function () {
    actingAsRole('admin');
    $product = Product::factory()->create(['stock_quantity' => 10]);
    $old = pendingPurchaseForReceipt($product);
    $old->purchase->forceFill(['created_at' => '2026-09-01 00:00:00'])->save();
    $new = pendingPurchaseForReceipt($product);

    $this->putJson('/api/purchase-items/'.$old->id, ['id_purchase' => $new->id_purchase])->assertConflict();

    expect($old->fresh()->id_purchase)->toBe($old->id_purchase);
    expect($product->fresh()->stock_quantity)->toBe(10);
});
