<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeadUser extends Model
{
    protected $fillable = [
        'user_id',
        'lead_id',
        'assigned_by',
        'is_primary',
    ];
}
