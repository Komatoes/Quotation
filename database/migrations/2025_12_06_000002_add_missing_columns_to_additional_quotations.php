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
        Schema::table('additional_quotations', function (Blueprint $table) {
            // Add missing columns to match quotations table structure
            if (!Schema::hasColumn('additional_quotations', 'status_id')) {
                $table->unsignedBigInteger('status_id')->default(1)->after('description');
                $table->foreign('status_id')
                    ->references('id')
                    ->on('quotation_status')
                    ->onDelete('restrict');
            }

            if (!Schema::hasColumn('additional_quotations', 'labor_fee')) {
                $table->decimal('labor_fee', 10, 2)->default(0)->after('progress');
            }

            if (!Schema::hasColumn('additional_quotations', 'delivery_fee')) {
                $table->decimal('delivery_fee', 10, 2)->default(0)->after('labor_fee');
            }

            if (!Schema::hasColumn('additional_quotations', 'customer_approved')) {
                $table->boolean('customer_approved')->default(false)->after('delivery_fee');
            }

            if (!Schema::hasColumn('additional_quotations', 'rejection_reason')) {
                $table->string('rejection_reason')->nullable()->after('customer_approved');
            }

            if (!Schema::hasColumn('additional_quotations', 'public_token')) {
                $table->string('public_token')->nullable()->unique()->after('rejection_reason');
            }

            // Add indexes if they don't exist
            if (!Schema::hasColumn('additional_quotations', 'status_id')) {
                $table->index('status_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('additional_quotations', function (Blueprint $table) {
            $table->dropForeignKeyIfExists(['status_id']);
            $table->dropIndex('additional_quotations_status_id_index');
            $table->dropColumn(['status_id', 'labor_fee', 'delivery_fee', 'customer_approved', 'rejection_reason', 'public_token']);
        });
    }
};
