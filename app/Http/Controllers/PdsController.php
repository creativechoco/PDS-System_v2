<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\PdsRejection;
use App\Models\PdsSubmission;
use App\Models\PdsForm5Remark;

class PdsController extends Controller
{
    public function view()
    {
        $userId = Auth::id();
        if (!$userId) {
            abort(403, 'Unauthorized');
        }

        if ($redirect = $this->redirectIfRejected($userId)) {
            return $redirect;
        }

        $signatureFiles = DB::table('pds_signature_files')->where('user_id', $userId)->first();
        $signaturePath = $signatureFiles->signature_file_path ?? null;
        $photoPath = $signatureFiles->photo_file_path ?? null;

        $personal = DB::table('pds_personal_infos')->where('user_id', $userId)->first();
        $address = DB::table('pds_addresses')->where('user_id', $userId)->first();
        $contact = DB::table('pds_contact_infos')->where('user_id', $userId)->first();
        $idInfo = DB::table('pds_id_infos')->where('user_id', $userId)->first();

        $spouse = DB::table('pds_family_members')->where('user_id', $userId)->where('type', 'spouse')->first();
        $father = DB::table('pds_family_members')->where('user_id', $userId)->where('type', 'father')->first();
        $mother = DB::table('pds_family_members')->where('user_id', $userId)->where('type', 'mother')->first();
        $children = DB::table('pds_family_members')->where('user_id', $userId)->where('type', 'child')->get();

        $draft = DB::table('pds_drafts')->where('user_id', $userId)->first();

        $education = collect();
        if ($draft && !empty($draft->data)) {
            $data = $draft->data;

            $baseLevels = [
                'elementary' => 'ELEMENTARY',
                'secondary' => 'SECONDARY',
                'vocational' => 'VOCATIONAL / TRADE COURSE',
                'college' => 'COLLEGE',
                'graduate_studies' => 'GRADUATE STUDIES',
            ];

            foreach ($baseLevels as $key => $label) {
                if (!empty($data['education'][$key])) {
                    $row = $data['education'][$key];
                    $education->push([
                        'level' => $label,
                        'school_name' => $row['school_name'] ?? null,
                        'degree_course' => $row['basic_education'] ?? null,
                        'from' => $row['from'] ?? null,
                        'to' => $row['to'] ?? null,
                        'highest_level' => $row['highest_level'] ?? null,
                        'year_graduated' => $row['year_graduated'] ?? null,
                        'academic_honors' => $row['scholarship_acadhonors'] ?? null,
                    ]);
                }
            }

            $extras = collect($data['education_extra_level'] ?? [])->map(function ($level, $i) use ($data) {
                return [
                    'level' => $level ?? null,
                    'school_name' => $data['education_extra_school_name'][$i] ?? null,
                    'degree_course' => $data['education_extra_basic_education'][$i] ?? null,
                    'from' => $data['education_extra_from'][$i] ?? null,
                    'to' => $data['education_extra_to'][$i] ?? null,
                    'highest_level' => $data['education_extra_highest_level'][$i] ?? null,
                    'year_graduated' => $data['education_extra_year_graduated'][$i] ?? null,
                    'academic_honors' => $data['education_extra_scholarship_acadhonors'][$i] ?? null,
                ];
            })->filter(function ($row) {
                return collect($row)->some(function ($val) {
                    $v = trim((string) ($val ?? ''));
                    return $v !== '' && !in_array(strtoupper($v), ['NA', 'N/A', 'NONE'], true);
                });
            });

            $education = $education->concat($extras)->values();
        }

        if ($education->isEmpty()) {
            $education = DB::table('pds_education_records')->where('user_id', $userId)->get();
        }
        $eligibilities = DB::table('pds_eligibilities')->where('user_id', $userId)->get();
        $work = DB::table('pds_work_experiences')->where('user_id', $userId)->orderByDesc('from')->get();
        $voluntary = DB::table('pds_voluntary_work')->where('user_id', $userId)->orderByDesc('from')->get();
        $declaration = DB::table('pds_declarations')->where('user_id', $userId)->first();
        
        return view('pdsreview.pdsreview1', compact(
            'personal',
            'address',
            'contact',
            'idInfo',
            'spouse',
            'father',
            'mother',
            'children',
            'education',
            'eligibilities',
            'work',
            'voluntary',
            'declaration',
            'signaturePath',
            'photoPath'
        ));
    }

