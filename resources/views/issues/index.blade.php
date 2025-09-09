@extends('layouts.app')

@section('title', 'Report Manager Portal')

@push('styles')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

        body {
            font-family: 'Inter', sans-serif;
            transition: background-color 0.3s, color 0.3s;
        }

        .card-hover {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .card-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, .1);
        }

        .status-badge,
        .segmented-control a,
        .table-row {
            transition: all 0.2s ease;
        }

        .animate-float {
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0% {
                transform: translateY(0)
            }

            50% {
                transform: translateY(-5px)
            }

            100% {
                transform: translateY(0)
            }
        }

        .dark .gradient-bg {
            background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
        }

        .light .gradient-bg {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
        }

        .issue-priority-low {
            background-color: #dcfce7;
            color: #166534;
        }

        .issue-priority-normal {
            background-color: #e0e7ff;
            color: #3730a3;
        }

        .issue-priority-high {
            background-color: #fef3c7;
            color: #92400e;
        }

        .issue-priority-urgent {
            background-color: #fee2e2;
            color: #991b1b;
        }

        .dark .issue-priority-low {
            background-color: #14532d;
            color: #bbf7d0;
        }

        .dark .issue-priority-normal {
            background-color: #312e81;
            color: #c7d2fe;
        }

        .dark .issue-priority-high {
            background-color: #78350f;
            color: #fde68a;
        }

        .dark .issue-priority-urgent {
            background-color: #7f1d1d;
            color: #fecaca;
        }
    </style>
