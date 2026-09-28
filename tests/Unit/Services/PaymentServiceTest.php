<?php

use App\Exceptions\BusinessException;
use App\Models\Payment;
use App\Models\Sale;
use App\Models\User;
use App\Services\PaymentService;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Gate;

function ventaConTotal(float $total): Sale
{
    return Sale::factory()->create(['user_id' => User::factory()->create()->id, 'total' => $total]);
}

function datosDePago(Sale $sale, float $monto): array
{
    return ['sale_id' => $sale->id, 'payment_method' => 'cash', 'amount' => $monto, 'status' => 'completed'];
}

test('registra un pago parcial dentro del total de la venta', function () {
    Gate::shouldReceive('authorize')->once()->with('create', Payment::class)->andReturn(Response::allow());
    $sale = ventaConTotal(10000);

    $payment = (new PaymentService)->crear(datosDePago($sale, 4000));

    expect($payment->amount)->toBe('4000.00');
    $this->assertDatabaseHas('payments', ['sale_id' => $sale->id, 'amount' => 4000]);
});

test('permite completar exactamente el total de la venta', function () {
    Gate::shouldReceive('authorize')->once()->andReturn(Response::allow());
    $sale = ventaConTotal(10000);
    Payment::factory()->create(['sale_id' => $sale->id, 'amount' => 7000, 'status' => 'completed']);

    (new PaymentService)->crear(datosDePago($sale, 3000));

    $this->assertDatabaseCount('payments', 2);
});

test('rechaza con 409 un pago que supera lo pendiente de la venta', function () {
    Gate::shouldReceive('authorize')->once()->andReturn(Response::allow());
    $sale = ventaConTotal(10000);
    Payment::factory()->create(['sale_id' => $sale->id, 'amount' => 7000, 'status' => 'completed']);

    expect(fn () => (new PaymentService)->crear(datosDePago($sale, 3000.01)))
        ->toThrow(function (BusinessException $e) {
            expect($e->getMessage())->toBe('La suma de los pagos no puede superar el total de la venta.');
            expect($e->statusCode)->toBe(409);
        });

    $this->assertDatabaseCount('payments', 1);
});

test('no cuenta los pagos cancelados como pagados', function () {
    Gate::shouldReceive('authorize')->once()->andReturn(Response::allow());
    $sale = ventaConTotal(10000);
    Payment::factory()->create(['sale_id' => $sale->id, 'amount' => 9000, 'status' => 'cancelled']);

    (new PaymentService)->crear(datosDePago($sale, 5000));

    $this->assertDatabaseHas('payments', ['sale_id' => $sale->id, 'amount' => 5000, 'status' => 'completed']);
});

test('al actualizar un pago no lo cuenta dos veces', function () {
    $sale = ventaConTotal(10000);
    $payment = Payment::factory()->create(['sale_id' => $sale->id, 'amount' => 6000, 'status' => 'completed']);
    Gate::shouldReceive('authorize')->once()->with('update', $payment)->andReturn(Response::allow());

    (new PaymentService)->actualizar($payment, ['amount' => 9000]);

    expect($payment->fresh()->amount)->toBe('9000.00');
});
