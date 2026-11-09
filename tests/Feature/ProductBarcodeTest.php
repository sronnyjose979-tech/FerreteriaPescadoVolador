<?php

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Laravel\Sanctum\Sanctum;

test('crea productos sin código con identificadores únicos y conserva el stock', function (?string $barcode) {
    actingAsRole('bodeguero');
    $payload = Product::factory()->make(['barcode' => $barcode, 'stock_quantity' => 12, 'minimum_stock' => 1])->getAttributes();

    $first = $this->postJson('/api/products', $payload)->assertCreated();
    $payload['sku'] = 'SECOND-SKU';
    $second = $this->postJson('/api/products', $payload)->assertCreated();

    expect($first->json('data.codigo_barras'))->toMatch('/^[0-9]{20}$/');
    expect($second->json('data.codigo_barras'))->not->toBe($first->json('data.codigo_barras'));
    $this->assertDatabaseHas('products', ['id' => $first->json('data.id'), 'barcode' => $first->json('data.codigo_barras'), 'stock_quantity' => 12]);
    $this->assertDatabaseCount('inventory_movements', 0);
})->with([null, '']);

test('conserva el código asignado al crear y no lo regenera al editar', function () {
    actingAsRole('bodeguero');
    $product = Product::factory()->create(['barcode' => '0000123456789']);

    $this->putJson("/api/products/{$product->id}", ['name' => 'Nombre actualizado'])
        ->assertOk()->assertJsonPath('data.codigo_barras', '0000123456789');

    expect($product->fresh()->barcode)->toBe('0000123456789');
});

test('no asigna códigos a registros antiguos sin código durante lecturas o ediciones', function () {
    actingAsRole('bodeguero');
    $product = Product::factory()->create();
    $product->updateQuietly(['barcode' => null]);

    $this->getJson("/api/products/{$product->id}/barcode")->assertUnprocessable()
        ->assertJsonPath('message', 'Este producto no tiene código de barras asignado.');
    $this->putJson("/api/products/{$product->id}", ['name' => 'Producto antiguo'])
        ->assertOk()->assertJsonPath('data.codigo_barras', null);

    expect($product->fresh()->barcode)->toBeNull();
});

test('rechaza un código duplicado incluso de un producto eliminado', function () {
    actingAsRole('admin');
    Product::factory()->create(['barcode' => 'EXISTING'])->delete();
    $payload = Product::factory()->make(['barcode' => 'EXISTING'])->getAttributes();

    $this->postJson('/api/products', $payload)->assertUnprocessable()->assertJsonValidationErrors('barcode');

    expect(fn () => Product::factory()->create(['barcode' => 'EXISTING']))
        ->toThrow(UniqueConstraintViolationException::class);
});

test('consulta el valor escaneado y genera SVG Code 128 del producto correcto sin modificarlo', function () {
    actingAsRole('bodeguero');
    $product = Product::factory()->create(['name' => 'Martillo', 'barcode' => null]);
    $other = Product::factory()->create();
    $before = $product->fresh()->getRawOriginal();
    $otherBefore = $other->fresh()->getRawOriginal();

    $this->getJson('/api/products/by-barcode?'.http_build_query(['barcode' => $product->barcode]))
        ->assertOk()->assertJsonPath('data.id', $product->id)
        ->assertJsonPath('data.codigo_barras', $product->barcode);
    $response = $this->getJson("/api/products/{$product->id}/barcode")
        ->assertOk()->assertHeader('Cache-Control', 'no-store, private')
        ->assertJsonPath('data.id', $product->id)
        ->assertJsonPath('data.nombre', 'Martillo')
        ->assertJsonPath('data.codigo_barras', $product->barcode);

    $xml = simplexml_load_string($response->json('data.svg'));
    expect($xml->getName())->toBe('svg');
    expect((string) $xml['height'])->toBe('80');
    expect($xml->xpath('//*[local-name()="rect"]'))->not->toBeEmpty();
    expect($product->fresh()->getRawOriginal())->toBe($before);
    expect($other->fresh()->getRawOriginal())->toBe($otherBefore);
    $this->assertDatabaseCount('inventory_movements', 0);
});

test('devuelve 404 para códigos inexistentes parciales comodines o con mayúsculas diferentes', function (string $barcode) {
    actingAsRole('bodeguero');
    Product::factory()->create(['barcode' => 'AbC/000123']);

    $this->getJson('/api/products/by-barcode?'.http_build_query(['barcode' => $barcode]))->assertNotFound();
})->with(['missing', 'AbC', '%', 'abc/000123', 'AbC/000123 ']);

test('acepta códigos con ceros iniciales y caracteres de URL sin transformarlos', function () {
    actingAsRole('bodeguero');
    $product = Product::factory()->create(['barcode' => '000123/+?#&']);

    $this->getJson('/api/products/by-barcode?'.http_build_query(['barcode' => $product->barcode]))
        ->assertOk()->assertJsonPath('data.id', $product->id)
        ->assertJsonPath('data.codigo_barras', '000123/+?#&');
});

test('excluye productos eliminados de la consulta y el SVG', function () {
    actingAsRole('bodeguero');
    $product = Product::factory()->create(['barcode' => 'DELETED']);
    $product->delete();

    $this->getJson('/api/products/by-barcode?barcode=DELETED')->assertNotFound();
    $this->getJson("/api/products/{$product->id}/barcode")->assertNotFound();
});

test('valida el código de consulta con 422', function (mixed $barcode) {
    actingAsRole('bodeguero');

    $this->getJson('/api/products/by-barcode?'.http_build_query(['barcode' => $barcode]))
        ->assertUnprocessable()->assertJsonValidationErrors('barcode');
})->with(['vacío' => [null], 'arreglo' => [['array']], 'demasiado largo' => [str_repeat('1', 101)]]);

test('un código antiguo incompatible devuelve 422 y se conserva', function () {
    actingAsRole('bodeguero');
    $product = Product::factory()->create(['barcode' => '商品']);

    $this->getJson("/api/products/{$product->id}/barcode")->assertUnprocessable();

    expect($product->fresh()->barcode)->toBe('商品');
});

test('exige autenticación para consultar y generar etiquetas', function () {
    $this->getJson('/api/products/by-barcode?barcode=123')->assertUnauthorized();
    $this->getJson('/api/products/1/barcode')->assertUnauthorized();
});

test('rechaza usuarios sin permiso de consulta con 403', function () {
    $this->actingAs(User::factory()->create());
    $product = Product::factory()->create();

    $this->getJson('/api/products/by-barcode?barcode='.$product->barcode)->assertForbidden();
    $this->getJson("/api/products/{$product->id}/barcode")->assertForbidden();
});

test('rechaza tokens sin la habilidad view products aunque el usuario tenga permiso', function () {
    $user = actingAsRole('bodeguero');
    Sanctum::actingAs($user, ['update products']);
    $product = Product::factory()->create();

    $this->getJson('/api/products/by-barcode?barcode='.$product->barcode)->assertForbidden();
    $this->getJson("/api/products/{$product->id}/barcode")->assertForbidden();
});
