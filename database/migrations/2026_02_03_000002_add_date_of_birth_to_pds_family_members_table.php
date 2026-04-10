<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('pds_family_members', function (Blueprint $table) {
            if (!Schema::hasColumn('pds_family_members', 'date_of_birth')) {
                $table->string('date_of_birth')->nullable()->after('telephone_no');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pds_family_members', function (Blueprint $table) {
            if (Schema::hasColumn('pds_family_members', 'date_of_birth')) {
                $table->dropColumn('date_of_birth');
            }
        });
    }
};
