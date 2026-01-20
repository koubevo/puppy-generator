<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UpdateLog extends Model
{
    protected $fillable = [
        'provider',
        'transport',
        'status',
        'payload',
        'error_message',
        'sent_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'sent_at' => 'datetime',
    ];
}
