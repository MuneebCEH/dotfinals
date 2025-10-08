@extends('layouts.app')

@push('head')
    {{-- CSRF token meta for JS scripts --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
@endpush

@section('title', 'Dashboard')

@section('content')
    @php
        use Carbon\Carbon;
        use Illuminate\Support\Facades\DB;
        use App\Models\UserAttendance;

        $LeadModel = \App\Models\Lead::class;
        $UserModel = \App\Models\User::class;
        $CategoryModel = \App\Models\Category::class;

        // Get current user and role
        $user = auth()->user();
        $userId = $user->id;

        $isRegularUser = $user->role === 'user';
        $isMaxOutUser = $user->role === 'max_out';
        $isThatSubmittedUser = $user->role === 'that_submitted';

        // Pakistan timezone anchors
        $pakistanNow = now()->setTimezone('Asia/Karachi');
        $todayStartLocal = $pakistanNow->copy()->startOfDay();
        $todayEndLocal = $pakistanNow->copy()->endOfDay();
        $monthStartLocal = $pakistanNow->copy()->startOfMonth();

        // Convert to UTC (if timestamps are stored in UTC)
        $todayStartUtc = $todayStartLocal->copy()->setTimezone('UTC');
        $todayEndUtc = $todayEndLocal->copy()->setTimezone('UTC');
        $monthStartUtc = $monthStartLocal->copy()->setTimezone('UTC');

        // Today's attendance for regular users
$todayAttendance = $isRegularUser
    ? UserAttendance::where('user_id', $user->id)
        ->whereBetween('check_in', [$todayStartUtc, $todayEndUtc])
        ->latest('check_in')
        ->first()
    : null;

$isCheckedIn = $todayAttendance && $todayAttendance->status === 'in';
$isCheckedOut = $todayAttendance && $todayAttendance->status === 'out';

// Base query
$leadQuery = class_exists($LeadModel) ? $LeadModel::query() : null;

if ($leadQuery) {
    if ($isThatSubmittedUser) {
        // That Submitted users see only That Submitted leads
        $leadQuery->where('status', 'That Submitted');
    } elseif ($isMaxOutUser) {
        // Max Out users see only Max Out leads
        $leadQuery->where('status', 'Max Out');
    } elseif ($isRegularUser) {
        // Regular users see only their assigned leads
        $leadQuery->where(function ($query) use ($userId) {
            $query
                ->where('assigned_to', $userId)
                ->orWhere('super_agent_id', $userId)
                ->orWhere('closer_id', $userId);
        });
    }
    // Admins/lead managers see all leads (no restrictions)
}

// Primary metrics
$totalLeads = $leadQuery ? $leadQuery->clone()->count() : 0;
$newLeadsToday = $leadQuery
    ? $leadQuery
        ->clone()
        ->whereBetween('created_at', [$todayStartUtc, $todayEndUtc])
        ->count()
    : 0;
$thisMonthLeads = $leadQuery ? $leadQuery->clone()->where('created_at', '>=', $monthStartUtc)->count() : 0;

$unassignedLeads = $leadQuery
    ? $leadQuery
        ->clone()
        ->whereNull('assigned_to')
        ->whereNull('super_agent_id')
        ->whereNull('closer_id')
        ->count()
    : 0;

$withCards = $leadQuery
    ? $leadQuery->clone()->whereNotNull('cards_json')->whereRaw('JSON_LENGTH(cards_json) > 0')->count()
    : 0;

$withBalance = $leadQuery
    ? (int) $leadQuery->clone()->whereNotNull('balance')->where('balance', '>', 0)->count()
    : 0;

// Status/category only for non-max_out and non-that_submitted users
$leadsByStatus =
    !$isMaxOutUser && !$isThatSubmittedUser && $leadQuery
        ? $leadQuery
            ->clone()
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->orderByDesc('count')
            ->get()
        : collect();

$topCategories =
    !$isMaxOutUser && !$isThatSubmittedUser && $leadQuery && class_exists($CategoryModel)
        ? $leadQuery
            ->clone()
            ->select('category_id', DB::raw('COUNT(*) as count'))
            ->groupBy('category_id')
            ->orderByDesc('count')
            ->limit(5)
            ->get()
            ->map(function ($row) use ($CategoryModel) {
                $row->category_name =
                    optional($CategoryModel::find($row->category_id))->name ?? 'Uncategorized';
                return $row;
            })
        : collect();

// Monthly trend
$monthlyLeadCounts = $leadQuery
    ? $leadQuery
        ->clone()
        ->select(DB::raw("DATE_FORMAT(created_at, '%Y-%m') as ym"), DB::raw('COUNT(*) as count'))
        ->where('created_at', '>=', now()->subMonths(11)->startOfMonth())
        ->groupBy(DB::raw("DATE_FORMAT(created_at, '%Y-%m')"))
        ->orderBy('ym')
        ->get()
    : collect();

// Owner performance (not for regular/max_out/that_submitted users)
$ownersPerformance =
    !$isRegularUser && !$isMaxOutUser && !$isThatSubmittedUser && $leadQuery
        ? $leadQuery
            ->clone()
            ->select('assigned_to', DB::raw('COUNT(*) as count'))
            ->whereNotNull('assigned_to')
            ->groupBy('assigned_to')
            ->orderByDesc('count')
            ->limit(5)
            ->get()
            ->map(function ($row) use ($UserModel) {
                $row->owner = class_exists($UserModel)
                    ? optional($UserModel::find($row->assigned_to))->name ?? 'Unknown'
                    : 'Unknown';
                return $row;
            })
        : collect();

// Recent leads
$recentLeads = $leadQuery ? $leadQuery->clone()->latest()->limit(10)->get() : collect();

// Cards
$cards = [];

if ($isThatSubmittedUser) {
    $thatSubmittedTodayCount = $LeadModel
        ::where('status', 'That Submitted')
        ->whereBetween('created_at', [Carbon::today()->startOfDay(), Carbon::today()->endOfDay()])
        ->count();

    $cards = [
        [
            'label' => 'Active That Submitted Leads',
            'value' => $totalLeads,
            'icon' => 'fas fa-check-circle',
            'color' => 'bg-green-100 text-green-800',
            'visible' => true,
        ],
        [
            'label' => 'New That Submitted Today',
            'value' => $thatSubmittedTodayCount,
            'icon' => 'fas fa-sun',
            'color' => 'bg-amber-100 text-amber-800',
            'visible' => true,
        ],
        [
            'label' => 'That Submitted Leads',
            'value' => $totalLeads,
            'icon' => 'fas fa-check-circle',
            'color' => 'bg-green-100 text-green-800',
            'visible' => $totalLeads > 0,
        ],
    ];
} elseif ($isMaxOutUser) {
    $maxOutTodayCount = $LeadModel
        ::where('status', 'Max Out')
        ->whereBetween('created_at', [Carbon::today()->startOfDay(), Carbon::today()->endOfDay()])
        ->count();

    $cards = [
        [
            'label' => 'Active Max Out Leads',
            'value' => $totalLeads,
            'icon' => 'fas fa-bolt',
            'color' => 'bg-red-100 text-red-800',
            'visible' => true,
        ],
        [
            'label' => 'New Max Out Today',
            'value' => $maxOutTodayCount,
            'icon' => 'fas fa-sun',
            'color' => 'bg-amber-100 text-amber-800',
            'visible' => true,
        ],
        [
            'label' => 'Max Out Leads',
            'value' => $totalLeads,
            'icon' => 'fas fa-bolt',
            'color' => 'bg-red-100 text-red-800',
            'visible' => $totalLeads > 0,
        ],
    ];
} else {
    $cards = [
        [
            'label' => 'Total Leads',
            'value' => $totalLeads,
            'icon' => 'fas fa-users',
            'color' => 'bg-indigo-100 text-indigo-800',
            'visible' => true,
        ],
        [
            'label' => 'New Today',
            'value' => $newLeadsToday,
            'icon' => 'fas fa-sun',
            'color' => 'bg-emerald-100 text-emerald-800',
            'visible' => true,
        ],
        [
            'label' => 'This Month',
            'value' => $thisMonthLeads,
            'icon' => 'fas fa-calendar',
            'color' => 'bg-amber-100 text-amber-800',
            'visible' => true,
        ],
        [
            'label' => 'Unassigned',
            'value' => $unassignedLeads,
            'icon' => 'fas fa-exclamation-triangle',
            'color' => 'bg-rose-100 text-rose-800',
            'visible' => $unassignedLeads > 0 && !$isRegularUser,
        ],
        [
            'label' => 'With Credit Cards',
            'value' => $withCards,
            'icon' => 'fas fa-credit-card',
            'color' => 'bg-cyan-100 text-cyan-800',
            'visible' => $withCards > 0,
        ],
    ];
}

$cards = array_values(array_filter($cards, fn($c) => $c['visible']));
    @endphp

    <div class="space-y-8 animate-on-load">
        {{-- Header --}}
        <div
            class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl shadow-2xl rounded-2xl p-8 border border-gray-200/50 dark:border-gray-700/50">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">
                        Welcome back, {{ $user->name }}! 👋
                    </h2>
                    <p class="text-gray-600 dark:text-gray-400 text-lg">
                        @if ($isThatSubmittedUser)
                            Here's an overview of all That Submitted leads and their status.
                        @elseif ($isMaxOutUser)
                            Here's an overview of all Max Out leads and their status.
                        @elseif ($isRegularUser)
                            Here's an overview of your assigned leads.
                        @else
                            Here's what's happening with all leads today.
                        @endif
                    </p>
                </div>
            </div>
        </div>

        {{-- Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($cards as $card)
                <div
                    class="rounded-2xl p-6 shadow-2xl border border-gray-200/50 dark:border-gray-700/50 bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">{{ $card['label'] }}</p>
                            <p class="text-3xl font-bold text-gray-900 dark:text-white">
                                {{ is_numeric($card['value']) ? number_format((float) $card['value']) : $card['value'] }}
                            </p>
                        </div>
                        <div
                            class="w-12 h-12 bg-gradient-to-br from-primary-500 to-primary-600 rounded-xl flex items-center justify-center shadow-lg">
                            <i class="{{ $card['icon'] }} text-white text-2xl"></i>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Main Content Grid (hide status/category for max_out and that_submitted) --}}
        @if (!$isMaxOutUser && !$isThatSubmittedUser)
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Leads by Status --}}
                <div class="bg-white/80 dark:bg-gray-800/80 rounded-2xl shadow-2xl border">
                    <div class="p-6 border-b">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Leads by Status</h3>
                    </div>
                    <div class="p-6">
                        <ul class="space-y-4">
                            @forelse ($leadsByStatus as $row)
                                <li class="flex items-center justify-between py-2">
                                    <span
                                        class="capitalize text-gray-700 dark:text-gray-300">{{ $row->status ?? 'Unknown' }}</span>
                                    <span
                                        class="font-medium text-gray-900 dark:text-white">{{ number_format((int) $row->count) }}</span>
                                </li>
                            @empty
                                <li class="text-gray-500 dark:text-gray-400 py-4 text-center">No status data available</li>
                            @endforelse
                        </ul>
                    </div>
                </div>

                {{-- Top Categories --}}
                <div class="bg-white/80 dark:bg-gray-800/80 rounded-2xl shadow-2xl border">
                    <div class="p-6 border-b">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Top Categories</h3>
                    </div>
                    <div class="p-6">
                        <ul class="space-y-4">
                            @forelse ($topCategories as $category)
                                <li class="flex items-center justify-between py-2">
                                    <span class="text-gray-700 dark:text-gray-300">{{ $category->category_name }}</span>
                                    <span
                                        class="font-medium text-gray-900 dark:text-white">{{ number_format((int) $category->count) }}</span>
                                </li>
                            @empty
                                <li class="text-gray-500 dark:text-gray-400 py-4 text-center">No category data available
                                </li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        {{-- Chart --}}
        <div class="bg-white/80 dark:bg-gray-800/80 rounded-2xl shadow-2xl border">
            <div class="p-6 border-b">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    @if ($isThatSubmittedUser)
                        That Submitted Leads Monthly Trends
                    @elseif ($isMaxOutUser)
                        Max Out Leads Monthly Trends
                    @elseif ($isRegularUser)
                        Your Monthly Lead Trends
                    @else
                        Monthly Lead Trends
                    @endif
                </h3>
            </div>
            <div class="p-6">
                <canvas id="leadsPerMonthChart" height="100"></canvas>
            </div>
        </div>

        {{-- Recent Leads --}}
        <div class="bg-white/80 dark:bg-gray-800/80 rounded-2xl shadow-2xl border overflow-hidden">
            <div class="p-6 border-b">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    @if ($isThatSubmittedUser)
                        Recent That Submitted Leads
                    @elseif ($isMaxOutUser)
                        Recent Max Out Leads
                    @elseif ($isRegularUser)
                        Your Recent Leads
                    @else
                        Recent Leads
                    @endif
                </h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50/50 dark:bg-gray-800/50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400">Location
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400">Category
                            </th>
                            @if (!$isRegularUser && !$isMaxOutUser && !$isThatSubmittedUser)
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400">
                                    Assigned To</th>
                            @endif
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400">Created
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400">Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white/50 dark:bg-gray-900/50 divide-y">
                        @forelse ($recentLeads as $lead)
                            @php
                                $fullName = trim(
                                    collect([$lead->first_name, $lead->surname, $lead->gen_code])
                                        ->filter()
                                        ->implode(' '),
                                );
                                $assignedName = class_exists($UserModel)
                                    ? optional($UserModel::find($lead->assigned_to))->name
                                    : null;
                                $categoryName = class_exists($CategoryModel)
                                    ? optional($CategoryModel::find($lead->category_id))->name
                                    : null;
                                $cityState = trim(
                                    collect([$lead->city, $lead->state_abbreviation])
                                        ->filter()
                                        ->implode(', '),
                                );
                            @endphp
                            <tr>
                                <td class="px-6 py-4 text-sm">{{ $fullName ?: '—' }}</td>
                                <td class="px-6 py-4 text-sm">{{ $cityState ?: '—' }}</td>
                                <td class="px-6 py-4 text-sm">{{ $lead->status ?? '—' }}</td>
                                <td class="px-6 py-4 text-sm">{{ $categoryName ?? 'Uncategorized' }}</td>
                                @if (!$isRegularUser && !$isMaxOutUser && !$isThatSubmittedUser)
                                    <td class="px-6 py-4 text-sm">{{ $assignedName ?? 'Unassigned' }}</td>
                                @endif
                                <td class="px-6 py-4 text-sm">{{ optional($lead->created_at)->diffForHumans() ?? '—' }}
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <a href="{{ route('leads.show', $lead) }}"
                                        class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300 mr-2">
                                        View
                                    </a>
                                    @if ($user->can('edit', $lead))
                                        <a href="{{ route('leads.edit', $lead) }}"
                                            class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300">
                                            Edit
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $isRegularUser || $isMaxOutUser || $isThatSubmittedUser ? 5 : 6 }}"
                                    class="px-6 py-8 text-center text-sm text-gray-500">No recent leads found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const monthlyRaw = @json($monthlyLeadCounts);
            const labels = (monthlyRaw || []).map(r => {
                const [year, month] = r.ym.split('-');
                return new Date(year, month - 1).toLocaleDateString('en-US', {
                    month: 'short',
                    year: 'numeric'
                });
            });
            const data = (monthlyRaw || []).map(r => Number(r.count || 0));

            const ctx = document.getElementById('leadsPerMonthChart');
            if (ctx) {
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels,
                        datasets: [{
                            label: 'Leads',
                            data,
                            borderColor: 'rgba(79, 70, 229, 0.8)',
                            backgroundColor: 'rgba(79, 70, 229, 0.1)',
                            borderWidth: 2,
                            fill: true,
                            tension: 0.3
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false
                    }
                });
            }
        });
    </script>
@endpush
