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

    #[Authorize('viewAny', Supplier::class)]
    public function index(Request $request)
    {
        $suppliers = $this->orderSupplier->listPaginated($request->all());

        return SupplierResource::collection($suppliers);
    }

    #[Authorize('create', Supplier::class)]
    public function store(StoreSupplierRequest $request)
    {
        $supplier = $this->orderSupplier->crear($request->validated());

        return new SupplierResource($supplier);
    }

    #[Authorize('view', 'supplier')]
    public function show(Supplier $supplier)
    {
        return new SupplierResource($supplier->load('purchases'));
    }

    #[Authorize('update', Supplier::class)]
    public function update(UpdateSupplierRequest $request, Supplier $supplier)
    {
        return new SupplierResource($this->orderSupplier->actualizar($supplier, $request->validated()));
    }

    #[Authorize('delete', Supplier::class)]
    public function destroy(Supplier $supplier)
    {
        $this->orderSupplier->eliminar($supplier); //FALTA EL RESOURCE

        return response()->json(null, 204);
    }
}
