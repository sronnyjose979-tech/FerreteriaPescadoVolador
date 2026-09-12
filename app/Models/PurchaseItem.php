<?php

namespace App\Models;

use Database\Factories\PurchaseItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseItem extends Model
{
    /** @use HasFactory<PurchaseItemsFactory> */
    use HasFactory;
    protected $table = 'purchase__item';
    protected $fillable = [
        'id_Purchase',
        'id_product',
        'quantity',
        'unit_cost',
        'subtotal',
    ];
    /**
     * Compra del item.
     * DER: Purchase_Items (N) — (1) Purchase via id_Purchase
     */
    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class, 'id_Purchase', 'id_Purchase');
    }

    /**
     * Producto comprado.
     * DER: Purchase_Items (N) — (1) Product via id_product
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'id_product', 'id');
    }
}
