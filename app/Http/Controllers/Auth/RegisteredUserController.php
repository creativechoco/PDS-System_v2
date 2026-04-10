<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\RegistrationUser;
use App\Models\UserProfile;
use App\Models\User;
use App\Models\AdminUser;
use App\Notifications\EmployeeRegistered;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rules;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    private function trimNotificationHistory(Collection $notifiables, int $limit = 20): void
    {
        foreach ($notifiables as $notifiable) {
            $query = $notifiable->notifications()
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->skip($limit);

            // Delete in small chunks to avoid dialect quirks
            do {
                $excessIds = $query->take(500)->pluck('id');
                if ($excessIds->isEmpty()) {
                    break;
                }
                $notifiable->notifications()->whereIn('id', $excessIds)->delete();
            } while ($excessIds->count() === 500);
        }
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique(User::class, 'name')],
            'gender' => ['required', 'in:Male,Female'],
            'unit' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'digits:11'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'type' => ['required', 'in:Permanent Employee,Contract of Service,Job Order'],
            'location_assigned' => ['required', 'string', 'max:255'],
            'profile_photo' => ['required', 'image', 'max:3072'],
        ]);
        
    $role = str_starts_with($request->email, 'admin1@gmail.com') ? 'admin' : 'employee';

        $approved = RegistrationUser::whereRaw('LOWER(full_name) = LOWER(?)', [$request->name])->first();

        if (! $approved) {
            return back()
                ->withInput()
                ->withErrors(['name' => 'Name not found in the official employee list.']);
        }

        $role = 'employee';
        $status = 'Active';

        // Store file first but roll back DB + delete file if anything fails
        $path = $request->file('profile_photo')->store('profiles', 'public');

        try {
            return DB::transaction(function () use ($request, $role, $status, $path) {
                $user = User::create([
                    'name' => $request->name,
                    'gender' => $request->gender,
                    'unit' => $request->unit,
                    'phone' => $request->phone,
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                    'type' => $request->type,
                    'status' => $status,
                    'location_assigned' => $request->location_assigned,
                    'role' => $role,
                ]);

                UserProfile::create([
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'profile' => $path,
                ]);

                // Notify all admins about the new employee registration
                $adminUsers = AdminUser::all();
                $adminsFromUsersTable = User::where('role', 'admin')->get();
                $recipients = $adminUsers->concat($adminsFromUsersTable);

                if ($recipients->isNotEmpty()) {
                    try {
                        Notification::send($recipients, new EmployeeRegistered($user));
                        $this->trimNotificationHistory($recipients);
                    } catch (\Symfony\Component\Mailer\Exception\TransportException $e) {
                        Log::warning('Failed to send admin notification email: ' . $e->getMessage());
                    } catch (\Exception $e) {
                        Log::warning('Failed to send admin notification email: ' . $e->getMessage());
                    }
                }

                try {
                    event(new Registered($user));
                } catch (\Symfony\Component\Mailer\Exception\TransportException $e) {
                    Log::warning('Failed to send email verification: ' . $e->getMessage());
                    return redirect()->route('verification.notice')->with('status', 'registration-successful-email-failed');
                } catch (\Exception $e) {
                    Log::warning('Failed to send email verification: ' . $e->getMessage());
                    return redirect()->route('verification.notice')->with('status', 'registration-successful-email-failed');
                }

                Auth::login($user);

                // ensure fresh pds session cache for new account
                session()->forget(['pds', 'pds_owner']);
                session(['pds_owner' => $user->id]);

                // Require email verification before proceeding
                if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail()) {
                    return redirect()->route('verification.notice')->with('status', 'verification-link-sent');
                }

                return $user->role === 'employee'
                    ? redirect('/employee')->with('clearRegisterCache', true)
                    : redirect(route('dashboard', absolute: false))->with('clearRegisterCache', true);
            });
        } catch (\Throwable $e) {
            Storage::disk('public')->delete($path);
            throw $e;
        }
    }
}