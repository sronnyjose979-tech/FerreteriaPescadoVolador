<?php

namespace App\Models;

use Database\Factories\CustomerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    /** @use HasFactory<CustomersFactory> */
    use HasFactory;

    /**
     * Direcciones del cliente.
     * DER: Customer (1) — (N) Customer_Address via Customer_Address.id_customer
     */
    public function addresses(): HasMany
    {
        return $this->hasMany(CustomerAddress::class, 'id_customer', 'id_Customer');
    }

    /**
     * Pedidos del cliente.
     * DER: Customer (1) — (N) Order via Order.id_customer
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'id_customer', 'id_Customer');
    }

    /**
     * Ventas al cliente.
     * DER: Customer (1) — (N) Sale via Sale.id_customer
     */
    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class, 'id_customer', 'id_Customer');
    }

    /**
     * Carritos del cliente.
     * DER: Customer (1) — (N) Shopping_carts via Shopping_carts.Customer_id
     */
    public function shoppingCarts(): HasMany
    {
        return $this->hasMany(ShoppingCart::class, 'Customer_id', 'id_Customer');
    }
}
