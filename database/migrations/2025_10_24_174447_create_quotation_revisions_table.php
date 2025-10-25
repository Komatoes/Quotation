<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotation_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quotation_id')->constrained('quotations')->onDelete('cascade');
            $table->json('old_data'); // JSON field to store all old data
            $table->string('reason')->nullable(); // Reason for the revision
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotation_revisions');
    }
};
