<?php

namespace App\Notifications;

use App\Models\LeadIssue;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class IssueCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public LeadIssue $issue) {}

    public function via(object $notifiable): array
    {
        return ['database','mail'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'issue_created',
            'issue_id' => $this->issue->id,
            'title' => $this->issue->title,
            'priority' => $this->issue->priority,
            'lead_id' => $this->issue->lead_id,
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("New Lead Issue: {$this->issue->title} ({$this->issue->priority})")
            ->line('A new issue has been reported.')
            ->action('Open Issue', url("/issues/{$this->issue->id}"));
    }
}