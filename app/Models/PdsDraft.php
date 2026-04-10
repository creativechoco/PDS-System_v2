<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PdsDraft extends Model
{
    protected $fillable = [
        'user_id', // <-- ADD THIS
        'data'
    ];

    protected $casts = [
        'data' => 'array'
    ];
}