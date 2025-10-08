@extends('layouts.app')

@section('title', 'Create User')
@section('page-title', 'Create User')

@section('content')
<div class="space-y-8 animate-on-load">
    <!-- Header -->
    <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl shadow-2xl rounded-2xl p-8 border border-gray-200/50 dark:border-gray-700/50">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">Create New User</h2>
                <p class="text-lg text-gray-600 dark:text-gray-400">Add a new user to the system.</p>
            </div>
            <a href="{{ route('users.index') }}" class="inline-flex items-center px-6 py-3 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 bg-white/80 dark:bg-gray-700/80 backdrop-blur-xl font-semibold rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition-all duration-200">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Users
            </a>
        </div>
    </div>

    <!-- Create User Form -->
    <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl shadow-2xl rounded-2xl border border-gray-200/50 dark:border-gray-700/50">
        <div class="px-8 py-6 border-b border-gray-200/50 dark:border-gray-700/50">
            <h3 class="text-xl font-bold text-gray-900 dark:text-white">User Information</h3>
        </div>
        
        <form action="{{ route('users.store') }}" method="POST" class="p-8 space-y-8">
            @csrf
            
            <!-- Basic Information -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div>
                    <label for="name" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Full Name *</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required class="block w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white/80 dark:bg-gray-700/80 backdrop-blur-xl text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 transition-all duration-200" placeholder="Enter full name">
                    @error('name')
                        <p class="mt-2 text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Email Address *</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required class="block w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white/80 dark:bg-gray-700/80 backdrop-blur-xl text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 transition-all duration-200" placeholder="Enter email address">
                    @error('email')
                        <p class="mt-2 text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Role and Super Agent -->
                <div>
                    <label for="role" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">User Role *</label>
                    <select id="role" name="role" required class="block w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white/80 dark:bg-gray-700/80 backdrop-blur-xl text-gray-900 dark:text-white transition-all duration-200">
                        <option value="user" {{ old('role') === 'user' ? 'selected' : '' }}>User</option>
                        <option value="closer" {{ old('role') === 'closer' ? 'selected' : '' }}>Closer</option>
                        <option value="super_agent" {{ old('role') === 'super_agent' ? 'selected' : '' }}>Super Agent</option>
                        <option value="report_manager" {{ old('role') === 'report_manager' ? 'selected' : '' }}>Report Manager</option>
                        <option value="lead_manager" {{ old('role') === 'lead_manager' ? 'selected' : '' }}>Lead Manager</option>
                        <option value="max_out" {{ old('role') === 'max_out' ? 'selected' : '' }}>Max Out</option>
                        <option value="that_submitted" {{ old('role') === 'that_submitted' ? 'selected' : '' }}>That Submitted</option>
                        <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                    </select>
                    @error('role')
                        <p class="mt-2 text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Password *</label>
                    <input type="password" id="password" name="password" required class="block w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white/80 dark:bg-gray-700/80 backdrop-blur-xl text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 transition-all duration-200" placeholder="Enter password">
                    @error('password')
                        <p class="mt-2 text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Confirm Password *</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required class="block w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white/80 dark:bg-gray-700/80 backdrop-blur-xl text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 transition-all duration-200" placeholder="Confirm password">
                </div>
            </div>

            <!-- Additional Information -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div>
                    <label for="phone" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Phone Number</label>
                    <input type="tel" id="phone" name="phone" value="{{ old('phone') }}" class="block w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white/80 dark:bg-gray-700/80 backdrop-blur-xl text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 transition-all duration-200" placeholder="Enter phone number">
                    @error('phone')
                        <p class="mt-2 text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="company" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Company</label>
                    <input type="text" id="company" name="company" value="{{ old('company') }}" class="block w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white/80 dark:bg-gray-700/80 backdrop-blur-xl text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 transition-all duration-200" placeholder="Enter company name">
                    @error('company')
                        <p class="mt-2 text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200/50 dark:border-gray-700/50">
                <a href="{{ route('users.index') }}" class="inline-flex items-center px-6 py-3 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 bg-white/80 dark:bg-gray-700/80 backdrop-blur-xl font-semibold rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition-all duration-200">
                    Cancel
                </a>
                <button type="submit" class="inline-flex items-center px-8 py-3 bg-gradient-to-r from-primary-500 to-primary-600 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition-all duration-200">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Create User
                </button>
            </div>
        </form>
    </div>
</div>
@endsection 