<?php

namespace App\Models;

use Database\Factories\InventoryMovementsFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Inventory_Movements extends Model
{
    /** @use HasFactory<InventoryMovementsFactory> */
    use HasFactory;

    /**
     * Producto afectado por el movimiento.
     * DER: Inventory_movement (N) — (1) Product via id_product
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Products::class, 'id_product', 'id_Product');
    }

    /**
     * Entidad polimórfica origen del movimiento (Sale, Purchase, etc.).
     * DER: Inventory_movement.movementable_type / movementable_id
     */
    public function movementable(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'movementable_type', 'movementable_id');
    }
}
