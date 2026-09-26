<?php

namespace App\Models;

use Database\Factories\PurchaseItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseItem extends Model
{
    /** @use HasFactory<PurchaseItemFactory> */
    use HasFactory;

    protected $table = 'purchase_items';

    protected $fillable = [
        'id_purchase',
        'product_id',
        'quantity',
        'unit_cost',
        'subtotal',
    ];

    /**
     * Atributos ocultos en la serialización JSON.
     */
    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    /**
     * Casts de atributos para conversión automática de tipos.
     */
    protected function casts(): array
    {
        return [
            'unit_cost' => 'decimal:2',
            'subtotal' => 'decimal:2',
        ];
    }

    /**
     * Compra del item.
     * DER: PurchaseItems (N) — (1) Purchase via id_purchase
     */
    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class, 'id_purchase', 'id_purchase');
    }

    /**
     * Producto comprado.
     * DER: PurchaseItems (N) — (1) Product via product_id
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
