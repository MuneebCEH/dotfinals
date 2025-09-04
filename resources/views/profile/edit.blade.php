@extends('layouts.app')

@section('title', 'Edit Profile')
@section('page-title', 'Edit Profile')

@section('content')
    <div class="space-y-8 animate-on-load" x-data="profileTabs()" x-init="init()"
        x-on:hashchange.window="onHashChange()">

        {{-- Header --}}
        <div
            class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl shadow-2xl rounded-2xl p-8 border border-gray-200/50 dark:border-gray-700/50">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">Edit Profile</h2>
                    <p class="text-lg text-gray-600 dark:text-gray-400">Update your account information and password.</p>
                </div>
                <a href="{{ route('dashboard') }}"
                    class="inline-flex items-center px-6 py-3 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 bg-white/80 dark:bg-gray-700/80 backdrop-blur-xl font-semibold rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition-all duration-200">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back to Dashboard
                </a>
            </div>
        </div>

        {{-- Global Success flashes --}}
        {{-- @if (session('status') === 'profile-updated')
            <div class="p-4 rounded-xl border border-green-200 bg-green-50 text-green-800">Profile updated.</div>
        @endif
        @if (session('status') === 'password-updated')
            <div class="p-4 rounded-xl border border-green-200 bg-green-50 text-green-800">Password updated.</div>
        @endif --}}

        {{-- Card with Tabs --}}
        <div
            class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl shadow-2xl rounded-2xl border border-gray-200/50 dark:border-gray-700/50">

            {{-- Tabs Header --}}
            <div class="px-6 pt-6">
                <div class="flex gap-2" role="tablist" aria-label="Profile tabs">
                    <button :class="tabBtn('info')" @click="setTab('info')" id="tab-info" type="button"
                        aria-controls="panel-info" :aria-selected="active === 'info'" role="tab">
                        <svg class="w-4 h-4" :class="iconClass('info')" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M5.121 17.804A13.937 13.937 0 0112 15c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span>User Information</span>
                        {{-- <span x-show="active === 'info'" class="block h-0.5 w-full bg-white/70 rounded-b mt-1"></span> --}}
                        <span x-show="active === 'info'"></span>
                    </button>

                    @if (auth()->user()->role === 'admin')
                        <button :class="tabBtn('password')" @click="setTab('password')" id="tab-password" type="button"
                            aria-controls="panel-password" :aria-selected="active === 'password'" role="tab">
                            <svg class="w-4 h-4" :class="iconClass('password')" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 11c.828 0 1.5.672 1.5 1.5S12.828 14 12 14s-1.5-.672-1.5-1.5S11.172 11 12 11z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 8V6a5 5 0 10-10 0v2M5 10h14v9a2 2 0 01-2 2H7a2 2 0 01-2-2v-9z" />
                            </svg>
                            <span>Change Password</span>
                            <span x-show="active === 'password'"></span>
                        </button>
                    @endif
                </div>
            </div>

            {{-- Divider under tabs --}}
            <div class="px-6 pt-3">
                <div class="h-px w-full bg-gray-200/70 dark:bg-gray-700/70"></div>
            </div>

            {{-- Panels --}}
            <div class="p-6">

                {{-- Panel: User Information --}}
                <section x-show="active === 'info'" x-cloak id="panel-info" role="tabpanel" aria-labelledby="tab-info"
                    class="space-y-8">

                    <div class="border border-gray-200/50 dark:border-gray-700/50 rounded-2xl overflow-hidden">
                        <div class="px-8 py-6 border-b border-gray-200/50 dark:border-gray-700/50">
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white">Profile Information</h3>
                        </div>

                        <form action="{{ route('profile.update') }}" method="POST" class="p-8 space-y-8">
                            @csrf
                            @method('PUT')

                            {{-- Profile header --}}
                            <div class="flex items-center space-x-8">
                                <div
                                    class="w-24 h-24 bg-gradient-to-br from-primary-500 to-primary-600 rounded-xl flex items-center justify-center shadow-lg">
                                    <span class="text-3xl font-bold text-white">
                                        {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}
                                    </span>
                                </div>
                                <div>
                                    <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-1">{{ $user->name }}
                                    </h4>
                                    <p class="text-gray-600 dark:text-gray-400">{{ $user->email }}</p>
                                </div>
                            </div>

                            {{-- Basic Information --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                <div>
                                    <label for="name"
                                        class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">
                                        Full Name *
                                    </label>
                                    <input type="text" id="name" name="name"
                                        value="{{ old('name', $user->name) }}" required
                                        class="block w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white/80 dark:bg-gray-700/80 backdrop-blur-xl text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 transition-all duration-200"
                                        placeholder="Enter your full name">
                                    @error('name')
                                        <p class="text-danger-600 text-sm mt-2">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="email"
                                        class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">
                                        Email Address *
                                    </label>
                                    <input type="email" id="email" name="email"
                                        value="{{ old('email', $user->email) }}" required
                                        class="block w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white/80 dark:bg-gray-700/80 backdrop-blur-xl text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 transition-all duration-200"
                                        placeholder="Enter your email address">
                                    @error('email')
                                        <p class="text-danger-600 text-sm mt-2">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            {{-- Additional Information --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                <div>
                                    <label for="phone"
                                        class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">
                                        Phone Number
                                    </label>
                                    <input type="tel" id="phone" name="phone"
                                        value="{{ old('phone', $user->phone) }}"
                                        class="block w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white/80 dark:bg-gray-700/80 backdrop-blur-xl text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 transition-all duration-200"
                                        placeholder="Enter your phone number">
                                    @error('phone')
                                        <p class="text-danger-600 text-sm mt-2">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="company"
                                        class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">
                                        Company
                                    </label>
                                    <input type="text" id="company" name="company"
                                        value="{{ old('company', $user->company) }}"
                                        class="block w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white/80 dark:bg-gray-700/80 backdrop-blur-xl text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 transition-all duration-200"
                                        placeholder="Enter your company name">
                                    @error('company')
                                        <p class="text-danger-600 text-sm mt-2">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            {{-- Actions --}}
                            <div
                                class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200/50 dark:border-gray-700/50">
                                <a href="{{ route('dashboard') }}"
                                    class="inline-flex items-center px-6 py-3 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 bg-white/80 dark:bg-gray-700/80 backdrop-blur-xl font-semibold rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition-all duration-200">
                                    Cancel
                                </a>
                                <button type="submit"
                                    class="inline-flex items-center px-8 py-3 bg-gradient-to-r from-primary-500 to-primary-600 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition-all duration-200">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    Update Profile
                                </button>
                            </div>
                        </form>
                    </div>
                </section>

                {{-- Panel: Change Password --}}
                @if (auth()->user()->role === 'admin')
                    <section x-show="active === 'password'" x-cloak id="panel-password" role="tabpanel"
                        aria-labelledby="tab-password" class="space-y-8">

                        <div class="border border-gray-200/50 dark:border-gray-700/50 rounded-2xl overflow-hidden">
                            <div class="px-8 py-6 border-b border-gray-200/50 dark:border-gray-700/50">
                                <h3 class="text-xl font-bold text-gray-900 dark:text-white">Change Password</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                    Use a strong password you haven’t used elsewhere.
                                </p>
                            </div>

                            <form action="{{ route('profile.updatePassword') }}" method="POST" class="p-8 space-y-8">
                                @csrf

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                    <div class="md:col-span-1">
                                        <label for="current_password"
                                            class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">
                                            Current Password
                                        </label>
                                        <input type="password" id="current_password" name="current_password" required
                                            class="block w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white/80 dark:bg-gray-700/80 backdrop-blur-xl text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 transition-all duration-200"
                                            placeholder="Enter current password">
                                        @error('current_password')
                                            <p class="text-danger-600 text-sm mt-2">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div class="md:col-span-1">
                                        <label for="new_password"
                                            class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">
                                            New Password
                                        </label>
                                        <input type="password" id="new_password" name="new_password" required
                                            minlength="8"
                                            class="block w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white/80 dark:bg-gray-700/80 backdrop-blur-xl text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 transition-all duration-200"
                                            placeholder="At least 8 characters">
                                        @error('new_password')
                                            <p class="text-danger-600 text-sm mt-2">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div class="md:col-span-2">
                                        <label for="new_password_confirmation"
                                            class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">
                                            Confirm New Password
                                        </label>
                                        <input type="password" id="new_password_confirmation"
                                            name="new_password_confirmation" required
                                            class="block w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white/80 dark:bg-gray-700/80 backdrop-blur-xl text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 transition-all duration-200"
                                            placeholder="Re-enter new password">
                                    </div>
                                </div>

                                <div
                                    class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200/50 dark:border-gray-700/50">
                                    <button type="submit"
                                        class="inline-flex items-center px-8 py-3 bg-gradient-to-r from-primary-500 to-primary-600 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition-all duration-200">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        Update Password
                                    </button>
                                </div>
                            </form>
                        </div>
                    </section>
                @endif
            </div>
        </div>
    </div>

    {{-- Alpine Tab Logic --}}
    @push('scripts')
        <script>
            function profileTabs() {
                return {
                    active: 'info',
                    init() {
                        const hash = (window.location.hash || '').replace('#', '');
                        this.active = (hash === 'password' || hash === 'info') ? hash : 'info';
                        if (!hash) this.pushHash(this.active);
                    },
                    onHashChange() {
                        const h = (window.location.hash || '').replace('#', '');
                        if (h === 'password' || h === 'info') this.active = h;
                    },
                    setTab(tab) {
                        this.active = tab;
                        this.pushHash(tab);
                    },
                    pushHash(tab) {
                        if (history.replaceState) {
                            const url = new URL(window.location);
                            url.hash = tab;
                            history.replaceState(null, '', url);
                        } else {
                            window.location.hash = tab;
                        }
                    },
                    // --- UI classes
                    tabBtn(tab) {
                        const base =
                            'relative inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold transition focus:outline-none';
                        const active = 'bg-primary-600 text-white shadow ring-2 ring-primary-500/60';
                        const idle =
                            'text-gray-400 dark:text-gray-300/70 border border-transparent hover:bg-white/10 hover:text-white';
                        return `${base} ${this.active === tab ? active : idle}`;
                    },
                    iconClass(tab) {
                        return this.active === tab ? 'text-white' : 'text-gray-400 dark:text-gray-300/70';
                    }
                }
            }
        </script>
        {{-- If Alpine isn’t globally included, keep this; otherwise remove. --}}
        <script src="//unpkg.com/alpinejs" defer></script>
    @endpush
@endsection
