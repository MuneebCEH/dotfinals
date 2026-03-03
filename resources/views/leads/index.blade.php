@php
    // User role and permissions
    $currentUser = auth()->user();

    // User role checks
    $isLeadManager = method_exists($currentUser, 'hasRole')
        ? $currentUser->hasRole('lead_manager')
        : ($currentUser->role ?? null) === 'lead_manager';

    $isAdmin = $currentUser->isAdmin() || $isLeadManager;
    $isMaxOut = $currentUser->role === 'max_out';
    $isCloser = $currentUser->isCloser();
    $isSuperAgent = $currentUser->isSuperAgent();

    // Initialize the base query
    $query = \App\Models\Lead::query();

    // Role-based query restrictions
    if ($isMaxOut) {
        // For max_out users, show ALL Max Out leads
        $query->where('status', 'Max Out');
    }

    // Data for admin functionality
    $users = $isAdmin ? \App\Models\User::all() : collect();

    // Online users (ensure a Collection to safely call ->count())
    $onlineUsers = $isAdmin ? (isset($onlineUsers) ? collect($onlineUsers) : collect()) : collect();

    // UI configuration
    $cardClass = 'card-premium rounded-2xl shadow-2xl border border-gray-200/50 dark:border-gray-700/50';
    $tableHeaderClass =
        'px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest';
    $btnBaseClass = 'inline-flex items-center gap-2 px-5 py-2.5 rounded-xl transition-all duration-300 font-bold text-sm shadow-sm';
    $btnGhostClass =
        $btnBaseClass .
        ' border border-gray-200/50 dark:border-gray-700/50 text-gray-700 dark:text-gray-300 bg-white/50 dark:bg-gray-800/50 hover:bg-white dark:hover:bg-gray-800 hover:shadow-md';
    $btnPrimaryClass = $btnBaseClass . ' bg-gradient-to-br from-primary-500 to-primary-600 text-white hover:shadow-primary-500/25 hover:shadow-lg transform hover:-translate-y-0.5';

    // Filter state (now includes "today")
    $hasActiveFilters = request()->hasAny(['q', 'status', 'category', 'today', 'days', 'date', 'from', 'to']);

    // Current workload to show beside each user
    $assignedCounts = \App\Models\Lead::selectRaw('assigned_to, COUNT(*) as c')
        ->groupBy('assigned_to')
        ->pluck('c', 'assigned_to');
@endphp

@extends('layouts.app')

@section('title', $isMaxOut ? 'Max Out Leads Management' : 'Leads Management')
@section('page-title', $isMaxOut ? 'Max Out Leads' : 'Leads')

@section('description')
    @if ($isMaxOut)
        Manage and process your assigned Max Out leads
    @else
        Manage and process your assigned leads
    @endif
@endsection

