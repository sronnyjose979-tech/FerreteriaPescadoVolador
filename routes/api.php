<?php

use App\Http\Controllers\BrandController;
use App\Http\Controllers\Cart_ItemController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderItemController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\PurchaseItemController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SaleItemController;
use App\Http\Controllers\ShoppingCartController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UnitController;
use App\Models\CustomerAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/login', LoginController::class);






Route::middleware('auth:sanctum')->group(function () {
    //PUNTO DE VENTA

    //producto 
    // crud de productos
    Route::apiResource('/products', ProductController::class);
    //Route::get('/products/inventory-summary', [ProductController::class, 'inventorySummary']);
    //crud de categorias
    Route::apiResource('/categories', CategoryController::class);
    //crud de marcas
    Route::apiResource('/brands', BrandController::class);
    //crud de unidades de medida
    Route::apiResource('/units', UnitController::class);


    //Cliente
    Route::apiResource('/sales', SaleController::class);
    Route::apiResource('/sale-items', SaleItemController::class);
 

    //CRUD DE PURCHASE (Russell)
    Route::apiResource('purchases', PurchaseController::class);
    //CRUD DE SUPPLIER (Russell)
    Route::apiResource('suppliers', SupplierController::class);
    //CRUD DE PURCHASEITEM (Russell)
    Route::apiResource('purchase-items', PurchaseItemController::class);
    //CRUD DE CUSTOMER
    Route::apiResource('/customers', CustomerController::class);

  
});
