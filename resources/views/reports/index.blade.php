{{-- resources/views/reports/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Intelligence Reports')
@section('page-title', 'Intelligence Reports')

@section('content')
    <div class="space-y-6 animate-on-load">
        {{-- Reports Header --}}
        <div
            class="card-premium p-8 flex flex-col md:flex-row md:items-center justify-between gap-6 overflow-hidden relative">
            <div class="relative z-10">
                <h1 class="text-4xl font-black tracking-tight text-white mb-2">Data <span
                        class="gradient-text">Intelligence</span></h1>
                <p class="text-slate-400 font-medium">Extracting actionable insights from the lead ecosystem.</p>
            </div>

            <div class="flex items-center gap-3 relative z-10">
                <button id="exportReport"
                    class="px-8 py-4 bg-emerald-600 hover:bg-emerald-500 text-white rounded-2xl font-black transition-all shadow-xl shadow-emerald-600/20 flex items-center gap-3 group">
                    <i class="fas fa-file-export group-hover:translate-y-[-2px] transition-transform"></i>
                    <span>Export Protocol</span>
                </button>
            </div>

            <div class="absolute -right-20 -top-20 w-64 h-64 bg-emerald-500/10 blur-[100px] rounded-full"></div>
        </div>

        {{-- Core Metrics Terminal --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="card-premium p-6 relative overflow-hidden group">
                <div class="flex items-center justify-between mb-4">
                    <div
                        class="w-12 h-12 rounded-2xl bg-indigo-500/20 flex items-center justify-center text-indigo-400 border border-indigo-500/30 group-hover:scale-110 transition-transform">
                        <i class="fas fa-users text-xl"></i>
                    </div>
                    <span
                        class="text-[10px] font-black uppercase tracking-widest {{ $leadGrowth >= 0 ? 'text-emerald-500' : 'text-rose-500' }}">
                        {{ $leadGrowth >= 0 ? '+' : '' }}{{ $leadGrowth }}%
                    </span>
                </div>
                <h3 class="text-[10px] font-black uppercase tracking-widest text-slate-500 mb-1">Total Population</h3>
                <p class="text-3xl font-black text-white mb-2">{{ number_format($totalLeads) }}</p>
                <div class="w-full bg-white/5 rounded-full h-1 overflow-hidden">
                    <div class="bg-indigo-500 h-full rounded-full" style="width: 70%"></div>
                </div>
            </div>

            <div class="card-premium p-6 relative overflow-hidden group">
                <div class="flex items-center justify-between mb-4">
                    <div
                        class="w-12 h-12 rounded-2xl bg-emerald-500/20 flex items-center justify-center text-emerald-400 border border-emerald-500/30 group-hover:scale-110 transition-transform">
                        <i class="fas fa-bullseye text-xl"></i>
                    </div>
                    <span
                        class="text-[10px] font-black uppercase tracking-widest {{ $conversionRateGrowth >= 0 ? 'text-emerald-500' : 'text-rose-500' }}">
                        {{ $conversionRateGrowth >= 0 ? '+' : '' }}{{ $conversionRateGrowth }}%
                    </span>
                </div>
                <h3 class="text-[10px] font-black uppercase tracking-widest text-slate-500 mb-1">Conversion Ratio</h3>
                <p class="text-3xl font-black text-white mb-2">{{ $conversionRate }}%</p>
                <div class="w-full bg-white/5 rounded-full h-1 overflow-hidden">
                    <div class="bg-emerald-500 h-full rounded-full" style="width: {{ $conversionRate }}%"></div>
                </div>
            </div>

            <div class="card-premium p-6 relative overflow-hidden group">
                <div class="flex items-center justify-between mb-4">
                    <div
                        class="w-12 h-12 rounded-2xl bg-amber-500/20 flex items-center justify-center text-amber-400 border border-amber-500/30 group-hover:scale-110 transition-transform">
                        <i class="fas fa-tags text-xl"></i>
                    </div>
                    <span
                        class="text-[10px] font-black uppercase tracking-widest {{ $categoryGrowth >= 0 ? 'text-emerald-500' : 'text-rose-500' }}">
                        {{ $categoryGrowth >= 0 ? '+' : '' }}{{ $categoryGrowth }}%
                    </span>
                </div>
                <h3 class="text-[10px] font-black uppercase tracking-widest text-slate-500 mb-1">Sectors Tracked</h3>
                <p class="text-3xl font-black text-white mb-2">{{ $topCategories->count() }}</p>
                <div class="w-full bg-white/5 rounded-full h-1 overflow-hidden">
                    <div class="bg-amber-500 h-full rounded-full" style="width: 45%"></div>
                </div>
            </div>

            <div class="card-premium p-6 relative overflow-hidden group">
                <div class="flex items-center justify-between mb-4">
                    <div
                        class="w-12 h-12 rounded-2xl bg-rose-500/20 flex items-center justify-center text-rose-400 border border-rose-500/30 group-hover:scale-110 transition-transform">
                        <i class="fas fa-bolt text-xl"></i>
                    </div>
                    <span class="text-[10px] font-black uppercase tracking-widest text-rose-500">Warning</span>
                </div>
                <h3 class="text-[10px] font-black uppercase tracking-widest text-slate-500 mb-1">Response Latency</h3>
                <p class="text-3xl font-black text-white mb-2">0h</p>
                <div class="w-full bg-white/5 rounded-full h-1 overflow-hidden">
                    <div class="bg-rose-500 h-full rounded-full" style="width: 15%"></div>
                </div>
            </div>
        </div>

        {{-- Visual Analytics Grid --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Performance Matrix --}}
            <div class="card-premium flex flex-col min-h-[400px]">
                <div class="p-6 border-b border-white/5 flex items-center justify-between bg-white/[0.02]">
                    <h3 class="text-[10px] font-black uppercase tracking-widest text-slate-500">Agent Performance Matrix
                    </h3>
                    <div class="flex gap-2">
                        <div class="w-2 h-2 rounded-full bg-indigo-500 animate-pulse"></div>
                        <span class="text-[10px] font-black uppercase tracking-widest text-indigo-500">Live Stream</span>
                    </div>
                </div>
                <div class="p-8 flex-1 flex items-center justify-center relative">
                    <div class="w-full h-72" id="performanceChartTerminal"></div>
                </div>
            </div>

            {{-- Conversion Funnel --}}
            <div class="card-premium flex flex-col min-h-[400px]">
                <div class="p-6 border-b border-white/5 flex items-center justify-between bg-white/[0.02]">
                    <h3 class="text-[10px] font-black uppercase tracking-widest text-slate-500">Conversion Funnel Analytics
                    </h3>
                    <i class="fas fa-filter text-slate-600 text-[10px]"></i>
                </div>
                <div class="p-10 space-y-10 flex-1 flex flex-col justify-center">
                    <div class="space-y-3">
                        <div class="flex justify-between items-end">
                            <span class="text-[10px] font-black uppercase tracking-widest text-slate-500">Awareness
                                (Total)</span>
                            <span class="text-sm font-black text-white">{{ $totalLeads }}</span>
                        </div>
                        <div class="h-6 bg-white/5 rounded-2xl overflow-hidden relative border border-white/5 p-1">
                            <div class="h-full bg-gradient-to-r from-indigo-600 to-indigo-400 rounded-xl"
                                style="width: 100%"></div>
                        </div>
                    </div>

                    <div class="space-y-3 pl-8">
                        <div class="flex justify-between items-end">
                            <span class="text-[10px] font-black uppercase tracking-widest text-slate-500">Consideration
                                (Active)</span>
                            <span class="text-sm font-black text-white">{{ $activeLeads }}</span>
                        </div>
                        <div class="h-6 bg-white/5 rounded-2xl overflow-hidden relative border border-white/5 p-1">
                            <div class="h-full bg-gradient-to-r from-emerald-600 to-emerald-400 rounded-xl"
                                style="width: {{ $totalLeads > 0 ? ($activeLeads / $totalLeads) * 100 : 0 }}%"></div>
                        </div>
                    </div>

                    <div class="space-y-3 pl-16">
                        <div class="flex justify-between items-end">
                            <span class="text-[10px] font-black uppercase tracking-widest text-slate-500">Conversion
                                (Closed)</span>
                            <span class="text-sm font-black text-white">{{ $convertedLeads }}</span>
                        </div>
                        <div class="h-6 bg-white/5 rounded-2xl overflow-hidden relative border border-white/5 p-1">
                            <div class="h-full bg-gradient-to-r from-amber-600 to-amber-400 rounded-xl"
                                style="width: {{ $totalLeads > 0 ? ($convertedLeads / $totalLeads) * 100 : 0 }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Deep Intelligence Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="card-premium p-6">
                <h4 class="text-[10px] font-black uppercase tracking-widest text-slate-500 mb-6">Sector Dominance</h4>
                <div class="space-y-4">
                    @forelse($topCategories as $cat)
                        <div
                            class="p-4 bg-white/5 rounded-2xl border border-white/5 flex items-center justify-between group hover:bg-white/10 transition-all cursor-default">
                            <div class="flex items-center gap-3">
                                <div class="w-2 h-2 rounded-full bg-indigo-500"></div>
                                <span class="text-xs font-bold text-slate-300">{{ $cat->name }}</span>
                            </div>
                            <span
                                class="text-[10px] font-black text-white bg-indigo-500/20 px-2 py-1 rounded-lg border border-indigo-500/20">{{ $cat->leads_count }}</span>
                        </div>
                    @empty
                        <p class="text-[10px] font-black text-slate-600 text-center py-8 uppercase tracking-widest">No sector
                            data</p>
                    @endforelse
                </div>
            </div>

            <div class="card-premium p-6">
                <h4 class="text-[10px] font-black uppercase tracking-widest text-slate-500 mb-6">Top Performers</h4>
                <div class="space-y-4">
                    <p class="text-[10px] font-black text-slate-600 text-center py-12 uppercase tracking-widest">Awaiting
                        Performance Data</p>
                </div>
            </div>

            <div class="card-premium p-6">
                <h4 class="text-[10px] font-black uppercase tracking-widest text-slate-500 mb-6">Recent Operations</h4>
                <div class="space-y-4">
                    <p class="text-[10px] font-black text-slate-600 text-center py-12 uppercase tracking-widest">System Idle
                    </p>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const chartOptions = {
                series: [{
                    name: 'Magnitude',
                    data: [{{ $totalLeads }}, {{ $activeLeads }}, {{ $convertedLeads }}, {{ $pendingLeads }}]
                }],
                chart: {
                    type: 'bar',
                    height: 300,
                    toolbar: { show: false },
                    foreColor: '#64748b'
                },
                plotOptions: {
                    bar: {
                        borderRadius: 12,
                        horizontal: false,
                        columnWidth: '50%',
                        distributed: true
                    }
                },
                dataLabels: { enabled: false },
                xaxis: {
                    categories: ['TOTAL', 'ACTIVE', 'CLOSED', 'PENDING'],
                    axisBorder: { show: false },
                    axisTicks: { show: false },
                    labels: {
                        style: {
                            fontSize: '10px',
                            fontWeight: 900,
                            fontFamily: 'Outfit'
                        }
                    }
                },
                grid: {
                    borderColor: 'rgba(255,255,255,0.05)',
                    strokeDashArray: 4
                },
                colors: ['#4f46e5', '#10b981', '#f59e0b', '#ef4444'],
                theme: { mode: 'dark' },
                tooltip: {
                    theme: 'dark',
                    y: {
                        formatter: function (val) { return val + " Units" }
                    }
                }
            };

            const chart = new ApexCharts(document.querySelector("#performanceChartTerminal"), chartOptions);
            chart.render();

            // Export logic
            document.getElementById('exportReport')?.addEventListener('click', async function () {
                const btn = this;
                const originalText = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-circle-notch animate-spin"></i> <span>Processing...</span>';

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
                        a.download = 'nexus-intelligence-report-{{ date("Y-m-d") }}.pdf';
                        document.body.appendChild(a);
                        a.click();
                        window.URL.revokeObjectURL(url);
                    } else {
                        throw new Error('Export failure');
                    }
                } catch (error) {
                    console.error('Export Error:', error);
                } finally {
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                }
            });
        });
    </script>
@endpush