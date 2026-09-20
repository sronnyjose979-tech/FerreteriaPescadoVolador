<?php

use App\Http\Controllers\BrandController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\PurchaseItemController;
use App\Http\Controllers\SuppliersController;
use App\Http\Controllers\UnitController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
// crud de productos
Route::apiResource('/products', ProductController::class);
Route::get('/products/inventory-summary', [ProductController::class, 'inventorySummary']);

//crud de categorias
Route::apiResource('/categories', CategoryController::class);

//crud de marcas
Route::apiResource('/brands', BrandController::class);

//crud de unidades de medida
Route::apiResource('/units', UnitController::class);



Route::middleware('auth:sanctum')->group(function () {
    //CRUD DE PURCHASE (Russell)
    Route::apiResource('purchase', PurchaseController::class);

    //CRUD DE SUPPLIER (Russell)
    Route::apiResource('supplier', SuppliersController::class);

    //CRUD DE PURCHASEITEM (Russell)
    Route::apiResource('purchaseItem', PurchaseItemController::class);
});

