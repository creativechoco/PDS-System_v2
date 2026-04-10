<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'pgsql') {
            // Drop existing constraint if exists
            DB::statement("
                DO $$
                BEGIN
                    IF EXISTS (
                        SELECT 1 FROM pg_constraint
                        WHERE conname = 'users_type_check'
                    ) THEN
                        ALTER TABLE users DROP CONSTRAINT users_type_check;
                    END IF;
                END
                $$;
            ");

            // Ensure column is varchar
            DB::statement('ALTER TABLE users ALTER COLUMN type TYPE varchar(255)');

            // Add new constraint
            DB::statement("
                ALTER TABLE users
                ADD CONSTRAINT users_type_check
                CHECK (type IN ('Permanent Employee', 'Contract of Service', 'Job Order'))
            ");
        } else {
            // MySQL / MariaDB
            Schema::table('users', function (Blueprint $table) {
                $table->enum('type', [
                    'Permanent Employee',
                    'Contract of Service',
                    'Job Order'
                ])->change();
            });
        }
    }

    public function down(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'pgsql') {
            // Drop constraint
            DB::statement("
                DO $$
                BEGIN
                    IF EXISTS (
                        SELECT 1 FROM pg_constraint
                        WHERE conname = 'users_type_check'
                    ) THEN
                        ALTER TABLE users DROP CONSTRAINT users_type_check;
                    END IF;
                END
                $$;
            ");

            // Restore old constraint
            DB::statement("
                ALTER TABLE users
                ADD CONSTRAINT users_type_check
                CHECK (type IN ('Permanent Employee', 'Contract of Service'))
            ");
        } else {
            Schema::table('users', function (Blueprint $table) {
                $table->enum('type', [
                    'Permanent Employee',
                    'Contract of Service'
                ])->change();
            });
        }
    }
};