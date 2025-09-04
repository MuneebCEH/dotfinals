@extends('layouts.app')

@section('title', 'User Details')
@section('page-title', 'User Details')

@section('content')
<div class="space-y-8 animate-on-load">
    <!-- Header -->
    <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl shadow-2xl rounded-2xl p-8 border border-gray-200/50 dark:border-gray-700/50">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">User Details</h2>
                <p class="text-lg text-gray-600 dark:text-gray-400">View user information and recent activity.</p>
            </div>
            <div class="flex items-center space-x-4">
                <a href="{{ route('users.edit', $user) }}" class="inline-flex items-center px-6 py-3 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 bg-white/80 dark:bg-gray-700/80 backdrop-blur-xl font-semibold rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition-all duration-200">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    Edit User
                </a>
                <a href="{{ route('users.index') }}" class="inline-flex items-center px-6 py-3 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 bg-white/80 dark:bg-gray-700/80 backdrop-blur-xl font-semibold rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition-all duration-200">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back to Users
                </a>
            </div>
        </div>
    </div>

    <!-- User Information -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- User Profile Card -->
        <div class="lg:col-span-1">
            <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl shadow-2xl rounded-2xl border border-gray-200/50 dark:border-gray-700/50">
                <div class="px-8 py-6 border-b border-gray-200/50 dark:border-gray-700/50">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white">Profile Information</h3>
                </div>
                
                <div class="p-8">
                    <div class="flex items-center mb-6">
                        <div class="w-20 h-20 bg-gradient-to-br from-primary-500 to-primary-600 rounded-xl flex items-center justify-center shadow-lg">
                            <span class="text-2xl font-bold text-white">
                                {{ substr($user->name ?? 'U', 0, 1) }}
                            </span>
                        </div>
                        <div class="ml-6">
                            <h4 class="text-xl font-bold text-gray-900 dark:text-white">{{ $user->name }}</h4>
                            <p class="text-gray-600 dark:text-gray-400">{{ $user->email }}</p>
                            @if($user->is_admin)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary-100 text-primary-800 dark:bg-primary-900 dark:text-primary-200">
                                    Admin
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="space-y-4">
                        @if($user->phone)
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">Phone</label>
                            <p class="text-gray-900 dark:text-white">{{ $user->phone }}</p>
                        </div>
                        @endif

                        @if($user->company)
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">Company</label>
                            <p class="text-gray-900 dark:text-white">{{ $user->company }}</p>
                        </div>
                        @endif

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">Member Since</label>
                            <p class="text-gray-900 dark:text-white">{{ $user->created_at->format('M d, Y') }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">Last Updated</label>
                            <p class="text-gray-900 dark:text-white">{{ $user->updated_at->format('M d, Y') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Leads -->
        <div class="lg:col-span-2">
            <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl shadow-2xl rounded-2xl border border-gray-200/50 dark:border-gray-700/50">
                <div class="px-8 py-6 border-b border-gray-200/50 dark:border-gray-700/50">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white">Recent Leads</h3>
                </div>
                
                <div class="p-8">
                    @if($user->leads->count() > 0)
                        <div class="space-y-4">
                            @foreach($user->leads->take(10) as $lead)
                            <div class="flex items-center justify-between p-4 bg-gray-50/50 dark:bg-gray-700/50 rounded-xl border border-gray-200/50 dark:border-gray-600/50">
                                <div class="flex-1">
                                    <h4 class="font-semibold text-gray-900 dark:text-white">{{ $lead->name }}</h4>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ $lead->email }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-500">{{ $lead->created_at->format('M d, Y') }}</p>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        @if(($lead->status ?? '') === 'active') bg-success-100 text-success-800 dark:bg-success-900 dark:text-success-200
                                        @elseif(($lead->status ?? '') === 'converted') bg-warning-100 text-warning-800 dark:bg-warning-900 dark:text-warning-200
                                        @elseif(($lead->status ?? '') === 'closed') bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200
                                        @else bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200 @endif">
                                        {{ ucfirst($lead->status ?? 'pending') }}
                                    </span>
                                    <a href="{{ route('leads.show', $lead) }}" class="text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                        </svg>
                                    </a>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        
                        @if($user->leads->count() > 10)
                        <div class="mt-6 text-center">
                            <a href="{{ route('leads.index', ['user' => $user->id]) }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300">
                                View all {{ $user->leads->count() }} leads
                                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </a>
                        </div>
                        @endif
                    @else
                        <div class="text-center py-8">
                            <svg class="w-16 h-16 mx-auto text-gray-400 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No leads yet</h3>
                            <p class="text-gray-600 dark:text-gray-400">This user hasn't created any leads yet.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 