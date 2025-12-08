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
        Schema::table('quotation_comment_replies', function (Blueprint $table) {
            // Add session_token to track public/unauthenticated replies
            if (!Schema::hasColumn('quotation_comment_replies', 'session_token')) {
                $table->string('session_token')->nullable()->after('user_name');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quotation_comment_replies', function (Blueprint $table) {
            $table->dropColumn('session_token');
        });
    }
};
