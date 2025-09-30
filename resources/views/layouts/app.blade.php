<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Leads Management Portal') }} - @yield('title', 'Dashboard')</title>

    {{-- Alpine Js --}}
    <script src="//unpkg.com/alpinejs" defer></script>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800" rel="stylesheet" />

    <meta name="lead-realtime-url" content="{{ route('leads.realtime') }}">

    <!-- Auto Redirect Component -->
    <x-auto-redirect />

    <!-- Styles -->
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
        <script>
            tailwind.config = {
                theme: {
                    extend: {
                        fontFamily: {
                            'sans': ['Inter', 'system-ui', 'sans-serif']
                        },
                        colors: {
                            primary: {
                                50: '#eff6ff',
                                100: '#dbeafe',
                                200: '#bfdbfe',
                                300: '#93c5fd',
                                400: '#60a5fa',
                                500: '#3b82f6',
                                600: '#2563eb',
                                700: '#1d4ed8',
                                800: '#1e40af',
                                900: '#1e3a8a',
                                950: '#172554'
                            },
                            success: {
                                50: '#f0fdf4',
                                100: '#dcfce7',
                                200: '#bbf7d0',
                                300: '#86efac',
                                400: '#4ade80',
                                500: '#22c55e',
                                600: '#16a34a',
                                700: '#15803d',
                                800: '#166534',
                                900: '#14532d',
                                950: '#052e16'
                            },
                            warning: {
                                50: '#fffbeb',
                                100: '#fef3c7',
                                200: '#fde68a',
                                300: '#fcd34d',
                                400: '#fbbf24',
                                500: '#f59e0b',
                                600: '#d97706',
                                700: '#b45309',
                                800: '#92400e',
                                900: '#78350f',
                                950: '#451a03'
                            },
                            danger: {
                                50: '#fef2f2',
                                100: '#fee2e2',
                                200: '#fecaca',
                                300: '#fca5a5',
                                400: '#f87171',
                                500: '#ef4444',
                                600: '#dc2626',
                                700: '#b91c1c',
                                800: '#991b1b',
                                900: '#7f1d1d',
                                950: '#450a0a'
                            },
                            gray: {
                                50: '#f9fafb',
                                100: '#f3f4f6',
                                200: '#e5e7eb',
                                300: '#d1d5db',
                                400: '#9ca3af',
                                500: '#6b7280',
                                600: '#4b5563',
                                700: '#374151',
                                800: '#1f2937',
                                900: '#111827',
                                950: '#030712'
                            }
                        },
                        animation: {
                            'fade-in': 'fadeIn 0.5s ease-in-out',
                            'slide-up': 'slideUp 0.3s ease-out',
                            'slide-down': 'slideDown 0.3s ease-out',
                            'scale-in': 'scaleIn 0.2s ease-out',
                            'bounce-in': 'bounceIn 0.6s ease-out'
                        },
                        keyframes: {
                            fadeIn: {
                                '0%': {
                                    opacity: '0'
                                },
                                '100%': {
                                    opacity: '1'
                                }
                            },
                            slideUp: {
                                '0%': {
                                    transform: 'translateY(10px)',
                                    opacity: '0'
                                },
                                '100%': {
                                    transform: 'translateY(0)',
                                    opacity: '1'
                                }
                            },
                            slideDown: {
                                '0%': {
                                    transform: 'translateY(-10px)',
                                    opacity: '0'
                                },
                                '100%': {
                                    transform: 'translateY(0)',
                                    opacity: '1'
                                }
                            },
                            scaleIn: {
                                '0%': {
                                    transform: 'scale(0.95)',
                                    opacity: '0'
                                },
                                '100%': {
                                    transform: 'scale(1)',
                                    opacity: '1'
                                }
                            },
                            bounceIn: {
                                '0%': {
                                    transform: 'scale(0.3)',
                                    opacity: '0'
                                },
                                '50%': {
                                    transform: 'scale(1.05)'
                                },
                                '70%': {
                                    transform: 'scale(0.9)'
                                },
                                '100%': {
                                    transform: 'scale(1)',
                                    opacity: '1'
                                }
                            }
                        },
                        boxShadow: {
                            'soft': '0 2px 15px -3px rgba(0,0,0,.07), 0 10px 20px -2px rgba(0,0,0,.04)',
                            'medium': '0 4px 25px -5px rgba(0,0,0,.1), 0 10px 10px -5px rgba(0,0,0,.04)',
                            'large': '0 10px 40px -10px rgba(0,0,0,.15), 0 20px 25px -5px rgba(0,0,0,.1)'
                        },
                        backdropBlur: {
                            xs: '2px'
                        }
                    }
                }
            }
        </script>
    @endif

    <!-- Additional Styles -->
    <style>
        [x-cloak] {
            display: none !important;
        }

        .sidebar-transition {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .card-hover {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .card-hover:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 40px -10px rgba(0, 0, 0, .15), 0 10px 20px -5px rgba(0, 0, 0, .1);
        }

        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .gradient-text {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .glass-effect {
            background: rgba(255, 255, 255, .1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, .2);
        }

        .glass-effect-dark {
            background: rgba(17, 24, 39, .8);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(55, 65, 81, .3);
        }

        .animate-float {
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0)
            }

            50% {
                transform: translateY(-10px)
            }
        }

        .stat-card {
            background: linear-gradient(135deg, rgba(255, 255, 255, .1) 0%, rgba(255, 255, 255, .05) 100%);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, .2);
        }

        .stat-card-dark {
            background: linear-gradient(135deg, rgba(17, 24, 39, .8) 0%, rgba(31, 41, 55, .6) 100%);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(55, 65, 81, .3);
        }

        /* Toast styles kept minimal; dropdown is primary UI */
        .notification-container {
            position: fixed;
            top: 5rem;
            right: 1rem;
            z-index: 1000;
            width: 24rem;
            max-width: 90vw;
        }
    </style>

    @stack('styles')
