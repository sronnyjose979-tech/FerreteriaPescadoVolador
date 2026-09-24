<?php

namespace App\Models;

use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    /** @use HasFactory<ProductsFactory> */
    use HasFactory;

    protected $fillable = [  
        
        'name',
        'description',
        'sku',
        'barcode',
        'price',
        'tax_rate',
        'stock_quantity',
        'minimum_stock',
        'maximum_stock',
        'weight',
        'is_active'
    ];

    protected $hidden = [//los que no se van a mostrar 
        'created_at',
        'updated_at',
        'deleted_at',
        'image_url',
        'category_id',
        'brand_id',
        'unit_id',
    ];


    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'tax_rate' => 'decimal:2',
            'weight' => 'decimal:2',
            'is_active' => 'boolean',
            'stock_quantity' => 'integer',
            'minimum_stock' => 'integer',
            'maximum_stock' => 'integer',
        ];
    }

    /**
     * Marca del producto.
     * DER: Product (N) — (1) Brands via id_brand
     */
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    /**
     * Categoría del producto.
     * DER: Product (N) — (1) Category via id_Category
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Unidad del producto.
     * DER: Product (N) — (1) Unit via id_unit
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * Items de venta de este producto.
     * DER: Product (1) — (N) Sale_Items via id_product
     */
    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class, );
    }

    /**
     * Items de pedido de este producto.
     * DER: Product (1) — (N) Orders_items via id_product
     */
 

    /**
     * Items de compra de este producto.
     * DER: Product (1) — (N) Purchase_Items via id_product
     */
    public function purchaseItems(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    /**
     * Movimientos de inventario de este producto.
     * DER: Product (1) — (N) Inventory_movement via id_product
     */
    public function inventoryMovements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeLowStock($query)
    {
        return $query->whereColumn('stock_quantity', '<=', 'minimum_stock');
    }
}
