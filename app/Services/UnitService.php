<?php

namespace App\Services;

use App\Models\Unit;

class UnitService
{

    public function createUnit(array $validated)
    {
        $unit = Unit::create($validated);
        return $unit;
    }
}
