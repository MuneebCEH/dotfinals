<?php

namespace App\Events;

use App\Models\IssueComment;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class IssueCommentAdded implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    
    public function __construct(public IssueComment $comment) 
    {
        // Eager load attachments for broadcasting
        $this->comment->load('attachments');
    }
    
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('issues.' . $this->comment->lead_issue_id), // reporter + managers
        ];
    }
    
    public function broadcastAs(): string
    {
        return 'issue.comment.added';
    }
    
    public function broadcastWith(): array
    {
        $attachments = [];
        
        foreach ($this->comment->attachments as $attachment) {
            $attachments[] = [
                'id' => $attachment->id,
                'file_name' => $attachment->file_name,
                'file_type' => $attachment->file_type,
                'file_size' => $attachment->file_size,
                'url' => Storage::disk('public')->url($attachment->file_path)
            ];
        }
        
        return [
            'id' => $this->comment->id,
            'body' => $this->comment->body,
            'user' => ['id' => $this->comment->author->id, 'name' => $this->comment->author->name],
            'issue_id' => $this->comment->lead_issue_id,
            'created_at' => $this->comment->created_at->toIso8601String(),
            'attachments' => $attachments
        ];
    }
}
