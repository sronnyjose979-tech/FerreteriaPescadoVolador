<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Brand extends Model
{
    use HasFactory;

    protected $fillable = [
        'brand_name',
    ];

    /**
     * Productos de la marca.
     * DER: Brands (1) — (N) Product via Product.brand_id
     */
    public function products(): HasMany
    {
        //cuando se crea la migracion de forma de laravel con id, se puede usar asi,
        // si no se debe especificar el nombre de la llave primari
        return $this->hasMany(Product::class);
    }
}
