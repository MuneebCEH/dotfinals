<?php

namespace App\Notifications;

use App\Models\IssueComment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class IssueCommentAddedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public IssueComment $comment) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail', 'broadcast'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'issue_comment_added',
            'issue_id' => $this->comment->lead_issue_id,
            'title' => $this->comment->issue->title,
            'priority' => $this->comment->issue->priority,
            'lead_id' => $this->comment->issue->lead_id,
            'commenter_name' => $this->comment->author->name,
            'comment_body' => $this->comment->body,
            'url' => url("/issues/{$this->comment->lead_issue_id}"),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("New Comment on Issue: {$this->comment->issue->title}")
            ->line("{$this->comment->author->name} commented on your issue.")
            ->line("Comment: {$this->comment->body}")
            ->action('View Comment', url("/issues/{$this->comment->lead_issue_id}"));
    }

    public function toBroadcast(object $notifiable): array
    {
        return [
            'issue_id' => $this->comment->lead_issue_id,
            'title' => $this->comment->issue->title,
            'commenter_name' => $this->comment->author->name,
            'comment_body' => $this->comment->body,
        ];
    }
}