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
            $table->string('supplier_First_name');
            $table->string('supplier_Last_name');
            $table->string('supplier_phone');
            $table->string('supplier_address');
            $table->string('supplier_Email');
            $table->string('supplier_Type');
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
