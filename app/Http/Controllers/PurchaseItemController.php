<?php

namespace App\Http\Controllers;

use App\Models\PurchaseItem;
use Illuminate\Http\Request;
use App\Services\PurchaseItemService;
use App\Http\Requests\StorePurchaseItemRequest;


class PurchaseItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function __construct(public PurchaseItemService $purchaseItemService)
    {
        $this->purchaseItemService = $purchaseItemService;
    }

    public function index(Request $request)
    {
        $id = $request->input('id');
        return PurchaseItem::when($id, function ($query, $id) {
            return $query->where('id_PurchaseItem', 'like', "$id%");
        })
            ->paginate(10);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePurchaseItemRequest $request)
    {
        $validatedData = $request->validated();

        $purchaseItem = $this->purchaseItemService->crear($validatedData);

        return $purchaseItem;
    }

    /**
     * Display the specified resource.
     */
    public function show(PurchaseItem $purchaseItem)
    {
        $purchaseItem->load(['purchase', 'product']);

        return $purchaseItem;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PurchaseItem $purchaseItem)
    {
        $validatedData = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],

            'unit_cost' => ['required', 'numeric', 'min:0'],

            'subtotal' => ['required', 'numeric', 'min:0'],
        ]);

        $purchaseItem->update($validatedData);

        return $purchaseItem;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PurchaseItem $purchaseItem)
    {
        $purchaseItem->delete();

        return response()->json(null, 204);
    }
}
