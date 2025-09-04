@php
    // User role and permissions
    $currentUser = auth()->user();

    // Treat lead_manager like admin (admin-equivalent)
    $isLeadManager = method_exists($currentUser, 'hasRole')
        ? $currentUser->hasRole('lead_manager')
        : ($currentUser->role ?? null) === 'lead_manager';

    $isAdmin = $currentUser->isAdmin() || $isLeadManager;

    $isCloser = $currentUser->isCloser();
    $isSuperAgent = $currentUser->isSuperAgent();

    // Data for admin functionality
    $users = $isAdmin ? \App\Models\User::all() : collect();

    // Online users (ensure a Collection to safely call ->count())
    $onlineUsers = $isAdmin ? (isset($onlineUsers) ? collect($onlineUsers) : collect()) : collect();

    // UI configuration
    $cardClass = 'bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700';
    $tableHeaderClass =
        'px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider';
    $btnBaseClass = 'inline-flex items-center gap-2 px-4 py-2.5 rounded-lg transition-all duration-200 font-medium';
    $btnGhostClass =
        $btnBaseClass .
        ' border border-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600';
    $btnPrimaryClass = $btnBaseClass . ' bg-blue-600 text-white hover:bg-blue-700 shadow-sm';

    // Filter state
    $hasActiveFilters = request()->hasAny(['q', 'status', 'category']);

    // Current workload to show beside each user
    $assignedCounts = \App\Models\Lead::selectRaw('assigned_to, COUNT(*) as c')
        ->groupBy('assigned_to')
        ->pluck('c', 'assigned_to');
@endphp

@extends('layouts.app')

@section('title', 'Leads Management')
@section('page-title', 'Leads')

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
                    <form id="bulkAssignForm" method="POST" action="{{ route('leads.bulk-assign') }}"
                        class="overflow-y-auto">
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
                                        <input id="leads_count" name="leads_count" type="number" min="1"
                                            value="10" required
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
                                                data-email="{{ Str::lower($onlineUser->email ?? '') }}"
                                                data-load="{{ $count }}"
                                                data-eligible="{{ $isEligible ? '1' : '0' }}">
                                                <div class="flex items-center gap-3 min-w-0">
                                                    <input type="checkbox" name="assignee_ids[]"
                                                        value="{{ $onlineUser->id }}"
                                                        class="user-checkbox rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                                        {{ $isEligible ? '' : 'disabled' }}>
                                                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                                                    <div class="min-w-0">
                                                        <label
                                                            class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate cursor-pointer">{{ $onlineUser->name }}</label>
                                                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                                                            {{ $onlineUser->email ?? '—' }}</p>
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
                                                data-email="{{ Str::lower($user->email ?? '') }}"
                                                data-load="{{ $count }}"
                                                data-eligible="{{ $isEligible ? '1' : '0' }}">
                                                <div class="flex items-center gap-3 min-w-0">
                                                    <input type="checkbox" name="assignee_ids[]"
                                                        value="{{ $user->id }}"
                                                        class="user-checkbox rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                                        {{ $isEligible ? '' : 'disabled' }}>
                                                    <span
                                                        class="w-2.5 h-2.5 rounded-full {{ $isOnline ? 'bg-emerald-500' : 'bg-gray-400 dark:bg-gray-600' }}"></span>
                                                    <div class="min-w-0">
                                                        <label
                                                            class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate cursor-pointer">{{ $user->name }}</label>
                                                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                                                            {{ $user->email ?? '—' }}</p>
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
    @endif
@endpush

