<?php

namespace App\Http\Controllers;

use App\Http\Requests\Supplier\StoreSupplierRequest;
use App\Http\Requests\Supplier\UpdateSupplierRequest;
use App\Http\Resources\SupplierResource;
use App\Models\Supplier;
use App\Services\SupplierServices;
use Illuminate\Http\JsonResponse;
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
    public function store(StoreSupplierRequest $request): JsonResponse
    {
        $supplier = $this->orderSupplier->crear($request->validated());

        return (new SupplierResource($supplier))
            ->response()
            ->setStatusCode(201)
            ->header('Location', route('suppliers.show', $supplier));
    }

    #[Authorize('view', 'supplier')]
    public function show(Supplier $supplier)
    {
        return new SupplierResource($supplier->load('purchases.purchaseItems'));
    }

    #[Authorize('update', 'supplier')]
    public function update(UpdateSupplierRequest $request, Supplier $supplier)
    {
        return new SupplierResource($this->orderSupplier->actualizar($supplier, $request->validated()));
    }

    #[Authorize('delete', 'supplier')]
    public function destroy(Supplier $supplier)
    {
        $this->orderSupplier->eliminar($supplier); // FALTA EL RESOURCE

        return response()->json(null, 204);
    }
}
