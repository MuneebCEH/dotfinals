@php
    // User role and permissions
    $currentUser = auth()->user();

    // User role checks
    $isLeadManager = method_exists($currentUser, 'hasRole')
        ? $currentUser->hasRole('lead_manager')
        : ($currentUser->role ?? null) === 'lead_manager';

    $isAdmin = $currentUser->isAdmin() || $isLeadManager;
    $isMaxOut = true; // Always true for this view
    $isCloser = $currentUser->isCloser();
    $isSuperAgent = $currentUser->isSuperAgent();

    // ✅ Query: All Max Out leads (no assignment restriction)
    $query = \App\Models\Lead::query()->where('status', 'Max Out');

    // Data for functionality
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

    // Filter state (now includes "today")
    $hasActiveFilters = request()->hasAny(['q', 'category', 'today', 'days', 'date', 'from', 'to']);

    // Current workload to show beside each user
    $assignedCounts = \App\Models\Lead::where('status', 'Max Out')
        ->selectRaw('assigned_to, COUNT(*) as c')
        ->groupBy('assigned_to')
        ->pluck('c', 'assigned_to');

    // ✅ Paginate all Max Out leads
    $leads = $query->latest()->paginate(25)->withQueryString();
@endphp

@extends('layouts.app')

@section('title', 'Max Out Leads Management')
@section('page-title', 'Max Out Leads')

