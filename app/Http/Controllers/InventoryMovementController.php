<?php

namespace App\Http\Controllers;

use App\Http\Requests\InventaryMovement\StoreInventaryMovementRequest;
use App\Http\Requests\InventaryMovement\UpdateInventaryMovementRequest;
use App\Http\Resources\InventoryMovementResource;
use App\Models\InventoryMovement;
use App\Services\InventoryMovementService;
use Illuminate\Http\Request;
use Illuminate\Routing\Attributes\Controllers\Authorize;

class InventoryMovementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function __construct(public InventoryMovementService $inventaryMovement)
    {
        $this->inventaryMovement = $inventaryMovement;
    }

    #[Authorize('viewAny', InventoryMovement::class)]
    public function index(Request $request)
    {
        $inventaryMovement = $this->inventaryMovement->listPaginated($request->all());
        return InventoryMovementResource::collection($inventaryMovement);
    }

    /**
     * Store a newly created resource in storage.
     */
    #[Authorize('store', InventoryMovement::class)]
    public function store(StoreInventaryMovementRequest $request)
    {
        $inventaryMovement = $this->inventaryMovement->crear($request->validated());
        return response()->json($inventaryMovement, 201);
    }

    /**
     * Display the specified resource.
     */
    #[Authorize('show', 'inventarymovement')]
    public function show(InventoryMovement $inventaryMovement)
    {
        return new InventoryMovementResource($inventaryMovement);
    }

    /**
     * Update the specified resource in storage.
     */
    #[Authorize('update', InventoryMovement::class)]
    public function update(UpdateInventaryMovementRequest $request, InventoryMovement $inventaryMovement)
    {
        //NO CREEMOS IMPLEMENTARLO YA QUE INVENTARYMOVEMENT ES UN REGISTRO HISTORICO DE TRANSACCIONES, NO SE DEBE ACTUALIZAR
        $inventaryMovement = $this->inventaryMovement->actualizar($inventaryMovement, $request->validated());
        return new InventoryMovementResource($inventaryMovement);
    }

    /**
     * Remove the specified resource from storage.
     */
    #[Authorize('delete', InventoryMovement::class)]
    public function destroy(InventoryMovement $inventaryMovement)
    {
        $this->inventaryMovement->eliminar($inventaryMovement);
        return response()->json(null, 204);
    }
}
