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
        Schema::table('quotation_comments', function (Blueprint $table) {
            // Add quotation_type to discriminate between quotation and additional_quotation comments
            if (!Schema::hasColumn('quotation_comments', 'quotation_type')) {
                $table->string('quotation_type')->default('quotation')->after('quotation_id');
                $table->index(['quotation_id', 'quotation_type']);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quotation_comments', function (Blueprint $table) {
            // Drop index first
            $table->dropIndex('quotation_comments_quotation_id_quotation_type_index');
            // Drop column
            $table->dropColumn('quotation_type');
        });
    }
};
