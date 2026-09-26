<?php

namespace App\Http\Controllers;

use App\Http\Requests\PurchaseItem\StorePurchaseItemRequest;
use App\Http\Requests\PurchaseItem\UpdatePurchaseItemRequest;
use App\Http\Resources\PurchaseItemResource;
use App\Models\PurchaseItem;
use App\Services\PurchaseItemService;
use Illuminate\Http\Request;
use Illuminate\Routing\Attributes\Controllers\Authorize;

class PurchaseItemController extends Controller
{
    public function __construct(public PurchaseItemService $purchaseItem)
    {
        $this->purchaseItem = $purchaseItem;
    }

    #[Authorize('viewAny', PurchaseItem::class)]
    public function index(Request $request)
    {
        $purchaseItems = $this->purchaseItem->listPaginated($request->all());
        return PurchaseItemResource::collection($purchaseItems);
    }

    #[Authorize('store', PurchaseItem::class)]
    public function store(StorePurchaseItemRequest $request)
    {
        $purchaseItem = $this->purchaseItem->crear($request->validated());
        return response()->json($purchaseItem, 201);
    }

    #[Authorize('show', 'purchaseItem')]
    public function show(PurchaseItem $purchaseItem)
    {
        return new PurchaseItemResource($purchaseItem);
    }

    #[Authorize('update', PurchaseItem::class)]
    public function update(UpdatePurchaseItemRequest $request, PurchaseItem $purchaseItem)
    {
        $purchaseItems = $this->purchaseItem->actualizar($purchaseItem, $request->validated());
        return new PurchaseItemResource($purchaseItems);
    }

    #[Authorize('delete', PurchaseItem::class)]
    public function destroy(PurchaseItem $purchaseItem)
    {
        $this->purchaseItem->eliminar($purchaseItem);
        return response()->json(null, 204);
    }
}
