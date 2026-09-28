<?php

use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::view('/docs/api', 'api-docs')->name('docs.api');
Route::get('/docs/openapi.yaml', fn () => response()->file(base_path('docs/openapi.yaml'), [
    'Content-Type' => 'application/yaml',
]))->name('docs.openapi');
