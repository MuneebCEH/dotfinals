<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IssueComment extends Model
{
    protected $fillable = ['lead_issue_id', 'user_id', 'body'];
    
    /**
     * Get the issue that this comment belongs to.
     */
    public function issue()
    {
        return $this->belongsTo(LeadIssue::class, 'lead_issue_id');
    }
    
    /**
     * Get the user who authored this comment.
     */
    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    
    /**
     * Get the attachments for this comment.
     */
    public function attachments()
    {
        return $this->hasMany(IssueAttachment::class, 'issue_comment_id');
    }
}
