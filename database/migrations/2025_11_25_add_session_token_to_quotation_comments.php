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
            // Add session_token to track public/unauthenticated commenters
            if (!Schema::hasColumn('quotation_comments', 'session_token')) {
                $table->string('session_token')->nullable()->after('user_name');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quotation_comments', function (Blueprint $table) {
            $table->dropColumn('session_token');
        });
    }
};
