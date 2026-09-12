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
            $table->string('id_Purchase')->primary();

            //$table->string('id_user');
            $table->foreignId('id_user')->constrained('users', 'id');//Con esto trabajo solo con el unico usuario
            $table->string('id_Supplier');

            $table->foreign('id_Supplier')->references('id_Supplier')->on('suppliers');

            $table->decimal('Purchase_Total', 12, 2);

            $table->string('Purchase_status', 20);

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
