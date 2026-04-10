<?php

namespace App\Notifications;

use App\Models\ProfileEditRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class ProfileEditRequested extends Notification
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
        $user = $this->request->user;

        return [
            'title' => 'Edit Profile Request',
            'message' => sprintf('%s requested to edit their profile.', $user?->name ?? 'An employee'),
            'request_id' => $this->request->id,
            'user_id' => $user?->id,
            'name' => $user?->name,
            'email' => $user?->email,
            'status' => $this->request->status,
            'link' => route('manage-user', ['view_user' => $user?->id]),
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}