</head>

@php
    $user = auth()->user();
    $isAdmin =
        $user &&
        ((method_exists($user, 'isAdmin') && $user->isAdmin()) ||
            (method_exists($user, 'hasRole') && $user->hasRole('admin')) ||
            ($user->role ?? null) === 'admin');
@endphp

<body
    class="bg-gradient-to-br from-gray-50 via-blue-50 to-indigo-50 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900 text-gray-900 dark:text-gray-100 font-sans">
    <div class="min-h-screen flex flex-col lg:flex-row">

        <!-- Sidebar (fixed on desktop, drawer on mobile) -->
        <div id="sidebar"
            class="sidebar-transition fixed top-0 left-0 z-50 w-64 h-screen overflow-y-auto
                    bg-white/80 dark:bg-gray-800/90 backdrop-blur-xl shadow-2xl border-r border-gray-200/50 dark:border-gray-700/50
                    transform -translate-x-full
                    lg:translate-x-0 lg:fixed lg:z-40">
            <div
                class="flex items-center justify-between h-16 px-6 border-b border-gray-200/50 dark:border-gray-700/50">
                <div class="flex items-center">
                    <div
                        class="w-10 h-10 bg-gradient-to-br from-primary-500 to-primary-600 rounded-xl flex items-center justify-center shadow-lg">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                    <span class="ml-3 text-xl font-bold gradient-text">Leads Portal</span>
                </div>
                <button id="closeSidebar"
                    class="lg:hidden p-2 rounded-lg text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>

            <nav class="mt-8 px-4 pb-6">
                <div class="space-y-2">
                    @php
                        $user = auth()->user();
                        $role = $user?->role;
                    @endphp

                    {{-- Report Manager: ONLY Issues + Profile --}}
                    @if ($role === 'report_manager')
                        <a href="{{ route('issues.index') }}"
                            class="flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-200
               {{ request()->routeIs('issues.*') ? 'bg-gray-900 text-white dark:bg-gray-700' : 'text-gray-700 hover:bg-gray-100/80 dark:text-gray-300 dark:hover:bg-gray-700/80' }}">
                            <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6M7 8h10M5 6a2 2 0 012-2h10a2 2 0 012 2v12a2 2 0 01-2 2H7a2 2 0 01-2-2z" />
                            </svg>
                            User Reports
                            @php $unread = $user->unreadNotifications()->count(); @endphp
                            @if ($unread)
                                <span
                                    class="ml-2 text-xs bg-red-600 text-white px-2 py-0.5 rounded-full">{{ $unread }}</span>
                            @endif
                        </a>

                        <a href="{{ route('profile.edit') }}"
                            class="flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-200
               {{ request()->routeIs('profile.*') ? 'bg-primary-500 text-white shadow-lg border-l-4 border-primary-300' : 'text-gray-700 hover:bg-gray-100/80 dark:text-gray-300 dark:hover:bg-gray-700/80' }}">
                            <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            Profile
                        </a>

                        {{-- Admin menu (unchanged) --}}
                    @elseif ($user?->isAdmin())
                        <a href="{{ route('dashboard') }}"
                            class="flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-200
               {{ request()->routeIs('dashboard') ? 'bg-primary-500 text-white shadow-lg border-l-4 border-primary-300' : 'text-gray-700 hover:bg-gray-100/80 dark:text-gray-300 dark:hover:bg-gray-700/80' }}">
                            <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 5a2 2 0 012-2h4a2 2 0 012 2v6H8V5z"></path>
                            </svg>
                            Dashboard
                        </a>

                        <a href="{{ route('leads.index') }}"
                            class="flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-200
               {{ request()->routeIs('leads.*') ? 'bg-primary-500 text-white shadow-lg border-l-4 border-primary-300' : 'text-gray-700 hover:bg-gray-100/80 dark:text-gray-300 dark:hover:bg-gray-700/80' }}">
                            <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            Leads
                        </a>

                        <a href="{{ route('users.index') }}"
                            class="flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-200
               {{ request()->routeIs('users.*') ? 'bg-primary-500 text-white shadow-lg border-l-4 border-primary-300' : 'text-gray-700 hover:bg-gray-100/80 dark:text-gray-300 dark:hover:bg-gray-700/80' }}">
                            <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                            </svg>
                            Users
                        </a>

                        <a href="{{ route('reports.index') }}"
                            class="flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-200
               {{ request()->routeIs('reports.*') ? 'bg-primary-500 text-white shadow-lg border-l-4 border-primary-300' : 'text-gray-700 hover:bg-gray-100/80 dark:text-gray-300 dark:hover:bg-gray-700/80' }}">
                            <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 01-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2z" />
                            </svg>
                            Reports
                        </a>

                        <a href="{{ route('profile.edit') }}"
                            class="flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-200
               {{ request()->routeIs('profile.*') ? 'bg-primary-500 text-white shadow-lg border-l-4 border-primary-300' : 'text-gray-700 hover:bg-gray-100/80 dark:text-gray-300 dark:hover:bg-gray-700/80' }}">
                            <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            Profile
                        </a>
                    @elseif ($user?->isLeadManager())
                        <a href="{{ route('dashboard') }}"
                            class="flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-200
               {{ request()->routeIs('dashboard') ? 'bg-primary-500 text-white shadow-lg border-l-4 border-primary-300' : 'text-gray-700 hover:bg-gray-100/80 dark:text-gray-300 dark:hover:bg-gray-700/80' }}">
                            <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0-2-2z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 5a2 2 0 012-2h4a2 2 0 012 2v6H8V5z"></path>
                            </svg>
                            Dashboard
                        </a>

                        <a href="{{ route('leads.index') }}"
                            class="flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-200
               {{ request()->routeIs('leads.*') ? 'bg-primary-500 text-white shadow-lg border-l-4 border-primary-300' : 'text-gray-700 hover:bg-gray-100/80 dark:text-gray-300 dark:hover:bg-gray-700/80' }}">
                            <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            Leads
                        </a>

                        <a href="{{ route('profile.edit') }}"
                            class="flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-200
               {{ request()->routeIs('profile.*') ? 'bg-primary-500 text-white shadow-lg border-l-4 border-primary-300' : 'text-gray-700 hover:bg-gray-100/80 dark:text-gray-300 dark:hover:bg-gray-700/80' }}">
                            <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            Profile
                        </a>


                        {{-- Maxout --}}
                    @elseif ($role === 'max_out')
                        @php
                            $maxoutLeadsCount = \App\Models\Lead::query()
                                ->where('status', 'Max Out')
                                // ->where(function ($q) use ($user) {
                                //     $q->where('assigned_to', $user->id)
                                //         ->orWhere('super_agent_id', $user->id)
                                //         ->orWhere('closer_id', $user->id);
                                // });
                                ->count();
                        @endphp


                        {{-- dashboard --}}
                        <a href="{{ route('dashboard') }}"
                            class="flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-200
            {{ request()->routeIs('dashboard')
                ? 'bg-primary-500 text-white shadow-lg border-l-4 border-primary-300'
                : 'text-gray-700 hover:bg-gray-100/80 dark:text-gray-300 dark:hover:bg-gray-700/80' }}">
                            <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 5a2 2 0 012-2h4a2 2 0 012 2v6H8V5z"></path>
                            </svg>
                            Dashboard
                        </a>

                        <a href="{{ route('leads.maxout') }}"
                            class="flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-200
            {{ request()->routeIs('leads.*')
                ? 'bg-primary-500 text-white shadow-lg border-l-4 border-primary-300'
                : 'text-gray-700 hover:bg-gray-100/80 dark:text-gray-300 dark:hover:bg-gray-700/80' }}">

                            <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2
                       c0-.656-.126-1.283-.356-1.857M7 20H2v-2
                       a3 3 0 015.356-1.857M7 20v-2
                       c0-.656.126-1.283.356-1.857
                       m0 0a5.002 5.002 0 019.288 0
                       M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>

                            Maxout Leads

                            {{-- @if ($maxoutLeadsCount > 0)
                                <span class="ml-2 text-xs bg-red-600 text-white px-2 py-0.5 rounded-full">
                                    {{ $maxoutLeadsCount }}
                                </span>
                            @endif --}}
                        </a>

                        <a href="{{ route('profile.edit') }}"
                            class="flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-200
            {{ request()->routeIs('profile.*')
                ? 'bg-primary-500 text-white shadow-lg border-l-4 border-primary-300'
                : 'text-gray-700 hover:bg-gray-100/80 dark:text-gray-300 dark:hover:bg-gray-700/80' }}">
                            <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            Profile
                        </a>

                        {{-- Regular (non-admin, non-RM) --}}
                    @else
                        <a href="{{ route('dashboard') }}"
                            class="flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-200
               {{ request()->routeIs('dashboard') ? 'bg-primary-500 text-white shadow-lg border-l-4 border-primary-300' : 'text-gray-700 hover:bg-gray-100/80 dark:text-gray-300 dark:hover:bg-gray-700/80' }}">
                            <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 5a2 2 0 012-2h4a2 2 0 012 2v6H8V5z"></path>
                            </svg>
                            Dashboard
                        </a>

                        <a href="{{ route('leads.mine') }}"
                            class="flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-200
               {{ request()->routeIs('leads.mine') || request()->routeIs('leads.assigned.*') ? 'bg-primary-500 text-white shadow-lg border-l-4 border-primary-300' : 'text-gray-700 hover:bg-gray-100/80 dark:text-gray-300 dark:hover:bg-gray-700/80' }}">
                            <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            My Leads
                        </a>

                        <a href="{{ route('callbacks.index') }}"
                            class="flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-200
               {{ request()->routeIs('callbacks.*') ? 'bg-primary-500 text-white shadow-lg border-l-4 border-primary-300' : 'text-gray-700 hover:bg-gray-100/80 dark:text-gray-300 dark:hover:bg-gray-700/80' }}">
                            <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 5h6l2 3h10M4 15h8m-8 4h12M4 7v14" />
                            </svg>
                            Call Backs
                        </a>

                        <a href="{{ route('notes.index') }}"
                            class="flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-200
               {{ request()->routeIs('notes.*') ? 'bg-primary-500 text-white shadow-lg border-l-4 border-primary-300' : 'text-gray-700 hover:bg-gray-100/80 dark:text-gray-300 dark:hover:bg-gray-700/80' }}">
                            <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 10h8M8 14h6M5 6h14a2 2 0 012 2v10l-4-3H5a2 2 0 01-2-2V8a2 2 0 012-2z" />
                            </svg>
                            Notes
                        </a>

                        <a href="{{ route('profile.edit') }}"
                            class="flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-200
               {{ request()->routeIs('profile.*') ? 'bg-primary-500 text-white shadow-lg border-l-4 border-primary-300' : 'text-gray-700 hover:bg-gray-100/80 dark:text-gray-300 dark:hover:bg-gray-700/80' }}">
                            <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            Profile
                        </a>
                    @endif
                </div>
            </nav>
        </div>

        <!-- Main Content (offset for fixed sidebar on lg) -->
        <div class="flex-1 flex flex-col lg:ml-64">
            <!-- Top Navigation -->
            <div
                class="relative z-40 sticky top-0 bg-white/80 dark:bg-gray-800/90 backdrop-blur-xl shadow-sm border-b border-gray-200/50 dark:border-gray-700/50">
                <div class="flex items-center justify-between h-16 px-4 sm:px-6 lg:px-8">
                    <div class="flex items-center">
                        <button id="openSidebar"
                            class="lg:hidden p-2 rounded-lg text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 6h16M4 12h16M4 18h16"></path>
                            </svg>
                        </button>
                        <h1 class="ml-4 text-xl font-bold text-gray-900 dark:text-white">@yield('page-title', 'Dashboard')</h1>
                    </div>

                    <div class="flex items-center space-x-4">
                        <!-- Notifications Button & Dropdown (hidden for admins) -->
                        @if ($user->role == 'user' || $user->role == 'report_manager')
                            <div class="relative" x-data="{
                                ...notificationsDropdown({
                                    initialUnread: {{ $user->role == 'report_manager'
                                        ? auth()->user()->unreadNotifications()->whereJsonContains('data->issue_status', 'open')->count()
                                        : auth()->user()->unreadNotifications()->count() }},
                                    isReportManager: {{ $user->role == 'report_manager' ? 'true' : 'false' }},
                                    userId: {{ auth()->id() }},
                                    unreadRtCount: 0,
                                    unreadDbCount: {{ auth()->user()->unreadNotifications()->count() }}
                                }),
                                getNotificationUrl(item) {
                                    if (!this._isReportManager && item.id) {
                                        return `/leads/${item.id}/edit`;
                                    }
                                    return item.url || '#';
                                }
                            }" x-init="init()">
                                <button @click="toggle()"
                                    class="p-2 text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-gray-100 relative hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors"
                                    aria-label="Notifications">
                                    <!-- bell -->
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M14.857 17.082A4.002 4.002 0 0 1 12 19a4.002 4.002 0 0 1-2.857-1.918M6.5 8a5.5 5.5 0 1 1 11 0c0 3.07.582 4.626 1.08 5.428.38.608.57.912.566 1.107a.75.75 0 0 1-.428.63c-.174.085-.526.085-1.23.085H6.512c-.704 0-1.056 0-1.23-.085a.75.75 0 0 1-.428-.63c-.004-.195.185-.499.566-1.107C5.918 12.626 6.5 11.07 6.5 8Z" />
                                    </svg>
                                    <!-- badge with total unread (DB + realtime) -->
                                    <span x-show="unreadRtCount > 0 || unreadDbCount > 0"
                                        class="absolute -top-1 -right-1 min-w-[1.1rem] h-5 px-1 grid place-items-center rounded-full bg-red-600 text-white text-[10px] font-semibold shadow animate-pulse"
                                        x-text="unreadRtCount + unreadDbCount"></span>
                                </button>

                                <!-- Notifications Dropdown -->
                                <div x-show="open" @click.away="open = false"
                                    x-transition:enter="transition ease-out duration-200"
                                    x-transition:enter-start="opacity-0 transform scale-95"
                                    x-transition:enter-end="opacity-100 transform scale-100"
                                    x-transition:leave="transition ease-in duration-75"
                                    x-transition:leave-start="opacity-100 transform scale-100"
                                    x-transition:leave-end="opacity-0 transform scale-95"
                                    class="absolute right-0 mt-2 w-80 sm:w-96 bg-white/90 dark:bg-gray-800/90 backdrop-blur-xl rounded-xl shadow-2xl py-2 z-50 border border-gray-200/50 dark:border-gray-700/50 hidden"
                                    :class="{ 'hidden': !open }" style="display: none;">
                                    <div
                                        class="px-4 py-2 border-b border-gray-200/50 dark:border-gray-700/50 flex justify-between items-center">
                                        <h3 class="font-semibold text-gray-900 dark:text-white">Notifications</h3>
                                        <button @click="markAllRead()"
                                            class="text-xs text-primary-600 hover:text-primary-800 dark:text-primary-400 dark:hover:text-primary-300 cursor-pointer">
                                            Mark all as read
                                        </button>
                                    </div>

                                    <div class="max-h-96 overflow-y-auto">
                                        <!-- Realtime lead updates (injected via polling) -->
                                        <template x-if="rtItems.length > 0">
                                            <div class="px-4 py-2 text-[11px] tracking-wide uppercase text-gray-500"
                                                x-text="isReportManager ? 'Report Requests' : 'Recent Lead Updates'">
                                            </div>
                                        </template>
                                        <!-- We wrap the list in a container so we can count DOM items if needed -->
                                        <div x-ref="rtList">
                                            <template x-for="it in rtItems" :key="it.id + '-' + it.updated_at">
                                                <div class="rt-lead px-4 py-3 border-b border-gray-100/50 dark:border-gray-700/50 last:border-b-0 hover:bg-gray-50/80 dark:hover:bg-gray-700/50 transition-colors"
                                                    :class="{ 'bg-red-50 dark:bg-red-900/10': it.unread }">
                                                    <a :href="getNotificationUrl(it)" class="block"
                                                        @click="markAsRead(it)">
                                                        <div class="flex items-start">
                                                            <div class="flex-shrink-0 mt-0.5">
                                                                <div class="h-8 w-8 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center"
                                                                    :class="{ 'bg-red-100 dark:bg-red-900/30': it.unread }">
                                                                    <svg class="h-5 w-5"
                                                                        :class="it.unread ? 'text-red-600 dark:text-red-400' :
                                                                            'text-primary-600 dark:text-primary-400'"
                                                                        fill="none" stroke="currentColor"
                                                                        viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round"
                                                                            stroke-linejoin="round" stroke-width="2"
                                                                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                                                                    </svg>
                                                                </div>
                                                            </div>
                                                            <div class="ml-3 flex-1">
                                                                <p
                                                                    class="text-sm font-medium text-gray-900 dark:text-white">
                                                                    <span
                                                                        x-text="it.issue ? `Issue #${it.issue.id}` : `Lead #${it.id}`"></span>
                                                                    <span x-show="it.unread"
                                                                        class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400">New</span>
                                                                </p>
                                                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1"
                                                                    x-text="it.message || (it.issue ? it.issue.title : `${it.first_name || ''} ${it.surname || ''}`)"
                                                                    <span class="inline-flex items-center gap-1">
                                                                    <span
                                                                        class="inline-block h-2 w-2 rounded-full bg-indigo-500"></span>
                                                                    <span x-text="it.status || 'updated'"></span>
                                                                    </span>
                                                                </p>
                                                                <p class="text-[11px] text-gray-400 dark:text-gray-500 mt-1"
                                                                    x-text="timeAgo(it.issue ? it.issue.updated_at : it.updated_at)">
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </a>
                                                </div>
                                            </template>
                                        </div>

                                        <!-- Divider if both types exist -->
                                        <template x-if="rtItems.length > 0 && hasDbNotifs">
                                            <div class="px-4 py-2 text-[11px] tracking-wide uppercase text-gray-500">
                                                System
                                                notifications</div>
                                        </template>

                                        <!-- Server-rendered DB notifications (initial) - only unread and open issues -->
                                        @if (auth()->user()->unreadNotifications()->count() > 0)
                                            @foreach (auth()->user()->unreadNotifications()->take(10) as $notification)
                                                @php
                                                    $issueStatus = $notification->data['issue_status'] ?? '';
                                                    if ($user->role === 'report_manager' && $issueStatus !== 'open') {
                                                        continue;
                                                    }
                                                @endphp
                                                <div class="px-4 py-3 border-b border-gray-100/50 dark:border-gray-700/50 last:border-b-0 hover:bg-gray-50/80 dark:hover:bg-gray-700/50 transition-colors db-notif"
                                                    data-notif-id="{{ $notification->id }}"
                                                    data-issue-status="{{ $issueStatus }}">
                                                    <div class="flex items-start">
                                                        <div class="flex-shrink-0 mt-0.5">
                                                            @if ($notification->type === 'App\Notifications\LeadAssigned')
                                                                <div
                                                                    class="h-8 w-8 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center">
                                                                    <svg class="h-5 w-5 text-primary-600 dark:text-primary-400"
                                                                        fill="none" stroke="currentColor"
                                                                        viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round"
                                                                            stroke-linejoin="round" stroke-width="2"
                                                                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                                                                    </svg>
                                                                </div>
                                                            @elseif($notification->type === 'App\Notifications\IssueReported')
                                                                <div
                                                                    class="h-8 w-8 rounded-full bg-warning-100 dark:bg-warning-900/30 flex items-center justify-center">
                                                                    <svg class="h-5 w-5 text-warning-600 dark:text-warning-400"
                                                                        fill="none" stroke="currentColor"
                                                                        viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round"
                                                                            stroke-linejoin="round" stroke-width="2"
                                                                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                                                                    </svg>
                                                                </div>
                                                            @else
                                                                <div
                                                                    class="h-8 w-8 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                                                                    <svg class="h-5 w-5 text-gray-600 dark:text-gray-400"
                                                                        fill="none" stroke="currentColor"
                                                                        viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round"
                                                                            stroke-linejoin="round" stroke-width="2"
                                                                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                                    </svg>
                                                                </div>
                                                            @endif
                                                        </div>
                                                        <div class="ml-3 flex-1">
                                                            <p
                                                                class="text-sm font-medium text-gray-900 dark:text-white">
                                                                {{ $notification->data['title'] ?? 'Notification' }}
                                                            </p>
                                                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                                                {{ $notification->data['message'] ?? '' }}
                                                            </p>
                                                            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                                                                {{ $notification->created_at->diffForHumans() }}
                                                            </p>
                                                        </div>
                                                        @if ($notification->unread())
                                                            <button onclick="markAsRead('{{ $notification->id }}')"
                                                                class="ml-2 flex-shrink-0" title="Mark as read">
                                                                <svg class="h-5 w-5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                                                                    fill="none" stroke="currentColor"
                                                                    viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round"
                                                                        stroke-linejoin="round" stroke-width="2"
                                                                        d="M5 13l4 4L19 7" />
                                                                </svg>
                                                            </button>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        @else
                                            <div class="px-4 py-6 text-center">
                                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none"
                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M15 17h5l-5 5v-5zM4.83 19.07A11 11 0 015 12c0-6.075 4.925-11 11-11s11 4.925 11 11a11 11 0 01-1.07 7.07M4.83 19.07L9 15M4.83 19.07l-2.83 2.83" />
                                                </svg>
                                                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">No
                                                    notifications
                                                    yet</p>
                                            </div>
                                        @endif
                                    </div>

                                    <template x-if="hasDbNotifs">
                                        <div class="px-4 py-2 border-t border-gray-200/50 dark:border-gray-700/50">
                                            <a href="{{ route('rm.notifications') }}"
                                                class="block text-center text-sm font-medium text-primary-600 hover:text-primary-800 dark:text-primary-400 dark:hover:text-primary-300">
                                                View all notifications
                                            </a>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        @endif

                        <div class="relative">
                            <button id="userMenuButton"
                                class="flex items-center text-sm rounded-xl focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 p-2 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                                <img class="h-8 w-8 rounded-full ring-2 ring-primary-200 dark:ring-primary-700"
                                    src="https://ui-avatars.com/api/?name={{ auth()->user()->name ?? 'User' }}&color=0ea5e9&background=f0f9ff"
                                    alt="{{ auth()->user()->name ?? 'User' }}">
                                <span
                                    class="ml-2 text-gray-700 dark:text-gray-300 font-medium">{{ auth()->user()->name ?? 'User' }}</span>
                                <svg class="ml-1 h-4 w-4 text-gray-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>

                            <div id="userMenu"
                                class="hidden absolute right-0 mt-2 w-48 bg-white/90 dark:bg-gray-800/90 backdrop-blur-xl rounded-xl shadow-2xl py-1 z-50 border border-gray-200/50 dark:border-gray-700/50">
                                <a href="{{ route('profile.edit') }}"
                                    class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100/80 dark:hover:bg-gray-700/80 transition-colors">Profile</a>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit"
                                        class="block w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100/80 dark:hover:bg-gray-700/80 transition-colors">Sign
                                        out</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto">
                <div class="py-6">
                    <div class="mx-auto px-4 sm:px-6 lg:px-8">
                        @if (session('success'))
                            <div
                                class="mb-6 bg-success-50 border border-success-200 text-success-800 px-4 py-3 rounded-xl shadow-sm animate-slide-down">
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    {{ session('success') }}
                                </div>
                            </div>
                        @endif

                        @if (session('error'))
                            <div
                                class="mb-6 bg-danger-50 border border-danger-200 text-danger-800 px-4 py-3 rounded-xl shadow-sm animate-slide-down">
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z">
                                        </path>
                                    </svg>
                                    {{ session('error') }}
                                </div>
                            </div>
                        @endif

                        @if (session('status'))
                            @if (session('status') === 'profile-updated')
                                <div
                                    class="mb-6 bg-success-50 border border-success-200 text-success-800 px-4 py-3 rounded-xl shadow-sm animate-slide-down">
                                    <div class="flex items-center">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        Profile updated successfully!
                                    </div>
                                </div>
                            @elseif(session('status') === 'password-updated')
                                <div
                                    class="mb-6 bg-success-50 border border-success-200 text-success-800 px-4 py-3 rounded-xl shadow-sm animate-slide-down">
                                    <div class="flex items-center">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        Password updated successfully!
                                    </div>
                                </div>
                            @endif
                        @endif

                        @if ($errors->any())
                            <div
                                class="mb-6 bg-danger-50 border border-danger-200 text-danger-800 px-4 py-3 rounded-xl shadow-sm animate-slide-down">
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 mr-2 mt-0.5 flex-shrink-0" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z">
                                        </path>
                                    </svg>
                                    <ul class="list-disc list-inside">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        @endif

                        @yield('content')
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Overlay for mobile sidebar -->
    <div id="sidebarOverlay" class="fixed inset-0 bg-gray-600/75 backdrop-blur-sm z-40 lg:hidden hidden"></div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const openSidebarBtn = document.getElementById('openSidebar');
            const closeSidebarBtn = document.getElementById('closeSidebar');
            const sidebarOverlay = document.getElementById('sidebarOverlay');
            const userMenuButton = document.getElementById('userMenuButton');
            const userMenu = document.getElementById('userMenu');

            // --- Mobile drawer toggle ---
            if (openSidebarBtn) {
                openSidebarBtn.addEventListener('click', function() {
                    sidebar.classList.remove('-translate-x-full'); // show
                    sidebarOverlay.classList.remove('hidden');
                });
            }
            if (closeSidebarBtn) {
                closeSidebarBtn.addEventListener('click', function() {
                    sidebar.classList.add('-translate-x-full'); // hide
                    sidebarOverlay.classList.add('hidden');
                });
            }
            if (sidebarOverlay) {
                sidebarOverlay.addEventListener('click', function() {
                    sidebar.classList.add('-translate-x-full');
                    sidebarOverlay.classList.add('hidden');
                });
            }

            // --- User menu toggle ---
            if (userMenuButton && userMenu) {
                userMenuButton.addEventListener('click', function() {
                    userMenu.classList.toggle('hidden');
                });
                document.addEventListener('click', function(event) {
                    if (!userMenuButton.contains(event.target) && !userMenu.contains(event.target)) {
                        userMenu.classList.add('hidden');
                    }
                });
            }

            // --- On load animation ---
            const animateElements = document.querySelectorAll('.animate-on-load');
            animateElements.forEach((el, i) => {
                el.style.animationDelay = `${i * 0.1}s`;
                el.classList.add('animate-fade-in');
            });
        });
    </script>

    {{-- Alpine helper for Notifications Dropdown (polling, no Echo/Pusher) --}}
    @unless ($isAdmin)
        <script>
            function notificationsDropdown({
                initialUnread = 0,
                isReportManager = false,
                userId = null
            } = {}) {
                // Local unread management for realtime items
                const LS_LAST_SEEN_KEY = 'notif:last_seen_iso';
                const nowIso = () => new Date().toISOString();
                const getLastSeen = () => localStorage.getItem(LS_LAST_SEEN_KEY) || '';
                const setLastSeen = (iso) => localStorage.setItem(LS_LAST_SEEN_KEY, iso || nowIso());

                // Get the server-side notifications_read_at timestamp
                const serverNotificationsReadAt = '{{ auth()->user()->getNotificationsReadAt()?->toISOString() ?? '' }}';

                return {
                    open: false,
                    // DB unread shown in dropdown "System notifications"
                    unreadDbCount: initialUnread,
                    // Realtime unread derived from last_seen vs updated_at
                    unreadRtCount: 0,

                    _seen: new Set(), // to avoid duplicates
                    hasDbNotifs: {{ auth()->user()->unreadNotifications()->count() > 0 ? 'true' : 'false' }},
                    rtItems: [],
                    _sinceISO: new Date(Date.now() - 60 * 1000).toISOString(),
                    _url: null,
                    _interval: 10000,
                    _timer: null,
                    _isReportManager: isReportManager,
                    _userId: userId,

                    // Helper method to check if notification should be shown
                    shouldShowNotification(item) {
                        if (!this._isReportManager) {
                            return true; // Regular users see all notifications
                        }
                        // For report managers, only show open issues where they are the resolver
                        if (item.issue &&
                            item.issue.resolver_id === this._userId &&
                            item.issue.status === 'open') {
                            return true;
                        }
                        return false;
                    },

                    _isUnread(itemDate, issueDate) {
                        const lastSeen = getLastSeen();
                        if (!lastSeen) return true;
                        const compareDate = issueDate || itemDate;
                        return new Date(compareDate) > new Date(lastSeen);
                    },

                    _recomputeUnread() {
                        // Filter and count notifications based on role
                        if (this._isReportManager) {
                            // For report managers: only show open issues assigned to them
                            this.rtItems = this.rtItems.filter(it =>
                                it.issue &&
                                it.issue.resolver_id === this._userId &&
                                it.issue.status === 'open'
                            );
                            this.unreadRtCount = this.rtItems.length;
                        } else {
                            // For regular users: show all unread notifications
                            this.unreadRtCount = this.rtItems.filter(it => it.unread).length;
                        }

                        // Update total unread count
                        this.unreadTotal = this.unreadRtCount + this.unreadDbCount;

                        // Update total unread count
                        this.unreadTotal = this.unreadRtCount + this.unreadDbCount;

                        // If we're a report manager, also check DB notifications for open issues
                        if (this._isReportManager) {
                            this.unreadDbCount = document.querySelectorAll('.db-notif[data-issue-status="open"]').length;
                        }

                        // Update total unread count in UI
                        this.unreadTotal = this.unreadDbCount + this.unreadRtCount;
                    },

                    // Bell badge: sum of DB unread + realtime unread
                    get unreadTotal() {
                        return Math.max(0, (this.unreadDbCount || 0) + (this.unreadRtCount || 0));
                    },

                    markAllRead() {
                        // 1) Update the global notifications_read_at on the server
                        fetch('/notifications/mark-all-read', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Content-Type': 'application/json',
                                'Accept': 'application/json'
                            }
                        }).then(() => {
                            // 2) Mark realtime as read (advance last-seen)
                            setLastSeen(nowIso());
                            // 3) Reset realtime unread count
                            this.unreadRtCount = 0;
                            // 4) Mark all items as read
                            this.rtItems.forEach(it => it.unread = false);
                            // 5) Clear DB unread count
                            this.unreadDbCount = 0;
                            // 6) Update hasDbNotifs flag
                            this.hasDbNotifs = false;
                            // 7) Close dropdown
                            this.open = false;
                        }).catch(console.error);
                    },

                    init() {
                        const meta = document.querySelector('meta[name="lead-realtime-url"]');
                        if (meta) this._url = meta.content;

                        // Initialize last_seen if missing (keeps old items "unread" until first open)
                        if (!getLastSeen()) setLastSeen('');

                        // DB unread event listeners (from per-item markAsRead & mark-all)
                        window.addEventListener('db:read-one', () => {
                            if (this.unreadDbCount > 0) this.unreadDbCount--;
                        });
                        window.addEventListener('db:read-all', () => {
                            this.unreadDbCount = 0;
                        });

                        if (this._url) this._tick();
                    },

                    markAsRead(item) {
                        item.unread = false;
                        this._recomputeUnread();

                        // If it's an issue notification and we're a report manager, update the server
                        if (this._isReportManager && item.issue) {
                            fetch(`/notifications/${item.issue.id}/mark-as-read`, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json'
                                }
                            }).catch(console.error);
                        }
                    },

                    toggle() {
                        this.open = !this.open;
                        if (this.open) {
                            // Just recompute unread count, don't mark as seen automatically
                            this._recomputeUnread();
                        }
                    },

                    async _tick() {
                        try {
                            const lastSeen = encodeURIComponent(getLastSeen() || '');
                            // Only use server-side notifications_read_at for explicit "mark all as read"
                            // For general polling, use localStorage lastSeen
                            const res = await fetch(
                                `${this._url}?since=${encodeURIComponent(this._sinceISO)}&last_seen=${lastSeen}`, {
                                    credentials: 'same-origin',
                                    headers: {
                                        'X-Requested-With': 'XMLHttpRequest'
                                    },
                                });
                            if (!res.ok) throw new Error('network');
                            const data = await res.json();

                            if (data.since) this._sinceISO = data.since;

                            if (Array.isArray(data.items) && data.items.length) {
                                const newest = data.items.reverse();
                                for (const it of newest) {
                                    const key = `${it.id}-${it.updated_at}`;
                                    if (this._seen.has(key)) continue; // avoid duplicates

                                    // Only add notification if it should be shown based on role
                                    if (this.shouldShowNotification(it)) {
                                        this._seen.add(key);
                                        // compute unread flag against last_seen
                                        const relevantDate = it.issue ? it.issue.updated_at : it.updated_at;
                                        it.unread = this._isUnread(it.updated_at, it.issue?.updated_at);
                                        this.rtItems.unshift(it);
                                        // Increment unread count if item is unread
                                        if (it.unread) {
                                            this.unreadRtCount++;
                                        }
                                    }
                                }
                                if (this.rtItems.length > 100) this.rtItems.length = 100;
                            }

                            // Prefer server-provided count; otherwise compute locally
                            if (typeof data.unread_count === 'number') {
                                this.unreadRtCount = data.unread_count;
                            } else {
                                this._recomputeUnread();
                            }

                            this._interval = 10000; // reset after success
                        } catch (e) {
                            this._interval = Math.min(this._interval * 1.5, 60000);
                        } finally {
                            clearTimeout(this._timer);
                            this._timer = setTimeout(() => this._tick(), this._interval);
                        }
                    },

                    _isUnread(iso) {
                        if (!iso) return false;
                        const lastSeen = getLastSeen();
                        return lastSeen ? (new Date(iso) > new Date(lastSeen)) : true;
                    },

                    _recomputeUnread() {
                        let count = 0;
                        const lastSeen = getLastSeen();
                        this.rtItems.forEach(it => {
                            it.unread = lastSeen ? (new Date(it.updated_at) > new Date(lastSeen)) : true;
                            if (it.unread) count++;
                        });
                        this.unreadRtCount = count;
                    },

                    timeAgo(iso) {
                        const d = new Date(iso);
                        const sec = Math.floor((Date.now() - d.getTime()) / 1000);
                        if (sec < 60) return `${sec}s ago`;
                        const m = Math.floor(sec / 60);
                        if (m < 60) return `${m}m ago`;
                        const h = Math.floor(m / 60);
                        if (h < 24) return `${h}h ago`;
                        return `${Math.floor(h/24)}d ago`;
                    },

                    // Single authoritative "Mark all as read"
                    async markAllRead() {
                        // 1) Tell server to mark DB notifications as read
                        try {
                            const response = await fetch('/notifications/mark-all-as-read', {
                                credentials: 'same-origin',
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json'
                                }
                            });

                            if (!response.ok) {
                                throw new Error(`HTTP error! status: ${response.status}`);
                            }

                            const result = await response.json();
                            console.log('Notifications marked as read:', result);
                        } catch (e) {
                            console.error('Error marking all notifications as read:', e);
                            // Show user-friendly error message
                            alert('Failed to mark notifications as read. Please try again.');
                            return;
                        }

                        // 2) Mark realtime as read (advance last-seen)
                        setLastSeen(nowIso());

                        // 3) Clear all realtime notifications from UI
                        this.rtItems = [];
                        this.unreadRtCount = 0;

                        // 4) Clear DB unread count + visually remove DB items
                        document.querySelectorAll('.db-notif').forEach(n => n.remove());
                        this.unreadDbCount = 0;

                        // 5) Update the hasDbNotifs flag since all DB notifications are now read
                        this.hasDbNotifs = false;

                        // 6) Notify any listeners
                        window.dispatchEvent(new CustomEvent('db:read-all'));

                        // 7) Close the dropdown to show the effect
                        this.open = false;

                        // 8) Force refresh of realtime data to respect new server-side timestamp
                        if (this._url) {
                            setTimeout(() => this._tick(), 100);
                        }

                        console.log('All notifications removed from UI');
                    }
                }
            }
        </script>
    @endunless

    <!-- Helpers for DB notifications actions (emit events the Alpine component listens for) -->
    <script>
        function markAsRead(notificationId) {
            fetch(`/notifications/${notificationId}/mark-as-read`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                })
                .then(r => r.json()).then(data => {
                    if (data.success) {
                        const el = document.querySelector(`.db-notif[data-notif-id="${notificationId}"]`);
                        if (el) el.remove();
                        window.dispatchEvent(new CustomEvent('db:read-one'));
                    }
                }).catch(e => {
                    console.error('Error marking notification as read:', e);
                });
        }
        // NOTE: Removed the old global markAllAsRead() to avoid conflicts.
    </script>

    @stack('scripts')
    @stack('modals')

    {{-- NO Echo/Pusher include: realtime via simple polling --}}
    {{-- @include('layouts.partials.notifications_echo') --}}
