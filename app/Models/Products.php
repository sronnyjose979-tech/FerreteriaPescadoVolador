<?php

namespace App\Models;

use Database\Factories\ProductsFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Products extends Model
{
    /** @use HasFactory<ProductsFactory> */
    use HasFactory;

    /**
     * Marca del producto.
     * DER: Product (N) — (1) Brands via id_brand
     */
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brands::class, 'id_brand', 'id_Brand');
    }

    /**
     * Categoría del producto.
     * DER: Product (N) — (1) Category via id_Category
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Categories::class, 'id_Category', 'id_Category');
    }

    /**
     * Unidad del producto.
     * DER: Product (N) — (1) Unit via id_unit
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Units::class, 'id_unit', 'id_unit');
    }

    /**
     * Items de venta de este producto.
     * DER: Product (1) — (N) Sale_Items via id_product
     */
    public function saleItems(): HasMany
    {
        return $this->hasMany(Sale_Items::class, 'id_product', 'id_Product');
    }

    /**
     * Items de pedido de este producto.
     * DER: Product (1) — (N) Orders_items via id_product
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(Order_Items::class, 'id_product', 'id_Product');
    }

    /**
     * Items de carrito de este producto.
     * DER: Product (1) — (N) Cart_items via Product_id
     */
    public function cartItems(): HasMany
    {
        return $this->hasMany(Cart_Items::class, 'Product_id', 'id_Product');
    }

    /**
     * Items de compra de este producto.
     * DER: Product (1) — (N) Purchase_Items via id_product
     */
    public function purchaseItems(): HasMany
    {
        return $this->hasMany(Purchase_Items::class, 'id_product', 'id_Product');
    }

    /**
     * Movimientos de inventario de este producto.
     * DER: Product (1) — (N) Inventory_movement via id_product
     */
    public function inventoryMovements(): HasMany
    {
        return $this->hasMany(Inventory_Movements::class, 'id_product', 'id_Product');
    }
}
