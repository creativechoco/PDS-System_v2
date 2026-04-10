<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Route;

class PdsResubmitted extends Notification implements ShouldBroadcast
{
    use Queueable;

    public function __construct(public User $user)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'PDS Re-submitted',
            'message' => sprintf('%s updated and resubmitted their PDS.', $this->user->name),
            'kind' => 'pds_resubmitted',
            'name' => $this->user->name,
            'user_id' => $this->user->id,
            'email' => $this->user->email,
            'role' => $this->user->role,
            'type' => $this->user->type,
            // Link to admin PDS review page (fallback to preview route if needed) with highlight
            'link' => Route::has('pds.form')
                ? route('pds.form', ['highlight' => $this->user?->id])
                : route('pds.preview.admin', ['user' => $this->user?->id]),
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}
