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
        $isThatSubmittedUser = $user->role === 'death_submitted';
        $isLeadManager = $user->role === 'lead_manager';

        // Pakistan timezone anchors
        $pakistanNow = now()->setTimezone('Asia/Karachi');
        $todayStartLocal = $pakistanNow->copy()->startOfDay();
        $todayEndLocal = $pakistanNow->copy()->endOfDay();
        $monthStartLocal = $pakistanNow->copy()->startOfMonth();

        // Convert to UTC
        $todayStartUtc = $todayStartLocal->copy()->setTimezone('UTC');
        $todayEndUtc = $todayEndLocal->copy()->setTimezone('UTC');
        $monthStartUtc = $monthStartLocal->copy()->setTimezone('UTC');

        // Attendance logic
        $todayAttendance = UserAttendance::where('user_id', $user->id)
            ->whereBetween('check_in', [$todayStartUtc, $todayEndUtc])
            ->latest('check_in')
            ->first();

        $isCheckedIn = $todayAttendance && $todayAttendance->status === 'in';
        $isCheckedOut = $todayAttendance && $todayAttendance->status === 'out';

        // Base query
        $leadQuery = class_exists($LeadModel) ? $LeadModel::query() : null;

        if ($leadQuery) {
            if ($isThatSubmittedUser) {
                $leadQuery->where('status', 'Death Submitted');
            } elseif ($isMaxOutUser) {
                $leadQuery->where('status', 'Max Out');
            } elseif ($isRegularUser || $isLeadManager) {
                // Use the shared visibility logic from the model if possible, 
                // or replicate forUser() here for metrics consistency.
                $leadQuery->where(function ($query) use ($userId) {
                    $query->where('assigned_to', $userId)
                        ->orWhere('super_agent_id', $userId)
                        ->orWhere('closer_id', $userId)
                        ->orWhere('created_by', $userId)
                        ->orWhereHas('users', fn($q) => $q->where('users.id', $userId));
                });
            }
        }

        // Metrics
        $totalLeads = $leadQuery ? $leadQuery->clone()->count() : 0;
        $newLeadsToday = $leadQuery
            ? $leadQuery->clone()->whereBetween('created_at', [$todayStartUtc, $todayEndUtc])->count()
            : 0;
        $thisMonthLeads = $leadQuery ? $leadQuery->clone()->where('created_at', '>=', $monthStartUtc)->count() : 0;

        $successfullySubmitted = $leadQuery
            ? $leadQuery->clone()->whereIn('status', ['Submitted', 'Deal', 'Paid Off'])->count()
            : 0;

        $callbacksCount = \App\Models\Callback::where('user_id', $userId)
            ->where('status', 'pending')
            ->count();

        $openIssuesCount = \App\Models\LeadIssue::where('status', 'open')
            ->when($isRegularUser, function ($q) use ($userId) {
                return $q->where('reporter_id', $userId);
            })
            ->count();

        // Monthly trend for chart
        $monthlyLeadCounts = $leadQuery
            ? $leadQuery->clone()
                ->select(DB::raw("DATE_FORMAT(created_at, '%Y-%m') as ym"), DB::raw('COUNT(*) as count'))
                ->where('created_at', '>=', now()->subMonths(11)->startOfMonth())
                ->groupBy(DB::raw("DATE_FORMAT(created_at, '%Y-%m')"))
                ->orderBy('ym')
                ->get()
            : collect();

        // Status distribution for chart
        $leadsByStatus = ($leadQuery ? $leadQuery->clone()->select('status', DB::raw('COUNT(*) as count'))->groupBy('status')->get() : collect());

        // Recent leads
        $recentLeads = $leadQuery ? $leadQuery->clone()->latest()->limit(8)->get() : collect();
    @endphp

        <div class="space-y-6 animate-on-load">
            {{-- Welcome Island --}}
            <div class="card-premium p-8 relative overflow-hidden">
                <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <div>
                        <h1 class="text-4xl font-extrabold tracking-tight text-slate-900 mb-2">
                            Welcome back, <span class="gradient-text">{{ $user->name }}</span>
                        </h1>
                        <p class="text-slate-400 text-lg font-medium">
                            @if ($isThatSubmittedUser) Monitoring all Death Submitted leads.
                            @elseif ($isMaxOutUser) Global overview of Max Out leads.
                            @else Here's a snapshot of your leads ecosystem today. @endif
                        </p>
                    </div>
                    <div class="flex items-center gap-4">
                        @if($isRegularUser)
                            <div class="flex items-center gap-3 bg-white/5 p-2 rounded-2xl border border-gray-200/50">
                                <form action="{{ route('attendance.checkIn') }}" method="POST">
                                    @csrf
                                    <button type="submit" @disabled($isCheckedIn) class="px-4 py-2 rounded-xl text-xs font-bold transition-all {{ $isCheckedIn ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/20' : 'bg-white/5 hover:bg-white/10 text-slate-300 border border-gray-200/50' }}">
                                        <i class="fas fa-sign-in-alt mr-1"></i> Check In
                                    </button>
                                </form>
                                <form action="{{ route('attendance.checkout.beacon') }}" method="POST">
                                    @csrf
                                    <button type="submit" @disabled(!$isCheckedIn) class="px-4 py-2 rounded-xl text-xs font-bold transition-all {{ $isCheckedOut ? 'bg-rose-500/20 text-rose-400 border border-rose-500/20' : 'bg-white/5 hover:bg-white/10 text-slate-300 border border-gray-200/50' }}">
                                        <i class="fas fa-sign-out-alt mr-1"></i> Check Out
                                    </button>
                                </form>
                            </div>
                        @endif
                        <a href="{{ route('leads.index') }}" class="px-8 py-3 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl font-bold transition-all shadow-lg shadow-indigo-600/20">
                            Launch Workspace
                        </a>
                    </div>
                </div>
                <div class="absolute top-[-10%] right-[-10%] w-[40%] h-[120%] bg-indigo-500/10 blur-[100px] rounded-full"></div>
            </div>

            {{-- Bento Grid Metrics --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="card-premium p-6 flex flex-col justify-between group">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-indigo-500/20 rounded-2xl flex items-center justify-center text-indigo-400 group-hover:scale-110 transition-transform">
                            <i class="fas fa-users text-2xl"></i>
                        </div>
                        <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Total Reach</span>
                    </div>
                    <div>
                        <h3 class="text-3xl font-black text-slate-900 mb-1">{{ number_format($totalLeads) }}</h3>
                        <p class="text-xs text-indigo-400 font-bold flex items-center gap-1">
                            <i class="fas fa-arrow-up"></i> +{{ $newLeadsToday }} new targets today
                        </p>
                    </div>
                </div>

                <div class="card-premium p-6 flex flex-col justify-between group">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-emerald-500/20 rounded-2xl flex items-center justify-center text-emerald-400 group-hover:scale-110 transition-transform">
                            <i class="fas fa-check-double text-2xl"></i>
                        </div>
                        <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Successful Deals</span>
                    </div>
                    <div>
                        <h3 class="text-3xl font-black text-slate-900 mb-1">{{ number_format($successfullySubmitted) }}</h3>
                        <p class="text-xs text-emerald-400 font-bold flex items-center gap-1">
                            <i class="fas fa-chart-line"></i> Optimized conversion
                        </p>
                    </div>
                </div>

                <div class="card-premium p-6 flex flex-col justify-between group">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-amber-500/20 rounded-2xl flex items-center justify-center text-amber-400 group-hover:scale-110 transition-transform">
                            <i class="fas fa-clock text-2xl"></i>
                        </div>
                        <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Active Callbacks</span>
                    </div>
                    <div>
                        <h3 class="text-3xl font-black text-slate-900 mb-1">{{ number_format($callbacksCount) }}</h3>
                        <p class="text-xs text-amber-400 font-bold">Protocol action required</p>
                    </div>
                </div>

                <div class="card-premium p-6 flex flex-col justify-between group">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-rose-500/20 rounded-2xl flex items-center justify-center text-rose-400 group-hover:scale-110 transition-transform">
                            <i class="fas fa-exclamation-triangle text-2xl"></i>
                        </div>
                        <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Open Reports</span>
                    </div>
                    <div>
                        <h3 class="text-3xl font-black text-slate-900 mb-1">{{ number_format($openIssuesCount) }}</h3>
                        <p class="text-xs text-rose-400 font-bold">Critical review pending</p>
                    </div>
                </div>
            </div>

            {{-- Intelligence Stream & Visual Analytics --}}
            <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
                {{-- Activity List --}}
                <div class="col-span-12 md:col-span-8 card-premium overflow-hidden">
                    <div class="px-8 py-6 border-b border-gray-200/50 flex items-center justify-between bg-white/[0.02]">
                        <div>
                            <h3 class="text-xl font-bold text-slate-900">Intelligence Stream</h3>
                            <p class="text-xs text-slate-500 uppercase font-bold tracking-widest mt-1">Recent Lead Dynamics</p>
                        </div>
                        <a href="{{ route('leads.index') }}" class="text-xs font-bold text-indigo-700 hover:text-indigo-800 flex items-center gap-2 transition-all group">
                            View Ecosystem <i class="fas fa-arrow-right group-hover:translate-x-1 transition-transform"></i>
                        </a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="bg-gray-100 text-[10px] font-black uppercase tracking-[0.2em] text-slate-700">
                                    <th class="px-8 py-4">Lead Information</th>
                                    <th class="px-8 py-4">Current Status</th>
                                    <th class="px-8 py-4">Assignment</th>
                                    <th class="px-8 py-4 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200/50">
                                @forelse($recentLeads as $lead)
                                    <tr class="hover:bg-gray-50 transition-all group">
                                        <td class="px-8 py-5">
                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-500/20 to-purple-500/20 flex items-center justify-center font-bold text-indigo-400 border border-indigo-500/20">
                                                    {{ strtoupper(substr($lead->first_name, 0, 1)) }}
                                                </div>
                                                <div>
                                                    <p class="text-sm font-bold text-slate-900 group-hover:text-indigo-400 transition-colors">
                                                        {{ $lead->first_name }} {{ $lead->surname }}
                                                    </p>
                                                    <p class="text-[10px] text-slate-500 capitalize">{{ $lead->street }}, {{ $lead->city }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-8 py-5">
                                            @php
                                                $statusColors = [
                                                    'submitted' => 'emerald',
                                                    'call back' => 'amber',
                                                    'new lead' => 'indigo',
                                                    'death submitted' => 'rose',
                                                    'max out' => 'orange',
                                                    'deal' => 'emerald',
                                                ];
                                                $color = $statusColors[strtolower($lead->status ?? '')] ?? 'slate';
                                            @endphp
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-wider bg-{{ $color }}-500/10 text-{{ $color }}-700 border border-{{ $color }}-500/20">
                                                {{ $lead->status }}
                                            </span>
                                        </td>
                                        <td class="px-8 py-5">
                                            <div class="flex items-center gap-2">
                                                <i class="fas fa-user-circle text-slate-600 text-sm"></i>
                                                <span class="text-[11px] font-bold text-slate-800">{{ $lead->assignee?->name ?? 'Unassigned' }}</span>
                                            </div>
                                        </td>
                                        <td class="px-8 py-5 text-right">
                                            <div class="flex items-center justify-end gap-2">
                                                <a href="{{ route('leads.show', $lead) }}"
                                                    class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg text-[10px] text-slate-900 font-black uppercase tracking-widest transition-all">
                                                    <i class="fas fa-external-link-alt text-indigo-600"></i> Open
                                                </a>
                                                @if (auth()->user()->isAdmin() || auth()->user()->isLeadManager() || $lead->assigned_to === auth()->id())
                                                    <a href="{{ route('leads.edit', $lead) }}"
                                                        class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 hover:bg-indigo-600 hover:text-white rounded-lg text-[10px] text-slate-900 font-black uppercase tracking-widest transition-all border border-gray-200 hover:border-indigo-500">
                                                        <i class="fas fa-edit"></i> Edit
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-8 py-20 text-center">
                                            <p class="text-slate-500 font-bold uppercase tracking-widest text-xs">No Recent Dynamics Detected</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Analytics Islands --}}
                <div class="col-span-12 md:col-span-4 space-y-6">
                    {{-- Status Distribution --}}
                    <div class="card-premium p-8 h-[350px] flex flex-col">
                        <div class="mb-6">
                            <h3 class="text-xl font-bold text-slate-900">Status Distribution</h3>
                            <p class="text-xs text-slate-500 uppercase font-bold tracking-widest mt-1">Magnitude breakdown</p>
                        </div>
                        <div class="flex-1 min-h-0 flex items-center justify-center">
                            <canvas id="leadsByStatusChart"></canvas>
                        </div>
                    </div>

                    {{-- Performance Trends --}}
                    <div class="card-premium p-8 h-[350px] flex flex-col">
                        <div class="mb-6">
                            <h3 class="text-xl font-bold text-white">Engagement Trends</h3>
                            <p class="text-xs text-slate-500 uppercase font-bold tracking-widest mt-1">Annual activity scale</p>
                        </div>
                        <div class="flex-1 min-h-0">
                            <canvas id="monthlyTrendsChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Trend Chart
            const monthlyRaw = @json($monthlyLeadCounts);
            const trendCtx = document.getElementById('monthlyTrendsChart');
            if (trendCtx) {
                new Chart(trendCtx, {
                    type: 'line',
                    data: {
                        labels: monthlyRaw.map(r => {
                            const [y, m] = r.ym.split('-');
                            return new Date(y, m-1).toLocaleDateString('en-US', {month: 'short'});
                        }),
                        datasets: [{
                            label: 'Magnitude',
                            data: monthlyRaw.map(r => r.count),
                            borderColor: '#818cf8',
                            backgroundColor: 'rgba(129, 140, 248, 0.1)',
                            borderWidth: 3,
                            fill: true,
                            tension: 0.4,
                            pointRadius: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            y: { display: false },
                            x: {
                                grid: { display: false },
                                ticks: { color: '#64748b', font: { size: 10, weight: 'bold' } }
                            }
                        }
                    }
                });
            }

            // Status Pie Chart
            const statusRaw = @json($leadsByStatus);
            const statusCtx = document.getElementById('leadsByStatusChart');
            if (statusCtx) {
                new Chart(statusCtx, {
                    type: 'doughnut',
                    data: {
                        labels: statusRaw.map(r => r.status),
                        datasets: [{
                            data: statusRaw.map(r => r.count),
                            backgroundColor: [
                                '#6366f1', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899'
                            ],
                            borderWidth: 0,
                            hoverOffset: 15
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '80%',
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    color: '#1e293b',
                                    padding: 20,
                                    font: { size: 10, weight: 'bold' },
                                    usePointStyle: true
                                }
                            }
                        }
                    }
                });
            }
        });
    </script>
@endpush