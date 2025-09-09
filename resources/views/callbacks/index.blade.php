@extends('layouts.app')

@section('title', 'Call Back Leads')
@section('page-title', 'Call Back Leads')

@section('content')
@php
    use App\Models\Lead;
    use Illuminate\Support\Str;

    $authId = auth()->id();
    $q      = trim(request('q', ''));

    // Build the leads query *in Blade* per your request
    $leadQuery = Lead::query()
    ->where(function ($w) use ($authId) {
        $authIdStr = (string) $authId;

        $w->where('assigned_to', $authId)
          ->orWhereRaw('CAST(assigned_to AS CHAR) LIKE ?', ['%"'.$authIdStr.'"%'])
          ->orWhereRaw('CAST(assigned_to AS CHAR) LIKE ?', ['%['.$authIdStr.',%'])
          ->orWhereRaw('CAST(assigned_to AS CHAR) LIKE ?', ['%,'.$authIdStr.',%'])
          ->orWhereRaw('CAST(assigned_to AS CHAR) LIKE ?', ['%,'.$authIdStr.']%'])
          ->orWhereRaw('CAST(assigned_to AS CHAR) LIKE ?', ['["'.$authIdStr.'"]']);
    })
    ->whereRaw('LOWER(TRIM(`status`)) = ?', ['call back'])
    ->when($q !== '', function ($qq) use ($q) {
        $qq->where(function ($s) use ($q) {
            $s->where('first_name', 'like', "%{$q}%")
              ->orWhere('middle_initial', 'like', "%{$q}%")
              ->orWhere('surname', 'like', "%{$q}%")
              ->orWhere('primary_phone', 'like', "%{$q}%");
        });
    })
    ->with(['callbacks' => function ($cb) {
        $cb->orderBy('scheduled_at');
    }, 'callbacks.user'])
    ->orderByRaw("CONCAT_WS(' ', first_name, middle_initial, surname) ASC");

    $leads = $leadQuery->paginate(12)->withQueryString();
@endphp

