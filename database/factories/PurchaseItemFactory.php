<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Purchase_Items>
 */
class PurchaseItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $unitCost = fake()->randomFloat(2, 10, 500);
        $quantity = fake()->numberBetween(1, 10);

        return [
            'id_Purchase' => Purchase::inRandomOrder()->value('id_Purchase'),

            //'id_product' => Product::inRandomOrder()->value('id_Product'), estos es para productos de id no autoincremental
            'id_product' => Product::inRandomOrder()->value('id'),

            'quantity' => $quantity,

            'unit_cost' => $unitCost,

            'subtotal' => $quantity * $unitCost,
        ];
    }
}
