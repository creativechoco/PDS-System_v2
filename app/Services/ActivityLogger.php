<?php

namespace App\Services;

use App\Models\AdminActivityLog;
use App\Models\AdminUser;
use Illuminate\Support\Facades\Auth;

class ActivityLogger
{
    public static function log(
        string $actionType,
        string $activity,
        array $targetUser = [],
        array $meta = []
    ): void {
        $admin = Auth::guard('admin')->user();

        if (! $admin) {
            return;
        }

        AdminActivityLog::create([
            'admin_id'         => $admin->id,
            'admin_name'       => $admin->name,
            'admin_role'       => $admin->role ?? 'admin user',
            'action_type'      => $actionType,
            'activity'         => $activity,
            'target_user_id'   => $targetUser['id'] ?? null,
            'target_user_name' => $targetUser['name'] ?? null,
            'target_user_email'=> $targetUser['email'] ?? null,
            'target_user_type' => $targetUser['type'] ?? null,
            'target_user_unit' => $targetUser['unit'] ?? null,
            'meta'             => ! empty($meta) ? $meta : null,
        ]);

        $total = AdminActivityLog::count();
        if ($total > 30) {
            $excess = $total - 30;
            $ids = AdminActivityLog::orderBy('created_at', 'asc')
                ->limit($excess)
                ->pluck('id');

            AdminActivityLog::whereIn('id', $ids)->delete();
        }
    }
}
