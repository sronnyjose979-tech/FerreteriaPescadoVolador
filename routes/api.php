<?php

use App\Http\Controllers\BrandsController;
use App\Http\Controllers\CategoriesController;
use App\Http\Controllers\ProductsController;
use App\Http\Controllers\UnitsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
// crud de productos
Route::get('/products', [ProductsController::class, 'index']);
Route::post('/products', [ProductsController::class, 'store']);
Route::get('/products/inventory-summary', [ProductsController::class, 'inventorySummary']);
Route::get('/products/{product}', [ProductsController::class, 'show']);
Route::put('/products/{product}', [ProductsController::class, 'update']);
Route::delete('/products/{product}', [ProductsController::class, 'destroy']);

//crud de categorias
Route::get('/categories', [CategoriesController::class, 'index']);
Route::post('/categories', [CategoriesController::class, 'store']);
Route::get('/categories/{category}', [CategoriesController::class, 'show']);
Route::put('/categories/{category}', [CategoriesController::class, 'update']);
Route::delete('/categories/{category}', [CategoriesController::class, 'destroy']);

//crud de marcas
Route::get('/brands', [BrandsController::class, 'index']);
Route::post('/brands', [BrandsController::class, 'store']);
Route::get('/brands/{brand}', [BrandsController::class, 'show']);
Route::put('/brands/{brand}', [BrandsController::class, 'update']);
Route::delete('/brands/{brand}', [BrandsController::class, 'destroy']);

//crud de unidades de medida
Route::get('/units', [UnitsController::class, 'index']);
Route::post('/units', [UnitsController::class, 'store']);
Route::get('/units/{unit}', [UnitsController::class, 'show']);
Route::put('/units/{unit}', [UnitsController::class, 'update']);
Route::delete('/units/{unit}', [UnitsController::class, 'destroy']);

//crud de items de compra(rusell)
