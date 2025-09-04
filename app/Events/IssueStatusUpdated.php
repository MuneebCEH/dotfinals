<?php

// app/Events/IssueStatusUpdated.php
namespace App\Events;

use App\Models\LeadIssue;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class IssueStatusUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public function __construct(public LeadIssue $issue) {}
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('issues.' . $this->issue->id),       // reporter + managers
            new \Illuminate\Broadcasting\PresenceChannel('report-managers') // managers board summary
        ];
    }
    public function broadcastAs(): string
    {
        return 'issue.status.updated';
    }
    public function broadcastWith(): array
    {
        return ['id' => $this->issue->id, 'status' => $this->issue->status];
    }
}
