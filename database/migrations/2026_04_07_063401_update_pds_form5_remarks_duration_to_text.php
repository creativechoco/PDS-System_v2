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
        // Allow very long values for duration on form 5 remarks
        DB::statement('ALTER TABLE pds_form5_remarks MODIFY duration TEXT');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert duration back to varchar(255)
        DB::statement('ALTER TABLE pds_form5_remarks MODIFY duration VARCHAR(255)');
    }
};
