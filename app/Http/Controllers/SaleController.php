<?php

namespace App\Http\Controllers;

use App\Http\Requests\Sale\StoreSaleRequest;
use App\Http\Requests\Sale\UpdateSaleRequest;
use App\Http\Resources\SaleResource;
use App\Models\Sale;
use App\Services\SaleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Attributes\Controllers\Authorize;

class SaleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function __construct(public SaleService $sale)
    {
        $this->sale = $sale;
    }

    #[Authorize('viewAny', Sale::class)]
    public function index(Request $request)
    {
        $sale = $this->sale->listPaginated($request->all());

        return SaleResource::collection($sale);
    }

    /**
     * Store a newly created resource in storage.
     */
    #[Authorize('create', Sale::class)]
    public function store(StoreSaleRequest $request): JsonResponse
    {
        $sale = $this->sale->crear($request->validated());

        return (new SaleResource($sale))
            ->response()
            ->setStatusCode(201)
            ->header('Location', route('sales.show', $sale));
    }

    /**
     * Display the specified resource.
     */
    #[Authorize('view', 'sale')]
    public function show(Sale $sale)
    {
        return new SaleResource($sale);
    }

    /**
     * Update the specified resource in storage.
     */
    #[Authorize('update', 'sale')]
    public function update(UpdateSaleRequest $request, Sale $sale)
    {
        $sale = $this->sale->actualizar($sale, $request->validated());

        return new SaleResource($sale);
    }

    /**
     * Remove the specified resource from storage.
     */
    #[Authorize('delete', 'sale')]
    public function destroy(Sale $sale)
    {
        $this->sale->eliminar($sale);

        return response()->json(null, 204);
    }
}
