<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Otp;
use App\Models\PdsDraft;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cookie;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        // Identify authenticated guard and user
        $guard = Auth::guard('admin')->check() ? 'admin' : 'web';
        $user = Auth::guard($guard)->user();

        // Track last login time
        $user->update(['last_login_at' => now()]);

        // Require verified email for users that implement it
        if ($user instanceof MustVerifyEmail && ! $user->hasVerifiedEmail()) {
            $user->sendEmailVerificationNotification();
            return redirect()->route('verification.notice')->with('status', 'verification-link-sent');
        }

        // Skip OTP for privileged admin roles
        $otpExemptAdminRoles = ['main admin', 'admin user'];
        if ($guard === 'admin' && in_array(strtolower($user->role ?? ''), $otpExemptAdminRoles, true)) {
            return redirect()->intended(route('dashboard', absolute: false));
        }

        // Generate and send OTP
        $code = rand(100000, 999999);
        Otp::updateOrCreate(
            ['email' => $user->email],
            ['code' => $code, 'expires_at' => Carbon::now()->addMinutes(3)]
        );

        Mail::raw("Your OTP code is: $code", function ($message) use ($user) {
            $message->to($user->email)->subject('Your OTP Code');
        });

        // Stage OTP session and log out until verification
        $request->session()->put('otp_pending', [
            'user_id' => $user->id,
            'email' => $user->email,
            'guard' => $guard,
            'redirect' => $user?->role === 'employee' ? '/employee' : route('dashboard', absolute: false),
        ]);

        Auth::guard($guard)->logout();

        return redirect()->route('otp.show')->with('status', 'We sent a one-time passcode to your email.');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        // Logout both guards to avoid lingering admin sessions
        $adminGuard = Auth::guard('admin');
        $webGuard = Auth::guard('web');

        // persist cached pds data to draft before clearing session, only if owned by this user
        $webUserId = $webGuard->id();
        $sessionPds = session('pds');
        $sessionOwner = session('pds_owner');
        if ($webUserId && is_array($sessionPds) && $sessionOwner === $webUserId) {
            $draft = PdsDraft::firstOrCreate(['user_id' => $webUserId]);
            $existing = $draft->data ?? [];
            $draft->data = array_replace_recursive($existing, $sessionPds);
            $draft->save();
        }

        session()->forget(['pds', 'pds_owner']);

        $adminGuard->logout();
        $webGuard->logout();

        // Clear remember-me cookies for both guards if present
        if (method_exists($adminGuard, 'getRecallerName')) {
            Cookie::queue(Cookie::forget($adminGuard->getRecallerName()));
        }
        if (method_exists($webGuard, 'getRecallerName')) {
            Cookie::queue(Cookie::forget($webGuard->getRecallerName()));
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
