<?php

namespace App\Models;

use Database\Factories\CustomerAddressFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomerAddress extends Model
{
    /** @use HasFactory<CustomerAddressFactory> */
    use HasFactory;

    /**
     * Cliente dueño de la dirección.
     * DER: Customer_Address (N) — (1) Customer via id_customer
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'id_customer', 'id_Customer');
    }

    /**
     * Pedidos que usan esta dirección.
     * DER: Order.id_customer_address → Customer_Address.id_customer_address
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'id_customer_address', 'id_customer_address');
    }
}
