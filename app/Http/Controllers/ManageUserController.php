<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\ProfileEditRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;
use App\Notifications\EmployeeInfoUpdated;
use App\Services\ActivityLogger;

class ManageUserController extends Controller
{
    // profile
    public function index(Request $request)
        {
            $units = config('units.list', []);
            $status = $request->query('status');

            $employees = User::select('id', 'name', 'gender', 'unit', 'email', 'phone', 'type', 'status', 'location_assigned', 'created_at')
                ->where('is_archive', false) // Only show non-archived users
                ->with('profile')
                ->latest('created_at')
                ->get()
                ->map(function (User $user) {
                    $avatar = $this->avatarUrl($user->profile?->profile);
                    $latestEditRequest = ProfileEditRequest::where('user_id', $user->id)
                        ->latest()
                        ->first();

                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'gender' => $user->gender,
                        'unit' => $user->unit,
                        'email' => $user->email,
                        'phone' => $user->phone,
                        'type' => $user->type,
                        'status' => $user->status,
                        'location' => $user->location_assigned,
                        'created_at' => $user->created_at?->toIso8601String(),
                        'avatar' => $avatar,
                        'edit_request' => $latestEditRequest ? [
                            'id' => $latestEditRequest->id,
                            'status' => $latestEditRequest->status,
                            'remarks' => $latestEditRequest->remarks,
                        ] : null,
                    ];
                })
                ->values();

