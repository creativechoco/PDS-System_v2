<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pds_personal_infos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('surname')->nullable();
            $table->string('firstname')->nullable();
            $table->string('middlename')->nullable();
            $table->string('name_extension')->nullable();
            $table->string('date_of_birth')->nullable();
            $table->string('place_of_birth')->nullable();
            $table->string('sex')->nullable();
            $table->string('civil_status')->nullable();
            $table->string('height')->nullable();
            $table->string('weight')->nullable();
            $table->string('blood_type')->nullable();
            $table->string('umid_no')->nullable();
            $table->string('country')->nullable();
            $table->string('pagibig_no')->nullable();
            $table->string('philhealth_no')->nullable();
            $table->string('philsys_no')->nullable();
            $table->string('tin_no')->nullable();
            $table->string('agency_employee_no')->nullable();
            $table->string('citizenship')->nullable();
            $table->timestamps();
        });

        Schema::create('pds_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('present_house_block_lot')->nullable();
            $table->string('present_street')->nullable();
            $table->string('present_subdivision_village')->nullable();
            $table->string('present_barangay')->nullable();
            $table->string('present_city_municipality')->nullable();
            $table->string('present_province')->nullable();
            $table->string('present_zip_code')->nullable();
            $table->string('permanent_house_block_lot')->nullable();
            $table->string('permanent_street')->nullable();
            $table->string('permanent_subdivision_village')->nullable();
            $table->string('permanent_barangay')->nullable();
            $table->string('permanent_city_municipality')->nullable();
            $table->string('permanent_province')->nullable();   
            $table->timestamps();
        });

        Schema::create('pds_contact_infos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('telephone_no')->nullable();
            $table->string('mobile_no')->nullable();
            $table->string('email_address')->nullable();
            $table->timestamps();
        });

        Schema::create('pds_family_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // spouse, child, father, mother
            $table->string('firstname')->nullable();
            $table->string('middlename')->nullable();
            $table->string('surname')->nullable();
            $table->string('name_extension')->nullable();
            $table->string('occupation')->nullable();
            $table->string('employer')->nullable();
            $table->string('business_address')->nullable();
            $table->string('telephone_no')->nullable();
            $table->string('maiden_name')->nullable();
            $table->timestamps();
        });

        Schema::create('pds_education_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('level')->nullable();
            $table->string('school_name')->nullable();
            $table->string('degree_course')->nullable();
            $table->string('from')->nullable();
            $table->string('to')->nullable();
            $table->string('highest_level')->nullable();
            $table->string('year_graduated')->nullable();
            $table->string('academic_honors')->nullable();
            $table->timestamps();
        });

        Schema::create('pds_eligibilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('eligibility')->nullable();
            $table->string('rating')->nullable();
            $table->string('exam_date')->nullable();
            $table->string('exam_place')->nullable();
            $table->string('license_no')->nullable();
            $table->string('validity')->nullable();
            $table->timestamps();
        });

        Schema::create('pds_work_experiences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('from')->nullable();
            $table->string('to')->nullable();
            $table->string('position_title')->nullable();
            $table->string('department')->nullable();
            $table->string('status')->nullable();
            $table->string('govt_service')->nullable();
            $table->timestamps();
        });

        Schema::create('pds_voluntary_work', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('organization')->nullable();
            $table->string('address')->nullable();
            $table->string('from')->nullable();
            $table->string('to')->nullable();
            $table->string('hours')->nullable();
            $table->string('position')->nullable();
            $table->timestamps();
        });

        Schema::create('pds_training_programs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title')->nullable();
            $table->string('from')->nullable();
            $table->string('to')->nullable();
            $table->string('hours')->nullable();
            $table->string('type_of_ld')->nullable();
            $table->string('conducted_by')->nullable();
            $table->timestamps();
        });

        Schema::create('pds_other_info', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('category'); // skills, recognition, association
            $table->string('description')->nullable();
            $table->timestamps();
        });

        Schema::create('pds_references', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name')->nullable();
            $table->string('address')->nullable();
            $table->string('contact')->nullable();
            $table->timestamps();
        });

        Schema::create('pds_id_infos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('gov_id')->nullable();
            $table->string('licence_passport_id')->nullable();
            $table->string('id_issue_date_place')->nullable();
            $table->timestamps();
        });

        Schema::create('pds_signature_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('signature_file_path')->nullable();
            $table->string('thumbmark_file_path')->nullable();
            $table->string('photo_file_path')->nullable();
            $table->timestamps();
        });

        Schema::create('pds_declarations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('date_accomplished')->nullable();
            $table->timestamps();
        });

        Schema::create('pds_form5_remarks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pds_form5_remarks');
        Schema::dropIfExists('pds_declarations');
        Schema::dropIfExists('pds_signature_files');
        Schema::dropIfExists('pds_id_infos');
        Schema::dropIfExists('pds_references');
        Schema::dropIfExists('pds_other_info');
        Schema::dropIfExists('pds_training_programs');
        Schema::dropIfExists('pds_voluntary_work');
        Schema::dropIfExists('pds_work_experiences');
        Schema::dropIfExists('pds_eligibilities');
        Schema::dropIfExists('pds_education_records');
        Schema::dropIfExists('pds_family_members');
        Schema::dropIfExists('pds_contact_infos');
        Schema::dropIfExists('pds_addresses');
        Schema::dropIfExists('pds_personal_infos');
    }
};
