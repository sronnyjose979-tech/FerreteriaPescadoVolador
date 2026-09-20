<?php

use App\Http\Controllers\BrandController;
use App\Http\Controllers\Cart_ItemController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderItemController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\PurchaseItemController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\ShoppingCartController;
use App\Http\Controllers\SuppliersController;
use App\Http\Controllers\UnitController;
use App\Models\CustomerAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');







Route::middleware('auth:sanctum')->group(function () {
    //PUNTO DE VENTA

    //producto 
    // crud de productos
    Route::apiResource('/products', ProductController::class);
    Route::get('/products/inventory-summary', [ProductController::class, 'inventorySummary']);
    //crud de categorias
    Route::apiResource('/categories', CategoryController::class);
    //crud de marcas
    Route::apiResource('/brands', BrandController::class);
    //crud de unidades de medida
    Route::apiResource('/units', UnitController::class);


    //Cliente
    Route::apiResource('/sales', SaleController::class);
    Route::apiResource('/sale-items', SaleController::class);
    Route::apiResource('/customers', SaleController::class);


    //CRUD DE PURCHASE (Russell)
    Route::apiResource('purchase', PurchaseController::class);
    //CRUD DE SUPPLIER (Russell)
    Route::apiResource('supplier', SuppliersController::class);
    //CRUD DE PURCHASEITEM (Russell)
    Route::apiResource('purchase-items', PurchaseItemController::class);

    //ECOMMERCE
    
    Route::apiResource('/orders', OrderController::class);
    Route::apiResource('/order-items', OrderItemController::class);
    Route::apiResource('/shopping-carts', ShoppingCartController::class);
    Route::apiResource('/cart-items', Cart_ItemController::class);
    Route::apiResource('/customer-addresses', CustomerAddress::class);
    Route::apiResource('/order-items', OrderItemController::class);
});
