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
        Schema::create('system_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('action')->comment('e.g., created, updated, deleted, approved, rejected');
            $table->text('description')->nullable()->comment('Detailed description of the action');
            $table->string('model')->nullable()->comment('Related model class name');
            $table->unsignedBigInteger('model_id')->nullable()->comment('Related model ID');
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->json('changes')->nullable()->comment('Store before/after changes as JSON');
            $table->timestamps();

            // Indexes for better query performance
            $table->index('user_id');
            $table->index('action');
            $table->index('model');
            $table->index('created_at');
            $table->index(['model', 'model_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_logs');
    }
};
