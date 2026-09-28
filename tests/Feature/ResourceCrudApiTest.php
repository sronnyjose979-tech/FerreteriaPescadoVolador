<?php

use App\Models\Brand;
use App\Models\Category;
use App\Models\Customer;
use App\Models\InventoryMovement;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

function compraDePrueba(): Purchase
{
    return Purchase::factory()->create([
        'user_id' => User::factory()->create()->id,
        'id_supplier' => Supplier::factory()->create()->id_supplier,
        'purchase_status' => 'pendiente',
    ]);
}

function ventaDeCatalogo(): Sale
{
    return Sale::factory()->create(['user_id' => User::factory()->create()->id, 'total' => 50000]);
}

$registros = [
    'productos' => ['products', fn () => Product::factory()->create()],
    'categorías' => ['categories', fn () => Category::factory()->create()],
    'marcas' => ['brands', fn () => Brand::factory()->create()],
    'unidades' => ['units', fn () => Unit::factory()->create()],
    'proveedores' => ['suppliers', fn () => Supplier::factory()->create()],
    'compras' => ['purchases', fn () => compraDePrueba()],
    'detalles de compra' => ['purchase-items', fn () => PurchaseItem::factory()->create([
        'id_purchase' => compraDePrueba()->id_purchase,
        'product_id' => Product::factory()->create(['stock_quantity' => 50])->id,
        'quantity' => 2,
    ])],
    'clientes' => ['customers', fn () => Customer::factory()->create()],
    'ventas' => ['sales', fn () => ventaDeCatalogo()],
    'detalles de venta' => ['sale-items', fn () => SaleItem::factory()->create([
        'sale_id' => ventaDeCatalogo()->id,
        'product_id' => Product::factory()->create(['stock_quantity' => 50])->id,
        'quantity' => 2,
    ])],
    'pagos' => ['payments', fn () => Payment::factory()->create(['sale_id' => ventaDeCatalogo()->id, 'amount' => 1000])],
    'movimientos de inventario' => ['inventory-movements', fn () => InventoryMovement::factory()->create([
        'product_id' => Product::factory()->create()->id,
        'user_id' => User::factory()->create()->id,
        'movementable_type' => SaleItem::class,
        'movementable_id' => 1,
    ])],
];

test('el admin crea el recurso con 201, Location y el registro guardado', function (string $uri, Closure $datos, string $tabla, string $clave) {
    actingAsRole('admin');
    $payload = $datos();

    $response = $this->postJson("/api/{$uri}", $payload);

    $id = $response->json("data.{$clave}");
    $response->assertCreated()
        ->assertHeader('Location', url("/api/{$uri}/{$id}"));
    $this->assertDatabaseHas($tabla, $payload);
})->with([
    'proveedor' => ['suppliers', fn () => [
        'id_supplier' => 'SUP-900',
        'supplier_first_name' => 'Juan',
        'supplier_last_name' => 'Madrigal',
        'supplier_phone' => '22223333',
        'supplier_address' => 'San Pedro, Montes de Oca',
        'supplier_type' => 'Proveedor de herramientas',
        'supplier_email' => 'ventas@proveedor.example.com',
    ], 'suppliers', 'id'],
    'cliente' => ['customers', fn () => [
        'id_customer' => 'CUS-950',
        'first_name' => 'Sofía',
        'last_name1' => 'Camacho',
        'email' => 'sofia.camacho@example.com',
    ], 'customers', 'id_customer'],
    'detalle de compra' => ['purchase-items', fn () => [
        'id_purchase' => compraDePrueba()->id_purchase,
        'product_id' => Product::factory()->create()->id,
        'quantity' => 3,
        'unit_cost' => 2500,
    ], 'purchase_items', 'ID Detalle'],
    'movimiento de inventario' => ['inventory-movements', fn () => [
        'product_id' => Product::factory()->create()->id,
        'movementable_type' => PurchaseItem::class,
        'movementable_id' => 1,
        'type' => 'entrada',
        'quantity' => 5,
        'stock_after' => 55,
    ], 'inventory_movements', 'ID Movimiento'],
]);

test('el admin lista cada recurso con paginación y orden descendente', function (string $uri, Model $registro) {
    actingAsRole('admin');

    $response = $this->getJson("/api/{$uri}?per_page=1&direction=desc");

    $response->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('meta.per_page', 1)
        ->assertJsonStructure(['data', 'links' => ['first', 'last', 'prev', 'next'], 'meta' => ['current_page', 'last_page', 'total']]);
})->with($registros);

test('la búsqueda sin coincidencias devuelve una página vacía', function (string $uri, Model $registro) {
    actingAsRole('admin');

    $response = $this->getJson("/api/{$uri}?q=zzz-sin-coincidencias&sort=created_at");

    $response->assertOk()
        ->assertJsonCount(0, 'data')
        ->assertJsonPath('meta.total', 0);
})->with(array_diff_key($registros, array_flip(['detalles de compra', 'movimientos de inventario'])));

test('el admin consulta el detalle de cada recurso', function (string $uri, Model $registro) {
    actingAsRole('admin');

    $response = $this->getJson("/api/{$uri}/{$registro->getRouteKey()}");

    $response->assertOk()
        ->assertJsonStructure(['data']);
})->with($registros);

test('el admin actualiza cada recurso y el cambio queda guardado', function (string $uri, Model $registro, array $cambios) {
    actingAsRole('admin');

    $response = $this->putJson("/api/{$uri}/{$registro->getRouteKey()}", $cambios);

    $response->assertOk();
    $this->assertDatabaseHas($registro->getTable(), [$registro->getKeyName() => $registro->getKey(), ...$cambios]);
})->with([
    'productos' => [...$registros['productos'], ['name' => 'Nombre editado']],
    'categorías' => [...$registros['categorías'], ['category_name' => 'Categoría editada']],
    'marcas' => [...$registros['marcas'], ['brand_name' => 'Marca editada']],
    'unidades' => [...$registros['unidades'], ['unit_name' => 'Unidad editada']],
    'proveedores' => [...$registros['proveedores'], ['supplier_phone' => '22223333']],
    'compras' => [...$registros['compras'], ['purchase_status' => 'recibida']],
    'detalles de compra' => [...$registros['detalles de compra'], ['quantity' => 5]],
    'clientes' => [...$registros['clientes'], ['telephone_number' => '88887777']],
    'ventas' => [...$registros['ventas'], ['status' => 'completado']],
    'detalles de venta' => [...$registros['detalles de venta'], ['quantity' => 1]],
    'pagos' => [...$registros['pagos'], ['status' => 'cancelled']],
    'movimientos de inventario' => [...$registros['movimientos de inventario'], ['quantity' => 7]],
]);

test('el admin elimina cada recurso sin dependencias con 204', function (string $uri, Model $registro) {
    actingAsRole('admin');

    $response = $this->deleteJson("/api/{$uri}/{$registro->getRouteKey()}");

    $response->assertNoContent();
    $registro instanceof Product
        ? $this->assertSoftDeleted($registro)
        : $this->assertModelMissing($registro);
})->with($registros);
