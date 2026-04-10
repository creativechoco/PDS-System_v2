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
        // Widen all string columns on pds_form5_remarks that can hold long text
        DB::statement('ALTER TABLE pds_form5_remarks
            MODIFY duration TEXT,
            MODIFY position_title TEXT,
            MODIFY office_unit TEXT,
            MODIFY immediate_supervisor TEXT,
            MODIFY agency_location TEXT,
            MODIFY signature_path TEXT');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to varchar(255) for the original string columns
        DB::statement('ALTER TABLE pds_form5_remarks
            MODIFY duration VARCHAR(255),
            MODIFY position_title VARCHAR(255),
            MODIFY office_unit VARCHAR(255),
            MODIFY immediate_supervisor VARCHAR(255),
            MODIFY agency_location VARCHAR(255),
            MODIFY signature_path VARCHAR(255)');
    }
};
