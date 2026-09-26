<?php

namespace App\Models;

use Database\Factories\PurchaseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Purchase extends Model
{
    /** @use HasFactory<PurchaseFactory> */
    use HasFactory;

    protected $primaryKey = 'id_purchase';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id_purchase',
        'user_id',
        'id_supplier',
        'purchase_total',
        'purchase_status',
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
            'purchase_total' => 'decimal:2',
        ];
    }

    /**
     * Usuario que registró la compra.
     * DER: Purchase.user_id — User.id
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Proveedor de la compra.
     * DER: Purchase (N) — (1) Supplier via id_supplier
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'id_supplier', 'id_supplier');
    }

    /**
     * Detalle de la compra.
     * DER: Purchase (1) — (N) PurchaseItems via id_purchase
     */
    public function purchaseItems(): HasMany
    {
        return $this->hasMany(PurchaseItem::class, 'id_purchase', 'id_purchase');
    }
}
