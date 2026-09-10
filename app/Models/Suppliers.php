<?php

namespace App\Models;

use Database\Factories\SuppliersFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Suppliers extends Model
{
    /** @use HasFactory<SuppliersFactory> */
    use HasFactory;

    /**
     * Compras al proveedor.
     * DER: Supplier (1) — (N) Purchase via Purchase.id_supplier
     */
    public function purchases(): HasMany
    {
        return $this->hasMany(Purchases::class, 'id_supplier', 'id_Supplier');
    }
}
