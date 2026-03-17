<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLeadRequest;
use App\Http\Requests\UpdateLeadRequest;
use App\Models\Category;
use App\Models\Lead;
use App\Models\User;
use App\Http\Controllers\Traits\HandleLeadFiles;
use App\Models\LeadIssue;
use App\Models\UserAttendance;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Models\IssueAttachment;

class LeadController extends Controller
{
    use HandleLeadFiles;

    /**
     * Hardcoded statuses used across the app.
     */
    protected const STATUSES = [
        'Voice Mail',
        'Wrong Info',
        'Not Interested',
        'Deal',
        'Call Back',
        'Disconnected Number',
        'Hangup',
        'Max Out',
        'Paid Off',
        'Not Qualified (NQ)',
        'Submitted',
        'New Lead',
        'Super Lead',
        'Death Submitted',
    ];

    protected function isLeadManagerUser(User $u): bool
    {
        return method_exists($u, 'hasRole') ? $u->hasRole('lead_manager')
            : (($u->role ?? null) === 'lead_manager');
    }

    protected function isSuperAgentUser(User $u): bool
    {
        // supports method, role name, or boolean column (you used is_super_agent in assign())
        return (method_exists($u, 'isSuperAgent') && $u->isSuperAgent())
            || (method_exists($u, 'hasRole') && $u->hasRole('super_agent'))
            || (($u->role ?? null) === 'super_agent')
            || (property_exists($u, 'is_super_agent') && (bool) $u->is_super_agent);
    }

    protected function isCloserUser(User $u): bool
    {
        return (method_exists($u, 'isCloser') && $u->isCloser())
            || (method_exists($u, 'hasRole') && $u->hasRole('closer'))
            || (($u->role ?? null) === 'closer');
    }


    protected function isMaxOutUser(User $u): bool
    {
        return ($u->role ?? null) === 'max_out';
    }

    protected function isThatSubmittedUser(User $u): bool
    {
        return ($u->role ?? null) === 'death_submitted';
    }

    /** Admin-like (admin OR lead_manager) */
    // protected function isElevated(): bool
    // {
    //     $u = auth()->user();
    //     if (!$u) return false;
    //     return $u->isAdmin() || $this->isLeadManagerUser($u);
    // }

    /** View guard covering all roles */
    // protected function canViewLead(?User $u, Lead $lead): bool
    // {
    //     if (!$u) return false;
    //     if ($this->isElevated()) return true;

    //     if ($this->isSuperAgentUser($u) && (int)$lead->super_agent_id === (int)$u->id) {
    //         return true;
    //     }

    //     if ($this->isCloserUser($u) && ((int)$lead->closer_id === (int)$u->id || (int)$lead->assigned_to === (int)$u->id)) {
    //         return true;
    //     }

    //     return (int)$lead->assigned_to === (int)$u->id;
    // }


    protected function canViewLead(?User $u, Lead $lead): bool
    {
        if (!$u) {
            return false;
        }

        if ($this->canSeeAllLeads()) {
            return true;
        }

        if ($this->isThatSubmittedUser($u)) {
            // Allow access if the lead is currently "Death Submitted" or has a history of being "Death Submitted"
            if (strcasecmp((string) $lead->status, 'Death Submitted') === 0) {
                return true;
            }
            if (method_exists($lead, 'statusTransitions') && $lead->statusTransitions()->where('from_status', 'Death Submitted')->exists()) {
                return true;
            }
        }

        if ($this->isMaxOutUser($u)) {
            if (strcasecmp((string) $lead->status, 'Max Out') === 0) {
                return true;
            }

            if (method_exists($lead, 'hasMaxOutHistory') && $lead->hasMaxOutHistory()) {
                return true;
            }
        }

        if ($this->isSuperAgentUser($u) && (int) $lead->super_agent_id === (int) $u->id) {
            return true;
        }

        if ($this->isCloserUser($u) && ((int) $lead->closer_id === (int) $u->id || (int) $lead->assigned_to === (int) $u->id)) {
            return true;
        }

        // Generic involvement: assigned, created, or in pivot
        return (int) $lead->assigned_to === (int) $u->id || 
               (int) $lead->created_by === (int) $u->id ||
               ((int) $lead->super_agent_id === (int) $u->id) ||
               ((int) $lead->closer_id === (int) $u->id);
    }

    protected function canEditLead(?User $u, Lead $lead): bool
    {
        return $this->canViewLead($u, $lead);
    }

    /** Edit/update guard (same as view; adjust here if you want tighter edit rules) */
    // protected function canEditLead(?User $u, Lead $lead): bool
    // {
    //     // For now, same as canViewLead
    //     return $this->canViewLead($u, $lead);
    // }

    /** Legacy helper used elsewhere */
    protected function ensureCanAccessLead(Lead $lead): void
    {
        $u = auth()->user();
        abort_unless($this->canViewLead($u, $lead), 403);
    }


    /** Admin-only check for global data visibility */
    protected function canSeeAllLeads(): bool
    {
        $u = auth()->user();
        return $u && $u->isAdmin();
    }

    /** Admin-like (admin OR lead_manager) for management features */
    protected function isElevated(): bool
    {
        $u = auth()->user();
        if (!$u)
            return false;

        $isLeadManager = method_exists($u, 'hasRole')
            ? $u->hasRole('lead_manager')
            : (($u->role ?? null) === 'lead_manager');

        return $u->isAdmin() || $isLeadManager;
    }

