<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Events\EmailVerifiedBroadcast;
use App\Models\User;
use App\Models\AdminUser;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        $user = $request->user();
        $target = ($user->role ?? null) === 'employee'
            ? '/employee'
            : route('dashboard', absolute: false);

        if (! $this->tokenMatches($request, $user)) {
            return view('auth.verify-invalid');
        }

        if ($user->hasVerifiedEmail()) {
            return redirect()->intended($target.'?verified=1');
        }

        if ($user->markEmailAsVerified()) {
            $user->forceFill(['email_verification_token' => null])->save();
            event(new Verified($user));
            event(new EmailVerifiedBroadcast($user, $target.'?verified=1'));
        }

        return redirect()->intended($target.'?verified=1');
    }

    /**
     * Handle verification link for guests (e.g., opening from email while logged out).
     */
    public function guestVerify(Request $request): \Illuminate\Contracts\View\View|RedirectResponse
    {
        if (! $request->hasValidSignature()) {
            return view('auth.verify-invalid');
        }

        $user = $this->resolveUserFromRequest($request);

        if (! $user) {
            return view('auth.verify-invalid');
        }

        // Validate hash from signed URL matches user's email
        if (! hash_equals((string) $request->route('hash'), sha1($user->getEmailForVerification()))) {
            return view('auth.verify-invalid');
        }

        $target = ($user->role ?? null) === 'employee'
            ? '/employee'
            : route('dashboard', absolute: false);

        if (! $this->tokenMatches($request, $user)) {
            return view('auth.verify-invalid');
        }

        if ($user->hasVerifiedEmail()) {
            return view('auth.verify-success', ['redirect' => $target]);
        }

        if ($user->markEmailAsVerified()) {
            $user->forceFill(['email_verification_token' => null])->save();
            event(new Verified($user));
            event(new EmailVerifiedBroadcast($user, $target));
        }

        // Do not log in this browser; let the original session poll and redirect
        return view('auth.verify-success', ['redirect' => $target]);
    }

    private function resolveUserFromRequest(Request $request): ?\Illuminate\Contracts\Auth\Authenticatable
    {
        $id = $request->route('id');

        // Try web users first
        $user = User::find($id);
        if ($user) {
            return $user;
        }

        // Fallback to admin users
        return AdminUser::find($id);
    }

    private function tokenMatches(Request $request, $user): bool
    {
        $tokenFromLink = (string) $request->query('token', '');
        $currentToken = (string) ($user->email_verification_token ?? '');

        return $tokenFromLink !== '' && $currentToken !== '' && hash_equals($currentToken, $tokenFromLink);
    }
}
