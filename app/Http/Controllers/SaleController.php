<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSaleRequest;
use App\Http\Requests\UpdateSaleRequest;
use App\Http\Resources\SaleResource;
use App\Models\Customer;
use App\Models\Sale;
use App\Services\SaleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;


class SaleController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function __construct(public SaleService $sale)
    {
        $this->sale = $sale;
    }

    public function index(Request $request)
    {
        Gate::authorize('viewAny', Sale::class);
        $sale = $this->sale->listPaginated($request->all());
        return SaleResource::collection($sale);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSaleRequest $request)
    {
        Gate::authorize('create', Sale::class);
        $sale = $this->sale->crear($request->validated());
        return response()->json($sale, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Sale $sale)
    {
        return new SaleResource($sale);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSaleRequest $request, Sale $sale)
    {
        Gate::authorize('update', $sale);
        $sale = $this->sale->actualizar($sale, $request->validated());
        return new SaleResource($sale);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Sale $sale)
    {
        Gate::authorize('delete', $sale);
        $this->sale->eliminar($sale);
        return response()->json(null, 204);
    }
}
