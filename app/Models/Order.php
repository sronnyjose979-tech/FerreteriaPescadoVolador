<?php

namespace App\Models;

use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    /** @use HasFactory<OrdersFactory> */
    use HasFactory;

    /**
     * Cliente del pedido.
     * DER: Order (N) — (1) Customer via id_customer
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'id_customer', 'id_Customer');
    }

    /**
     * Dirección de entrega del pedido.
     * DER: Order (N) — (1) Customer_Address via id_customer_address
     */
    public function customerAddress(): BelongsTo
    {
        return $this->belongsTo(CustomerAddress::class, 'id_customer_address', 'id_customer_address');
    }

    /**
     * Items del pedido.
     * DER: Order (1) — (N) Orders_items via Orders_items.id_order
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'id_order', 'id_order');
    }

    /**
     * Pago del pedido.
     * DER: Order (1) — (1) Payments via Payments.id_order
     */
    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class, 'id_order', 'id_order');
    }

    /**
     * Venta generada desde el pedido.
     * DER: Sale.id_order → Order.id_order
     */
    public function sale(): HasOne
    {
        return $this->hasOne(Sale::class, 'id_order', 'id_order');
    }
}
