<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PdsForm5Remark extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'duration',
        'position_title',
        'office_unit',
        'immediate_supervisor',
        'agency_location',
        'accomplishments',
        'duties',
        'signature_path',
        'signature_data',
        'date5',
    ];

    protected $casts = [
        'accomplishments' => 'array',
        'date5' => 'date',
    ];

    /**
     * Get the user that owns the form5 remark.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
