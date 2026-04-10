<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $credentials = $this->only('email', 'password');
        $remember = $this->boolean('remember');
        $email = $this->input('email');

        // Check if email exists in either admin_users or users table
        $emailExists = $this->checkEmailExists($email);

        if (!$emailExists) {
            RateLimiter::hit($this->throttleKey());
            throw ValidationException::withMessages([
                'email' => 'This email address is not registered in our system.',
            ]);
        }

        // Try admin first and ensure role is allowed
        if (Auth::guard('admin')->attempt($credentials, $remember)) {
            $admin = Auth::guard('admin')->user();
            $allowedAdminRoles = ['main admin', 'admin user'];

            // Check if account is active
            if (strtolower($admin->status ?? '') !== 'active') {
                Auth::guard('admin')->logout();
                RateLimiter::hit($this->throttleKey());
                throw ValidationException::withMessages([
                    'email' => 'Your account is inactive. Please contact the administrator.',
                ]);
            }

            if (in_array($admin->role, $allowedAdminRoles, true)) {
                Auth::shouldUse('admin');
                RateLimiter::clear($this->throttleKey());
                return;
            }

            Auth::guard('admin')->logout();
        }

        // Then fallback to normal users and ensure role is employee
        if (Auth::guard('web')->attempt($credentials, $remember)) {
            $user = Auth::guard('web')->user();
            
            // Check if account is active
            if (strtolower($user->status ?? '') !== 'active') {
                Auth::guard('web')->logout();
                RateLimiter::hit($this->throttleKey());
                throw ValidationException::withMessages([
                    'email' => 'Your account is inactive. Please contact the administrator.',
                ]);
            }
            
            if ($user?->role === 'employee') {
                Auth::shouldUse('web');
                RateLimiter::clear($this->throttleKey());
                return;
            }

            Auth::guard('web')->logout();
        }

        RateLimiter::hit($this->throttleKey());

        // Email exists but authentication failed - must be wrong password or wrong role
        throw ValidationException::withMessages([
            'password' => 'The password you entered is incorrect.',
        ]);
    }

    /**
     * Check if email exists in either admin_users or users table
     */
    private function checkEmailExists($email): bool
    {
        // Check in admin_users table
        $adminExists = \DB::table('admin_users')
            ->where('email', $email)
            ->exists();

        // Check in users table  
        $userExists = \DB::table('users')
            ->where('email', $email)
            ->exists();

        return $adminExists || $userExists;
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => 'Too many login attempts. Please try again in ' . $seconds . ' seconds.',
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }
}
