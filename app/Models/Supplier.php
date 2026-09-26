<?php

namespace App\Models;

use Database\Factories\SupplierFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    /** @use HasFactory<SupplierFactory> */
    use HasFactory;

    protected $table = 'suppliers';

    protected $primaryKey = 'id_supplier';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id_supplier',
        'supplier_first_name',
        'supplier_last_name',
        'supplier_phone',
        'supplier_address',
        'supplier_email',
        'supplier_type',
    ];

    /**
     * Atributos ocultos en la serialización JSON.
     */
    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    /**
     * Compras al proveedor.
     * DER: Supplier (1) — (N) Purchase via Purchase.id_supplier
     */
    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class, 'id_supplier', 'id_supplier');
    }
}
