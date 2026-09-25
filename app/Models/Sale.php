<?php

namespace App\Models;

use Database\Factories\SaleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    /** @use HasFactory<SalesFactory> */
    use HasFactory;

    /**
     * Usuario que registró la venta.
     * DER: Sale (N) — (1) User via id_user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user', 'id_User');
    }

    /**
     * Cliente de la venta.
     * DER: Sale (N) — (1) Customer via id_customer
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'id_customer', 'id_Customer');
    }



    /**
     * Detalle de la venta.
     * DER: Sale (1) — (N) Sale_Items via Sale_Items.id_sale
     */
    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class, 'sale_id', 'sale_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
