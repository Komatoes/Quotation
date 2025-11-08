<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQuotationRevisionsTable extends Migration
{
    public function up()
    {
        Schema::create('quotation_revisions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('quotation_id');
            $table->json('old_data');
            $table->string('reason')->nullable();
            $table->timestamps();

            $table->foreign('quotation_id')->references('id')->on('quotations')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('quotation_revisions');
    }
}