@section('content')
    <div class="space-y-6" x-data="{ filterOpen: {{ $hasActiveFilters ? 'true' : 'false' }}, exportOpen: false }">
        @if (session('error'))
            <div class="p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-red-600 dark:text-red-400 mr-3" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    <span class="text-red-800 dark:text-red-200 text-sm">{{ session('error') }}</span>
                </div>
            </div>
        @endif

        <!-- Header -->
        <div class="{{ $cardClass }} p-6">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div class="flex-1">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-1">
                        {{ $isAdmin ? 'All Leads' : 'My Leads' }}</h1>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">
                        {{ $isAdmin ? 'Manage and track all leads across the team' : 'Manage the leads assigned to you' }}
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    @if ($isAdmin)
                        <button type="button" class="{{ $btnGhostClass }} text-sm" data-open-bulk-assign>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <span>Bulk Assign</span>
                        </button>
                    @endif

                    <button type="button" class="{{ $btnGhostClass }} text-sm" @click="filterOpen = !filterOpen">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 4h18M6 8h12M9 12h6M10 16h4M11 20h2" />
                        </svg>
                        <span x-text="filterOpen ? 'Hide Filters' : 'Show Filters'"></span>
                    </button>

                    <a href="{{ url()->current() . (request()->query() ? '?' . http_build_query(request()->query()) : '') }}"
                        class="{{ $btnGhostClass }} text-sm js-refresh" title="Refresh leads list">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 4v6h6M20 20v-6h-6M20 8a8 8 0 10-7.5 12" />
                        </svg>
                        <span>Refresh</span>
                    </a>

                    @if ($isAdmin)
                        <button data-open-import class="{{ $btnPrimaryClass }} text-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 10v6m0 0l-3-3m3 3l3-3M5 20h14a2 2 0 002-2V7l-6-5H7L1 7v11a2 2 0 002 2z" />
                            </svg>
                            <span>Import CSV</span>
                        </button>

                        <a href="{{ route('leads.create') }}" class="{{ $btnPrimaryClass }} text-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 6v12m6-6H6" />
                            </svg>
                            <span>Add Lead</span>
                        </a>
                    @endif
                </div>
            </div>

            <!-- Filters -->
            <div x-cloak x-show="filterOpen" x-transition class="mt-6 pt-6 border-t border-gray-100 dark:border-gray-700">
                <form id="leadsFilterForm" method="GET"
                    action="{{ $isAdmin ? route('leads.index') : route('leads.mine') }}"
                    class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="md:col-span-2">
                        <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">Search Leads</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <input id="qInput" type="text" name="q" value="{{ $filters['q'] ?? '' }}"
                                placeholder="Search by name, gen code, city, or phone..."
                                class="pl-10 w-full px-3 py-2 border border-gray-200 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>

                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                        <select id="statusSelect" name="status"
                            class="w-full px-3 py-2 border border-gray-200 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">All Statuses</option>
                            @foreach ($statuses as $status)
                                <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ $status }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @if ($isAdmin)
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">Category</label>
                            <select id="categorySelect" name="category"
                                class="w-full px-3 py-2 border border-gray-200 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">All Categories</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" @selected(($filters['category'] ?? '') == $category->id)>
                                        {{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div class="md:col-span-3 flex items-center justify-between pt-4">
                        <div id="resultsMeta" class="text-sm text-gray-600 dark:text-gray-400 font-medium">
                            Showing {{ $leads->firstItem() ?? 0 }}–{{ $leads->lastItem() ?? 0 }} of {{ $leads->total() }}
                            results
                        </div>
                        <div class="flex gap-2">
                            <button type="submit"
                                class="px-3 py-2 rounded-lg text-white font-medium bg-blue-600 hover:bg-blue-700 transition-colors text-sm">Apply
                                Filters</button>
                            <a id="clearFilters" href="{{ $isAdmin ? route('leads.index') : route('leads.mine') }}"
                                class="px-3 py-2 rounded-lg font-medium bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-500 transition-colors text-sm">Clear
                                All</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Leads Table -->
        <div class="{{ $cardClass }} overflow-hidden" id="leadsTableCard">
            <div class="relative">
                <div id="ajaxOverlay"
                    class="hidden absolute inset-0 bg-white/50 dark:bg-gray-900/50 backdrop-blur-sm z-10 flex items-center justify-center">
                    <div class="animate-spin h-6 w-6 border-2 border-gray-400 border-t-transparent rounded-full"></div>
                </div>

                <div id="leadsTableContainer">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="{{ $tableHeaderClass }}">Lead Details</th>
                                    <th class="{{ $tableHeaderClass }}">Contact Information</th>
                                    @if ($isAdmin)
                                        <th class="{{ $tableHeaderClass }}">Assigned To</th>
                                    @endif
                                    <th class="{{ $tableHeaderClass }}">Status</th>
                                    <th class="{{ $tableHeaderClass }}">Created Date</th>
                                    <th class="{{ $tableHeaderClass }}">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
                                @forelse($leads as $lead)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-150">
                                        <!-- Lead Details -->
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div
                                                    class="h-10 w-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg flex items-center justify-center shadow-sm flex-shrink-0">
                                                    <span
                                                        class="text-sm font-bold text-white">{{ strtoupper(substr($lead->surname ?: $lead->first_name ?: 'L', 0, 1)) }}</span>
                                                </div>
                                                <div class="ml-3 min-w-0">
                                                    <a href="{{ route('leads.show', $lead) }}"
                                                        class="text-sm font-medium text-gray-900 dark:text-white hover:text-blue-600 dark:hover:text-blue-400 truncate block">
                                                        {{ trim($lead->first_name . ' ' . $lead->surname) ?: '—' }}
                                                    </a>
                                                    <div class="text-xs text-gray-500 dark:text-gray-400 truncate">
                                                        {{ $lead->gen_code ?: '—' }}</div>
                                                </div>
                                            </div>
                                        </td>

                                        <!-- Contact -->
                                        <td class="px-6 py-4">
                                            @php
                                                $nums = [];
                                                if (is_array($lead->numbers)) {
                                                    $nums = $lead->numbers;
                                                } elseif (is_string($lead->numbers)) {
                                                    $decoded = json_decode($lead->numbers, true);
                                                    $nums = is_array($decoded) ? $decoded : [];
                                                }
                                            @endphp
                                            @if (!empty($nums))
                                                <div class="space-y-1">
                                                    @foreach (array_slice($nums, 0, 2) as $n)
                                                        <div class="text-sm text-gray-900 dark:text-white">
                                                            {{ $n }}</div>
                                                    @endforeach
                                                    @if (count($nums) > 2)
                                                        <div class="text-xs text-gray-500 dark:text-gray-400">
                                                            +{{ count($nums) - 2 }} more numbers</div>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="text-sm text-gray-500 dark:text-gray-400">No contact
                                                    numbers</span>
                                            @endif
                                        </td>

                                        @if ($isAdmin)
                                            <td class="px-6 py-4"><span
                                                    class="text-sm text-gray-900 dark:text-white">{{ $lead->assignee->name ?? 'Unassigned' }}</span>
                                            </td>
                                        @endif

                                        <!-- Status -->
                                        <td class="px-6 py-4">
                                            @php
                                                // lower-case keys to match strtolower() below
                                                $statusColors = [
                                                    'deal' =>
                                                        'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
                                                    'call back' =>
                                                        'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300',
                                                    'super lead' =>
                                                        'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300',
                                                    'new lead' =>
                                                        'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
                                                    'submitted' =>
                                                        'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-300',
                                                    'default' =>
                                                        'bg-gray-100 text-gray-800 dark:bg-gray-700/60 dark:text-gray-300',
                                                ];
                                                $statusKey = strtolower(trim($lead->status ?? ''));
                                                $statusClass = $statusColors[$statusKey] ?? $statusColors['default'];
                                            @endphp
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusClass }}">{{ ucfirst($lead->status) }}</span>
                                        </td>

                                        <!-- Created -->
                                        <td class="px-6 py-4"><span
                                                class="text-sm text-gray-900 dark:text-white">{{ $lead->created_at?->format('M d, Y') }}</span>
                                        </td>

                                        <!-- Actions -->
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center gap-1">
                                                <a href="{{ route('leads.show', $lead) }}"
                                                    class="p-1.5 text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors"
                                                    title="View Lead">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                    </svg>
                                                </a>
                                                @if ($isAdmin)
                                                    <a href="{{ route('leads.pdf', $lead) }}"
                                                        class="p-1.5 text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors"
                                                        title="Download Lead">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5m0 0l5-5m-5 5V4" />
                                                        </svg>
                                                    </a>
                                                @endif
                                                <a href="{{ route('leads.edit', $lead) }}"
                                                    class="p-1.5 text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors"
                                                    title="Edit Lead">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                    </svg>
                                                </a>
                                                @can('delete', $lead)
                                                    <button type="button"
                                                        class="p-1.5 text-gray-400 hover:text-red-600 dark:hover:text-red-400 transition-colors"
                                                        data-open-delete data-type="lead"
                                                        data-url="{{ route('leads.destroy', $lead) }}" title="Delete Lead">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862A2 2 0 015.867 19L5 7m5 4v6m4-6v6M9 7h6m-7 0l1-2h6l1 2" />
                                                        </svg>
                                                    </button>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ $isAdmin ? 6 : 5 }}" class="px-6 py-12 text-center">
                                            <div
                                                class="flex flex-col items-center justify-center text-gray-400 dark:text-gray-500">
                                                <svg class="w-12 h-12 mb-3 opacity-50" fill="none"
                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                <p class="text-sm font-medium">No leads found</p>
                                                <p class="text-xs mt-1">Try adjusting your search filters</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if ($leads->hasPages())
                        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50"
                            id="paginationWrap">
                            {{ $leads->appends(request()->query())->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if ($isAdmin)
        <!-- Import Modal -->
        <div id="importModal" class="fixed inset-0 z-50 hidden">
            <div class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>
            <div class="relative z-50 mx-auto w-full max-w-md p-8 mt-24">
                <div
                    class="rounded-xl bg-white dark:bg-gray-800 shadow-xl border border-gray-200 dark:border-gray-700 p-6">
                    <div class="text-center">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Import Leads</h2>
                        <form id="importForm" action="{{ route('leads.import') }}" method="POST"
                            enctype="multipart/form-data" class="space-y-4">
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

        /* ---------- Wire up ---------- */
        document.addEventListener('DOMContentLoaded', () => {
            // Modal open/close
            qsa('[data-open-bulk-assign]').forEach(b => b.addEventListener('click', openBulkAssignModal));
            qs('[data-close-bulk-assign]')?.addEventListener('click', closeBulkAssignModal);
            qs('#cancelBulkAssign')?.addEventListener('click', closeBulkAssignModal);
            qs('#bulkAssignBackdrop')?.addEventListener('click', (e) => {
                if (e.target === qs('#bulkAssignBackdrop')) closeBulkAssignModal();
            });

            // Tabs
            qsa('[data-user-tab]').forEach(btn =>
                btn.addEventListener('click', () => setTab(btn.getAttribute('data-user-tab')))
            );
            setTab('online');

            // Search/sort/filter
            qs('#userSearch')?.addEventListener('input', debounce(applySearchAndSort, 200));
            qs('#sortUsers')?.addEventListener('change', applySearchAndSort);
            qs('#busy_threshold')?.addEventListener('input', debounce(applySearchAndSort, 200));

            // Select all
            const all = qs('#selectAllUsers');
            all?.addEventListener('change', function() {
                toggleSelectAll(this.checked);
                updateSelectAllState();
            });
            ['usersOnlineWrap', 'usersAllWrap'].forEach(id => {
                qs('#' + id)?.addEventListener('change', e => {
                    if (e.target.classList.contains('user-checkbox')) {
                        updateSelectAllState();
                        recalcState();
                    }
                });
            });

            // Status / counts
            qs('#leads_count')?.addEventListener('input', () => {
                updateAvailableCount();
                recalcState();
            });
            qs('#assign_status')?.addEventListener('change', () => {
                updateAvailableCount();
                recalcState();
            });

            // ESC close
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && !qs('#bulkAssignModal').classList.contains('hidden'))
                    closeBulkAssignModal();
            });

            // Delete modal
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

            // Leads list AJAX
            initAjaxFiltering();

            // Initial state
            updateAvailableCount();
            applySearchAndSort();
            recalcState();
        });

        function debounce(fn, delay) {
            let t;
            return (...a) => {
                clearTimeout(t);
                t = setTimeout(() => fn(...a), delay);
            };
        }

        /* ---------- Leads list AJAX ---------- */
        function initAjaxFiltering() {
            const $form = $('#leadsFilterForm');
            const $q = $('#qInput');
            const $status = $('#statusSelect');
            const $category = $('#categorySelect');
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

            function ajaxLoad(url) {
                showOverlay();
                $.get(url)
                    .done(function(html) {
                        const $html = $(html);
                        $container.html($html.find('#leadsTableContainer').html());
                        $resultsMeta.html($html.find('#resultsMeta').html());
                        window.history.pushState({}, '', url);
                    })
                    .fail(function() {
                        window.location.href = url;
                    })
                    .always(hideOverlay);
            }

            $form.on('submit', function(e) {
                e.preventDefault();
                const s = serializeFormToQuery($form);
                ajaxLoad($form.attr('action') + (s ? '?' + s : ''));
            });
            $('#clearFilters').on('click', function(e) {
                e.preventDefault();
                ajaxLoad($(this).attr('href'));
            });
            $('.js-refresh').on('click', function(e) {
                e.preventDefault();
                ajaxLoad($(this).attr('href'));
            });
            $q.on('keyup', debounce(function() {
                const s = serializeFormToQuery($form, {
                    q: $q.val()
                });
                ajaxLoad($form.attr('action') + (s ? '?' + s : ''));
            }, 400));
            $status.on('change', function() {
                const s = serializeFormToQuery($form, {
                    status: $status.val(),
                    page: 1
                });
                ajaxLoad($form.attr('action') + (s ? '?' + s : ''));
            });
            if ($category.length) {
                $category.on('change', function() {
                    const s = serializeFormToQuery($form, {
                        category: $category.val(),
                        page: 1
                    });
                    ajaxLoad($form.attr('action') + (s ? '?' + s : ''));
                });
            }
            $('#leadsTableCard').on('click', '#paginationWrap a', function(e) {
                e.preventDefault();
                ajaxLoad($(this).attr('href'));
            });
            window.addEventListener('popstate', function() {
                ajaxLoad(location.href);
            });
        }

        // Prevent submit if errors
        document.getElementById('bulkAssignForm')?.addEventListener('submit', function(e) {
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
