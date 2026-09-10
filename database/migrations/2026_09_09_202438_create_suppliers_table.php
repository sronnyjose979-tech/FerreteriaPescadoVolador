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
        Schema::create('Suppliers', function (Blueprint $table) {
            $table->id('id_Supplier');
            $table->string('Supplier_First_name');
            $table->string('Supplier_Last_name');
            $table->string('Supplier_phone');
            $table->string('Supplier_address');
            $table->string('Supplier_Email');
            $table->string('Supplier_Type');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('Suppliers');
    }
};
