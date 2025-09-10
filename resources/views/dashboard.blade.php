{{-- resources/views/dashboard_updated.blade.php --}}
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

        // Leads updated today (assigned to current user)
        $todayLeadsCount = \App\Models\Lead::where(function ($query) use ($userId) {
            $query->where('assigned_to', $userId)->orWhere('super_agent_id', $userId)->orWhere('closer_id', $userId);
        })
            ->whereDate('updated_at', Carbon::today())
            ->count();

        $isRegularUser = $user->role === 'user';

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

// Base query with role-based restrictions
$leadQuery = class_exists($LeadModel) ? $LeadModel::query() : null;

// Regular users can only see their own leads
if ($isRegularUser && $leadQuery) {
    $leadQuery->where(function ($query) use ($user) {
        $query
            ->where('assigned_to', $user->id)
            ->orWhere('super_agent_id', $user->id)
            ->orWhere('closer_id', $user->id);
    });
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

// Unassigned means: nobody in any assignment seat
$unassignedLeads = $leadQuery
    ? $leadQuery
        ->clone()
        ->whereNull('assigned_to')
        ->whereNull('super_agent_id')
        ->whereNull('closer_id')
        ->count()
    : 0;

// Optional/conditional metrics
$avgFico = $leadQuery ? (float) $leadQuery->clone()->whereNotNull('fico')->avg('fico') : null;

$withCards = $leadQuery
    ? $leadQuery->clone()->whereNotNull('cards_json')->whereRaw('JSON_LENGTH(cards_json) > 0')->count()
    : 0;

$withBalance = $leadQuery
    ? (int) $leadQuery->clone()->whereNotNull('balance')->where('balance', '>', 0)->count()
    : 0;

// Leads by status
$leadsByStatus = $leadQuery
    ? $leadQuery
        ->clone()
        ->select('status', DB::raw('COUNT(*) as count'))
        ->groupBy('status')
        ->orderByDesc('count')
        ->get()
    : collect();

// Monthly lead counts (last 12 months)
$monthlyLeadCounts = $leadQuery
    ? $leadQuery
        ->clone()
        ->select(DB::raw("DATE_FORMAT(created_at, '%Y-%m') as ym"), DB::raw('COUNT(*) as count'))
        ->where('created_at', '>=', now()->subMonths(11)->startOfMonth())
        ->groupBy(DB::raw("DATE_FORMAT(created_at, '%Y-%m')"))
        ->orderBy('ym')
        ->get()
    : collect();

// Top categories
if ($leadQuery && class_exists($CategoryModel)) {
    $topCategories = $leadQuery
        ->clone()
        ->select('category_id', DB::raw('COUNT(*) as count'))
        ->groupBy('category_id')
        ->orderByDesc('count')
        ->limit(5)
        ->get()
        ->map(function ($row) use ($CategoryModel) {
            $row->category_name = optional($CategoryModel::find($row->category_id))->name ?? 'Uncategorized';
            return $row;
        });
} else {
    $topCategories = collect();
}

// Owner performance (by assigned_to) - only for admins
$ownersPerformance = collect();
if (!$isRegularUser && $leadQuery) {
    $ownersPerformance = $leadQuery
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
        });
}

// Recent leads
$recentLeads = $leadQuery ? $leadQuery->clone()->latest()->limit(10)->get() : collect();

$submittedLeadsAll = class_exists($LeadModel)
    ? $LeadModel
        ::query()
        ->where('status', 'Submitted')
        ->where(function ($q) use ($userId) {
            $q->where('assigned_to', $userId)
                ->orWhere('super_agent_id', $userId)
                ->orWhere('closer_id', $userId);
        })
        ->count()
    : 0;

// Dynamic cards
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
        'value' => $todayLeadsCount,
        'icon' => 'fas fa-sun',
        'color' => 'bg-emerald-100 text-emerald-800',
        'visible' => true,
    ],
    [
        'label' => 'Submitted (All Time)',
        'value' => $submittedLeadsAll,
        'icon' => 'fas fa-paper-plane',
        'color' => 'bg-fuchsia-100 text-fuchsia-800',
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
        'label' => 'Avg FICO',
        'value' => $avgFico ? number_format($avgFico, 0) : 0,
        'icon' => 'fas fa-chart-pie',
        'color' => 'bg-blue-100 text-blue-800',
        'visible' => $avgFico !== null,
    ],
    [
        'label' => 'With Credit Cards',
        'value' => $withCards,
        'icon' => 'fas fa-credit-card',
        'color' => 'bg-cyan-100 text-cyan-800',
        'visible' => $withCards > 0,
    ],
    [
        'label' => 'With Balance',
        'value' => $withBalance,
        'icon' => 'fas fa-dollar-sign',
        'color' => 'bg-lime-100 text-lime-800',
        'visible' => $withBalance > 0,
    ],
];

