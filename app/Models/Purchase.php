<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Database\Factories\PurchaseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Purchase extends Model
{
    public static function receivingStartsAt(): ?CarbonImmutable
    {
        $value = config('purchases.receiving_starts_at');

        if (! is_string($value) || $value === '') {
            return null;
        }

        try {
            $date = CarbonImmutable::createFromFormat('!Y-m-d H:i:s', $value, 'UTC');
        } catch (\InvalidArgumentException) {
            return null;
        }

        return $date && $date->format('Y-m-d H:i:s') === $value ? $date : null;
    }

    public function usesReceiptFlow(): bool
    {
        $start = static::receivingStartsAt();

        return $start !== null && $this->created_at?->gt($start) === true;
    }

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
