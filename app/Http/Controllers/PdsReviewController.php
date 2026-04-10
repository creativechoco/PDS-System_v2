<?php

namespace App\Http\Controllers;

use App\Models\PdsRejection;
use App\Models\PdsSubmission;
use App\Notifications\PdsStatusUpdated;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class PdsReviewController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status');
        $submissions = $this->mappedSubmissions();

        return view('pds-form', compact('submissions', 'status'));
    }

    public function latest(): \Illuminate\Http\JsonResponse
    {
        return response()->json($this->mappedSubmissions());
    }

    private function mappedSubmissions(): array
    {
        return PdsSubmission::with('user')
            ->orderBy('submitted', 'desc')
            ->get()
            ->map(function ($submission) {
                $avatar = $this->avatarUrl($submission->user?->profile?->profile);

                return [
                    'id' => $submission->id,
                    'key' => 'pds-' . $submission->id,
                    'user_id' => $submission->user_id,
                    'name' => $submission->name ?? 'Unknown',
                    'avatar' => $avatar,
                    'unit' => $submission->unit ?? '—',
                    'email' => $submission->email ?? '—',
                    'type' => $submission->type ?? 'Permanent Employee',
                    'status' => $submission->status ?? 'Pending',
                    'status_key' => strtolower($submission->status ?? 'pending'),
                    'submitted_at' => $submission->submitted ? $submission->submitted->format('M d, Y • g:i A') : '—',
                ];
            })
            ->toArray();
    }

    public function updateStatus(Request $request, $id)
    {
        $data = $request->validate([
            'status' => 'required|in:Pending,Approved,Rejected',
            'note' => 'nullable|string|max:2000',
            'highlighted_sections' => 'nullable|array',
            'highlighted_sections.*' => 'string',
        ]);

        $submission = PdsSubmission::findOrFail($id);
        $submission->status = $data['status'];

        // Reset approval dismissal so modals can surface on any new decision.
        // If approved: ensure prior dismissal doesn't suppress the modal.
        // If not approved: clear dismissal for future approvals.
        $submission->approval_dismissed_at = null;

        $submission->save();

        if ($submission->status === 'Rejected' && $submission->user_id) {
            PdsRejection::updateOrCreate(
                ['user_id' => $submission->user_id],
                [
                    'name' => $submission->name ?? $submission->user?->name ?? 'Unknown',
                    'status' => 'Rejected',
                    'notes' => $data['note'] ?? null,
                    'highlighted_sections' => $data['highlighted_sections'] ?? null,
                ]
            );
        } elseif ($submission->user_id) {
            PdsRejection::where('user_id', $submission->user_id)->delete();
        }

        if ($submission->user) {
            $noteToSend = $data['note'] ?? null;

            if (!$noteToSend && $submission->status === 'Rejected') {
                $noteToSend = PdsRejection::where('user_id', $submission->user_id)->value('notes');
            }

            Notification::send($submission->user, new PdsStatusUpdated($submission, $noteToSend));
            $this->trimNotificationHistory($submission->user);
        }

        $employee = $submission->user;
        $activityPhrase = match ($submission->status) {
            'Approved' => "Approved the PDS submission.",
            'Rejected' => "Rejected the PDS submission." . ($data['note'] ? " Reason: {$data['note']}" : ''),
            'Pending'  => "Set the PDS submission back to Pending.",
            default    => "Updated PDS status to {$submission->status}.",
        };

        ActivityLogger::log(
            'pds_status',
            $activityPhrase,
            [
                'id'    => $employee?->id,
                'name'  => $submission->name,
                'email' => $submission->email ?? $employee?->email,
                'type'  => $submission->type ?? $employee?->type,
                'unit'  => $submission->unit ?? $employee?->unit,
            ],
            ['pds_status' => $submission->status, 'pds_id' => $submission->id]
        );

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully',
            'submission' => [
                'id' => $submission->id,
                'status' => $submission->status,
                'status_key' => strtolower($submission->status),
                'note' => $data['note'] ?? null,
            ],
        ]);
    }

    private function trimNotificationHistory($notifiable, int $limit = 20): void
    {
        $query = $notifiable->notifications()
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->skip($limit);

        do {
            $excessIds = $query->take(500)->pluck('id');
            if ($excessIds->isEmpty()) {
                break;
            }
            $notifiable->notifications()->whereIn('id', $excessIds)->delete();
        } while ($excessIds->count() === 500);
    }
}
