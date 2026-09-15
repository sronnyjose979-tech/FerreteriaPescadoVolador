<?php

use App\Exceptions\BusinessException;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use App\Services\ProductService;
use App\Services\PurchaseItemService;
use App\Services\PurchaseService;
use App\Services\SupplierServices;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('no permite eliminar producto con dependencias activas', function () {
    $product = Product::factory()->create();
    $supplier = Supplier::factory()->create();
    $user = User::factory()->create();
    $purchase = Purchase::create([
        'id_Purchase' => 'PUR-001',
        'id_user' => $user->id,
        'id_Supplier' => $supplier->id_Supplier,
        'Purchase_Total' => 0,
        'Purchase_status' => 'pendiente',
    ]);
    PurchaseItem::create([
        'id_Purchase' => $purchase->id_Purchase,
        'id_product' => $product->id,
        'quantity' => 2,
        'unit_cost' => 100,
        'subtotal' => 200,
    ]);

    $service = app(ProductService::class);

    expect(fn () => $service->deleteProduct($product))->toThrow(BusinessException::class, 'dependencias activas');
});

test('valida coherencia de stock minimo mayor que maximo', function () {
    $service = app(ProductService::class);

    expect(fn () => $service->createProduct([
        'category_id' => Category::factory()->create()->id,
        'brand_id' => Brand::factory()->create()->id,
        'unit_id' => Unit::factory()->create()->id,
        'name' => 'Producto Test',
        'sku' => 'SKU-TEST-001',
        'price' => 1000,
        'stock_quantity' => 5,
        'minimum_stock' => 20,
        'maximum_stock' => 10,
    ]))->toThrow(BusinessException::class, 'mínimo no puede ser mayor');
});

test('no permite crear compra sin detalle', function () {
    $supplier = Supplier::factory()->create();
    $user = User::factory()->create();
    $service = app(PurchaseService::class);

    expect(fn () => $service->crearConDetalle([
        'id_Purchase' => 'PUR-002',
        'id_user' => $user->id,
        'id_Supplier' => $supplier->id_Supplier,
        'Purchase_status' => 'pendiente',
    ], []))->toThrow(BusinessException::class, 'sin detalle');
});

test('aplica descuento segun umbral y valida total', function () {
    $supplier = Supplier::factory()->create();
    $user = User::factory()->create();
    $product = Product::factory()->create(['price' => 10000]);
    $service = app(PurchaseService::class);

    $purchase = $service->crearConDetalle([
        'id_Purchase' => 'PUR-003',
        'id_user' => $user->id,
        'id_Supplier' => $supplier->id_Supplier,
        'Purchase_status' => 'pendiente',
    ], [
        ['id_product' => $product->id, 'quantity' => 2, 'unit_cost' => 6000, 'subtotal' => 12000],
    ]);

    expect($purchase->Purchase_Total)->toBe(11400.0);
});

test('valida subtotal igual a cantidad por costo unitario', function () {
    $product = Product::factory()->create();
    $supplier = Supplier::factory()->create();
    $purchase = Purchase::create([
        'id_Purchase' => 'PUR-004',
        'id_user' => User::factory()->create()->id,
        'id_Supplier' => $supplier->id_Supplier,
        'Purchase_Total' => 0,
        'Purchase_status' => 'pendiente',
    ]);
    $service = app(PurchaseItemService::class);

    expect(fn () => $service->crear([
        'id_Purchase' => $purchase->id_Purchase,
        'id_product' => $product->id,
        'quantity' => 2,
        'unit_cost' => 100,
        'subtotal' => 999,
    ]))->toThrow(BusinessException::class, 'subtotal debe ser igual');
});

test('reversion de transaccion ante fallo intermedio no deja registros parciales', function () {
    $supplier = Supplier::factory()->create();
    $user = User::factory()->create();
    $productOk = Product::factory()->create();
    $service = app(PurchaseService::class);

    try {
        $service->crearConDetalle([
            'id_Purchase' => 'PUR-005',
            'id_user' => $user->id,
            'id_Supplier' => $supplier->id_Supplier,
            'Purchase_status' => 'pendiente',
        ], [
            ['id_product' => $productOk->id, 'quantity' => 1, 'unit_cost' => 100, 'subtotal' => 100],
            ['id_product' => 999999, 'quantity' => 1, 'unit_cost' => 100, 'subtotal' => 999],
        ]);
    } catch (BusinessException $e) {
    }

    expect(Purchase::where('id_Purchase', 'PUR-005')->exists())->toBeFalse();
    expect(PurchaseItem::where('id_Purchase', 'PUR-005')->exists())->toBeFalse();
});

test('no permite eliminar proveedor con compras asociadas', function () {
    $supplier = Supplier::factory()->create();
    $user = User::factory()->create();
    Purchase::create([
        'id_Purchase' => 'PUR-006',
        'id_user' => $user->id,
        'id_Supplier' => $supplier->id_Supplier,
        'Purchase_Total' => 100,
        'Purchase_status' => 'pendiente',
    ]);
    $service = app(SupplierServices::class);

    expect(fn () => $service->eliminar($supplier))->toThrow(BusinessException::class, 'compras asociadas');
});