            return view('manage-user', compact('employees', 'units', 'status'));
        }

    // archive page
    public function archive(Request $request)
    {
        $units = config('units.list', []);
        $status = $request->query('status');

        // Get archived users (is_archive = 1)
        $employees = User::select('id', 'name', 'gender', 'unit', 'email', 'phone', 'type', 'status', 'location_assigned', 'updated_at')
            ->where('is_archive', true)
            ->latest('updated_at')
            ->get()
            ->map(function (User $user) {
                $avatar = $this->avatarUrl($user->profile?->profile);
                $latestEditRequest = ProfileEditRequest::where('user_id', $user->id)
                    ->latest()
                    ->first();

                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'gender' => $user->gender,
                    'unit' => $user->unit,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'type' => $user->type,
                    'status' => $user->status,
                    'location' => $user->location_assigned,
                    'archived_at' => $user->updated_at?->toIso8601String(),
                    'avatar' => $avatar,
                    'edit_request' => $latestEditRequest ? [
                        'id' => $latestEditRequest->id,
                        'status' => $latestEditRequest->status,
                        'remarks' => $latestEditRequest->remarks,
                    ] : null,
                ];
            })
            ->values();

        return view('archive', compact('employees', 'units', 'status'));
    }

    // archive user
    public function archiveUser(User $user)
    {
        $user->update([
            'is_archive' => true,
            'archived_at' => now(),
            'archived_by' => auth()->user()->name,
            'status' => 'Inactive', // Also update status to Inactive
        ]);

        ActivityLogger::log(
            'archive',
            "Archived the employee account.",
            ['id' => $user->id, 'name' => $user->name, 'email' => $user->email, 'type' => $user->type, 'unit' => $user->unit]
        );

        return response()->json([
            'message' => 'User archived successfully',
            'user' => $user->only(['id','name','gender','unit','email','phone','type','status','location_assigned', 'is_archive']),
        ]);
    }

    // unarchive user
    public function unarchiveUser(User $user)
    {
        $user->update([
            'is_archive' => false,
            'archived_at' => null,
            'archived_by' => null,
            'status' => 'Active', // Also update status to Active
        ]);

        ActivityLogger::log(
            'unarchive',
            "Restored (unarchived) the employee account.",
            ['id' => $user->id, 'name' => $user->name, 'email' => $user->email, 'type' => $user->type, 'unit' => $user->unit]
        );

        return response()->json([
            'message' => 'User unarchived successfully',
            'user' => $user->only(['id','name','gender','unit','email','phone','type','status','location_assigned', 'is_archive']),
        ]);
    }

    
        // update
    public function update(Request $request, User $user)
    {
        $units = config('units.list', []);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'unit' => ['required', Rule::in($units)],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'phone' => ['required', 'digits:11'],
            'type' => ['required', 'in:Permanent Employee,Contract of Service,Job Order'],
            'status' => ['required', 'in:Active,Inactive'],
            'location_assigned' => ['required', 'string', 'max:255'],
        ]);

        $original = $user->only(['name','unit','email','phone','type','status','location_assigned']);

        $user->fill($data);
        $user->save();

        // Check if status changed to Inactive and automatically archive
        $shouldArchive = false;
        if (isset($data['status']) && $data['status'] === 'Inactive' && $original['status'] !== 'Inactive') {
            $user->update([
                'is_archive' => true,
                'archived_at' => now(),
                'archived_by' => auth()->user()->name,
            ]);
            $shouldArchive = true;
        }
        // Check if status changed from Inactive to Active and automatically unarchive
        elseif (isset($data['status']) && $data['status'] === 'Active' && $original['status'] === 'Inactive' && $user->is_archive) {
            $user->update([
                'is_archive' => false,
                'archived_at' => null,
                'archived_by' => null,
            ]);
        }
        // Skip automatic archiving if this was a manual archive/unarchive action
        // because status was already handled in the respective methods

        $changed = [];
        foreach ($data as $key => $value) {
            $origVal = $original[$key] ?? null;
            if ($origVal !== $value) {
                $changed[] = match ($key) {
                    'name' => 'Name',
                    'unit' => 'Division/Section/Unit/Office',
                    'email' => 'Email',
                    'phone' => 'Phone',
                    'type' => 'Employee Status',
                    'status' => 'Status',
                    'location_assigned' => 'Place of Assignment',
                    default => $key,
                };
            }
        }

        if (!empty($changed)) {
            Notification::send($user, new EmployeeInfoUpdated($user, $changed));
            $this->trimNotificationHistory($user);

            $changedList = implode(', ', $changed);
            ActivityLogger::log(
                'update',
                "Updated the information. Changed fields: {$changedList}.",
                ['id' => $user->id, 'name' => $user->name, 'email' => $user->email, 'type' => $user->type, 'unit' => $user->unit],
                ['changed_fields' => $changed]
            );
        }

        // Prepare response message based on what happened
        $message = 'User updated';
        if ($shouldArchive) {
            $message = 'User status changed to Inactive and automatically archived';
        } elseif (isset($data['status']) && $data['status'] === 'Active' && $original['status'] === 'Inactive' && isset($original['is_archive']) && $original['is_archive']) {
            $message = 'User status changed to Active and automatically unarchived';
        } elseif (isset($data['status']) && $data['status'] === 'Inactive' && $original['status'] !== 'Inactive' && !isset($data['is_archive'])) {
            $message = 'User status changed to Inactive and automatically archived';
        } elseif (isset($data['status']) && $data['status'] === 'Active' && $original['status'] === 'Inactive' && !isset($data['is_archive'])) {
            $message = 'User status changed to Active and automatically unarchived';
        }

        return response()->json([
            'message' => $message,
            'user' => $user->only(['id','name','gender','unit','email','phone','type','status','location_assigned', 'is_archive']),
        ]);
    }

    private function trimNotificationHistory($notifiable, int $limit = 20): void
    {
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

    // delete all account info
    public function destroy(User $user)
    {
        $deletedName  = $user->name;
        $deletedEmail = $user->email;
        $deletedType  = $user->type;
        $deletedUnit  = $user->unit;

        DB::transaction(function () use ($user) {
            // Collect file paths before deleting rows
            $photoPaths = DB::table('pds_signature_files')
                ->where('user_id', $user->id)
                ->pluck('photo_file_path')
                ->filter();

            $signaturePaths = DB::table('pds_signature_files')
                ->where('user_id', $user->id)
                ->pluck('signature_file_path')
                ->filter();

            $thumbmarkPaths = DB::table('pds_signature_files')
                ->where('user_id', $user->id)
                ->pluck('thumbmark_file_path')
                ->filter();

            $profilePaths = DB::table('users_profile')
                ->where('user_id', $user->id)
                ->pluck('profile')
                ->filter();

            $tables = [
                'pds_addresses',
                'pds_contact_infos',
                'pds_declarations',
                'pds_drafts',
                'pds_education_records',
                'pds_eligibilities',
                'pds_family_members',
                'pds_form5_remarks',
                'pds_id_infos',
                'pds_other_info',
                'pds_personal_infos',
                'pds_references',
                'pds_rejections',
                'pds_signature_files',
                'pds_submissions',
                'pds_training_programs',
                'pds_voluntary_work',
                'pds_work_experiences',
                'profile_edit_requests',
                'users_profile',
                'otps',
                'notifications',
            ];

            foreach ($tables as $table) {
                $query = DB::table($table);

                if ($table === 'notifications') {
                    $query->where('notifiable_id', $user->id);
                } elseif ($table === 'otps') {
                    $query->where('email', $user->email);
                } else {
                    $query->where('user_id', $user->id);
                }

                $query->delete();
            }

            // Also remove notifications that reference this user in payload (e.g., sent to admins)
            $connection = DB::connection();
            $driver = $connection->getDriverName();
            
            if ($driver === 'pgsql') {
                // PostgreSQL syntax
                DB::table('notifications')
                    ->whereRaw("(data::jsonb)->>'user_id' = ?", [$user->id])
                    ->delete();
            } else {
                // MySQL syntax
                DB::table('notifications')
                    ->whereRaw("JSON_EXTRACT(data, '$.user_id') = ?", [$user->id])
                    ->delete();
            }

            // Delete stored files tied to this user (passport photos, signatures, profiles)
            foreach ($photoPaths as $path) {
                Storage::disk('public')->delete($path);
            }
            foreach ($signaturePaths as $path) {
                Storage::disk('public')->delete($path);
            }
            foreach ($thumbmarkPaths as $path) {
                Storage::disk('public')->delete($path);
            }
            foreach ($profilePaths as $path) {
                Storage::disk('public')->delete($path);
            }

            $user->delete();
        });

        ActivityLogger::log(
            'delete',
            "Permanently deleted the employee account.",
            ['id' => null, 'name' => $deletedName, 'email' => $deletedEmail, 'type' => $deletedType, 'unit' => $deletedUnit]
        );

        return response()->json([
            'message' => 'User deleted',
        ]);
    }
}
