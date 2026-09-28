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
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Gate;

function datosDeProducto(array $stock): array
{
    return [
        'category_id' => Category::factory()->create()->id,
        'brand_id' => Brand::factory()->create()->id,
        'unit_id' => Unit::factory()->create()->id,
        'name' => 'Cinta métrica 5 m',
        'sku' => 'SKU-CINTA-005',
        'price' => 3500,
        ...$stock,
    ];
}

test('crea el producto cuando el stock respeta los límites', function () {
    Gate::shouldReceive('authorize')->once()->with('create', Product::class)->andReturn(Response::allow());

    $product = (new ProductService)->crear(datosDeProducto([
        'stock_quantity' => 7,
        'minimum_stock' => 5,
        'maximum_stock' => 10,
    ]));

    expect($product->exists)->toBeTrue();
    $this->assertDatabaseHas('products', ['sku' => 'SKU-CINTA-005', 'stock_quantity' => 7]);
});

test('rechaza con 409 un stock incoherente', function (array $stock, string $message) {
    Gate::shouldReceive('authorize')->once()->with('create', Product::class)->andReturn(Response::allow());

    expect(fn () => (new ProductService)->crear(datosDeProducto($stock)))
        ->toThrow(function (BusinessException $e) use ($message) {
            expect($e->getMessage())->toBe($message);
            expect($e->statusCode)->toBe(409);
        });

    $this->assertDatabaseCount('products', 0);
})->with([
    'mínimo mayor que el máximo' => [['stock_quantity' => 15, 'minimum_stock' => 20, 'maximum_stock' => 10], 'El stock mínimo no puede ser mayor que el stock máximo.'],
    'stock menor que el mínimo' => [['stock_quantity' => 4, 'minimum_stock' => 5, 'maximum_stock' => 10], 'La cantidad en stock no puede ser menor que el stock mínimo.'],
    'stock mayor que el máximo' => [['stock_quantity' => 11, 'minimum_stock' => 5, 'maximum_stock' => 10], 'La cantidad en stock no puede superar el stock máximo.'],
]);

test('acepta un stock igual a los límites', function (int $stock) {
    Gate::shouldReceive('authorize')->once()->andReturn(Response::allow());

    $product = (new ProductService)->crear(datosDeProducto([
        'stock_quantity' => $stock,
        'minimum_stock' => 5,
        'maximum_stock' => 10,
    ]));

    expect($product->fresh()->stock_quantity)->toBe($stock);
})->with([
    'igual al mínimo' => 5,
    'igual al máximo' => 10,
]);

test('valida la coherencia combinando los datos guardados con los nuevos al actualizar', function () {
    $product = Product::factory()->create(['stock_quantity' => 20, 'minimum_stock' => 5, 'maximum_stock' => 50]);
    Gate::shouldReceive('authorize')->once()->with('update', $product)->andReturn(Response::allow());

    expect(fn () => (new ProductService)->actualizar($product, ['maximum_stock' => 10]))
        ->toThrow(BusinessException::class, 'La cantidad en stock no puede superar el stock máximo.');

    expect($product->fresh()->maximum_stock)->toBe(50);
});

test('no elimina un producto con compras asociadas', function () {
    $product = Product::factory()->create();
    PurchaseItem::factory()->create([
        'product_id' => $product->id,
        'id_purchase' => Purchase::factory()->create([
            'user_id' => User::factory()->create()->id,
            'id_supplier' => Supplier::factory()->create()->id_supplier,
        ])->id_purchase,
    ]);
    Gate::shouldReceive('authorize')->once()->with('delete', $product)->andReturn(Response::allow());

    expect(fn () => (new ProductService)->eliminar($product))
        ->toThrow(BusinessException::class, 'No se puede eliminar el producto porque tiene dependencias activas.');

    $this->assertNotSoftDeleted($product);
});

test('no guarda nada cuando la autorización es rechazada', function () {
    Gate::shouldReceive('authorize')->once()->andThrow(new AuthorizationException);

    expect(fn () => (new ProductService)->crear(datosDeProducto(['stock_quantity' => 7])))
        ->toThrow(AuthorizationException::class);

    $this->assertDatabaseCount('products', 0);
});
