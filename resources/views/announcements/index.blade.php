@extends('layouts.app')

@php
    $pageTitle = $isAdmin ? 'Announcement Room' : 'Notifications';
@endphp

@section('title', $pageTitle)
@section('page-title', $pageTitle)

@section('content')
<div class="container mx-auto px-4 py-8">
    @if($isAdmin)
        {{-- Admin Posting Area --}}
        <div class="card-premium p-8 mb-8 animate-fade-in">
            <div class="flex items-center gap-4 mb-6">
                <div class="w-12 h-12 rounded-2xl bg-primary/10 flex items-center justify-center text-primary text-2xl">
                    <i class="fas fa-bullhorn"></i>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-slate-900">Broadcast New Announcement</h3>
                    <p class="text-sm text-slate-500">Share updates with the entire team or specific individuals.</p>
                </div>
            </div>

            <form action="{{ route('announcements.store') }}" method="POST" class="space-y-6">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="text-xs font-bold uppercase tracking-widest text-slate-500 ml-1">Announcement Title</label>
                        <input type="text" name="title" required placeholder="Important Update..."
                            class="w-full px-5 py-4 rounded-2xl border border-slate-200 focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all">
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold uppercase tracking-widest text-slate-500 ml-1">Target Audience</label>
                        <select name="recipient_id" class="w-full px-5 py-4 rounded-2xl border border-slate-200 focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all">
                            <option value="">Broadcast to Everyone (All Users)</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->role }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="md:col-span-2 space-y-2">
                        <label class="text-xs font-bold uppercase tracking-widest text-slate-500 ml-1">Message Content</label>
                        <textarea name="message" rows="4" required placeholder="Type your announcement here..."
                            class="w-full px-5 py-4 rounded-2xl border border-slate-200 focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all"></textarea>
                    </div>
                    <div class="space-y-6">
                        <div class="space-y-2">
                            <label class="text-xs font-bold uppercase tracking-widest text-slate-500 ml-1">Context Type</label>
                            <div class="grid grid-cols-2 gap-3">
                                <label class="cursor-pointer">
                                    <input type="radio" name="type" value="info" checked class="peer sr-only">
                                    <div class="p-3 text-center rounded-xl border-2 border-slate-100 peer-checked:border-blue-500 peer-checked:bg-blue-50 text-slate-600 transition-all font-bold text-xs uppercase tracking-tighter">
                                        <i class="fas fa-info-circle mb-1 block text-lg"></i> Info
                                    </div>
                                </label>
                                <label class="cursor-pointer">
                                    <input type="radio" name="type" value="success" class="peer sr-only">
                                    <div class="p-3 text-center rounded-xl border-2 border-slate-100 peer-checked:border-green-500 peer-checked:bg-green-50 text-slate-600 transition-all font-bold text-xs uppercase tracking-tighter">
                                        <i class="fas fa-check-circle mb-1 block text-lg"></i> Success
                                    </div>
                                </label>
                                <label class="cursor-pointer">
                                    <input type="radio" name="type" value="warning" class="peer sr-only">
                                    <div class="p-3 text-center rounded-xl border-2 border-slate-100 peer-checked:border-amber-500 peer-checked:bg-amber-50 text-slate-600 transition-all font-bold text-xs uppercase tracking-tighter">
                                        <i class="fas fa-exclamation-triangle mb-1 block text-lg"></i> Alert
                                    </div>
                                </label>
                                <label class="cursor-pointer">
                                    <input type="radio" name="type" value="danger" class="peer sr-only">
                                    <div class="p-3 text-center rounded-xl border-2 border-slate-100 peer-checked:border-rose-500 peer-checked:bg-rose-50 text-slate-600 transition-all font-bold text-xs uppercase tracking-tighter">
                                        <i class="fas fa-radiation mb-1 block text-lg"></i> Urgent
                                    </div>
                                </label>
                            </div>
                        </div>

                        <button type="submit" class="w-full py-4 bg-primary hover:bg-primary-hover text-white rounded-2xl font-black uppercase tracking-widest text-xs transition-all shadow-lg shadow-primary/20 active:scale-95">
                            Post Announcement
                        </button>
                    </div>
                </div>
            </form>
        </div>
    @endif

    {{-- Announcement Feed --}}
    <div class="space-y-6">
        <div class="flex items-center justify-between mb-2">
            <h4 class="text-sm font-black uppercase tracking-[0.3em] text-slate-500">Live Feed</h4>
            <div class="h-px flex-1 mx-6 bg-slate-200"></div>
        </div>

        @forelse($announcements as $item)
            <div class="card-premium relative overflow-hidden animate-slide-up group" style="animation-delay: {{ $loop->index * 0.1 }}s">
                {{-- Type Accent --}}
                @php
                    $classes = [
                        'info' => ['border' => 'border-l-blue-500', 'bg' => 'bg-blue-500', 'icon' => 'fa-info-circle', 'text' => 'text-blue-600'],
                        'success' => ['border' => 'border-l-green-500', 'bg' => 'bg-green-500', 'icon' => 'fa-check-circle', 'text' => 'text-green-600'],
                        'warning' => ['border' => 'border-l-amber-500', 'bg' => 'bg-amber-500', 'icon' => 'fa-exclamation-triangle', 'text' => 'text-amber-600'],
                        'danger' => ['border' => 'border-l-rose-500', 'bg' => 'bg-rose-500', 'icon' => 'fa-radiation', 'text' => 'text-rose-600'],
                    ][$item->type] ?? ['border' => 'border-l-slate-500', 'bg' => 'bg-slate-500', 'icon' => 'fa-bell', 'text' => 'text-slate-600'];
                @endphp
                
                <div class="absolute left-0 top-0 bottom-0 w-1.5 {{ $classes['bg'] }}"></div>

                <div class="p-6 md:p-8 flex flex-col md:flex-row gap-6 items-start">
                    <div class="w-14 h-14 rounded-2xl {{ $classes['bg'] }}/10 flex items-center justify-center {{ $classes['text'] }} text-2xl shrink-0 group-hover:scale-110 transition-transform duration-500">
                        <i class="fas {{ $classes['icon'] }}"></i>
                    </div>

                    <div class="flex-1 space-y-3">
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-2">
                            <h5 class="text-xl font-black text-slate-900 tracking-tight">{{ $item->title }}</h5>
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest flex items-center gap-2">
                                <i class="far fa-clock"></i> {{ $item->created_at->diffForHumans() }}
                            </span>
                        </div>
                        
                        <p class="text-slate-600 leading-relaxed text-lg whitespace-pre-wrap">{{ $item->message }}</p>

                        <div class="pt-4 flex flex-wrap items-center gap-4 border-t border-slate-100 mt-4">
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 rounded-full bg-slate-200 flex items-center justify-center text-[10px] font-bold">
                                    {{ strtoupper(substr($item->sender->name, 0, 1)) }}
                                </div>
                                <span class="text-[10px] font-black uppercase text-slate-500">Sent by {{ $item->sender->name }}</span>
                            </div>

                            @if($item->recipient)
                                <span class="px-2 py-1 rounded bg-indigo-50 text-indigo-600 text-[9px] font-black uppercase tracking-tighter">
                                    <i class="fas fa-lock text-[8px] mr-1"></i> Private: To {{ $item->recipient->name }}
                                </span>
                            @else
                                <span class="px-2 py-1 rounded bg-slate-100 text-slate-500 text-[9px] font-black uppercase tracking-tighter">
                                    <i class="fas fa-globe-americas text-[8px] mr-1"></i> Public Broadcast
                                </span>
                            @endif

                            @if($isAdmin)
                                <div class="flex items-center gap-2 ml-auto">
                                    <form action="{{ route('announcements.toggle', $item) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="px-3 py-1.5 rounded-lg border {{ $item->is_active ? 'border-amber-200 text-amber-600 hover:bg-amber-50' : 'border-green-200 text-green-600 hover:bg-green-50' }} text-[10px] font-black uppercase tracking-tighter transition-colors">
                                            {{ $item->is_active ? 'Disable' : 'Enable' }}
                                        </button>
                                    </form>
                                    <form action="{{ route('announcements.destroy', $item) }}" method="POST" onsubmit="return confirm('Archive this announcement?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-3 py-1.5 rounded-lg border border-red-200 text-red-600 hover:bg-red-50 text-[10px] font-black uppercase tracking-tighter transition-colors">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="card-premium p-12 text-center">
                <div class="w-20 h-20 mx-auto rounded-3xl bg-slate-50 flex items-center justify-center text-slate-300 text-4xl mb-4">
                    <i class="fas fa-inbox"></i>
                </div>
                <h5 class="text-xl font-bold text-slate-900">Quiet for now...</h5>
                <p class="text-slate-500">Check back later for important team updates.</p>
            </div>
        @endforelse

        <div class="mt-8">
            {{ $announcements->links() }}
        </div>
    </div>
</div>

<style>
    .card-premium {
        background: white;
        border: 1px solid rgba(0,0,0,0.05);
        border-radius: 24px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.02);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .card-premium:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 40px rgba(0,0,0,0.05);
        border-color: rgba(0,191,99,0.1);
    }
</style>
@endsection
