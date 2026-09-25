<?php

namespace Database\Factories;

use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\PurchaseItem;
use App\Models\SaleItem;
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
        $movementable = fake()->randomElement([
            SaleItem::class,
            PurchaseItem::class,
        ]);

        return [
            'product_id' => Product::inRandomOrder()->value('id'),
            'movementable_type' => $movementable,
            'movementable_id' => $movementable::factory(),
            'type' => fake()->randomElement(['entrada', 'salida', 'ajuste']),
            'quantity' => fake()->numberBetween(1, 100),
            'stock_after' => fake()->numberBetween(0, 500),
        ];
    }
}
