<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Sale_Items>
 */
class SaleItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        //Estas son las variables que necesito para que funcione el factory y hacer el fake necesario
        $quantity = fake()->numberBetween(1, 10);
        $unitPrice = fake()->randomFloat(2, 500, 100000);

        return [
            'sale_id' => Sale::inRandomOrder()->value('id'), //id de usuarios
            'product_id' => Product::inRandomOrder()->value('id'), //traigo el id de un producto
            'quantity' =>  $quantity,
            'unit_price' => $unitPrice,
            'subtotal' => $quantity * $unitPrice,

        ];
    }
}
