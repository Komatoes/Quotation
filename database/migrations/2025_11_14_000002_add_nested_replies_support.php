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
            // Add column to support nested replies (reply to a reply)
            $table->foreignId('parent_reply_id')->nullable()->after('quotation_comment_id')->constrained('quotation_comment_replies')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quotation_comment_replies', function (Blueprint $table) {
            $table->dropForeignKeyIfExists(['parent_reply_id']);
            $table->dropColumn('parent_reply_id');
        });
    }
};
