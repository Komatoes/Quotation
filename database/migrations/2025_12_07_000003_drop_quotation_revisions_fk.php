<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Force drop the foreign key constraint by directly executing SQL
        $connection = DB::getDriverName();
        
        if ($connection === 'mysql' || $connection === 'mariadb') {
            DB::statement('ALTER TABLE quotation_revisions DROP FOREIGN KEY quotation_revisions_quotation_id_foreign');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Re-add the foreign key
        DB::statement('ALTER TABLE quotation_revisions ADD CONSTRAINT quotation_revisions_quotation_id_foreign 
            FOREIGN KEY (quotation_id) REFERENCES quotations(id) ON DELETE CASCADE');
    }
};
