<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('password_resets', function (Blueprint $table) {
            // Add OTP columns if they don't exist
            if (!Schema::hasColumn('password_resets', 'otp')) {
                $table->string('otp')->nullable();
            }
            if (!Schema::hasColumn('password_resets', 'otp_verified')) {
                $table->boolean('otp_verified')->default(false);
            }
            if (!Schema::hasColumn('password_resets', 'otp_expires_at')) {
                $table->timestamp('otp_expires_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('password_resets', function (Blueprint $table) {
            if (Schema::hasColumn('password_resets', 'otp')) {
                $table->dropColumn('otp');
            }
            if (Schema::hasColumn('password_resets', 'otp_verified')) {
                $table->dropColumn('otp_verified');
            }
            if (Schema::hasColumn('password_resets', 'otp_expires_at')) {
                $table->dropColumn('otp_expires_at');
            }
        });
    }
};
