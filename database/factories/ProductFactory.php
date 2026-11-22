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
    public function definition(): array
    {
        $producto = fake()->randomElement([
            [
                'name' => 'Martillo de uña de 16 oz',
                'description' => 'Martillo con cabeza de acero y mango antideslizante para clavar y extraer clavos.',
                'price' => 6500,
                'weight' => 0.75,
            ],
            [
                'name' => 'Destornillador Phillips número 2',
                'description' => 'Destornillador de punta Phillips con mango ergonómico para trabajos de instalación y mantenimiento.',
                'price' => 2500,
                'weight' => 0.15,
            ],
            [
                'name' => 'Cinta métrica de 5 metros',
                'description' => 'Cinta métrica retráctil con seguro de bloqueo y medidas en centímetros y pulgadas.',
                'price' => 3500,
                'weight' => 0.25,
            ],
            [
                'name' => 'Taladro percutor de 650 W',
                'description' => 'Taladro eléctrico con función de percusión para perforar madera, metal y mampostería.',
                'price' => 42500,
                'weight' => 2.10,
            ],
            [
                'name' => 'Llave ajustable de 10 pulgadas',
                'description' => 'Llave de acero con apertura ajustable para apretar y aflojar tuercas de diferentes medidas.',
                'price' => 5800,
                'weight' => 0.45,
            ],
            [
                'name' => 'Alicate universal de 8 pulgadas',
                'description' => 'Alicate con mangos recubiertos para sujetar piezas, doblar alambre y realizar cortes.',
                'price' => 4900,
                'weight' => 0.30,
            ],
            [
                'name' => 'Tubo PVC de media pulgada y 6 metros',
                'description' => 'Tubo de PVC para instalaciones de agua fría, con diámetro nominal de media pulgada.',
                'price' => 2800,
                'weight' => 1.20,
            ],
            [
                'name' => 'Codo PVC de media pulgada a 90 grados',
                'description' => 'Accesorio de PVC para realizar cambios de dirección en instalaciones de agua fría.',
                'price' => 350,
                'weight' => 0.03,
            ],
            [
                'name' => 'Cemento gris de 50 kg',
                'description' => 'Saco de cemento gris para preparar mezclas de concreto y mortero en trabajos de construcción.',
                'price' => 7200,
                'weight' => 50,
            ],
            [
                'name' => 'Pintura látex blanca de un galón',
                'description' => 'Pintura blanca de acabado mate para paredes interiores de concreto y mampostería.',
                'price' => 12900,
                'weight' => 5.20,
            ],
            [
                'name' => 'Brocha de 3 pulgadas',
                'description' => 'Brocha de uso general para aplicar pintura en paredes, puertas y superficies de madera.',
                'price' => 1800,
                'weight' => 0.12,
            ],
            [
                'name' => 'Caja de 100 tornillos para madera de 2 pulgadas',
                'description' => 'Caja de tornillos con cabeza Phillips para ensamblar y fijar piezas de madera.',
                'price' => 3200,
                'weight' => 0.50,
            ],
        ]);

        return [
            'category_id' => Category::inRandomOrder()->value('id')
                ?? Category::factory(),

            'brand_id' => Brand::inRandomOrder()->value('id')
                ?? Brand::factory(),

            'unit_id' => Unit::inRandomOrder()->value('id')
                ?? Unit::factory(),

            // Datos comerciales del producto seleccionado
            'name' => $producto['name'],
            'description' => $producto['description'],
            'price' => $producto['price'],
            'weight' => $producto['weight'],

            // Identificadores diferentes para cada registro
            'sku' => fake()->unique()->numerify('FERR-########'),
            'barcode' => fake()->unique()->ean13(),

            // Impuesto del 13 %, expresado como proporción
            'tax_rate' => 0.13,

            // Existencias de ejemplo
            'stock_quantity' => fake()->numberBetween(10, 200),
            'minimum_stock' => fake()->numberBetween(5, 10),
            'maximum_stock' => fake()->numberBetween(250, 500),

            // Imagen de ejemplo
            'image_url' => fake()->imageUrl(640, 480, 'hardware', true),
            'is_active' => true,
        ];
    }
}