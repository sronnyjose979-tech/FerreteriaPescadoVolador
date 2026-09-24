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
     * Pedido pagado.
     * DER: Payments (N) — (1) Order via id_order
     */
    public function Sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class, 'id_order', 'id_order');
    }
}
