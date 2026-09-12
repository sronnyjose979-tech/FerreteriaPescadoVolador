<?php

namespace App\Models;

use Database\Factories\SaleItemsFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sale_Item extends Model
{
    /** @use HasFactory<SaleItemsFactory> */
    use HasFactory;

    /**
     * Venta del item.
     * DER: Sale_Items (N) — (1) Sale via id_sale
     */
    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class, 'id_sale', 'id_Sale');
    }

    /**
     * Producto vendido.
     * DER: Sale_Items (N) — (1) Product via id_product
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'id_product', 'id_Product');
    }
}
