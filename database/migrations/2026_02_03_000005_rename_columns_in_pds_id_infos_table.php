<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('pds_id_infos', function (Blueprint $table) {
            if (Schema::hasColumn('pds_id_infos', 'licence_passport_id')) {
                $table->renameColumn('licence_passport_id', 'passport_licence_id');
            }
            if (Schema::hasColumn('pds_id_infos', 'id_issue_date_place')) {
                $table->renameColumn('id_issue_date_place', 'date_place_issuance');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pds_id_infos', function (Blueprint $table) {
            if (Schema::hasColumn('pds_id_infos', 'passport_licence_id')) {
                $table->renameColumn('passport_licence_id', 'licence_passport_id');
            }
            if (Schema::hasColumn('pds_id_infos', 'date_place_issuance')) {
                $table->renameColumn('date_place_issuance', 'id_issue_date_place');
            }
        });
    }
};
