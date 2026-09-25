<?php

namespace Database\Factories;

use App\Models\Payment;
use App\Models\Sale;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payments>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $paymentMethod = fake()->randomElement(['cash', 'card', 'sinpe']);

        return [
            'sale_id' => Sale::inRandomOrder()->value('id'),
            'payment_method' => $paymentMethod,
            'transaction_reference' => $paymentMethod !== 'cash' ? fake()->unique()->bothify('TXN-########') : null,
            'status' => fake()->randomElement(['completed', 'pending', 'failed']),
        ];
    }
}
