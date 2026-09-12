<?php

namespace App\Models;

use Database\Factories\PurchaseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Purchase extends Model
{
    /** @use HasFactory<PurchasesFactory> */
    use HasFactory;

    protected $primaryKey = 'id_Purchase';

    public $incrementing = false;

    protected $keyType = 'string';
    
    protected $fillable = [
        'id_Purchase',
        'id_user',
        'id_Supplier',
        'Purchase_Total',
        'Purchase_status',
    ];
    /**
     * Usuario que registró la compra.
     * DER: Purchase (N) — (1) User via id_user
     */
    public function user(): BelongsTo
    {
        //return $this->belongsTo(User::class, 'id_user', 'id_User');
        return $this->belongsTo(User::class, 'id_user', 'id'); //para trabajar con el unico usuario

    }

    /**
     * Proveedor de la compra.
     * DER: Purchase (N) — (1) Supplier via id_supplier
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'id_Supplier', 'id_Supplier');
    }

    /**
     * Detalle de la compra.
     * DER: Purchase (1) — (N) Purchase_Items via id_Purchase
     */
    public function purchaseItems(): HasMany
    {
        return $this->hasMany(PurchaseItem::class, 'id_Purchase', 'id_Purchase');
    }
    //
}
