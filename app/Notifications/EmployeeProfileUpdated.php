<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class EmployeeProfileUpdated extends Notification implements ShouldBroadcastNow
{
    use Queueable;

    public function __construct(public User $user, public array $changedFields)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toArray(object $notifiable): array
    {
        $fields = $this->changedFields;
        $fieldList = $this->formatFieldList($fields);

        return [
            'title' => 'Employee Profile Updated',
            'message' => sprintf('%s updated profile: %s.', $this->user->name, $fieldList),
            'fields' => $fields,
            'user_id' => $this->user->id,
            'name' => $this->user->name,
            'email' => $this->user->email,
            'link' => route('manage-user', ['view_user' => $this->user?->id]),
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }

    private function formatFieldList(array $fields): string
    {
        $fields = array_values(array_filter($fields));
        $count = count($fields);

        if ($count === 0) {
            return 'details';
        }

        if ($count === 1) {
            return $fields[0];
        }

        if ($count === 2) {
            return $fields[0] . ' and ' . $fields[1];
        }

        $last = array_pop($fields);
        return implode(', ', $fields) . ', and ' . $last;
    }
}
