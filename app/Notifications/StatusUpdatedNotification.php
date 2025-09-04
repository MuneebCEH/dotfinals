<?php

namespace App\Notifications;

use App\Models\LeadIssue;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StatusUpdatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public LeadIssue $issue,
        public string $oldStatus,
        public string $newStatus
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail', 'broadcast'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'issue_status_updated',
            'issue_id' => $this->issue->id,
            'title' => $this->issue->title,
            'priority' => $this->issue->priority,
            'lead_id' => $this->issue->lead_id,
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
            'updater_name' => $this->issue->resolver?->name ?? 'A report manager',
            'url' => url("/issues/{$this->issue->id}"),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $oldStatus = ucwords(str_replace('_', ' ', $this->oldStatus));
        $newStatus = ucwords(str_replace('_', ' ', $this->newStatus));
        
        return (new MailMessage)
            ->subject("Issue Status Updated: {$this->issue->title}")
            ->line("The status of your issue has been updated.")
            ->line("Status changed from {$oldStatus} to {$newStatus}")
            ->action('View Issue', url("/issues/{$this->issue->id}"));
    }

    public function toBroadcast(object $notifiable): array
    {
        return [
            'issue_id' => $this->issue->id,
            'title' => $this->issue->title,
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
            'updater_name' => $this->issue->resolver?->name ?? 'A report manager',
        ];
    }
}