<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmailVerificationNotificationController extends Controller
{
    /**
     * Send a new email verification notification.
     */
    public function store(Request $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard', absolute: false));
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'verification-link-sent');
    }

    /**
     * Allow user to update their email before verifying, then resend link.
     */
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $request->validate([
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
        ]);

        // If the email is unchanged, just resend the verification link
        if (strcasecmp($user->email, $request->input('email')) === 0) {
            $user->sendEmailVerificationNotification();
            return back()->with('status', 'verification-link-sent');
        }

        $user->forceFill([
            'email' => $request->input('email'),
            'email_verified_at' => null,
        ])->save();

        // Refresh session to reflect new email
        $request->session()->put('email', $user->email);

        $user->sendEmailVerificationNotification();

        return back()->with('status', 'verification-email-updated');
    }

    /**
     * Check verification status (used for polling auto-redirect).
     */
    public function status(Request $request)
    {
        // Prefer web guard user (employee) if available
        $user = Auth::guard('web')->user() ?? $request->user();

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        // Refresh to pick up verification done in another browser/device
        $user->refresh();

        $verified = $user?->hasVerifiedEmail();
        $role = strtolower($user->role ?? '');
        $target = $role === 'employee' ? '/employee' : route('dashboard', absolute: false);

        return response()->json([
            'verified' => $verified,
            'redirect' => $verified ? $target : null,
        ]);
    }
}
