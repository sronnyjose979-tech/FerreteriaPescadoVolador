<?php

namespace Database\Factories;

use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Supplier>
 */
class SupplierFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_Supplier' => 'SUP' . fake()->unique()->numberBetween(100, 999),
            'Supplier_First_name' => fake()->randomElement(['Juan', 'Alonso', 'Pedro']),
            'Supplier_Last_name' => fake()->randomElement(['Madrigal', 'Guzman', 'Rodriguez']),
            'Supplier_Phone' => fake()->randomElement(['1234567890', '0987654321', '1122334455']),
            'Supplier_Address' => fake()->randomElement(['Calle 1', 'Avenida 2', 'Avenida 15 de julio']),
            'Supplier_Email' => fake()->randomElement(['juan@gmail.com', 'alonso@gmail.com', 'pedro@gmail.com']),
            'Supplier_Type' => fake()->randomElement(['Proveedor de herramientas', 'Proveedor de materiales', 'Proveedor de productos de limpieza']),
        ];
    }
}
