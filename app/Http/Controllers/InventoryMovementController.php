<?php

namespace App\Http\Controllers;

use App\Http\Requests\InventaryMovement\StoreInventaryMovementRequest;
use App\Http\Requests\InventaryMovement\UpdateInventaryMovementRequest;
use App\Http\Resources\InventoryMovementResource;
use App\Models\InventoryMovement;
use App\Services\InventoryMovementService;
use Illuminate\Http\JsonResponse;
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
        $inventoryMovement = $this->inventaryMovement->listPaginated($request->all());

        return InventoryMovementResource::collection($inventoryMovement);
    }

    /**
     * Store a newly created resource in storage.
     */
    #[Authorize('create', InventoryMovement::class)]
    public function store(StoreInventaryMovementRequest $request): JsonResponse
    {
        $inventoryMovement = $this->inventaryMovement->crear($request->validated());

        return (new InventoryMovementResource($inventoryMovement))
            ->response()
            ->setStatusCode(201)
            ->header('Location', route('inventory-movements.show', $inventoryMovement));
    }

    /**
     * Display the specified resource.
     */
    #[Authorize('view', 'inventory_movement')]
    public function show(InventoryMovement $inventoryMovement)
    {
        return new InventoryMovementResource($inventoryMovement);
    }

    /**
     * Update the specified resource in storage.
     */
    #[Authorize('update', 'inventory_movement')]
    public function update(UpdateInventaryMovementRequest $request, InventoryMovement $inventoryMovement)
    {
        // NO CREEMOS IMPLEMENTARLO YA QUE INVENTARYMOVEMENT ES UN REGISTRO HISTORICO DE TRANSACCIONES, NO SE DEBE ACTUALIZAR
        $inventoryMovement = $this->inventaryMovement->actualizar($inventoryMovement, $request->validated());

        return new InventoryMovementResource($inventoryMovement);
    }

    /**
     * Remove the specified resource from storage.
     */
    #[Authorize('delete', 'inventory_movement')]
    public function destroy(InventoryMovement $inventoryMovement)
    {
        $this->inventaryMovement->eliminar($inventoryMovement);

        return response()->json(null, 204);
    }
}
