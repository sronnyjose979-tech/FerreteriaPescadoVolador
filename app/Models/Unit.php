<?php

namespace App\Models;

use Database\Factories\UnitFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Unit extends Model
{
    /** @use HasFactory<UnitFactory> */
    use HasFactory;

    protected $fillable = [
        'unit_name',
    ];

    /**
     * Productos que usan esta unidad.
     * DER: Unit (1) — (N) Product via Product.unit_id
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'unit_id', 'id');
    }
}
