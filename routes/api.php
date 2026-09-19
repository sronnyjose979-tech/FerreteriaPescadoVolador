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


//crud de supplies(Russell)
Route::apiResource('/suppliers', SuppliersController::class);

//crud de purchase(Russell)
Route::apiResource('/purchases', PurchaseController::class);



//curd de purchase(Russell)
Route::apiResource('/purchases', PurchaseItemController::class);


//crud de purchaseItems(Russell)
Route::apiResource('/purchaseItems', PurchaseItemController::class);
