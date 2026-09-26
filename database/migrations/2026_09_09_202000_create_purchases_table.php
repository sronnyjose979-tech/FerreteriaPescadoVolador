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
        Schema::create('purchases', function (Blueprint $table) {
            $table->string('id_purchase')->primary();

            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Con esto trabajo solo con el unico usuario
            $table->string('id_supplier');

            $table->foreign('id_supplier')->references('id_supplier')->on('suppliers');

            $table->decimal('purchase_total', 12, 2);

            $table->string('purchase_status', 20);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};
