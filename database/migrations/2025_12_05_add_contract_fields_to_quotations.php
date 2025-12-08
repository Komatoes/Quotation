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
        Schema::table('quotations', function (Blueprint $table) {
            $table->string('contract_subject')->nullable()->after('rejection_reason');
            $table->date('project_start_date')->nullable()->after('contract_subject');
            $table->date('project_end_date')->nullable()->after('project_start_date');
            $table->boolean('with_contract')->default(false)->after('project_end_date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->dropColumn(['contract_subject', 'project_start_date', 'project_end_date', 'with_contract']);
        });
    }
};
