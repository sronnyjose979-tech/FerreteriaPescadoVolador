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
        'is_active'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at'
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
        return $this->belongsTo(Brand::class, 'id_brand', 'id_Brand');
    }

    /**
     * Categoría del producto.
     * DER: Product (N) — (1) Category via id_Category
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'id_Category', 'id_Category');
    }

    /**
     * Unidad del producto.
     * DER: Product (N) — (1) Unit via id_unit
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'id_unit', 'id_unit');
    }

    /**
     * Items de venta de este producto.
     * DER: Product (1) — (N) Sale_Items via id_product
     */
    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class, 'id_product', 'id_Product');
    }

    /**
     * Items de pedido de este producto.
     * DER: Product (1) — (N) Orders_items via id_product
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'id_product', 'id_Product');
    }

    /**
     * Items de carrito de este producto.
     * DER: Product (1) — (N) Cart_items via Product_id
     */
    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class, 'Product_id', 'id_Product');
    }

    /**
     * Items de compra de este producto.
     * DER: Product (1) — (N) Purchase_Items via id_product
     */
    public function purchaseItems(): HasMany
    {
        return $this->hasMany(PurchaseItem::class, 'id_product', 'id_Product');
    }

    /**
     * Movimientos de inventario de este producto.
     * DER: Product (1) — (N) Inventory_movement via id_product
     */
    public function inventoryMovements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class, 'id_product', 'id_Product');
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
