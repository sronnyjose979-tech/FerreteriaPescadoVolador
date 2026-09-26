<?php

namespace Database\Seeders;

use App\Models\Purchase;
use App\Models\PurchaseItem;
use Illuminate\Database\Seeder;

class PurchaseItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PurchaseItem::truncate();

        // Obtenemos todas las ventas que ya fueron creadas
        $purchases = Purchase::all();

        // Por cada venta, creamos entre 1 y 4 ítems de compra reales
        foreach ($purchases as $purchase) {
            $itemCount = fake()->numberBetween(1, 4);

            PurchaseItem::factory()->count($itemCount)->create([
                'id_purchase' => $purchase->id_purchase, // Amarra los ítems directamente a esta compra específica
            ]);
        }
    }
}
