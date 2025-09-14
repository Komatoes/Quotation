<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('materials', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->string('name'); // e.g. "Cement"
            $table->text('description')->nullable(); // Optional description
            $table->integer('quantity')->default(0); // Stock quantity
            $table->decimal('unit_price', 10, 2); // Price per unit
            $table->string('unit'); // e.g. "kg", "pcs", "liters"
            $table->timestamps(); // created_at, updated_at
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('materials');
    }
};
