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
        Schema::create('additional_quotations', function (Blueprint $table) {
            $table->id();
            
            // Foreign key to parent quotation
            $table->unsignedBigInteger('parent_quotation_id');
            $table->foreign('parent_quotation_id')
                ->references('id')
                ->on('quotations')
                ->onDelete('cascade');
            
            // Content fields (unique to this additional quotation)
            $table->string('subject');
            $table->longText('description')->nullable();
            
            // Status tracking
            $table->unsignedBigInteger('status_id')->default(1); // Default to Draft
            $table->foreign('status_id')
                ->references('id')
                ->on('quotation_status')
                ->onDelete('restrict');
            
            // Progress and approval tracking
            $table->integer('progress')->default(0); // 0-100
            $table->boolean('customer_approved')->default(false);
            $table->string('rejection_reason')->nullable();
            
            // Fees for this additional quotation
            $table->decimal('labor_fee', 10, 2)->default(0);
            $table->decimal('delivery_fee', 10, 2)->default(0);
            
            // Public token for sharing
            $table->string('public_token')->nullable()->unique();
            
            // Timestamps
            $table->timestamps();
            
            // Indexes for performance
            $table->index('parent_quotation_id');
            $table->index('status_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('additional_quotations');
    }
};
