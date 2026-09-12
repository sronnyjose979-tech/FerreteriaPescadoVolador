<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePurchaseRequest;
use App\Models\Purchase;
use Illuminate\Http\Request;
use App\Services\PurchaseService;
use Illuminate\Validation\Rule;


class PurchaseController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function __construct(public PurchaseService $purchaseService)
    {
        $this->purchaseService = $purchaseService;
    }

    public function index(Request $request)
    {
        $id = $request->input('id');
        return Purchase::when($id, function ($query, $id) {
            return $query->where('id_Purchase', 'like', "$id%");
        })
            ->paginate(10);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePurchaseRequest $request)
    {
        $validatedData = $request->validated(); //retornamos los datos validados, si no son validos retornará un error 422 en el request

        $purchase = $this->purchaseService->crear($validatedData); //llamamos al servicio para crear el pedido

        if ($purchase->total > 1000) {
        }
        return $purchase;
    }

    /**
     * Display the specified resource.
     */
    public function show(Purchase $purchase)
    {
        $purchase->load('purchaseItems');
        return $purchase;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Purchase $purchase)
    {
        $ValidatedData=$request->validate([
         'id_Purchase' => ['required', Rule::unique('Purchase', 'id_Purchase')->ignore($purchase->id)],
        ]);
         $purchase->update($ValidatedData);
        return $purchase;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Purchase $purchase)
    {
         $purchase->delete();
        return response()->json(null, 204);
    }
}
