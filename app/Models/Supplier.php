<?php

namespace App\Models;

use Database\Factories\SuppliersFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    /** @use HasFactory<SuppliersFactory> */
    use HasFactory;
    protected $table = 'suppliers';

    protected $primaryKey = 'id_Supplier';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id_Supplier',
        'Supplier_First_name',
        'Supplier_Last_name',
        'Supplier_Phone',
        'Supplier_Address',
        'Supplier_Email',
        'Supplier_Type',
    ];
    /**
     * Compras al proveedor.
     * DER: Supplier (1) — (N) Purchase via Purchase.id_supplier
     */
    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class, 'id_supplier', 'id_Supplier');
    }
}
