<?php

// app/Http/Controllers/IssueCommentController.php
namespace App\Http\Controllers;

use App\Events\IssueCommentAdded;
use App\Models\IssueAttachment;
use App\Models\IssueComment;
use App\Models\LeadIssue;
use App\Notifications\IssueCommentAddedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class IssueCommentController extends Controller
{
    public function store(Request $request, LeadIssue $issue)
    {
        // $this->authorize('view', $issue); // reporter or manager/admin

        $data = $request->validate([
            'body' => 'required|string|max:3000',
            'comment_attachments' => 'nullable|array',
            'comment_attachments.*' => 'file|max:10240', // 10MB max per file
        ]);
        
        $comment = IssueComment::create([
            'lead_issue_id' => $issue->id,
            'user_id' => $request->user()->id,
            'body' => $data['body'],
        ]);

        // Handle attachments
        if ($request->hasFile('comment_attachments')) {
            foreach ($request->file('comment_attachments') as $file) {
                $path = $file->store('issue-attachments', 'public');
                
                IssueAttachment::create([
                    'lead_issue_id' => $issue->id,
                    'issue_comment_id' => $comment->id,
                    'user_id' => $request->user()->id,
                    'file_path' => $path,
                    'file_name' => $file->getClientOriginalName(),
                    'file_type' => $file->getClientMimeType(),
                    'file_size' => $file->getSize(),
                    'is_solution' => false,
                ]);
            }
        }

        // audit
        DB::table('issue_events')->insert([
            'lead_issue_id' => $issue->id,
            'actor_id' => $request->user()->id,
            'type' => 'comment_added',
            'meta' => json_encode(['comment_id' => $comment->id]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Notify the issue reporter if the comment is from a report manager or admin
        $currentUser = $request->user();
        if (in_array($currentUser->role, ['report_manager', 'admin']) && $issue->reporter_id !== $currentUser->id) {
            $issue->reporter->notify(new IssueCommentAddedNotification($comment));
        }

        // realtime
        event(new IssueCommentAdded($comment));

        return back()->with('success', 'Comment posted.');
    }
}
