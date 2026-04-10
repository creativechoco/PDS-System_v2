<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PdsRejection extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'status',
        'notes',
        'highlighted_sections',
    ];

    protected $casts = [
        'highlighted_sections' => 'array',
    ];
}
