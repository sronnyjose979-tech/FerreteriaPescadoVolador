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
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // El cajero
            $table->string('id_Customer')->nullable();
            $table->foreign('id_Customer')->references('id_Customer')->on('customers')->nullOnDelete(); 
            //$table->foreignId('order_id')->nullable()->constrained(); // Si viene de la web
            $table->dateTime('sale_date');
            $table->decimal('total', 12, 2);
            $table->decimal('tax_amount', 12, 2)->default(0); 
            $table->decimal('discount', 12, 2)->default(0);   
            $table->string('status', 20)->default('completed');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
 //$table->string('id_Customer')->primary();
