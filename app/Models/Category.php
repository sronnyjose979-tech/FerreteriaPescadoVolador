<?php

namespace App\Models;

use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    /** @use HasFactory<CategoriesFactory> */
    use HasFactory;

    /**
     * Productos de la categoría.
     * DER: Category (1) — (N) Product via Product.id_Category
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'id_Category', 'id_Category');
    }
}
