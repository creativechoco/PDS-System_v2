<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pds_declarations', function (Blueprint $table) {
            $table->string('q34_a')->nullable();
            $table->string('q34_a_details')->nullable();
            $table->string('q34_b')->nullable();
            $table->string('q34_b_details')->nullable();
            $table->string('q35_a')->nullable();
            $table->string('q35_a_details')->nullable();
            $table->string('q35_b')->nullable();
            $table->string('q35_b_details_date')->nullable();
            $table->string('q35_b_details_status')->nullable();
            $table->string('q36')->nullable();
            $table->string('q36_details')->nullable();
            $table->string('q37')->nullable();
            $table->string('q37_details')->nullable();
            $table->string('q38_a')->nullable();
            $table->string('q38_a_details')->nullable();
            $table->string('q38_b')->nullable();
            $table->string('q38_b_details')->nullable();
            $table->string('q39')->nullable();
            $table->string('q39_details')->nullable();
            $table->string('q40_a')->nullable();
            $table->string('q40_a_details')->nullable();
            $table->string('q40_b')->nullable();
            $table->string('q40_b_details')->nullable();
            $table->string('q40_c')->nullable();
            $table->string('q40_c_details')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('pds_declarations', function (Blueprint $table) {
            $table->dropColumn([
                'q34_a', 'q34_a_details',
                'q34_b', 'q34_b_details',
                'q35_a', 'q35_a_details',
                'q35_b', 'q35_b_details_date', 'q35_b_details_status',
                'q36', 'q36_details',
                'q37', 'q37_details',
                'q38_a', 'q38_a_details',
                'q38_b', 'q38_b_details',
                'q39', 'q39_details',
                'q40_a', 'q40_a_details',
                'q40_b', 'q40_b_details',
                'q40_c', 'q40_c_details',
            ]);
        });
    }
};
