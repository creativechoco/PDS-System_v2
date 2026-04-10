<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;

class EmployeeInfoUpdated extends Notification implements ShouldBroadcastNow
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
        $verb = count($fields) > 1 ? 'were' : 'was';

        return [
            'title' => 'Profile Updated',
            'message' => sprintf('Your Profile %s %s changed.', $fieldList, $verb),
            'fields' => $fields,
            'user_id' => $this->user->id,
            'link' => route('profile.edit'),
        ];
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

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}
