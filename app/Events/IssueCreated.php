<?php

namespace App\Events;

use App\Models\LeadIssue;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class IssueCreated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public LeadIssue $issue)
    {
        $this->issue->loadMissing('reporter');
    }

    public function broadcastOn(): array
    {
        return [new PresenceChannel('report-managers')];
    }

    public function broadcastAs(): string
    {
        return 'issue.created';
    }

    public function broadcastWith(): array
    {
        return [
            'id'        => $this->issue->id,
            'lead_id'   => $this->issue->lead_id,
            'title'     => $this->issue->title,
            'priority'  => $this->issue->priority,
            'status'    => $this->issue->status,
            'reporter'  => [
                'id'   => $this->issue->reporter?->id,
                'name' => $this->issue->reporter?->name,
            ],
            'created_at' => $this->issue->created_at?->toIso8601String(),
        ];
    }
}
