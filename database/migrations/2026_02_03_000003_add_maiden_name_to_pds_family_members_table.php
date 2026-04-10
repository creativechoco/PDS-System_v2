<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('pds_family_members', function (Blueprint $table) {
            if (!Schema::hasColumn('pds_family_members', 'maiden_name')) {
                $table->string('maiden_name')->nullable()->after('type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pds_family_members', function (Blueprint $table) {
            if (Schema::hasColumn('pds_family_members', 'maiden_name')) {
                $table->dropColumn('maiden_name');
            }
        });
    }
};
