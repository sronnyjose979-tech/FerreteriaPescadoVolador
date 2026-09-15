<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePurchaseItemRequest;
use App\Http\Requests\UpdatePurchaseItemRequest;
use App\Models\PurchaseItem;
use App\Services\PurchaseItemService;
use Illuminate\Http\Request;

class PurchaseItemController extends Controller
{
    public function __construct(public PurchaseItemService $purchaseItemService) {}

    public function index(Request $request)
    {
        return $this->purchaseItemService->listPaginated($request->all());
    }

    public function store(StorePurchaseItemRequest $request)
    {
        $item = $this->purchaseItemService->crear($request->validated());

        return response()->json($item, 201)->header('Location', url("/api/purchaseItems/{$item->id}"));
    }

    public function show(PurchaseItem $purchaseItem)
    {
        return $purchaseItem->load(['purchase', 'product']);
    }

    public function update(UpdatePurchaseItemRequest $request, PurchaseItem $purchaseItem)
    {
        return $this->purchaseItemService->actualizar($purchaseItem, $request->validated());
    }

    public function destroy(PurchaseItem $purchaseItem)
    {
        $this->purchaseItemService->eliminar($purchaseItem);

        return response()->json(null, 204);
    }
}
