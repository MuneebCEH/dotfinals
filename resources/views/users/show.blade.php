{{-- resources/views/users/show.blade.php --}}
@extends('layouts.app')

@section('title', 'Agent Dossier')
@section('page-title', 'Agent Dossier')

@section('content')
<div class="space-y-6 animate-on-load">
    {{-- Header --}}
    <div class="card-premium p-8 flex flex-col md:flex-row md:items-center justify-between gap-6 overflow-hidden relative">
        <div class="relative z-10 flex items-center gap-6">
            <div class="w-20 h-20 rounded-3xl overflow-hidden border-2 border-indigo-500/30 shadow-2xl">
                <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=6366f1&color=fff&bold=true&size=128" class="w-full h-full object-cover">
            </div>
            <div>
                <h1 class="text-4xl font-black tracking-tight text-white mb-2">{{ $user->name }}</h1>
                <div class="flex items-center gap-3">
                    <span class="px-3 py-1 bg-indigo-500/10 text-indigo-400 rounded-xl text-[10px] font-black uppercase tracking-wider border border-indigo-500/20">
                        {{ str_replace('_', ' ', $user->role) }}
                    </span>
                    <span class="text-slate-500 text-xs font-bold">{{ $user->email }}</span>
                </div>
            </div>
        </div>
        
        <div class="flex items-center gap-3 relative z-10">
            <a href="{{ route('users.edit', $user) }}" 
               class="px-8 py-4 bg-indigo-600 hover:bg-indigo-500 text-white rounded-2xl font-black transition-all shadow-xl shadow-indigo-600/20 flex items-center gap-3 group">
                <i class="fas fa-edit group-hover:rotate-12 transition-transform"></i>
                <span>Modify Credentials</span>
            </a>
            <a href="{{ route('users.index') }}" 
               class="px-8 py-4 bg-white/5 hover:bg-white/10 text-white rounded-2xl font-black transition-all border border-white/10">
                <span>Return to Matrix</span>
            </a>
        </div>

        <div class="absolute -right-20 -top-20 w-64 h-64 bg-indigo-500/10 blur-[100px] rounded-full"></div>
    </div>

    {{-- Detail Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Profile Intelligence --}}
        <div class="lg:col-span-1 space-y-6">
            <div class="card-premium p-8 space-y-8">
                <h3 class="text-[10px] font-black uppercase tracking-widest text-slate-500 border-b border-white/5 pb-4">Profile Intelligence</h3>
                
                <div class="space-y-6">
                    <div class="space-y-1">
                        <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Comm Link</p>
                        <p class="text-sm font-bold text-white">{{ $user->phone ?? 'Not Protocolized' }}</p>
                    </div>
                    <div class="space-y-1">
                        <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Affiliation</p>
                        <p class="text-sm font-bold text-white">{{ $user->company ?? 'Independent Agent' }}</p>
                    </div>
                    <div class="space-y-1">
                        <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest">System Integration</p>
                        <p class="text-sm font-bold text-white">{{ $user->created_at->format('M d, Y') }}</p>
                    </div>
                    <div class="space-y-1">
                        <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Last Access Pulse</p>
                        <p class="text-sm font-bold text-white">{{ $user->updated_at->diffForHumans() }}</p>
                    </div>
                </div>

                @if($user->is_admin)
                    <div class="p-4 bg-rose-500/10 rounded-2xl border border-rose-500/20 flex items-center gap-4">
                        <i class="fas fa-shield-alt text-rose-500"></i>
                        <span class="text-[10px] font-black text-rose-400 uppercase tracking-widest">Level 10 Global Admin Clearance</span>
                    </div>
                @endif
            </div>

            {{-- Stat Insight --}}
            <div class="card-premium p-8 bg-indigo-500/5 border-indigo-500/20">
                <div class="text-center space-y-4">
                    <p class="text-[10px] font-black text-indigo-400 uppercase tracking-widest">Asset Management</p>
                    <p class="text-5xl font-black text-white">{{ $user->leads->count() }}</p>
                    <p class="text-xs font-bold text-slate-400">Total Leads Assigned</p>
                </div>
            </div>
        </div>

        {{-- Managed Assets Stream --}}
        <div class="lg:col-span-2">
            <div class="card-premium flex flex-col h-full overflow-hidden">
                <div class="p-6 border-b border-white/5 bg-white/[0.02] flex items-center justify-between">
                    <h3 class="text-[10px] font-black uppercase tracking-widest text-slate-500">Managed Assets Stream</h3>
                    <i class="fas fa-stream text-slate-600 text-[10px]"></i>
                </div>
                
                <div class="flex-1 overflow-y-auto">
                    @forelse($user->leads->take(15) as $lead)
                        <div class="p-6 border-b border-white/5 hover:bg-white/[0.03] transition-all group flex items-center justify-between gap-4">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-xl bg-white/5 flex items-center justify-center text-slate-400 border border-white/10 group-hover:border-indigo-500/30 transition-all">
                                    <i class="fas fa-briefcase text-xs"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-black text-white group-hover:text-indigo-400 transition-colors">{{ $lead->name }}</p>
                                    <p class="text-[10px] font-bold text-slate-500 uppercase tracking-tighter">{{ $lead->email }}</p>
                                </div>
                            </div>
                            
                            <div class="flex items-center gap-6">
                                <span class="px-3 py-1 rounded-xl text-[10px] font-black uppercase tracking-wider bg-slate-500/10 text-slate-400 border border-slate-500/20">
                                    {{ $lead->status ?? 'Unclassified' }}
                                </span>
                                <a href="{{ route('leads.show', $lead) }}" class="w-8 h-8 flex items-center justify-center bg-white/5 hover:bg-white/10 rounded-lg text-slate-400 hover:text-white transition-all">
                                    <i class="fas fa-external-link-alt text-[10px]"></i>
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="flex flex-col items-center justify-center h-full py-20 text-center space-y-6">
                            <div class="w-20 h-20 bg-white/5 rounded-full flex items-center justify-center text-slate-700">
                                <i class="fas fa-folder-open text-4xl"></i>
                            </div>
                            <div>
                                <h4 class="text-sm font-black text-slate-500 uppercase tracking-widest">No Managed Assets</h4>
                                <p class="text-[10px] font-medium text-slate-600">This agent has not yet been assigned any leads.</p>
                            </div>
                        </div>
                    @endforelse
                </div>

                @if($user->leads->count() > 15)
                    <div class="p-4 bg-black/20 text-center">
                        <a href="{{ route('leads.index', ['user' => $user->id]) }}" class="text-[10px] font-black uppercase tracking-widest text-indigo-400 hover:text-indigo-300 transition-colors">
                            View All {{ $user->leads->count() }} Assets
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection