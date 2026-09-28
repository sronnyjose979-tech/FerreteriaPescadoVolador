<?php

namespace App\Http\Controllers;

use App\Http\Requests\Purchase\StorePurchaseRequest;
use App\Http\Requests\Purchase\UpdatePurchaseRequest;
use App\Http\Resources\PurchaseResource;
use App\Models\Purchase;
use App\Services\PurchaseService;
use Illuminate\Http\JsonResponse;
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
    public function store(StorePurchaseRequest $request): JsonResponse
    {
        $purchase = $this->purchase->crearConDetalle(
            $request->safe()->except('items'),
            $request->validated('items'),
        );

        return (new PurchaseResource($purchase))
            ->response()
            ->setStatusCode(201)
            ->header('Location', route('purchases.show', $purchase));
    }

    #[Authorize('view', 'purchase')]
    public function show(Purchase $purchase)
    {
        return new PurchaseResource($purchase);
    }

    #[Authorize('update', 'purchase')]
    public function update(UpdatePurchaseRequest $request, Purchase $purchase)
    {
        $purchase = $this->purchase->actualizar($purchase, $request->validated());

        return new PurchaseResource($purchase);
    }

    #[Authorize('delete', 'purchase')]
    public function destroy(Purchase $purchase)
    {
        $this->purchase->eliminar($purchase);

        return response()->json(null, 204);
    }
}
