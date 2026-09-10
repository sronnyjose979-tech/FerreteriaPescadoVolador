<?php

namespace Database\Factories;

use App\Models\Categories;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Categories>
 */
class CategoriesFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'Category_name' => fake()->randomElement(['Hogar', 'Ligero','Fontaneria','Electricidad']),
            'Description' => fake()->randomElement(['En inventario','Producto de mejor calidad']),
        ];
    }
}
