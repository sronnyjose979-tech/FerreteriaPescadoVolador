<?php

use App\Http\Controllers\BrandController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\InventoryMovementController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\PurchaseItemController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SaleItemController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UnitController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegisterController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// SECCION DEL LOGIN, REGISTRO
Route::post('/register', RegisterController::class);
Route::post('/login', LoginController::class);



Route::middleware('auth:sanctum')->group(function () {
    //EL LOGOUT ESTA PROTEGIDO(Asi podemos ver que token se va a cerrar)
    Route::post('/logout', [LoginController::class, 'logout']);

    //PUNTO DE VENTA
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


    //CRUD DE PURCHASE 
    Route::apiResource('purchases', PurchaseController::class);
    //CRUD DE SUPPLIER 
    Route::apiResource('suppliers', SupplierController::class);
    //CRUD DE PURCHASEITEM 
    Route::apiResource('purchase-items', PurchaseItemController::class);
    //CRUD DE CUSTOMER
    Route::apiResource('/customers', CustomerController::class);

    Route::apiResource('/inventory-movements', InventoryMovementController::class);
    Route::apiResource('/payments', PaymentController::class);
});
