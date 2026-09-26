<?php

namespace App\Models;

use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id',
        'brand_id',
        'unit_id',
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
        'image_url',
        'is_active',
    ];

    protected $hidden = [ // los que no se van a mostrar
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
     * DER: Product (1) — (N) SaleItems via product_id
     */
    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    /**
     * Items de compra de este producto.
     * DER: Product (1) — (N) PurchaseItems via product_id
     */
    public function purchaseItems(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    /**
     * Movimientos de inventario de este producto.
     * DER: Product (1) — (N) InventoryMovement via product_id
     */
    public function inventoryMovements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class);
    }

    /**
     * Solo productos activos.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Productos cuyo stock iguala o supera el mínimo.
     */
    public function scopeLowStock(Builder $query): Builder
    {
        return $query->whereColumn('stock_quantity', '<=', 'minimum_stock');
    }
}