$cards = array_values(array_filter($cards, fn($c) => $c['visible']));
    @endphp

    <div class="space-y-8 animate-on-load">
        {{-- Header --}}
        <div
            class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl shadow-2xl rounded-2xl p-8 border border-gray-200/50 dark:border-gray-700/50">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">
                        Welcome back, {{ auth()->user()->name }}! 👋
                    </h2>
                    <p class="text-gray-600 dark:text-gray-400 text-lg">
                        @if ($isRegularUser)
                            Here's an overview of your assigned leads.
                        @else
                            Here's what's happening with all leads today.
                        @endif
                    </p>
                </div>
            </div>

            {{-- Auto-checkout banner (hidden by default, toggled by JS) --}}
            <div id="autoCheckoutBanner"
                class="hidden mt-4 rounded-lg border border-amber-200/70 bg-amber-50/70 dark:bg-amber-900/20 dark:border-amber-800/40 px-4 py-3">
                <div class="flex items-start gap-3">
                    <i class="fas fa-info-circle text-amber-600 dark:text-amber-300 mt-0.5"></i>
                    <div class="flex-1">
                        <p class="text-amber-900 dark:text-amber-100 font-medium">You were automatically checked out.</p>
                        <p class="text-amber-700 dark:text-amber-200 text-sm">Close/reload detected — your session was
                            safely ended.</p>
                    </div>
                    <button type="button" id="autoCheckoutBannerClose"
                        class="text-amber-700 dark:text-amber-200 hover:opacity-75 transition">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>

            {{-- Attendance (info-only; no manual actions) --}}
            @if ($isRegularUser)
                <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700" data-attendance-container>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Attendance</h3>

                    <div class="flex flex-col sm:flex-row gap-4">
                        @if ($isCheckedIn && !$isCheckedOut)
                            <div class="flex-1 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-3 h-3 rounded-full bg-green-500 animate-pulse"></div>
                                    <span class="text-green-700 dark:text-green-400 font-medium">
                                        Checked in since
                                        {{ $todayAttendance->check_in->setTimezone('Asia/Karachi')->format('g:i A') }}.
                                        You will be checked out automatically when you logout or close your last tab.
                                    </span>
                                </div>
                                <a href="{{ route('attendance.history') }}"
                                    class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors flex items-center justify-center gap-2">
                                    <i class="fas fa-history"></i> History
                                </a>
                            </div>
                        @elseif ($isCheckedOut)
                            <div class="flex-1">
                                <div
                                    class="p-4 rounded-xl border border-blue-200/60 dark:border-blue-800/40 bg-blue-50/60 dark:bg-blue-900/20">
                                    <div
                                        class="flex items-start sm:items-center justify-between gap-4 flex-col sm:flex-row">
                                        <div class="flex items-start gap-3">
                                            <i
                                                class="fas fa-clipboard-check text-blue-600 dark:text-blue-400 text-xl mt-1"></i>
                                            <div>
                                                <p class="text-blue-900 dark:text-blue-100 font-semibold">You're checked out
                                                    for today.</p>
                                                <p class="text-blue-700 dark:text-blue-300 text-sm mt-1">
                                                    Checked in:
                                                    {{ $todayAttendance->check_in->setTimezone('Asia/Karachi')->format('g:i A') }}
                                                    —
                                                    Checked out:
                                                    {{ $todayAttendance->check_out->setTimezone('Asia/Karachi')->format('g:i A') }}
                                                    —
                                                    Hours worked: <span
                                                        class="font-medium">{{ number_format($todayAttendance->hours_worked, 2) }}</span>
                                                </p>
                                                <p class="text-gray-600 dark:text-gray-400 text-sm mt-1">
                                                    You’ll be checked in automatically the next time you log in.
                                                </p>
                                            </div>
                                        </div>
                                        <a href="{{ route('attendance.history') }}"
                                            class="w-full sm:w-auto px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors flex items-center justify-center gap-2">
                                            <i class="fas fa-history"></i> History
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @else
                            {{-- No check-in yet (first load after login) --}}
                            <div class="flex-1 flex items-start gap-3">
                                <i class="fas fa-circle-info text-gray-500 dark:text-gray-400 mt-1"></i>
                                <p class="text-gray-700 dark:text-gray-300">
                                    You’ll be checked in automatically now. Closing your last tab or logging out will check
                                    you out automatically.
                                </p>
                            </div>
                            <a href="{{ route('attendance.history') }}"
                                class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors flex items-center justify-center gap-2">
                                <i class="fas fa-history"></i> History
                            </a>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        {{-- Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse ($cards as $card)
                <div
                    class="rounded-2xl p-6 shadow-2xl border border-gray-200/50 dark:border-gray-700/50 bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl animate-on-load">
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
            @empty
                <div class="col-span-full text-center py-8 text-gray-500 dark:text-gray-400">No metrics to display.</div>
            @endforelse
        </div>

        {{-- Main Content Grid --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Leads by Status --}}
            <div
                class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl shadow-2xl rounded-2xl border border-gray-200/50 dark:border-gray-700/50">
                <div class="p-6 border-b border-gray-200/50 dark:border-gray-700/50">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Leads by Status</h3>
                </div>
                <div class="p-6">
                    <ul class="space-y-4">
                        @forelse ($leadsByStatus as $row)
                            <li class="flex items-center justify-between py-2">
                                <div class="flex items-center">
                                    <span
                                        class="w-3 h-3 rounded-full bg-gradient-to-br from-primary-500 to-primary-600 mr-2"></span>
                                    <span
                                        class="capitalize text-gray-700 dark:text-gray-300">{{ $row->status ?? 'Unknown' }}</span>
                                </div>
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
            <div
                class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl shadow-2xl rounded-2xl border border-gray-200/50 dark:border-gray-700/50">
                <div class="p-6 border-b border-gray-200/50 dark:border-gray-700/50">
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
                            <li class="text-gray-500 dark:text-gray-400 py-4 text-center">No category data available</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>

        {{-- Owner Performance Section (Only for admins) --}}
        @if (!$isRegularUser && $ownersPerformance->isNotEmpty())
            <div
                class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl shadow-2xl rounded-2xl border border-gray-200/50 dark:border-gray-700/50">
                <div class="p-6 border-b border-gray-200/50 dark:border-gray-700/50">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Top Assignees</h3>
                </div>
                <div class="p-6">
                    <ul class="space-y-4">
                        @forelse ($ownersPerformance as $row)
                            <li class="flex items-center justify-between py-2">
                                <span class="text-gray-700 dark:text-gray-300">{{ $row->owner }}</span>
                                <span
                                    class="font-medium text-gray-900 dark:text-white">{{ number_format((int) $row->count) }}</span>
                            </li>
                        @empty
                            <li class="text-gray-500 dark:text-gray-400 py-4 text-center">No assignment data available</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        @endif

        {{-- Chart Section --}}
        <div
            class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl shadow-2xl rounded-2xl border border-gray-200/50 dark:border-gray-700/50">
            <div class="p-6 border-b border-gray-200/50 dark:border-gray-700/50">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    @if ($isRegularUser)
                        Your Monthly Lead Trends
                    @else
                        Monthly Lead Trends
                    @endif
                </h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Last 12 months</p>
            </div>
            <div class="p-6">
                <canvas id="leadsPerMonthChart" height="100"></canvas>
            </div>
        </div>

        {{-- Recent Leads Table --}}
        <div
            class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl shadow-2xl rounded-2xl border border-gray-200/50 dark:border-gray-700/50 overflow-hidden">
            <div class="p-6 border-b border-gray-200/50 dark:border-gray-700/50">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    @if ($isRegularUser)
                        Your Recent Leads
                    @else
                        Recent Leads
                    @endif
                </h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Most recently added leads</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50/50 dark:bg-gray-800/50">
                        <tr>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Name</th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Location</th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Status</th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Category</th>
                            @if (!$isRegularUser)
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Assigned To</th>
                            @endif
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Created</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white/50 dark:bg-gray-900/50 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse ($recentLeads as $lead)
                            @php
                                $fullName = trim(
                                    collect([$lead->first_name, $lead->middle_initial, $lead->surname, $lead->gen_code])
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
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/50 transition-colors duration-150">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $fullName !== '' ? $fullName : '—' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ $cityState !== '' ? $cityState : '—' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm capitalize">
                                    <span
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                        @if ($lead->status === 'Deal') bg-success-100 text-success-800 dark:bg-success-800/20 dark:text-success-300
                                        @elseif($lead->status === 'Call Back') bg-warning-100 text-warning-800 dark:bg-warning-800/20 dark:text-warning-300
                                        @else bg-gray-100 text-gray-800 dark:bg-gray-800/20 dark:text-gray-300 @endif">
                                        {{ $lead->status ?? '—' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ $categoryName ?? 'Uncategorized' }}</td>
                                @if (!$isRegularUser)
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ $assignedName ?? 'Unassigned' }}</td>
                                @endif
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ optional($lead->created_at)->diffForHumans() ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $isRegularUser ? 5 : 6 }}"
                                    class="px-6 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                    No recent leads found.
                                </td>
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
        // Chart.js (unchanged)
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
                            backgroundColor: 'rgba(79, 70, 229, 0.05)',
                            borderColor: 'rgba(79, 70, 229, 0.8)',
                            borderWidth: 2,
                            pointBackgroundColor: 'rgba(79, 70, 229, 1)',
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            fill: true,
                            tension: 0.3,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                mode: 'index',
                                intersect: false
                            }
                        },
                        scales: {
                            x: {
                                grid: {
                                    display: false
                                },
                                ticks: {
                                    color: '#6B7280'
                                }
                            },
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    precision: 0,
                                    color: '#6B7280'
                                },
                                grid: {
                                    color: 'rgba(0, 0, 0, 0.05)'
                                }
                            }
                        },
                        interaction: {
                            mode: 'nearest',
                            axis: 'x',
                            intersect: false
                        }
                    }
                });
            }
        });
    </script>

    {{-- Session & attendance handler --}}
    {{-- <script>
        (function() {
            if (window.__sessBound) return;
            window.__sessBound = true;

            // Routes
            const PING_URL = "{{ route('attendance.ping') }}"; // CSRF-protected
            const BEACON_URL = "{{ route('logout.beacon') }}"; // CSRF-exempt

            // CSRF
            const CSRF = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

            // --- Heartbeat (keep last_heartbeat_at fresh) ---
            function ping() {
                if (document.hidden) return;
                fetch(PING_URL, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'X-CSRF-TOKEN': CSRF,
                        'Accept': 'application/json'
                    }
                }).catch(() => {});
            }
            const HEARTBEAT_MS = 60_000;
            let hb = setInterval(ping, HEARTBEAT_MS);
            document.addEventListener('visibilitychange', () => {
                if (!document.hidden) ping();
            }, {
                passive: true
            });
            window.addEventListener('focus', ping, {
                passive: true
            });

            // --- Multi-tab tracking ---
            const TAB_KEY = 'app_active_tab_count';
            const getCount = () => parseInt(localStorage.getItem(TAB_KEY) || '0', 10) || 0;
            const setCount = (n) => localStorage.setItem(TAB_KEY, String(Math.max(0, n)));

            // increment on (re)show
            function markOpen() {
                setCount(getCount() + 1);
            }
            // decrement and if last → beacon logout
            let closing = false;

            function markClose() {
                if (closing) return;
                closing = true;
                setCount(getCount() - 1);
                const remaining = getCount();

                // small delay to distinguish reload from real close; cancelled on pageshow
                setTimeout(() => {
                    if (remaining <= 0) {
                        // flag for banner (use localStorage so it survives full browser close)
                        try {
                            localStorage.setItem('autoCheckedOut', String(Date.now()));
                        } catch (_) {}

                        // Logout + checkout on server
                        if (navigator.sendBeacon) {
                            const blob = new Blob([JSON.stringify({
                                reason: 'tab-or-browser-close'
                            })], {
                                type: 'application/json'
                            });
                            navigator.sendBeacon(BEACON_URL, blob);
                        } else {
                            fetch(BEACON_URL, {
                                method: 'POST',
                                credentials: 'same-origin',
                                keepalive: true,
                                headers: {
                                    'Accept': 'application/json'
                                }
                            }).catch(() => {});
                        }
                    }
                    clearInterval(hb);
                }, 900);
            }

            // hook lifecycle
            window.addEventListener('pageshow', function(e) {
                markOpen();
                // clear "closing" if we reloaded
                closing = false;
            });
            window.addEventListener('pagehide', markClose);
            window.addEventListener('beforeunload', markClose);

            // Optional: show the "auto checkout" banner if we detect a recent auto-close
            document.addEventListener('DOMContentLoaded', function() {
                try {
                    const ts = parseInt(localStorage.getItem('autoCheckedOut') || '0', 10);
                    // show if within last 10 minutes
                    if (ts && (Date.now() - ts) < 10 * 60 * 1000) {
                        const banner = document.getElementById('autoCheckoutBanner');
                        if (banner) banner.classList.remove('hidden');
                        const closeBtn = document.getElementById('autoCheckoutBannerClose');
                        if (closeBtn) closeBtn.addEventListener('click', () => banner.classList.add('hidden'));
                        // clear it so we don’t show forever
                        localStorage.removeItem('autoCheckedOut');
                    }
                } catch (_) {}
            });
        })();
    </script> --}}
@endpush
