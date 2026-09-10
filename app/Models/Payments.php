<?php

namespace App\Models;

use Database\Factories\PaymentsFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payments extends Model
{
    /** @use HasFactory<PaymentsFactory> */
    use HasFactory;

    /**
     * Pedido pagado.
     * DER: Payments (N) — (1) Order via id_order
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Orders::class, 'id_order', 'id_order');
    }
}
