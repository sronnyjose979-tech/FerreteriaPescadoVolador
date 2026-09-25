<?php

namespace App\Models;

use Database\Factories\SaleItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleItem extends Model
{
    /** @use HasFactory<SaleItemsFactory> */
    use HasFactory;

    protected $table = 'sale_items';//le doy el nombre a la tabla

    protected $fillable = [
        'sale_id',
        'product_id',
        'quantity',
        'unit_price',
        'subtotal',
    ];

    /**
     * Venta del item.
     * DER: Sale_Items (N) — (1) Sale via id_sale
     */
    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class, 'sale_id', 'sale_id');
    }

    /**
     * Producto vendido.
     * DER: Sale_Items (N) — (1) Product via id_product
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'id_product', 'id_Product');
    }
}
