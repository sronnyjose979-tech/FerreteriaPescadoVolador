<?php

use App\Exceptions\BusinessException;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\User;
use App\Services\SupplierServices;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Gate;

test('elimina un proveedor sin compras', function () {
    $supplier = Supplier::factory()->create();
    Gate::shouldReceive('authorize')->once()->with('delete', $supplier)->andReturn(Response::allow());

    (new SupplierServices)->eliminar($supplier);

    $this->assertModelMissing($supplier);
});

test('rechaza con 409 eliminar un proveedor con compras asociadas', function () {
    $supplier = Supplier::factory()->create();
    Purchase::factory()->create(['user_id' => User::factory()->create()->id, 'id_supplier' => $supplier->id_supplier]);
    Gate::shouldReceive('authorize')->once()->with('delete', $supplier)->andReturn(Response::allow());

    expect(fn () => (new SupplierServices)->eliminar($supplier))
        ->toThrow(function (BusinessException $e) {
            expect($e->getMessage())->toBe('No se puede eliminar el proveedor porque tiene compras asociadas.');
            expect($e->statusCode)->toBe(409);
        });

    $this->assertModelExists($supplier);
});
