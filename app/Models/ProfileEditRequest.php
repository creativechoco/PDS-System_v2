<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProfileEditRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'status',
        'remarks',
        'reviewed_by',
        'reviewed_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
