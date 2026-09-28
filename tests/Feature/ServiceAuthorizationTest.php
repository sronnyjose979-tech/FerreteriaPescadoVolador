<?php

use App\Models\Brand;
use App\Models\Category;
use App\Models\Sale;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use App\Services\BrandService;
use App\Services\ProductService;
use App\Services\PurchaseService;
use App\Services\SaleService;
use Illuminate\Auth\Access\AuthorizationException;

test('el servicio rechaza la invocación directa de un rol sin permiso y no guarda nada', function () {
    actingAsRole('cajero');

    expect(fn () => app(ProductService::class)->crear([
        'category_id' => Category::factory()->create()->id,
        'brand_id' => Brand::factory()->create()->id,
        'unit_id' => Unit::factory()->create()->id,
        'name' => 'Martillo',
        'sku' => 'SKU-DIRECTO-1',
        'price' => 1000,
        'stock_quantity' => 10,
    ]))->toThrow(AuthorizationException::class);

    $this->assertDatabaseCount('products', 0);
});

test('el servicio rechaza la invocación directa sin una persona autenticada', function () {
    expect(fn () => app(BrandService::class)->crear(['brand_name' => 'Truper']))
        ->toThrow(AuthorizationException::class);

    $this->assertDatabaseCount('brands', 0);
});

test('el servicio aplica la política de propietario aunque no se use la ruta', function () {
    actingAsRole('cajero');
    $ventaAjena = Sale::factory()->create(['user_id' => User::factory()->create()->id]);

    expect(fn () => app(SaleService::class)->getById($ventaAjena->id))
        ->toThrow(function (AuthorizationException $e) {
            expect($e->status())->toBe(404);
        });
});

test('el servicio de compras rechaza a un rol sin permiso de creación antes de validar el detalle', function () {
    actingAsRole('bodeguero');
    $supplier = Supplier::factory()->create();

    expect(fn () => app(PurchaseService::class)->crearConDetalle([
        'id_purchase' => 'PUR-DIRECTO',
        'id_supplier' => $supplier->id_supplier,
        'purchase_status' => 'pendiente',
    ], []))->toThrow(AuthorizationException::class);

    $this->assertDatabaseCount('purchases', 0);
});
