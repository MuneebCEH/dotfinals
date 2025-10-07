@extends('layouts.app')

@section('title', 'Max Out Leads')
@section('page-title', 'Max Out Leads')

@section('description')
    A focused workspace for your Max Out pipeline.
@endsection

@php
    use App\Models\Lead;
    use Illuminate\Support\Arr;
    use Illuminate\Support\Str;

    $user = auth()->user();
    $isLeadManager =
        $user &&
        (method_exists($user, 'hasRole') ? $user->hasRole('lead_manager') : ($user->role ?? null) === 'lead_manager');
    $isAdmin = $user && ((method_exists($user, 'isAdmin') && $user->isAdmin()) || $isLeadManager);

    $appTimezone = config('app.timezone', 'UTC');

    $filters = [
        'q' => trim((string) request('q', '')),
        'from' => request('from'),
        'to' => request('to'),
    ];

    $leadQuery = Lead::query()->where('status', 'Max Out');
    $convertedLeadQuery = Lead::query()
        ->where('status', '!=', 'Max Out')
        ->whereHas('statusTransitions', function ($query) {
            $query->where('from_status', 'Max Out')->where('to_status', '!=', 'Max Out');
        });

    if ($filters['q'] !== '') {
        $leadQuery = $leadQuery->search($filters['q']);
        $convertedLeadQuery = $convertedLeadQuery->search($filters['q']);
    }

    if (!empty($filters['from'])) {
        $leadQuery = $leadQuery->whereDate('created_at', '>=', $filters['from']);
        $convertedLeadQuery = $convertedLeadQuery->whereDate('created_at', '>=', $filters['from']);
    }

    if (!empty($filters['to'])) {
        $leadQuery = $leadQuery->whereDate('created_at', '<=', $filters['to']);
        $convertedLeadQuery = $convertedLeadQuery->whereDate('created_at', '<=', $filters['to']);
    }

    $leads = $leadQuery
        ->with(['assignee', 'closer', 'category'])
        ->latest('updated_at')
        ->paginate(25)
        ->withQueryString();

    $convertedLeads = $convertedLeadQuery
        ->with(['assignee', 'closer', 'category', 'lastMaxOutExit.changer'])
        ->withMax(
            [
                'statusTransitions as last_max_out_exit_at' => function ($query) {
                    $query->where('from_status', 'Max Out')->where('to_status', '!=', 'Max Out');
                },
            ],
            'created_at',
        )
        ->orderByDesc('last_max_out_exit_at')
        ->paginate(25, ['*'], 'converted_page')
        ->withQueryString();

    $hasActiveFilters = $filters['q'] !== '' || $filters['from'] || $filters['to'];
    $activeCount = $leads->total();
    $convertedCount = $convertedLeads->total();

    $activeTab = request('tab');
    if (!in_array($activeTab, ['active', 'converted'], true)) {
        $activeTab = request()->has('converted_page') ? 'converted' : 'active';
    }

    $normalizeLeadNumbers = static function ($raw) {
        if ($raw instanceof \Illuminate\Support\Collection) {
            $raw = $raw->all();
        }

        if (is_string($raw)) {
            $decoded = json_decode($raw, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $raw = $decoded;
            } else {
                $trimmed = trim($raw);
                $trimmed = trim($trimmed, '[]');
                $raw = $trimmed === '' ? [] : preg_split('/\s*,\s*/', $trimmed);
            }
        }

        return collect(Arr::wrap($raw))
            ->map(function ($value) {
                if (is_array($value)) {
                    foreach (['number', 'value', 'phone', 0] as $key) {
                        if (isset($value[$key]) && filled($value[$key])) {
                            return $value[$key];
                        }
                    }
                    return '';
                }

                return $value;
            })
            ->map(fn ($value) => trim((string) $value, " \t\n\r\0\x0B\"'"))
            ->filter(fn ($value) => $value !== '')
            ->unique()
            ->values();
    };

    $statusColors = [
        'deal' => 'bg-emerald-400/10 text-emerald-300 border border-emerald-400/30',
        'call back' => 'bg-amber-400/10 text-amber-200 border border-amber-400/30',
        'super lead' => 'bg-purple-400/10 text-purple-200 border border-purple-400/30',
        'new lead' => 'bg-blue-500/10 text-blue-200 border border-blue-500/30',
        'submitted' => 'bg-indigo-400/10 text-indigo-200 border border-indigo-400/30',
        'max out' => 'bg-sky-500/10 text-sky-200 border border-sky-500/30',
        'paid off' => 'bg-lime-400/10 text-lime-200 border border-lime-400/30',
        'not qualified (nq)' => 'bg-rose-400/10 text-rose-200 border border-rose-400/30',
        'default' => 'bg-slate-500/10 text-slate-300 border border-slate-600/40',
    ];
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
            <div class="text-sm text-slate-400 sm:text-right">
                <div>Active Max Out: {{ number_format($activeCount) }} lead{{ $activeCount === 1 ? '' : 's' }}</div>
                <div>Converted After Max Out: {{ number_format($convertedCount) }}
                    lead{{ $convertedCount === 1 ? '' : 's' }}</div>
            </div>
        </div>

        <form id="maxoutFilters" method="GET"
            class="rounded-2xl border border-slate-700 bg-slate-800/80 shadow-xl shadow-slate-900/40 backdrop-blur">
            <div class="space-y-6 p-6">
                <input type="hidden" name="tab" value="{{ $activeTab }}">
                <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                    <div>
                        <label for="q" class="text-sm font-medium text-slate-300">Search</label>
                        <div class="mt-2">
                            <input id="q" name="q" value="{{ $filters['q'] }}"
                                placeholder="Name, phone, city or status"
                                class="w-full rounded-xl border border-slate-700 bg-slate-900/70 px-4 py-2.5 text-sm text-slate-100 placeholder-slate-500 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-500/40"
                                type="text" />
                        </div>
                    </div>
                </div>
            </div>
        </form>

        @php
            $tabDefinitions = [
                'active' => ['label' => 'Active Max Out', 'count' => $activeCount],
                'converted' => ['label' => 'Converted After Max Out', 'count' => $convertedCount],
            ];
        @endphp
        <div class="rounded-2xl border border-slate-700 bg-slate-900/40 shadow-lg shadow-slate-900/30 backdrop-blur">
            <nav class="flex items-stretch gap-2 rounded-2xl border border-slate-700/70 bg-slate-900/60 p-2 text-sm">
                @foreach ($tabDefinitions as $tabKey => $tabMeta)
                    @php
                        $isTabActive = $activeTab === $tabKey;
                        $tabUrl = request()->fullUrlWithQuery([
                            'tab' => $tabKey,
                            'page' => null,
                            'converted_page' => null,
                        ]);
                    @endphp
                    <a href="{{ $tabUrl }}"
                        class="flex flex-1 items-center justify-between gap-3 rounded-xl border px-4 py-2 font-medium transition {{ $isTabActive ? 'border-emerald-400/40 bg-slate-900 text-white shadow-inner shadow-emerald-500/10' : 'border-transparent text-slate-400 hover:text-slate-100 hover:border-slate-700/80 hover:bg-slate-900/60' }}">
                        <span>{{ $tabMeta['label'] }}</span>
                        <span
                            class="rounded-full border border-slate-700/70 bg-slate-800/70 px-2 py-0.5 text-xs text-slate-300">
                            {{ number_format($tabMeta['count']) }}
                        </span>
                    </a>
                @endforeach
            </nav>
        </div>

        @if ($activeTab === 'active')
            <section
                class="space-y-6 rounded-3xl border border-slate-800 bg-slate-900/50 p-6 shadow-xl shadow-slate-900/30">
                <header class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-white">Active Max Out Leads</h2>
                        <p class="text-sm text-slate-400">
                            Cards are sorted by latest activity so the most relevant leads stay on top.
                        </p>
                    </div>
                    <div class="flex items-center gap-2 text-sm text-slate-400">
                        <span
                            class="inline-flex items-center rounded-full border border-slate-700 px-3 py-1 text-xs uppercase tracking-wide">
                            {{ number_format($leads->total()) }} total
                        </span>
                    </div>
                </header>

                @if ($leads->isEmpty())
                    <div class="rounded-2xl border border-slate-700 bg-slate-900/50 p-10 text-center">
                        <h3 class="text-lg font-semibold text-white">No Max Out leads match your filters</h3>
                        <p class="mt-2 text-sm text-slate-400">
                            Adjust your search criteria or reset filters to see all active Max Out leads.
                        </p>
                    </div>
                @else
                    <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                        @foreach ($leads as $lead)
                            @php
                            $numbers = $normalizeLeadNumbers($lead->numbers ?? []);

                                $statusKey = strtolower(trim($lead->status ?? ''));
                                $statusClass = $statusColors[$statusKey] ?? $statusColors['default'];
                                $createdAt = optional(optional($lead->created_at)->timezone($appTimezone));
                                $displayStatus = Str::ucfirst(strtolower($lead->status ?? 'Unknown'));
                            @endphp

                            <article
                                class="group flex h-full flex-col rounded-2xl border border-slate-800 bg-slate-900/60 p-5 shadow-slate-900/40 transition hover:border-blue-500/40 hover:bg-slate-900/80">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <a href="{{ route('leads.show', $lead) }}"
                                            class="text-lg font-semibold text-white transition hover:text-blue-300">
                                            {{ trim($lead->first_name . ' ' . $lead->surname) ?: 'Untitled Lead' }}
                                        </a>
                                        <div class="mt-1 text-xs uppercase tracking-wide text-slate-500">
                                            {{ $lead->gen_code ?: 'GEN #' }}
                                        </div>
                                    </div>
                                    <span
                                        class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusClass }}">
                                        {{ $displayStatus }}
                                    </span>
                                </div>

                                @if ($lead->category)
                                    <div
                                        class="mt-3 inline-flex items-center gap-2 rounded-full border border-slate-700 px-3 py-1 text-xs text-slate-300">
                                        <span class="h-2 w-2 rounded-full bg-blue-400/80"></span>
                                        {{ $lead->category->name }}
                                    </div>
                                @endif

                                <dl class="mt-4 space-y-3 text-sm text-slate-300">
                                    @if ($numbers->isNotEmpty())
                                        <div>
                                            <dt class="text-xs uppercase tracking-wide text-slate-500">Primary contact</dt>
                                            <dd class="mt-1 font-medium text-slate-200">{{ $numbers->first() }}</dd>
                                            @if ($numbers->count() > 1)
                                                <dd class="text-xs text-slate-500">
                                                    +{{ $numbers->count() - 1 }} more
                                                    number{{ $numbers->count() - 1 === 1 ? '' : 's' }}
                                                </dd>
                                            @endif
                                        </div>
                                    @endif

                                    @if ($createdAt)
                                        <div>
                                            <dt class="text-xs uppercase tracking-wide text-slate-500">Created</dt>
                                            <dd class="mt-1 text-slate-200">{{ $createdAt->format('M d, Y') }}</dd>
                                        </div>
                                    @endif

                                    @if ($isAdmin)
                                        <div>
                                            <dt class="text-xs uppercase tracking-wide text-slate-500">Assigned to</dt>
                                            <dd class="mt-1 text-slate-200">{{ $lead->assignee->name ?? 'Unassigned' }}
                                            </dd>
                                        </div>
                                    @endif

                                    @if ($lead->closer)
                                        <div>
                                            <dt class="text-xs uppercase tracking-wide text-slate-500">Closer</dt>
                                            <dd class="mt-1 text-slate-200">{{ $lead->closer->name }}</dd>
                                        </div>
                                    @endif
                                </dl>

                                <div class="mt-auto flex items-center justify-end gap-2 pt-4">
                                    <a href="{{ route('leads.show', $lead) }}"
                                        class="inline-flex items-center gap-2 rounded-xl border border-slate-700 px-3 py-1.5 text-xs font-medium text-slate-200 transition hover:border-blue-500/40 hover:text-blue-200">
                                        View
                                    </a>
                                    <a href="{{ route('leads.edit', $lead) }}"
                                        class="inline-flex items-center gap-2 rounded-xl border border-blue-500/40 px-3 py-1.5 text-xs font-medium text-blue-200 transition hover:bg-blue-500/10">
                                        Edit
                                    </a>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif

                @if ($leads->hasPages())
                    <div class="mt-6 border-t border-slate-800 pt-4 text-sm text-slate-300">
                        {{ $leads->links() }}
                    </div>
                @endif
            </section>
        @endif

        @if ($activeTab === 'converted')
            <section
                class="space-y-6 rounded-3xl border border-slate-800 bg-slate-900/40 p-6 shadow-lg shadow-slate-900/30">
                <header class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-white">Converted After Max Out</h2>
                        <p class="text-sm text-slate-400">
                            Leads that have progressed after hitting Max Out. Track outcomes and recent conversions.
                        </p>
                    </div>
                    <div class="flex items-center gap-2 text-sm text-slate-400">
                        <span
                            class="inline-flex items-center rounded-full border border-slate-700 px-3 py-1 text-xs uppercase tracking-wide">
                            {{ number_format($convertedLeads->total()) }} total
                        </span>
                    </div>
                </header>

                @if ($convertedLeads->isEmpty())
                    <div class="rounded-2xl border border-slate-700 bg-slate-900/50 p-10 text-center">
                        <h3 class="text-lg font-semibold text-white">No converted leads right now</h3>
                        <p class="mt-2 text-sm text-slate-400">
                            As soon as a Max Out lead is moved to another status, it will appear in this tracker.
                        </p>
                    </div>
                @else
                    <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                        @foreach ($convertedLeads as $lead)
                            @php
                                $numbers = $normalizeLeadNumbers($lead->numbers ?? []);
                                $convertedTo =
                                    optional($lead->lastMaxOutExit)->to_status ?? ($lead->status ?? 'Unknown');
                                $convertedBadgeKey = strtolower(trim($convertedTo));
                                $convertedBadgeClass = $statusColors[$convertedBadgeKey] ?? $statusColors['default'];

                                $statusKey = strtolower(trim($lead->status ?? ''));
                                $statusClass = $statusColors[$statusKey] ?? $statusColors['default'];

                                $convertedAt = $lead->last_max_out_exit_at
                                    ? \Illuminate\Support\Carbon::parse($lead->last_max_out_exit_at)->timezone(
                                        $appTimezone,
                                    )
                                    : optional(optional($lead->lastMaxOutExit)->created_at)->timezone($appTimezone);
                                $convertedBy = optional(optional($lead->lastMaxOutExit)->changer)->name ?? 'N/A';
                                $displayStatus = Str::ucfirst(strtolower($lead->status ?? 'Unknown'));
                                $displayConvertedTo = Str::ucfirst(strtolower($convertedTo));
                            @endphp

                            <article
                                class="group flex h-full flex-col rounded-2xl border border-slate-800 bg-slate-900/60 p-5 shadow-slate-900/30 transition hover:border-emerald-400/40 hover:bg-slate-900/80">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <a href="{{ route('leads.show', $lead) }}"
                                            class="text-lg font-semibold text-white transition hover:text-emerald-200">
                                            {{ trim($lead->first_name . ' ' . $lead->surname) ?: 'Untitled Lead' }}
                                        </a>
                                        <div class="mt-1 text-xs uppercase tracking-wide text-slate-500">
                                            {{ $lead->gen_code ?: 'GEN #' }}
                                        </div>
                                    </div>
                                    <span
                                        class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusClass }}">
                                        {{ $displayStatus }}
                                    </span>
                                </div>

                                @if ($lead->category)
                                    <div
                                        class="mt-3 inline-flex items-center gap-2 rounded-full border border-slate-700 px-3 py-1 text-xs text-slate-300">
                                        <span class="h-2 w-2 rounded-full bg-blue-400/80"></span>
                                        {{ $lead->category->name }}
                                    </div>
                                @endif

                                <dl class="mt-4 space-y-3 text-sm text-slate-300">
                                    <div>
                                        <dt class="text-xs uppercase tracking-wide text-slate-500">Converted to</dt>
                                        <dd class="mt-1 inline-flex items-center gap-2">
                                            <span
                                                class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold {{ $convertedBadgeClass }}">
                                                {{ $displayConvertedTo }}
                                            </span>
                                        </dd>
                                    </div>

                                    <div>
                                        <dt class="text-xs uppercase tracking-wide text-slate-500">Current status</dt>
                                        <dd class="mt-1 inline-flex items-center gap-2">
                                            <span
                                                class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusClass }}">
                                                {{ $displayStatus }}
                                            </span>
                                        </dd>
                                    </div>

                                    @if ($numbers->isNotEmpty())
                                        <div>
                                            <dt class="text-xs uppercase tracking-wide text-slate-500">Primary contact</dt>
                                            <dd class="mt-1 font-medium text-slate-200">{{ $numbers->first() }}</dd>
                                            @if ($numbers->count() > 1)
                                                <dd class="text-xs text-slate-500">
                                                    +{{ $numbers->count() - 1 }} more
                                                    number{{ $numbers->count() - 1 === 1 ? '' : 's' }}
                                                </dd>
                                            @endif
                                        </div>
                                    @endif

                                    <div>
                                        <dt class="text-xs uppercase tracking-wide text-slate-500">Converted on</dt>
                                        <dd class="mt-1 text-slate-200">
                                            {{ $convertedAt ? $convertedAt->format('M d, Y h:i A') : 'N/A' }}
                                        </dd>
                                        @if ($convertedAt)
                                            <dd class="text-xs text-slate-500">{{ $convertedAt->diffForHumans() }}</dd>
                                        @endif
                                    </div>

                                    {{-- <div>
                                        <dt class="text-xs uppercase tracking-wide text-slate-500">Converted by</dt>
                                        <dd class="mt-1 text-slate-200">{{ $convertedBy }}</dd>
                                    </div> --}}

                                    @if ($isAdmin)
                                        <div>
                                            <dt class="text-xs uppercase tracking-wide text-slate-500">Assigned to</dt>
                                            <dd class="mt-1 text-slate-200">{{ $lead->assignee->name ?? 'Unassigned' }}
                                            </dd>
                                        </div>
                                    @endif

                                    @if ($lead->closer)
                                        <div>
                                            <dt class="text-xs uppercase tracking-wide text-slate-500">Closer</dt>
                                            <dd class="mt-1 text-slate-200">{{ $lead->closer->name }}</dd>
                                        </div>
                                    @endif
                                </dl>

                                <div class="mt-auto flex items-center justify-end gap-2 pt-4">
                                    <a href="{{ route('leads.show', $lead) }}"
                                        class="inline-flex items-center gap-2 rounded-xl border border-slate-700 px-3 py-1.5 text-xs font-medium text-slate-200 transition hover:border-emerald-400/40 hover:text-emerald-200">
                                        View
                                    </a>
                                    <a href="{{ route('leads.edit', $lead) }}"
                                        class="inline-flex items-center gap-2 rounded-xl border border-emerald-400/40 px-3 py-1.5 text-xs font-medium text-emerald-200 transition hover:bg-emerald-400/10">
                                        Edit
                                    </a>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif

                @if ($convertedLeads->hasPages())
                    <div class="mt-6 border-t border-slate-800 pt-4 text-sm text-slate-300">
                        {{ $convertedLeads->links() }}
                    </div>
                @endif
            </section>
        @endif
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
