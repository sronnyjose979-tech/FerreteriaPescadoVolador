<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Categories>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'category_name' => fake()->randomElement(['Hogar', 'Ligero','Fontaneria','Electricidad']),
            'description' => fake()->randomElement(['En inventario','Producto de mejor calidad']),
        ];
    }
}
