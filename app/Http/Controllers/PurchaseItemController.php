<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePurchaseItemRequest;
use App\Http\Requests\UpdatePurchaseItemRequest;
use App\Http\Resources\PurchaseItemResource;
use App\Models\PurchaseItem;
use App\Services\PurchaseItemService;
use Illuminate\Http\Request;
use Illuminate\Routing\Attributes\Controllers\Authorize;

class PurchaseItemController extends Controller
{
    public function __construct(public PurchaseItemService $purchaseItemService) {}

    public function index(Request $request)
    {
        $purchaseItems = $this->purchaseItemService->listPaginated($request->all());
        return PurchaseItemResource::collection($purchaseItems);
    }

    #[Authorize('view', 'PurchaseItem')]
    public function store(StorePurchaseItemRequest $request)
    {
        $item = $this->purchaseItemService->crear($request->validated());

        return (new PurchaseItemResource($item))
            ->response()
            ->setStatusCode(201)
            ->header(
                'Location',
                url("/api/purchase-items/{$item->id}")
            );
    }

    public function show(PurchaseItem $purchaseItem)
    {
        return new PurchaseItemResource($purchaseItem);
    }

    public function update(UpdatePurchaseItemRequest $request, PurchaseItem $purchaseItem)
    {
        $purchaseItems = $this->purchaseItemService->actualizar(
            $purchaseItem,
            $request->validated()
        );

        return new PurchaseItemResource($purchaseItems);
    }

    public function destroy(PurchaseItem $purchaseItem)
    {
        $this->purchaseItemService->eliminar($purchaseItem);

        return response()->json(null, 204);
    }
}
