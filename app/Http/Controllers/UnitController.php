<?php

namespace App\Http\Controllers;

use App\Http\Requests\Unit\StoreUnitRequest;
use App\Http\Requests\Unit\UpdateUnitRequest;
use App\Http\Requests\Unit\DeleteUnitRequest;
use App\Http\Resources\UnitResource;
use App\Models\Unit;
use App\Services\UnitService;
use Illuminate\Http\Request;
use Illuminate\Routing\Attributes\Controllers\Authorize;

class UnitController extends Controller
{
    public function __construct(protected UnitService $unitService)
    {
        $this->unitService = $unitService;
    }

    #[Authorize('viewAny', Unit::class)]
    public function index(Request $request)
    {
        $units = $this->unitService->listPaginated(
            $request->all()
        );

        return UnitResource::collection($units);
    }

    #[Authorize('create', Unit::class)]
    public function store(StoreUnitRequest $request)
    {
        $unit = $this->unitService->createUnit($request->validated());

        return response()->json(new UnitResource($unit), 201);
    }

    #[Authorize('view', 'unit')]
    public function show(Unit $unit)
    {
        return new UnitResource($unit);
    }

    #[Authorize('update', Unit::class)]
    public function update(UpdateUnitRequest $request, Unit $unit)
    {
        $unit->update($request->validated());

        return new UnitResource($unit);
    }

    #[Authorize('delete', Unit::class)]
    public function destroy(Unit $unit)
    {
        $unit->delete();

        return response()->json(null, 204);
    }
}
