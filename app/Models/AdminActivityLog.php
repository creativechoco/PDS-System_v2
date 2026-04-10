<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminActivityLog extends Model
{
    protected $table = 'admin_activity_logs';

    protected $fillable = [
        'admin_id',
        'admin_name',
        'admin_role',
        'action_type',
        'activity',
        'target_user_id',
        'target_user_name',
        'target_user_email',
        'target_user_type',
        'target_user_unit',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function targetUser()
    {
        return $this->belongsTo(User::class, 'target_user_id');
    }
}
