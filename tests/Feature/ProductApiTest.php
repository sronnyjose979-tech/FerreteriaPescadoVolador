<?php

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;

test('el cajero consulta el listado paginado de productos', function () {
    actingAsRole('cajero');
    Product::factory()->count(3)->create();

    $response = $this->getJson('/api/products?per_page=2');

    $response->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonStructure([
            'data' => [['id', 'nombre', 'sku', 'precio', 'cantidad_stock', 'stock_minimo', 'stock_maximo']],
            'links' => ['first', 'last', 'prev', 'next'],
            'meta' => ['current_page', 'last_page', 'per_page', 'total'],
        ])
        ->assertJsonPath('meta.total', 3);
});

test('el bodeguero actualiza un producto y el cambio queda guardado', function () {
    actingAsRole('bodeguero');
    $product = Product::factory()->create(['price' => 1500]);

    $response = $this->putJson("/api/products/{$product->id}", ['price' => 2500]);

    $response->assertOk()
        ->assertJsonPath('data.precio', 2500);
    expect($product->fresh()->price)->toBe('2500.00');
});

test('el cajero recibe 403 al eliminar un producto y el producto se conserva', function () {
    actingAsRole('cajero');
    $product = Product::factory()->create();

    $response = $this->deleteJson("/api/products/{$product->id}");

    $response->assertForbidden();
    $this->assertNotSoftDeleted($product);
});

test('el admin crea un producto con 201, Location y los campos del contrato', function () {
    actingAsRole('admin');

    $response = $this->postJson('/api/products', [
        'category_id' => Category::factory()->create()->id,
        'brand_id' => Brand::factory()->create()->id,
        'unit_id' => Unit::factory()->create()->id,
        'name' => 'Martillo de Uña Profesional 16oz',
        'description' => 'Martillo de acero forjado con mango de fibra de vidrio.',
        'sku' => 'SKU-MART-016-001',
        'barcode' => '7501234567890',
        'price' => 12500,
        'stock_quantity' => 45,
        'minimum_stock' => 10,
        'maximum_stock' => 100,
    ]);

    $id = $response->json('data.id');
    $response->assertCreated()
        ->assertHeader('Location', url("/api/products/{$id}"))
        ->assertJsonPath('data.nombre', 'Martillo de Uña Profesional 16oz')
        ->assertJsonPath('data.descripcion', 'Martillo de acero forjado con mango de fibra de vidrio.')
        ->assertJsonPath('data.codigo_barras', '7501234567890');
    $this->assertDatabaseHas('products', [
        'id' => $id,
        'sku' => 'SKU-MART-016-001',
        'description' => 'Martillo de acero forjado con mango de fibra de vidrio.',
        'barcode' => '7501234567890',
    ]);
});

test('responde 422 con el mensaje en español cuando un campo es inválido', function (string $field, mixed $value, string $message) {
    actingAsRole('admin');

    $response = $this->postJson('/api/products', [$field => $value]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors([$field => $message]);
    $this->assertDatabaseCount('products', 0);
})->with([
    'nombre de 151 caracteres' => ['name', str_repeat('a', 151), 'El campo nombre no debe exceder los 150 caracteres.'],
    'precio negativo' => ['price', -5, 'El campo precio debe ser mayor o igual a 0.'],
    'código de barras de 51 caracteres' => ['barcode', str_repeat('7', 51), 'El código de barras no debe exceder los 50 caracteres.'],
    'URL de imagen inválida' => ['image_url', 'no-es-una-url', 'La URL de la imagen debe ser una URL válida.'],
]);

test('el reporte de inventario agrupa por categoría y excluye productos eliminados', function () {
    actingAsRole('cajero');
    $herramientas = Category::factory()->create(['category_name' => 'Herramientas']);
    $electricidad = Category::factory()->create(['category_name' => 'Electricidad']);
    Product::factory()->for($herramientas)->create(['stock_quantity' => 10, 'price' => 1000]);
    Product::factory()->for($herramientas)->create(['stock_quantity' => 20, 'price' => 3000]);
    Product::factory()->for($herramientas)->create(['stock_quantity' => 99, 'price' => 9999])->delete();
    Product::factory()->for($electricidad)->create(['stock_quantity' => 5, 'price' => 500]);

    $response = $this->getJson('/api/products/inventory-summary');

    $response->assertOk()
        ->assertExactJson(['data' => [
            ['category_name' => 'Electricidad', 'total_products' => 1, 'total_stock' => 5, 'average_price' => 500.0],
            ['category_name' => 'Herramientas', 'total_products' => 2, 'total_stock' => 30, 'average_price' => 2000.0],
        ]]);
});
