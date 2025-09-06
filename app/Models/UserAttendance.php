<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class UserAttendance extends Model
{
    protected $fillable = [
        'user_id',
        'check_in',
        'check_out',
        'hours_worked',
        'notes',
        'last_heartbeat_at',
        'status',
    ];

    protected $casts = [
        'check_in' => 'datetime',
        'check_out' => 'datetime',
        'last_heartbeat_at' => 'datetime',
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
