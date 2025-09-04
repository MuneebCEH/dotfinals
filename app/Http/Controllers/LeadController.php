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
        $statuses   = self::STATUSES;                  // your hard-coded statuses
        $categories = Category::orderBy('name')->get();
        $users      = User::where('role', 'user')->orderBy('name')->get(); // regular users for bulk assign

        // Get counts for available leads by status (only unassigned leads)
        $statusCounts = [
            'New Lead' => Lead::where('status', 'New Lead')
                ->where(function ($q) {
                    $q->whereNull('assigned_to')
                        ->orWhere('assigned_to', 0);
                })
                ->count(),
            'Super Lead' => Lead::where('status', 'Super Lead')
                ->where(function ($q) {
                    $q->whereNull('assigned_to')
                        ->orWhere('assigned_to', 0);
                })
                ->count(),
        ];

        $isElevated = $this->isElevated();

        $query = Lead::query()
            ->with(['category', 'assignee'])         // eager-load
            // If NOT elevated, show only leads assigned to this user
            ->when(!$isElevated, function ($q) {
                $q->where('assigned_to', auth()->id());
            })
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
            ->when($request->filled('status') && in_array($request->status, $statuses, true), function ($q) use ($request) {
                $q->where('status', $request->status);
            })
            // Category filter
            ->when($request->filled('category') && ctype_digit((string)$request->category), function ($q) use ($request) {
                $q->where('category_id', $request->category);
            })
            ->orderByDesc('id');

        $leads = $query->paginate(10)->withQueryString();

        // Always use a Collection for $onlineUsers (prevents ->count() on array)
        $onlineUsers = collect();
        if ($isElevated) {
            $today = now()->format('Y-m-d');
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
            'users'        => $users, // Pass users for bulk assign dropdown
            'statusCounts' => $statusCounts, // Pass status counts for bulk assign modal
            'filters'      => [
                'q'        => $request->q ?? '',
                'status'   => $request->status ?? '',
                'category' => $request->category ?? '',
            ],
        ]);
    }

    public function myLeads(Request $request)
    {
        // status list used by the blade
        $statuses = self::STATUSES;

        // collect filters expected by the blade
        $filters = [
            'q'      => trim((string) $request->input('q', '')),
            'status' => (string) $request->input('status', ''),
        ];

        $query = Lead::query()
            ->with(['assignee'])            // avoid N+1 in table column
            ->where('assigned_to', Auth::id()); // only my leads

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

        // status filter (only allow valid statuses)
        if ($filters['status'] !== '' && in_array($filters['status'], $statuses, true)) {
            $query->where('status', $filters['status']);
        }

        // order + paginate; keep query string for pager links
        $leads = $query->latest()->paginate(15)->withQueryString();

        // Minimal set is fine for non-admin view
        return view('leads.index', [
            'leads'      => $leads,
            'statuses'   => $statuses,
            'filters'    => $filters,
            'categories' => collect(),     // not shown when not elevated
            'users'      => collect(),
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

            // Create the lead
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

            // Generate and store text report
            $textReportPath = $this->storeTextReport($lead);

            // Generate and save text report
            $content = $lead->generateTextReport();
            $filename = $this->generateTextReportFilename($lead);

            DB::commit();

            // Prepare response headers
            $headers = [
                'Content-Type' => 'text/plain',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            ];

            // Store success message and redirect URL in session
            session()->flash('success', 'Lead created successfully.');
            session()->flash('redirect_to', route('leads.show', $lead));

            // Return clean text file download
            return Response::make($content, 200, $headers);
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
        // Only elevated or the assignee can view
        if (!$this->isElevated() && $lead->assigned_to !== Auth::id()) {
            abort(403);
        }

        $lead->load(['category', 'assignee', 'superAgent']);

        return view('leads.show', compact('lead'));
    }

    public function edit(Lead $lead)
    {
        // Only elevated or the assignee can edit
        if (!$this->isElevated() && $lead->assigned_to !== Auth::id()) {
            abort(403);
        }

        $categories  = Category::orderBy('name')->get();
        $statuses    = self::STATUSES;

        // for elevated: fill selects
        $tos         = User::where('role', 'user')->orderBy('name')->get();
        $superAgents = User::where('role', 'super_agent')->orderBy('name')->get();
        $closers     = User::where('role', 'closer')->orderBy('name')->get();

        return view('leads.edit', compact('lead', 'categories', 'statuses', 'tos', 'superAgents', 'closers'));
    }

    public function update(UpdateLeadRequest $request, Lead $lead)
    {
        $user = Auth::user();
        if (!$user || (!$this->isElevated() && $lead->assigned_to !== $user->id)) {
            abort(403, 'Unauthorized action.');
        }

        try {
            DB::beginTransaction();

            // Get validated data
            $data = $request->validated();

            // Process arrays and JSON
            $data['numbers'] = isset($data['numbers']) ? array_values(array_filter($data['numbers'], fn($v) => filled($v))) : [];
            $data['cards_json'] = isset($data['cards_json']) ? array_values(array_filter($data['cards_json'], fn($v) => filled($v))) : [];

            // Non-elevated cannot reassign users
            if (!$this->isElevated()) {
                unset($data['assigned_to'], $data['super_agent_id'], $data['closer_id']);
            }

            // Handle PDF file
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

            // Capture original assignee before update for pivot table handling
            $previousAssignedTo = $lead->assigned_to;

            // Update lead with validated data
            $lead->update($data);

            // Update lead_user pivot table if assignment changed
            if (isset($data['assigned_to']) && $data['assigned_to'] !== $previousAssignedTo) {
                $newUserId = $data['assigned_to'];

                if (empty($newUserId)) {
                    // Clear assignment - demote all to non-primary
                    DB::table('lead_user')
                        ->where('lead_id', $lead->id)
                        ->update(['is_primary' => false, 'updated_at' => now()]);
                } else {
                    // Check if new user already has a row
                    $existingRow = DB::table('lead_user')
                        ->where('lead_id', $lead->id)
                        ->where('user_id', $newUserId)
                        ->first();

                    if ($existingRow) {
                        // Promote existing row to primary
                        DB::table('lead_user')
                            ->where('id', $existingRow->id)
                            ->update([
                                'is_primary' => true,
                                'assigned_by' => Auth::id(),
                                'updated_at' => now()
                            ]);

                        // Demote others
                        DB::table('lead_user')
                            ->where('lead_id', $lead->id)
                            ->where('id', '!=', $existingRow->id)
                            ->update(['is_primary' => false, 'updated_at' => now()]);
                    } else {
                        // No existing row - update primary or create new
                        $primaryRow = DB::table('lead_user')
                            ->where('lead_id', $lead->id)
                            ->where('is_primary', true)
                            ->first();

                        if ($primaryRow) {
                            // Update primary row
                            DB::table('lead_user')
                                ->where('id', $primaryRow->id)
                                ->update([
                                    'user_id' => $newUserId,
                                    'assigned_by' => Auth::id(),
                                    'updated_at' => now()
                                ]);
                        } else {
                            // Create new primary row
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

            // Generate text report for download
            $content = $lead->generateTextReport();
            $filename = $this->generateTextReportFilename($lead);

            // Prepare response headers
            $headers = [
                'Content-Type' => 'text/plain',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            ];

            // Store success message and redirect URL in session
            session()->flash('success', 'Lead updated successfully.');
            session()->flash('redirect_to', route('leads.show', $lead));

            // Return clean text file download
            return Response::make($content, 200, $headers);
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
        // Only elevated can bulk assign
        abort_unless($this->isElevated(), 403);

        // Validate inputs
        $data = $request->validate([
            'status'         => 'required|in:New Lead,Super Lead',
            'assignee_ids'   => 'required|array|min:1',
            'assignee_ids.*' => 'integer|exists:users,id',
            'leads_count'    => 'required|integer|min:1|max:1000',

            // Optional advanced filter: exclude users who already have >= X leads
            'busy_threshold' => 'nullable|integer|min:0',
        ]);

        $perUser       = (int) $data['leads_count'];          // fixed # to give EACH selected user
        $busyThreshold = $data['busy_threshold'] ?? null;     // null => ignore

        try {
            return DB::transaction(function () use ($data, $perUser, $busyThreshold) {
                // 1) Load selected users and keep only those with role "user"
                /** @var \Illuminate\Support\Collection<int,\App\Models\User> $users */
                $users = User::whereIn('id', $data['assignee_ids'])->get();

                $validUsers = $users->filter(function ($u) {
                    // adapt if you use Spatie roles or a 'role' column
                    if (method_exists($u, 'hasRole')) {
                        return $u->hasRole('user');
                    }
                    return ($u->role ?? null) === 'user';
                })->values();

                if ($validUsers->isEmpty()) {
                    return back()->with('error', 'Please select at least one teammate with the "user" role.');
                }

                // 2) Current workload (assigned leads) — used for fairness and busy filter
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

                // Sort by current workload ascending so lower-load users are first
                $validUsers = $validUsers->sortBy(fn($u) => (int) ($workload[$u->id] ?? 0))->values();

                // 3) Fetch the oldest, unassigned leads for the requested status
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

                // 4) Assign exactly $perUser to EACH user (or fewer if we run out)
                $assignments = [];      // [userName => count]
                $cursor      = 0;       // slice pointer into $leads
                $actuallyAssigned = 0;

                foreach ($validUsers as $user) {
                    $remaining = max(0, $leads->count() - $cursor);
                    if ($remaining <= 0) break;

                    $take = min($perUser, $remaining);
                    $chunk = $leads->slice($cursor, $take);
                    $cursor += $chunk->count();

                    foreach ($chunk as $lead) {
                        $lead->assigned_to = $user->id;
                        // keep the status as selected (already matches)
                        $lead->save();

                        // update pivot
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

                // 5) Build summary message
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

        // Ensure super_agent_id really is super agent
        if (!empty($data['super_agent_id'])) {
            $isSuper = User::where('id', $data['super_agent_id'])->where('is_super_agent', true)->exists();
            abort_unless($isSuper, 422, 'Selected user is not a Super Agent.');
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
    protected function ensureCanAccessLead(Lead $lead): void
    {
        if ($this->isElevated()) {
            return;
        }

        $uid = Auth::id();

        abort_unless(
            ($lead->assigned_to && $lead->assigned_to == $uid) ||
                ($lead->super_agent_id && $lead->super_agent_id == $uid),
            403
        );
    }

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
