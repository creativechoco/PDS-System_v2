<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Otp;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OtpController extends Controller
{
    public function show(Request $request)
    {
        $pending = $request->session()->get('otp_pending');

        if (! $pending) {
            return redirect()->route('login');
        }

        return view('auth.otp', [
            'email' => $pending['email'] ?? '',
        ]);
    }

    /**
     * Verify the submitted OTP and complete login.
     */
    public function verify(Request $request)
    {
        $pending = $request->session()->get('otp_pending');
        if (! $pending) {
            return redirect()->route('login');
        }

        $request->validate([
            'code' => ['required', 'digits:6'],
        ]);

        $otp = Otp::where('email', $pending['email'] ?? null)
            ->where('code', $request->input('code'))
            ->where('expires_at', '>', Carbon::now())
            ->first();

        if (! $otp) {
            return back()->withErrors(['code' => 'Invalid or expired OTP'])->withInput();
        }

        $guard = $pending['guard'] ?? 'web';
        $userId = $pending['user_id'] ?? null;

        if ($userId) {
            Auth::guard($guard)->loginUsingId($userId);
        }

        // Clear pending state and rotate session
        $request->session()->forget('otp_pending');
        $request->session()->regenerate();

        $redirectTo = $pending['redirect'] ?? route('dashboard', absolute: false);

        return redirect()->intended($redirectTo);
    }

    public function resend(Request $request)
    {
        $pending = $request->session()->get('otp_pending');

        if (! $pending) {
            return response()->json(['message' => 'No OTP session found'], 400);
        }

        $userId = $pending['user_id'] ?? null;
        $guard = $pending['guard'] ?? 'web';

        if (! $userId) {
            return response()->json(['message' => 'Invalid OTP session'], 400);
        }

        // Fetch the user
        $user = Auth::guard($guard)->getProvider()->retrieveById($userId);
        if (! $user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // Generate new OTP
        $code = rand(100000, 999999);
        Otp::updateOrCreate(
            ['email' => $user->email],
            ['code' => $code, 'expires_at' => \Carbon\Carbon::now()->addMinutes(3)]
        );

        // Send OTP via email
        \Mail::raw("Your OTP code is: $code", function ($message) use ($user) {
            $message->to($user->email)->subject('Your OTP Code');
        });

        return response()->json(['message' => 'OTP resent successfully']);
    }

    /**
     * Cancel OTP: clear pending session, log out, and invalidate OTP.
     */
    public function cancel(Request $request)
    {
        $pending = $request->session()->get('otp_pending');

        // Clear session state
        $request->session()->forget('otp_pending');
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Logout both guards to be safe
        Auth::guard('web')->logout();
        Auth::guard('admin')->logout();

        // Remove any stored OTP for this email
        if ($pending && ! empty($pending['email'])) {
            Otp::where('email', $pending['email'])->delete();
        }

        return redirect()->route('login')->with('status', 'Session ended. Please sign in again.');
    }
}
