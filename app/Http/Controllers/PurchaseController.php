<?php

namespace App\Http\Controllers;

use App\Http\Requests\Purchase\StorePurchaseRequest;
use App\Http\Requests\Purchase\UpdatePurchaseRequest;
use App\Http\Resources\PurchaseResource;
use App\Models\Purchase;
use App\Services\PurchaseService;
use Illuminate\Http\Request;
use Illuminate\Routing\Attributes\Controllers\Authorize;

class PurchaseController extends Controller
{
    public function __construct(public PurchaseService $purchase)
    {
        $this->purchase = $purchase;
    }

    #[Authorize('viewAny', Purchase::class)]
    public function index(Request $request)
    {
        $purchase = $this->purchase->listPaginated($request->all());
        return PurchaseResource::collection($purchase);
    }

    #[Authorize('create', Purchase::class)]
    public function store(StorePurchaseRequest $request)
    {
        $purchase = $this->purchase->crear($request->validated());
        return response()->json($purchase, 201);
    }

    #[Authorize('view', 'purchase')]
    public function show(Purchase $purchase)
    {
        return new PurchaseResource($purchase);
    }

    #[Authorize('update', Purchase::class)]
    public function update(UpdatePurchaseRequest $request, Purchase $purchase)
    {
        $purchase = $this->purchase->actualizar($purchase, $request->validated());
        return new PurchaseResource($purchase);
    }

    #[Authorize('delete', Purchase::class)]
    public function destroy(Purchase $purchase)
    {
        $this->purchase->eliminar($purchase);

        return response()->json(null, 204);
    }
}
