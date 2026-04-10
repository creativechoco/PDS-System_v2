<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Support\Facades\Route;

class PdsSubmitted extends Notification implements ShouldBroadcast
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
            'title' => 'PDS Submitted',
            'message' => sprintf('%s submitted their PDS.', $this->user->name),
            'kind' => 'pds_submitted',
            'name' => $this->user->name,
            'user_id' => $this->user->id,
            'email' => $this->user->email,
            'role' => $this->user->role,
            'type' => $this->user->type,
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
