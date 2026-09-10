<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            // Llaves foráneas (Catálogos)
            $table->foreignId('category_id')->constrained('categories')->onDelete('restrict');
            $table->foreignId('brand_id')->constrained('brands')->onDelete('restrict');
            $table->foreignId('unit_id')->constrained('units')->onDelete('restrict');

            // Datos comerciales
            $table->string('name', 200);
            $table->text('description')->nullable();
            $table->string('sku', 50)->unique();
            $table->string('barcode', 100)->unique()->nullable();

            // Precios e Impuestos
            $table->decimal('price', 12, 2);
            $table->decimal('tax_rate', 5, 2)->default(0.13); 

            // Control de Inventario y Logística
            $table->integer('stock_quantity')->default(0);
            $table->integer('minimum_stock')->default(1);
            $table->integer('maximum_stock')->nullable();
            $table->decimal('weight', 8, 2)->nullable(); 

            // Visualización Web
            $table->string('image_url')->nullable();
            $table->boolean('is_active')->default(true);

            // Auditoría de Laravel
            $table->timestamps(); 
            $table->softDeletes(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
