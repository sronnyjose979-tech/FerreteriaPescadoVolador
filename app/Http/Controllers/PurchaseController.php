<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePurchaseRequest;
use App\Http\Requests\UpdatePurchaseRequest;
use App\Models\Purchase;
use App\Services\PurchaseService;
use Illuminate\Http\Request;

class PurchaseController extends Controller
{
    public function __construct(public PurchaseService $purchaseService) {}

    public function index(Request $request)
    {
        return $this->purchaseService->listPaginated($request->all());
    }

    public function store(Request $request)
    {
        if ($request->has('items')) {
            $request->validate((new StorePurchaseRequest)->rules(), (new StorePurchaseRequest)->messages());
            $purchase = $this->purchaseService->crearConDetalle($request->only(['id_Purchase', 'id_user', 'id_Supplier', 'Purchase_status', 'Purchase_Total']), $request->input('items'));

            return response()->json($purchase, 201)->header('Location', url("/api/purchases/{$purchase->id_Purchase}"));
        }

        $validated = app(StorePurchaseRequest::class)->validated();
        $purchase = $this->purchaseService->crear($validated);

        return response()->json($purchase, 201)->header('Location', url("/api/purchases/{$purchase->id_Purchase}"));
    }

    public function storeSimple(StorePurchaseRequest $request)
    {
        $purchase = $this->purchaseService->crear($request->validated());

        return response()->json($purchase, 201)->header('Location', url("/api/purchases/{$purchase->id_Purchase}"));
    }

    public function show(Purchase $purchase)
    {
        return $purchase->load('purchaseItems');
    }

    public function update(UpdatePurchaseRequest $request, Purchase $purchase)
    {
        return $this->purchaseService->actualizar($purchase, $request->validated());
    }

    public function destroy(Purchase $purchase)
    {
        $this->purchaseService->eliminar($purchase);

        return response()->json(null, 204);
    }
}
