<?php

namespace Database\Factories;

use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Purchases>
 */
class PurchaseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_purchase' => 'PUR'.fake()->unique()->numberBetween(100, 999),
            'user_id' => User::inRandomOrder()->value('id'),
            'id_supplier' => Supplier::inRandomOrder()->value('id_supplier'),
            'purchase_total' => fake()->randomFloat(2, 10, 1000),
            'purchase_status' => fake()->randomElement([
                'Pendiente',
                'Completada',
                'Cancelada',
            ]),

        ];
    }
}
