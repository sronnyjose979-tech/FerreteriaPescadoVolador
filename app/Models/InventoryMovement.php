<?php

namespace App\Models;

use Database\Factories\InventoryMovementFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class InventoryMovement extends Model
{
    /** @use HasFactory<InventoryMovementsFactory> */
    use HasFactory;

    protected $fillable = [
        'product_id',
        'movementable_type',
        'movementable_id',
        'type',
        'quantity',
        'stock_after',
    ];

    /**
     * Atributos ocultos en la serialización JSON.
     */
    protected $hidden = [
        'created_at',
        'updated_at',
    ];
    

    /**
     * Producto afectado por el movimiento.
     * DER: Inventory_movement (N) — (1) Product via id_product
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
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
