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
    
        $user = $request->user();
    
        $query = LeadIssue::with(['lead', 'reporter'])
            ->latest();
    
        // ðŸ”‘ Only show issues assigned to logged-in report manager
        if ($user->role === 'report_manager') {
            $query->where('resolver_id', $user->id);
        }
    
        if ($s = $request->get('status')) {
            $query->where('status', $s);
        }
    
        if ($p = $request->get('priority')) {
            $query->where('priority', $p);
        }
    
        if ($q = trim((string) $request->get('q'))) {
            $query->where(function ($x) use ($q) {
                $x->where('title', 'like', "%{$q}%")
                  ->orWhere('description', 'like', "%{$q}%");
            });
        }
    
        // Count for tabs (also restricted to this manager)
        $counts = LeadIssue::selectRaw('status, COUNT(*) as c')
            ->when($user->role === 'report_manager', fn($q) => $q->where('resolver_id', $user->id))
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


<<<<<<< HEAD
    // public function store(Request $request, \App\Models\Lead $lead)
    // {
    //     $data = $request->validate([
    //         'title'         => 'required|string|max:160',
    //         'priority'      => 'nullable|in:low,normal,high,urgent',
    //         'description'   => 'required|string|max:5000',
    //         'attachments.*' => 'file|max:10240',
    //     ]);
    
    //     // Karachi day window (attendance is stored in UTC)
    //     $tz        = 'Asia/Karachi';
    //     $startUtc  = now($tz)->startOfDay()->setTimezone('UTC');
    //     $endUtc    = now($tz)->endOfDay()->setTimezone('UTC');
    //     $freshCut  = now()->subMinutes(3); // consider “online” if heartbeat within last 3 minutes
    
    //     // 1) All ACTIVE report managers (status='in' today + fresh heartbeat)
    //     $activeRMs = User::query()
    //         ->where('role', 'report_manager')
    //         ->whereExists(function ($q) use ($startUtc, $endUtc, $freshCut) {
    //             $q->select(DB::raw(1))
    //               ->from('user_attendances as ua')
    //               ->whereColumn('ua.user_id', 'users.id')
    //               ->whereBetween('ua.check_in', [$startUtc, $endUtc])
    //               ->where('ua.status', 'in')                          // open session by status
    //               ->where('ua.last_heartbeat_at', '>=', $freshCut);   // online recently
    //         })
    //         ->pluck('id');
    
    //     $resolverId = null;
    
    //     if ($activeRMs->isNotEmpty()) {
    //         // 2) Count open issues per active RM
    //         $counts = LeadIssue::select('resolver_id', DB::raw('COUNT(*) as open_count'))
    //             ->whereIn('resolver_id', $activeRMs)
    //             ->where('status', 'open')
    //             ->groupBy('resolver_id')
    //             ->pluck('open_count', 'resolver_id');
    
    //         // 3) Pick RM with fewest open issues
    //         $resolverId = $activeRMs->sortBy(fn($id) => $counts[$id] ?? 0)->first();
    
    //         // Safety: ensure picked user is still a report_manager
    //         if (!User::whereKey($resolverId)->where('role', 'report_manager')->exists()) {
    //             $resolverId = null;
    //         }
    //     }
    
    //     // 4) Create issue
    //     $issue = LeadIssue::create([
    //         'lead_id'     => $lead->id,
    //         'reporter_id' => $request->user()->id,
    //         'title'       => $data['title'],
    //         'priority'    => $data['priority'] ?? 'normal',
    //         'description' => $data['description'],
    //         'status'      => 'open',
    //         'resolver_id' => $resolverId,
    //     ]);
    
    //     // 5) Attach files
    //     if ($request->hasFile('attachments')) {
    //         foreach ($request->file('attachments') as $file) {
    //             $path = $file->store("issues/{$lead->id}", 'public');
    //             $issue->attachments()->create([
    //                 'user_id'     => $request->user()->id,
    //                 'file_path'   => $path,
    //                 'file_name'   => $file->getClientOriginalName(),
    //                 'file_type'   => $file->getClientMimeType(),
    //                 'file_size'   => $file->getSize(),
    //                 'is_solution' => false,
    //             ]);
    //         }
    //     }
    
    //     // 6) Notify resolver (or fallback to all RMs)
    //     if ($resolverId) {
    //         User::whereKey($resolverId)
    //             ->each(fn ($u) => $u->notify(new IssueCreatedNotification($issue)));
    //     } else {
    //         User::where('role', 'report_manager')
    //             ->each(fn ($u) => $u->notify(new IssueCreatedNotification($issue)));
    //     }
    
    //     return back()->with(
    //         'success',
    //         $resolverId
    //             ? 'Issue submitted and assigned to the least-loaded active report manager.'
    //             : 'Issue submitted. No active report manager detected; all report managers were notified.'
    //     );
    // }
    
    
    public function store(Request $request, \App\Models\Lead $lead)
{
    $data = $request->validate([
        'title'         => 'required|string|max:160',
        'priority'      => 'nullable|in:low,normal,high,urgent',
        'description'   => 'required|string|max:5000',
        'attachments.*' => 'file|max:10240',
    ]);

    // Find all report managers first; if none, bail out (do NOT create a report)
    $allRMs = User::query()
        ->where('role', 'report_manager')
        ->pluck('id');

    if ($allRMs->isEmpty()) {
        return back()
            ->withInput()
            ->with('error', 'Issue not created — no Report Managers are configured. Please contact an administrator.');
    }

    // Karachi day window (attendance stored in UTC)
    $tz       = 'Asia/Karachi';
    $startUtc = now($tz)->startOfDay()->setTimezone('UTC');
    $endUtc   = now($tz)->endOfDay()->setTimezone('UTC');
    $freshCut = now()->subMinutes(3); // "online" if heartbeat within last 3 minutes

    // ONLINE report managers (active session + fresh heartbeat)
    $activeRMs = User::query()
        ->where('role', 'report_manager')
        ->whereExists(function ($q) use ($startUtc, $endUtc, $freshCut) {
            $q->select(DB::raw(1))
              ->from('user_attendances as ua')
              ->whereColumn('ua.user_id', 'users.id')
              ->whereBetween('ua.check_in', [$startUtc, $endUtc])
              ->where('ua.status', 'in')
              ->where('ua.last_heartbeat_at', '>=', $freshCut);
        })
        ->pluck('id');

    // Open issue counts per RM (for ALL RMs; default 0 when missing)
    $openCounts = LeadIssue::select('resolver_id', DB::raw('COUNT(*) as open_count'))
        ->whereIn('resolver_id', $allRMs)
        ->where('status', 'open')
        ->groupBy('resolver_id')
        ->pluck('open_count', 'resolver_id');

    // Helper: pick id with the fewest open issues (default 0)
    $pickLeastLoaded = function (\Illuminate\Support\Collection $ids) use ($openCounts) {
        if ($ids->isEmpty()) return null;
        return $ids->sortBy(fn ($id) => $openCounts[$id] ?? 0)->first();
    };

    // 1) Prefer ONLINE RM with the fewest open issues
    $resolverId = $pickLeastLoaded($activeRMs);

    // 2) If none online, pick OFFLINE RM (fewest open issues)
    if (!$resolverId) {
        $offlineRMs = $allRMs->diff($activeRMs);
        $resolverId = $pickLeastLoaded($offlineRMs);
    }

    // 3) Create the issue (assigned to chosen RM; $resolverId is guaranteed since $allRMs not empty)
    $issue = LeadIssue::create([
        'lead_id'     => $lead->id,
        'reporter_id' => $request->user()->id,
        'title'       => $data['title'],
        'priority'    => $data['priority'] ?? 'normal',
        'description' => $data['description'],
        'status'      => 'open',
        'resolver_id' => $resolverId,
    ]);

    // 4) Attach files (if any)
    if ($request->hasFile('attachments')) {
        foreach ($request->file('attachments') as $file) {
            $path = $file->store("issues/{$lead->id}", 'public');
            $issue->attachments()->create([
                'user_id'     => $request->user()->id,
                'file_path'   => $path,
                'file_name'   => $file->getClientOriginalName(),
                'file_type'   => $file->getClientMimeType(),
                'file_size'   => $file->getSize(),
                'is_solution' => false,
            ]);
        }
    }

    // 5) Notify the assigned RM
    User::whereKey($resolverId)
        ->each(fn ($u) => $u->notify(new IssueCreatedNotification($issue)));

    return back()->with('success', 'Issue submitted and assigned to the least-loaded report manager.');
}



