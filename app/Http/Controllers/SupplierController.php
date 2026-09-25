<?php

namespace App\Http\Controllers;

use App\Http\Requests\Supplier\StoreSupplierRequest;
use App\Http\Requests\Supplier\UpdateSupplierRequest;
use App\Http\Resources\SupplierResource;
use App\Models\Supplier;
use App\Services\SupplierServices;
use Illuminate\Http\Request;
use Illuminate\Routing\Attributes\Controllers\Authorize;

class SupplierController extends Controller
{
    public function __construct(public SupplierServices $orderSupplier)
    {
        $this->orderSupplier = $orderSupplier;
    }

    public function index(Request $request)
    {
        $suppliers = $this->orderSupplier->listPaginated($request->all());
        return SupplierResource::collection($suppliers);
    }

    public function store(StoreSupplierRequest $request)
    {
        $supplier = $this->orderSupplier->crear($request->validated());//FALTA EL RESOURCE

        return response()->json($supplier, 201);
    }

    #[Authorize('view', Supplier::class)]
    public function show(Supplier $supplier)
    {
        return new SupplierResource($supplier->load('purchases'));
    }

    public function update(UpdateSupplierRequest $request, Supplier $supplier)
    {
        return new SupplierResource($this->orderSupplier->actualizar($supplier, $request->validated()));
    }

    public function destroy(Supplier $supplier)
    {
        $this->orderSupplier->eliminar($supplier);//FALTA EL RESOURCE

        return response()->json(null, 204);
    }
}
