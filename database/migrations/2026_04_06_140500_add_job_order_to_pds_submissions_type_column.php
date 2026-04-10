<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'pgsql') {
            // Drop existing check constraint if it exists
            DB::statement("
                DO $$
                BEGIN
                    IF EXISTS (
                        SELECT 1 FROM pg_constraint
                        WHERE conname = 'pds_submissions_type_check'
                    ) THEN
                        ALTER TABLE pds_submissions DROP CONSTRAINT pds_submissions_type_check;
                    END IF;
                END
                $$;
            ");

            // Alter column type safely
            DB::statement('ALTER TABLE pds_submissions ALTER COLUMN type TYPE varchar(255)');
            DB::statement('ALTER TABLE pds_submissions ALTER COLUMN type SET NOT NULL');

            // Add new check constraint
            DB::statement("
                ALTER TABLE pds_submissions
                ADD CONSTRAINT pds_submissions_type_check
                CHECK (type IN ('Permanent Employee', 'Contract of Service', 'Job Order'))
            ");
        } else {
            // MySQL / MariaDB: use enum change
            Schema::table('pds_submissions', function (Blueprint $table) {
                $table->enum('type', ['Permanent Employee', 'Contract of Service', 'Job Order'])->change();
            });
        }
    }

    public function down(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'pgsql') {
            // Drop the check constraint
            DB::statement("
                DO $$
                BEGIN
                    IF EXISTS (
                        SELECT 1 FROM pg_constraint
                        WHERE conname = 'pds_submissions_type_check'
                    ) THEN
                        ALTER TABLE pds_submissions DROP CONSTRAINT pds_submissions_type_check;
                    END IF;
                END
                $$;
            ");

            // Revert column type
            DB::statement('ALTER TABLE pds_submissions ALTER COLUMN type TYPE varchar(255)');
            DB::statement('ALTER TABLE pds_submissions ALTER COLUMN type DROP NOT NULL');
        } else {
            // MySQL: revert enum to previous values
            Schema::table('pds_submissions', function (Blueprint $table) {
                $table->enum('type', ['Permanent Employee', 'Contract of Service'])->change();
            });
        }
    }
};