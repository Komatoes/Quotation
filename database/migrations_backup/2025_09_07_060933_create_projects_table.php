<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id(); // Primary Key
            $table->foreignId('quotation_id')->constrained('quotations'); // Link to quotation
            $table->foreignId('status_id')->constrained('project_status'); // Project status
            $table->date('start_date'); // Project start
            $table->date('end_date')->nullable(); // Project end
            $table->timestamps(); // created_at and updated_at
        });
    }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('projects');
    }
};
