<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Callback extends Model
{
    protected $fillable = ['lead_id', 'user_id', 'scheduled_at', 'notes', 'status'];

    protected $casts = ['scheduled_at' => 'datetime'];
    

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }
    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
