<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVersionToQuotationRevisionsTable extends Migration
{
    public function up()
    {
        Schema::table('quotation_revisions', function (Blueprint $table) {
            $table->unsignedInteger('version')->default(1)->after('quotation_id');
        });
    }

    public function down()
    {
        Schema::table('quotation_revisions', function (Blueprint $table) {
            $table->dropColumn('version');
        });
    }
}