@endpush
@section('content')
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8">
        @php
            use Carbon\Carbon;
            use App\Models\UserAttendance;

            $user = auth()->user();
            $isReporter = $user && $user->role === 'report_manager';

            $pakistanNow = now()->setTimezone('Asia/Karachi');
            $todayStartLocal = $pakistanNow->copy()->startOfDay();
            $todayEndLocal = $pakistanNow->copy()->endOfDay();
            $todayStartUtc = $todayStartLocal->copy()->setTimezone('UTC');
            $todayEndUtc = $todayEndLocal->copy()->setTimezone('UTC');

            $todayAttendance = $isReporter
                ? UserAttendance::where('user_id', $user->id)
                    ->whereBetween('check_in', [$todayStartUtc, $todayEndUtc])
                    ->latest('check_in')
                    ->first()
                : null;

            $isCheckedIn = $todayAttendance && $todayAttendance->status === 'in';
            $isCheckedOut = $todayAttendance && $todayAttendance->status === 'out';
        @endphp
        {{-- Header --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Report Manager Portal</h1>
                <p class="text-gray-600 dark:text-gray-400 mt-2">Triage and resolve user-reported lead issues in real time
                </p>
            </div>
            <div class="flex items-center gap-4">
                <button id="theme-toggle"
                    class="p-2 rounded-lg bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700"
                    style="display:none">
                    <i class="fas fa-moon text-gray-700 dark:text-yellow-400"></i>
                </button>
                <a href="{{ route('rm.notifications') }}"
                    class="relative inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 hover:shadow-md transition-shadow">
                    <i class="fas fa-bell text-gray-700 dark:text-gray-300"></i>
                    <span class="font-medium text-gray-700 dark:text-gray-300">Notifications</span>
                    @php $unread = auth()->user()->unreadNotifications()->count(); @endphp
                    <span id="notif-badge"
                        class="absolute -top-2 -right-2 bg-red-500 text-white text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center">
                        {{ $unread }}
                    </span>
                </a>
            </div>
        </div>

        {{-- Auto-checkout banner --}}
        <div id="autoCheckoutBanner"
            class="hidden mt-2 rounded-lg border border-amber-200/70 bg-amber-50/70 dark:bg-amber-900/20 dark:border-amber-800/40 px-4 py-3">
            <div class="flex items-start gap-3">
                <i class="fas fa-info-circle text-amber-600 dark:text-amber-300 mt-0.5"></i>
                <div class="flex-1">
                    <p class="text-amber-900 dark:text-amber-100 font-medium">You were automatically checked out.</p>
                    <p class="text-amber-700 dark:text-amber-200 text-sm">Close/reload detected — your session was safely
                        ended.</p>
                </div>
                <button type="button" id="autoCheckoutBannerClose"
                    class="text-amber-700 dark:text-amber-200 hover:opacity-75 transition">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>

        {{-- Attendance box --}}
        @if ($isReporter)
            <div class="mt-4 rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4">
                <h3 class="text-md font-semibold text-gray-900 dark:text-white mb-3">Attendance</h3>

                @if ($isCheckedIn && !$isCheckedOut)
                    {{-- ✅ Currently checked-in --}}
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
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
                    {{-- ✅ Checked-out already today --}}
                    <div
                        class="p-4 rounded-xl border border-blue-200/60 dark:border-blue-800/40 bg-blue-50/60 dark:bg-blue-900/20">
                        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                            <div class="flex items-start gap-3">
                                <i class="fas fa-clipboard-check text-blue-600 dark:text-blue-400 text-xl mt-1"></i>
                                <div>
                                    <p class="text-blue-900 dark:text-blue-100 font-semibold">You're checked out for today.
                                    </p>
                                    <p class="text-blue-700 dark:text-blue-300 text-sm mt-1">
                                        Checked in:
                                        {{ $todayAttendance->check_in->setTimezone('Asia/Karachi')->format('g:i A') }}
                                        —
                                        Checked out:
                                        {{ $todayAttendance->check_out->setTimezone('Asia/Karachi')->format('g:i A') }}
                                        —
                                        Hours worked:
                                        <span
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
                @else
                    {{-- ✅ No attendance yet today (fresh login) --}}
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                        <div class="flex items-start gap-3">
                            <i class="fas fa-circle-info text-gray-500 dark:text-gray-400 mt-1"></i>
                            <p class="text-gray-700 dark:text-gray-300">
                                You’ll be checked in automatically now.
                                Closing your last tab or logging out will check you out automatically.
                            </p>
                        </div>
                        <a href="{{ route('attendance.history') }}"
                            class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors flex items-center justify-center gap-2">
                            <i class="fas fa-history"></i> History
                        </a>
                    </div>
                @endif

                {{-- Optional debug panel (uncomment when needed) --}}
                {{-- 
                <details class="mt-3">
                    <summary class="text-xs text-gray-500 dark:text-gray-400 cursor-pointer">Attendance Debug</summary>
                    <div class="debug-panel mt-2 rounded-md border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 p-2 text-gray-800 dark:text-gray-200 text-[12px]" id="debug-panel"></div>
                </details>
                --}}
            </div>
        @endif


        <!-- Stats Overview -->
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-8 mt-6">
            <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Open Issues</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $counts['open'] ?? 0 }}</p>
                    </div>
                    <div class="p-3 rounded-lg bg-blue-100 dark:bg-blue-900/30">
                        <i class="fas fa-folder-open text-blue-600 dark:text-blue-400 text-xl"></i>
                    </div>
                </div>
                <p class="text-xs text-green-600 dark:text-green-400 mt-2"><i class="fas fa-arrow-up"></i> 2 from yesterday
                </p>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">In Progress</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $counts['in_progress'] ?? 0 }}</p>
                    </div>
                    <div class="p-3 rounded-lg bg-amber-100 dark:bg-amber-900/30">
                        <i class="fas fa-cog text-amber-600 dark:text-amber-400 text-xl"></i>
                    </div>
                </div>
                <p class="text-xs text-gray-600 dark:text-gray-400 mt-2">Steady progress</p>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Urgent Issues</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $urgentCount ?? 0 }}</p>
                    </div>
                    <div class="p-3 rounded-lg bg-red-100 dark:bg-red-900/30">
                        <i class="fas fa-exclamation-circle text-red-600 dark:text-red-400 text-xl"></i>
                    </div>
                </div>
                <p class="text-xs text-red-600 dark:text-red-400 mt-2">Needs immediate attention</p>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Resolved Today</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $resolvedToday ?? 0 }}</p>
                    </div>
                    <div class="p-3 rounded-lg bg-green-100 dark:bg-green-900/30">
                        <i class="fas fa-check-circle text-green-600 dark:text-green-400 text-xl"></i>
                    </div>
                </div>
                <p class="text-xs text-green-600 dark:text-green-400 mt-2">Good work!</p>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Avg. Resolution</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $avgResolutionTime ?? '6.2h' }}</p>
                    </div>
                    <div class="p-3 rounded-lg bg-purple-100 dark:bg-purple-900/30">
                        <i class="fas fa-clock text-purple-600 dark:text-purple-400 text-xl"></i>
                    </div>
                </div>
                <p class="text-xs text-green-600 dark:text-green-400 mt-2"><i class="fas fa-arrow-down"></i> 1.3h from last
                    week</p>
            </div>
        </div>

        <!-- Filters Card -->
        <div
            class="bg-white dark:bg-gray-800 rounded-2xl shadow-md border border-gray-200 dark:border-gray-700 overflow-hidden mb-8 card-hover">
            <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Filter Issues</h2>
                <p class="text-sm text-gray-600 dark:text-gray-400">Find specific issues with the filters below</p>
            </div>

            <div class="p-6">
                <form id="issues-filter-form" class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end"
                    onsubmit="return false;">
                    <div class="md:col-span-7">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Search</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-search text-gray-400"></i>
                            </div>
                            <input id="filter-q" name="q" value="{{ request('q') }}" type="text"
                                placeholder="Search by title, description, or ID..."
                                class="pl-10 w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                        </div>
                    </div>

                    <div class="md:col-span-3">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Priority</label>
                        <select id="filter-priority" name="priority"
                            class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                            <option value="">All Priorities</option>
                            @foreach (['low', 'normal', 'high', 'urgent'] as $p)
                                <option value="{{ $p }}" @selected(request('priority') === $p)>{{ ucfirst($p) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="md:col-span-2 flex gap-2">
                        <button type="button" id="filter-apply"
                            class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-3 rounded-xl bg-primary-600 text-white hover:bg-primary-700 transition-colors shadow-sm">
                            <i class="fas fa-filter"></i>
                            Apply
                        </button>
                        <button type="button" id="filter-clear"
                            class="px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </form>

                <!-- Status Tabs -->
                <div class="mt-6 flex flex-wrap gap-2">
                    <a href="{{ route('issues.index', array_merge(request()->only(['priority', 'q']), ['status' => ''])) }}"
                        class="px-4 py-2 rounded-xl {{ !request('status') ? 'bg-primary-600 text-white' : 'border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300' }} shadow-sm flex items-center gap-2">
                        <span>All Issues</span>
                        @php
                            $countsArray =
                                $counts instanceof \Illuminate\Support\Collection ? $counts->toArray() : $counts;
                            $totalCount = is_array($countsArray) ? array_sum($countsArray) : 0;
                        @endphp
                        <span class="bg-white/20 text-xs px-2 py-1 rounded-full">{{ $totalCount }}</span>
                    </a>

                    @foreach (['open', 'triaged', 'in_progress', 'resolved', 'closed'] as $s)
                        <a href="{{ route('issues.index', array_merge(request()->only(['priority', 'q']), ['status' => $s])) }}"
                            class="px-4 py-2 rounded-xl border border-gray-300 dark:border-gray-600 {{ request('status') === $s ? 'bg-primary-600 text-white' : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300' }} hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors flex items-center gap-2 status-tab"
                            data-status="{{ $s }}">
                            <span>{{ ucwords(str_replace('_', ' ', $s)) }}</span>
                            <span class="bg-gray-200 dark:bg-gray-600 text-xs px-2 py-1 rounded-full"
                                data-count-status="{{ $s }}">{{ $counts[$s] ?? 0 }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Issues Table Card -->
        <div
            class="bg-white dark:bg-gray-800 rounded-2xl shadow-md border border-gray-200 dark:border-gray-700 overflow-hidden card-hover">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Issues</h2>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm" id="issues-table" data-page-size="{{ $issues->perPage() }}">
                    <thead class="bg-gray-50 dark:bg-gray-700/50">
                        <tr>
                            <th
                                class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                ID</th>
                            <th
                                class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Title</th>
                            <th
                                class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Priority</th>
                            <th
                                class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Status</th>
                            <th
                                class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Lead</th>
                            <th
                                class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Reporter</th>
                            <th
                                class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Created</th>
                            <th
                                class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse ($issues as $issue)
                            <tr data-id="{{ $issue->id }}" data-title="{{ $issue->title }}"
                                data-priority="{{ $issue->priority }}" data-status="{{ $issue->status }}"
                                class="table-row hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-4 py-2 whitespace-nowrap">
                                    <span
                                        class="text-sm font-medium text-gray-900 dark:text-white">#{{ $issue->id }}</span>
                                </td>
                                <td class="px-4 py-2">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $issue->title }}
                                    </div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ Str::limit($issue->description, 30) }}</div>
                                </td>
                                <td class="px-4 py-2 whitespace-nowrap">
                                    <span
                                        class="px-2.5 py-1.5 rounded-full text-xs font-medium issue-priority-{{ $issue->priority }}">
                                        {{ ucfirst($issue->priority) }}
                                    </span>
                                </td>
                                <td class="px-4 py-2 whitespace-nowrap">
                                    @php
                                        $statusColors = [
                                            'open' => 'blue',
                                            'triaged' => 'purple',
                                            'in_progress' => 'amber',
                                            'resolved' => 'green',
                                            'closed' => 'gray',
                                        ];
                                        $color = $statusColors[$issue->status] ?? 'blue';
                                    @endphp
                                    <span
                                        class="px-2.5 py-1.5 rounded-full text-xs font-medium bg-{{ $color }}-100 text-{{ $color }}-800 dark:bg-{{ $color }}-900/30 dark:text-{{ $color }}-400">
                                        {{ ucwords(str_replace('_', ' ', $issue->status)) }}
                                    </span>
                                </td>
                                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900 dark:text-white">Lead
                                    #{{ $issue->lead_id }}</td>
                                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    {{ $issue->reporter->name }}</td>
                                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ $issue->created_at->diffForHumans() }}</td>
                                <td class="px-4 py-2 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="{{ route('issues.show', $issue) }}"
                                        class="text-primary-600 dark:text-primary-400 hover:text-primary-900 dark:hover:text-primary-300">View</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-8 text-center">
                                    <div
                                        class="mx-auto w-12 h-12 rounded-xl bg-gray-100 dark:bg-gray-700 flex items-center justify-center mb-3">
                                        <i class="fas fa-inbox text-gray-400 text-lg"></i>
                                    </div>
                                    <p class="text-gray-500 dark:text-gray-400">No issues found. Try adjusting your
                                        filters.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($issues->hasPages())
                <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/20">
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-700 dark:text-gray-300">
                            Showing <span class="font-medium">{{ $issues->firstItem() }}</span> to
                            <span class="font-medium">{{ $issues->lastItem() }}</span> of
                            <span class="font-medium">{{ $issues->total() }}</span> results
                        </div>
                        <div class="flex gap-2">
                            @if ($issues->onFirstPage())
                                <span
                                    class="px-3 py-1.5 rounded-md bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-400 dark:text-gray-500 text-sm font-medium">Previous</span>
                            @else
                                <a href="{{ $issues->previousPageUrl() }}"
                                    class="px-3 py-1.5 rounded-md bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium hover:bg-gray-50 dark:hover:bg-gray-600">Previous</a>
                            @endif

                            @foreach ($issues->getUrlRange(1, $issues->lastPage()) as $page => $url)
                                @if ($page == $issues->currentPage())
                                    <span
                                        class="px-3 py-1.5 rounded-md bg-primary-600 text-white text-sm font-medium">{{ $page }}</span>
                                @else
                                    <a href="{{ $url }}"
                                        class="px-3 py-1.5 rounded-md bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium hover:bg-gray-50 dark:hover:bg-gray-600">{{ $page }}</a>
                                @endif
                            @endforeach

                            @if ($issues->hasMorePages())
                                <a href="{{ $issues->nextPageUrl() }}"
                                    class="px-3 py-1.5 rounded-md bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium hover:bg-gray-50 dark:hover:bg-gray-600">Next</a>
                            @else
                                <span
                                    class="px-3 py-1.5 rounded-md bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-400 dark:text-gray-500 text-sm font-medium">Next</span>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Toast -->
        <div id="issue-toast"
            class="fixed bottom-4 right-4 bg-green-600 text-white px-6 py-3 rounded-xl shadow-lg transform transition-all duration-300 translate-y-20 opacity-0 hidden">
            <div class="flex items-center gap-3">
                <i class="fas fa-check-circle"></i>
                <span id="toast-message"></span>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Theme toggle
        document.getElementById('theme-toggle').addEventListener('click', function() {
            if (document.documentElement.classList.contains('dark')) {
                document.documentElement.classList.remove('dark');
                localStorage.setItem('theme', 'light');
                this.innerHTML = '<i class="fas fa-moon text-gray-700"></i>';
            } else {
                document.documentElement.classList.add('dark');
                localStorage.setItem('theme', 'dark');
                this.innerHTML = '<i class="fas fa-sun text-yellow-400"></i>';
            }
        });
        if (localStorage.getItem('theme') === 'dark') {
            document.documentElement.classList.add('dark');
            document.getElementById('theme-toggle').innerHTML = '<i class="fas fa-sun text-yellow-400"></i>';
        } else {
            document.documentElement.classList.remove('dark');
            document.getElementById('theme-toggle').innerHTML = '<i class="fas fa-moon text-gray-700"></i>';
        }

        // Toast helper
        function showToast(message) {
            const toast = document.getElementById('issue-toast');
            const toastMessage = document.getElementById('toast-message');
            toastMessage.textContent = message;
            toast.classList.remove('translate-y-20', 'opacity-0', 'hidden');
            toast.classList.add('-translate-y-0', 'opacity-100');
            setTimeout(() => {
                toast.classList.remove('-translate-y-0', 'opacity-100');
                toast.classList.add('translate-y-20', 'opacity-0');
            }, 5000);
        }

        // jQuery filters
        (function loadJQ(cb) {
            if (window.jQuery) return cb();
            const s = document.createElement('script');
            s.src = 'https://code.jquery.com/jquery-3.7.1.min.js';
            s.integrity = 'sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=';
            s.crossOrigin = 'anonymous';
            s.onload = cb;
            document.head.appendChild(s);
        })(function initFilters() {
            const $ = window.jQuery;
            const debounce = (fn, ms = 300) => {
                let t;
                return (...args) => {
                    clearTimeout(t);
                    t = setTimeout(() => fn.apply(null, args), ms);
                };
            };
            const $tbody = $('#issues-table tbody');
            const $q = $('#filter-q');
            const $priority = $('#filter-priority');
            const $tabs = $('.status-tab');
            const $applyBtn = $('#filter-apply');
            const $clearBtn = $('#filter-clear');

            function activeStatus() {
                return $tabs.filter(function() {
                    return $(this).hasClass('bg-primary-600');
                }).data('status') || '';
            }

            function setActiveTab($tab) {
                $tabs.removeClass('bg-primary-600 text-white shadow').addClass(
                    'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600'
                    );
                $tab.addClass('bg-primary-600 text-white shadow').removeClass(
                    'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600'
                    );
            }

            function applyFilters() {
                const q = ($.trim($q.val() || '').toLowerCase());
                const p = ($priority.val() || '').toLowerCase();
                const s = (activeStatus() || '').toLowerCase();
                $tbody.find('tr').each(function() {
                    const $tr = $(this);
                    const title = ($tr.data('title') || '').toString().toLowerCase();
                    const pri = ($tr.data('priority') || '').toLowerCase();
                    const stat = ($tr.data('status') || '').toLowerCase();
                    const okQ = !q || title.indexOf(q) !== -1;
                    const okP = !p || pri === p;
                    const okS = !s || stat === s;
                    $tr.toggle(okQ && okP && okS);
                });
                ['open', 'triaged', 'in_progress', 'resolved', 'closed'].forEach(function(key) {
                    const count = $tbody.find('tr').filter(function() {
                        const $tr = $(this);
                        const title = ($tr.data('title') || '').toString().toLowerCase();
                        const pri = ($tr.data('priority') || '').toLowerCase();
                        const stat = ($tr.data('status') || '').toLowerCase();
                        const okQ = !q || title.indexOf(q) !== -1;
                        const okP = !p || pri === p;
                        return okQ && okP && stat === key && $tr.is(':visible');
                    }).length;
                    $(`[data-count-status="${key}"]`).text(count);
                });
            }
            window.applyIssueFilters = applyFilters;
            $q.on('keyup', debounce(applyFilters, 250));
            $priority.on('change', applyFilters);
            $applyBtn.on('click', applyFilters);
            $clearBtn.on('click', function() {
                $q.val('');
                $priority.val('');
                applyFilters();
            });
            $tabs.on('click', function(e) {
                e.preventDefault();
                setActiveTab($(this));
                applyFilters();
            });
            applyFilters();
        });

        // Echo realtime
        (function() {
            if (!window.Echo) return;
            const baseIssueUrl = @json(url('/issues'));
            const tbody = document.querySelector('#issues-table tbody');
            const notifBadge = document.getElementById('notif-badge');
            const pageSize = parseInt(document.getElementById('issues-table').dataset.pageSize || '0', 10) || 0;

            const esc = (s) => (s ?? '').toString().replaceAll('&', '&amp;').replaceAll('<', '&lt;').replaceAll('>',
                '&gt;').replaceAll('"', '&quot;').replaceAll("'", '&#39;');
            const ucFirst = (s) => (s || '').charAt(0).toUpperCase() + (s || '').slice(1);
            const humanize = (s) => ucFirst((s || '').toString().replaceAll('_', ' '));
            const priorityPill = (p) =>
                `<span class="px-2.5 py-1.5 rounded-full text-xs font-medium issue-priority-${p}">${esc(ucFirst(p||'normal'))}</span>`;
            const statusPill = (status) => {
                const map = {
                    open: 'blue',
                    triaged: 'purple',
                    in_progress: 'amber',
                    resolved: 'green',
                    closed: 'gray'
                };
                const c = map[status] || 'blue';
                return `<span class="px-2.5 py-1.5 rounded-full text-xs font-medium bg-${c}-100 text-${c}-800 dark:bg-${c}-900/30 dark:text-${c}-400">${esc(humanize(status||'open'))}</span>`;
            };

            const upsertRow = (e) => {
                const id = e.id;
                let row = tbody.querySelector(`tr[data-id="${id}"]`);
                const html =
                    `
                    <td class="px-4 py-2 whitespace-nowrap"><span class="text-sm font-medium text-gray-900 dark:text-white">#${esc(id)}</span></td>
                    <td class="px-4 py-2"><div class="text-sm font-medium text-gray-900 dark:text-white">${esc(e.title)}</div><div class="text-sm text-gray-500 dark:text-gray-400">${esc(e.description?.substring(0,30)||'')}</div></td>
                    <td class="px-4 py-2 whitespace-nowrap">${priorityPill(e.priority)}</td>
                    <td class="px-4 py-2 whitespace-nowrap">${statusPill(e.status||'open')}</td>
                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900 dark:text-white">Lead #${esc(e.lead_id)}</td>
                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900 dark:text-white">${esc(e?.reporter?.name||'Reporter')}</td>
                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">just now</td>
                    <td class="px-4 py-2 whitespace-nowrap text-right text-sm font-medium"><a href="${baseIssueUrl}/${esc(id)}" class="text-primary-600 dark:text-primary-400 hover:text-primary-900 dark:hover:text-primary-300">View</a></td>`;
                if (row) {
                    row.innerHTML = html;
                    row.classList.add('bg-yellow-50', 'dark:bg-yellow-900/20');
                    setTimeout(() => row.classList.remove('bg-yellow-50', 'dark:bg-yellow-900/20'), 1600);
                } else {
                    row = document.createElement('tr');
                    row.className =
                        'table-row hover:bg-gray-50 dark:hover:bg-gray-700/50 bg-yellow-50 dark:bg-yellow-900/20';
                    row.setAttribute('data-id', id);
                    row.setAttribute('data-title', e.title || '');
                    row.setAttribute('data-priority', e.priority || 'normal');
                    row.setAttribute('data-status', e.status || 'open');
                    row.innerHTML = html;
                    tbody.prepend(row);
                    if (pageSize > 0) {
                        const rows = tbody.querySelectorAll('tr');
                        if (rows.length > pageSize) rows[rows.length - 1].remove();
                    }
                    setTimeout(() => row.classList.remove('bg-yellow-50', 'dark:bg-yellow-900/20'), 1600);
                }
                if (window.applyIssueFilters) window.applyIssueFilters();
            };

            window.Echo.join('report-managers').listen('.issue.created', (e) => {
                showToast(`New issue: ${e.title} (Lead #${e.lead_id})`);
                const badge = notifBadge;
                if (badge) {
                    badge.textContent = (parseInt(badge.textContent || '0', 10) + 1).toString();
                    badge.classList.remove('bg-gray-300', 'text-gray-700', 'dark:bg-gray-600',
                        'dark:text-gray-200');
                    badge.classList.add('bg-red-500', 'text-white');
                }
                upsertRow(e);
            });
        })();
    </script>

    {{-- === Auto check-in/out logic (fixed guard + server-state aware) === --}}
    <script>
        (function() {
            // ===== Server truth from Blade =====
            const IS_REPORTER = @json($isReporter);
            const SERVER_SAYS_IN = @json($isCheckedIn);
            const SERVER_SAYS_OUT = @json($isCheckedOut);
            const HAS_TODAY_ATTENDANCE = @json((bool) $todayAttendance);
            const USER_ID = @json(optional(auth()->user())->id);
            const TODAY_YYYY_MM_DD = @json(now()->setTimezone('Asia/Karachi')->format('Y-m-d'));

            // ===== Routes & CSRF =====
            const LOGOUT_URL = @json(route('logout'));
            const CHECKIN_URL = @json($isReporter ? route('attendance.checkIn') : null);
            const CHECKOUT_URL = @json($isReporter ? route('attendance.checkOut') : null);
            const CSRF = (document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')) ||
                @json(csrf_token());

            // ===== Keys (date-scoped) =====
            const TAB_KEY = 'app_active_tab_count';
            const GUARD_KEY = `checked_in_once:${USER_ID || 'anon'}:${TODAY_YYYY_MM_DD}`;
            const ORIGIN = location.origin;

            // ===== Debug helper =====
            function dbg(msg, data) {
                const panel = document.getElementById('debug-panel');
                if (!panel) return;
                const pre = document.createElement('div');
                pre.className = 'debug-pre mb-1';
                pre.textContent =
                    `[${new Date().toLocaleTimeString()}] ${msg}${data !== undefined ? '\n' + (typeof data === 'string' ? data : JSON.stringify(data, null, 2)) : ''}`;
                panel.prepend(pre);
            }

            // ===== HTTP helpers =====
            function sendFormBeacon(url, payload = {}) {
                if (!url) return false;
                try {
                    const bodyStr = new URLSearchParams(Object.assign({
                        _token: CSRF
                    }, payload)).toString();
                    const blob = new Blob([bodyStr], {
                        type: 'application/x-www-form-urlencoded;charset=UTF-8'
                    });
                    const ok = navigator.sendBeacon(url, blob);
                    dbg('sendBeacon', {
                        url,
                        ok
                    });
                    return ok;
                } catch (e) {
                    dbg('sendBeacon error', String(e));
                    return false;
                }
            }
            async function postForm(url, payload = {}, opts = {}) {
                if (!url) return;
                const body = new URLSearchParams(Object.assign({
                    _token: CSRF
                }, payload));
                try {
                    const resp = await fetch(url, {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'Accept': 'application/json, text/plain, */*',
                            'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8',
                            'X-CSRF-TOKEN': CSRF
                        },
                        body,
                        keepalive: !!opts.keepalive
                    });
                    dbg('fetch POST', {
                        url,
                        status: resp.status
                    });
                    return resp;
                } catch (e) {
                    dbg('fetch error', String(e));
                }
            }

            // ===== Tab counters =====
            const getCount = () => parseInt(localStorage.getItem(TAB_KEY) || '0', 10) || 0;
            const setCount = (n) => localStorage.setItem(TAB_KEY, String(Math.max(0, n)));

            // ===== Auto CHECK-IN =====
            function allowAnotherAutoCheckIn() {
                // If server says OUT (or no attendance today), allow check-in again
                if (SERVER_SAYS_OUT || !HAS_TODAY_ATTENDANCE) {
                    sessionStorage.removeItem(GUARD_KEY);
                    dbg('Cleared guard (server OUT or no today row)');
                }
            }
            async function ensureCheckInNow(reason = 'auto') {
                if (!IS_REPORTER || !CHECKIN_URL) return;
                allowAnotherAutoCheckIn();

                if (sessionStorage.getItem(GUARD_KEY)) {
                    dbg(`auto check-in skipped (guard present)`);
                    return;
                }
                sessionStorage.setItem(GUARD_KEY, '1');
                dbg(`auto check-in START [${reason}]`);

                const ok = sendFormBeacon(CHECKIN_URL);
                if (!ok) await postForm(CHECKIN_URL);

                // optional soft verify
                setTimeout(() => {
                    if (SERVER_SAYS_OUT) {
                        dbg('server still OUT after 1s — retry check-in');
                        sendFormBeacon(CHECKIN_URL) || postForm(CHECKIN_URL);
                    }
                }, 1000);
            }

            // ===== Auto CHECK-OUT (last tab or explicit logout) =====
            let skipCloseFlow = false,
                isLoggingOut = false,
                leavingFired = false;

            document.addEventListener('click', (e) => {
                const a = e.target.closest?.('a[href]');
                if (!a) return;
                try {
                    const url = new URL(a.href, ORIGIN);
                    if (url.origin === ORIGIN) {
                        if (url.href === LOGOUT_URL) isLoggingOut = true;
                        else skipCloseFlow = true;
                    }
                } catch (_) {}
            }, true);

            document.addEventListener('submit', (e) => {
                const form = e.target;
                try {
                    const actionUrl = new URL((form.getAttribute('action') || ''), ORIGIN).href;
                    if (actionUrl === LOGOUT_URL) {
                        isLoggingOut = true;
                        if (IS_REPORTER && CHECKOUT_URL) sendFormBeacon(CHECKOUT_URL);
                    } else skipCloseFlow = true;
                } catch (_) {
                    skipCloseFlow = true;
                }
            }, true);

            async function handleLeaving() {
                if (leavingFired) return;
                leavingFired = true;
                setCount(getCount() - 1);
                const remaining = getCount();

                if (isLoggingOut) {
                    if (IS_REPORTER && CHECKOUT_URL) sendFormBeacon(CHECKOUT_URL);
                    return;
                }
                if (skipCloseFlow) return;

                if (remaining === 0) {
                    // ✅ Only auto-checkout, DO NOT log user out on reload
                    if (IS_REPORTER && CHECKOUT_URL) {
                        if (!sendFormBeacon(CHECKOUT_URL)) postForm(CHECKOUT_URL, {}, {
                            keepalive: true
                        });
                    }
                    try {
                        sessionStorage.setItem('autoCheckedOut', '1');
                    } catch (_) {}
                    sessionStorage.removeItem(GUARD_KEY);
                }
            }

            // ===== Lifecycle =====
            window.addEventListener('pageshow', function() {
                setCount(getCount() + 1);
                if (IS_REPORTER) ensureCheckInNow('pageshow');
            });

            document.addEventListener('DOMContentLoaded', function() {
                if (IS_REPORTER) ensureCheckInNow('domready');
                try {
                    if (sessionStorage.getItem('autoCheckedOut') === '1') {
                        document.getElementById('autoCheckoutBanner')?.classList.remove('hidden');
                        sessionStorage.removeItem('autoCheckedOut');
                        sessionStorage.removeItem(GUARD_KEY);
                    }
                } catch (_) {}
            });

            document.addEventListener('visibilitychange', function() {
                if (document.visibilityState === 'visible' && IS_REPORTER) ensureCheckInNow('visibility');
            });

            window.addEventListener('beforeunload', handleLeaving);
            window.addEventListener('pagehide', handleLeaving);

            window.addEventListener('storage', function(e) {
                if (e.key === TAB_KEY && e.newValue == null) setCount(1);
            });

            dbg('BOOT', {
                IS_REPORTER,
                SERVER_SAYS_IN,
                SERVER_SAYS_OUT,
                HAS_TODAY_ATTENDANCE,
                GUARD_KEY,
                CHECKIN_URL,
                CHECKOUT_URL
            });
        })();
    </script>
@endpush
