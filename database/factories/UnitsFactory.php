<?php

namespace Database\Factories;

use App\Models\Units;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Units>
 */
class UnitsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'Unit_name' => fake()->randomElement(['Cm', 'kg','ml']),
        ];
    }
}
