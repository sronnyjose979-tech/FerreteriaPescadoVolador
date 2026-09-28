<?php

use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Supplier;
use App\Models\User;

test('registra la compra con su detalle, aplica el descuento y suma el stock', function () {
    $admin = actingAsRole('admin');
    $supplier = Supplier::factory()->create();
    $product = Product::factory()->create(['stock_quantity' => 10]);

    $response = $this->postJson('/api/purchases', [
        'id_purchase' => 'PUR-900',
        'user_id' => User::factory()->create()->id,
        'id_supplier' => $supplier->id_supplier,
        'purchase_status' => 'pendiente',
        'items' => [
            ['product_id' => $product->id, 'quantity' => 2, 'unit_cost' => 6000],
        ],
    ]);

    $response->assertCreated()
        ->assertHeader('Location', url('/api/purchases/PUR-900'))
        ->assertJsonPath('data.user_id', $admin->id)
        ->assertJsonPath('data.purchase_total', '11400.00')
        ->assertJsonCount(1, 'data.purchaseItems');
    $this->assertDatabaseHas('purchases', ['id_purchase' => 'PUR-900', 'user_id' => $admin->id, 'purchase_total' => 11400]);
    expect($product->fresh()->stock_quantity)->toBe(12);
    $this->assertDatabaseHas('inventory_movements', [
        'product_id' => $product->id,
        'user_id' => $admin->id,
        'movementable_type' => PurchaseItem::class,
        'type' => 'entrada',
        'quantity' => 2,
        'stock_after' => 12,
    ]);
});

test('rechaza con 409 una compra sin detalle y no guarda nada', function () {
    actingAsRole('admin');
    $supplier = Supplier::factory()->create();

    $response = $this->postJson('/api/purchases', [
        'id_purchase' => 'PUR-901',
        'id_supplier' => $supplier->id_supplier,
        'purchase_status' => 'pendiente',
        'items' => [],
    ]);

    $response->assertConflict()
        ->assertExactJson(['message' => 'No se puede confirmar una compra sin detalle.']);
    $this->assertDatabaseMissing('purchases', ['id_purchase' => 'PUR-901']);
});

test('responde 422 cuando falta el detalle o el estado no es válido', function () {
    actingAsRole('admin');
    $supplier = Supplier::factory()->create();

    $response = $this->postJson('/api/purchases', [
        'id_purchase' => 'PUR-902',
        'id_supplier' => $supplier->id_supplier,
        'purchase_status' => 'Completada',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors([
            'items' => 'Debe enviar el detalle de la compra en el campo items.',
            'purchase_status' => 'El estado debe ser pendiente, confirmada, recibida o cancelada.',
        ]);
    $this->assertDatabaseMissing('purchases', ['id_purchase' => 'PUR-902']);
});

test('lista los detalles de una compra en la ruta anidada', function () {
    $admin = actingAsRole('admin');
    $purchase = Purchase::factory()->create([
        'user_id' => $admin->id,
        'id_supplier' => Supplier::factory()->create()->id_supplier,
    ]);
    PurchaseItem::factory()->count(2)->create([
        'id_purchase' => $purchase->id_purchase,
        'product_id' => Product::factory()->create()->id,
    ]);
    PurchaseItem::factory()->create([
        'id_purchase' => Purchase::factory()->create([
            'user_id' => $admin->id,
            'id_supplier' => $purchase->id_supplier,
        ])->id_purchase,
    ]);

    $response = $this->getJson("/api/purchases/{$purchase->id_purchase}/items");

    $response->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonStructure([
            'data' => [['ID Detalle', 'ID Compra', 'ID Producto', 'Producto', 'Cantidad', 'Costo Unitario', 'SubTotal']],
            'links',
            'meta',
        ])
        ->assertJsonPath('data.0.ID Compra', $purchase->id_purchase);
});

test('el bodeguero solo ve sus compras y recibe 404 en las ajenas', function () {
    $bodeguero = actingAsRole('bodeguero');
    $otroBodeguero = User::factory()->create()->assignRole('bodeguero');
    $supplier = Supplier::factory()->create();
    $propia = Purchase::factory()->create(['user_id' => $bodeguero->id, 'id_supplier' => $supplier->id_supplier]);
    $ajena = Purchase::factory()->create(['user_id' => $otroBodeguero->id, 'id_supplier' => $supplier->id_supplier]);

    $this->getJson('/api/purchases')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id_purchase', $propia->id_purchase);
    $this->getJson("/api/purchases/{$propia->id_purchase}")->assertOk();
    $this->getJson("/api/purchases/{$ajena->id_purchase}")
        ->assertNotFound()
        ->assertExactJson(['message' => 'El recurso solicitado no existe.']);
});