<div class="space-y-8">
    <!-- Header -->
    <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl shadow-2xl rounded-2xl p-6 border border-gray-200/50 dark:border-gray-700/50">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold">Leads (Status: Call Back)</h2>
                <p class="text-gray-600 dark:text-gray-400">Only leads assigned to you with status “Call Back”.</p>
            </div>

            <form method="GET" action="{{ url()->current() }}" class="relative w-full max-w-md" id="serverSearchForm">
                <input id="searchInput" type="search" name="q" value="{{ $q }}" placeholder="Search by lead name or phone…"
                       class="pl-10 pr-10 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 bg-white/80 dark:bg-gray-700/80 w-full"
                       autocomplete="off">
                <svg class="w-5 h-5 absolute left-3 top-3.5 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M21 21l-5-5m2-6a7 7 0 10-14 0 7 7 0 0014 0z" />
                </svg>
                <button type="button" id="clearSearch" class="absolute right-2 top-2.5 hidden px-2 py-1 text-xs rounded-md bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">
                    Clear
                </button>
            </form>
        </div>

        <div class="mt-4 text-sm text-gray-500">
            Showing <span class="font-semibold" id="shownCount">{{ $leads->count() }}</span>
            of <span class="font-semibold" id="totalCount">{{ $leads->total() }}</span> leads.
        </div>
    </div>

    <!-- Hidden route templates for JS actions -->
    <div id="callbacksRoot"
         data-route-complete="{{ route('callbacks.complete', ['callback' => '__ID__']) }}"
         data-route-reschedule="{{ route('callbacks.reschedule', ['callback' => '__ID__']) }}"
         data-route-destroy="{{ route('callbacks.destroy', ['callback' => '__ID__']) }}"
         class="hidden"></div>

    <!-- Lead cards -->
    @if ($leads->isEmpty())
        <div class="bg-white/80 dark:bg-gray-800/80 rounded-2xl border border-gray-200/50 dark:border-gray-700/50 p-8 text-center text-gray-500">
            No “Call Back” leads are assigned to you.
        </div>
    @else
        <div id="leadGrid" class="grid grid-cols-1 xl:grid-cols-2 gap-6">
            @foreach ($leads as $lead)
                @php
                    // Build display name and a search blob for client-side filtering
                    $displayName = trim(collect([$lead->first_name, $lead->middle_initial, $lead->surname])->filter()->implode(' '));
                    $searchBlob  = strtolower(trim($displayName.' '.$lead->primary_phone));
                    $leadPhone   = $lead->primary_phone ?? '—';
                    $leadIdStr   = $lead->id ? '#'.$lead->id : '';
                @endphp

                <div class="lead-card bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl shadow-2xl rounded-2xl border border-gray-200/50 dark:border-gray-700/50 overflow-hidden"
                     data-search="{{ e($searchBlob) }}">
                    <!-- Lead header -->
                    <div class="px-6 py-5 border-b border-gray-200/50 dark:border-gray-700/50 flex items-start justify-between gap-4">
                        <div>
                            <div class="text-lg font-semibold">{{ $displayName !== '' ? $displayName : '—' }}</div>
                            <div class="text-sm text-gray-500">
                                Lead {{ $leadIdStr }} · Phone: {{ $leadPhone }} · Status:
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs bg-warning-100 text-warning-800 dark:bg-warning-900/40 dark:text-warning-200">
                                    {{ $lead->status }}
                                </span>
                            </div>
                        </div>

                        <!-- Schedule for this lead -->
                        <button data-open-schedule
                                class="inline-flex items-center px-3 py-1.5 text-sm rounded-lg bg-primary-600 text-white hover:bg-primary-700 transition"
                                data-lead-id="{{ $lead->id }}"
                                data-lead-name="{{ $displayName !== '' ? $displayName : 'Lead '.$lead->id }}">
                            <svg class="w-4 h-4 mr-1.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m6-6H6"/>
                            </svg>
                            Schedule for this Lead
                        </button>
                    </div>

                    <!-- Existing callbacks (if any) -->
                    @if(($lead->callbacks ?? collect())->isNotEmpty())
                        <div class="divide-y divide-gray-100/50 dark:divide-gray-700/50">
                            @foreach ($lead->callbacks->sortBy('scheduled_at') as $cb)
                                @php
                                    $when = $cb->scheduled_at instanceof \Carbon\Carbon
                                        ? $cb->scheduled_at->format('M d, Y g:i A')
                                        : \Carbon\Carbon::parse($cb->scheduled_at)->format('M d, Y g:i A');

                                    $tone = (function($cb){
                                        $when = \Carbon\Carbon::parse($cb->scheduled_at);
                                        return match(true){
                                            $cb->status === 'completed' => ['bg' => 'bg-success-100 dark:bg-success-900/40', 'text'=>'text-success-800 dark:text-success-200', 'label'=>'Completed'],
                                            $when->lt(\Carbon\Carbon::now()->copy()->startOfDay()) => ['bg'=>'bg-danger-100 dark:bg-danger-900/40', 'text'=>'text-danger-800 dark:text-danger-200', 'label'=>'Overdue'],
                                            $when->isToday() => ['bg'=>'bg-warning-100 dark:bg-warning-900/40', 'text'=>'text-warning-800 dark:text-warning-200', 'label'=>'Today'],
                                            default => ['bg'=>'bg-success-100 dark:bg-success-900/40', 'text'=>'text-success-800 dark:text-success-200', 'label'=>'Upcoming'],
                                        };
                                    })($cb);
                                @endphp
                                <div class="p-4">
                                    <div class="flex items-start justify-between gap-4">
                                        <div class="space-y-1">
                                            <div class="text-sm text-gray-600 dark:text-gray-300">
                                                Assignee: <span class="font-medium">{{ $cb->user->name ?? '—' }}</span>
                                            </div>
                                            <div class="text-sm text-gray-600 dark:text-gray-300">
                                                Notes: {{ $cb->notes ?: '—' }}
                                            </div>
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs {{ $tone['bg'] }} {{ $tone['text'] }}">
                                                {{ $tone['label'] }} • {{ $when }}
                                            </span>
                                        </div>

                                        <div class="shrink-0">
                                            @if ($cb->status === 'completed')
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs
                                                    bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-200">
                                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                    Completed
                                                </span>
                                            @else
                                                <div class="flex flex-wrap items-center gap-2">
                                                    <!-- Complete -->
                                                    <form class="inline" action="{{ route('callbacks.complete', $cb) }}" method="POST"
                                                          onsubmit="return confirm('Mark this callback as completed?');">
                                                        @csrf
                                                        <button type="submit"
                                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm rounded-lg
                                                                       bg-green-600 text-white hover:bg-green-700 active:bg-green-800
                                                                       shadow-sm transition">
                                                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                            </svg>
                                                            Complete
                                                        </button>
                                                    </form>

                                                    <!-- Reschedule -->
                                                    <button
                                                        class="px-3 py-1.5 text-sm rounded-lg border border-gray-300 dark:border-gray-600
                                                               hover:bg-gray-50 dark:hover:bg-gray-700/50 transition"
                                                        data-open-reschedule
                                                        data-id="{{ $cb->id }}"
                                                        data-when="{{ $cb->scheduled_at }}">
                                                        Reschedule
                                                    </button>

                                                    <!-- Delete -->
                                                    <button type="button"
                                                            class="inline-flex items-center p-2 text-sm text-red-600 hover:text-red-800"
                                                            title="Delete" data-open-delete
                                                            data-url="{{ route('callbacks.destroy', $cb) }}">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                        </svg>
                                                    </button>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="p-6 text-sm text-gray-500">
                            No callbacks yet for this lead — use the button above to schedule one.
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        <div class="mt-6" id="paginationWrap">
            {{ $leads->links() }}
        </div>
    @endif
