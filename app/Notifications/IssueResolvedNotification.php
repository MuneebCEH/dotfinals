<?php

namespace App\Notifications;

use App\Models\LeadIssue;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class IssueResolvedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public LeadIssue $issue) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail', 'broadcast'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'issue_resolved',
            'issue_id' => $this->issue->id,
            'title' => $this->issue->title,
            'priority' => $this->issue->priority,
            'lead_id' => $this->issue->lead_id,
            'resolver_name' => $this->issue->resolver?->name ?? 'A report manager',
            'resolution' => $this->issue->resolution,
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Issue Resolved: {$this->issue->title}")
            ->line('Your reported issue has been resolved.')
            ->line("Resolution: {$this->issue->resolution}")
            ->action('View Resolution', url("/issues/{$this->issue->id}"));
    }

    public function toBroadcast(object $notifiable): array
    {
        return [
            'issue_id' => $this->issue->id,
            'title' => $this->issue->title,
            'resolver_name' => $this->issue->resolver?->name ?? 'A report manager',
        ];
    }
}