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
            // Add user_id and user_name if they don't exist
            if (!Schema::hasColumn('quotation_comments', 'user_id')) {
                $table->foreignId('user_id')->nullable()->after('quotation_id')->constrained('users')->onDelete('set null');
            }
            if (!Schema::hasColumn('quotation_comments', 'user_name')) {
                $table->string('user_name')->nullable()->after('user_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quotation_comments', function (Blueprint $table) {
            $table->dropForeignKeyIfExists(['user_id']);
            $table->dropColumn(['user_id', 'user_name']);
        });
    }
};