</body>

<script>
    (function() {
        if (window.__sessBound) return;
        window.__sessBound = true;

        const PING_URL = "{{ route('attendance.ping') }}";
        const BEACON_URL = "{{ route('logout.beacon') }}";
        const CSRF = document.querySelector('meta[name="csrf-token"]')?.content || '';

        function ping() {
            if (document.hidden) return;
            fetch(PING_URL, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'X-CSRF-TOKEN': CSRF,
                    'Accept': 'application/json'
                }
            }).catch(() => {});
        }
        let hb = setInterval(ping, 60000);
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) ping();
        }, {
            passive: true
        });
        window.addEventListener('focus', ping, {
            passive: true
        });

        const KEY = 'app_active_tab_count';
        const getCount = () => parseInt(localStorage.getItem(KEY) || '0', 10) || 0;
        const setCount = (n) => localStorage.setItem(KEY, String(Math.max(0, n)));

        window.addEventListener('pageshow', () => setCount(getCount() + 1));

        let closing = false;

        function onClose() {
            if (closing) return;
            closing = true;
            setCount(getCount() - 1);
            const remaining = getCount();
            setTimeout(() => {
                if (remaining <= 0) {
                    if (navigator.sendBeacon) {
                        const blob = new Blob([JSON.stringify({
                            reason: 'tab-or-browser-close'
                        })], {
                            type: 'application/json'
                        });
                        navigator.sendBeacon(BEACON_URL, blob);
                    } else {
                        fetch(BEACON_URL, {
                            method: 'POST',
                            credentials: 'same-origin',
                            keepalive: true,
                            headers: {
                                'Accept': 'application/json'
                            }
                        });
                    }
                }
                clearInterval(hb);
            }, 900);
        }
        window.addEventListener('pagehide', onClose);
        window.addEventListener('beforeunload', onClose);
    })();
</script>

</html>
