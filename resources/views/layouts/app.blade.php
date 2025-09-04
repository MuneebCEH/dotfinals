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
    </style>

    @stack('styles')
</head>

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
                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
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
                        {{-- <button
                            class="p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 relative hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 17h5l-5 5v-5zM4.83 19.07A11 11 0 015 12c0-6.075 4.925-11 11-11s11 4.925 11 11a11 11 0 01-1.07 7.07M4.83 19.07L9 15M4.83 19.07l-2.83 2.83">
                                </path>
                            </svg>
                            <span
                                class="absolute top-1 right-1 block h-3 w-3 rounded-full bg-red-400 animate-pulse"></span>
                        </button> --}}

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

    @stack('scripts')
    @stack('modals')
    
    {{-- Include real-time notifications component --}}
    @include('layouts.partials.notifications_echo')
</body>

</html>