=======
    public function store(Request $request, \App\Models\Lead $lead)
    {
        $data = $request->validate([
            'title'         => 'required|string|max:160',
            'priority'      => 'nullable|in:low,normal,high,urgent',
            'description'   => 'required|string|max:5000',
            'attachments.*' => 'file|max:10240',
        ]);
    
        // 1) Get all ACTIVE report managers (checked-in, no checkout)
        $activeRMs = DB::table('users as u')
            ->join('user_attendances as ua', function ($j) {
                $j->on('ua.user_id', '=', 'u.id')
                  ->whereNull('ua.check_out')
                  ->where('ua.status', '=', 'in');
            })
            ->where('u.role', 'report_manager')
            ->select('u.id')
            ->distinct()
            ->pluck('id');
    
        $resolverId = null;
    
        if ($activeRMs->isNotEmpty()) {
            // 2) Count open issues per active RM
            $counts = LeadIssue::select('resolver_id', DB::raw('COUNT(*) as open_count'))
                ->whereIn('resolver_id', $activeRMs)
                ->where('status', 'open')
                ->groupBy('resolver_id')
                ->pluck('open_count', 'resolver_id');
    
            // 3) Pick RM with fewest open issues
            $resolverId = $activeRMs->sortBy(fn ($id) => $counts[$id] ?? 0)->first();
    
            // 🔒 Safety: double-check that picked user is still report_manager
            if (!User::where('id', $resolverId)->where('role', 'report_manager')->exists()) {
                $resolverId = null;
            }
        }
    
        // 4) Create issue
        $issue = LeadIssue::create([
            'lead_id'     => $lead->id,
            'reporter_id' => $request->user()->id,
            'title'       => $data['title'],
            'priority'    => $data['priority'] ?? 'normal',
            'description' => $data['description'],
            'status'      => 'open',
            'resolver_id' => $resolverId,
        ]);
    
        // 5) Attach files
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store("issues/{$lead->id}", 'public');
                $issue->attachments()->create([
                    'user_id'     => $request->user()->id,
                    'file_path'   => $path,
                    'file_name'   => $file->getClientOriginalName(),
                    'file_type'   => $file->getClientMimeType(),
                    'file_size'   => $file->getSize(),
                    'is_solution' => false,
                ]);
            }
        }
    
        // 6) Notify resolver (or fallback to all RMs)
        if ($resolverId) {
            User::whereKey($resolverId)
                ->each(fn ($u) => $u->notify(new IssueCreatedNotification($issue)));
        } else {
            User::where('role', 'report_manager')
                ->each(fn ($u) => $u->notify(new IssueCreatedNotification($issue)));
        }
    
        return back()->with(
            'success',
            $resolverId
                ? 'Issue submitted and assigned to the least-loaded active report manager.'
                : 'Issue submitted. No active report manager detected; all report managers were notified.'
        );
    }

>>>>>>> 5ef97175f7f017a4a3cb89de250b8b0719e957c5


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
