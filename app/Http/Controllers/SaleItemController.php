<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaleItem\StoreSaleItemRequest;
use App\Http\Requests\SaleItem\UpdateSaleItemRequest;
use App\Http\Resources\SaleItemResource;
use App\Models\SaleItem;
use App\Services\SaleItemService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Attributes\Controllers\Authorize;
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

    #[Authorize('viewAny', SaleItem::class)]
    public function index(Request $request)
    {
        Gate::authorize('viewAny', SaleItem::class);
        $sale = $this->saleItem->listPaginated($request->all());

        return SaleItemResource::collection($sale);
    }

    /**
     * Store a newly created resource in storage.
     */
    #[Authorize('create', SaleItem::class)]
    public function store(StoreSaleItemRequest $request): JsonResponse
    {
        $sale = $this->saleItem->crear($request->validated());

        return (new SaleItemResource($sale))
            ->response()
            ->setStatusCode(201)
            ->header('Location', route('sale-items.show', $sale));
    }

    /**
     * Display the specified resource.
     */
    #[Authorize('view', 'sale_item')]
    public function show(SaleItem $saleItem)
    {
        return new SaleItemResource($saleItem);
    }

    /**
     * Update the specified resource in storage.
     */
    #[Authorize('update', 'sale_item')]
    public function update(UpdateSaleItemRequest $request, SaleItem $saleItem)
    {
        Gate::authorize('update', $saleItem);
        $saleItem = $this->saleItem->actualizar($saleItem, $request->validated());

        return new SaleItemResource($saleItem);
    }

    /**
     * Remove the specified resource from storage.
     */
    #[Authorize('delete', 'sale_item')]
    public function destroy(SaleItem $saleItem)
    {
        $this->saleItem->eliminar($saleItem);

        return response()->json(null, 204);
    }
}