</div>

{{-- ========================= Modals ========================= --}}

{{-- SCHEDULE --}}
<div id="scheduleModal" class="modal-root fixed inset-0 bg-black/50 hidden z-50">
    <div class="mx-auto mt-24 max-w-lg rounded-2xl bg-white dark:bg-gray-800 shadow-xl">
        <div class="border-b px-6 py-4 flex items-center justify-between">
            <h3 class="text-lg font-semibold">Schedule Callback</h3>
            <button type="button" data-close-modal class="text-gray-500 hover:text-gray-800">✕</button>
        </div>

        <form action="{{ route('callbacks.store') }}" method="POST" class="p-6 space-y-4">
            @csrf
            <div>
                <label for="lead_id" class="block text-sm font-medium">Lead</label>
                <select id="lead_id" name="lead_id"
                        class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-3 py-2.5" required>
                    @foreach (\App\Models\Lead::whereRaw('LOWER(TRIM(`status`)) = ?', ['call back'])
                        ->where(function ($w) use ($authId) {
                            $authIdStr = (string) $authId;
                            $w->where('assigned_to', $authId)
                              ->orWhereRaw('CAST(assigned_to AS CHAR) LIKE ?', ['%"'.$authIdStr.'"%'])
                              ->orWhereRaw('CAST(assigned_to AS CHAR) LIKE ?', ['%['.$authIdStr.',%'])
                              ->orWhereRaw('CAST(assigned_to AS CHAR) LIKE ?', ['%,'.$authIdStr.',%'])
                              ->orWhereRaw('CAST(assigned_to AS CHAR) LIKE ?', ['%,'.$authIdStr.']%'])
                              ->orWhereRaw('CAST(assigned_to AS CHAR) LIKE ?', ['["'.$authIdStr.'"]']);
                        })
                        ->orderByRaw("CONCAT_WS(' ', first_name, middle_initial, surname) ASC")
                        ->get() as $optLead)
                        @php
                            $optName = trim(collect([$optLead->first_name, $optLead->middle_initial, $optLead->surname])->filter()->implode(' '));
                        @endphp
                        <option value="{{ $optLead->id }}">{{ $optName }} @if($optLead->primary_phone) — {{ $optLead->primary_phone }} @endif</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="scheduled_at" class="block text-sm font-medium">When</label>
                <input type="datetime-local" id="scheduled_at" name="scheduled_at"
                       class="mt-1 w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 px-3 py-2.5"
                       required>
            </div>

            <div>
                <label for="notes" class="block text-sm font-medium">Notes</label>
                <textarea id="notes" name="notes" rows="3"
                          class="mt-1 w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 px-3 py-2.5"></textarea>
            </div>

            <div class="flex justify-end gap-2">
                <button type="button" data-close-modal class="px-4 py-2 rounded-lg border">Cancel</button>
                <button type="submit" class="px-4 py-2 rounded-lg bg-primary-600 text-white">Schedule</button>
            </div>
        </form>
    </div>
