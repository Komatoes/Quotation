<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('quotation_status', function (Blueprint $table) {
            $table->id(); // Primary Key
            $table->string('status_name', 50); // e.g., Draft, Approved, Rejected
            $table->timestamps(); // created_at and updated_at
        });

        // 👇 Insert default values with fixed IDs
        DB::table('quotation_status')->insert([
            [
                'id' => 1,
                'status_name' => 'Draft',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'status_name' => 'Approved',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'status_name' => 'Rejected',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('quotation_status');
    }
};
