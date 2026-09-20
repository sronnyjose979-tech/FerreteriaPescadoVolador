<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSupplierRequest;
use App\Http\Requests\UpdateSupplierRequest;
use App\Http\Resources\SupplierResource;
use App\Models\Supplier;
use App\Services\SupplierServices;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function __construct(public SupplierServices $orderSupplier) {}

    public function index(Request $request)
    {
        //return $this->orderSupplier->listPaginated($request->all());
        $suppliers = $this->orderSupplier->listPaginated($request->all());

        return SupplierResource::collection($suppliers);
    }

    public function store(StoreSupplierRequest $request)
    {
        $supplier = $this->orderSupplier->crear($request->validated());

        return response()->json($supplier, 201);
    }

    public function show(Supplier $supplier)
    {
        //return $supplier->load('purchases');
        return new SupplierResource($supplier);
    }

    public function update(UpdateSupplierRequest $request, Supplier $supplier)
    {
        $supplier = $this->orderSupplier->actualizar(
            $supplier,
            $request->validated()
        );

        return new SupplierResource($supplier);
    }

    public function destroy(Supplier $supplier)
    {
        $this->orderSupplier->eliminar($supplier);

        return response()->json(null, 204);
    }
}