    public function review2()
    {
        $userId = Auth::id();
        if (!$userId) {
            abort(403, 'Unauthorized');
        }

        if ($redirect = $this->redirectIfRejected($userId)) {
            return $redirect;
        }

        $signatureFiles = DB::table('pds_signature_files')->where('user_id', $userId)->first();
        $signaturePath = $signatureFiles->signature_file_path ?? null;
        $photoPath = $signatureFiles->photo_file_path ?? null;

        $eligibilities = DB::table('pds_eligibilities')->where('user_id', $userId)->get();
        // Keep user-entered order (insertion sequence)
        $workExperiences = DB::table('pds_work_experiences')->where('user_id', $userId)->get();
        $declaration = DB::table('pds_declarations')->where('user_id', $userId)->first();

        return view('pdsreview.pdsreview2', compact('eligibilities', 'workExperiences', 'declaration', 'signaturePath', 'photoPath'));
    }

    public function review3()
    {
        $userId = Auth::id();
        if (!$userId) {
            abort(403, 'Unauthorized');
        }

        if ($redirect = $this->redirectIfRejected($userId)) {
            return $redirect;
        }

        $signatureFiles = DB::table('pds_signature_files')->where('user_id', $userId)->first();
        $signaturePath = $signatureFiles->signature_file_path ?? null;
        $photoPath = $signatureFiles->photo_file_path ?? null;

        // Keep user-entered order (insertion sequence) for voluntary work and training
        $voluntaryWorks = DB::table('pds_voluntary_work')
            ->where('user_id', $userId)
            ->get();

        $training = DB::table('pds_training_programs')
            ->where('user_id', $userId)
            ->get();
        
        $other = DB::table('pds_other_info')
            ->where('user_id', $userId)
            ->get();

        $declaration = DB::table('pds_declarations')->where('user_id', $userId)->first();


        return view('pdsreview.pdsreview3', compact('voluntaryWorks', 'training', 'other', 'declaration', 'signaturePath', 'photoPath'));
    }

    public function review4()
    {
        $userId = Auth::id();
        if (!$userId) {
            abort(403, 'Unauthorized');
        }

        if ($redirect = $this->redirectIfRejected($userId)) {
            return $redirect;
        }

        $signatureFiles = DB::table('pds_signature_files')->where('user_id', $userId)->first();
        $signaturePath = $signatureFiles->signature_file_path ?? null;
        $photoPath = $signatureFiles->photo_file_path ?? null;

        $declaration = DB::table('pds_declarations')->where('user_id', $userId)->first();
        $idInfo = DB::table('pds_id_infos')->where('user_id', $userId)->first();
        // Limit to the on-form capacity (7 rows) and keep stable insertion order
        $references = DB::table('pds_references')
            ->where('user_id', $userId)
            ->orderBy('id')
            ->limit(7)
            ->get();
        $passportPhotoPath = DB::table('pds_signature_files')->where('user_id', $userId)->value('photo_file_path');

        $passportPhotoUrl = null;
        if ($passportPhotoPath) {
            $filename = basename($passportPhotoPath);
            $sanitized = $filename ? 'passport_photo/' . $filename : null;
            if ($sanitized && Storage::disk('public')->exists($sanitized)) {
                $passportPhotoUrl = $this->assetFromPublicDisk($sanitized);
            }
        }

        return view('pdsreview.pdsreview4', compact('declaration', 'idInfo', 'references', 'passportPhotoUrl', 'signaturePath'));
    }

    public function review5()
    {
        $userId = Auth::id();
        if (!$userId) {
            abort(403, 'Unauthorized');
        }

        if ($redirect = $this->redirectIfRejected($userId)) {
            return $redirect;
        }

        $signatureFiles = DB::table('pds_signature_files')->where('user_id', $userId)->first();
        $signaturePath = $signatureFiles->signature_file_path ?? null;
        $photoPath = $signatureFiles->photo_file_path ?? null;

        // Get work experience data from the new pds_form5_remarks table
        $workExperiences = PdsForm5Remark::where('user_id', $userId)->get();
        $declaration = DB::table('pds_declarations')->where('user_id', $userId)->first();

        return view('pdsreview.pdsreview5', compact('workExperiences', 'declaration', 'signaturePath', 'photoPath'));
    }

    private function redirectIfRejected(int $userId)
    {
        $hasRejection = PdsRejection::where('user_id', $userId)->exists();
        if ($hasRejection) {
            // If admin rejected, send employee back to editable PDS forms to resubmit
            return redirect()->route('pds.form1');
        }

        // If approved, keep view-only behavior; otherwise allow view
        $submission = PdsSubmission::where('user_id', $userId)->first();
        if ($submission && $submission->status === 'Approved') {
            return null;
        }

        return null;
    }
}
