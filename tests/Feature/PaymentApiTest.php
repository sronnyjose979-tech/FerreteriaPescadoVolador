<?php

use App\Models\Payment;
use App\Models\Sale;

test('responde 422 con mensajes en español cuando el pago viene vacío', function () {
    actingAsRole('admin');

    $response = $this->postJson('/api/payments', []);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors([
            'sale_id' => 'La venta es obligatoria.',
            'payment_method' => 'El metodo de pago es obligatorio.',
            'amount' => 'El monto es obligatorio.',
            'status' => 'El estado es obligatorio.',
        ]);
    $this->assertDatabaseCount('payments', 0);
});

test('registra un pago parcial con 201 y Location', function () {
    $admin = actingAsRole('admin');
    $sale = Sale::factory()->create(['user_id' => $admin->id, 'total' => 10000]);

    $response = $this->postJson('/api/payments', [
        'sale_id' => $sale->id,
        'payment_method' => 'card',
        'amount' => 4000,
        'transaction_reference' => 'TXN-00000001',
        'status' => 'completed',
    ]);

    $id = $response->json('data.ID Pago');
    $response->assertCreated()
        ->assertHeader('Location', url("/api/payments/{$id}"))
        ->assertJsonPath('data.Monto', 4000);
    $this->assertDatabaseHas('payments', ['id' => $id, 'sale_id' => $sale->id, 'amount' => 4000]);
});

test('responde 409 cuando los pagos superan el total de la venta', function () {
    $admin = actingAsRole('admin');
    $sale = Sale::factory()->create(['user_id' => $admin->id, 'total' => 10000]);
    Payment::factory()->create(['sale_id' => $sale->id, 'amount' => 7000, 'status' => 'completed']);

    $response = $this->postJson('/api/payments', [
        'sale_id' => $sale->id,
        'payment_method' => 'cash',
        'amount' => 3500,
        'status' => 'completed',
    ]);

    $response->assertConflict()
        ->assertExactJson(['message' => 'La suma de los pagos no puede superar el total de la venta.']);
    $this->assertDatabaseCount('payments', 1);
});
