<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\LeadIssue;
use App\Models\User;
use App\Notifications\IssueCreatedNotification;
use App\Notifications\IssueResolvedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LeadIssueController extends Controller
{
    public function showForUser(LeadIssue $issue)
    {
        // $this->authorize('view', $issue);

        auth()->user()->unreadNotifications
            ->where('data.issue_id', $issue->id)
            ->markAsRead();

        $issue->load(['comments.author', 'comments.attachments', 'attachments']);

        return view('leads.issue-responses', compact('issue'));
    }

    public function index(Request $request)
    {
        // $this->authorize('viewAny', LeadIssue::class);

        $query = LeadIssue::with(['lead', 'reporter'])->latest();

        if ($s = $request->get('status')) $query->where('status', $s);
        if ($p = $request->get('priority')) $query->where('priority', $p);
        if ($q = trim((string) $request->get('q'))) {
            $query->where(function ($x) use ($q) {
                $x->where('title', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%");
            });
        }

        $counts = LeadIssue::selectRaw('status, COUNT(*) as c')
            ->groupBy('status')
            ->pluck('c', 'status');

        $issues = $query->paginate(20)->withQueryString();

        return view('issues.index', compact('issues', 'counts'));
    }

    public function show(LeadIssue $issue)
    {
        // $this->authorize('view', $issue);

        $issue->load(['reporter', 'lead']);
        $comments = $issue->comments()->with('author')->latest()->take(50)->get()->reverse();

        return view('issues.show', compact('issue', 'comments'));
    }


    public function store(Request $request, Lead $lead)
    {
        // $this->authorize('create', [LeadIssue::class, $lead]);

        $data = $request->validate([
            'title' => 'required|string|max:160',
            'priority' => 'nullable|in:low,normal,high,urgent',
            'description' => 'required|string|max:5000',
            'attachments.*' => 'file|max:10240',
        ]);

        $issue = LeadIssue::create([
            'lead_id'     => $lead->id,
            'reporter_id' => $request->user()->id,
            'title'       => $data['title'],
            'priority'    => $data['priority'] ?? 'normal',
            'description' => $data['description'],
            'status'      => 'open',
        ]);

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store("issues/{$lead->id}", 'public');
                $issue->attachments()->create([
                    'user_id'    => $request->user()->id,
                    'file_path'  => $path,
                    'file_name'  => $file->getClientOriginalName(),
                    'file_type'  => $file->getClientMimeType(),
                    'file_size'  => $file->getSize(),
                    'is_solution' => false,
                ]);
            }
        }

        // Keep database notifications (non-broadcast)
        User::whereIn('role', ['report_manager', 'admin'])
            ->each(fn($u) => $u->notify(new IssueCreatedNotification($issue)));

        return back()->with('success', 'Issue submitted. The report team has been notified.');
    }

    public function updateStatus(Request $request, LeadIssue $issue)
    {
        // $this->authorize('update', $issue);

        $data = $request->validate([
            'status' => 'required|in:open,triaged,in_progress,resolved,closed',
            'resolution' => 'nullable|required_if:status,resolved|string|max:5000',
            'solution_files.*' => 'nullable|file|max:10240',
        ]);

        $from = $issue->status;

        if ($data['status'] === 'resolved' && $from !== 'resolved') {
            $issue->resolution = $data['resolution'];
            $issue->resolver_id = $request->user()->id;
            $issue->resolved_at = now();

            if ($issue->reporter) {
                $issue->reporter->notify(new IssueResolvedNotification($issue));
            }
        }

        $issue->status = $data['status'];
        $issue->save();

        if ($request->hasFile('solution_files')) {
            foreach ($request->file('solution_files') as $file) {
                $path = $file->store("issues/{$issue->lead_id}/solutions", 'public');
                $issue->attachments()->create([
                    'user_id'    => $request->user()->id,
                    'file_path'  => $path,
                    'file_name'  => $file->getClientOriginalName(),
                    'file_type'  => $file->getClientMimeType(),
                    'file_size'  => $file->getSize(),
                    'is_solution' => true,
                ]);
            }
        }

        $this->logEvent($issue->id, $request->user()->id, 'status_changed', [
            'from' => $from,
            'to'   => $issue->status,
        ]);

        // Optional: if you still want a DB notification on status change (non-broadcast)
        if ($data['status'] !== 'resolved' && $from !== $data['status'] && $issue->reporter_id !== $request->user()->id) {
            $issue->reporter->notify(new \App\Notifications\StatusUpdatedNotification($issue, $from, $data['status']));
        }

        return back()->with('success', 'Status updated.');
    }

    public function updatePriority(Request $request, LeadIssue $issue)
    {
        // $this->authorize('update', $issue);

        $data = $request->validate([
            'priority' => 'required|in:low,normal,high,urgent',
        ]);

        $issue->update($data);

        $this->logEvent($issue->id, $request->user()->id, 'priority_changed', [
            'to' => $issue->priority,
        ]);

        return back()->with('success', 'Priority updated.');
    }

    protected function logEvent(int $issueId, int $actorId, string $type, array $meta = []): void
    {
        if (!Schema::hasTable('issue_events')) return;

        DB::table('issue_events')->insert([
            'lead_issue_id' => $issueId,
            'actor_id'      => $actorId,
            'type'          => $type,
            'meta'          => $meta ? json_encode($meta) : null,
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);
    }
}
