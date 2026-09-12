<?php

namespace Database\Factories;

use App\Models\Purchase;
use App\Models\Supplier;
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
            'id_Purchase' => 'PUR' . fake()->unique()->numberBetween(100, 999),
            // 'id_user' => 'USR' . fake()->numberBetween(100, 999),
            'id_user' => 1,//con esto se tarbaja con el unico usuario
            'id_Supplier' => Supplier::inRandomOrder()->value('id_Supplier'),
            'Purchase_Total' => fake()->randomFloat(2, 10, 1000),
            'Purchase_status' => fake()->randomElement([
                'Pendiente',
                'Completada',
                'Cancelada'
            ]),


        ];
    }
}
