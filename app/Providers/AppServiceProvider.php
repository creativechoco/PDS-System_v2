<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View; // <-- import View
use Illuminate\Support\Facades\Auth; // <-- import Auth
use App\Models\PdsRejection;
use App\Models\PdsSubmission;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Make $hasSubmittedPds available in all Blade views for employees only
        View::composer('*', function ($view) {
            $user = Auth::user();

            // Check if the user is logged in AND is an employee
            $hasSubmitted = false;
            $hasRejected = false;
            $pdsModalData = null;

            if ($user && $user->role === 'employee') {
                $hasSubmitted = $user->hasSubmittedPds();
                $hasRejected = PdsRejection::where('user_id', $user->id)->exists();

                $latestSubmission = PdsSubmission::where('user_id', $user->id)
                    ->orderByDesc('submitted')
                    ->orderByDesc('id')
                    ->first();

                $latestStatus = strtolower($latestSubmission->status ?? '') ?: null;
                $latestId = $latestSubmission?->id;
                $latestUpdatedAt = $latestSubmission?->updated_at?->getTimestamp();
                $approvalDismissedAt = $latestSubmission?->approval_dismissed_at;

                $dismissedRejection = session('dismissed_rejection', []);
                $dismissedApproval = session('dismissed_approval', []);

                $hasMatchingRejection = $latestId
                    && (int) ($dismissedRejection['id'] ?? null) === (int) $latestId
                    && $latestUpdatedAt
                    && (int) ($dismissedRejection['updated_at'] ?? null) === (int) $latestUpdatedAt;

                $hasMatchingApproval = $latestId
                    && (int) ($dismissedApproval['id'] ?? null) === (int) $latestId
                    && $latestUpdatedAt
                    && (int) ($dismissedApproval['updated_at'] ?? null) === (int) $latestUpdatedAt;

                $isApprovedDismissed = (bool) $approvalDismissedAt;

                $pdsModalData = [
                    'latest_status' => $latestStatus,
                    'latest_id' => $latestId,
                    'latest_updated_at' => $latestUpdatedAt,
                    'show_rejected_modal' => $latestStatus === 'rejected' && $latestId && ! $hasMatchingRejection,
                    'show_approved_modal' => $latestStatus === 'approved' && $latestId && ! $hasMatchingApproval && ! $isApprovedDismissed,
                ];
            }

            $view->with('hasSubmittedPds', $hasSubmitted);
            $view->with('hasRejectedPds', $hasRejected);
            $view->with('pdsModalData', $pdsModalData);
        });

        // In local/dev, generate URLs (including email verification) using the current host
        if (app()->environment('local')) {
            $host = request()->getSchemeAndHttpHost();
            if ($host) {
                URL::forceRootUrl($host);
                URL::forceScheme(request()->getScheme());
            }
        }

        // Force https when secure or production (ngrok)
        if (app()->environment('production') || request()->isSecure()) {
            URL::forceScheme('https');
        }
    }

}