</div>

{{-- RESCHEDULE --}}
<div id="rescheduleModal" class="modal-root fixed inset-0 bg-black/50 hidden z-50">
    <div class="mx-auto mt-24 max-w-lg rounded-2xl bg-white dark:bg-gray-800 shadow-xl">
        <div class="border-b px-6 py-4 flex items-center justify-between">
            <h3 class="text-lg font-semibold">Reschedule Callback</h3>
        <button type="button" data-close-modal class="text-gray-500 hover:text-gray-800">✕</button>
        </div>

        {{-- action set by JS to /callbacks/{id}/reschedule --}}
        <form action="" method="POST" class="p-6 space-y-4">
            @csrf
            <div>
                <label for="rescheduled_at" class="block text-sm font-medium">New Time</label>
                <input type="datetime-local" id="rescheduled_at" name="scheduled_at"
                       class="mt-1 w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 px-3 py-2.5"
                       required>
            </div>
            <div class="flex justify-end gap-2">
                <button type="button" data-close-modal class="px-4 py-2 rounded-lg border">Cancel</button>
                <button type="submit" class="px-4 py-2 rounded-lg bg-primary-600 text-white">Save</button>
            </div>
        </form>
    </div>
</div>

{{-- COMPLETE (optional) --}}
<div id="completeModal" class="modal-root fixed inset-0 bg-black/50 hidden z-50">
    <div class="mx-auto mt-24 max-w-md rounded-2xl bg-white dark:bg-gray-800 shadow-xl">
        <div class="border-b px-6 py-4 flex items-center justify-between">
            <h3 class="text-lg font-semibold">Mark as Completed</h3>
            <button type="button" data-close-modal class="text-gray-500 hover:text-gray-800">✕</button>
        </div>
        <form action="" method="POST" class="p-6 space-y-4">
            @csrf
            <p>Are you sure you want to mark this callback as completed?</p>
            <div class="flex justify-end gap-2">
                <button type="button" data-close-modal class="px-4 py-2 rounded-lg border">Cancel</button>
                <button type="submit" class="px-4 py-2 rounded-lg bg-primary-600 text-white">Yes, Complete</button>
            </div>
        </form>
    </div>
</div>

{{-- Delete Modal --}}
<x-modal.confirm-delete id="deleteModal" title="Delete Callback"
    message="Are you sure you want to delete this callback?" />
@endsection

