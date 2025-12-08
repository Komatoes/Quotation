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
            // Add approved_by_customer_at column if it doesn't exist
            if (!Schema::hasColumn('quotations', 'approved_by_customer_at')) {
                $table->timestamp('approved_by_customer_at')->nullable()->after('updated_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            if (Schema::hasColumn('quotations', 'approved_by_customer_at')) {
                $table->dropColumn('approved_by_customer_at');
            }
        });
    }
};
