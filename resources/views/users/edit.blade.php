@extends('layouts.app')

@section('title', 'Edit User')
@section('page-title', 'Edit User')

@section('content')
<div class="space-y-8 animate-on-load">
    <!-- Header -->
    <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl shadow-2xl rounded-2xl p-8 border border-gray-200/50 dark:border-gray-700/50">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">Edit User</h2>
                <p class="text-lg text-gray-600 dark:text-gray-400">Update user information and permissions.</p>
            </div>
            <a href="{{ route('users.index') }}" class="inline-flex items-center px-6 py-3 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 bg-white/80 dark:bg-gray-700/80 backdrop-blur-xl font-semibold rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition-all duration-200">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Users
            </a>
        </div>
    </div>

    <!-- Edit User Form -->
    <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl shadow-2xl rounded-2xl border border-gray-200/50 dark:border-gray-700/50">
        <div class="px-8 py-6 border-b border-gray-200/50 dark:border-gray-700/50">
            <h3 class="text-xl font-bold text-gray-900 dark:text-white">User Information</h3>
        </div>
        
        <form action="{{ route('users.update', $user) }}" method="POST" class="p-8 space-y-8">
            @csrf
            @method('PUT')
            
            <!-- Basic Information -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div>
                    <label for="name" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Full Name *</label>
                    <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required class="block w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white/80 dark:bg-gray-700/80 backdrop-blur-xl text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 transition-all duration-200" placeholder="Enter full name">
                    @error('name')
                        <p class="mt-2 text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Email Address *</label>
                    <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required class="block w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white/80 dark:bg-gray-700/80 backdrop-blur-xl text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 transition-all duration-200" placeholder="Enter email address">
                    @error('email')
                        <p class="mt-2 text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Role and Password -->
                <div>
                    <label for="role" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">User Role *</label>
                    <select id="role" name="role" required class="block w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white/80 dark:bg-gray-700/80 backdrop-blur-xl text-gray-900 dark:text-white transition-all duration-200">
                        <option value="user" {{ (old('role', $user->role) === 'user') ? 'selected' : '' }}>User</option>
                        <option value="closer" {{ (old('role', $user->role) === 'closer') ? 'selected' : '' }}>Closer</option>
                        <option value="super_agent" {{ (old('role', $user->role) === 'super_agent') ? 'selected' : '' }}>Super Agent</option>
                        <option value="report_manager" {{ (old('role', $user->role) === 'report_manager') ? 'selected' : '' }}>Report Manager</option>
                        <option value="lead_manager" {{ (old('role', $user->role) === 'lead_manager') ? 'selected' : '' }}>Lead Manager</option>
                        <option value="max_out" {{ (old('role', $user->role) === 'max_out') ? 'selected' : '' }}>Max Out</option>
                        <option value="that_submitted" {{ (old('role', $user->role) === 'that_submitted') ? 'selected' : '' }}>That Submitted</option>
                        <option value="admin" {{ (old('role', $user->role) === 'admin') ? 'selected' : '' }}>Admin</option>
                    </select>
                    @error('role')
                        <p class="mt-2 text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Password <span class="text-gray-500">(optional)</span></label>
                    <input type="password" id="password" name="password" class="block w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white/80 dark:bg-gray-700/80 backdrop-blur-xl text-gray-900 dark:text-white transition-all duration-200" placeholder="Leave blank to keep current">
                    @error('password')
                        <p class="mt-2 text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Additional Information -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div>
                    <label for="phone" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Phone Number</label>
                    <input type="tel" id="phone" name="phone" value="{{ old('phone', $user->phone) }}" class="block w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white/80 dark:bg-gray-700/80 backdrop-blur-xl text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 transition-all duration-200" placeholder="Enter phone number">
                    @error('phone')
                        <p class="mt-2 text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="company" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Company</label>
                    <input type="text" id="company" name="company" value="{{ old('company', $user->company) }}" class="block w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white/80 dark:bg-gray-700/80 backdrop-blur-xl text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 transition-all duration-200" placeholder="Enter company name">
                    @error('company')
                        <p class="mt-2 text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Admin Role -->
            <div>
                <div class="flex items-center">
                    <input id="is_admin" name="is_admin" type="checkbox" {{ old('is_admin', $user->is_admin) ? 'checked' : '' }} class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded transition-colors">
                    <label for="is_admin" class="ml-3 block text-sm text-gray-700 dark:text-gray-300">
                        Grant admin privileges
                    </label>
                </div>
                @error('is_admin')
                    <p class="mt-2 text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200/50 dark:border-gray-700/50">
                <a href="{{ route('users.index') }}" class="inline-flex items-center px-6 py-3 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 bg-white/80 dark:bg-gray-700/80 backdrop-blur-xl font-semibold rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition-all duration-200">
                    Cancel
                </a>
                <button type="submit" class="inline-flex items-center px-8 py-3 bg-gradient-to-r from-primary-500 to-primary-600 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition-all duration-200">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Update User
                </button>
            </div>
        </form>
    </div>
</div>
@endsection 