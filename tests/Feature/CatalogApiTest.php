<?php

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;

test('crea el catálogo con 201, Location y el Resource', function (string $uri, array $payload, string $idKey) {
    actingAsRole('admin');

    $response = $this->postJson("/api/{$uri}", $payload);

    $id = $response->json("data.{$idKey}");
    $response->assertCreated()
        ->assertHeader('Location', url("/api/{$uri}/{$id}"))
        ->assertJsonPath('data.Nombre', reset($payload));
    $this->assertDatabaseHas($uri, ['id' => $id, ...$payload]);
})->with([
    'marca' => ['brands', ['brand_name' => 'Truper'], 'ID Marca'],
    'categoría' => ['categories', ['category_name' => 'Herramientas', 'description' => 'Herramientas manuales'], 'ID Categoria'],
    'unidad' => ['units', ['unit_name' => 'Galón'], 'ID Unidad de Medida'],
]);

test('responde 409 al eliminar un catálogo con productos asociados', function (string $uri, string $model, string $foreignKey, string $message) {
    actingAsRole('admin');
    $catalogo = $model::factory()->create();
    Product::factory()->create([$foreignKey => $catalogo->id]);

    $response = $this->deleteJson("/api/{$uri}/{$catalogo->id}");

    $response->assertConflict()
        ->assertExactJson(['message' => $message]);
    $this->assertModelExists($catalogo);
})->with([
    'marca' => ['brands', Brand::class, 'brand_id', 'No se puede eliminar la marca porque tiene productos asociados.'],
    'categoría' => ['categories', Category::class, 'category_id', 'No se puede eliminar la categoría porque tiene productos asociados.'],
    'unidad' => ['units', Unit::class, 'unit_id', 'No se puede eliminar la unidad porque tiene productos asociados.'],
]);
