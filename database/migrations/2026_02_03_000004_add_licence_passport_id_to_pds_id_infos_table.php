<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('pds_id_infos', function (Blueprint $table) {
            if (!Schema::hasColumn('pds_id_infos', 'licence_passport_id')) {
                $table->string('licence_passport_id')->nullable()->after('gov_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pds_id_infos', function (Blueprint $table) {
            if (Schema::hasColumn('pds_id_infos', 'licence_passport_id')) {
                $table->dropColumn('licence_passport_id');
            }
        });
    }
};
