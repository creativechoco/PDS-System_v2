<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Add archiving columns if they don't exist
            if (!Schema::hasColumn('users', 'is_archive')) {
                $table->boolean('is_archive')->default(false)->after('status');
            }
            if (!Schema::hasColumn('users', 'archived_at')) {
                $table->timestamp('archived_at')->nullable()->after('is_archive');
            }
            if (!Schema::hasColumn('users', 'archived_by')) {
                $table->string('archived_by')->nullable()->after('archived_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['is_archive', 'archived_at', 'archived_by']);
        });
    }
};
