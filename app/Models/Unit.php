<?php

namespace App\Models;

use Database\Factories\UnitFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Unit extends Model
{
    /** @use HasFactory<UnitsFactory> */
    use HasFactory;

    /**
     * Productos que usan esta unidad.
     * DER: Unit (1) — (N) Product via Product.id_unit
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'id_unit', 'id_unit');
    }
}
