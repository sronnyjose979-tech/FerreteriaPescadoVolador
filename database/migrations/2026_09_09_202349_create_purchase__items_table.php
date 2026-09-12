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
        Schema::create('purchase__item', function (Blueprint $table) {
            $table->id();

            $table->foreignId('id_product')
                ->constrained('products')
                ->cascadeOnDelete();

            $table->string('id_Purchase');

            $table->foreign('id_Purchase')
                ->references('id_Purchase')
                ->on('purchases')
                ->cascadeOnDelete();

            $table->integer('quantity');
            $table->decimal('unit_cost', 12, 2);
            $table->decimal('subtotal', 12, 2);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase__item');
    }
};
