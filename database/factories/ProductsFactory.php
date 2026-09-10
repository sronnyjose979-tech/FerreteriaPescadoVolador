<?php

namespace Database\Factories;

use App\Models\Brands;
use App\Models\Categories;
use App\Models\Products;
use App\Models\Units;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Products>
 */
class ProductsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
       'category_id' => Categories::inRandomOrder()->value('id') ?? Categories::factory(),
        'brand_id' => Brands::inRandomOrder()->value('id') ?? Brands::factory(),
        'unit_id' => Units::inRandomOrder()->value('id') ?? Units::factory(),

        // Datos comerciales
        'name' => fake()->words(3, true), 
        'description' => fake()->sentence(10),
        'sku' => fake()->unique()->numerify('FERR-#####'), 
        'barcode' => fake()->unique()->ean13(), 
        
        // Precios e Impuestos 
        'price' => fake()->randomFloat(2, 1000, 50000),
        'tax_rate' => 0.13, 

        // Inventario y Logística
        'stock_quantity' => fake()->numberBetween(10, 200),
        'minimum_stock' => fake()->numberBetween(5, 10),
        'maximum_stock' => fake()->numberBetween(250, 500),
        'weight' => fake()->randomFloat(2, 0.5, 50.0), 
        
        // Web
        'image_url' => fake()->imageUrl(640, 480, 'hardware', true),
        'is_active' => fake()->boolean(90),   
    
        //
        ];
    }
}
