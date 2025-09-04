<?php

namespace App\Events;

use App\Models\LeadIssue;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class IssueResolved implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public LeadIssue $issue)
    {
        //
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('issues.' . $this->issue->id),
            new PrivateChannel('App.Models.User.' . $this->issue->reporter_id),
        ];
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->issue->id,
            'title' => $this->issue->title,
            'status' => $this->issue->status,
            'resolution' => $this->issue->resolution,
            'resolver_id' => $this->issue->resolver_id,
            'resolver_name' => $this->issue->resolver?->name ?? 'A report manager',
            'resolved_at' => $this->issue->resolved_at?->toIso8601String(),
        ];
    }
}