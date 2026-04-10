<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class EmployeeRegistered extends Notification implements ShouldBroadcast
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
            'title' => 'New Employee Registered',
            'message' => sprintf('%s has just registered.', $this->user->name),
            'name' => $this->user->name,
            'user_id' => $this->user->id,
            'email' => $this->user->email,
            'role' => $this->user->role,
            'type' => $this->user->type,
            'link' => route('manage-user', ['view_user' => $this->user?->id]),
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }

}
