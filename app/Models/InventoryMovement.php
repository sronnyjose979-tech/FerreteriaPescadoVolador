<?php

namespace App\Models;

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
        'user_id',
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
     * DER: InventoryMovement (N) — (1) Product via product_id
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Usuario que registró el movimiento.
     * DER: InventoryMovement.user_id — User.id
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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
