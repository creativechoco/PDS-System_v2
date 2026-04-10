<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;

class EmployeeController extends Controller
{
    public function dashboard()
    {
        $userId = auth()->id();

        $pendingCount = \App\Models\PdsSubmission::where('user_id', $userId)
            ->where(function ($query) {
                $query->whereNull('status')->orWhereRaw('LOWER(status) = ?', ['pending']);
            })
            ->count();

        $approvedCount = \App\Models\PdsSubmission::where('user_id', $userId)
            ->whereRaw('LOWER(status) = ?', ['approved'])
            ->count();

        $rejectedCount = \App\Models\PdsSubmission::where('user_id', $userId)
            ->whereRaw('LOWER(status) = ?', ['rejected'])
            ->count();

        $latestSubmission = \App\Models\PdsSubmission::where('user_id', $userId)
            ->orderByDesc('submitted')
            ->orderByDesc('id')
            ->first();

        $latestEditRequest = \App\Models\ProfileEditRequest::where('user_id', $userId)
            ->latest()
            ->first();

        $latestSubmissionStatus = strtolower($latestSubmission->status ?? '') ?: null;
        $latestRequestStatus = strtolower($latestEditRequest->status ?? '') ?: null;

        $hasSubmission = (bool) $latestSubmission;
        $hasApprovedSubmission = $latestSubmissionStatus === 'approved';
        $editAllowed = !$hasApprovedSubmission || $latestRequestStatus === 'approved';

        $pdsModalData = $this->buildPdsModalData($userId);

        $stats = [
            'overview' => [
                ['label' => 'Pending', 'value' => $pendingCount, 'accent' => 'from-amber-400 to-orange-400'],
                ['label' => 'Approved', 'value' => $approvedCount, 'accent' => 'from-emerald-400 to-teal-500'],
                ['label' => 'Rejected', 'value' => $rejectedCount, 'accent' => 'from-rose-400 to-pink-500'],
            ],
            'pds' => [
                'has_submission' => $hasSubmission,
                'latest_status' => $latestSubmissionStatus,
                'edit_allowed' => $editAllowed,
                'edit_request_status' => $latestRequestStatus,
            ],
        ];

        return view('employee_dashboard.employee_dashboard', compact('stats', 'pdsModalData'));
    }

    public function dismissRejection(Request $request)
    {
        $submissionId = $request->integer('submission_id');
        $submissionUpdatedAt = $request->integer('submission_updated_at');

        if ($submissionId) {
            session([
                'dismissed_rejection' => [
                    'id' => $submissionId,
                    'updated_at' => $submissionUpdatedAt,
                ],
            ]);
        }

        return back();
    }

    public function dismissApproval(Request $request)
    {
        $submissionId = $request->integer('submission_id');
        $userId = auth()->id();

        if ($submissionId && $userId) {
            $submission = \App\Models\PdsSubmission::where('id', $submissionId)
                ->where('user_id', $userId)
                ->first();

            if ($submission && strtolower($submission->status ?? '') === 'approved') {
                $submission->update([
                    'approval_dismissed_at' => Carbon::now(),
                ]);

                session([
                    'dismissed_approval' => [
                        'id' => $submissionId,
                        'updated_at' => $submission->updated_at?->getTimestamp(),
                    ],
                ]);
            }
        }

        return $request->wantsJson()
            ? response()->json(['ok' => true])
            : back();
    }

    public function latestPdsStatus(Request $request)
    {
        $userId = auth()->id();

        if (! $userId) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $data = $this->buildPdsModalData($userId);

        return response()->json($data ?? []);
    }

    private function buildPdsModalData(int $userId): ?array
    {
        $latestSubmission = \App\Models\PdsSubmission::where('user_id', $userId)
            ->orderByDesc('submitted')
            ->orderByDesc('id')
            ->first();

        if (! $latestSubmission) {
            return null;
        }

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

        return [
            'latest_status' => $latestStatus,
            'latest_id' => $latestId,
            'latest_updated_at' => $latestUpdatedAt,
            'show_rejected_modal' => $latestStatus === 'rejected' && $latestId && ! $hasMatchingRejection,
            'show_approved_modal' => $latestStatus === 'approved' && $latestId && ! $hasMatchingApproval && ! $isApprovedDismissed,
        ];
    }
}