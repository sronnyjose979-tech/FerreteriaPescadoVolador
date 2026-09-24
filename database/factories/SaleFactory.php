<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Sales>
 */
class SaleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        //El ? es el if()
        return [
            'user_id' => User::inRandomOrder()->value('id'), //id de usuarios
            'id_Customer' => fake()->boolean() ? Customer::inRandomOrder()->value('id_Customer') : null,
            //'order_id' => fake()->boolean() ? Order::inRandomOrder()->value('id') : null,
            'sale_date' => fake()->dateTimeBetween('-1 año', 'ahora'),
            'total' => fake()->randomFloat(2, 1000, 150000),
            'tax_amount' => fake()->randomFloat(2, 100, 20000),
            'discount' => fake()->randomFloat(2, 0, 5000),
            'status' => fake()->randomElement(['completado', 'pendiente', 'cancelado']),
        ];
    }
}
