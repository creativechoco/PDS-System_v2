<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pds_submissions', function (Blueprint $table) {
            if (! Schema::hasColumn('pds_submissions', 'approval_dismissed_at')) {
                $table->timestamp('approval_dismissed_at')->nullable()->after('submitted');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pds_submissions', function (Blueprint $table) {
            if (Schema::hasColumn('pds_submissions', 'approval_dismissed_at')) {
                $table->dropColumn('approval_dismissed_at');
            }
        });
    }
};