@push('modals')
    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>
        <div class="relative z-50 mx-auto w-full max-w-md p-8 mt-24">
            <div class="rounded-xl bg-white dark:bg-gray-800 shadow-xl border border-gray-200 dark:border-gray-700 p-6">
                <div class="text-center">
                    <div
                        class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30">
                        <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01M5.07 19h13.86c1.54 0 2.5-1.67 1.73-2.5L13.73 4c-.77-.83-1.96-.83-2.73 0L3.34 16.5c-.77.83.19 2.5 1.73 2.5z" />
                        </svg>
                    </div>
                    <h3 class="mb-2 text-lg font-semibold text-gray-900 dark:text-white">Delete Lead</h3>
                    <p class="mb-6 text-gray-600 dark:text-gray-400 text-sm">Are you sure you want to delete this lead? This
                        action cannot be undone.</p>
                    <div class="flex justify-center gap-3">
                        <form id="deleteForm" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                class="px-4 py-2 rounded-lg text-white font-medium bg-red-600 hover:bg-red-700 transition-colors text-sm">Confirm
                                Delete</button>
                        </form>
                        <button type="button" id="cancelDelete"
                            class="px-4 py-2 rounded-lg font-medium bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-500 transition-colors text-sm">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if ($isAdmin)
        <!-- Bulk Assign Modal (viewport-fit, scrollable) -->
        <div id="bulkAssignModal" class="fixed inset-0 z-50 hidden">
            <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" id="bulkAssignBackdrop"></div>

            <div class="relative z-50 mx-auto w-full max-w-5xl p-4 md:p-6 lg:p-8">
                <div
                    class="rounded-2xl bg-white dark:bg-gray-900 shadow-2xl border border-gray-200/70 dark:border-gray-700/60 flex flex-col max-h-[90vh]">
                    <!-- Sticky header -->
                    <div
                        class="flex items-start justify-between gap-6 p-5 md:p-6 border-b border-gray-200 dark:border-gray-800 sticky top-0 z-10 bg-white/90 dark:bg-gray-900/90 backdrop-blur">
                        <div class="space-y-1">
                            <h3 class="text-xl font-semibold text-gray-900 dark:text-white">Bulk Assign Leads</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Choose a status and number of leads, search
                                or filter teammates, preview the split, then assign.</p>
                        </div>
                        <button type="button" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                            data-close-bulk-assign aria-label="Close">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Scrollable body -->
                    <form id="bulkAssignForm" method="POST" action="{{ route('leads.bulk-assign') }}" class="overflow-y-auto">
                        @csrf

                        <div class="grid grid-cols-1 lg:grid-cols-[360px,1fr] gap-6 p-5 md:p-6">
                            <!-- LEFT controls -->
                            <aside class="space-y-5">
                                <div
                                    class="p-4 rounded-xl border border-gray-200 dark:border-gray-800 bg-gray-50/60 dark:bg-gray-800/40">
                                    <label for="assign_status"
                                        class="block mb-2 text-sm font-medium text-gray-800 dark:text-gray-200">Lead
                                        status</label>
                                    <select id="assign_status" name="status" required
                                        class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                                        <option value="">Select status</option>
                                        <option value="New Lead" data-count="{{ $statusCounts['New Lead'] ?? 0 }}">New Leads
                                            ({{ $statusCounts['New Lead'] ?? 0 }})</option>
                                        <option value="Super Lead" data-count="{{ $statusCounts['Super Lead'] ?? 0 }}">Super
                                            Leads ({{ $statusCounts['Super Lead'] ?? 0 }})</option>
                                    </select>
                                    <p id="availableCount" class="mt-2 text-xs text-gray-600 dark:text-gray-400">Please
                                        select a status</p>
                                </div>

                                <div
                                    class="p-4 rounded-xl border border-gray-200 dark:border-gray-800 bg-gray-50/60 dark:bg-gray-800/40">
                                    <label for="leads_count"
                                        class="block mb-2 text-sm font-medium text-gray-800 dark:text-gray-200">Number of
                                        leads</label>
                                    <div class="flex gap-2">
                                        <input id="leads_count" name="leads_count" type="number" min="1" value="10" required
                                            class="w-40 px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                                        <div class="flex-1">
                                            <p class="text-xs text-gray-600 dark:text-gray-400">We’ll split these between
                                                selected teammates.</p>
                                            <p id="leadsPerUser" class="text-xs text-gray-600 dark:text-gray-400 mt-1"></p>
                                        </div>
                                    </div>
                                </div>

                                <div
                                    class="p-4 rounded-xl border border-blue-200 dark:border-blue-900/40 bg-blue-50/60 dark:bg-blue-900/20">
                                    <h4 class="font-medium text-blue-900 dark:text-blue-200 text-sm">Tips</h4>
                                    <ul class="mt-1 text-xs text-blue-900/80 dark:text-blue-200/80 space-y-1">
                                        <li>Use search & sort to quickly find teammates.</li>
                                        <li>“Preview” shows the exact split before assigning.</li>
                                    </ul>
                                </div>
                            </aside>

                            <!-- RIGHT list & actions -->
                            <section class="space-y-4">
                                <!-- Sticky tabs -->
                                <div
                                    class="flex items-center gap-6 border-b border-gray-200 dark:border-gray-800 sticky top-0 z-10 bg-white/90 dark:bg-gray-900/90 backdrop-blur -mt-2 pt-2">
                                    <button type="button" data-user-tab="online"
                                        class="tab-btn -mb-px border-b-2 border-blue-600 text-blue-600 px-1.5 pb-2 text-sm font-medium">Online
                                        ({{ $onlineUsers->count() }})</button>
                                    <button type="button" data-user-tab="all"
                                        class="tab-btn -mb-px border-b-2 border-transparent text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 px-1.5 pb-2 text-sm font-medium">All
                                        Users ({{ $users->count() }})</button>
                                </div>

                                <!-- Toolbar -->
                                <div class="flex flex-wrap items-center gap-3">
                                    <div class="relative flex-1 min-w-[220px]">
                                        <div class="pointer-events-none absolute inset-y-0 left-0 pl-3 flex items-center">
                                            <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                            </svg>
                                        </div>
                                        <input id="userSearch" type="text" placeholder="Search by name or email…"
                                            class="pl-10 w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                                    </div>

                                    <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                                        <input id="selectAllUsers" type="checkbox"
                                            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                        Select all (filtered)
                                    </label>

                                    <div class="flex items-center gap-2">
                                        <span class="text-sm text-gray-700 dark:text-gray-300">Sort</span>
                                        <select id="sortUsers"
                                            class="px-2 py-1.5 text-sm rounded-md border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-blue-500">
                                            <option value="load-asc">Lowest workload</option>
                                            <option value="load-desc">Highest workload</option>
                                            <option value="name-asc">Name A→Z</option>
                                            <option value="name-desc">Name Z→A</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Lists -->
                                <div id="usersScrollArea" class="space-y-2">
                                    <!-- Online -->
                                    <div id="usersOnlineWrap" class="space-y-2">
                                        @forelse ($onlineUsers as $onlineUser)
                                            @php
                                                $count = (int) ($assignedCounts[$onlineUser->id] ?? 0);
                                                $isEligible = method_exists($onlineUser, 'hasRole')
                                                    ? $onlineUser->hasRole('user')
                                                    : (property_exists($onlineUser, 'role')
                                                        ? $onlineUser->role === 'user'
                                                        : true);
                                            @endphp
                                            <div class="user-row flex items-center justify-between gap-3 p-3 rounded-xl border {{ $isEligible ? 'border-emerald-200 dark:border-emerald-900/40 bg-white dark:bg-gray-800' : 'opacity-60 border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-900/40' }}"
                                                data-name="{{ Str::lower($onlineUser->name) }}"
                                                data-email="{{ Str::lower($onlineUser->email ?? '') }}" data-load="{{ $count }}"
                                                data-eligible="{{ $isEligible ? '1' : '0' }}">
                                                <div class="flex items-center gap-3 min-w-0">
                                                    <input type="checkbox" name="assignee_ids[]" value="{{ $onlineUser->id }}"
                                                        class="user-checkbox rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                                        {{ $isEligible ? '' : 'disabled' }}>
                                                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                                                    <div class="min-w-0">
                                                        <label
                                                            class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate cursor-pointer">{{ $onlineUser->name }}</label>
                                                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                                                            {{ $onlineUser->email ?? '—' }}
                                                        </p>
                                                    </div>
                                                </div>
                                                <span
                                                    class="shrink-0 text-xs px-2 py-1 rounded-lg bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300">{{ $count }}
                                                    assigned</span>
                                            </div>
                                        @empty
                                            <div
                                                class="p-4 rounded-xl border border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-900 text-sm text-gray-600 dark:text-gray-300">
                                                No team members are currently online.
                                            </div>
                                        @endforelse
                                    </div>

                                    <!-- All -->
                                    <div id="usersAllWrap" class="space-y-2 hidden">
                                        @foreach ($users as $user)
                                            @php
                                                $count = (int) ($assignedCounts[$user->id] ?? 0);
                                                $isEligible = method_exists($user, 'hasRole')
                                                    ? $user->hasRole('user')
                                                    : (property_exists($user, 'role')
                                                        ? $user->role === 'user'
                                                        : true);
                                                $isOnline = $onlineUsers->pluck('id')->contains($user->id);
                                            @endphp
                                            <div class="user-row flex items-center justify-between gap-3 p-3 rounded-xl border {{ $isEligible ? 'border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-800' : 'opacity-60 border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-900/40' }}"
                                                data-name="{{ Str::lower($user->name) }}"
                                                data-email="{{ Str::lower($user->email ?? '') }}" data-load="{{ $count }}"
                                                data-eligible="{{ $isEligible ? '1' : '0' }}">
                                                <div class="flex items-center gap-3 min-w-0">
                                                    <input type="checkbox" name="assignee_ids[]" value="{{ $user->id }}"
                                                        class="user-checkbox rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                                        {{ $isEligible ? '' : 'disabled' }}>
                                                    <span
                                                        class="w-2.5 h-2.5 rounded-full {{ $isOnline ? 'bg-emerald-500' : 'bg-gray-400 dark:bg-gray-600' }}"></span>
                                                    <div class="min-w-0">
                                                        <label
                                                            class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate cursor-pointer">{{ $user->name }}</label>
                                                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                                                            {{ $user->email ?? '—' }}
                                                        </p>
                                                    </div>
                                                </div>
                                                <span
                                                    class="shrink-0 text-xs px-2 py-1 rounded-lg bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300">{{ $count }}
                                                    assigned</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                <!-- Inline feedback -->
                                <div class="pt-1 space-y-3">
                                    <div class="flex items-center justify-between text-xs">
                                        <div id="selectedUsersCount" class="text-blue-700 dark:text-blue-300"><span
                                                id="selectedCount">0</span> selected</div>
                                        <div class="text-gray-500 dark:text-gray-400"><span id="filteredCount">0</span>
                                            shown</div>
                                    </div>
                                    <div id="inlineErrors" class="hidden text-sm text-red-600 dark:text-red-400"></div>
                                    <div id="distributionPreview"
                                        class="hidden p-3 rounded-xl border border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-900 text-xs text-gray-700 dark:text-gray-300">
                                    </div>
                                </div>
                            </section>
                        </div>

                        <!-- Sticky footer buttons -->
                        <div
                            class="sticky bottom-0 z-10 bg-white/90 dark:bg-gray-900/90 backdrop-blur border-t border-gray-200 dark:border-gray-800 px-5 md:px-6 py-4 flex justify-end gap-3">
                            <button type="button" id="cancelBulkAssign"
                                class="px-4 py-2 rounded-lg font-medium bg-gray-200 dark:bg-gray-800 text-gray-800 dark:text-gray-200 hover:bg-gray-300 dark:hover:bg-gray-700">Cancel</button>
                            <button type="submit" id="bulkAssignSubmit"
                                class="px-5 py-2.5 rounded-lg font-medium text-white bg-blue-600 hover:bg-blue-700 disabled:opacity-40 disabled:cursor-not-allowed">
                                Assign Leads
                                <span id="assignChip"
                                    class="ml-2 hidden align-middle px-2 py-0.5 rounded-md bg-blue-500/20 border border-blue-400/40 text-xs">0
                                    users</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>


        <!-- Bulk Delete Modal -->
        <div id="bulkDeleteModal" class="fixed inset-0 z-50 hidden">
            <div class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>
            <div class="relative z-50 mx-auto w-full max-w-md p-8 mt-24">
                <div class="rounded-xl bg-white dark:bg-gray-800 shadow-xl border border-gray-200 dark:border-gray-700 p-6">
                    <div class="text-center">
                        <div
                            class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30">
                            <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01M5.07 19h13.86c1.54 0 2.5-1.67 1.73-2.5L13.73 4c-.77-.83-1.96-.83-2.73 0L3.34 16.5c-.77.83.19 2.5 1.73 2.5z" />
                            </svg>
                        </div>
                        <h3 class="mb-2 text-lg font-semibold text-gray-900 dark:text-white">Delete Selected Leads</h3>
                        <p class="mb-6 text-gray-600 dark:text-gray-400 text-sm">
                            You’re about to delete <span id="bulkDeleteCount" class="font-semibold">0</span> lead(s).
                            This action cannot be undone.
                        </p>
                        <form id="bulkDeleteForm" method="POST" action="{{ route('leads.bulk-destroy') }}">
                            @csrf
                            @method('DELETE')
                            <div id="bulkDeleteIds"></div> <!-- hidden inputs injected by JS -->
                            <div class="flex justify-center gap-3">
                                <button type="submit"
                                    class="px-4 py-2 rounded-lg text-white font-medium bg-red-600 hover:bg-red-700 transition-colors text-sm">
                                    Confirm Delete
                                </button>
                                <button type="button" id="cancelBulkDelete"
                                    class="px-4 py-2 rounded-lg font-medium bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-500 transition-colors text-sm">
                                    Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endpush

