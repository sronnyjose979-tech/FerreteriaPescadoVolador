<?php

use App\Exceptions\BusinessException;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use Illuminate\Support\Facades\Exceptions;
use Illuminate\Support\Facades\Route;

test('responde 400 cuando el cuerpo JSON está mal formado', function () {
    actingAsRole('admin');

    $response = $this->call('POST', '/api/brands', server: [
        'CONTENT_TYPE' => 'application/json',
        'HTTP_ACCEPT' => 'application/json',
    ], content: '{"brand_name": "Truper"');

    $response->assertBadRequest()
        ->assertExactJson(['message' => 'El cuerpo de la solicitud no es un JSON válido.']);
    $this->assertDatabaseCount('brands', 0);
});

test('responde 401 sin traza cuando la peticion no trae token', function () {
    $response = $this->getJson('/api/products');

    $response->assertUnauthorized()
        ->assertExactJson(['message' => 'No autenticado.']);
});

test('responde 403 sin traza cuando el rol no tiene permiso', function () {
    actingAsRole('cajero');
    $product = Product::factory()->create(['price' => 1500]);

    $response = $this->putJson("/api/products/{$product->id}", ['price' => 10]);

    $response->assertForbidden()
        ->assertExactJson(['message' => 'No tiene permiso para realizar esta acción.']);
    expect($product->fresh()->price)->toBe('1500.00');
});

test('responde 404 sin traza cuando el recurso no existe', function () {
    actingAsRole('admin');

    $response = $this->getJson('/api/products/999999');

    $response->assertNotFound()
        ->assertExactJson(['message' => 'El recurso solicitado no existe.']);
});

test('responde 405 con el encabezado Allow cuando el metodo no esta permitido', function () {
    $response = $this->postJson('/api/products/1');

    $response->assertMethodNotAllowed()
        ->assertHeader('Allow', 'GET, HEAD, PUT, PATCH, DELETE')
        ->assertExactJson(['message' => 'El método HTTP no está permitido para esta ruta.']);
});

test('responde 409 cuando se viola una regla de negocio y no la registra como error', function () {
    Exceptions::fake();
    actingAsRole('admin');
    $product = Product::factory()->create([
        'stock_quantity' => 20,
        'minimum_stock' => 5,
        'maximum_stock' => 50,
    ]);

    $response = $this->putJson("/api/products/{$product->id}", ['stock_quantity' => 999]);

    $response->assertConflict()
        ->assertExactJson(['message' => 'La cantidad en stock no puede superar el stock máximo.']);
    expect($product->fresh()->stock_quantity)->toBe(20);
    Exceptions::assertNotReported(BusinessException::class);
});

test('responde 409 cuando el subtotal de un detalle de compra no coincide', function () {
    $admin = actingAsRole('admin');
    $product = Product::factory()->create();
    $purchase = Purchase::factory()->create([
        'user_id' => $admin->id,
        'id_supplier' => Supplier::factory()->create()->id_supplier,
    ]);

    $response = $this->postJson('/api/purchase-items', [
        'id_purchase' => $purchase->id_purchase,
        'product_id' => $product->id,
        'quantity' => 2,
        'unit_cost' => 100,
        'subtotal' => 999,
    ]);

    $response->assertConflict()
        ->assertExactJson(['message' => 'El subtotal debe ser igual a cantidad por costo unitario.']);
    $this->assertDatabaseMissing('purchase_items', ['id_purchase' => $purchase->id_purchase]);
});

test('responde 422 con los errores por campo y sin traza cuando los datos son invalidos', function () {
    actingAsRole('admin');

    $response = $this->postJson('/api/products', []);

    $response->assertUnprocessable()
        ->assertJsonPath('message', 'Los datos enviados no son válidos.')
        ->assertJsonValidationErrors([
            'category_id' => 'El campo categoría es obligatorio.',
            'name' => 'El campo nombre es obligatorio.',
            'price' => 'El campo precio es obligatorio.',
        ])
        ->assertJsonMissingPath('trace');
});

test('responde 500 con un mensaje generico sin detalles internos y registra el error', function () {
    Exceptions::fake();
    Route::get('/api/prueba-error-interno', fn () => throw new RuntimeException('Detalle interno del servidor'));

    $response = $this->getJson('/api/prueba-error-interno');

    $response->assertInternalServerError()
        ->assertExactJson(['message' => 'Ocurrió un error interno. Intente de nuevo más tarde.']);
    Exceptions::assertReported(RuntimeException::class);
});
