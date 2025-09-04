    @extends('layouts.app')

    @section('title', 'Call Backs')
    @section('page-title', 'Call Backs')

    @section('content')
        @php
            // Safe defaults in case controller didn't pass them
$tab = $tab ?? request('tab', 'today');
$q = $q ?? request('q', '');
        @endphp

        <div class="space-y-8">

            <!-- Header -->
            <div
                class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl shadow-2xl rounded-2xl p-6 border border-gray-200/50 dark:border-gray-700/50">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-2xl font-bold">Call Back Queue</h2>
                        <p class="text-gray-600 dark:text-gray-400">Manage scheduled and personal follow‑ups.</p>
                    </div>

                    <button id="openScheduleModal"
                        class="inline-flex items-center px-5 py-2.5 bg-gradient-to-r from-primary-500 to-primary-600 text-white rounded-xl shadow-md hover:shadow-lg transition">
                        <svg class="w-5 h-5 mr-2" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m6-6H6" />
                        </svg>
                        Schedule Call Back
                    </button>
                </div>

                <!-- Tabs / Filters -->
                <div class="mt-6 flex flex-wrap gap-3 items-center">
                    <a href="{{ route('callbacks.index', ['tab' => 'overdue', 'q' => $q]) }}"
                        class="px-4 py-2 rounded-lg text-sm font-medium {{ $tab === 'overdue' ? 'bg-danger-100 text-danger-800 dark:bg-danger-900/40 dark:text-danger-200' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300' }}">
                        Overdue
                    </a>
                    <a href="{{ route('callbacks.index', ['tab' => 'today', 'q' => $q]) }}"
                        class="px-4 py-2 rounded-lg text-sm font-medium {{ $tab === 'today' ? 'bg-warning-100 text-warning-800 dark:bg-warning-900/40 dark:text-warning-200' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300' }}">
                        Today
                    </a>
                    <a href="{{ route('callbacks.index', ['tab' => 'upcoming', 'q' => $q]) }}"
                        class="px-4 py-2 rounded-lg text-sm font-medium {{ $tab === 'upcoming' ? 'bg-success-100 text-success-800 dark:bg-success-900/40 dark:text-success-200' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300' }}">
                        Upcoming
                    </a>

                    <form method="GET" action="{{ route('callbacks.index') }}" class="ml-auto relative">
                        <input type="hidden" name="tab" value="{{ $tab }}">
                        <input type="search" name="q" value="{{ $q }}"
                            placeholder="Search by lead, phone, assignee…"
                            class="pl-10 pr-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 bg-white/80 dark:bg-gray-700/80">
                        <svg class="w-5 h-5 absolute left-3 top-3.5 text-gray-400" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-5-5m2-6a7 7 0 10-14 0 7 7 0 0014 0z" />
                        </svg>
                    </form>
                </div>
            </div>

            <!-- Table -->
            <div id="callbacksRoot" data-route-complete="{{ route('callbacks.complete', ['callback' => '__ID__']) }}"
                data-route-reschedule="{{ route('callbacks.reschedule', ['callback' => '__ID__']) }}"
                data-route-destroy="{{ route('callbacks.destroy', ['callback' => '__ID__']) }}"
                class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl shadow-2xl rounded-2xl border border-gray-200/50 dark:border-gray-700/50 overflow-hidden">

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200/50 dark:divide-gray-700/50">
                        <thead class="bg-gray-50/80 dark:bg-gray-700/80">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider">Lead</th>
                                <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider">Phone</th>
                                <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider">Scheduled</th>
                                <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider">Assignee</th>
                                <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider">Notes</th>
                                <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100/50 dark:divide-gray-700/50">
                            @forelse ($callbacks as $cb)
                                @php
                                    $leadName = $cb->lead?->full_name ?? 'No Lead Assigned';
                                    $phone = $cb->lead?->primary_phone ?? '—';
                                    $leadId = $cb->lead?->id ? '#' . $cb->lead->id : '';
                                    $assignee = $cb->user->name ?? '—';
                                    $when =
                                        $cb->scheduled_at instanceof \Carbon\Carbon
                                            ? $cb->scheduled_at->format('M d, Y g:i A')
                                            : \Carbon\Carbon::parse($cb->scheduled_at)->format('M d, Y g:i A');

                                    $tone = match (true) {
                                        $cb->status === 'completed' => [
                                            'bg' => 'bg-success-100 dark:bg-success-900/40',
                                            'text' => 'text-success-800 dark:text-success-200',
                                        ],
                                        $cb->status === 'cancelled' => [
                                            'bg' => 'bg-gray-200 dark:bg-gray-700',
                                            'text' => 'text-gray-800 dark:text-gray-200',
                                        ],
                                        \Carbon\Carbon::parse($cb->scheduled_at)->isPast() => [
                                            'bg' => 'bg-danger-100 dark:bg-danger-900/40',
                                            'text' => 'text-danger-800 dark:text-danger-200',
                                        ],
                                        \Carbon\Carbon::parse($cb->scheduled_at)->isToday() => [
                                            'bg' => 'bg-warning-100 dark:bg-warning-900/40',
                                            'text' => 'text-warning-800 dark:text-warning-200',
                                        ],
                                        default => [
                                            'bg' => 'bg-success-100 dark:bg-success-900/40',
                                            'text' => 'text-success-800 dark:text-success-200',
                                        ],
                                    };
                                @endphp

                                <tr class="hover:bg-gray-50/60 dark:hover:bg-gray-700/60">
                                    <td class="px-6 py-4">
                                        <div class="font-semibold">{{ $leadName }}</div>
                                        @if ($leadId)
                                            <div class="text-sm text-gray-500">Lead {{ $leadId }}</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">{{ $phone }}</td>
                                    <td class="px-6 py-4">
                                        <span
                                            class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs {{ $tone['bg'] }} {{ $tone['text'] }}">
                                            {{ $when }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">{{ $assignee }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300 max-w-[24rem]">
                                        {{ $cb->notes ?: '—' }}
                                    </td>
                                    <td class="px-6 py-4">
                                        @if ($cb->status === 'completed')
                                            {{-- Status badge only (no actions) --}}
                                            <span
                                                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs
                     bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-200">
                                                {{-- check icon --}}
                                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"
                                                    stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M5 13l4 4L19 7" />
                                                </svg>
                                                Completed
                                            </span>
                                        @else
                                            <div class="flex flex-wrap items-center gap-2">
                                                {{-- Complete --}}
                                                <form class="inline" action="{{ route('callbacks.complete', $cb) }}"
                                                    method="POST"
                                                    onsubmit="return confirm('Mark this callback as completed?');">
                                                    @csrf
                                                    <button type="submit"
                                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm rounded-lg
                           bg-green-600 text-white hover:bg-green-700 active:bg-green-800
                           shadow-sm transition">
                                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"
                                                            stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2" d="M5 13l4 4L19 7" />
                                                        </svg>
                                                        Complete
                                                    </button>
                                                </form>

                                                {{-- Reschedule (modal) --}}
                                                <button
                                                    class="px-3 py-1.5 text-sm rounded-lg border border-gray-300 dark:border-gray-600
                       hover:bg-gray-50 dark:hover:bg-gray-700/50 transition"
                                                    data-open-reschedule data-id="{{ $cb->id }}"
                                                    data-when="{{ $cb->scheduled_at }}">
                                                    Reschedule
                                                </button>

                                                {{-- Delete --}}
                                                <button type="button"
                                                    class="inline-flex items-center p-2 text-sm text-red-600 hover:text-red-800"
                                                    title="Delete" data-open-delete
                                                    data-url="{{ route('callbacks.destroy', $cb) }}">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none"
                                                        viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                </button>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12">
                                        <div class="text-center text-gray-500">No callbacks here — try another tab or
                                            schedule
                                            one.</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if (method_exists($callbacks, 'links'))
                    <div class="px-6 py-4">
                        {{ $callbacks->withQueryString()->links() }}
                    </div>
                @endif
            </div>
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
                        <label for="lead_id" class="block text-sm font-medium">Lead (optional)</label>
                        <select name="lead_id"
                            class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-3 py-2.5">
                            <option value="">Personal (no lead)</option>
                            @foreach ($leadsForUser ?? collect() as $lead)
                                <option value="{{ $lead->id }}">
                                    {{ $lead->full_name }} @if ($lead->primary_phone)
                                        — {{ $lead->primary_phone }}
                                    @endif
                                </option>
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

        {{-- COMPLETE --}}
        <div id="completeModal" class="modal-root fixed inset-0 bg-black/50 hidden z-50">
            <div class="mx-auto mt-24 max-w-md rounded-2xl bg-white dark:bg-gray-800 shadow-xl">
                <div class="border-b px-6 py-4 flex items-center justify-between">
                    <h3 class="text-lg font-semibold">Mark as Completed</h3>
                    <button type="button" data-close-modal class="text-gray-500 hover:text-gray-800">✕</button>
                </div>

                {{-- action set by JS to /callbacks/{id}/complete --}}
                <form action="" method="POST" class="p-6 space-y-4">
                    @csrf
                    <p>Are you sure you want to mark this callback as completed?</p>
                    <div class="flex justify-end gap-2">
                        <button type="button" data-close-modal class="px-4 py-2 rounded-lg border">Cancel</button>
                        <button type="submit" class="px-4 py-2 rounded-lg bg-primary-600 text-white">Yes,
                            Complete</button>
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
            (function() {
                const root = document.getElementById('callbacksRoot');
                const scheduleBtn = document.getElementById('openScheduleModal');

                const routeTpl = {
                    complete: root?.dataset.routeComplete || '',
                    reschedule: root?.dataset.routeReschedule || '',
                    destroy: root?.dataset.routeDestroy || '',
                };

                // Schedule
                scheduleBtn?.addEventListener('click', () => {
                    const modal = document.getElementById('scheduleModal');
                    if (!modal) return;
                    modal.classList.remove('hidden');
                    modal.querySelector('form')?.reset();
                });

                // Reschedule
                document.querySelectorAll('[data-open-reschedule]').forEach(btn => {
                    btn.addEventListener('click', () => {
                        const id = btn.dataset.id;
                        const when = btn.dataset.when;
                        const modal = document.getElementById('rescheduleModal');
                        if (!modal) return;

                        modal.classList.remove('hidden');

                        const form = modal.querySelector('form');
                        if (form && routeTpl.reschedule) {
                            form.action = routeTpl.reschedule.replace('__ID__', id);
                        }

                        const inputWhen = modal.querySelector('input[name="scheduled_at"]');
                        if (inputWhen && when) {
                            try {
                                const dt = new Date(when);
                                inputWhen.value = dt.toISOString().slice(0, 16); // yyyy-MM-ddTHH:mm
                            } catch (e) {
                                /* noop */
                            }
                        }
                    });
                });

                // Complete
                document.querySelectorAll('[data-open-complete]').forEach(btn => {
                    btn.addEventListener('click', () => {
                        const id = btn.dataset.id;
                        const modal = document.getElementById('completeModal');
                        if (!modal) return;

                        modal.classList.remove('hidden');

                        const form = modal.querySelector('form');
                        if (form && routeTpl.complete) {
                            form.action = routeTpl.complete.replace('__ID__', id);
                        }
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


            document.querySelectorAll('[data-open-delete]').forEach(btn => {
                btn.addEventListener('click', () => {
                    const modal = document.getElementById('deleteModal');
                    const form = modal.querySelector('form');
                    form.action = btn.dataset.url;
                    modal.classList.remove('hidden');
                });
            });
            document.querySelectorAll('[data-close-modal]').forEach(btn => {
                btn.addEventListener('click', () => {
                    btn.closest('.modal-root').classList.add('hidden');
                });
            });
        </script>
    @endpush
