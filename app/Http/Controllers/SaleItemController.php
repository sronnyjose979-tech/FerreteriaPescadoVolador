<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaleItem\StoreSaleItemRequest;
use App\Http\Requests\SaleItem\UpdateSaleItemRequest;
use App\Http\Resources\SaleItemResource;
use App\Models\SaleItem;
use App\Services\SaleItemService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;


class SaleItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function __construct(public SaleItemService $saleItem)
    {
        $this->saleItem = $saleItem;
    }

    public function index(Request $request)
    {
        Gate::authorize('viewAny', SaleItem::class);
        $sale = $this->saleItem->listPaginated($request->all());
        return SaleItemResource::collection($sale);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSaleItemRequest $request)
    {
        Gate::authorize('create', SaleItem::class);
        $sale = $this->saleItem->crear($request->validated());
        return response()->json($sale, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(SaleItem $saleItem)
    {
        return new SaleItemResource($saleItem);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSaleItemRequest $request, SaleItem $saleItem)
    {
        Gate::authorize('update', $saleItem);
        $saleItem = $this->saleItem->actualizar($saleItem, $request->validated());
        return new SaleItemResource($saleItem);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SaleItem $saleItem)
    {
        Gate::authorize('delete', $saleItem);
        $this->saleItem->eliminar($saleItem);
        return response()->json(null, 204);
    }
}
