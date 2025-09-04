<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IssueAttachment extends Model
{
    protected $fillable = [
        'lead_issue_id',
        'issue_comment_id',
        'user_id',
        'file_path',
        'file_name',
        'file_type',
        'file_size',
        'is_solution'
    ];

    /**
     * Get the issue that this attachment belongs to.
     */
    public function issue()
    {
        return $this->belongsTo(LeadIssue::class, 'lead_issue_id');
    }

    /**
     * Get the user who uploaded this attachment.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Get the comment that this attachment belongs to (if any).
     */
    public function comment()
    {
        return $this->belongsTo(IssueComment::class, 'issue_comment_id');
    }
}