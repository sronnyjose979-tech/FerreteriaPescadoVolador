<?php

use App\Http\Controllers\ProductsController;
use App\Http\Controllers\SuppliersController;
use App\Models\Products;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/products', [ProductsController::class, 'index']);
Route::post('/products', [ProductsController::class, 'store']);
Route::get('/products/inventory-summary', [ProductsController::class, 'inventorySummary']);
Route::get('/products/{product}', [ProductsController::class, 'show']);
Route::put('/products/{product}', [ProductsController::class, 'update']);
Route::delete('/products/{product}', [ProductsController::class, 'destroy']);


Route::get('/Suppliers', [SuppliersController::class, 'index']);
Route::post('/Suppliers', [SuppliersController::class, 'store']);
Route::get('/Suppliers/{supplier}', [SuppliersController::class, 'show']);
Route::put('/Suppliers/{supplier}', [SuppliersController::class, 'update']);
Route::delete('/Suppliers/{supplier}', [SuppliersController::class, 'destroy']);