@section('content')
    <div class="space-y-6 animate-on-load">
        {{-- Leads Intelligence Header --}}
        <div
            class="card-premium p-8 flex flex-col md:flex-row md:items-center justify-between gap-6 overflow-hidden relative">
            <div class="relative z-10">
                <h1 class="text-4xl font-black tracking-tight text-white mb-2">Leads <span
                        class="gradient-text">Vault</span></h1>
                <p class="text-slate-400 font-medium">Managing {{ number_format($leads->total()) }} intelligence entries
                    across the ecosystem.</p>
            </div>

            <div class="flex items-center gap-3 relative z-10">
                @if (auth()->user()?->isAdmin() || auth()->user()?->isLeadManager())
                    <a href="{{ route('leads.create') }}"
                        class="px-8 py-4 bg-indigo-600 hover:bg-indigo-500 text-white rounded-2xl font-black transition-all shadow-xl shadow-indigo-600/20 flex items-center gap-3 group">
                        <i class="fas fa-plus group-hover:rotate-90 transition-transform"></i>
                        <span>Inject New Lead</span>
                    </a>
                @endif
            </div>

            {{-- Background Accent --}}
            <div class="absolute -right-20 -top-20 w-64 h-64 bg-indigo-500/10 blur-[100px] rounded-full"></div>
        </div>

        {{-- Workspace Grid --}}
        <div class="grid grid-cols-1 xl:grid-cols-4 gap-6">
            {{-- Search & Filter Command Panel --}}
            <div class="xl:col-span-1 space-y-6">
                <div class="card-premium p-6">
                    <form method="GET" action="{{ route('leads.index') }}" class="space-y-6">
                        <div>
                            <label class="text-[10px] font-black uppercase tracking-widest text-slate-500 mb-3 block">Global
                                Search</label>
                            <div class="relative">
                                <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-500"></i>
                                <input type="text" name="search" value="{{ request('search') }}"
                                    class="w-full pl-12 pr-4 py-4 bg-white/5 border border-white/10 rounded-2xl text-white placeholder:text-slate-600 focus:border-indigo-500/50 focus:ring-0 transition-all"
                                    placeholder="ID, Name, Phone...">
                            </div>
                        </div>

                        <div>
                            <label class="text-[10px] font-black uppercase tracking-widest text-slate-500 mb-3 block">Filter
                                by Status</label>
                            <select name="status"
                                class="w-full px-4 py-4 bg-white/5 border border-white/10 rounded-2xl text-white focus:border-indigo-500/50 focus:ring-0 transition-all">
                                <option value="">All Scopes</option>
                                @foreach($statuses as $label)
                                    <option value="{{ $label }}" {{ request('status') == $label ? 'selected' : '' }}>{{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        @if($isAdmin)
                            <div>
                                <label
                                    class="text-[10px] font-black uppercase tracking-widest text-slate-500 mb-3 block">Assigned
                                    Specialist</label>
                                <select name="assigned_to"
                                    class="w-full px-4 py-4 bg-white/5 border border-white/10 rounded-2xl text-white focus:border-indigo-500/50 focus:ring-0 transition-all">
                                    <option value="">Every Agent</option>
                                    @foreach($tos as $to)
                                        <option value="{{ $to->id }}" {{ request('assigned_to') == $to->id ? 'selected' : '' }}>
                                            {{ $to->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <button type="submit"
                            class="w-full py-4 bg-white/10 hover:bg-white/20 text-white rounded-2xl font-black transition-all border border-white/10">
                            Apply Intelligence Filters
                        </button>

                        <a href="{{ route('leads.index') }}"
                            class="block text-center text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 hover:text-indigo-400 transition-colors">
                            Reset All Parameters
                        </a>
                    </form>
                </div>

                {{-- Quick Stats Island --}}
                <div class="card-premium p-6">
                    <h3 class="text-[10px] font-black uppercase tracking-widest text-slate-500 mb-6">Workspace Metrics</h3>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between p-4 bg-white/5 rounded-2xl border border-white/5">
                            <span class="text-xs font-bold text-slate-400">Filtered Depth</span>
                            <span class="text-sm font-black text-white">{{ $leads->total() }}</span>
                        </div>
                        <div
                            class="flex items-center justify-between p-4 bg-emerald-500/10 rounded-2xl border border-emerald-500/20">
                            <span class="text-xs font-bold text-emerald-400/80">Success Rate</span>
                            <span class="text-sm font-black text-emerald-400">14.2%</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Main Intelligence Stream --}}
            <div class="xl:col-span-3 card-premium overflow-hidden flex flex-col">
                <div class="p-6 border-b border-white/5 bg-white/[0.02] flex items-center justify-between">
                    <h3 class="text-[10px] font-black uppercase tracking-widest text-slate-500">Live Workspace Stream</h3>
                    <div class="flex gap-2">
                        <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div>
                        <span class="text-[10px] font-black uppercase tracking-widest text-emerald-500">Synced</span>
                    </div>
                </div>

                <div class="overflow-x-auto flex-1">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-black/20 text-[10px] font-black uppercase tracking-[0.2em] text-slate-500">
                                <th class="px-8 py-5">Core Target</th>
                                <th class="px-8 py-5">Intel Status</th>
                                <th class="px-8 py-5">Agent Control</th>
                                <th class="px-8 py-5">Injection Date</th>
                                <th class="px-8 py-5 text-right">Operations</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            @forelse($leads as $lead)
                                            <tr class="hover:bg-white/[0.03] transition-all group">
                                                <td class="px-8 py-6">
                                                    <div class="flex items-center gap-4">
                                                        <div
                                                            class="w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-500/20 to-purple-500/20 flex items-center justify-center font-black text-indigo-400 border border-indigo-500/20 shadow-lg">
                                                            {{ strtoupper(substr($lead->first_name, 0, 1)) }}
                                                        </div>
                                                        <div>
                                                            <p
                                                                class="text-sm font-black text-white group-hover:text-indigo-400 transition-colors">
                                                                {{ $lead->first_name }} {{ $lead->surname }}
                                                            </p>
                                                            <div class="flex items-center gap-2 mt-1">
                                                                <span class="text-[10px] font-bold text-slate-500">#{{ $lead->id }}</span>
                                                                <span class="w-1 h-1 rounded-full bg-slate-700"></span>
                                                                <span
                                                                    class="text-[10px] font-bold text-slate-500 uppercase">{{ $lead->city }}</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-8 py-6">
                                                    @php
                                                        $state = strtolower($lead->status);
                                                        $colorClass = match (true) {
                                                            str_contains($state, 'success') => 'emerald',
                                                            str_contains($state, 'call') => 'amber',
                                                            str_contains($state, 'dead') => 'rose',
                                                            str_contains($state, 'max') => 'orange',
                                                            default => 'indigo'
                                                        };
                                                    @endphp
                                <div
                                                        class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-{{ $colorClass }}-500/10 text-{{ $colorClass }}-400 border border-{{ $colorClass }}-500/20">
                                                        <div class="w-1.5 h-1.5 rounded-full bg-{{ $colorClass }}-500"></div>
                                                        <span
                                                            class="text-[10px] font-black uppercase tracking-wider">{{ $lead->status }}</span>
                                                    </div>
                                                </td>
                                                <td class="px-8 py-6">
                                                    <div class="flex items-center gap-3">
                                                        <div
                                                            class="w-8 h-8 rounded-full bg-white/5 flex items-center justify-center text-[10px] font-bold text-slate-400">
                                                            <i class="fas fa-user-shield"></i>
                                                        </div>
                                                        <span
                                                            class="text-xs font-bold text-slate-300">{{ $lead->assignee?->name ?? 'None' }}</span>
                                                    </div>
                                                </td>
                                                <td class="px-8 py-6">
                                                    <p class="text-[11px] font-black text-slate-400">
                                                        {{ $lead->created_at->format('d M Y') }}</p>
                                                    <p class="text-[10px] text-slate-600 font-bold uppercase tracking-tighter">
                                                        {{ $lead->created_at->diffForHumans() }}</p>
                                                </td>
                                                <td class="px-8 py-6">
                                                    <div class="flex items-center justify-end gap-2">
                                                        <a href="{{ route('leads.edit', $lead) }}"
                                                            class="w-10 h-10 flex items-center justify-center bg-white/5 hover:bg-indigo-600 rounded-xl text-slate-400 hover:text-white transition-all border border-white/5 hover:border-indigo-500 shadow-lg">
                                                            <i class="fas fa-fingerprint"></i>
                                                        </a>
                                                        @if($isAdmin)
                                                            <form action="{{ route('leads.destroy', $lead) }}" method="POST"
                                                                onsubmit="return confirm('Purge this intelligence entry?')" class="inline">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit"
                                                                    class="w-10 h-10 flex items-center justify-center bg-white/5 hover:bg-rose-600 rounded-xl text-slate-400 hover:text-white transition-all border border-white/5 hover:border-rose-500 shadow-lg">
                                                                    <i class="fas fa-trash-alt"></i>
                                                                </button>
                                                            </form>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-8 py-32 text-center">
                                        <div
                                            class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-white/5 mb-6">
                                            <i class="fas fa-database text-slate-700 text-3xl"></i>
                                        </div>
                                        <p class="text-slate-500 font-black uppercase tracking-[0.3em] text-xs">No Signal
                                            Detected in Scopes</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination Island --}}
                <div class="p-8 border-t border-white/5 bg-black/10">
                    {{ $leads->links() }}
                </div>
            </div>
        </div>
    </div>


    @if ($isAdmin)
        <!-- Import Modal -->
        <div id="importModal" class="fixed inset-0 z-50 hidden">
            <div class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>
            <div class="relative z-50 mx-auto w-full max-w-md p-8 mt-24">
                <div class="rounded-xl bg-white dark:bg-gray-800 shadow-xl border border-gray-200 dark:border-gray-700 p-6">
                    <div class="text-center">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Import Leads</h2>
                        <form id="importForm" action="{{ route('leads.import') }}" method="POST" enctype="multipart/form-data"
                            class="space-y-4">
                            @csrf
                            <div>
                                <label for="leads_file"
                                    class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">Upload CSV
                                    File</label>
                                <input type="file" id="leads_file" name="leads_file" accept=".csv" required
                                    class="w-full px-3 py-2 border border-gray-200 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Supported format: CSV files only
                                </p>
                            </div>
                            <div class="flex justify-center gap-3">
                                <button type="button" id="cancelImport"
                                    class="px-3 py-2 rounded-lg font-medium bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-500 transition-colors text-sm">Cancel</button>
                                <button type="submit"
                                    class="px-3 py-2 rounded-lg text-white font-medium bg-blue-600 hover:bg-blue-700 transition-colors text-sm">Import
                                    File</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        /* ---------- Small helpers ---------- */
        const qs = (s, r = document) => r.querySelector(s);
        const qsa = (s, r = document) => Array.from(r.querySelectorAll(s));
        const num = (v, d = 0) => {
            const n = parseInt(v, 10);
            return isNaN(n) ? d : n;
        };

        /* ---------- Modal open/close ---------- */
        function openBulkAssignModal() {
            qs('#bulkAssignModal').classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
            updateAvailableCount();
            setTab('online');
            applySearchAndSort();
            recalcState();
            renderDistributionPreview();
            updateSelectAllState();
        }

        function closeBulkAssignModal() {
            qs('#bulkAssignModal').classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }

        /* ---------- Status / availability ---------- */
        function updateAvailableCount() {
            const select = qs('#assign_status');
            const el = qs('#availableCount');
            if (!select) return;
            if (select.value) {
                const c = parseInt(select.options[select.selectedIndex].getAttribute('data-count')) || 0;
                el.textContent = `${c} leads available for assignment`;
                const input = qs('#leads_count');
                input.setAttribute('max', c);
                if (parseInt(input.value) > c) input.value = Math.max(1, c);
            } else el.textContent = 'Please select a status';
        }

        /* ---------- Tabs / search / sort ---------- */
        let activeTab = 'online';

        function setTab(tab) {
            activeTab = tab;
            qsa('.tab-btn').forEach(b => b.classList.remove('border-blue-600', 'text-blue-600'));
            qs(`[data-user-tab="${tab}"]`).classList.add('border-blue-600', 'text-blue-600');
            qs('#usersOnlineWrap').classList.toggle('hidden', tab !== 'online');
            qs('#usersAllWrap').classList.toggle('hidden', tab !== 'all');
            const all = qs('#selectAllUsers');
            all.checked = false;
            all.indeterminate = false;
            applySearchAndSort();
            recalcState();
        }

        function container() {
            return activeTab === 'online' ? qs('#usersOnlineWrap') : qs('#usersAllWrap');
        }

        function applySearchAndSort() {
            const wrap = container();
            const term = (qs('#userSearch').value || '').trim().toLowerCase();
            const sort = qs('#sortUsers').value;
            const busy = num(qs('#busy_threshold')?.value, -1);

            const rows = qsa('.user-row', wrap);
            let visible = [];
            rows.forEach(row => {
                const name = row.getAttribute('data-name') || '';
                const email = row.getAttribute('data-email') || '';
                const load = num(row.getAttribute('data-load'), 0);
                const eligible = row.getAttribute('data-eligible') === '1';
                const show = (!term || name.includes(term) || email.includes(term)) && (busy < 0 || load < busy);
                row.style.display = show ? '' : 'none';
                const cb = row.querySelector('.user-checkbox');
                cb.disabled = !eligible || !show;
                if (cb.disabled) cb.checked = false;
                if (show) visible.push(row);
            });

            visible.sort((a, b) => {
                const nA = a.getAttribute('data-name'),
                    nB = b.getAttribute('data-name');
                const lA = num(a.getAttribute('data-load')),
                    lB = num(b.getAttribute('data-load'));
                if (sort === 'load-asc') return lA - lB;
                if (sort === 'load-desc') return lB - lA;
                if (sort === 'name-asc') return nA.localeCompare(nB);
                if (sort === 'name-desc') return nB.localeCompare(nA);
                return 0;
            });
            visible.forEach(r => wrap.appendChild(r));
            qs('#filteredCount').textContent = visible.length;
            updateSelectAllState();
            recalcState();
        }

        function updateSelectAllState() {
            const checks = qsa('.user-checkbox', container())
                .filter(cb => !cb.disabled && cb.closest('.user-row').style.display !== 'none');
            const checked = checks.filter(cb => cb.checked).length;
            const all = qs('#selectAllUsers');
            all.checked = checked > 0 && checked === checks.length;
            all.indeterminate = checked > 0 && checked < checks.length;
        }

        function toggleSelectAll(checked) {
            qsa('.user-checkbox', container()).forEach(cb => {
                if (!cb.disabled && cb.closest('.user-row').style.display !== 'none') {
                    cb.checked = checked;
                }
            });
            recalcState();
            updateSelectAllState();
        }

        /* ---------- Validation + Preview ---------- */
        function recalcState() {
            const perUser = num(qs('#leads_count').value);
            const selected = qsa('.user-checkbox:checked');
            const available = num(qs('#assign_status').options[qs('#assign_status').selectedIndex]?.getAttribute(
                'data-count'), 0);

            qs('#selectedCount').textContent = selected.length;
            qs('#selectedUsersCount').classList.toggle('hidden', selected.length === 0);

            const chip = qs('#assignChip');
            if (selected.length > 0) {
                chip.textContent = `${selected.length} users`;
                chip.classList.remove('hidden');
            } else chip.classList.add('hidden');

            const info = qs('#leadsPerUser');
            if (selected.length > 0 && perUser > 0) {
                info.textContent = `Each user will get ${perUser} lead(s)`;
            } else info.textContent = 'Select users and enter lead count';

            const errors = [];
            if (!qs('#assign_status').value) errors.push('Please select a lead status.');
            if (selected.length === 0) errors.push('Select at least one teammate.');
            if (perUser <= 0) errors.push('Enter a number of leads per user.');
            if ((perUser * selected.length) > available) {
                errors.push(`Only ${available} leads available (need ${perUser * selected.length}).`);
            }

            const box = qs('#inlineErrors');
            if (errors.length) {
                box.innerHTML = errors.map(e => '• ' + e).join('<br>');
                box.classList.remove('hidden');
            } else {
                box.classList.add('hidden');
                box.innerHTML = '';
            }

            qs('#bulkAssignSubmit').disabled = errors.length > 0;
            renderDistributionPreview();
        }

        function renderDistributionPreview() {
            const preview = qs('#distributionPreview');
            const perUser = num(qs('#leads_count').value);
            const rows = qsa('.user-checkbox:checked').map(cb => cb.closest('.user-row'));
            if (rows.length === 0 || perUser <= 0) {
                preview.classList.add('hidden');
                preview.innerHTML = '';
                return;
            }

            const html = rows.map(r => {
                const name = r.querySelector('label').textContent.trim();
                return `
                      <div class="flex items-center justify-between p-2 rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-800">
                        <span class="truncate">${name}</span>
                        <span class="font-medium">${perUser}</span>
                      </div>`;
            }).join('');

            preview.innerHTML = `
                    <div class="flex items-center justify-between mb-2">
                        <div class="font-medium">Preview</div>
                        <div class="text-gray-500">Each user will get ${perUser} lead(s)</div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2">${html}</div>
                `;
            preview.classList.remove('hidden');
        }

        /* ---------- Leads list AJAX (made global) ---------- */
        function initAjaxFiltering() {
            const $form = $('#leadsFilterForm');
            const $q = $('#qInput');
            const $status = $('#statusSelect');
            const $category = $('#categorySelect');
            const $today = $('#todayOnly'); // optional legacy toggle
            const $container = $('#leadsTableContainer');
            const $overlay = $('#ajaxOverlay');
            const $resultsMeta = $('#resultsMeta');

            function showOverlay() {
                $overlay.removeClass('hidden');
            }

            function hideOverlay() {
                $overlay.addClass('hidden');
            }

            function serializeFormToQuery($f, overrides = {}) {
                const params = new URLSearchParams($f.serialize());
                Object.entries(overrides).forEach(([k, v]) => {
                    if (v === '' || v == null) params.delete(k);
                    else params.set(k, v);
                });
                return params.toString();
            }

            // ✅ Expose globally so other code can call it and we can re-apply selection state after each load
            window.ajaxLoad = function (url) {
                showOverlay();
                $.get(url)
                    .done(function (html) {
                        const $html = $(html);
                        $container.html($html.find('#leadsTableContainer').html());
                        $resultsMeta.html($html.find('#resultsMeta').html());
                        window.history.pushState({}, '', url);

                        // Re-apply selection state and header checkbox after DOM replacement
                        applySelectionStateToDOM();
                        updateHeaderSelectAllState();
                        reflectSelectionUI();
                    })
                    .fail(function () {
                        window.location.href = url;
                    })
                    .always(hideOverlay);
            };

            $form.on('submit', function (e) {
                e.preventDefault();
                const s = serializeFormToQuery($form);
                window.ajaxLoad($form.attr('action') + (s ? '?' + s : ''));
            });

            $('#clearFilters').on('click', function (e) {
                e.preventDefault();
                window.ajaxLoad($(this).attr('href'));
            });

            $('.js-refresh').on('click', function (e) {
                e.preventDefault();
                window.ajaxLoad($(this).attr('href'));
            });

            $q.on('keyup', debounce(function () {
                const s = serializeFormToQuery($form, {
                    q: $q.val()
                });
                window.ajaxLoad($form.attr('action') + (s ? '?' + s : ''));
            }, 400));

            $status.on('change', function () {
                const s = serializeFormToQuery($form, {
                    status: $status.val(),
                    page: 1
                });
                window.ajaxLoad($form.attr('action') + (s ? '?' + s : ''));
            });

            if ($category.length) {
                $category.on('change', function () {
                    const s = serializeFormToQuery($form, {
                        category: $category.val(),
                        page: 1
                    });
                    window.ajaxLoad($form.attr('action') + (s ? '?' + s : ''));
                });
            }

            // Optional legacy "today" toggle
            $today.on?.('change', function () {
                const s = serializeFormToQuery($form, {
                    today: $today.is(':checked') ? '1' : '',
                    page: 1
                });
                window.ajaxLoad($form.attr('action') + (s ? '?' + s : ''));
            });

            // Delegated pagination (survives DOM replace)
            $('#leadsTableCard').on('click', '#paginationWrap a', function (e) {
                e.preventDefault();
                window.ajaxLoad($(this).attr('href'));
            });

            window.addEventListener('popstate', function () {
                window.ajaxLoad(location.href);
            });
        }

        function debounce(fn, delay) {
            let t;
            return (...a) => {
                clearTimeout(t);
                t = setTimeout(() => fn(...a), delay);
            };
        }

        /** ---- Bulk Delete Selection State ---- **/
        window.selectedLeadIds = window.selectedLeadIds || new Set();
        window._leadSelectionHandlersBound = window._leadSelectionHandlersBound || false;

        // ✅ Apply Set -> DOM after any content refresh
        function applySelectionStateToDOM() {
            $('#leadsTableContainer .lead-select').each(function () {
                const id = parseInt(this.dataset.id, 10);
                this.checked = window.selectedLeadIds.has(id) && !this.disabled;
            });
        }

        // ✅ One-time delegated handlers (survive DOM replacements)
        function initLeadSelectionHandlers() {
            if (window._leadSelectionHandlersBound) return;
            window._leadSelectionHandlersBound = true;

            // Row checkbox change (delegated)
            $('#leadsTableContainer').on('change', '.lead-select', function () {
                const id = parseInt(this.dataset.id, 10);
                if (this.checked) window.selectedLeadIds.add(id);
                else window.selectedLeadIds.delete(id);
                updateHeaderSelectAllState();
                reflectSelectionUI();
            });

            // Header select-all (delegated)
            $('#leadsTableCard').on('change', '#selectAllLeads', function () {
                const visible = $('#leadsTableContainer .lead-select').filter(function () {
                    const row = this.closest('tr');
                    return row && $(row).is(':visible') && !this.disabled;
                });

                visible.each((_, el) => {
                    const id = parseInt(el.dataset.id, 10);
                    el.checked = !!this.checked;
                    if (el.checked) window.selectedLeadIds.add(id);
                    else window.selectedLeadIds.delete(id);
                });

                // Trigger UI updates once after the batch
                updateHeaderSelectAllState();
                reflectSelectionUI();
            });
        }

        function updateHeaderSelectAllState() {
            const checks = $('#leadsTableContainer .lead-select').filter(function () {
                const row = this.closest('tr');
                return row && $(row).is(':visible') && !this.disabled;
            });
            const total = checks.length;
            const checked = checks.filter(':checked').length;

            const all = document.getElementById('selectAllLeads');
            if (!all) return;
            all.indeterminate = checked > 0 && checked < total;
            all.checked = total > 0 && checked === total;
        }

        function reflectSelectionUI() {
            const count = window.selectedLeadIds.size;
            const btn = document.getElementById('openBulkDelete');
            const chip = document.getElementById('bulkDeleteChip');
            if (btn) btn.disabled = count === 0;
            if (chip) {
                if (count > 0) {
                    chip.textContent = count;
                    chip.classList.remove('hidden');
                } else chip.classList.add('hidden');
            }
        }

        function openBulkDeleteModal() {
            const modal = document.getElementById('bulkDeleteModal');
            const countEl = document.getElementById('bulkDeleteCount');
            const idsWrap = document.getElementById('bulkDeleteIds');
            if (!modal || !countEl || !idsWrap) return;

            const ids = Array.from(window.selectedLeadIds);
            countEl.textContent = ids.length;
            idsWrap.innerHTML = ids.map(id => `<input type="hidden" name="ids[]" value="${id}">`).join('');
            modal.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        }

        function closeBulkDeleteModal() {
            const modal = document.getElementById('bulkDeleteModal');
            if (!modal) return;
            modal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }

        /* ---------- Online users refresher (unchanged) ---------- */
        (function () {
            const ONLINE_URL = "{{ route('users.online', ['minutes' => 3, 'tz' => 'Asia/Karachi']) }}";
            const ASSIGNED_COUNTS = @json($assignedCounts ?? []);
            const ONLINE_WRAP_SEL = '#usersOnlineWrap';
            const ONLINE_TAB_BTN = '[data-user-tab="online"]';
            let onlineRefreshTimer = null;

            async function loadOnlineUsers() {
                try {
                    const res = await fetch(ONLINE_URL, {
                        credentials: 'same-origin',
                        headers: {
                            'Accept': 'application/json'
                        }
                    });
                    if (!res.ok) throw new Error('Failed to fetch online users');
                    const json = await res.json();
                    const list = Array.isArray(json?.data) ? json.data : [];
                    renderOnlineUsers(list);
                    updateOnlineTabCount(list.length);
                    applySearchAndSort();
                    recalcState();
                } catch (e) {
                    renderOnlineUsers([]);
                    updateOnlineTabCount(0);
                }
            }

            function updateOnlineTabCount(n) {
                const btn = document.querySelector(ONLINE_TAB_BTN);
                if (!btn) return;
                const text = btn.textContent || 'Online';
                if (/\(.*\)/.test(text)) btn.textContent = text.replace(/\(.*\)/, `(${n})`);
                else btn.textContent = text.trim() + ` (${n})`;
            }

            function renderOnlineUsers(users) {
                const wrap = document.querySelector(ONLINE_WRAP_SEL);
                if (!wrap) return;

                if (!users.length) {
                    wrap.innerHTML = `
                <div class="p-4 rounded-xl border border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-900 text-sm text-gray-600 dark:text-gray-300">
                  No team members are currently online.
                </div>`;
                    return;
                }

                users.sort((a, b) => new Date(b.last_seen || 0) - new Date(a.last_seen || 0));

                const rowsHtml = users.map(u => {
                    const id = u.user_id ?? u.id;
                    const name = (u.name || 'Unknown').toString();
                    const role = (u.role || '').toString();
                    const eligible = role === 'user';
                    const load = parseInt(ASSIGNED_COUNTS[id] ?? 0, 10) || 0;

                    return `
              <div class="user-row flex items-center justify-between gap-3 p-3 rounded-xl border ${eligible
                            ? 'border-emerald-200 dark:border-emerald-900/40 bg-white dark:bg-gray-800'
                            : 'opacity-60 border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-900/40'}"
                   data-name="${name.toLowerCase()}"
                   data-email=""
                   data-load="${load}"
                   data-eligible="${eligible ? '1' : '0'}">
                <div class="flex items-center gap-3 min-w-0">
                  <input type="checkbox" name="assignee_ids[]" value="${id}"
                    class="user-checkbox rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                    ${eligible ? '' : 'disabled'}>
                  <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                  <div class="min-w-0">
                    <label class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate cursor-pointer">${name}</label>
                    <p class="text-xs text-gray-500 dark:text-gray-400 truncate">—</p>
                  </div>
                </div>
                <span class="shrink-0 text-xs px-2 py-1 rounded-lg bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300">${load} assigned</span>
              </div>`;
                }).join('');

                wrap.innerHTML = rowsHtml;
            }

            const _openBulkAssignModal = window.openBulkAssignModal;
            const _closeBulkAssignModal = window.closeBulkAssignModal;

            window.openBulkAssignModal = function () {
                if (typeof _openBulkAssignModal === 'function') _openBulkAssignModal();
                loadOnlineUsers();
                clearInterval(onlineRefreshTimer);
                onlineRefreshTimer = setInterval(loadOnlineUsers, 60_000);
            };

            window.closeBulkAssignModal = function () {
                if (typeof _closeBulkAssignModal === 'function') _closeBulkAssignModal();
                clearInterval(onlineRefreshTimer);
            };

            // When clicking the Online tab, refresh its list shortly after switching
            document.addEventListener('click', (e) => {
                const btn = e.target.closest('[data-user-tab="online"]');
                if (!btn) return;
                setTimeout(() => loadOnlineUsers(), 50);
            });

        })();

        /* ---------- Created filter dropdown (uses global ajaxLoad) ---------- */
        document.addEventListener('DOMContentLoaded', () => {
            const $form = $('#leadsFilterForm');
            const $menu = $('#createdMenu');
            const $btn = $('#createdFilterBtn');
            const $label = $('#createdFilterLabel');

            const $hToday = $('#createdToday');
            const $hDays = $('#createdDaysHidden');
            const $hDate = $('#createdDateHidden');
            const $hFrom = $('#createdFromHidden');
            const $hTo = $('#createdToHidden');

            const $daysInput = $('#createdDays');
            const $dateInput = $('#createdDate');
            const $fromInput = $('#createdFrom');
            const $toInput = $('#createdTo');

            function openMenu() {
                $menu.removeClass('hidden');
            }

            function closeMenu() {
                $menu.addClass('hidden');
            }

            function setLabel(text) {
                $label.text(text);
            }

            function clearAllHidden() {
                $hToday.val('');
                $hDays.val('');
                $hDate.val('');
                $hFrom.val('');
                $hTo.val('');
            }

            function push(overrides = {}) {
                const params = new URLSearchParams($form.serialize());
                Object.entries(Object.assign({
                    page: 1
                }, overrides)).forEach(([k, v]) => {
                    if (v === '' || v == null) params.delete(k);
                    else params.set(k, v);
                });
                const url = $form.attr('action') + (params.toString() ? '?' + params.toString() : '');
                if (typeof window.ajaxLoad === 'function') window.ajaxLoad(url);
                else window.location.href = url;
            }

            $btn.on('click', function () {
                $menu.hasClass('hidden') ? openMenu() : closeMenu();
            });
            document.addEventListener('click', (e) => {
                if (!document.getElementById('createdFilter').contains(e.target)) closeMenu();
            });

            $('.created-option').on('click', function () {
                const kind = this.dataset.kind;
                clearAllHidden();

                if (kind === 'today') {
                    $hToday.val('1');
                    setLabel('Today');
                    closeMenu();
                    push({
                        today: '1',
                        days: '',
                        date: '',
                        from: '',
                        to: ''
                    });
                    return;
                }
                if (kind === 'days') {
                    const d = parseInt(this.dataset.days || '7', 10);
                    $hDays.val(d);
                    setLabel(`Last ${d} days`);
                    closeMenu();
                    push({
                        days: d,
                        today: '',
                        date: '',
                        from: '',
                        to: ''
                    });
                    return;
                }
            });

            $('.created-open-custom').on('click', function () {
                const mode = this.dataset.mode;
                $('.created-custom').addClass('hidden');
                $(`.created-custom-${mode}`).removeClass('hidden');
            });

            $('.created-apply-days').on('click', function () {
                const d = Math.max(1, Math.min(parseInt($daysInput.val() || '0', 10), 365));
                if (!d) return;
                clearAllHidden();
                $hDays.val(d);
                setLabel(`Last ${d} days`);
                closeMenu();
                push({
                    days: d,
                    today: '',
                    date: '',
                    from: '',
                    to: ''
                });
            });

            $('.created-apply-date').on('click', function () {
                const v = $dateInput.val();
                if (!v) return;
                clearAllHidden();
                $hDate.val(v);
                setLabel(new Date(v).toLocaleDateString());
                closeMenu();
                push({
                    date: v,
                    today: '',
                    days: '',
                    from: '',
                    to: ''
                });
            });

            $('.created-apply-range').on('click', function () {
                const f = $fromInput.val();
                const t = $toInput.val();
                if (!f && !t) return;
                clearAllHidden();
                if (f) $hFrom.val(f);
                if (t) $hTo.val(t);
                setLabel(`${f || '…'} → ${t || '…'}`);
                closeMenu();
                push({
                    from: f || '',
                    to: t || '',
                    today: '',
                    days: '',
                    date: ''
                });
            });

            $('.created-clear').on('click', function () {
                $daysInput.val('');
                $dateInput.val('');
                $fromInput.val('');
                $toInput.val('');
            });
        });

        /* ---------- Page init ---------- */
        document.addEventListener('DOMContentLoaded', () => {
            // Modals
            qsa('[data-open-bulk-assign]').forEach(b => b.addEventListener('click', openBulkAssignModal));
            qs('[data-close-bulk-assign]')?.addEventListener('click', closeBulkAssignModal);
            qs('#cancelBulkAssign')?.addEventListener('click', closeBulkAssignModal);
            qs('#bulkAssignBackdrop')?.addEventListener('click', (e) => {
                if (e.target === qs('#bulkAssignBackdrop')) closeBulkAssignModal();
            });
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && !qs('#bulkAssignModal').classList.contains('hidden'))
                    closeBulkAssignModal();
            });

            // Single delete modal
            const deleteModal = document.getElementById('deleteModal');
            const deleteForm = document.getElementById('deleteForm');
            document.querySelectorAll('[data-open-delete]').forEach(btn => {
                btn.addEventListener('click', () => {
                    deleteForm.action = btn.dataset.url;
                    deleteModal.classList.remove('hidden');
                    document.body.classList.add('overflow-hidden');
                });
            });
            document.getElementById('cancelDelete').addEventListener('click', () => {
                deleteModal.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            });

            // Import modal
            document.querySelectorAll('[data-open-import]').forEach(btn => {
                btn.addEventListener('click', () => {
                    document.getElementById('importModal').classList.remove('hidden');
                    document.body.classList.add('overflow-hidden');
                });
            });
            document.getElementById('cancelImport')?.addEventListener('click', () => {
                document.getElementById('importModal').classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            });

            // Initialize AJAX filtering + selection handlers
            initAjaxFiltering();
            initLeadSelectionHandlers();
            applySelectionStateToDOM();
            updateHeaderSelectAllState();
            reflectSelectionUI();

            // Bulk delete modal open/close
            $('#openBulkDelete').on('click', openBulkDeleteModal);
            $('#cancelBulkDelete').on('click', closeBulkDeleteModal);
            $('#bulkDeleteModal').on('click', function (e) {
                if (e.target === this) closeBulkDeleteModal();
            });

            // Initial admin helpers
            updateAvailableCount();
            applySearchAndSort();
            recalcState();

            /* ---------- NEW: wire up Bulk Assign controls ---------- */
            // Tabs (Online / All Users)
            document.addEventListener('click', (e) => {
                const btn = e.target.closest('[data-user-tab]');
                if (!btn) return;
                const tab = btn.getAttribute('data-user-tab');
                setTab(tab);
                updateSelectAllState();
                recalcState();
            });

            // Search & Sort in the modal
            $('#userSearch').on('input', debounce(applySearchAndSort, 200));
            $('#sortUsers').on('change', applySearchAndSort);

            // Select all (filtered) for current tab
            $('#selectAllUsers').on('change', function () {
                toggleSelectAll(this.checked);
            });

            // Keep header state when individual boxes change
            $('#usersScrollArea').on('change', '.user-checkbox', function () {
                updateSelectAllState();
                recalcState();
            });

            // React to status / per-user count changes
            $('#assign_status').on('change', function () {
                updateAvailableCount();
                recalcState();
            });
            $('#leads_count').on('input', function () {
                recalcState();
            });
        });

        // Prevent bulk-assign submit if inline errors
        document.getElementById('bulkAssignForm')?.addEventListener('submit', function (e) {
            if (!qs('#inlineErrors').classList.contains('hidden')) {
                e.preventDefault();
                qs('#inlineErrors').scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });
            }
        });
    </script>
@endpush