<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUnitRequest;
use App\Models\Unit;
use App\Services\UnitService;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    public function __construct(protected UnitService $unitService)// aqui se esta inyectando la clase UnitService para poder usarla en los funciones de este controlador
    {
        $this->unitService = $unitService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $units = Unit::paginate(10);

        return $units;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUnitRequest $request) // menos de 10 lineas de codigo, se esta usando el request para validar los datos
    {
        $validated = $request->validated();

        $validated = $request->validated();
        $unit = $this->unitService->createUnit($validated);

        return $unit;
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $unit = Unit::findOrFail($id);

        return $unit;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $unit = Unit::findOrFail($id);

        $validated = $request->validate([
            'unit_name' => 'string|max:200',
        ]);

        $unit->update($validated);

        return $unit;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $unit = Unit::findOrFail($id);
        $unit->delete();

        $success = true;

        return $success;
    }
}
