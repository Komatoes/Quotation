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
        Schema::table('quotations', function (Blueprint $table) {
            // Add rejection handling columns
            $table->text('rejection_reason')->nullable()->comment('Reason for quotation rejection');
            $table->timestamp('rejected_at')->nullable()->comment('When quotation was rejected');
            $table->unsignedBigInteger('rejected_by')->nullable()->comment('User who rejected the quotation');
            
            // Add parent quotation reference for linked quotations
            $table->unsignedBigInteger('parent_quotation_id')->nullable()->comment('Parent quotation if this is a linked/add-on quotation');
            $table->string('quotation_type')->default('standalone')->comment('standalone or addon');
            
            // Foreign keys
            $table->foreign('rejected_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('parent_quotation_id')->references('id')->on('quotations')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            // Drop foreign keys
            $table->dropForeign(['rejected_by']);
            $table->dropForeign(['parent_quotation_id']);
            
            // Drop columns
            $table->dropColumn(['rejection_reason', 'rejected_at', 'rejected_by', 'parent_quotation_id', 'quotation_type']);
        });
    }
};
