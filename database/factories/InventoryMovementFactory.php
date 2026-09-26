<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\PurchaseItem;
use App\Models\SaleItem;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Inventory_Movements>
 */
class InventoryMovementFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $isSale = fake()->boolean();

        return [
            'product_id' => Product::inRandomOrder()->value('id'),
            'user_id' => User::inRandomOrder()->value('id'),
            'movementable_type' => $isSale ? SaleItem::class : PurchaseItem::class,
            'movementable_id' => $isSale
                ? SaleItem::inRandomOrder()->value('id')
                : PurchaseItem::inRandomOrder()->value('id'),
            'type' => $isSale ? 'salida' : 'entrada',
            'quantity' => fake()->numberBetween(1, 100),
            'stock_after' => fake()->numberBetween(0, 500),
        ];
    }
}
