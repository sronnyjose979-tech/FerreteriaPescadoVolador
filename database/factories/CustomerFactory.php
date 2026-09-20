<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Customers>
 */
class CustomerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_Customer' => 'CUS' . fake()->unique()->numberBetween(100, 900),
            'first_name' => fake()->randomElement(['Pedro', 'Sofia', 'Laura', 'Steven']),
            'second_name' => fake()->randomElement(['Enrique', 'Alejandra', 'Maria', 'Tomas']),
            'last_name1' => fake()->randomElement(['Guzman', 'Fernandez', 'Camacho', 'Porras']),
            'last_name2' => fake()->randomElement(['Alfaro', 'Zamora', 'Chavarria', 'Mora']),
            'email' => fake()->unique()->safeEmail(),//No me dejo colocarlos personalizado por la cantidad, aqui los hace fake
            'telephone_number' => fake()->randomElement(['15424674', '23464321', '45787567', '345654323'])
        ];
    }
}
