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
            $table->id(); // Primary Key
            $table->string('name'); // Material name
            $table->text('description')->nullable(); // Material description
            $table->integer('quantity')->default(0); // Quantity on hand
            $table->decimal('unit_price', 10, 2); // Cost per unit
            $table->foreignId('unit_id')->constrained('units'); // Foreign key to units table
            $table->timestamps(); // created_at and updated_at
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
