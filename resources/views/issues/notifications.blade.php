@extends('layouts.app')

@section('title', 'Notifications')
@section('page-title', 'Notifications')

@section('content')
<div class="max-w-5xl   mx-auto space-y-6 animate-on-load">

    <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl shadow-2xl rounded-2xl border border-gray-200/50 dark:border-gray-700/50 overflow-hidden">
        <div class="px-8 py-6 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-primary-500 to-primary-600 flex items-center justify-center shadow-lg">
                    <svg class="w-7 h-7 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V4a2 2 0 10-4 0v1.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Notifications</h1>
                    <p class="text-gray-600 dark:text-gray-400">New activity arrives here.</p>
                </div>
            </div>
            <form method="POST" action="{{ route('rm.notifications.readAll') }}">
                @csrf
                <button class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 bg-white/80 dark:bg-gray-700/80 hover:shadow-md transition">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M5 13l4 4L19 7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    Mark all as read
                </button>
            </form>
        </div>

        <div class="px-8 pb-6">
            <div id="notif-list" class="space-y-3">
                @forelse ($notifications as $n)
                    @php $data = $n->data; @endphp
                    <a href="{{ $data['url'] ?? '#' }}"
                       class="block rounded-xl border border-gray-200/70 dark:border-gray-700/70 p-4 hover:bg-gray-50/80 dark:hover:bg-gray-700/40 transition">
                        <div class="flex items-center justify-between">
                            <div class="text-sm text-gray-600 dark:text-gray-400">
                                {{ $n->created_at->diffForHumans() }}
                            </div>
                            @if(is_null($n->read_at))
                                <span class="inline-flex items-center px-2 py-0.5 text-xs rounded-full bg-red-100 text-red-700">New</span>
                            @endif
                        </div>
                        <div class="mt-1 font-semibold text-gray-900 dark:text-white">
                            {{ $data['type'] ?? 'Notification' }}
                        </div>
                        @if(!empty($data['title']))
                            <div class="mt-1 text-gray-800 dark:text-gray-200">
                                Issue: {{ $data['title'] }} <span class="text-gray-500">({{ ucfirst($data['priority'] ?? '') }})</span>
                            </div>
                        @endif
                    </a>
                @empty
                    <div class="text-center py-10 text-gray-500">No notifications yet.</div>
                @endforelse
            </div>
        </div>
    </div>

</div>
@endsection
