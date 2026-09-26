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
            'id_supplier' => 'SUP'.fake()->unique()->numberBetween(100, 999),
            'supplier_first_name' => fake()->randomElement(['Juan', 'Alonso', 'Pedro']),
            'supplier_last_name' => fake()->randomElement(['Madrigal', 'Guzman', 'Rodriguez']),
            'supplier_phone' => fake()->randomElement(['1234567890', '0987654321', '1122334455']),
            'supplier_address' => fake()->randomElement(['Calle 1', 'Avenida 2', 'Avenida 15 de julio']),
            'supplier_email' => fake()->unique()->companyEmail(),
            'supplier_type' => fake()->randomElement(['Proveedor de herramientas', 'Proveedor de materiales', 'Proveedor de productos de limpieza']),
        ];
    }
}
