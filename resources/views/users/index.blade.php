@extends('layouts.app')

@section('title', 'Users')
@section('page-title', 'Users')

@section('content')
    <div class="space-y-8 animate-on-load">
        <!-- Header with Actions -->
        <div
            class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl shadow-2xl rounded-2xl p-8 border border-gray-200/50 dark:border-gray-700/50">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">User Management</h2>
                    <p class="text-lg text-gray-600 dark:text-gray-400">Manage user accounts and permissions.</p>
                </div>
                <div class="mt-6 sm:mt-0 flex space-x-4">
                    <a href="{{ route('users.create') }}"
                        class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-primary-500 to-primary-600 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition-all duration-200">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Add User
                    </a>
                </div>
            </div>
        </div>

        <!-- Users Table -->
        <div
            class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl shadow-2xl rounded-2xl border border-gray-200/50 dark:border-gray-700/50 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200/50 dark:divide-gray-700/50">
                    <thead class="bg-gray-50/80 dark:bg-gray-700/80 backdrop-blur-xl">
                        <tr>
                            <th scope="col"
                                class="px-8 py-4 text-left text-xs font-bold text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                                User
                            </th>
                            <th scope="col"
                                class="px-8 py-4 text-left text-xs font-bold text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                                Email
                            </th>
                            <th scope="col"
                                class="px-8 py-4 text-left text-xs font-bold text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                                Role
                            </th>
                            <th scope="col"
                                class="px-8 py-4 text-left text-xs font-bold text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                                Status
                            </th>
                            <th scope="col"
                                class="px-8 py-4 text-left text-xs font-bold text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                                Joined
                            </th>
                            <th scope="col"
                                class="px-8 py-4 text-left text-xs font-bold text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody
                        class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl divide-y divide-gray-200/50 dark:divide-gray-700/50">
                        @forelse($users ?? [] as $user)
                            <tr class="hover:bg-gray-50/80 dark:hover:bg-gray-700/80 transition-colors">
                                <td class="px-8 py-6 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-12 w-12">
                                            <img class="h-12 w-12 rounded-xl ring-2 ring-primary-200 dark:ring-primary-700"
                                                src="https://ui-avatars.com/api/?name={{ $user->name ?? 'User' }}&color=0ea5e9&background=f0f9ff"
                                                alt="{{ $user->name ?? 'User' }}">
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-lg font-semibold text-gray-900 dark:text-white">
                                                {{ $user->name ?? 'User Name' }}
                                            </div>
                                            <div class="text-sm text-gray-600 dark:text-gray-400">
                                                {{ $user->email ?? 'email@example.com' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-6 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $user->email ?? 'email@example.com' }}</div>
                                </td>
                                <td class="px-8 py-6 whitespace-nowrap">
                                    <span
                                        class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium 
                                @switch($user->role)
                                    @case('admin')
                                        bg-primary-100 text-primary-800 dark:bg-primary-900 dark:text-primary-200
                                        @break
                                    @case('super_agent')
                                        bg-success-100 text-success-800 dark:bg-success-900 dark:text-success-200
                                        @break
                                    @case('closer')
                                        bg-warning-100 text-warning-800 dark:bg-warning-900 dark:text-warning-200
                                        @break
                                    @default
                                        bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200
                                @endswitch">
                                        @switch($user->role)
                                            @case('admin')
                                                Admin
                                            @break

                                            @case('super_agent')
                                                Super Agent
                                            @break

                                            @case('closer')
                                                Closer
                                            @break

                                            @default
                                                User
                                        @endswitch
                                    </span>
                                </td>
                                <td class="px-8 py-6 whitespace-nowrap">
                                    <span
                                        class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-success-100 text-success-800 dark:bg-success-900 dark:text-success-200">
                                        Active
                                    </span>
                                </td>
                                <td class="px-8 py-6 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                                    {{ \Carbon\Carbon::parse($user->created_at ?? now())->format('M d, Y') }}
                                </td>
                                <td class="px-8 py-6 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-3">
                                        <a href="{{ route('users.show', $user->id ?? 1) }}"
                                            class="text-primary-600 dark:text-primary-400 hover:text-primary-900 dark:hover:text-primary-300 p-2 rounded-lg hover:bg-primary-50 dark:hover:bg-primary-900/20 transition-colors">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                                </path>
                                            </svg>
                                        </a>
                                        <a href="{{ route('users.edit', $user->id ?? 1) }}"
                                            class="text-primary-600 dark:text-primary-400 hover:text-primary-900 dark:hover:text-primary-300 p-2 rounded-lg hover:bg-primary-50 dark:hover:bg-primary-900/20 transition-colors">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                                </path>
                                            </svg>
                                        </a>
                                        @if ($user->id !== auth()->id())
                                            <button type="button"
                                                class="text-danger-600 dark:text-danger-400 hover:text-danger-900 dark:hover:text-danger-300 p-2 rounded-lg hover:bg-danger-50 dark:hover:bg-danger-900/20 transition-colors"
                                                onclick="deleteUser({{ $user->id ?? 1 }})">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                    </path>
                                                </svg>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-8 py-16 text-center">
                                        <div class="text-center">
                                            <div
                                                class="w-24 h-24 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-6">
                                                <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z">
                                                    </path>
                                                </svg>
                                            </div>
                                            <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">No users found
                                            </h3>
                                            <p class="text-gray-600 dark:text-gray-400 mb-8">Get started by creating a new user
                                                account.</p>
                                            <a href="{{ route('users.create') }}"
                                                class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-primary-500 to-primary-600 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition-all duration-200">
                                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                                </svg>
                                                Create User
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if (isset($users) && $users->hasPages())
                    <div
                        class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl px-8 py-6 flex items-center justify-between border-t border-gray-200/50 dark:border-gray-700/50">
                        <div class="hidden md:block">
                            <p class="text-sm text-gray-700 dark:text-gray-300">
                                Showing <span class="font-semibold">{{ $users->firstItem() }}</span>
                                to <span class="font-semibold">{{ $users->lastItem() }}</span>
                                of <span class="font-semibold">{{ $users->total() }}</span> results
                            </p>
                        </div>
                        <div class="w-full md:w-auto">
                            {{ $users->onEachSide(1)->links('vendor.pagination.recomune') }}
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div id="deleteModal" class="fixed inset-0 bg-gray-600/75 backdrop-blur-sm overflow-y-auto h-full w-full hidden z-50">
            <div
                class="relative top-20 mx-auto p-8 border w-96 shadow-2xl rounded-2xl bg-white/90 dark:bg-gray-800/90 backdrop-blur-xl border-gray-200/50 dark:border-gray-700/50">
                <div class="mt-3 text-center">
                    <div
                        class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-danger-100 dark:bg-danger-900 mb-6">
                        <svg class="h-8 w-8 text-danger-600 dark:text-danger-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z">
                            </path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Delete User</h3>
                    <div class="mb-8 px-4">
                        <p class="text-gray-600 dark:text-gray-400">Are you sure you want to delete this user? This action
                            cannot be undone.</p>
                    </div>
                    <div class="flex justify-center space-x-4">
                        <form id="deleteForm" method="POST" style="display: inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                class="px-6 py-3 bg-gradient-to-r from-danger-500 to-danger-600 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition-all duration-200">
                                Delete
                            </button>
                        </form>
                        <button id="cancelDelete"
                            class="px-6 py-3 bg-gray-300 dark:bg-gray-600 text-gray-700 dark:text-gray-300 font-semibold rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition-all duration-200">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>

    @endsection

    @push('scripts')
        <script>
            function deleteUser(userId) {
                document.getElementById('deleteForm').action = `/users/${userId}`;
                document.getElementById('deleteModal').classList.remove('hidden');
            }

            document.getElementById('cancelDelete').addEventListener('click', function() {
                document.getElementById('deleteModal').classList.add('hidden');
            });
        </script>
    @endpush
