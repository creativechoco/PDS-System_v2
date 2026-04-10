<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\ProfileEditRequest;
use App\Models\AdminUser;
use App\Notifications\EmployeeProfileUpdated;
use App\Notifications\ProfileEditRequested;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();
        $latestEditRequest = ProfileEditRequest::where('user_id', $user->id)
            ->latest()
            ->first();
        $editAllowed = $latestEditRequest && $latestEditRequest->status === 'approved';

        return view('profile.edit', [
            'user' => $user,
            'avatar' => $this->avatarUrl($user?->profile?->profile),
            'units' => config('units.list', []),
            'editRequest' => $latestEditRequest,
            'editAllowed' => $editAllowed,
        ]);
    }

    /**
     * Create a profile edit request and notify admins.
     */
    public function requestEdit(Request $request): RedirectResponse
    {
        $user = $request->user();

        $existing = ProfileEditRequest::where('user_id', $user->id)
            ->where('status', 'pending')
            ->first();

        $editRequest = $existing ?? ProfileEditRequest::create([
            'user_id' => $user->id,
            'status' => 'pending',
        ]);

        $notification = new ProfileEditRequested($editRequest);
        $admins = AdminUser::all();
        $adminUsers = \App\Models\User::where('role', 'admin')->get();

        if ($admins->isNotEmpty()) {
            Notification::send($admins, $notification);
            foreach ($admins as $admin) {
                $this->trimNotificationHistory($admin);
            }
        }

        if ($adminUsers->isNotEmpty()) {
            Notification::send($adminUsers, $notification);
            foreach ($adminUsers as $adminUser) {
                $this->trimNotificationHistory($adminUser);
            }
        }

        return Redirect::route('profile.edit')->with('status', 'profile-edit-requested');
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $original = $user->only(['name','gender','unit','phone','email','type','location_assigned']);
        $originalPhoto = $user->profile?->profile;

        $user->fill($request->validated());

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $photoChanged = false;
        if ($request->hasFile('profile_photo')) {
            $oldPath = $user->profile?->profile;
            $path = $request->file('profile_photo')->store('profiles', 'public');
            $user->profile()->updateOrCreate([], [
                'name' => $user->name,
                'profile' => $path,
            ]);
            $photoChanged = $photoChanged || $oldPath !== $path;

            if ($oldPath) {
                $oldFilename = basename($oldPath);
                $oldSanitized = $oldFilename ? 'profiles/' . $oldFilename : null;
                if ($oldSanitized && Storage::disk('public')->exists($oldSanitized)) {
                    Storage::disk('public')->delete($oldSanitized);
                }
            }
        }

        $user->save();

        $changed = [];
        foreach ($original as $key => $value) {
            if ($user->{$key} !== $value) {
                $changed[] = match ($key) {
                    'name' => 'Name',
                    'gender' => 'Sex',
                    'unit' => 'Division/Section/Unit/Office',
                    'phone' => 'Phone',
                    'email' => 'Email',
                    'type' => 'Employee Status',
                    'location_assigned' => 'Place of Assignment',
                    default => $key,
                };
            }
        }

        if ($photoChanged) {
            $changed[] = 'Profile Photo';
        }

        if (!empty($changed)) {
            $notification = new EmployeeProfileUpdated($user, $changed);
            $admins = AdminUser::all();
            $adminUsers = \App\Models\User::where('role', 'admin')->get();

            if ($admins->isNotEmpty()) {
                Notification::send($admins, $notification);
                foreach ($admins as $admin) {
                    $this->trimNotificationHistory($admin);
                }
            }

            if ($adminUsers->isNotEmpty()) {
                Notification::send($adminUsers, $notification);
                foreach ($adminUsers as $adminUser) {
                    $this->trimNotificationHistory($adminUser);
                }
            }
        }

        // Consume approved edit request (one-time edit window)
        $latestApproved = ProfileEditRequest::where('user_id', $user->id)
            ->where('status', 'approved')
            ->latest()
            ->first();
        if ($latestApproved) {
            $latestApproved->update([
                'status' => 'rejected', // used/consumed
                'remarks' => 'Edit window used on save',
                'reviewed_at' => $latestApproved->reviewed_at ?? now(),
                'reviewed_by' => $latestApproved->reviewed_by,
            ]);
        }

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
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

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'department' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'digits:11'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'type' => ['required', 'in:Permanent Employee,Contract of Service'],
            'location_assigned' => ['required', 'string', 'max:255'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
