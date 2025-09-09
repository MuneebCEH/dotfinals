<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLeadRequest;
use App\Http\Requests\UpdateLeadRequest;
use App\Models\Category;
use App\Models\Lead;
use App\Models\User;
use App\Http\Controllers\Traits\HandleLeadFiles;
use App\Models\UserAttendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

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
        'Super Lead', // Added Super Lead status
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

    /** Admin-like (admin OR lead_manager) */
    // protected function isElevated(): bool
    // {
    //     $u = auth()->user();
    //     if (!$u) return false;
    //     return $u->isAdmin() || $this->isLeadManagerUser($u);
    // }

    /** View guard covering all roles */
    protected function canViewLead(?User $u, Lead $lead): bool
    {
        if (!$u) return false;
        if ($this->isElevated()) return true;

        if ($this->isSuperAgentUser($u) && (int)$lead->super_agent_id === (int)$u->id) {
            return true;
        }

        if ($this->isCloserUser($u) && ((int)$lead->closer_id === (int)$u->id || (int)$lead->assigned_to === (int)$u->id)) {
            return true;
        }

        return (int)$lead->assigned_to === (int)$u->id;
    }

    /** Edit/update guard (same as view; adjust here if you want tighter edit rules) */
    protected function canEditLead(?User $u, Lead $lead): bool
    {
        // For now, same as canViewLead
        return $this->canViewLead($u, $lead);
    }

    /** Legacy helper used elsewhere */
    protected function ensureCanAccessLead(Lead $lead): void
    {
        $u = auth()->user();
        abort_unless($this->canViewLead($u, $lead), 403);
    }


    /**
     * Treat lead_manager as elevated (admin-like).
     */
    protected function isElevated(): bool
    {
        $u = auth()->user();
        if (!$u) return false;

        $isLeadManager = method_exists($u, 'hasRole')
            ? $u->hasRole('lead_manager')
            : (($u->role ?? null) === 'lead_manager');

        return $u->isAdmin() || $isLeadManager;
    }

    public function index(Request $request)
    {
        $statuses   = self::STATUSES;
        $categories = Category::orderBy('name')->get();
        $users      = User::where('role', 'user')->orderBy('name')->get();

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
            // If NOT elevated, show only leads assigned to this user
            ->when(!$isElevated, fn($q) => $q->where('assigned_to', auth()->id()))
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
            // Status filter (defensive: only apply if value is in our allowed list)
            ->when(
                $request->filled('status') && in_array($request->status, $statuses, true),
                fn($q) => $q->where('status', $request->status)
            )
            // Category filter
            ->when(
                $request->filled('category') && ctype_digit((string) $request->category),
                fn($q) => $q->where('category_id', $request->category)
            )
            // ✅ Today-only filter (NEW)
            ->when($request->boolean('today'), fn($q) => $q->whereDate('created_at', $today))
            ->orderByDesc('id');

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

        return view('leads.index', [
            'onlineUsers'  => $onlineUsers,
            'leads'        => $leads,
            'categories'   => $categories,
            'statuses'     => $statuses,
            'users'        => $users,
            'statusCounts' => $statusCounts,
            'filters'      => [
                'q'        => $request->q ?? '',
                'status'   => $request->status ?? '',
                'category' => $request->category ?? '',
                // optional but nice for symmetry with others:
                'today'    => $request->boolean('today'),
            ],
        ]);
    }


    public function myLeads(Request $request)
    {
        $user     = $request->user();
        $statuses = self::STATUSES;
    
        // robust super_agent detection
        $isSuperAgent =
            (method_exists($user, 'isSuperAgent') && $user->isSuperAgent()) ||
            (method_exists($user, 'hasRole') && $user->hasRole('super_agent')) ||
            (($user->role ?? null) === 'super_agent');
    
        // ❌ Always hide submitted & deal from the UI filter options
        $visibleStatuses = array_values(array_filter(
            $statuses,
            fn ($s) => !in_array(mb_strtolower($s), ['submitted', 'deal'], true)
        ));
    
        $filters = [
            'q'      => trim((string) $request->input('q', '')),
            'status' => (string) $request->input('status', ''),
        ];
    
        $query = Lead::query()
            ->with(['assignee'])
            // visibility: super_agent sees by super_agent_id, others by assigned_to
            ->when($isSuperAgent, function ($q) use ($user) {
                $q->where('super_agent_id', $user->id);
            }, function ($q) use ($user) {
                $q->where('assigned_to', $user->id);
            });
    
        // ❌ Always exclude submitted & deal leads regardless of role
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
    
        return view('leads.index', [
            'leads'        => $leads,
            'statuses'     => $visibleStatuses, // hide Submitted + Deal from UI
            'filters'      => $filters,
            'categories'   => collect(),
            'users'        => collect(),
            'statusCounts' => [],
            'onlineUsers'  => collect(),
        ]);
    }




    public function create()
    {
        abort_unless($this->isElevated(), 403);

        $categories   = Category::orderBy('name')->get();
        $tos          = User::where('role', 'user')->orderBy('name')->get();           // “Select TO”
        $superAgents  = User::where('role', 'super_agent')->orderBy('name')->get();    // “Select Super Agent”
        $closers      = User::where('role', 'closer')->orderBy('name')->get();         // “Select Closer”
        $statuses     = self::STATUSES; // your hardcoded list

        return view('leads.create', compact('categories', 'tos', 'superAgents', 'closers', 'statuses'));
    }

    public function store(StoreLeadRequest $request)
{
    $data = $request->validated();

    if (!Auth::user()->isAdmin()) {
        unset($data['assigned_to'], $data['super_agent_id']);
        // If you want to auto-assign to creator, uncomment:
        // $data['assigned_to'] = Auth::id();
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
            'lead_id'     => $lead->id,
            'user_id'     => $data['assigned_to'] ?? Auth::id(),
            'assigned_by' => Auth::id(),
            'is_primary'  => true,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        $this->storeTextReport($lead);

        DB::commit();

        session()->flash('success', 'Lead created successfully.');

        if (Auth::user()->isAdmin()) {
            $content  = $lead->generateTextReport();
            $filename = $this->generateTextReportFilename($lead);
            $headers  = [
                'Content-Type'        => 'text/plain',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Cache-Control'       => 'no-store, no-cache, must-revalidate, max-age=0',
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

        $categories  = Category::orderBy('name')->get();
        $statuses    = self::STATUSES;

        // for elevated: fill selects; others will see read-only or limited fields in Blade as you prefer
        $tos         = User::where('role', 'user')->orderBy('name')->get();
        $superAgents = User::where('role', 'super_agent')->orderBy('name')->get();
        $closers     = User::where('role', 'closer')->orderBy('name')->get();

        return view('leads.edit', compact('lead', 'categories', 'statuses', 'tos', 'superAgents', 'closers'));
    }


    public function update(UpdateLeadRequest $request, Lead $lead)
{
    $user = Auth::user();
    abort_unless($this->canEditLead($user, $lead), 403, 'Unauthorized action.');

    try {
        DB::beginTransaction();

        $data = $request->validated();

        $data['numbers']    = isset($data['numbers']) ? array_values(array_filter($data['numbers'], fn($v) => filled($v))) : [];
        $data['cards_json'] = isset($data['cards_json']) ? array_values(array_filter($data['cards_json'], fn($v) => filled($v))) : [];

        // Non-elevated users can't change assignment fields
        if (!$this->isElevated()) {
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
            if (!$this->isElevated() && (int)$lead->assigned_to === (int)$user->id) {
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
                            'is_primary'  => true,
                            'assigned_by' => Auth::id(),
                            'updated_at'  => now()
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
                                'user_id'     => $newUserId,
                                'assigned_by' => Auth::id(),
                                'updated_at'  => now()
                            ]);
                    } else {
                        DB::table('lead_user')->insert([
                            'lead_id'     => $lead->id,
                            'user_id'     => $newUserId,
                            'assigned_by' => Auth::id(),
                            'is_primary'  => true,
                            'created_at'  => now(),
                            'updated_at'  => now()
                        ]);
                    }
                }
            }
        }

        DB::commit();

        session()->flash('success', 'Lead updated successfully.');

        if (Auth::user()->isAdmin()) {
            $content  = $lead->generateTextReport();
            $filename = $this->generateTextReportFilename($lead);
            $headers  = [
                'Content-Type'        => 'text/plain',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Cache-Control'       => 'no-store, no-cache, must-revalidate, max-age=0',
            ];
            session()->flash('redirect_to', route('leads.show', $lead));
            return Response::make($content, 200, $headers);
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
        'status'         => 'required|in:New Lead,Super Lead',
        'assignee_ids'   => 'required|array|min:1',
        'assignee_ids.*' => 'integer|exists:users,id',
        'leads_count'    => 'required|integer|min:1|max:1000',
        'busy_threshold' => 'nullable|integer|min:0',
    ]);

    $perUser       = (int) $data['leads_count'];
    $busyThreshold = $data['busy_threshold'] ?? null;

    try {
        return DB::transaction(function () use ($data, $perUser, $busyThreshold) {
            $users = User::whereIn('id', $data['assignee_ids'])->get();

            $validUsers = $users->filter(function ($u) {
                if (method_exists($u, 'hasRole')) return $u->hasRole('user');
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
                    return back()->with('error', 'No eligible users after applying the "Exclude if assigned ≥" filter.');
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
                if ($remaining <= 0) break;

                $take = min($perUser, $remaining);
                $chunk = $leads->slice($cursor, $take);
                $cursor += $chunk->count();

                foreach ($chunk as $lead) {
                    $lead->assigned_to   = $user->id;
                    $lead->assigned_time = now(); // <-- stamp time
                    $lead->save();

                    DB::table('lead_user')->updateOrInsert(
                        ['lead_id' => $lead->id, 'user_id' => $user->id],
                        [
                            'assigned_by' => Auth::id(),
                            'is_primary'  => true,
                            'created_at'  => now(),
                            'updated_at'  => now(),
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
                $parts[] = "{$count} → {$name}";
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
        'assigned_to'    => ['nullable', 'exists:users,id'],
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
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control'       => 'no-store, no-cache, must-revalidate, max-age=0',
        ]);
    }

    public function downloadTxt(Lead $lead)
    {
        $user = auth()->user();

        // ✅ Ensure the user is authenticated (optional, usually handled by middleware)
        abort_unless($user, 403, 'You must be logged in to download this document.');

        // ✅ Remove role-based restrictions (any authenticated user)
        $lines = [];

        $lines[] = "=== Lead Details ===";
        $lines[] = "Name: " . trim($lead->first_name . ' ' . $lead->middle_initial . ' ' . $lead->surname);
        $lines[] = "Gen Code: " . ($lead->gen_code ?: '—');
        $lines[] = "Age: " . ($lead->age ?: '—');
        $lines[] = "SSN: " . ($lead->ssn ?: '—');

        $lines[] = "";
        $lines[] = "Address:";
        $lines[] = "- Street: " . ($lead->street ?: '—');
        $lines[] = "- City: " . ($lead->city ?: '—');
        $lines[] = "- State: " . ($lead->state_abbreviation ?: '—');
        $lines[] = "- Zip Code: " . ($lead->zip_code ?: '—');

        $lines[] = "";
        $lines[] = "Phone Numbers:";
        $numbers = is_array($lead->numbers)
            ? $lead->numbers
            : (json_decode($lead->numbers ?? '[]', true) ?? []);
        $lines[] = count($numbers)
            ? implode(PHP_EOL, array_map(fn($n) => "- $n", $numbers))
            : "- No numbers found";

        $lines[] = "";
        $lines[] = "Custom Fields:";
        $lines[] = "- XFC06: " . ($lead->xfc06 ?: '—');
        $lines[] = "- XFC07: " . ($lead->xfc07 ?: '—');
        $lines[] = "- DEMO7: " . ($lead->demo7 ?: '—');
        $lines[] = "- DEMO9: " . ($lead->demo9 ?: '—');

        $lines[] = "";
        $lines[] = "Financial:";
        $lines[] = "- FICO: " . ($lead->fico ?: '—');
        $lines[] = "- Balance: " . ($lead->balance ?: '—');
        $lines[] = "- Credits: " . ($lead->credits ?: '—');

        $lines[] = "- Cards:";
        $cards = is_array($lead->cards_json)
            ? $lead->cards_json
            : (json_decode($lead->cards_json ?? '[]', true) ?? []);
        $lines[] = count($cards)
            ? implode(PHP_EOL, array_map(fn($c) => "  - $c", $cards))
            : "  - None";

        $lines[] = "";
        $lines[] = "Status: " . ($lead->status ?: '—');
        $lines[] = "Assigned To: " . optional($lead->assignee)->name;
        $lines[] = "Super Agent: " . optional($lead->superAgent)->name;
        $lines[] = "Closer: " . optional($lead->closer)->name;

        $lines[] = "";
        $lines[] = "Notes:";
        $lines[] = $lead->notes ?: '—';

        $lines[] = "";
        $lines[] = "Created At: " . ($lead->created_at?->toDateTimeString() ?? '—');
        $lines[] = "Created By: " . optional($lead->creator)->name;

        $content = implode(PHP_EOL, $lines);

        $filename = str_replace(' ', '_', trim($lead->first_name . ' ' . $lead->surname) ?: 'Lead') . '_Details.txt';

        return response($content, 200, [
            'Content-Type' => 'text/plain',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
        ]);
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
        $data = array_map('str_getcsv', file($file->getRealPath()));

        // Assuming the first row is the header
        $headers = array_map('trim', $data[0]);
        unset($data[0]); // remove header row

        $inserted = 0;

        foreach ($data as $row) {
            $row = array_map('trim', $row);

            // Combine headers with row data
            $leadData = array_combine($headers, $row);

            if ($leadData === false) {
                continue;
            }

            // Parse 'numbers' and 'cards_json' columns as JSON if present, else set null
            $leadData['numbers'] = isset($leadData['numbers']) && $leadData['numbers'] !== ''
                ? $this->validateJsonOrNull($leadData['numbers'])
                : null;

            $leadData['cards_json'] = isset($leadData['cards_json']) && $leadData['cards_json'] !== ''
                ? $this->validateJsonOrNull($leadData['cards_json'])
                : null;

            // Add created_by manually
            $leadData['created_by'] = Auth::id();

            // ✅ Use status from CSV if available, otherwise default to "New Lead"
            if (isset($leadData['status']) && $leadData['status'] !== '') {
                $status = strtolower(trim($leadData['status']));

                // Normalize casing
                switch ($status) {
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
                        $leadData['status'] = 'New Lead'; // fallback
                }
            } else {
                $leadData['status'] = 'New Lead';
            }

            // Validate each row data
            $validator = Validator::make($leadData, [
                'first_name' => 'required|string|max:255',
                'surname' => 'required|string|max:255',
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
                'numbers' => 'nullable|string',
                'cards_json' => 'nullable|string',
                'created_by' => 'required|integer',
            ]);

            if ($validator->fails()) {
                continue;
            }

            Lead::create($validator->validated());
            $inserted++;
        }

        return redirect()->back()->with('success', "$inserted leads imported successfully from CSV.");
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
}
