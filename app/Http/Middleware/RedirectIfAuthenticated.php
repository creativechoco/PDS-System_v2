<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    /**
     * If the user is already authenticated on any guard, redirect them to the right dashboard.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::guard('admin')->check()) {
            return redirect()->route('dashboard');
        }

        if (Auth::guard('web')->check()) {
            $user = Auth::guard('web')->user();

            if ($user?->role === 'employee') {
                return redirect()->route('employee.dashboard');
            }

            return redirect()->route('dashboard');
        }

        return $next($request);
    }
}
