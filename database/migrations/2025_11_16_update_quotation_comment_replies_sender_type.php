<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations - Add 'staff' to sender_type enum
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE quotation_comment_replies MODIFY sender_type ENUM('admin', 'staff', 'customer') DEFAULT 'customer'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE quotation_comment_replies MODIFY sender_type ENUM('admin', 'customer') DEFAULT 'customer'");
    }
};