    public function index(Request $request)
    {
        $statuses = self::STATUSES;
        $categories = Category::orderBy('name')->get();
        $tos = User::where('role', 'user')->orderBy('name')->get();
        $users = $tos; // for compatibility if needed elsewhere

        // Reuse today's date consistently (app timezone)
        $today = now()->toDateString();

        // Get counts for available leads by status (only unassigned leads)
        $statusCounts = [
            'New Lead' => Lead::where('status', 'New Lead')
                ->where(function ($q) {
                    $q->whereNull('assigned_to')->orWhere('assigned_to', 0);
                })
                ->count(),
            'Super Lead' => Lead::where('status', 'Super Lead')
                ->where(function ($q) {
                    $q->whereNull('assigned_to')->orWhere('assigned_to', 0);
                })
                ->count(),
        ];

        $isElevated = $this->isElevated();

        $query = Lead::query()
            ->with(['category', 'assignee'])
            // Restrict leads based on user role involvement (unless Admin)
            ->visibleTo(auth()->user())
            // Search filter
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = '%' . trim($request->q) . '%';
                $q->where(function ($sub) use ($term) {
                    $sub->where('first_name', 'like', $term)
                        ->orWhere('surname', 'like', $term)
                        ->orWhere('gen_code', 'like', $term)
                        ->orWhere('city', 'like', $term)
                        ->orWhere('state_abbreviation', 'like', $term)
                        ->orWhere('zip_code', 'like', $term);
                });
            })
            // Status filter
            ->when(
                $request->filled('status') && in_array($request->status, $statuses, true),
                fn($q) => $q->where('status', $request->status)
            )
            // Category filter
            ->when(
                $request->filled('category') && ctype_digit((string) $request->category),
                fn($q) => $q->where('category_id', $request->category)
            )
            // Today-only filter
            ->when($request->boolean('today'), fn($q) => $q->whereDate('created_at', $today))
            ->orderByDesc('updated_at'); // Changed from orderByDesc('id') to orderByDesc('updated_at')

        $this->applyCreatedDateFilter($query, $request, 'created_at');

        $leads = $query->paginate(10)->withQueryString();

        // Always use a Collection for $onlineUsers
        $onlineUsers = collect();
        if ($isElevated) {
            $onlineUsers = UserAttendance::whereDate('check_in', $today)
                ->whereNull('check_out')
                ->with('user')
                ->get()
                ->pluck('user')
                ->filter()
                ->unique()
                ->values();
        }

        // Maxout leads logic (only for maxout role)
        $maxoutLeadsCount = 0;
        if (auth()->check() && auth()->user()->role === 'maxout') {
            $userId = auth()->id();
            $maxoutLeadsCount = Lead::where('status', 'maxout')
                ->where(function ($q) use ($userId) {
                    $q->where('assigned_to', $userId)
                        ->orWhere('super_agent_id', $userId)
                        ->orWhere('closer_id', $userId);
                })
                ->count();
        }

        return view('leads.index', [
            'onlineUsers' => $onlineUsers,
            'leads' => $leads,
            'categories' => $categories,
            'statuses' => $statuses,
            'users' => $users,
            'tos' => $tos,
            'statusCounts' => $statusCounts,
            'maxoutLeadsCount' => $maxoutLeadsCount,
            'filters' => [
                'q' => $request->q ?? '',
                'status' => $request->status ?? '',
                'category' => $request->category ?? '',
                'today' => $request->boolean('today'),
            ],
        ]);
    }



    public function myLeads(Request $request)
    {
        $user = $request->user();
        $statuses = self::STATUSES;

        // robust super_agent detection
        $isSuperAgent =
            (method_exists($user, 'isSuperAgent') && $user->isSuperAgent()) ||
            (method_exists($user, 'hasRole') && $user->hasRole('super_agent')) ||
            (($user->role ?? null) === 'super_agent');

        // Î“Â¥Ã® Always hide submitted & deal from the UI filter options
        $visibleStatuses = array_values(array_filter(
            $statuses,
            fn($s) => !in_array(mb_strtolower($s), ['submitted', 'deal'], true)
        ));

        $filters = [
            'q' => trim((string) $request->input('q', '')),
            'status' => (string) $request->input('status', ''),
        ];

        $query = Lead::query()
            ->with(['assignee'])
            // Use the comprehensive visibility scope
            ->visibleTo($user);

        // Î“Â¥Ã® Always exclude submitted & deal leads regardless of role
        $query->where(function ($q) {
            $q->whereNull('status')
                ->orWhereRaw('LOWER(status) NOT IN (?, ?)', ['submitted', 'deal']);
        });

        // search filter (name/gen_code/city)
        if ($filters['q'] !== '') {
            $like = '%' . $filters['q'] . '%';
            $query->where(function ($q) use ($like) {
                $q->where('first_name', 'like', $like)
                    ->orWhere('surname', 'like', $like)
                    ->orWhere('gen_code', 'like', $like)
                    ->orWhere('city', 'like', $like);
            });
        }

        // status filter (respect filtered statuses)
        if ($filters['status'] !== '' && in_array($filters['status'], $visibleStatuses, true)) {
            $query->where('status', $filters['status']);
        }

        $leads = $query->latest()->paginate(15)->withQueryString();

        $tos = User::where('role', 'user')->orderBy('name')->get();

        return view('leads.index', [
            'leads' => $leads,
            'statuses' => $visibleStatuses, // hide Submitted + Deal from UI
            'filters' => $filters,
            'categories' => collect(),
            'users' => $tos,
            'tos' => $tos,
            'statusCounts' => [],
            'onlineUsers' => collect(),
        ]);
    }




    public function create()
    {
        $u = auth()->user();
        $canCreate = $u->isAdmin() || $u->role === 'lead_manager' || $u->role === 'super_agent' || $u->role === 'user';
        abort_unless($canCreate, 403);

        $categories = Category::orderBy('name')->get();
        $tos = User::where('role', 'user')->orderBy('name')->get();           // Î“Ã‡Â£Select TOÎ“Ã‡Â¥
        $superAgents = User::where('role', 'super_agent')->orderBy('name')->get();    // Î“Ã‡Â£Select Super AgentÎ“Ã‡Â¥
        $closers = User::where('role', 'closer')->orderBy('name')->get();         // Î“Ã‡Â£Select CloserÎ“Ã‡Â¥
        $statuses = self::STATUSES; // your hardcoded list

        return view('leads.create', compact('categories', 'tos', 'superAgents', 'closers', 'statuses'));
    }

    public function store(StoreLeadRequest $request)
    {
        $data = $request->validated();

        if (!Auth::user()->isAdmin() && !in_array(Auth::user()->role, ['user', 'lead_manager', 'super_agent'])) {
            unset($data['assigned_to'], $data['super_agent_id']);
        }

        // If it's a standard agent and they DIDN'T select a TO, auto-assign to themselves
        if (Auth::user()->role === 'user' && empty($data['assigned_to'])) {
            $data['assigned_to'] = Auth::id();
        }

        $data['numbers'] = collect($data['numbers'] ?? [])
            ->filter(fn($v) => filled($v))
            ->values()
            ->all();

        $data['created_by'] = Auth::id();

        if ($request->hasFile('lead_pdf')) {
            $data['lead_pdf_path'] = $request->file('lead_pdf')->store('leads', 'public');
        }

        try {
            DB::beginTransaction();

            // If an assignee is present on create, stamp assigned_time now
            if (!empty($data['assigned_to'])) {
                $data['assigned_time'] = now();
            }

            $lead = Lead::create($data);

            // Save to lead_user table
            DB::table('lead_user')->insert([
                'lead_id' => $lead->id,
                'user_id' => $data['assigned_to'] ?? Auth::id(),
                'assigned_by' => Auth::id(),
                'is_primary' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->storeTextReport($lead);

            DB::commit();

            session()->flash('success', 'Lead created successfully.');

            if (Auth::user()->isAdmin()) {
                $content = $lead->generateTextReport();
                $filename = $this->generateTextReportFilename($lead);
                $headers = [
                    'Content-Type' => 'text/plain',
                    'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                    'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
                ];
                session()->flash('redirect_to', route('leads.show', $lead));
                return Response::make($content, 200, $headers);
            }

            return redirect()->route('leads.show', $lead);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lead creation failed: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Failed to create lead: ' . $e->getMessage()]);
        }
    }


    public function show(Lead $lead)
    {
        abort_unless($this->canViewLead(Auth::user(), $lead), 403);

        $lead->load(['category', 'assignee', 'superAgent', 'closer', 'creator']);

        return view('leads.show', compact('lead'));
    }


    public function edit(Lead $lead)
    {
        abort_unless($this->canEditLead(Auth::user(), $lead), 403);

        $categories = Category::orderBy('name')->get();
        $statuses = self::STATUSES;

        // for elevated: fill selects; others will see read-only or limited fields in Blade as you prefer
        $tos = User::where('role', 'user')->orderBy('name')->get();
        $superAgents = User::where('role', 'super_agent')->orderBy('name')->get();
        $closers = User::where('role', 'closer')->orderBy('name')->get();

        return view('leads.edit', compact('lead', 'categories', 'statuses', 'tos', 'superAgents', 'closers'));
    }


    public function update(UpdateLeadRequest $request, Lead $lead)
    {
        $user = Auth::user();
        abort_unless($user, 403, 'Unauthorized action.');

        try {
            DB::beginTransaction();

            $data = $request->validated();

            $data['numbers'] = isset($data['numbers']) ? array_values(array_filter($data['numbers'], fn($v) => filled($v))) : [];
            $data['cards_json'] = isset($data['cards_json']) ? array_values(array_filter($data['cards_json'], fn($v) => filled($v))) : [];

            // Non-elevated users can't change assignment fields (except Standard Agents and TL Managers who are now allowed)
            if (!$this->isElevated() && $user->role !== 'user' && $user->role !== 'lead_manager') {
                unset($data['assigned_to'], $data['super_agent_id'], $data['closer_id']);
            }

            $wantsRemoval = $request->boolean('remove_lead_pdf');

            if ($request->hasFile('lead_pdf')) {
                if ($lead->lead_pdf_path) {
                    Storage::disk('public')->delete($lead->lead_pdf_path);
                }
                $data['lead_pdf_path'] = $request->file('lead_pdf')->store('leads', 'public');
            } elseif ($wantsRemoval && $lead->lead_pdf_path) {
                Storage::disk('public')->delete($lead->lead_pdf_path);
                $data['lead_pdf_path'] = null;
            }

            $previousAssignedTo = $lead->assigned_to;

            /**
             * A) If assignment CHANGED (admin/lead_manager path), set/clear assigned_time accordingly.
             */
            if (array_key_exists('assigned_to', $data) && $data['assigned_to'] !== $previousAssignedTo) {
                // on assign -> now(); on unassign -> null
                $data['assigned_time'] = !empty($data['assigned_to']) ? now() : null;
            } else {
                /**
                 * B) If assignment did NOT change:
                 *    When a non-elevated user who IS the current assignee updates the lead,
                 *    refresh assigned_time to "now" to reflect latest activity.
                 */
                if (!$this->isElevated() && (int) $lead->assigned_to === (int) $user->id) {
                    $data['assigned_time'] = now();
                }
            }

            // Update lead
            $lead->update($data);

            // Maintain lead_user pivot if assignment changed (admin/lead_manager)
            if (isset($data['assigned_to']) && $data['assigned_to'] !== $previousAssignedTo) {
                $newUserId = $data['assigned_to'];

                if (empty($newUserId)) {
                    DB::table('lead_user')
                        ->where('lead_id', $lead->id)
                        ->update(['is_primary' => false, 'updated_at' => now()]);
                } else {
                    $existingRow = DB::table('lead_user')
                        ->where('lead_id', $lead->id)
                        ->where('user_id', $newUserId)
                        ->first();

                    if ($existingRow) {
                        DB::table('lead_user')
                            ->where('id', $existingRow->id)
                            ->update([
                                'is_primary' => true,
                                'assigned_by' => Auth::id(),
                                'updated_at' => now()
                            ]);

                        DB::table('lead_user')
                            ->where('lead_id', $lead->id)
                            ->where('id', '!=', $existingRow->id)
                            ->update(['is_primary' => false, 'updated_at' => now()]);
                    } else {
                        $primaryRow = DB::table('lead_user')
                            ->where('lead_id', $lead->id)
                            ->where('is_primary', true)
                            ->first();

                        if ($primaryRow) {
                            DB::table('lead_user')
                                ->where('id', $primaryRow->id)
                                ->update([
                                    'user_id' => $newUserId,
                                    'assigned_by' => Auth::id(),
                                    'updated_at' => now()
                                ]);
                        } else {
                            DB::table('lead_user')->insert([
                                'lead_id' => $lead->id,
                                'user_id' => $newUserId,
                                'assigned_by' => Auth::id(),
                                'is_primary' => true,
                                'created_at' => now(),
                                'updated_at' => now()
                            ]);
                        }
                    }
                }
            }

            DB::commit();
            $lead->refresh();

            session()->flash('success', 'Lead updated successfully.');

            if ($user->isAdmin()) {
                $content = $lead->generateTextReport();
                $filename = $this->generateTextReportFilename($lead);
                $headers = [
                    'Content-Type' => 'text/plain',
                    'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                    'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
                ];
                session()->flash('redirect_to', route('leads.show', $lead));
                return Response::make($content, 200, $headers);
            }

            if ($this->isMaxOutUser($user)) {
                return redirect()->route('leads.maxout');
            }

            if ($this->isThatSubmittedUser($user)) {
                return redirect()->route('leads.submitted');
            }

            if (!$this->canViewLead($user, $lead)) {
                return redirect()->route('leads.index');
            }

            return redirect()->route('leads.show', $lead);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lead update failed: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Failed to update lead: ' . $e->getMessage()]);
        }
    }




    public function destroy(Lead $lead)
    {
        abort_unless($this->isElevated(), 403);
        $lead->delete();

        return redirect()->route('leads.index')->with('success', 'Lead deleted.');
    }

    /**
     * Bulk assign leads to a user
     */
    public function bulkAssign(Request $request)
    {
        abort_unless($this->isElevated(), 403);

        $data = $request->validate([
            'status' => 'required|in:New Lead,Super Lead',
            'assignee_ids' => 'required|array|min:1',
            'assignee_ids.*' => 'integer|exists:users,id',
            'leads_count' => 'required|integer|min:1|max:1000',
            'busy_threshold' => 'nullable|integer|min:0',
        ]);

        $perUser = (int) $data['leads_count'];
        $busyThreshold = $data['busy_threshold'] ?? null;

        try {
            return DB::transaction(function () use ($data, $perUser, $busyThreshold) {
                $users = User::whereIn('id', $data['assignee_ids'])->get();

                $validUsers = $users->filter(function ($u) {
                    if (method_exists($u, 'hasRole'))
                        return $u->hasRole('user');
                    return ($u->role ?? null) === 'user';
                })->values();

                if ($validUsers->isEmpty()) {
                    return back()->with('error', 'Please select at least one teammate with the "user" role.');
                }

                $workload = Lead::selectRaw('assigned_to, COUNT(*) as c')
                    ->whereNotNull('assigned_to')
                    ->groupBy('assigned_to')
                    ->pluck('c', 'assigned_to');

                if ($busyThreshold !== null) {
                    $validUsers = $validUsers->filter(function ($u) use ($workload, $busyThreshold) {
                        $count = (int) ($workload[$u->id] ?? 0);
                        return $count < $busyThreshold;
                    })->values();

                    if ($validUsers->isEmpty()) {
                        return back()->with('error', 'No eligible users after applying the "Exclude if assigned Î“Ã«Ã‘" filter.');
                    }
                }

                $validUsers = $validUsers->sortBy(fn($u) => (int) ($workload[$u->id] ?? 0))->values();

                $totalNeeded = $perUser * $validUsers->count();

                $leads = Lead::query()
                    ->where('status', $data['status'])
                    ->where(function ($q) {
                        $q->whereNull('assigned_to')->orWhere('assigned_to', 0);
                    })
                    ->orderBy('created_at', 'asc')
                    ->limit($totalNeeded)
                    ->get();

                if ($leads->isEmpty()) {
                    return back()->with('error', 'No leads are available for the selected status.');
                }

                $assignments = [];
                $cursor = 0;
                $actuallyAssigned = 0;

                foreach ($validUsers as $user) {
                    $remaining = max(0, $leads->count() - $cursor);
                    if ($remaining <= 0)
                        break;

                    $take = min($perUser, $remaining);
                    $chunk = $leads->slice($cursor, $take);
                    $cursor += $chunk->count();

                    foreach ($chunk as $lead) {
                        $lead->assigned_to = $user->id;
                        $lead->assigned_time = now(); // <-- stamp time
                        $lead->save();

                        DB::table('lead_user')->updateOrInsert(
                            ['lead_id' => $lead->id, 'user_id' => $user->id],
                            [
                                'assigned_by' => Auth::id(),
                                'is_primary' => true,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]
                        );
                    }

                    $assignments[$user->name] = ($assignments[$user->name] ?? 0) + $chunk->count();
                    $actuallyAssigned += $chunk->count();
                }

                if ($actuallyAssigned === 0) {
                    return back()->with('error', 'Not enough leads to assign to the selected users.');
                }

                $parts = [];
                foreach ($assignments as $name => $count) {
                    $parts[] = "{$count} Î“Ã¥Ã† {$name}";
                }
                $summary = implode(', ', $parts);

                $requestedTotal = $perUser * $validUsers->count();
                $note = $actuallyAssigned < $requestedTotal
                    ? " (assigned {$actuallyAssigned} of {$requestedTotal}; not enough available leads)"
                    : '';

                Log::info("Bulk assignment (per-user): {$actuallyAssigned} {$data['status']} leads. {$summary}");

                return back()->with(
                    'success',
                    "Assigned {$perUser} {$data['status']} lead(s) to each selected user{$note}. Distribution: {$summary}"
                );
            });
        } catch (\Throwable $e) {
            Log::error('Bulk assignment failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return back()->with('error', 'Failed to assign leads. Please try again.');
        }
    }


    /**
     * Admin: quick assignment endpoint (optional).
     */
    public function assign(Request $request, Lead $lead)
    {
        abort_unless($this->isElevated(), 403);

        $data = $request->validate([
            'assigned_to' => ['nullable', 'exists:users,id'],
            'super_agent_id' => ['nullable', 'exists:users,id'],
        ]);

        if (!empty($data['super_agent_id'])) {
            $isSuper = User::where('id', $data['super_agent_id'])->where('is_super_agent', true)->exists();
            abort_unless($isSuper, 422, 'Selected user is not a Super Agent.');
        }

        // If assignment is changing, set/clear assigned_time
        if (array_key_exists('assigned_to', $data) && $data['assigned_to'] !== $lead->assigned_to) {
            $data['assigned_time'] = !empty($data['assigned_to']) ? now() : null;
        }

        $lead->update($data);

        return back()->with('success', 'Assignment updated');
    }


    public function downloadPdf(Lead $lead)
    {
        dd($lead->lead_pdf_path);

        // AuthZ: only admins, assignee, or creator may download
        $user = Auth::user();
        $allowed = $user->isAdmin()
            || $lead->assigned_to === $user->id
            || $lead->created_by === $user->id;

        abort_unless($allowed, 403, 'You are not allowed to download this document.');

        // Validate file presence
        $path = $lead->lead_pdf_path;
        abort_unless($path && Storage::disk('public')->exists($path), 404, 'Document not found.');

        // Nice filename e.g. "Mechelle_Ruiz_Lead.pdf"
        $base = trim(($lead->first_name . ' ' . $lead->surname)) ?: 'Lead';
        $filename = str_replace(' ', '_', $base) . '_Document.pdf';

        // Force download
        $file = Storage::disk('public')->get($path);

        return Response::make($file, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
        ]);
    }

    public function downloadTxt(Lead $lead)
    {
        $user = auth()->user();
        abort_unless($user, 403, 'You must be logged in to download this document.');

        // ---------- Normalize data ----------
        // Phone numbers (array or JSON -> trimmed, non-empty)
        $numbers = is_array($lead->numbers)
            ? $lead->numbers
            : (json_decode($lead->numbers ?? '[]', true) ?? []);
        $numbers = array_values(array_filter(array_map(
            fn($n) => trim((string) $n),
            is_array($numbers) ? $numbers : []
        ), fn($n) => $n !== ''));

        // Cards (you said you only store the card number; keep a single string per entry)
        $rawCards = is_array($lead->cards_json)
            ? $lead->cards_json
            : (json_decode($lead->cards_json ?? '[]', true) ?? []);

        $cardNumbers = [];
        foreach ($rawCards as $c) {
            if (is_string($c)) {
                $val = trim($c);
                if ($val !== '')
                    $cardNumbers[] = $val;
            } elseif (is_array($c)) {
                // Just in case someone saved a structure: pick the most likely number string
                $candidate = $c['cc'] ?? $c['card'] ?? $c['number'] ?? '';
                if (!$candidate) {
                    $candidate = implode(' ', array_filter(array_map(
                        fn($v) => is_scalar($v) ? trim((string) $v) : '',
                        $c
                    )));
                }
                $candidate = trim($candidate);
                if ($candidate !== '')
                    $cardNumbers[] = $candidate;
            }
        }

        // Helper
        $L = fn(string $label, $value = '') => $label . ': ' . (isset($value) ? (string) $value : '');

        $fullName = trim(implode(' ', array_filter([
            $lead->first_name,
            $lead->middle_initial,
            $lead->surname,
        ], fn($v) => (string) $v !== '')));

        // ---------- Build TXT content ----------
        $lines = [];
        $lines[] = $L('Date', $lead->created_at ? $lead->created_at->format('m-d-Y') : now()->format('m-d-Y'));
        $lines[] = str_repeat('-', 70) . ')';
        $lines[] = $L('Name', $fullName);
        $lines[] = $L('Phone', $numbers[0] ?? '000-000-0000');
        $lines[] = $L('Cell', $lead->cell ?? '000-000-0000');
        $lines[] = $L('Address', $lead->street);
        $lines[] = $L('City', $lead->city);
        $lines[] = $L('State', $lead->state_abbreviation);
        $lines[] = $L('Zip code', $lead->zip_code);
        $lines[] = $L('SSN', $lead->ssn ?? '000-00-0000');
        $lines[] = $L('DOB', $lead->dob ?? 'MM-DD-YYYY');
        $lines[] = $L('MMN', $lead->mmn);
        $lines[] = $L('Email', $lead->email);
        $lines[] = str_repeat('-', 70) . ')';
        $lines[] = $L('Credit Score', $lead->fico);
        $lines[] = $L('Total Cards', $lead->total_cards);
        $lines[] = $L('Total Debt', '$' . ($lead->total_debt ?? '0.00'));

        $banks = is_array($lead->bank_details) ? $lead->bank_details : [];
        if (empty($banks)) {
            // Fallback for older leads or single bank data not yet migrated (though migration should have covered it)
            $banks = [[
                'bank_name' => $lead->bank_name,
                'name_on_card' => $lead->name_on_card,
                'card_number' => $lead->card_number,
                'exp_date' => $lead->exp_date,
                'cvc' => $lead->cvc,
                'balance' => $lead->balance,
                'available' => $lead->available,
                'last_payment_amount' => $lead->last_payment_amount,
                'last_payment_date' => $lead->last_payment_date,
                'next_payment_amount' => $lead->next_payment_amount,
                'next_payment_date' => $lead->next_payment_date,
                'credit_limit' => $lead->credit_limit,
                'apr' => $lead->apr,
                'charge' => $lead->charge,
                'tollfree' => $lead->tollfree,
            ]];
        }

        foreach ($banks as $index => $bank) {
            $lines[] = str_repeat('-', 70) . ')';
            $lines[] = $L('Bank Name', $bank['bank_name'] ?? '');
            $lines[] = $L('Name on Card', $bank['name_on_card'] ?? '');
            $lines[] = $L('Card Number', $bank['card_number'] ?? '');
            $lines[] = $L('Exp Date', ($bank['exp_date'] ?? 'MM-YYYY'));
            $lines[] = $L('CVC', $bank['cvc'] ?? '');
            $lines[] = $L('Balance', '$' . ($bank['balance'] ?? '0.00'));
            $lines[] = $L('Available', '$' . ($bank['available'] ?? '0.00'));
            $lines[] = $L('Last Payment', '$' . ($bank['last_payment_amount'] ?? '0.00')) . "\t\tDate: " . ($bank['last_payment_date'] ?? 'DD-MM-YY');
            $lines[] = $L('Next Payment', '$' . ($bank['next_payment_amount'] ?? '0.00')) . "\t\tDate: " . ($bank['next_payment_date'] ?? 'DD-MM-YY');
            $lines[] = $L('Credit Limit', '$' . ($bank['credit_limit'] ?? '0.00'));
            $lines[] = $L('Apr', ($bank['apr'] ?? '0.00%'));
            $lines[] = $L('Charge', '$' . ($bank['charge'] ?? '0.00'));
            $lines[] = $L('Tollfree', $bank['tollfree'] ?? '1-8xx-xxx-xxxx');
        }
        $lines[] = '';
        $lines[] = 'NOTES:';
        $notes = trim((string) ($lead->notes ?? ''));
        $lines[] = $notes !== '' ? $notes : 'No notes available.';

        $content = implode(PHP_EOL, $lines);
        $baseName = str_replace(' ', '_', $fullName ?: 'Lead') . '_Details';
        $txtFilename = $baseName . '.txt';

        // ---------- ZIP / attachment packaging (unchanged) ----------
        $latestIssue = LeadIssue::where('lead_id', $lead->id)->orderByDesc('created_at')->first();
        if (!$latestIssue) {
            return response($content, 200, [
                'Content-Type' => 'text/plain',
                'Content-Disposition' => 'attachment; filename="' . $txtFilename . '"',
                'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            ]);
        }

        $latestAttachment = IssueAttachment::where('lead_issue_id', $latestIssue->id)->orderByDesc('created_at')->first();
        if (!$latestAttachment) {
            return response($content, 200, [
                'Content-Type' => 'text/plain',
                'Content-Disposition' => 'attachment; filename="' . $txtFilename . '"',
                'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            ]);
        }

        $zipDownloadName = $baseName . '_with_attachment.zip';
        $tmpDir = storage_path('app/tmp');
        if (!is_dir($tmpDir)) {
            @mkdir($tmpDir, 0775, true);
        }
        $zipPath = $tmpDir . '/' . Str::uuid()->toString() . '.zip';

        $zip = new \ZipArchive();
        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            return response($content, 200, [
                'Content-Type' => 'text/plain',
                'Content-Disposition' => 'attachment; filename="' . $txtFilename . '"',
                'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            ]);
        }

        // Add TXT
        $zip->addFromString($txtFilename, $content);

        // Attachment resolution (unchanged)
        $origPath = $latestAttachment->file_path;
        $attachmentName = basename($latestAttachment->file_name ?: $origPath);

        $disksToTry = array_values(array_unique([config('filesystems.default'), 'public', 'local']));
        $pathsToTry = [$origPath, 'public/' . ltrim($origPath, '/')];

        $resolvedDisk = null;
        $resolvedPath = null;
        foreach ($disksToTry as $disk) {
            foreach ($pathsToTry as $p) {
                try {
                    if (Storage::disk($disk)->exists($p)) {
                        $resolvedDisk = $disk;
                        $resolvedPath = $p;
                        break 2;
                    }
                } catch (\Throwable $e) {
                }
            }
        }

        $manifest = [];
        $manifest[] = "Package: {$zipDownloadName}";
        $manifest[] = "Generated: " . now()->toDateTimeString();
        $manifest[] = "";
        $manifest[] = "Files:";
        $manifest[] = "- {$txtFilename} (text/plain, " . strlen($content) . " bytes)";

        if (!$resolvedDisk) {
            $zip->addFromString(
                'Attachment/READ_ERROR.txt',
                "Could not locate attachment on any tried disk/path.\n" .
                "Original path: {$origPath}\n" .
                "Tried disks: " . implode(', ', $disksToTry) . "\n" .
                "Tried paths: " . implode(', ', $pathsToTry) . "\n"
            );
            $manifest[] = "- Attachment/{$attachmentName} (READ_ERROR: file not found on tried disks/paths)";
        } else {
            try {
                $bytes = Storage::disk($resolvedDisk)->get($resolvedPath);
            } catch (\Throwable $e) {
                $bytes = null;
                $readErr = $e->getMessage();
            }

            if ($bytes === null) {
                $zip->addFromString(
                    'Attachment/READ_ERROR.txt',
                    "Failed to read attachment.\nDisk: {$resolvedDisk}\nPath: {$resolvedPath}\nError: {$readErr}"
                );
                $manifest[] = "- Attachment/{$attachmentName} (READ_ERROR: {$readErr})";
            } else {
                $zip->addFromString('Attachment/' . $attachmentName, $bytes);

                $mime = 'application/octet-stream';
                try {
                    $tmpMime = Storage::disk($resolvedDisk)->mimeType($resolvedPath);
                    if ($tmpMime)
                        $mime = $tmpMime;
                } catch (\Throwable $e) {
                }
                $size = strlen($bytes);
                try {
                    $tmpSize = Storage::disk($resolvedDisk)->size($resolvedPath);
                    if (is_numeric($tmpSize))
                        $size = (int) $tmpSize;
                } catch (\Throwable $e) {
                }

                $hash = hash('sha256', $bytes);
                $manifest[] = "- Attachment/{$attachmentName} ({$mime}, {$size} bytes, sha256={$hash})";
                $manifest[] = "  Stored at Î“Ã¥Ã† disk: {$resolvedDisk}, path: {$resolvedPath}";
            }
        }

        $zip->addFromString('MANIFEST.txt', implode(PHP_EOL, $manifest));
        $zip->close();

        return response()->download($zipPath, $zipDownloadName, [
            'Content-Type' => 'application/zip',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
        ])->deleteFileAfterSend(true);
    }

    /**
     * Helper: ensure current user can see/edit this lead.
     */
    // protected function ensureCanAccessLead(Lead $lead): void
    // {
    //     if ($this->isElevated()) {
    //         return;
    //     }

    //     $uid = Auth::id();

    //     abort_unless(
    //         ($lead->assigned_to && $lead->assigned_to == $uid) ||
    //             ($lead->super_agent_id && $lead->super_agent_id == $uid),
    //         403
    //     );
    // }

    /**
     * Download a text report for a lead
     */
    public function downloadTextReport(Lead $lead)
    {
        // Check authorization
        $user = Auth::user();
        $allowed = $user && ($this->isElevated() || $lead->assigned_to === $user->id);
        abort_unless($allowed, 403, 'You are not allowed to download this report.');

        // Generate report content
        $content = $lead->generateTextReport();
        $filename = $this->generateTextReportFilename($lead);

        // Return as download
        return Response::make($content, 200, [
            'Content-Type' => 'text/plain',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
        ]);
    }


    public function import(Request $request)
    {
        $request->validate([
            'leads_file' => 'required|file|mimes:csv,txt',
        ]);

        $file = $request->file('leads_file');
        $rows = array_map('str_getcsv', file($file->getRealPath()));

        // headers
        $headers = array_map('trim', $rows[0] ?? []);
        unset($rows[0]);

        $inserted = 0;
        $skipped = 0;

        foreach ($rows as $row) {
            // Normalize row shape
            $row = array_map(static fn($v) => is_string($v) ? trim($v) : $v, $row);
            $leadData = @array_combine($headers, $row);
            if ($leadData === false) {
                $skipped++;
                continue;
            }

            // --- Utility: convert empty strings to null (for ALL columns) ---
            $leadData = array_map(static function ($v) {
                if (is_string($v)) {
                    $v = trim($v);
                    return $v === '' ? null : $v;
                }
                return $v === '' ? null : $v;
            }, $leadData);

            // --- Status normalization (nullable-friendly) ---
            if (!empty($leadData['status'])) {
                switch (strtolower(trim($leadData['status']))) {
                    case 'super lead':
                        $leadData['status'] = 'Super Lead';
                        break;
                    case 'deal':
                        $leadData['status'] = 'Deal';
                        break;
                    case 'call back':
                        $leadData['status'] = 'Call Back';
                        break;
                    case 'new lead':
                        $leadData['status'] = 'New Lead';
                        break;
                    default:
                        $leadData['status'] = 'New Lead';
                }
            } else {
                $leadData['status'] = 'New Lead';
            }

            // --- Build numbers (no longer required) ---
            $primary = $leadData['primary_number'] ?? null;
            $alts = [
                $leadData['alt_number_1'] ?? null,
                $leadData['alt_number_2'] ?? null,
                $leadData['alt_number_3'] ?? null,
                $leadData['alt_number_4'] ?? null,
            ];

            $numbers = [];
            if (!empty($primary)) {
                $numbers[] = $primary;
                foreach ($alts as $a) {
                    if (!empty($a))
                        $numbers[] = $a;
                }
            } elseif (!empty($leadData['numbers'])) {
                // Fallback: existing numbers column (JSON or delimited)
                $parsed = $this->validateJsonOrNull($leadData['numbers']);
                if (!is_null($parsed)) {
                    $decoded = json_decode($parsed, true);
                    if (is_array($decoded)) {
                        $numbers = array_values(array_filter(array_map('trim', $decoded)));
                    }
                } else {
                    $parts = preg_split('/[;,\/|]+/', (string) $leadData['numbers']);
                    $numbers = array_values(array_filter(array_map('trim', $parts ?? [])));
                }
            }

            // Store as JSON string or null
            $leadData['numbers'] = !empty($numbers) ? json_encode($numbers, JSON_UNESCAPED_UNICODE) : null;

            // Drop helper columns if your table doesnÎ“Ã‡Ã–t have them
            unset(
                $leadData['primary_number'],
                $leadData['alt_number_1'],
                $leadData['alt_number_2'],
                $leadData['alt_number_3'],
                $leadData['alt_number_4']
            );

            // cards_json normalization
            $leadData['cards_json'] = !empty($leadData['cards_json'])
                ? $this->validateJsonOrNull($leadData['cards_json'])
                : null;

            // created_by
            $leadData['created_by'] = Auth::id();

            // --- Safe numeric casting ("" -> null) ---
            $leadData['age'] = isset($leadData['age']) && $leadData['age'] !== null && $leadData['age'] !== '' ? (int) $leadData['age'] : null;
            $leadData['fico'] = isset($leadData['fico']) && $leadData['fico'] !== null && $leadData['fico'] !== '' ? (int) $leadData['fico'] : null;
            $leadData['balance'] = isset($leadData['balance']) && $leadData['balance'] !== null && $leadData['balance'] !== '' ? (float) $leadData['balance'] : null;
            $leadData['credits'] = isset($leadData['credits']) && $leadData['credits'] !== null && $leadData['credits'] !== '' ? (float) $leadData['credits'] : null;

            // --- Validation: everything nullable (skip nothing because of missing fields) ---
            $validator = Validator::make($leadData, [
                'first_name' => 'nullable|string|max:255',
                'surname' => 'nullable|string|max:255',
                'middle_initial' => 'nullable|string|max:5',
                'gen_code' => 'nullable|string|max:255',
                'age' => 'nullable|integer',
                'ssn' => 'nullable|string',
                'street' => 'nullable|string|max:255',
                'city' => 'nullable|string|max:255',
                'state_abbreviation' => 'nullable|string|max:5',
                'zip_code' => 'nullable|string|max:20',
                'fico' => 'nullable|integer',
                'balance' => 'nullable|numeric',
                'credits' => 'nullable|numeric',
                'notes' => 'nullable|string',
                'status' => 'nullable|string',
                'xfc06' => 'nullable|string|max:255',
                'xfc07' => 'nullable|string|max:255',
                'demo7' => 'nullable|string|max:255',
                'demo9' => 'nullable|string|max:255',
                'numbers' => 'nullable|string',   // JSON string or null
                'cards_json' => 'nullable|string',   // JSON string or null
                'created_by' => 'required|integer',
            ]);

            if ($validator->fails()) {
                // Log and continue; do not break the import
                // \Log::warning('Lead row failed validation', ['errors' => $validator->errors(), 'row' => $leadData]);
                $skipped++;
                continue;
            }

            try {
                Lead::create($validator->validated());
                $inserted++;
            } catch (\Throwable $e) {
                // Most common cause: DB column NOT NULL + null value. Log and skip.
                // \Log::error('Lead insert failed', ['error' => $e->getMessage(), 'row' => $leadData]);
                $skipped++;
                continue;
            }
        }

        return redirect()->back()->with(
            'success',
            "{$inserted} leads imported. {$skipped} row(s) skipped (validation/DB constraints)."
        );
    }



    /**
     * Helper method to validate if string is valid JSON
     * Returns JSON string if valid, else null
     */
    private function validateJsonOrNull(string $jsonString): ?string
    {
        json_decode($jsonString);
        return (json_last_error() === JSON_ERROR_NONE) ? $jsonString : null;
    }

    private function applyCreatedDateFilter(
        Builder
        $query,
        Request $request,
        string $field = 'created_at'
    ): void {
        // App runs in Asia/Karachi; DB timestamps typically stored in UTC.
        $tz = config('app.timezone', 'Asia/Karachi');

        // Highest specificity wins: date > range > today > days
        if ($request->filled('date')) {
            $start = Carbon::parse($request->input('date'), $tz)->startOfDay()->utc();
            $end = Carbon::parse($request->input('date'), $tz)->endOfDay()->utc();
            $query->whereBetween($field, [$start, $end]);
            return;
        }

        if ($request->filled('from') || $request->filled('to')) {
            $from = $request->filled('from')
                ? Carbon::parse($request->input('from'), $tz)->startOfDay()->utc()
                : Carbon::minValue();
            $to = $request->filled('to')
                ? Carbon::parse($request->input('to'), $tz)->endOfDay()->utc()
                : Carbon::now($tz)->endOfDay()->utc();
            $query->whereBetween($field, [$from, $to]);
            return;
        }

        if ($request->boolean('today')) {
            $start = Carbon::now($tz)->startOfDay()->utc();
            $end = Carbon::now($tz)->endOfDay()->utc();
            $query->whereBetween($field, [$start, $end]);
            return;
        }

        if ($request->filled('days')) {
            $days = max(1, min((int) $request->input('days'), 365));
            $start = Carbon::now($tz)->subDays($days - 1)->startOfDay()->utc();
            $end = Carbon::now($tz)->endOfDay()->utc();
            $query->whereBetween($field, [$start, $end]);
            return;
        }
    }


    public function ids(Request $request)
    {
        $query = Lead::query();

        // Reuse the same filters you use on index()
        $this->applyLeadFilters($query, $request); // extract your index filters into this helper

        // If non-admin, limit to "my leads", same as index()
        if (!auth()->user()->isAdmin()) {
            $query->where('assigned_to', auth()->id());
        }

        // Only return ids + total count
        return response()->json([
            'ids' => $query->pluck('id'),
            'count' => $query->count(),
        ]);
    }

    // Example filter helper Î“Ã‡Ã´ mirror your existing index() logic
    protected function applyLeadFilters($q, Request $r)
    {
        if ($r->filled('q')) {
            $q->where(function ($x) use ($r) {
                $term = '%' . $r->q . '%';
                $x->where('first_name', 'like', $term)
                    ->orWhere('surname', 'like', $term)
                    ->orWhere('gen_code', 'like', $term)
                    ->orWhereJsonContains('numbers', $r->q);
            });
        }

        if ($r->filled('status'))
            $q->where('status', $r->status);
        if ($r->filled('category'))
            $q->where('category_id', $r->category);

        // Created filters (today / days / date / range)
        if ($r->boolean('today')) {
            $q->whereDate('created_at', now()->toDateString());
        } elseif ($r->filled('days')) {
            $q->where('created_at', '>=', now()->subDays((int) $r->days));
        } elseif ($r->filled('date')) {
            $q->whereDate('created_at', $r->date);
        } else {
            if ($r->filled('from'))
                $q->whereDate('created_at', '>=', $r->from);
            if ($r->filled('to'))
                $q->whereDate('created_at', '<=', $r->to);
        }
    }


    public function bulkDestroy(Request $request)
    {
        $ids = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:leads,id'],
        ])['ids'];

        // Optional: Authorization Î“Ã‡Ã´ ensure user can delete each lead
        $leads = Lead::whereIn('id', $ids)->get();

        $deletable = [];
        foreach ($leads as $lead) {
            if ($request->user()->can('delete', $lead)) {
                $deletable[] = $lead->id;
            }
        }

        if (empty($deletable)) {
            return back()->with('error', 'You are not authorized to delete the selected leads.');
        }

        DB::transaction(function () use ($deletable) {
            Lead::whereIn('id', $deletable)->delete(); // uses SoftDeletes if enabled
        });

        $blocked = count($ids) - count($deletable);
        $msg = count($deletable) . ' lead(s) deleted.';
        if ($blocked > 0) {
            $msg .= " {$blocked} skipped due to permissions.";
        }

        return back()->with('status', $msg);
    }


    public function maxOut()
    {
        return view('leads.maxout');
    }

    public function submitted(Request $request)
    {
        $user = auth()->user();
        $appTimezone = config('app.timezone', 'UTC');

        // Get filters from request
        $filters = [
            'q' => trim((string) $request->input('q', '')),
            'from' => $request->input('from'),
            'to' => $request->input('to'),
        ];

        // Query for active "Death Submitted" leads
        $leadQuery = Lead::query()
            ->where('status', 'Death Submitted');

        // Query for leads that have converted from "Death Submitted" to another status
        $convertedLeadQuery = Lead::query()
            ->where('status', '!=', 'Death Submitted')
            ->whereHas('statusTransitions', function ($query) {
                $query->where('from_status', 'Death Submitted')->where('to_status', '!=', 'Death Submitted');
            });

        // Apply search filter
        if ($filters['q'] !== '') {
            $leadQuery->where(function ($q) use ($filters) {
                $term = '%' . $filters['q'] . '%';
                $q->where('first_name', 'like', $term)
                    ->orWhere('surname', 'like', $term)
                    ->orWhere('gen_code', 'like', $term)
                    ->orWhere('numbers', 'like', $term);
            });
            $convertedLeadQuery->where(function ($q) use ($filters) {
                $term = '%' . $filters['q'] . '%';
                $q->where('first_name', 'like', $term)
                    ->orWhere('surname', 'like', $term)
                    ->orWhere('gen_code', 'like', $term)
                    ->orWhere('numbers', 'like', $term);
            });
        }

        // Apply date range filters
        if (!empty($filters['from'])) {
            $leadQuery->whereDate('created_at', '>=', $filters['from']);
            $convertedLeadQuery->whereDate('created_at', '>=', $filters['from']);
        }
        if (!empty($filters['to'])) {
            $leadQuery->whereDate('created_at', '<=', $filters['to']);
            $convertedLeadQuery->whereDate('created_at', '<=', $filters['to']);
        }

        // Load relationships and paginate
        $leads = $leadQuery
            ->with(['assignee'])
            ->latest('updated_at')
            ->paginate(25)
            ->withQueryString();

        $convertedLeads = $convertedLeadQuery
            ->with(['assignee', 'lastMaxOutExit.changer'])
            ->withMax(
                [
                    'statusTransitions as last_death_submitted_exit_at' => function ($query) {
                        $query->where('from_status', 'Death Submitted')->where('to_status', '!=', 'Death Submitted');
                    },
                ],
                'created_at'
            )
            ->orderByDesc('last_death_submitted_exit_at')
            ->paginate(25, ['*'], 'converted_page')
            ->withQueryString();

        $activeCount = $leads->total();
        $convertedCount = $convertedLeads->total();

        // Determine active tab
        $activeTab = $request->input('tab');
        if (!in_array($activeTab, ['active', 'converted'], true)) {
            $activeTab = $request->has('converted_page') ? 'converted' : 'active';
        }

        return view('leads.submitted', [
            'leads' => $leads,
            'convertedLeads' => $convertedLeads,
            'activeCount' => $activeCount,
            'convertedCount' => $convertedCount,
            'filters' => $filters,
            'activeTab' => $activeTab,
            'statuses' => self::STATUSES,
        ]);
    }
}
