<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeadIssue extends Model
{
    protected $fillable = [
        'lead_id',
        'reporter_id',
        'status',
        'priority',
        'title',
        'description',
        'attachments',
        'resolution',
        'resolved_at',
        'resolver_id'
    ];
    protected $casts = [
        'attachments' => 'array',
        'resolved_at' => 'datetime'
    ];

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }
    
    public function reporter()
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }
    
    public function resolver()
    {
        return $this->belongsTo(User::class, 'resolver_id');
    }

    public function comments()
    {
        return $this->hasMany(\App\Models\IssueComment::class, 'lead_issue_id');
    }
    
    public function attachments()
    {
        return $this->hasMany(IssueAttachment::class, 'lead_issue_id');
    }
    
    public function solutions()
    {
        return $this->hasMany(IssueAttachment::class, 'lead_issue_id')->where('is_solution', true);
    }
}
