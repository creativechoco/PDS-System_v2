<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;
use App\Notifications\CustomVerifyEmail;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'gender',
        'unit',
        'phone',
        'email',
        'password',
        'type',
        'status',
        'location_assigned',
        'role',
        'is_archive',
        'archived_at',
        'archived_by',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_archive' => 'boolean',
            'archived_at' => 'datetime',
        ];
    }

    /**
     * Send a verification notification with a fresh token, invalidating previous links.
     */
    public function sendEmailVerificationNotification(): void
    {
        $token = Str::random(64);
        $this->forceFill(['email_verification_token' => $token])->save();

        $this->notify(new CustomVerifyEmail($token));
    }

    public function hasSubmittedPds(): bool
    {
    // Only employees can have PDS submissions
    if ($this->role !== 'employee') {
        return false;
    }

    return DB::table('pds_personal_infos')
        ->where('user_id', $this->id)
        ->exists();
}

    public function profile(): HasOne
    {
        return $this->hasOne(UserProfile::class, 'user_id');
    }
}


