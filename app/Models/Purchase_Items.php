<?php

namespace App\Models;

use Database\Factories\PurchaseItemsFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Purchase_Items extends Model
{
    /** @use HasFactory<PurchaseItemsFactory> */
    use HasFactory;

    /**
     * Compra del item.
     * DER: Purchase_Items (N) — (1) Purchase via id_Purchase
     */
    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchases::class, 'id_Purchase', 'id_Purchase');
    }

    /**
     * Producto comprado.
     * DER: Purchase_Items (N) — (1) Product via id_product
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Products::class, 'id_product', 'id_Product');
    }
}
