<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('quotation_revisions', function (Blueprint $table) {
            // Add created_by if it doesn't exist
            if (!Schema::hasColumn('quotation_revisions', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable()->after('id');
                $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            }
        });

        // Add change_reason column if it doesn't exist (keep reason as is)
        if (!Schema::hasColumn('quotation_revisions', 'change_reason')) {
            Schema::table('quotation_revisions', function (Blueprint $table) {
                $table->text('change_reason')->nullable()->after('old_data');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quotation_revisions', function (Blueprint $table) {
            if (Schema::hasColumn('quotation_revisions', 'created_by')) {
                $table->dropForeignKeyIfExists(['created_by']);
                $table->dropColumn('created_by');
            }
            if (Schema::hasColumn('quotation_revisions', 'change_reason')) {
                $table->dropColumn('change_reason');
            }
        });
    }
};
