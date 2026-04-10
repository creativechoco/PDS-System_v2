<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('pds_id_infos', function (Blueprint $table) {
            if (Schema::hasColumn('pds_id_infos', 'id_number')) {
                $table->dropColumn('id_number');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pds_id_infos', function (Blueprint $table) {
            if (!Schema::hasColumn('pds_id_infos', 'id_number')) {
                $table->string('id_number')->nullable();
            }
        });
    }
};
