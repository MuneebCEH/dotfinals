@extends('layouts.app')

@section('title', 'Max Out Leads')
@section('page-title', 'Max Out Leads')

@section('description')
    A focused workspace for your Max Out pipeline.
@endsection

@php
    use App\Models\Lead;

    $user = auth()->user();
    $isLeadManager = $user && (method_exists($user, 'hasRole')
        ? $user->hasRole('lead_manager')
        : (($user->role ?? null) === 'lead_manager'));
    $isAdmin = $user && ((method_exists($user, 'isAdmin') && $user->isAdmin()) || $isLeadManager);

    $appTimezone = config('app.timezone', 'UTC');

    $filters = [
        'q' => trim((string) request('q', '')),
        'from' => request('from'),
        'to' => request('to'),
    ];

    $leadQuery = Lead::query()->where('status', 'Max Out');

    if ($filters['q'] !== '') {
        $leadQuery = $leadQuery->search($filters['q']);
    }

    if (!empty($filters['from'])) {
        $leadQuery = $leadQuery->whereDate('created_at', '>=', $filters['from']);
    }

    if (!empty($filters['to'])) {
        $leadQuery = $leadQuery->whereDate('created_at', '<=', $filters['to']);
    }

    $leads = $leadQuery
        ->with(['assignee', 'closer', 'category'])
        ->latest('updated_at')
        ->paginate(25)
        ->withQueryString();

    $hasActiveFilters = $filters['q'] !== '' || $filters['from'] || $filters['to'];
@endphp

