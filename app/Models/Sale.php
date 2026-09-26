<?php

namespace App\Models;

use Database\Factories\SaleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    /** @use HasFactory<SaleFactory> */
    use HasFactory;

    /**
     * Atributos asignables de forma masiva.
     */
    protected $fillable = [
        'user_id',
        'id_customer',
        'sale_date',
        'total',
        'tax_amount',
        'discount',
        'status',
    ];

    /**
     * Atributos ocultos en la serialización JSON.
     */
    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    /**
     * Casts de atributos para conversión automática de tipos.
     */
    protected function casts(): array
    {
        return [
            'sale_date' => 'datetime',
            'total' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'discount' => 'decimal:2',
        ];
    }

    /**
     * Usuario que registró la venta.
     * DER: Sale.user_id — User.id
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Cliente de la venta.
     * DER: Sale (N) — (1) Customer via id_customer
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'id_customer', 'id_customer');
    }

    /**
     * Detalle de la venta.
     * DER: Sale (1) — (N) SaleItems via sale_id
     */
    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    /**
     * Pagos recibidos por la venta.
     * DER: Sale (1) — (N) Payment via sale_id
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
