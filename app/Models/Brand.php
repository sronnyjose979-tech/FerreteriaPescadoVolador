<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Brand extends Model
{
    use HasFactory;

    protected $table = 'brands';

    protected $primaryKey = 'id_Brand';

    public $timestamps = false;

    protected $fillable = [
        'Brand_name',
    ];

    /**
     * Productos de la marca.
     * DER: Brands (1) — (N) Product via Product.id_brand
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'id_brand', 'id_Brand');
    }
}
