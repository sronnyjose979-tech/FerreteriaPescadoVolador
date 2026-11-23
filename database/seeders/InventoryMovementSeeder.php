<?php

namespace Database\Seeders;

use App\Models\InventoryMovement;
use App\Models\PurchaseItem;
use App\Models\SaleItem;
use App\Models\User;
use Illuminate\Database\Seeder;

class InventoryMovementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        InventoryMovement::truncate();

        // 1. Crear movimientos de entrada solo para compras ya recibidas
        $purchaseItems = PurchaseItem::query()
            ->whereHas('purchase', fn ($query) => $query->whereIn('purchase_status', ['confirmada', 'recibida']))
            ->get();

        foreach ($purchaseItems as $purchaseItem) {
            $purchaseItem->forceFill(['received_at' => $purchaseItem->received_at ?? now()])->save();

            InventoryMovement::create([
                'product_id' => $purchaseItem->product_id,
                'user_id' => User::inRandomOrder()->value('id'),
                'movementable_type' => PurchaseItem::class,
                'movementable_id' => $purchaseItem->id,
                'type' => 'entrada',
                'quantity' => $purchaseItem->quantity,
                'stock_after' => fake()->numberBetween(50, 200),
            ]);
        }

        // 2. Crear un movimiento de venta por cada SaleItem
        $saleItems = SaleItem::all();

        foreach ($saleItems as $saleItem) {
            InventoryMovement::create([
                'product_id' => $saleItem->product_id,
                'user_id' => User::inRandomOrder()->value('id'),
                'movementable_type' => SaleItem::class,
                'movementable_id' => $saleItem->id,
                'type' => 'salida',
                'quantity' => $saleItem->quantity,
                'stock_after' => fake()->numberBetween(10, 100),
            ]);
        }
    }
}
