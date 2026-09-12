<?php

namespace App\Models;

use Database\Factories\OrderItemsFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order_Item extends Model
{
    /** @use HasFactory<OrderItemsFactory> */
    use HasFactory;

    /**
     * Pedido al que pertenece el item.
     * DER: Orders_items (N) — (1) Order via id_order
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'id_order', 'id_order');
    }

    /**
     * Producto del item.
     * DER: Orders_items (N) — (1) Product via id_product
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'id_product', 'id_Product');
    }
}
