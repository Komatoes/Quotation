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
        Schema::create('quotation_materials', function (Blueprint $table) {
            $table->id(); // Primary Key
            $table->foreignId('quotation_id')->constrained('quotations'); // Links to quotations
            $table->foreignId('material_id')->constrained('materials'); // Links to materials
            $table->decimal('unit_cost', 10, 2); // Unit cost at time of quotation
            $table->integer('quantity'); // Quantity used
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
        Schema::dropIfExists('quotation_materials');
    }
};
