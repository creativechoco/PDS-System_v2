<?php

namespace App\Notifications;

use App\Models\PdsSubmission;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;

class PdsStatusUpdated extends Notification implements ShouldBroadcastNow
{
    use Queueable;

    public function __construct(public PdsSubmission $submission, public ?string $note = null)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toArray(object $notifiable): array
    {
        $status = $this->submission->status ?? 'Pending';

        $message = sprintf("Your PDS was marked %s.", $status);

        if ($status === 'Rejected' && $this->note) {
            $message .= "\nNote: " . $this->note;
        }

        return [
            'title' => 'PDS Submission Updates',
            'message' => $message,
            'status' => $status,
            'note' => ($status === 'Rejected') ? $this->note : null,
            'submission_id' => $this->submission->id,
            'updated_at_ts' => $this->submission->updated_at?->getTimestamp(),
            // Send employees directly to their PDS form to view or continue editing
            'link' => route('pds.form1'),
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}
