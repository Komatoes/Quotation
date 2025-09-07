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
        Schema::create('quotations', function (Blueprint $table) {
            $table->id(); // Primary Key
            $table->string('subject'); // Quotation title or subject
            $table->text('description'); // Quotation description
            $table->foreignId('employee_id')->constrained('service_providers'); // Who created it
            $table->foreignId('client_id')->constrained('clients'); // Client
            $table->foreignId('status_id')->constrained('quotation_status'); // Quotation status
            $table->decimal('labor_fee', 10, 2)->default(0); // Labor cost
            $table->decimal('delivery_fee', 10, 2)->default(0); // Delivery cost
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
        Schema::dropIfExists('quotations');
    }
};
