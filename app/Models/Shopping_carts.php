<?php

namespace App\Models;

use Database\Factories\ShoppingCartsFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shopping_carts extends Model
{
    /** @use HasFactory<ShoppingCartsFactory> */
    use HasFactory;

    /**
     * Cliente dueño del carrito.
     * DER: Shopping_carts (N) — (1) Customer via Customer_id
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customers::class, 'Customer_id', 'id_Customer');
    }

    /**
     * Items del carrito.
     * DER: Shopping_carts (1) — (N) Cart_items via Cart_id
     */
    public function cartItems(): HasMany
    {
        return $this->hasMany(Cart_Items::class, 'Cart_id', 'id_shopping_cart');
    }
}
