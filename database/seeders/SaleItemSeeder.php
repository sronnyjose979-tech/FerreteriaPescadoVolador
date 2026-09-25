<?php

namespace Database\Seeders;

use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SaleItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
    SaleItem::truncate();

        // Obtenemos todas las ventas que ya fueron creadas
        $sales = Sale::all();

        // Por cada venta, creamos entre 1 y 4 ítems de compra reales
        foreach ($sales as $sale) {
            $itemCount = fake()->numberBetween(1, 4);

            SaleItem::factory()->count($itemCount)->create([
                'sale_id' => $sale->id, // Amarra los ítems directamente a esta venta específica
            ]);
        }
    }
}
