<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\OtpController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('register', [RegisteredUserController::class, 'create'])
        ->name('register');

    Route::post('register', [RegisteredUserController::class, 'store']);

    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    Route::get('otp', [OtpController::class, 'show'])->name('otp.show');
    Route::post('otp', [OtpController::class, 'verify'])->name('otp.verify');
    Route::post('otp/cancel', [OtpController::class, 'cancel'])->name('otp.cancel');

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');

    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
        ->name('password.email');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');

    Route::post('reset-password', [NewPasswordController::class, 'store'])
        ->name('password.store');
});

Route::get('verify-email/{id}/{hash}', [VerifyEmailController::class, 'guestVerify'])
    ->middleware(['signed', 'throttle:6,1'])
    ->name('verification.verify');

Route::middleware('auth')->group(function () {
    Route::get('verify-email', EmailVerificationPromptController::class)
        ->name('verification.notice');

    // Limit resend to once per 3 minutes (1 attempt / 3 minutes)
    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:1,3')
        ->name('verification.send');

    Route::post('email/verification-update', [EmailVerificationNotificationController::class, 'update'])
        ->middleware('throttle:30,1')
        ->name('verification.update');

    Route::get('email/verification-status', [EmailVerificationNotificationController::class, 'status'])
        ->middleware('throttle:6,1')
        ->name('verification.status');

    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');

    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

    Route::put('password', [PasswordController::class, 'update'])->name('password.update');

});

Route::middleware(['auth','role:employee'])->group(function () {
    Route::get('/employee', [EmployeeController::class, 'dashboard'])->name('employee.dashboard');
});

// Allow logout for both admin and web guards
Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth:admin,web')
    ->name('logout');