@push('scripts')
<script>
(function () {
    const scheduleModal = document.getElementById('scheduleModal');
    const rescheduleModal = document.getElementById('rescheduleModal');
    const deleteModal = document.getElementById('deleteModal');

    // ---- Realtime client-side search (debounced) ----
    const searchInput = document.getElementById('searchInput');
    const clearBtn = document.getElementById('clearSearch');
    const grid = document.getElementById('leadGrid');
    const shownCountEl = document.getElementById('shownCount');
    const totalCountEl = document.getElementById('totalCount');
    const paginationWrap = document.getElementById('paginationWrap');

    let debTimer = null;

    function normalize(s) { return (s || '').toString().trim().toLowerCase(); }

    function filterCards(term) {
        const t = normalize(term);
        let shown = 0;

        grid?.querySelectorAll('.lead-card').forEach(card => {
            const hay = card.getAttribute('data-search') || '';
            const visible = t === '' || hay.includes(t);
            card.style.display = visible ? '' : 'none';
            if (visible) shown++;
        });

        if (shownCountEl) shownCountEl.textContent = shown;

        // Hide pagination while searching (client-side only affects current page)
        if (paginationWrap) paginationWrap.style.display = (t && shown >= 0) ? 'none' : '';

        // Show/hide Clear button
        if (clearBtn) clearBtn.classList.toggle('hidden', t === '');
    }

    function onSearchInput() {
        clearTimeout(debTimer);
        debTimer = setTimeout(() => filterCards(searchInput.value), 120);
    }

    searchInput?.addEventListener('input', onSearchInput);

    // Clear button
    clearBtn?.addEventListener('click', () => {
        searchInput.value = '';
        filterCards('');
        searchInput.focus();
    });

    // Initialize with existing value (if any)
    filterCards(searchInput?.value || '');

    // Keep Enter to submit for server-side search across pages
    document.getElementById('serverSearchForm')?.addEventListener('submit', () => {
        // no-op; default submit happens
    });

    // ---- Existing modals logic ----

    // Open schedule modal and preselect the lead
    document.querySelectorAll('[data-open-schedule]').forEach(btn => {
        btn.addEventListener('click', () => {
            scheduleModal.classList.remove('hidden');
            const form = scheduleModal.querySelector('form');
            form?.reset();

            const leadId = btn.dataset.leadId || '';
            const select = scheduleModal.querySelector('#lead_id');
            if (select) select.value = leadId;
        });
    });

    // Reschedule
    document.querySelectorAll('[data-open-reschedule]').forEach(btn => {
        btn.addEventListener('click', () => {
            const id = btn.dataset.id;
            const when = btn.dataset.when;
            rescheduleModal.classList.remove('hidden');
            const form = rescheduleModal.querySelector('form');
            const routeTpl = document.getElementById('callbacksRoot')?.dataset.routeReschedule || "{{ route('callbacks.reschedule', ['callback' => '__ID__']) }}";
            if (form && routeTpl) form.action = routeTpl.replace('__ID__', id);

            const inputWhen = rescheduleModal.querySelector('input[name="scheduled_at"]');
            if (inputWhen && when) {
                try {
                    const dt = new Date(when);
                    inputWhen.value = dt.toISOString().slice(0, 16);
                } catch (e) {}
            }
        });
    });

    // Complete (if wired)
    document.querySelectorAll('[data-open-complete]').forEach(btn => {
        btn.addEventListener('click', () => {
            const id = btn.dataset.id;
            const modal = document.getElementById('completeModal');
            if (!modal) return;
            modal.classList.remove('hidden');
            const form = modal.querySelector('form');
            const routeTpl = document.getElementById('callbacksRoot')?.dataset.routeComplete || "{{ route('callbacks.complete', ['callback' => '__ID__']) }}";
            if (form && routeTpl) form.action = routeTpl.replace('__ID__', id);
        });
    });

    // Delete
    document.querySelectorAll('[data-open-delete]').forEach(btn => {
        btn.addEventListener('click', () => {
            const form = deleteModal.querySelector('form');
            form.action = btn.dataset.url;
            deleteModal.classList.remove('hidden');
        });
    });

    // Close modals
    document.querySelectorAll('[data-close-modal]').forEach(x => {
        x.addEventListener('click', () => {
            const m = x.closest('.modal-root');
            if (m) m.classList.add('hidden');
        });
    });
})();
</script>
@endpush
