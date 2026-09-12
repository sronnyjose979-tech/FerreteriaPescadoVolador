<?php

use App\Http\Controllers\BrandController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UnitController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
// crud de productos
Route::get('/products', [ProductController::class, 'index']);
Route::post('/products', [ProductController::class, 'store']);
Route::get('/products/inventory-summary', [ProductController::class, 'inventorySummary']);
Route::get('/products/{product}', [ProductController::class, 'show']);
Route::put('/products/{product}', [ProductController::class, 'update']);
Route::delete('/products/{product}', [ProductController::class, 'destroy']);

//crud de categorias
Route::get('/categories', [CategoryController::class, 'index']);
Route::post('/categories', [CategoryController::class, 'store']);
Route::get('/categories/{category}', [CategoryController::class, 'show']);
Route::put('/categories/{category}', [CategoryController::class, 'update']);
Route::delete('/categories/{category}', [CategoryController::class, 'destroy']);

//crud de marcas
Route::get('/brands', [BrandController::class, 'index']);
Route::post('/brands', [BrandController::class, 'store']);
Route::get('/brands/{brand}', [BrandController::class, 'show']);
Route::put('/brands/{brand}', [BrandController::class, 'update']);
Route::delete('/brands/{brand}', [BrandController::class, 'destroy']);

//crud de unidades de medida
Route::get('/units', [UnitController::class, 'index']);
Route::post('/units', [UnitController::class, 'store']);
Route::get('/units/{unit}', [UnitController::class, 'show']);
Route::put('/units/{unit}', [UnitController::class, 'update']);
Route::delete('/units/{unit}', [UnitController::class, 'destroy']);

//crud de items de compra(rusell)
Route::get('/Suppliers', [SupplierController::class, 'index']);
Route::post('/Suppliers', [SupplierController::class, 'store']);
Route::get('/Suppliers/{supplier}', [SupplierController::class, 'show']);
Route::put('/Suppliers/{supplier}', [SupplierController::class, 'update']);
Route::delete('/Suppliers/{supplier}', [SupplierController::class, 'destroy']);
