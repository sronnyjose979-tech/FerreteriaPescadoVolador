<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUnitRequest;
use App\Http\Resources\UnitResource;
use App\Models\Unit;
use App\Services\UnitService;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    public function __construct(protected UnitService $unitService)
    {
        $this->unitService = $unitService;
    }

    public function index(Request $request)
    {
        $units = Unit::paginate(10);
        return UnitResource::collection($units);
    }

    public function store(StoreUnitRequest $request)
    {
        $validated = $request->validated();
        $unit = $this->unitService->createUnit($validated);

        return response()->json(new UnitResource($unit), 201);
    }

    public function show(string $id)
    {
        $unit = Unit::findOrFail($id);

        return new UnitResource($unit);
    }

    public function update(Request $request, string $id)
    {
        $unit = Unit::findOrFail($id);

        $validated = $request->validate([
            'unit_name' => 'string|max:200',
        ]);

        $unit->update($validated);

        return new UnitResource($unit);
    }

    public function destroy(string $id)
    {
        $unit = Unit::findOrFail($id);
        $unit->delete();

        return response()->json(null, 204);
    }
}