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
        Schema::create('pds_form5_remarks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            
            // Work experience fields
            $table->string('duration')->nullable();
            $table->string('position_title')->nullable();
            $table->string('office_unit')->nullable();
            $table->string('immediate_supervisor')->nullable();
            $table->string('agency_location')->nullable();
            $table->json('accomplishments')->nullable(); // Store array of accomplishments
            $table->text('duties')->nullable(); // Summary of actual duties
            
            // Form signature and date
            $table->string('signature_path')->nullable();
            $table->text('signature_data')->nullable(); // Base64 signature data
            $table->date('date5')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pds_form5_remarks');
    }
};
