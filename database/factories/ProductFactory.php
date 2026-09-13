<?php

namespace Database\Factories;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'category_id' => Category::inRandomOrder()->first()?->id ?? Category::factory(),
            'brand_id' => Brand::inRandomOrder()->first()?->id ?? Brand::factory(),
            'unit_id' => Unit::inRandomOrder()->first()?->id ?? Unit::factory(),

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
