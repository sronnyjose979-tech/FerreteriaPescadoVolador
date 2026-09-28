<?php

use App\Models\Sale;
use App\Models\User;

test('el cajero registra una venta a su nombre aunque envíe otro usuario', function () {
    $cajero = actingAsRole('cajero');

    $response = $this->postJson('/api/sales', [
        'user_id' => User::factory()->create()->id,
        'sale_date' => '2026-09-22 10:00:00',
        'total' => 15000,
        'status' => 'completed',
    ]);

    $id = $response->json('data.id');
    $response->assertCreated()
        ->assertHeader('Location', url("/api/sales/{$id}"))
        ->assertJsonPath('data.user_id', $cajero->id);
    $this->assertDatabaseHas('sales', ['id' => $id, 'user_id' => $cajero->id, 'total' => 15000]);
});

test('el cajero solo lista sus propias ventas', function () {
    $cajero = actingAsRole('cajero');
    Sale::factory()->count(2)->create(['user_id' => $cajero->id]);
    Sale::factory()->create(['user_id' => User::factory()->create()->id]);

    $response = $this->getJson('/api/sales');

    $response->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.user_id', $cajero->id)
        ->assertJsonPath('data.1.user_id', $cajero->id);
});

test('un cajero recibe 404 al consultar la venta de otro cajero', function () {
    actingAsRole('cajero');
    $otroCajero = User::factory()->create()->assignRole('cajero');
    $sale = Sale::factory()->create(['user_id' => $otroCajero->id]);

    $response = $this->getJson("/api/sales/{$sale->id}");

    $response->assertNotFound()
        ->assertExactJson(['message' => 'El recurso solicitado no existe.']);
});

test('el admin consulta y edita la venta de cualquier cajero', function () {
    actingAsRole('admin');
    $cajero = User::factory()->create()->assignRole('cajero');
    $sale = Sale::factory()->create(['user_id' => $cajero->id, 'status' => 'pendiente']);
    $this->getJson("/api/sales/{$sale->id}")->assertOk()->assertJsonPath('data.id', $sale->id);

    $response = $this->putJson("/api/sales/{$sale->id}", ['status' => 'completado']);

    $response->assertOk()
        ->assertJsonPath('data.status', 'completado');
    expect($sale->fresh()->status)->toBe('completado');
});