@section('description')
    Manage and process your assigned Max Out leads
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
                            You're about to delete <span id="bulkDeleteCount" class="font-semibold">0</span> lead(s).
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
                        Max Out Leads
                    </h1>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">
                        Manage and track all Max Out leads across the team
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2">

                    @if ($isAdmin)
                        <button type="button" id="openBulkDelete"
                            class="{{ $btnGhostClass }} text-sm text-red-600 hover:text-red-700 disabled:opacity-40 disabled:cursor-not-allowed"
                            disabled>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862A2 2 0 015.867 19L5 7m5 4v6m4-6v6M9 7h6m-7 0l1-2h6l1 2" />
                            </svg>
                            <span>Delete Selected</span>
                            <span id="bulkDeleteChip"
                                class="ml-2 hidden align-middle px-2 py-0.5 rounded-md bg-red-500/10 border border-red-400/40 text-xs text-red-700">
                                0
                            </span>
                        </button>
                    @endif

                    {{-- <button type="button" class="{{ $btnGhostClass }} text-sm" @click="filterOpen = !filterOpen">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 4h18M6 8h12M9 12h6M10 16h4M11 20h2" />
                        </svg>
                        <span x-text="filterOpen ? 'Hide Filters' : 'Show Filters'"></span>
                    </button> --}}

                    <a href="{{ url()->current() . (request()->query() ? '?' . http_build_query(request()->query()) : '') }}"
                        class="{{ $btnGhostClass }} text-sm js-refresh" title="Refresh leads list">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 4v6h6M20 20v-6h-6M20 8a8 8 0 10-7.5 12" />
                        </svg>
                        <span>Refresh</span>
                    </a>

                    @if ($isAdmin)
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
            {{-- <div x-cloak x-show="filterOpen" x-transition class="mt-6 pt-6 border-t border-gray-100 dark:border-gray-700">
                <form id="leadsFilterForm" method="GET" action="{{ route('leads.maxout') }}"
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
                            <input id="qInput" type="text" name="q" value="{{ request('q') ?? '' }}"
                                placeholder="Search by name, gen code, city, or phone..."
                                class="pl-10 w-full px-3 py-2 border border-gray-200 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>

                    @if ($isAdmin && class_exists('\App\Models\Category'))
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">Category</label>
                            <select id="categorySelect" name="category"
                                class="w-full px-3 py-2 border border-gray-200 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">All Categories</option>
                                @foreach (\App\Models\Category::all() as $category)
                                    <option value="{{ $category->id }}" @selected(request('category') == $category->id)>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <!-- Created (single compact control) -->
                    <div class="relative" id="createdFilter">
                        <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">Created</label>

                        <!-- The one visible control -->
                        <button type="button" id="createdFilterBtn"
                            class="w-full justify-between inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white hover:bg-gray-50 dark:hover:bg-gray-600 focus:ring-2 focus:ring-blue-500 text-sm">
                            <span id="createdFilterLabel">
                                {{ request()->boolean('today')
                                    ? 'Today'
                                    : (request()->filled('days')
                                        ? 'Last ' . (int) request('days') . ' days'
                                        : (request()->filled('date')
                                            ? \Illuminate\Support\Carbon::parse(request('date'))->format('M d, Y')
                                            : (request()->filled('from') || request()->filled('to')
                                                ? (request('from') ?: '…') . ' → ' . (request('to') ?: '…')
                                                : 'Any time'))) }}
                            </span>
                            <svg class="w-4 h-4 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <!-- Dropdown -->
                        <div id="createdMenu"
                            class="hidden absolute z-30 mt-2 w-80 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-xl">
                            <div class="p-3 space-y-3">
                                <!-- Quick presets -->
                                <div class="grid grid-cols-2 gap-2">
                                    <button type="button"
                                        class="created-option px-3 py-2 rounded-lg border text-sm hover:bg-gray-50 dark:hover:bg-gray-700"
                                        data-kind="today">Today</button>
                                    <button type="button"
                                        class="created-option px-3 py-2 rounded-lg border text-sm hover:bg-gray-50 dark:hover:bg-gray-700"
                                        data-kind="days" data-days="7">Last 7 days</button>
                                    <button type="button"
                                        class="created-option px-3 py-2 rounded-lg border text-sm hover:bg-gray-50 dark:hover:bg-gray-700"
                                        data-kind="days" data-days="14">Last 14 days</button>
                                    <button type="button"
                                        class="created-option px-3 py-2 rounded-lg border text-sm hover:bg-gray-50 dark:hover:bg-gray-700"
                                        data-kind="days" data-days="30">Last 30 days</button>
                                </div>

                                <div class="h-px bg-gray-200 dark:bg-gray-700"></div>

                                <!-- Custom area (still inside the same dropdown) -->
                                <div class="space-y-2">
                                    <button type="button"
                                        class="created-open-custom w-full flex items-center justify-between px-3 py-2 rounded-lg border text-sm hover:bg-gray-50 dark:hover:bg-gray-700"
                                        data-mode="days">
                                        <span>Last N days…</span>
                                        <svg class="w-4 h-4 opacity-70" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 5l7 7-7 7" />
                                        </svg>
                                    </button>
                                    <div class="created-custom created-custom-days hidden">
                                        <input id="createdDays" type="number" min="1" max="365"
                                            placeholder="e.g. 10"
                                            class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
                                        <div class="mt-2 flex justify-end gap-2">
                                            <button type="button"
                                                class="created-clear text-sm px-3 py-1.5 rounded-md border">Clear</button>
                                            <button type="button"
                                                class="created-apply created-apply-days text-sm px-3 py-1.5 rounded-md bg-blue-600 text-white">Apply</button>
                                        </div>
                                    </div>

                                    <button type="button"
                                        class="created-open-custom w-full flex items-center justify-between px-3 py-2 rounded-lg border text-sm hover:bg-gray-50 dark:hover:bg-gray-700"
                                        data-mode="date">
                                        <span>Specific day…</span>
                                        <svg class="w-4 h-4 opacity-70" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 5l7 7-7 7" />
                                        </svg>
                                    </button>
                                    <div class="created-custom created-custom-date hidden">
                                        <input id="createdDate" type="date"
                                            class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
                                        <div class="mt-2 flex justify-end gap-2">
                                            <button type="button"
                                                class="created-clear text-sm px-3 py-1.5 rounded-md border">Clear</button>
                                            <button type="button"
                                                class="created-apply created-apply-date text-sm px-3 py-1.5 rounded-md bg-blue-600 text-white">Apply</button>
                                        </div>
                                    </div>

                                    <button type="button"
                                        class="created-open-custom w-full flex items-center justify-between px-3 py-2 rounded-lg border text-sm hover:bg-gray-50 dark:hover:bg-gray-700"
                                        data-mode="range">
                                        <span>Date range…</span>
                                        <svg class="w-4 h-4 opacity-70" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 5l7 7-7 7" />
                                        </svg>
                                    </button>
                                    <div class="created-custom created-custom-range hidden">
                                        <div class="flex gap-2">
                                            <input id="createdFrom" type="date"
                                                class="flex-1 px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
                                            <input id="createdTo" type="date"
                                                class="flex-1 px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
                                        </div>
                                        <div class="mt-2 flex justify-end gap-2">
                                            <button type="button"
                                                class="created-clear text-sm px-3 py-1.5 rounded-md border">Clear</button>
                                            <button type="button"
                                                class="created-apply created-apply-range text-sm px-3 py-1.5 rounded-md bg-blue-600 text-white">Apply</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Hidden params mirrored for backend (UI still just 1 field) -->
                        <input type="hidden" name="today" id="createdToday"
                            value="{{ request()->boolean('today') ? '1' : '' }}">
                        <input type="hidden" name="days" id="createdDaysHidden" value="{{ request('days') }}">
                        <input type="hidden" name="date" id="createdDateHidden" value="{{ request('date') }}">
                        <input type="hidden" name="from" id="createdFromHidden" value="{{ request('from') }}">
                        <input type="hidden" name="to" id="createdToHidden" value="{{ request('to') }}">
                    </div>

                    <div class="md:col-span-3 flex items-center justify-between pt-4">
                        <div id="resultsMeta" class="text-sm text-gray-600 dark:text-gray-400 font-medium">
                            Showing {{ $leads->firstItem() ?? 0 }}–{{ $leads->lastItem() ?? 0 }} of {{ $leads->total() }}
                            results
                            @if (request()->boolean('today'))
                                <span
                                    class="ml-2 inline-flex items-center rounded-md bg-blue-100 px-2 py-0.5 text-xs font-medium text-blue-800 dark:bg-blue-900/30 dark:text-blue-200">
                                    Today
                                </span>
                            @endif
                        </div>
                        <div class="flex gap-2">
                            <button type="submit"
                                class="px-3 py-2 rounded-lg text-white font-medium bg-blue-600 hover:bg-blue-700 transition-colors text-sm">Apply
                                Filters</button>
                            <a id="clearFilters" href="{{ route('leads.maxout') }}"
                                class="px-3 py-2 rounded-lg font-medium bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-500 transition-colors text-sm">Clear
                                All</a>
                        </div>
                    </div>
                </form>
            </div> --}}
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
                                    @if ($isAdmin)
                                        <th class="{{ $tableHeaderClass }} w-12">
                                            <input id="selectAllLeads" type="checkbox"
                                                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                        </th>
                                    @endif
                                    <th class="{{ $tableHeaderClass }}">Lead Details</th>
                                    <th class="{{ $tableHeaderClass }}">Contact Information</th>
                                    @if ($isAdmin)
                                        <th class="{{ $tableHeaderClass }}">Assigned To</th>
                                    @endif
                                    <th class="{{ $tableHeaderClass }}">Financial Info</th>
                                    <th class="{{ $tableHeaderClass }}">Status & Category</th>
                                    <th class="{{ $tableHeaderClass }}">Created Date</th>
                                    <th class="{{ $tableHeaderClass }}">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
                                @forelse($leads as $lead)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-150">
                                        @if ($isAdmin)
                                            <td class="px-6 py-4">
                                                @can('delete', $lead)
                                                    <input type="checkbox"
                                                        class="lead-select rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                                        data-id="{{ $lead->id }}">
                                                @else
                                                    <input type="checkbox" class="rounded border-gray-300" disabled>
                                                @endcan
                                            </td>
                                        @endif
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
                                                        ID: {{ $lead->id }}
                                                    </div>
                                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                                        Added {{ $lead->created_at->diffForHumans() }}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>

                                        <!-- Contact Information -->
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
                                                            {{ $n }}
                                                        </div>
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

                                            @if ($lead->city || $lead->state_abbreviation)
                                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                                    {{ $lead->city }}{{ $lead->city && $lead->state_abbreviation ? ', ' : '' }}{{ $lead->state_abbreviation }}
                                                </div>
                                            @endif
                                        </td>

                                        @if ($isAdmin)
                                            <td class="px-6 py-4"><span
                                                    class="text-sm text-gray-900 dark:text-white">{{ $lead->assignee->name ?? 'Unassigned' }}</span>
                                            </td>
                                        @endif

                                        <!-- Financial Info -->
                                        <td class="px-6 py-4">
                                            <div class="flex flex-col">
                                                @if ($lead->fico)
                                                    <span class="text-sm font-medium text-gray-900 dark:text-white">
                                                        FICO: {{ $lead->fico }}
                                                    </span>
                                                @endif
                                                @if ($lead->balance)
                                                    <span class="text-sm text-gray-600 dark:text-gray-300">
                                                        Balance: ${{ number_format($lead->balance) }}
                                                    </span>
                                                @endif
                                            </div>
                                        </td>

                                        <!-- Status & Category -->
                                        <td class="px-6 py-4">
                                            <div class="flex flex-col space-y-2 items-center">
                                                @php
                                                    $statusColors = [
                                                        'max out' =>
                                                            'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300',
                                                        'default' =>
                                                            'bg-gray-100 text-gray-800 dark:bg-gray-700/60 dark:text-gray-300',
                                                    ];
                                                    $statusKey = strtolower(trim($lead->status ?? ''));
                                                    $statusClass =
                                                        $statusColors[$statusKey] ?? $statusColors['default'];
                                                @endphp
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusClass }}">{{ ucfirst($lead->status) }}</span>

                                                @if ($lead->category)
                                                    <span class="text-sm text-gray-500 dark:text-gray-400">
                                                        {{ $lead->category->name }}
                                                    </span>
                                                @endif
                                            </div>
                                        </td>

                                        <!-- Created Date -->
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
                                        <td colspan="{{ $isAdmin ? 7 : 6 }}" class="px-6 py-12 text-center">
                                            <div
                                                class="flex flex-col items-center justify-center text-gray-400 dark:text-gray-500">
                                                <svg class="w-12 h-12 mb-3 opacity-40" fill="none"
                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                <p class="text-base font-medium text-gray-500 dark:text-gray-400 mb-1">No
                                                    Max
                                                    Out leads found</p>
                                                <p class="text-sm text-gray-400 dark:text-gray-500">Get started by creating
                                                    a
                                                    new lead or adjusting your filters.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if ($leads->hasPages())
                        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                            {{ $leads->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Delete modal logic
            const deleteModal = document.getElementById('deleteModal');
            const cancelDelete = document.getElementById('cancelDelete');
            const deleteForm = document.getElementById('deleteForm');
            const openDeleteButtons = document.querySelectorAll('[data-open-delete]');

            openDeleteButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const url = this.dataset.url;
                    const type = this.dataset.type || 'lead';
                    deleteForm.action = url;
                    deleteModal.classList.remove('hidden');
                });
            });

            cancelDelete?.addEventListener('click', () => {
                deleteModal.classList.add('hidden');
            });

            // Bulk delete logic
            const bulkDeleteModal = document.getElementById('bulkDeleteModal');
            const cancelBulkDelete = document.getElementById('cancelBulkDelete');
            const bulkDeleteForm = document.getElementById('bulkDeleteForm');
            const bulkDeleteIds = document.getElementById('bulkDeleteIds');
            const openBulkDelete = document.getElementById('openBulkDelete');
            const bulkDeleteChip = document.getElementById('bulkDeleteChip');
            const bulkDeleteCount = document.getElementById('bulkDeleteCount');
            const selectAllLeads = document.getElementById('selectAllLeads');
            const leadCheckboxes = document.querySelectorAll('.lead-select');

            function updateBulkDeleteUI() {
                const selectedLeads = Array.from(leadCheckboxes).filter(cb => cb.checked);
                const count = selectedLeads.length;

                if (openBulkDelete) {
                    openBulkDelete.disabled = count === 0;
                }

                if (bulkDeleteChip) {
                    if (count > 0) {
                        bulkDeleteChip.textContent = count;
                        bulkDeleteChip.classList.remove('hidden');
                    } else {
                        bulkDeleteChip.classList.add('hidden');
                    }
                }
            }

            if (selectAllLeads) {
                selectAllLeads.addEventListener('change', function() {
                    leadCheckboxes.forEach(cb => {
                        if (!cb.disabled) {
                            cb.checked = this.checked;
                        }
                    });
                    updateBulkDeleteUI();
                });
            }

            leadCheckboxes.forEach(cb => {
                cb.addEventListener('change', updateBulkDeleteUI);
            });

            if (openBulkDelete) {
                openBulkDelete.addEventListener('click', function() {
                    if (this.disabled) return;

                    const selectedLeads = Array.from(leadCheckboxes).filter(cb => cb.checked);
                    const ids = selectedLeads.map(cb => cb.dataset.id);

                    // Clear previous hidden inputs
                    bulkDeleteIds.innerHTML = '';

                    // Add new hidden inputs
                    ids.forEach(id => {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'ids[]';
                        input.value = id;
                        bulkDeleteIds.appendChild(input);
                    });

                    // Update count in modal
                    bulkDeleteCount.textContent = ids.length;

                    // Show modal
                    bulkDeleteModal.classList.remove('hidden');
                });
            }

            if (cancelBulkDelete) {
                cancelBulkDelete.addEventListener('click', () => {
                    bulkDeleteModal.classList.add('hidden');
                });
            }

            // Created filter logic
            const createdFilterBtn = document.getElementById('createdFilterBtn');
            const createdMenu = document.getElementById('createdMenu');
            const createdFilterLabel = document.getElementById('createdFilterLabel');

            // Hidden inputs
            const createdToday = document.getElementById('createdToday');
            const createdDaysHidden = document.getElementById('createdDaysHidden');
            const createdDateHidden = document.getElementById('createdDateHidden');
            const createdFromHidden = document.getElementById('createdFromHidden');
            const createdToHidden = document.getElementById('createdToHidden');

            // Custom inputs
            const createdDays = document.getElementById('createdDays');
            const createdDate = document.getElementById('createdDate');
            const createdFrom = document.getElementById('createdFrom');
            const createdTo = document.getElementById('createdTo');

            // Open/close menu
            createdFilterBtn?.addEventListener('click', (e) => {
                e.stopPropagation();
                createdMenu.classList.toggle('hidden');
            });

            // Close menu when clicking outside
            document.addEventListener('click', (e) => {
                if (!createdFilterBtn?.contains(e.target) && !createdMenu?.contains(e.target)) {
                    createdMenu?.classList.add('hidden');
                }
            });

            // Quick preset buttons
            document.querySelectorAll('.created-option').forEach(btn => {
                btn.addEventListener('click', () => {
                    const kind = btn.dataset.kind;

                    // Clear all hidden fields first
                    createdToday.value = '';
                    createdDaysHidden.value = '';
                    createdDateHidden.value = '';
                    createdFromHidden.value = '';
                    createdToHidden.value = '';

                    if (kind === 'today') {
                        createdToday.value = '1';
                        createdFilterLabel.textContent = 'Today';
                    } else if (kind === 'days') {
                        const days = btn.dataset.days;
                        createdDaysHidden.value = days;
                        createdFilterLabel.textContent = `Last ${days} days`;
                    }

                    // Close menu
                    createdMenu.classList.add('hidden');
                });
            });

            // Open custom input sections
            document.querySelectorAll('.created-open-custom').forEach(btn => {
                btn.addEventListener('click', () => {
                    const mode = btn.dataset.mode;

                    // Hide all custom sections first
                    document.querySelectorAll('.created-custom').forEach(s => s.classList.add(
                        'hidden'));

                    // Show the selected one
                    document.querySelector(`.created-custom-${mode}`).classList.remove('hidden');
                });
            });

            // Apply custom filters
            document.querySelectorAll('.created-apply').forEach(btn => {
                btn.addEventListener('click', () => {
                    if (btn.classList.contains('created-apply-days')) {
                        const days = createdDays.value.trim();
                        if (days && !isNaN(days) && days > 0) {
                            createdDaysHidden.value = days;
                            createdFilterLabel.textContent = `Last ${days} days`;
                        }
                    } else if (btn.classList.contains('created-apply-date')) {
                        const date = createdDate.value;
                        if (date) {
                            createdDateHidden.value = date;
                            createdFilterLabel.textContent = new Date(date).toLocaleDateString(
                                'en-US', {
                                    month: 'short',
                                    day: 'numeric',
                                    year: 'numeric'
                                });
                        }
                    } else if (btn.classList.contains('created-apply-range')) {
                        const from = createdFrom.value;
                        const to = createdTo.value;
                        if (from || to) {
                            createdFromHidden.value = from;
                            createdToHidden.value = to;
                            createdFilterLabel.textContent = `${from || '…'} → ${to || '…'}`;
                        }
                    }

                    // Clear all other hidden fields
                    if (!btn.classList.contains('created-apply-days')) createdDaysHidden.value = '';
                    if (!btn.classList.contains('created-apply-date')) createdDateHidden.value = '';
                    if (!btn.classList.contains('created-apply-range')) {
                        createdFromHidden.value = '';
                        createdToHidden.value = '';
                    }
                    createdToday.value = '';

                    // Close menu
                    createdMenu.classList.add('hidden');
                });
            });

            // Clear custom inputs
            document.querySelectorAll('.created-clear').forEach(btn => {
                btn.addEventListener('click', () => {
                    // Find the parent custom section
                    const customSection = btn.closest('.created-custom');
                    customSection.classList.add('hidden');
                });
            });

            // Initialize filter values from URL
            function initFilterValues() {
                // Set custom input values from hidden fields
                if (createdDaysHidden.value) {
                    createdDays.value = createdDaysHidden.value;
                }
                if (createdDateHidden.value) {
                    createdDate.value = createdDateHidden.value;
                }
                if (createdFromHidden.value) {
                    createdFrom.value = createdFromHidden.value;
                }
                if (createdToHidden.value) {
                    createdTo.value = createdToHidden.value;
                }
            }

            initFilterValues();
        });
    </script>
@endpush
