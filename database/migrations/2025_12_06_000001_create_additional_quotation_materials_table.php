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
        Schema::create('additional_quotation_materials', function (Blueprint $table) {
            $table->id();
            
            // Foreign key to additional quotation
            $table->unsignedBigInteger('additional_quotation_id');
            $table->foreign('additional_quotation_id')
                ->references('id')
                ->on('additional_quotations')
                ->onDelete('cascade');
            
            // Foreign key to material
            $table->unsignedBigInteger('material_id');
            $table->foreign('material_id')
                ->references('id')
                ->on('materials')
                ->onDelete('cascade');
            
            // Material details
            $table->integer('quantity')->default(0);
            $table->decimal('unit_cost', 15, 2)->default(0);
            
            // Timestamps
            $table->timestamps();
            
            // Indexes for performance
            $table->index('additional_quotation_id');
            $table->index('material_id');
            
            // Unique constraint - can't add same material twice to same additional quotation
            // Use explicit short name to avoid MySQL 64-char identifier limit
            $table->unique(['additional_quotation_id', 'material_id'], 'add_qtn_mat_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('additional_quotation_materials');
    }
};
