<?php

namespace App\Models;

use Database\Factories\CartItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    /** @use HasFactory<CartItemsFactory> */
    use HasFactory;

    /**
     * Carrito al que pertenece el item.
     * DER: Cart_items (N) — (1) Shopping_carts via Cart_id
     */
    public function shoppingCart(): BelongsTo
    {
        return $this->belongsTo(Shoppingcart::class, 'Cart_id', 'id_shopping_cart');
    }

    /**
     * Producto del item.
     * DER: Cart_items (N) — (1) Product via Product_id
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'Product_id', 'id_Product');
    }
}