@section('content')
    <div class="space-y-8 text-slate-100">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-white">Max Out Leads</h1>
                <p class="mt-1 text-sm text-slate-400">
                    Review every lead marked as Max Out and drill down with focused filters.
                </p>
            </div>
            <div class="text-sm text-slate-400">
                Showing {{ number_format($leads->total()) }} lead{{ $leads->total() === 1 ? '' : 's' }}
            </div>
        </div>

        <form id="maxoutFilters" method="GET" class="rounded-2xl border border-slate-700 bg-slate-800/80 shadow-xl shadow-slate-900/40 backdrop-blur">
            <div class="space-y-6 p-6">
                <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                    <div>
                        <label for="q" class="text-sm font-medium text-slate-300">Search</label>
                        <div class="mt-2">
                            <input
                                id="q"
                                name="q"
                                value="{{ $filters['q'] }}"
                                placeholder="Name, phone, city or status"
                                class="w-full rounded-xl border border-slate-700 bg-slate-900/70 px-4 py-2.5 text-sm text-slate-100 placeholder-slate-500 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-500/40"
                                type="text"
                            />
                        </div>
                    </div>
                    {{-- <div>
                        <label for="from" class="text-sm font-medium text-slate-300">Created From</label>
                        <div class="mt-2">
                            <input
                                id="from"
                                name="from"
                                value="{{ $filters['from'] }}"
                                class="w-full rounded-xl border border-slate-700 bg-slate-900/70 px-4 py-2.5 text-sm text-slate-100 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-500/40"
                                type="date"
                            />
                        </div>
                    </div>
                    <div>
                        <label for="to" class="text-sm font-medium text-slate-300">Created To</label>
                        <div class="mt-2">
                            <input
                                id="to"
                                name="to"
                                value="{{ $filters['to'] }}"
                                class="w-full rounded-xl border border-slate-700 bg-slate-900/70 px-4 py-2.5 text-sm text-slate-100 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-500/40"
                                type="date"
                            />
                        </div>
                    </div> --}}
                </div>
            </div>
            {{-- <div class="flex flex-col gap-3 border-t border-slate-700 bg-slate-900/40 px-6 py-4 text-sm text-slate-400 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    Combine filters to quickly pinpoint the leads that need attention.
                </div>
                <div class="flex items-center gap-3">
                    @if ($hasActiveFilters)
                        <a
                            href="{{ route('leads.maxout') }}"
                            class="inline-flex items-center rounded-lg border border-transparent px-4 py-2 text-sm font-medium text-slate-300 transition hover:text-white"
                        >
                            Reset
                        </a>
                    @endif
                    <button
                        type="submit"
                        class="inline-flex items-center rounded-lg bg-blue-500 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-500/40"
                    >
                        Apply filters
                    </button>
                </div>
            </div> --}}
        </form>

        <div class="overflow-hidden rounded-2xl border border-slate-700 bg-slate-800/80 shadow-xl shadow-slate-900/40 backdrop-blur">
            <table class="min-w-full divide-y divide-slate-700">
                <thead class="bg-slate-900/70">
                    <tr>
                        @if ($isAdmin)
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-400 w-12">
                                <span class="sr-only">Select</span>
                            </th>
                        @endif
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-400">Lead Details</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-400">Contact Information</th>
                        @if ($isAdmin)
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-400">Assigned To</th>
                        @endif
                        {{-- <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-400">Assigned Time</th> --}}
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-400">Status</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-400">Created Date</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-wide text-slate-400">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800 bg-transparent">
                    @forelse ($leads as $lead)
                        @php
                            $numbers = [];
                            if (is_array($lead->numbers)) {
                                $numbers = $lead->numbers;
                            } elseif (is_string($lead->numbers)) {
                                $decoded = json_decode($lead->numbers, true);
                                $numbers = is_array($decoded) ? $decoded : [];
                            }

                            try {
                                $assignedAt = $lead->assigned_time
                                    ? \Illuminate\Support\Carbon::parse($lead->assigned_time)->timezone($appTimezone)->format('M d, Y h:i A')
                                    : null;
                            } catch (\Exception $exception) {
                                $assignedAt = null;
                            }

                            $statusColors = [
                                'deal' => 'bg-green-400/10 text-green-300 border border-green-400/30',
                                'call back' => 'bg-yellow-400/10 text-yellow-200 border border-yellow-400/30',
                                'super lead' => 'bg-purple-400/10 text-purple-200 border border-purple-400/30',
                                'new lead' => 'bg-blue-500/10 text-blue-200 border border-blue-500/30',
                                'submitted' => 'bg-indigo-400/10 text-indigo-200 border border-indigo-400/30',
                                'max out' => 'bg-blue-500/10 text-blue-200 border border-blue-500/30',
                                'default' => 'bg-slate-500/10 text-slate-300 border border-slate-600/40',
                            ];
                            $statusKey = strtolower(trim($lead->status ?? ''));
                            $statusClass = $statusColors[$statusKey] ?? $statusColors['default'];
                        @endphp
                        <tr class="transition hover:bg-slate-800/60">
                            @if ($isAdmin)
                                <td class="px-6 py-4 align-top">
                                    @can('delete', $lead)
                                        <input
                                            type="checkbox"
                                            class="lead-select rounded border-slate-600 bg-slate-900 text-blue-400 focus:ring-blue-500"
                                            data-id="{{ $lead->id }}"
                                        >
                                    @else
                                        <input type="checkbox" class="rounded border-slate-700 bg-slate-900" disabled>
                                    @endcan
                                </td>
                            @endif
                            <td class="px-6 py-4 align-top">
                                <div class="flex items-center gap-3">
                                    <div class="h-10 w-10 rounded-lg bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center shadow-lg shadow-blue-900/40 flex-shrink-0">
                                        <span class="text-sm font-bold text-white">
                                            {{ strtoupper(substr($lead->surname ?: $lead->first_name ?: 'L', 0, 1)) }}
                                        </span>
                                    </div>
                                    <div class="min-w-0">
                                        <a
                                            href="{{ route('leads.show', $lead) }}"
                                            class="block truncate text-sm font-medium text-slate-100 hover:text-blue-300"
                                        >
                                            {{ trim($lead->first_name . ' ' . $lead->surname) ?: 'N/A' }}
                                        </a>
                                        <div class="mt-1 text-xs text-slate-400">
                                            {{ $lead->gen_code ?: 'N/A' }}
                                        </div>
                                        @if ($lead->category)
                                            <div class="mt-1 inline-flex items-center rounded-full bg-slate-900/70 px-2 py-0.5 text-xs text-slate-300">
                                                {{ $lead->category->name }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 align-top">
                                @if (!empty($numbers))
                                    <div class="space-y-1 text-sm text-slate-200">
                                        @foreach (array_slice($numbers, 0, 2) as $n)
                                            <div>{{ $n }}</div>
                                        @endforeach
                                        @if (count($numbers) > 2)
                                            <div class="text-xs text-slate-400">
                                                +{{ count($numbers) - 2 }} more numbers
                                            </div>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-sm text-slate-500">No contact numbers</span>
                                @endif
                            </td>
                            @if ($isAdmin)
                                <td class="px-6 py-4 align-top">
                                    <span class="text-sm text-slate-200">
                                        {{ $lead->assignee->name ?? 'Unassigned' }}
                                    </span>
                                </td>
                            @endif
                            {{-- <td class="px-6 py-4 align-top">
                                <span class="text-sm text-slate-200">
                                    {{ $assignedAt ?? 'N/A' }}
                                </span>
                            </td> --}}
                            <td class="px-6 py-4 align-top">
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $statusClass }}">
                                    {{ $lead->status ?? 'Unknown' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 align-top">
                                <span class="text-sm text-slate-200">
                                    {{ optional(optional($lead->created_at)->timezone($appTimezone))->format('M d, Y') ?? 'N/A' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 align-top text-right text-sm">
                                {{-- @can('update', $lead) --}}
                                    <a
                                        href="{{ route('leads.edit', $lead) }}"
                                        class="inline-flex items-center rounded-lg border border-blue-500/40 px-4 py-2 font-medium text-blue-300 transition hover:bg-blue-500/10 hover:text-blue-200"
                                    >
                                        Edit
                                    </a>
                                {{-- @else --}}
                                    <a
                                        href="{{ route('leads.show', $lead) }}"
                                        class="inline-flex items-center rounded-lg border border-blue-500/40 px-4 py-2 font-medium text-blue-300 transition hover:bg-blue-500/10 hover:text-blue-200"
                                    >
                                        View
                                    </a>
                                {{-- @endcan/ --}}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $isAdmin ? 8 : 6 }}" class="px-6 py-12 text-center text-sm text-slate-500">
                                No Max Out leads match your current filters. Adjust the filters or check back later.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="flex flex-col gap-3 border-t border-slate-700 bg-slate-900/50 px-6 py-4 text-sm text-slate-400 md:flex-row md:items-center md:justify-between">
                <div>
                    @if ($leads->total() === 0)
                        Nothing to display.
                    @else
                        Showing
                        <span class="font-semibold text-slate-200">{{ number_format($leads->firstItem() ?? 0) }}</span>
                        to
                        <span class="font-semibold text-slate-200">{{ number_format($leads->lastItem() ?? 0) }}</span>
                        of
                        <span class="font-semibold text-slate-200">{{ number_format($leads->total()) }}</span>
                        results
                    @endif
                </div>
                <div class="text-slate-300">
                    {{ $leads->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('maxoutFilters');
            if (!form) return;

            const submitForm = () => {
                if (typeof form.requestSubmit === 'function') {
                    form.requestSubmit();
                } else {
                    form.submit();
                }
            };

            const debounce = (fn, delay = 400) => {
                let timer;
                return (...args) => {
                    clearTimeout(timer);
                    timer = setTimeout(() => fn.apply(null, args), delay);
                };
            };

            const searchInput = form.querySelector('input[name="q"]');
            if (searchInput) {
                const debouncedSubmit = debounce(submitForm, 400);
                searchInput.addEventListener('input', debouncedSubmit);
            }

            form.querySelectorAll('input[type="date"]').forEach((input) => {
                input.addEventListener('change', submitForm);
            });
        });
    </script>
@endpush
