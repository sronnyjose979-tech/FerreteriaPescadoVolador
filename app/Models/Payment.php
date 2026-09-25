<?php

namespace App\Models;

use Database\Factories\PaymentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    /** @use HasFactory<PaymentsFactory> */
    use HasFactory;


    /**
     * Atributos asignables de forma masiva.
     */
    protected $fillable = [
        'sale_id',
        'payment_method',
        'transaction_reference',
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
     * Pedido pagado.
     * DER: Payments (N) — (1) Order via id_order
     */
    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }
}
