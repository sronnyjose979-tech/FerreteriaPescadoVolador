<?php

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Support\Facades\DB;

test('el listado de ventas carga los detalles en una sola consulta sin importar cuántas ventas haya', function () {
    $admin = actingAsRole('admin');
    $product = Product::factory()->create();
    Sale::factory()
        ->count(6)
        ->has(SaleItem::factory()->count(2)->state(['product_id' => $product->id]))
        ->create(['user_id' => $admin->id]);
    DB::enableQueryLog();

    $this->getJson('/api/sales?per_page=50')->assertOk()->assertJsonCount(6, 'data');

    $consultasDeDetalle = collect(DB::getQueryLog())
        ->pluck('query')
        ->filter(fn (string $sql) => str_contains($sql, 'from "sale_items"'));
    expect($consultasDeDetalle)->toHaveCount(1);
});
