<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Collection;
use App\Models\AdminUser;
use App\Models\PdsRejection;
use App\Notifications\PdsSubmitted;
use App\Notifications\PdsResubmitted;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Models\PdsSubmission;
use App\Models\User;
use App\Models\PdsDraft;
use App\Models\PdsForm5Remark;

class PdsSubmissionController extends Controller
{
    private function trimNotificationHistory(Collection $notifiables, int $limit = 20): void
    {
        foreach ($notifiables as $notifiable) {
            $query = $notifiable->notifications()
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->skip($limit);

            do {
                $excessIds = $query->take(500)->pluck('id');
                if ($excessIds->isEmpty()) {
                    break;
                }
                $notifiable->notifications()->whereIn('id', $excessIds)->delete();
            } while ($excessIds->count() === 500);
        }
    }

    public function store(Request $request)
    {
        $userId = Auth::id();
        if (!$userId) {
            abort(403, 'Unauthorized');
        }

        // Merge prior step data (draft + session) with current payload to avoid null inserts
        $draft = PdsDraft::where('user_id', $userId)->first();
        $draftData = $draft->data ?? [];
        $sessionData = session('pds', []);

        $incoming = $request->except(array_keys($request->allFiles()));
        $merged = array_replace_recursive($draftData, $sessionData, $incoming);

        $request->merge($merged);

        // store merged step without files for consistency
        session(['pds' => $merged]);
        $req = $request;
        $userId = Auth::id();
        $photoPath = $this->storePhoto($request, $userId);
        $signaturePath = $this->storeSignature($request, $userId);
        $rowHasData = function (array $row): bool {
            return collect($row)->some(fn ($v) => strlen(trim((string) $v)) > 0);
        };

        $ensureFirstRowNotBlank = function (array $fields, string $label) {
            $hasValue = collect($fields)->contains(fn ($v) => strlen(trim((string) $v)) > 0);
            if (!$hasValue) {
                abort(422, "Please fill out at least the first row of {$label} (enter NA if not applicable).");
            }
        };

        $validateNa = function (array $fields, string $label) {
            $flat = collect($fields)->flatten()->map(fn ($v) => Str::upper(trim((string) $v)));
            if ($flat->filter()->isNotEmpty()) {
                return;
            }
            if ($flat->contains('NA')) {
                return;
            }
            abort(422, "$label requires at least one entry or an 'NA'.");
        };

        $existingSignatureRow = DB::table('pds_signature_files')->where('user_id', $userId)->first();
        $existingPhotoPath = $existingSignatureRow->photo_file_path ?? null;
        $existingSignaturePath = $existingSignatureRow->signature_file_path ?? null;
        $existingThumbmarkPath = $existingSignatureRow->thumbmark_file_path ?? null;

        $ensureFirstRowNotBlank([
            $req->input('eligibility.0'),
            $req->input('rating.0'),
            $req->input('date.0'),
            $req->input('place.0'),
            $req->input('license_no.0'),
            $req->input('validity.0'),
        ], 'Civil Service Eligibility');

        $ensureFirstRowNotBlank([
            $req->input('work_from.0'),
            $req->input('work_to.0'),
            $req->input('work_position_title.0'),
            $req->input('work_department.0'),
            $req->input('work_status.0'),
            $req->input('work_govt_service.0'),
        ], 'Work Experience');

        $alreadySubmitted = PdsSubmission::where('user_id', $userId)->exists();

        DB::transaction(function () use ($req, $userId, $rowHasData, $validateNa, $signaturePath, $photoPath, $existingPhotoPath, $existingSignaturePath, $existingThumbmarkPath, $draftData) {
            DB::table('pds_personal_infos')->updateOrInsert(
                ['user_id' => $userId],
                [
                    'surname' => $req->input('surname'),
                    'firstname' => $req->input('firstname'),
                    'middlename' => $req->input('middlename'),
                    'name_extension' => $req->input('employee_name_extension'),
                    'date_of_birth' => $req->input('date_of_birth'),
                    'place_of_birth' => $req->input('place_of_birth'),
                    'sex' => collect($req->input('sex', []))->first(),
                    'civil_status' => collect($req->input('civilstatus', []))->first(),
                    'height' => $req->input('height'),
                    'weight' => $req->input('weight'),
                    'blood_type' => $req->input('blood_type'),
                    'umid_no' => $req->input('umid_id_no'),
                    'country' => $req->input('country'),
                    'pagibig_no' => $req->input('pagibig_id_no'),
                    'philhealth_no' => $req->input('philhealth_no'),
                    'philsys_no' => $req->input('philsys_no'),
                    'tin_no' => $req->input('tin_no'),
                    'agency_employee_no' => $req->input('agency_employee_no'),
                    'citizenship' => collect($req->input('citizenship', []))->first(),
                ]
            );

            DB::table('pds_addresses')->updateOrInsert(
                ['user_id' => $userId],
                [
                    'present_house_block_lot' => $req->input('house_block_lot'),
                    'present_street' => $req->input('street'),
                    'present_subdivision_village' => $req->input('subdivision_village'),
                    'present_barangay' => $req->input('baranggay'),
                    'present_city_municipality' => $req->input('city_municipality'),
                    'present_province' => $req->input('province'),
                    'present_zip_code' => $req->input('zip_code'),
                    'permanent_house_block_lot' => $req->input('permanent_house_block_lot'),
                    'permanent_street' => $req->input('permanent_street'),
                    'permanent_subdivision_village' => $req->input('permanent_subdivision_village'),
                    'permanent_barangay' => $req->input('permanent_baranggay'),
                    'permanent_city_municipality' => $req->input('permanent_city_municipality'),
                    'permanent_province' => $req->input('permanent_province'),
                ]
            );

            DB::table('pds_contact_infos')->updateOrInsert(
                ['user_id' => $userId],
                [
                    'telephone_no' => $req->input('telephone_no'),
                    'mobile_no' => $req->input('mobile_no'),
                    'email_address' => $req->input('email_address'),
                ]
            );

            DB::table('pds_id_infos')->updateOrInsert(
                ['user_id' => $userId],
                [
                    'gov_id' => $req->input('gov_id'),
                    'passport_licence_id' => $req->input('licence_passport_id'),
                    'date_place_issuance' => $req->input('id_issue_date_place'),
                ]
            );

            DB::table('pds_declarations')->updateOrInsert(
                ['user_id' => $userId],
                [
                    'date_accomplished' => $req->input('date5') ?? $req->input('date_accomplished'),
                    'q34_a' => $req->input('q34_a'),
                    'q34_a_details' => $req->input('q34_a_details'),
                    'q34_b' => $req->input('q34_b'),
                    'q34_b_details' => $req->input('q34_b_details'),
                    'q35_a' => $req->input('q35_a'),
                    'q35_a_details' => $req->input('q35_a_details'),
                    'q35_b' => $req->input('q35_b'),
                    'q35_b_details_date' => $req->input('q35_b_details_date'),
                    'q35_b_details_status' => $req->input('q35_b_details_status'),
                    'q36' => $req->input('q36'),
                    'q36_details' => $req->input('q36_details'),
                    'q37' => $req->input('q37'),
                    'q37_details' => $req->input('q37_details'),
                    'q38_a' => $req->input('q38_a'),
                    'q38_a_details' => $req->input('q38_a_details'),
                    'q38_b' => $req->input('q38_b'),
                    'q38_b_details' => $req->input('q38_b_details'),
                    'q39' => $req->input('q39'),
                    'q39_details' => $req->input('q39_details'),
                    'q40_a' => $req->input('q40_a'),
                    'q40_a_details' => $req->input('q40_a_details'),
                    'q40_b' => $req->input('q40_b'),
                    'q40_b_details' => $req->input('q40_b_details'),
                    'q40_c' => $req->input('q40_c'),
                    'q40_c_details' => $req->input('q40_c_details'),
                ]
            );

            DB::table('pds_family_members')->where('user_id', $userId)->delete();

            $spouse = [
                'type' => 'spouse',
                'firstname' => $req->input('spouse_firstname'),
                'middlename' => $req->input('spouse_middlename'),
                'surname' => $req->input('spouse_surname'),
                'name_extension' => $req->input('spouse_name_extension'),
                'occupation' => $req->input('spouse_occupation'),
                'employer' => $req->input('spouse_employer_business_name'),
                'business_address' => $req->input('spouse_business_address'),
                'telephone_no' => $req->input('spouse_telephone_no'),
            ];
            if ($rowHasData($spouse)) {
                DB::table('pds_family_members')->insert(array_merge($spouse, ['user_id' => $userId]));
            }

            $childNames = collect($req->input('children_familybg', []));
            $childDob = collect($req->input('children_dateofbirth_familybg', []));

            // Validation: when a child name exists (not NA/N/A/NONE), the matching DOB is required
            $childNameDobValidator = Validator::make($req->all(), []);
            $childNames->each(function ($name, $i) use ($childDob, $childNameDobValidator) {
                $nameTrim = strtoupper(trim((string) $name));
                if ($nameTrim === '' || in_array($nameTrim, ['NA', 'N/A', 'NONE'], true)) {
                    return;
                }

                $dob = trim((string) $childDob->get($i));
                if ($dob === '') {
                    $childNameDobValidator->errors()->add("children_dateofbirth_familybg.$i", 'Date of birth is required for this child.');
                }
            });

            if ($childNameDobValidator->errors()->isNotEmpty()) {
                return redirect()->back()->withErrors($childNameDobValidator)->withInput();
            }

            $children = $childNames->map(function ($name, $i) use ($childDob, $userId) {
                return [
                    'user_id' => $userId,
                    'type' => 'child',
                    'firstname' => $name,
                    'date_of_birth' => $childDob->get($i),
                ];
            })->filter($rowHasData);

            $hasRealChild = $children->contains(function ($row) {
                $name = strtoupper(trim((string) $row['firstname']));
                return $name !== '' && $name !== 'NA';
            });

            if ($hasRealChild) {
                $children = $children->filter(function ($row) {
                    $name = strtoupper(trim((string) $row['firstname']));
                    return $name !== '' && $name !== 'NA';
                });
            } elseif ($children->isNotEmpty()) {
                // Keep only the first NA entry
                $first = $children->first();
                $children = collect([$first]);
            }

            if ($children->isNotEmpty()) {
                DB::table('pds_family_members')->insert($children->all());
            }

            $father = [
                'type' => 'father',
                'firstname' => $req->input('father_firstname'),
                'middlename' => $req->input('father_middlename'),
                'surname' => $req->input('father_surname'),
                'name_extension' => $req->input('father_name_extension'),
            ];
            if ($rowHasData($father)) {
                DB::table('pds_family_members')->insert(array_merge($father, ['user_id' => $userId]));
            }

            $mother = [
                'type' => 'mother',
                'firstname' => $req->input('mother_firstname'),
                'middlename' => $req->input('mother_middlename'),
                'surname' => $req->input('mother_surname'),
            ];
            if ($rowHasData($mother)) {
                DB::table('pds_family_members')->insert(array_merge($mother, ['user_id' => $userId]));
            }

            DB::table('pds_education_records')->where('user_id', $userId)->delete();
            $eduArray = collect($req->input('education', []));

            $edu = $eduArray->map(function ($row, $level) use ($userId) {
                return [
                    'user_id' => $userId,
                    'level' => $level,
                    'school_name' => $row['school_name'] ?? null,
                    'degree_course' => $row['basic_education'] ?? null,
                    'from' => $row['from'] ?? null,
                    'to' => $row['to'] ?? null,
                    'highest_level' => $row['highest_level'] ?? null,
                    'year_graduated' => $row['year_graduated'] ?? null,
                    'academic_honors' => $row['scholarship_acadhonors'] ?? null,
                ];
            })->filter($rowHasData);

            $hasRealEdu = $edu->contains(function ($row) {
                $name = strtoupper(trim((string) $row['school_name']));
                return $name !== '' && $name !== 'NA';
            });

            if ($hasRealEdu) {
                $edu = $edu->filter(function ($row) {
                    $name = strtoupper(trim((string) $row['school_name']));
                    return $name !== '' && $name !== 'NA';
                });
            } elseif ($edu->isNotEmpty()) {
                $edu = collect([$edu->first()]);
            }

            // Dynamic education extras (fallback to draft data if current request/session lacks them)
            $draftExtras = $draftData ?? [];

            $extraLevels = $req->input('education_extra_level');
            if (empty($extraLevels) && isset($draftExtras['education_extra_level'])) {
                $extraLevels = $draftExtras['education_extra_level'];
            }

            $extraSchools = $req->input('education_extra_school_name');
            if (empty($extraSchools) && isset($draftExtras['education_extra_school_name'])) {
                $extraSchools = $draftExtras['education_extra_school_name'];
            }

            $extraBasics = $req->input('education_extra_basic_education');
            if (empty($extraBasics) && isset($draftExtras['education_extra_basic_education'])) {
                $extraBasics = $draftExtras['education_extra_basic_education'];
            }

            $extraFrom = $req->input('education_extra_from');
            if (empty($extraFrom) && isset($draftExtras['education_extra_from'])) {
                $extraFrom = $draftExtras['education_extra_from'];
            }

            $extraTo = $req->input('education_extra_to');
            if (empty($extraTo) && isset($draftExtras['education_extra_to'])) {
                $extraTo = $draftExtras['education_extra_to'];
            }

            $extraHighest = $req->input('education_extra_highest_level');
            if (empty($extraHighest) && isset($draftExtras['education_extra_highest_level'])) {
                $extraHighest = $draftExtras['education_extra_highest_level'];
            }

            $extraYear = $req->input('education_extra_year_graduated');
            if (empty($extraYear) && isset($draftExtras['education_extra_year_graduated'])) {
                $extraYear = $draftExtras['education_extra_year_graduated'];
            }

            $extraHonors = $req->input('education_extra_scholarship_acadhonors');
            if (empty($extraHonors) && isset($draftExtras['education_extra_scholarship_acadhonors'])) {
                $extraHonors = $draftExtras['education_extra_scholarship_acadhonors'];
            }

            $extraEdu = collect($extraLevels)->map(function ($level, $i) use ($userId, $extraSchools, $extraBasics, $extraFrom, $extraTo, $extraHighest, $extraYear, $extraHonors) {
                return [
                    'user_id' => $userId,
                    'level' => $level ?? null,
                    'school_name' => $extraSchools[$i] ?? null,
                    'degree_course' => $extraBasics[$i] ?? null,
                    'from' => $extraFrom[$i] ?? null,
                    'to' => $extraTo[$i] ?? null,
                    'highest_level' => $extraHighest[$i] ?? null,
                    'year_graduated' => $extraYear[$i] ?? null,
                    'academic_honors' => $extraHonors[$i] ?? null,
                ];
            })->filter($rowHasData);

            $allEdu = $edu->concat($extraEdu);

            if ($allEdu->isNotEmpty()) {
                DB::table('pds_education_records')->insert($allEdu->all());
            }

            $elig = collect($req->input('eligibility', []))->map(function ($val, $i) use ($req, $userId) {
                return [
                    'user_id' => $userId,
                    'eligibility' => $val,
                    'rating' => $req->input("rating.$i"),
                    'exam_date' => $req->input("date.$i"),
                    'exam_place' => $req->input("place.$i"),
                    'license_no' => $req->input("license_no.$i"),
                    'validity' => $req->input("validity.$i"),
                ];
            })->filter($rowHasData);
            if ($elig->isNotEmpty()) {
                $validateNa([$req->input('eligibility', [])], 'Eligibilities');

                $existingElig = DB::table('pds_eligibilities')
                    ->where('user_id', $userId)
                    ->orderBy('id')
                    ->get();

                $elig->each(function ($row, $idx) use ($existingElig) {
                    $existing = $existingElig[$idx] ?? null;
                    if ($existing) {
                        DB::table('pds_eligibilities')->where('id', $existing->id)->update($row);
                    }
                });

                if ($elig->count() > $existingElig->count()) {
                    $toInsert = $elig->slice($existingElig->count());
                    DB::table('pds_eligibilities')->insert($toInsert->all());
                }

                if ($elig->count() < $existingElig->count()) {
                    $excessIds = $existingElig->slice($elig->count())->pluck('id');
                    DB::table('pds_eligibilities')->whereIn('id', $excessIds)->delete();
                }
            } else {
                DB::table('pds_eligibilities')->where('user_id', $userId)->delete();
            }

            $work = collect($req->input('work_from', []))->map(function ($from, $i) use ($req, $userId) {
                return [
                    'user_id' => $userId,
                    'from' => $from,
                    'to' => $req->input("work_to.$i"),
                    'position_title' => $req->input("work_position_title.$i"),
                    'department' => $req->input("work_department.$i"),
                    'status' => $req->input("work_status.$i"),
                    'govt_service' => $req->input("work_govt_service.$i"),
                ];
            })->filter($rowHasData);
            if ($work->isNotEmpty()) {
                $existingWork = DB::table('pds_work_experiences')
                    ->where('user_id', $userId)
                    ->orderBy('id')
                    ->get();

                $work->each(function ($row, $idx) use ($existingWork) {
                    $existing = $existingWork[$idx] ?? null;
                    if ($existing) {
                        DB::table('pds_work_experiences')->where('id', $existing->id)->update($row);
                    }
                });

                if ($work->count() > $existingWork->count()) {
                    $toInsert = $work->slice($existingWork->count());
                    DB::table('pds_work_experiences')->insert($toInsert->all());
                }

                if ($work->count() < $existingWork->count()) {
                    $excessIds = $existingWork->slice($work->count())->pluck('id');
                    DB::table('pds_work_experiences')->whereIn('id', $excessIds)->delete();
                }
            } else {
                DB::table('pds_work_experiences')->where('user_id', $userId)->delete();
            }

            $vol = collect($req->input('voluntary_organization', []))->map(function ($org, $i) use ($req, $userId) {
                return [
                    'user_id' => $userId,
                    'organization' => $org,
                    'address' => $req->input("voluntary_address.$i"),
                    'from' => $req->input("voluntary_from.$i"),
                    'to' => $req->input("voluntary_to.$i"),
                    'hours' => $req->input("voluntary_hours.$i"),
                    'position' => $req->input("voluntary_position_nature_of_work.$i"),
                ];
            })->filter($rowHasData);
            if ($vol->isNotEmpty()) {
                $validateNa([$req->input('voluntary_organization', [])], 'Voluntary work');

                $existingVol = DB::table('pds_voluntary_work')
                    ->where('user_id', $userId)
                    ->orderBy('id')
                    ->get();

                $vol->each(function ($row, $idx) use ($existingVol) {
                    $existing = $existingVol[$idx] ?? null;
                    if ($existing) {
                        DB::table('pds_voluntary_work')->where('id', $existing->id)->update($row);
                    }
                });

                if ($vol->count() > $existingVol->count()) {
                    $toInsert = $vol->slice($existingVol->count());
                    DB::table('pds_voluntary_work')->insert($toInsert->all());
                }

                if ($vol->count() < $existingVol->count()) {
                    $excessIds = $existingVol->slice($vol->count())->pluck('id');
                    DB::table('pds_voluntary_work')->whereIn('id', $excessIds)->delete();
                }
            } else {
                DB::table('pds_voluntary_work')->where('user_id', $userId)->delete();
            }

            $train = collect($req->input('learning_title_of_ld', []))->map(function ($title, $i) use ($req, $userId) {
                return [
                    'user_id' => $userId,
                    'title' => $title,
                    'from' => $req->input("learning_from.$i"),
                    'to' => $req->input("learning_to.$i"),
                    'hours' => $req->input("learning_hours.$i"),
                    'type_of_ld' => $req->input("learning_type_of_ld.$i"),
                    'conducted_by' => $req->input("learning_conducted_sponsored_by.$i"),
                ];
            })->filter($rowHasData);
            if ($train->isNotEmpty()) {
                $validateNa([$req->input('learning_title_of_ld', [])], 'Training');

                $existingTrain = DB::table('pds_training_programs')
                    ->where('user_id', $userId)
                    ->orderBy('id')
                    ->get();

                $train->each(function ($row, $idx) use ($existingTrain) {
                    $existing = $existingTrain[$idx] ?? null;
                    if ($existing) {
                        DB::table('pds_training_programs')->where('id', $existing->id)->update($row);
                    }
                });

                if ($train->count() > $existingTrain->count()) {
                    $toInsert = $train->slice($existingTrain->count());
                    DB::table('pds_training_programs')->insert($toInsert->all());
                }

                if ($train->count() < $existingTrain->count()) {
                    $excessIds = $existingTrain->slice($train->count())->pluck('id');
                    DB::table('pds_training_programs')->whereIn('id', $excessIds)->delete();
                }
            } else {
                DB::table('pds_training_programs')->where('user_id', $userId)->delete();
            }

            $skills = collect($req->input('special_skills_hobbies', []))->map(fn ($v) => ['category' => 'skills', 'description' => $v]);
            $recognition = collect($req->input('non_academic_distinctions_recognition', []))->map(fn ($v) => ['category' => 'recognition', 'description' => $v]);
            $assoc = collect($req->input('membership_in_association_organization', []))->map(fn ($v) => ['category' => 'association', 'description' => $v]);
            $otherCombined = $skills->concat($recognition)->concat($assoc)->map(fn ($row) => array_merge($row, ['user_id' => $userId]))->filter($rowHasData);
            if ($otherCombined->isNotEmpty()) {
                $validateNa([$req->input('special_skills_hobbies', []), $req->input('non_academic_distinctions_recognition', []), $req->input('membership_in_association_organization', [])], 'Other info');

                $existingOther = DB::table('pds_other_info')
                    ->where('user_id', $userId)
                    ->orderBy('id')
                    ->get();

                $otherCombined->values()->each(function ($row, $idx) use ($existingOther) {
                    $existing = $existingOther[$idx] ?? null;
                    if ($existing) {
                        DB::table('pds_other_info')->where('id', $existing->id)->update($row);
                    }
                });

                if ($otherCombined->count() > $existingOther->count()) {
                    $toInsert = $otherCombined->slice($existingOther->count());
                    DB::table('pds_other_info')->insert($toInsert->all());
                }

                if ($otherCombined->count() < $existingOther->count()) {
                    $excessIds = $existingOther->slice($otherCombined->count())->pluck('id');
                    DB::table('pds_other_info')->whereIn('id', $excessIds)->delete();
                }
            } else {
                DB::table('pds_other_info')->where('user_id', $userId)->delete();
            }

            $refs = collect($req->input('reference_name', []))->map(function ($name, $i) use ($req, $userId) {
                return [
                    'user_id' => $userId,
                    'name' => $name,
                    'address' => $req->input("reference_address.$i"),
                    'contact' => $req->input("reference_contact.$i"),
                ];
            })->filter(function ($row) use ($rowHasData) {
                $name = strtoupper(trim((string) ($row['name'] ?? '')));
                if ($name === 'NA' || $name === 'N/A') {
                    return false;
                }
                return $rowHasData($row);
            });

            // Replace references outright to avoid stale/duplicate rows showing in PDF
            DB::table('pds_references')->where('user_id', $userId)->delete();
            if ($refs->isNotEmpty()) {
                DB::table('pds_references')->insert($refs->all());
            }

            // Handle form5 work experience data
            PdsForm5Remark::where('user_id', $userId)->delete();

            // Get form5 data from request
            $durations = $req->input('duration', []);
            $positionTitles = $req->input('position_title', []);
            $officeUnits = $req->input('office_unit', []);
            $immediateSupervisors = $req->input('immediate_supervisor', []);
            $agencyLocations = $req->input('agency_location', []);
            $accomplishments = $req->input('accomplishments', []);
            $duties = $req->input('duties', []);

            // Resolve signature/photo paths once so they are available for each row
            $photoPathToPersist = $photoPath ?? $existingPhotoPath;
            $signaturePathToPersist = $signaturePath ?? ($existingSignaturePath ?? 'NA');
            $thumbmarkPathToPersist = $existingThumbmarkPath ?? 'NA';

            // Create work experience entries for each row
            $workExperienceData = [];
            $maxRows = max(count($durations), count($positionTitles), count($officeUnits), 
                          count($immediateSupervisors), count($agencyLocations), count($duties));
            
            for ($i = 0; $i < $maxRows; $i++) {
                $hasData = !empty($durations[$i]) || !empty($positionTitles[$i]) || 
                          !empty($officeUnits[$i]) || !empty($immediateSupervisors[$i]) || 
                          !empty($agencyLocations[$i]) || !empty($duties[$i]);
                
                if ($hasData) {
                    // Filter accomplishments for this row (accomplishments are stored as a flat array)
                    $rowAccomplishments = [];
                    if (!empty($accomplishments)) {
                        // Assuming accomplishments are stored per row, adjust logic if needed
                        $rowAccomplishments = array_filter($accomplishments, fn($v) => !empty(trim($v)));
                    }

                    $workExperienceData[] = [
                        'user_id' => $userId,
                        'duration' => $durations[$i] ?? null,
                        'position_title' => $positionTitles[$i] ?? null,
                        'office_unit' => $officeUnits[$i] ?? null,
                        'immediate_supervisor' => $immediateSupervisors[$i] ?? null,
                        'agency_location' => $agencyLocations[$i] ?? null,
                        'accomplishments' => $rowAccomplishments,
                        'duties' => $duties[$i] ?? null,
                        'signature_path' => $signaturePathToPersist,
                        'signature_data' => $req->input('signature_data'),
                        'date5' => $req->input('date5'),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
            
            if (!empty($workExperienceData)) {
                foreach ($workExperienceData as $row) {
                    PdsForm5Remark::create($row);
                }
            }

            DB::table('pds_signature_files')->updateOrInsert(
                ['user_id' => $userId],
                [
                    'photo_file_path' => $photoPathToPersist,
                    'signature_file_path' => $signaturePathToPersist,
                    'thumbmark_file_path' => $thumbmarkPathToPersist,
                ]
            );

            $user = User::find($userId);
            if ($user) {
                PdsSubmission::updateOrCreate(
                    ['user_id' => $userId],
                    [
                        'name' => $user->name,
                        'unit' => $user->unit,
                        'email' => $user->email,
                        'type' => $user->type,
                        'status' => 'Pending',
                        'submitted' => now(),
                    ]
                );

                // Clear prior rejection entry when user resubmits
                PdsRejection::where('user_id', $userId)->delete();
            }
        });

        session()->forget('pds');

        // Notify admins about the PDS submission/resubmission
        $adminUsers = AdminUser::all();
        $adminsFromUsersTable = User::where('role', 'admin')->get();
        $recipients = $adminUsers->concat($adminsFromUsersTable);

        if ($recipients->isNotEmpty()) {
            $user = User::find($userId);
            $notification = $alreadySubmitted
                ? new PdsResubmitted($user)
                : new PdsSubmitted($user);

            Notification::send($recipients, $notification);
            $this->trimNotificationHistory($recipients);
        }

        return back()->with('status', 'PDS saved');
    }

    /**
     * Persist submitted or cached photo to storage and return its path.
     */
    private function storePhoto(Request $request, int $userId): ?string
    {
        $disk = 'public';
        $directory = 'passport_photo';
        $existingPath = DB::table('pds_signature_files')->where('user_id', $userId)->value('photo_file_path');

        $deleteExisting = function (?string $path) use ($disk) {
            if ($path && Storage::disk($disk)->exists($path)) {
                Storage::disk($disk)->delete($path);
            }
        };

        $deleteUserPhotos = function (?string $exceptPath) use ($disk, $directory, $userId) {
            $files = Storage::disk($disk)->files($directory);
            foreach ($files as $file) {
                if (str_starts_with(basename($file), "photo_{$userId}_") && $file !== $exceptPath) {
                    Storage::disk($disk)->delete($file);
                }
            }
        };

        $uploaded = $request->file('photo');
        if ($uploaded) {
            $filename = 'photo_' . $userId . '_' . time() . '.' . $uploaded->getClientOriginalExtension();
            $path = $uploaded->storeAs($directory, $filename, $disk);
            if ($existingPath && $existingPath !== $path) {
                $deleteExisting($existingPath);
            }
            $deleteUserPhotos($path);
            return $path;
        }

        $dataUrl = $request->input('photo_data');
        if ($dataUrl && str_starts_with($dataUrl, 'data:image')) {
            if (preg_match('/^data:(image\/[a-zA-Z0-9.+-]+);base64,(.*)$/', $dataUrl, $matches)) {
                $mime = $matches[1];
                $base64 = $matches[2];
                $binary = base64_decode($base64);
                if ($binary !== false) {
                    $extension = match ($mime) {
                        'image/png' => 'png',
                        'image/webp' => 'webp',
                        default => 'jpg',
                    };
                    $filename = 'photo_' . $userId . '_' . time() . '.' . $extension;
                    $path = $directory . '/' . $filename;
                    Storage::disk($disk)->put($path, $binary, 'public');
                    if ($existingPath && $existingPath !== $path) {
                        $deleteExisting($existingPath);
                    }
                    $deleteUserPhotos($path);
                    return $path;
                }
            }
        }

        return $existingPath;
    }

    private function storeSignature(Request $request, int $userId): ?string
    {
        $disk = 'public';
        $directory = 'pds/signatures';
        $existingPath = DB::table('pds_signature_files')->where('user_id', $userId)->value('signature_file_path');
        $providedPath = $request->input('signature_path');
        if ($providedPath && !$request->hasFile('signature') && !$request->hasFile('signature_attachment')) {
            return $providedPath;
        }

        $deleteExisting = function (?string $path) use ($disk) {
            if ($path && Storage::disk($disk)->exists($path)) {
                Storage::disk($disk)->delete($path);
            }
        };

        $fileKeys = [
            'signature',
            'signature_attachment',
            'signature_attachment_1',
            'signature_attachment_2',
            'signature_attachment_3',
            'signature_attachment_4',
            'signature_attachment_5',
            'signature_file',
        ];

        foreach ($fileKeys as $key) {
            $uploaded = $request->file($key);
            if ($uploaded) {
                $filename = 'signature_' . $userId . '_' . time() . '.' . $uploaded->getClientOriginalExtension();
                $path = $uploaded->storeAs($directory, $filename, $disk);
                if ($existingPath && $existingPath !== $path) {
                    $deleteExisting($existingPath);
                }
                return $path;
            }
        }

        $dataUrl = $request->input('signature_data');
        if ($dataUrl && str_starts_with($dataUrl, 'data:image')) {
            if (preg_match('/^data:(image\/[a-zA-Z0-9.+-]+);base64,(.*)$/', $dataUrl, $matches)) {
                $mime = $matches[1];
                $base64 = $matches[2];
                $binary = base64_decode($base64);
                if ($binary !== false) {
                    $extension = match ($mime) {
                        'image/png' => 'png',
                        'image/webp' => 'webp',
                        default => 'jpg',
                    };
                    $filename = 'signature_' . $userId . '_' . time() . '.' . $extension;
                    $path = $directory . '/' . $filename;
                    Storage::disk($disk)->put($path, $binary, 'public');
                    if ($existingPath && $existingPath !== $path) {
                        $deleteExisting($existingPath);
                    }
                    return $path;
                }
            }
        }

        return $existingPath;
    }
}
