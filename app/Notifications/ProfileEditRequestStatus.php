<?php

namespace App\Notifications;

use App\Models\ProfileEditRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class ProfileEditRequestStatus extends Notification implements ShouldBroadcastNow
{
    use Queueable;

    public function __construct(public ProfileEditRequest $request)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toArray(object $notifiable): array
    {
        $status = $this->request->status;
        $user = $this->request->user;

        return [
            'title' => 'Edit Profile Request',
            'message' => match ($status) {
                'approved' => 'Your request was approved. You can now update your profile.',
                'rejected' => 'Your request has been rejected.',
                default => 'Your request has been updated.',
            },
            'status' => $status,
            'request_id' => $this->request->id,
            'user_id' => $user?->id,
            'name' => $user?->name,
            'link' => route('profile.edit'),
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}
