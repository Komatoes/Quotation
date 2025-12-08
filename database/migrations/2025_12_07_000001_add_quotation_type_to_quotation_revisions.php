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
        Schema::table('quotation_revisions', function (Blueprint $table) {
            // Add quotation_type to support both quotation and additional_quotation revisions
            if (!Schema::hasColumn('quotation_revisions', 'quotation_type')) {
                $table->string('quotation_type')->default('quotation')->after('quotation_id');
            }

            // Drop the existing foreign key constraint since we need to make quotation_id flexible
            if (Schema::hasTable('quotation_revisions')) {
                try {
                    $table->dropForeignKeyIfExists(['quotation_id']);
                } catch (\Throwable $e) {
                    // Ignore if key doesn't exist
                }
            }

            // Re-add foreign key with conditional logic (handled in app, not DB)
            // This allows quotation_id to reference either quotations or additional_quotations
            // based on the quotation_type value
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quotation_revisions', function (Blueprint $table) {
            $table->dropColumn('quotation_type');
            // Re-add the original FK
            $table->foreign('quotation_id')->references('id')->on('quotations')->onDelete('cascade');
        });
    }
};
