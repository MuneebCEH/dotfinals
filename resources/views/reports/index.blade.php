@extends('layouts.app')

@section('title', 'Reports')
@section('page-title', 'Reports')

@extends('layouts.app')

@section('title', 'Reports')
@section('page-title', 'Reports')

@section('content')
    <div class="space-y-8">
        <!-- Header -->
        <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl shadow-2xl rounded-2xl p-8 border border-gray-200/50 dark:border-gray-700/50">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">Analytics & Reports</h2>
                    <p class="text-lg text-gray-600 dark:text-gray-400">Track performance and generate insights.</p>
                </div>
                <div class="mt-6 sm:mt-0 flex space-x-4">
                    <button id="exportReport"
                        class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-success-500 to-success-600 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition-all duration-200">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                            </path>
                        </svg>
                        Export Report
                    </button>
                </div>
            </div>
        </div>

        <!-- Key Metrics -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="stat-card dark:stat-card-dark rounded-2xl p-6 shadow-2xl border border-gray-200/50 dark:border-gray-700/50">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Total Leads</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ $totalLeads }}</p>
                    </div>
                    <div class="w-12 h-12 bg-gradient-to-br from-primary-500 to-primary-600 rounded-xl flex items-center justify-center shadow-lg">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                            </path>
                        </svg>
                    </div>
                </div>
                <div class="mt-4 flex items-center">
                    <span class="text-sm {{ $leadGrowth >= 0 ? 'text-success-600 dark:text-success-400' : 'text-danger-600 dark:text-danger-400' }} font-medium flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="{{ $leadGrowth >= 0 ? 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6' : 'M13 17h8m0 0V9m0 8l-8-8-4 4-6-6' }}">
                            </path>
                        </svg>
                        {{ $leadGrowth >= 0 ? '+' : '' }}{{ $leadGrowth }}% from last month
                    </span>
                </div>
            </div>

            <div class="stat-card dark:stat-card-dark rounded-2xl p-6 shadow-2xl border border-gray-200/50 dark:border-gray-700/50">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Conversion Rate</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ $conversionRate }}%</p>
                    </div>
                    <div class="w-12 h-12 bg-gradient-to-br from-success-500 to-success-600 rounded-xl flex items-center justify-center shadow-lg">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                            </path>
                        </svg>
                    </div>
                </div>
                <div class="mt-4">
                    <div class="flex space-x-4">
                        <div class="flex-1">
                            <p class="text-xs text-gray-500 dark:text-gray-400">Active</p>
                            <p class="text-sm font-semibold">{{ $activeLeads }}</p>
                        </div>
                        <div class="flex-1">
                            <p class="text-xs text-gray-500 dark:text-gray-400">Converted</p>
                            <p class="text-sm font-semibold">{{ $convertedLeads }}</p>
                        </div>
                        <div class="flex-1">
                            <p class="text-xs text-gray-500 dark:text-gray-400">Pending</p>
                            <p class="text-sm font-semibold">{{ $pendingLeads }}</p>
                        </div>
                    </div>
                    <div class="mt-4">
                        <span class="text-sm {{ $conversionRateGrowth >= 0 ? 'text-success-600 dark:text-success-400' : 'text-danger-600 dark:text-danger-400' }} font-medium flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="{{ $conversionRateGrowth >= 0 ? 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6' : 'M13 17h8m0 0V9m0 8l-8-8-4 4-6-6' }}">
                                </path>
                            </svg>
                            {{ $conversionRateGrowth >= 0 ? '+' : '' }}{{ $conversionRateGrowth }}% from last month
                        </span>
                    </div>
                </div>
            </div>

            <div class="stat-card dark:stat-card-dark rounded-2xl p-6 shadow-2xl border border-gray-200/50 dark:border-gray-700/50">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Categories</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ $topCategories->count() }}</p>
                    </div>
                    <div class="w-12 h-12 bg-gradient-to-br from-warning-500 to-warning-600 rounded-xl flex items-center justify-center shadow-lg">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z">
                            </path>
                        </svg>
                    </div>
                </div>
                <div class="mt-4 flex items-center">
                    <span class="text-sm {{ $categoryGrowth >= 0 ? 'text-success-600 dark:text-success-400' : 'text-danger-600 dark:text-danger-400' }} font-medium flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="{{ $categoryGrowth >= 0 ? 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6' : 'M13 17h8m0 0V9m0 8l-8-8-4 4-6-6' }}">
                            </path>
                        </svg>
                        {{ $categoryGrowth >= 0 ? '+' : '' }}{{ $categoryGrowth }}% from last month
                    </span>
                </div>
            </div>

            <div class="stat-card dark:stat-card-dark rounded-2xl p-6 shadow-2xl border border-gray-200/50 dark:border-gray-700/50">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Avg Response Time</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white">0h</p>
                    </div>
                    <div class="w-12 h-12 bg-gradient-to-br from-danger-500 to-danger-600 rounded-xl flex items-center justify-center shadow-lg">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
                <div class="mt-4 flex items-center">
                    <span class="text-sm text-danger-600 dark:text-danger-400 font-medium flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"></path>
                        </svg>
                        +2h from last month
                    </span>
                </div>
            </div>
        </div>

        <!-- Charts and Analytics -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Lead Performance Chart -->
            <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl shadow-2xl rounded-2xl border border-gray-200/50 dark:border-gray-700/50">
                <div class="px-8 py-6 border-b border-gray-200/50 dark:border-gray-700/50">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white">Lead Performance</h3>
                </div>
                <div class="p-8">
                    <div class="h-64"></div>
                </div>
            </div>

            <!-- Conversion Funnel -->
            <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl shadow-2xl rounded-2xl border border-gray-200/50 dark:border-gray-700/50">
                <div class="px-8 py-6 border-b border-gray-200/50 dark:border-gray-700/50">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white">Conversion Funnel</h3>
                </div>
                <div class="p-8 space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Leads</span>
                        <span class="text-sm font-bold text-gray-900 dark:text-white">{{ $totalLeads }}</span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                        <div class="bg-primary-600 h-2 rounded-full" style="width: 100%"></div>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Active Leads</span>
                        <span class="text-sm font-bold text-gray-900 dark:text-white">{{ $activeLeads }}</span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                        <div class="bg-success-600 h-2 rounded-full" style="width: {{ $totalLeads > 0 ? ($activeLeads / $totalLeads) * 100 : 0 }}%"></div>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Converted</span>
                        <span class="text-sm font-bold text-gray-900 dark:text-white">{{ $convertedLeads }}</span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                        <div class="bg-warning-600 h-2 rounded-full" style="width: {{ $totalLeads > 0 ? ($convertedLeads / $totalLeads) * 100 : 0 }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detailed Reports -->
        <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl shadow-2xl rounded-2xl border border-gray-200/50 dark:border-gray-700/50">
            <div class="px-8 py-6 border-b border-gray-200/50 dark:border-gray-700/50">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white">Detailed Reports</h3>
            </div>
            <div class="p-8">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Top Performing Categories -->
                    <div class="bg-gray-50/80 dark:bg-gray-700/80 backdrop-blur-xl rounded-xl p-6">
                        <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Top Categories</h4>
                        <div class="space-y-3">
                            <p class="text-sm text-gray-500 dark:text-gray-400">No categories data available</p>
                        </div>
                    </div>

                    <!-- User Performance -->
                    <div class="bg-gray-50/80 dark:bg-gray-700/80 backdrop-blur-xl rounded-xl p-6">
                        <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Top Users</h4>
                        <div class="space-y-3">
                            <p class="text-sm text-gray-500 dark:text-gray-400">No user data available</p>
                        </div>
                    </div>

                    <!-- Recent Activity -->
                    <div class="bg-gray-50/80 dark:bg-gray-700/80 backdrop-blur-xl rounded-xl p-6">
                        <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Recent Activity</h4>
                        <div class="space-y-3">
                            <p class="text-sm text-gray-500 dark:text-gray-400">No recent activity</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection


@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        function initChart() {
            const isDark = document.documentElement.classList.contains('dark');
            const chart = document.querySelector('.h-64');
            if (!chart) return;

            const leadPerformanceOptions = {
                series: [{
                    name: 'Leads',
                    data: [{{ $totalLeads }}, {{ $activeLeads }}, {{ $convertedLeads }}, {{ $pendingLeads }}]
                }],
                chart: {
                    type: 'bar',
                    height: 256,
                    toolbar: {
                        show: false
                    },
                    background: 'transparent',
                },
                plotOptions: {
                    bar: {
                        borderRadius: 4,
                        horizontal: true,
                        distributed: true,
                    }
                },
                dataLabels: {
                    enabled: true,
                    style: {
                        colors: [isDark ? '#fff' : '#000']
                    }
                },
                xaxis: {
                    categories: ['Total', 'Active', 'Converted', 'Pending'],
                    labels: {
                        style: {
                            colors: isDark ? '#9ca3af' : '#4b5563'
                        }
                    }
                },
                yaxis: {
                    labels: {
                        style: {
                            colors: isDark ? '#9ca3af' : '#4b5563'
                        }
                    }
                },
                colors: ['#6366f1', '#22c55e', '#eab308', '#ef4444'],
                theme: {
                    mode: isDark ? 'dark' : 'light'
                },
                grid: {
                    borderColor: isDark ? '#374151' : '#e5e7eb',
                },
                legend: {
                    labels: {
                        colors: isDark ? '#fff' : '#000'
                    }
                }
            };

            const chartInstance = new ApexCharts(chart, leadPerformanceOptions);
            chartInstance.render();

            // Update chart on theme change
            const observer = new MutationObserver((mutations) => {
                mutations.forEach((mutation) => {
                    if (mutation.attributeName === 'class' && 
                        mutation.target === document.documentElement) {
                        const isDarkNew = document.documentElement.classList.contains('dark');
                        chartInstance.updateOptions({
                            theme: { mode: isDarkNew ? 'dark' : 'light' },
                            grid: { borderColor: isDarkNew ? '#374151' : '#e5e7eb' },
                            xaxis: {
                                labels: { style: { colors: isDarkNew ? '#9ca3af' : '#4b5563' } }
                            },
                            yaxis: {
                                labels: { style: { colors: isDarkNew ? '#9ca3af' : '#4b5563' } }
                            },
                            dataLabels: {
                                style: { colors: [isDarkNew ? '#fff' : '#000'] }
                            },
                            legend: {
                                labels: { colors: isDarkNew ? '#fff' : '#000' }
                            }
                        });
                    }
                });
            });

            observer.observe(document.documentElement, {
                attributes: true,
                attributeFilter: ['class']
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            initChart();

            // Export Report
            document.getElementById('exportReport')?.addEventListener('click', async function() {
                try {
                    const response = await fetch('{{ route("reports.export") }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    });
                    
                    if (response.ok) {
                        const blob = await response.blob();
                        const url = window.URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        a.href = url;
                        a.download = 'leads-report.pdf';
                        document.body.appendChild(a);
                        a.click();
                        window.URL.revokeObjectURL(url);
                    } else {
                        throw new Error('Export failed');
                    }
                } catch (error) {
                    alert('Failed to export report. Please try again later.');
                    console.error('Export error:', error);
                }
            });
        });
    </script>
@endpush
