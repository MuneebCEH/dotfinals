<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="light">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Leads Management Portal') }} - @yield('title', 'Dashboard')</title>

    {{-- Alpine Js --}}
    <script src="//unpkg.com/alpinejs" defer></script>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:ital,wght@0,200..800;1,200..800&display=swap"
        rel="stylesheet">

    <meta name="lead-realtime-url" content="{{ route('leads.realtime') }}">

    <!-- Auto Redirect Component -->
    <x-auto-redirect />

    <!-- Styles -->
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:ital,wght@0,200..800;1,200..800&display=swap');

        :root {
            --primary: #00bf63;
            --primary-hover: #009e52;
            --bg-deep: #f8fafc;
            --sidebar-width: 280px;
        }

        [x-cloak] {
            display: none !important;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-deep);
            color: #1e293b;
            overflow-x: hidden;
        }

        .mesh-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            background:
                radial-gradient(circle at 10% 20%, rgba(0, 191, 99, 0.05) 0%, transparent 40%),
                radial-gradient(circle at 90% 80%, rgba(0, 191, 99, 0.03) 0%, transparent 40%),
                radial-gradient(circle at 50% 50%, #f8fafc 0%, #ffffff 100%);
        }

        .floating-sidebar {
            width: var(--sidebar-width);
            height: calc(100vh - 40px);
            position: fixed;
            top: 20px;
            left: 20px;
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            border: 1px solid rgba(0, 0, 0, 0.05);
            z-index: 50;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            flex-direction: column;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        }

        .main-content {
            margin-left: calc(var(--sidebar-width) + 40px);
            padding: 20px;
            min-height: 100vh;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .glass-nav {
            background: rgba(255, 255, 255, 0.6);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(0, 0, 0, 0.05);
            border-radius: 16px;
            margin-bottom: 24px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
        }

        .nav-item {
            display: flex;
            align-items: center;
            padding: 12px 16px;
            margin: 4px 12px;
            border-radius: 12px;
            color: #64748b;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.2s;
            gap: 12px;
        }

        .nav-item:hover {
            color: var(--primary);
            background: rgba(0, 191, 99, 0.05);
        }

        .nav-item.active {
            color: white;
            background: linear-gradient(135deg, var(--primary), #059669);
            box-shadow: 0 4px 15px rgba(0, 191, 99, 0.3);
        }

        .card-premium {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(0, 0, 0, 0.05);
            border-radius: 20px;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .card-premium:hover {
            transform: translateY(-4px);
            border-color: rgba(0, 191, 99, 0.2);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.05);
        }

        .gradient-text {
            background: linear-gradient(to right, #00bf63, #059669);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        /* Responsive Mobile Sidebar */
        @media (max-width: 1280px) {
            .floating-sidebar {
                left: -300px;
            }

            .floating-sidebar.open {
                left: 20px;
            }

            .main-content {
                margin-left: 0;
            }

            .floating-sidebar.open~.main-content {
                margin-left: 0;
                opacity: 0.5;
                pointer-events: none;
            }
        }

        /* Scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        /* Global Text & Icon Color Fixes */
        .card-premium label,
        .card-premium p,
        .card-premium h1,
        .card-premium h2,
        .card-premium h3,
        .card-premium h4,
        header h1,
        header p,
        .nav-item span,
        .text-slate-100,
        .text-slate-200,
        .text-slate-300,
        .text-slate-400,
        .text-gray-100,
        .text-gray-200,
        .text-gray-300,
        .text-gray-400 {
            color: #1e293b !important;
        }

        /* Force dark text for specific white classes unless on a primary button or active nav */
        :not(.bg-indigo-600):not(.bg-primary):not(.bg-primary-600).text-white,
        :not(.nav-item.active).text-white {
            color: #1e293b !important;
        }

        .nav-item i,
        i.fas,
        i.fab,
        i.far {
            color: var(--primary) !important;
        }

        .nav-item.active i,
        .nav-item.active span {
            color: white !important;
        }

        input:not([type="checkbox"]),
        select,
        textarea {
            color: #1e293b !important;
            background-color: rgba(255, 255, 255, 0.8) !important;
            border-color: rgba(0, 0, 0, 0.1) !important;
        }

        input::placeholder,
        textarea::placeholder {
            color: rgba(0, 0, 0, 0.3) !important;
        }
    </style>

    @if (file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @elseif (file_exists(public_path('build/manifest.json')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
        <script>
            tailwind.config = {
                theme: {
                    extend: {
                        fontFamily: {
                            'sans': ['Plus Jakarta Sans', 'Outfit', 'system-ui', 'sans-serif']
                        },
                        colors: {
                            primary: {
                                50: '#f0fdf4',
                                100: '#dcfce7',
                                200: '#bbf7d0',
                                300: '#86efac',
                                400: '#4ade80',
                                500: '#22c55e',
                                600: '#00bf63',
                                700: '#15803d',
                                800: '#166534',
                                900: '#14532d',
                                950: '#052e16'
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
    {{-- <style>
        [x-cloak] {
            display: none !important;
        }

        :root {
            --primary-glow: conic-gradient(from 180deg at 50% 50%, #16abff33 0deg, #0885ff33 55deg, #54d6ff33 120deg, #0071ff33 160deg, transparent 360deg);
            --secondary-glow: radial-gradient(rgba(255, 255, 255, 1), rgba(255, 255, 255, 0));
        }

        body {
            background-attachment: fixed;
            letter-spacing: -0.01em;
        }

        .premium-bg {
            background: radial-gradient(circle at top right, rgba(124, 58, 237, 0.05), transparent 40%),
                radial-gradient(circle at bottom left, rgba(59, 130, 246, 0.05), transparent 40%);
        }

        .dark .premium-bg {
            background: radial-gradient(circle at top right, rgba(124, 58, 237, 0.1), transparent 40%),
                radial-gradient(circle at bottom left, rgba(59, 130, 246, 0.1), transparent 40%);
        }

        .sidebar-transition {
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .card-premium {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .dark .card-premium {
            background: rgba(17, 24, 39, 0.6);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(55, 65, 81, 0.3);
        }

        .card-premium:hover {
            transform: translateY(-2px);
            background: rgba(255, 255, 255, 0.8);
            border-color: rgba(124, 58, 237, 0.2);
            box-shadow: 0 20px 40px -20px rgba(0, 0, 0, 0.1);
        }

        .dark .card-premium:hover {
            background: rgba(17, 24, 39, 0.8);
            border-color: rgba(124, 58, 237, 0.3);
            box-shadow: 0 20px 40px -20px rgba(0, 0, 0, 0.5);
        }

        .gradient-text {
            background: linear-gradient(135deg, #7c3aed 0%, #3b82f6 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .nav-link-active {
            background: linear-gradient(90deg, rgba(124, 58, 237, 0.1) 0%, transparent 100%);
            border-left: 3px solid #7c3aed;
        }

        .dark .nav-link-active {
            background: linear-gradient(90deg, rgba(124, 58, 237, 0.2) 0%, transparent 100%);
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

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: rgba(124, 58, 237, 0.1);
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: rgba(124, 58, 237, 0.3);
        }

        .sidebar-item {
            position: relative;
            overflow: hidden;
        }

        .sidebar-item::after {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%) translateX(-100%);
            width: 4px;
            height: 60%;
            background: #7c3aed;
            border-radius: 0 4px 4px 0;
            transition: transform 0.3s ease;
        }

        .sidebar-item:hover::after,
        .sidebar-item.active::after {
            transform: translateY(-50%) translateX(0);
        }
    </style> --}}

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    @stack('styles')
</head>

@php
    $user = auth()->user();
    $isAdmin = $user && ((method_exists($user, 'isAdmin') && $user->isAdmin()) || (method_exists($user, 'hasRole') && $user->hasRole('admin')) || ($user->role ?? null) === 'admin');
    $isSuperAgent = $user && ((method_exists($user, 'isSuperAgent') && $user->isSuperAgent()) || ($user->role ?? null) === 'super_agent');
    $isSuperAgent1 = $user && ((method_exists($user, 'isSuperAgent1') && $user->isSuperAgent1()) || ($user->role ?? null) === 'super_agent_1');
    $isMaxOutUser = $user && (($user->role ?? null) === 'max_out');
    $isThatSubmittedUser = $user && (($user->role ?? null) === 'death_submitted');
    $isRegularUser = $user && (($user->role ?? null) === 'user');
    $isReportManager = $user && (($user->role ?? null) === 'report_manager');
@endphp

<body x-data="{ sidebarOpen: false }">
    <div class="mesh-bg"></div>

    {{-- Sidebar --}}
    <aside class="floating-sidebar" :class="{ 'open': sidebarOpen }">
        <div class="px-8 py-8 flex flex-col items-center justify-center">
            <a href="{{ route('dashboard') }}" class="group transition-transform hover:scale-105 duration-300">
                <img src="{{ asset('logo.png') }}" alt="DOT Logo" class="max-w-[180px] h-auto object-contain mix-blend-multiply"
                    onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\'flex items-center gap-2\'><i class=\'fas fa-dot-circle text-primary text-3xl\'></i><span class=\'text-2xl font-black text-slate-900 tracking-tighter\'>DOT.</span></div>'">
            </a>
        </div>

        <nav class="flex-1 overflow-y-auto mt-4 px-2">
            @php $currentRoute = request()->route()->getName(); @endphp

            <div class="px-6 mb-4 mt-6">
                <p class="text-[10px] font-bold uppercase tracking-widest text-slate-500">Core Navigation</p>
            </div>

            @if ($isSuperAgent || $isSuperAgent1 || $isAdmin)
                <a href="{{ route('reports.index') }}"
                    class="nav-item {{ $currentRoute === 'reports.index' ? 'active' : '' }}">
                    <i class="fas fa-chart-line w-5"></i>
                    <span>Intelligence Reports</span>
                </a>
            @endif

            <a href="{{ route('announcements.index') }}"
                class="nav-item {{ $currentRoute === 'announcements.index' ? 'active' : '' }}">
                <i class="fas {{ $isAdmin ? 'fa-bullhorn' : 'fa-bell' }} w-5"></i>
                <span>{{ $isAdmin ? 'Announcement Room' : 'Team Notifications' }}</span>
            </a>

            <a href="{{ route('profile.edit') }}"
                class="nav-item {{ $currentRoute === 'profile.edit' ? 'active' : '' }}">
                <i class="fas fa-user-circle w-5"></i>
                <span>Profile</span>
            </a>

            @if ($isAdmin)
                <div class="px-6 mb-4 mt-8">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-slate-500">Administration</p>
                </div>
                <a href="{{ route('dashboard') }}" class="nav-item {{ $currentRoute === 'dashboard' ? 'active' : '' }}">
                    <i class="fas fa-th-large w-5"></i>
                    <span>Dashboard</span>
                </a>
                <a href="{{ route('leads.index') }}" class="nav-item {{ $currentRoute === 'leads.index' ? 'active' : '' }}">
                    <i class="fas fa-address-book w-5"></i>
                    <span>Leads Vault</span>
                </a>
                <a href="{{ route('users.index') }}" class="nav-item {{ $currentRoute === 'users.index' ? 'active' : '' }}">
                    <i class="fas fa-users-cog w-5"></i>
                    <span>Team Access</span>
                </a>
                <a href="{{ route('reports.index') }}"
                    class="nav-item {{ $currentRoute === 'reports.index' ? 'active' : '' }}">
                    <i class="fas fa-file-invoice w-5"></i>
                    <span>Global Reports</span>
                </a>
            @endif

            @if ($isMaxOutUser)
                <div class="px-6 mb-4 mt-8">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-slate-500">Specialist</p>
                </div>
                <a href="{{ route('dashboard') }}" class="nav-item {{ $currentRoute === 'dashboard' ? 'active' : '' }}">
                    <i class="fas fa-th-large w-5"></i>
                    <span>Dashboard</span>
                </a>
                <a href="{{ route('leads.maxout') }}"
                    class="nav-item {{ $currentRoute === 'leads.maxout' ? 'active' : '' }}">
                    <i class="fas fa-fire-alt w-5"></i>
                    <span>Maxout Leads</span>
                </a>
            @endif

            @if ($isThatSubmittedUser)
                <div class="px-6 mb-4 mt-8">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-slate-500">Submissions</p>
                </div>
                <a href="{{ route('dashboard') }}" class="nav-item {{ $currentRoute === 'dashboard' ? 'active' : '' }}">
                    <i class="fas fa-th-large w-5"></i>
                    <span>Dashboard</span>
                </a>
                <a href="{{ route('leads.submitted') }}"
                    class="nav-item {{ $currentRoute === 'leads.submitted' ? 'active' : '' }}">
                    <i class="fas fa-skull-crossbones w-5"></i>
                    <span>Death Leads</span>
                </a>
            @endif

            @if ($isRegularUser)
                <div class="px-6 mb-4 mt-8">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-slate-500">Agent Zone</p>
                </div>
                <a href="{{ route('dashboard') }}" class="nav-item {{ $currentRoute === 'dashboard' ? 'active' : '' }}">
                    <i class="fas fa-th-large w-5"></i>
                    <span>Overview</span>
                </a>
                <a href="{{ route('leads.mine') }}" class="nav-item {{ $currentRoute === 'leads.mine' ? 'active' : '' }}">
                    <i class="fas fa-list-ul w-5"></i>
                    <span>My Workspace</span>
                </a>
                <a href="{{ route('leads.callbacks') }}"
                    class="nav-item {{ $currentRoute === 'leads.callbacks' ? 'active' : '' }}">
                    <i class="fas fa-phone-alt w-5"></i>
                    <span>Followups</span>
                </a>
                <a href="{{ route('leads.index') }}" class="nav-item {{ $currentRoute === 'leads.index' ? 'active' : '' }}">
                    <i class="fas fa-search w-5"></i>
                    <span>Lead Search</span>
                </a>
            @endif
        </nav>

        <div class="p-6 mt-auto">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                    class="w-full flex items-center justify-center gap-2 py-3 bg-red-500/10 hover:bg-red-500 text-red-500 hover:text-white rounded-xl font-bold transition-all duration-300">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </button>
            </form>
        </div>
    </aside>

    {{-- Main Content Window --}}
    <main class="main-content">
        {{-- Top Bar Island --}}
        <header class="glass-nav px-8 py-4 flex items-center justify-between">
            <div class="flex items-center gap-4 lg:hidden">
                <button @click="sidebarOpen = !sidebarOpen"
                    class="w-10 h-10 flex items-center justify-center bg-white/5 rounded-lg">
                    <i class="fas fa-bars text-white"></i>
                </button>
            </div>

            <div>
                <h2 class="text-xs font-bold uppercase tracking-widest text-slate-500">Current View</h2>
                <h1 class="text-xl font-extrabold text-white">@yield('page-title', 'Dashboard')</h1>
            </div>

            <div class="flex items-center gap-6">
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
                                    class="p-2 text-gray-500 hover:text-white relative hover:bg-white/5 rounded-lg transition-colors"
                                    aria-label="Notifications">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M14.857 17.082A4.002 4.002 0 0 1 12 19a4.002 4.002 0 0 1-2.857-1.918M6.5 8a5.5 5.5 0 1 1 11 0c0 3.07.582 4.626 1.08 5.428.38.608.57.912.566 1.107a.75.75 0 0 1-.428.63c-.174.085-.526.085-1.23.085H6.512c-.704 0-1.056 0-1.23-.085a.75.75 0 0 1-.428-.63c-.004-.195.185-.499.566-1.107C5.918 12.626 6.5 11.07 6.5 8Z" />
                                    </svg>
                                    <span x-show="unreadRtCount > 0 || unreadDbCount > 0"
                                        class="absolute top-1 right-1 min-w-[1.1rem] h-4 px-1 grid place-items-center rounded-full bg-red-600 text-white text-[10px] font-bold shadow animate-pulse"
                                        x-text="unreadRtCount + unreadDbCount"></span>
                                </button>

                                <!-- Notifications Dropdown -->
                                <div x-show="open" @click.away="open = false" x-cloak
                                    x-transition:enter="transition ease-out duration-200"
                                    x-transition:enter-start="opacity-0 translate-y-2"
                                    x-transition:enter-end="opacity-100 translate-y-0"
                                    x-transition:leave="transition ease-in duration-150"
                                    x-transition:leave-start="opacity-100 translate-y-0"
                                    x-transition:leave-end="opacity-0 translate-y-2"
                                    class="absolute right-0 mt-3 w-80 sm:w-96 bg-slate-800 border border-white/10 rounded-2xl shadow-2xl py-2 z-50">
                                    <div class="px-5 py-4 border-b border-white/10 flex justify-between items-center">
                                        <h3 class="font-bold text-white tracking-tight">Intelligence Stream</h3>
                                        <button @click="markAllRead()"
                                            class="text-xs font-bold text-primary-400 hover:text-primary-300 cursor-pointer transition-colors">
                                            Archive All
                                        </button>
                                    </div>

                                    <div class="max-h-96 overflow-y-auto">
                                        <template x-if="rtItems.length > 0">
                                            <div class="px-5 py-2 text-[10px] font-bold uppercase tracking-widest text-slate-500">
                                                Active Protocol
                                            </div>
                                        </template>
                                        <div x-ref="rtList" class="divide-y divide-white/5">
                                            <template x-for="it in rtItems" :key="it.id + '-' + it.updated_at">
                                                <div class="px-5 py-4 hover:bg-white/5 transition-colors group"
                                                    :class="{ 'bg-primary-500/5': it.unread }">
                                                    <a :href="getNotificationUrl(it)" class="block" @click="markAsRead(it)">
                                                        <div class="flex items-start gap-4">
                                                            <div
                                                                class="w-10 h-10 rounded-xl bg-white/5 flex items-center justify-center text-primary-400 group-hover:scale-110 transition-transform">
                                                                <i class="fas fa-satellite-dish"></i>
                                                            </div>
                                                            <div class="flex-1 min-w-0">
                                                                <p class="text-sm font-bold text-white flex items-center gap-2">
                                                                    <span
                                                                        x-text="it.issue ? `Issue #${it.issue.id}` : `Lead #${it.id}`"></span>
                                                                    <span x-show="it.unread"
                                                                        class="h-1.5 w-1.5 rounded-full bg-red-500 shadow-lg shadow-red-500/40"></span>
                                                                </p>
                                                                <p class="text-xs text-slate-400 line-clamp-2 mt-0.5"
                                                                    x-text="it.message || it.first_name + ' ' + it.surname"></p>
                                                                <div class="flex items-center gap-3 mt-2">
                                                                    <span
                                                                        class="text-[10px] font-black uppercase text-slate-500 tracking-tighter"
                                                                        x-text="timeAgo(it.issue ? it.issue.updated_at : it.updated_at)"></span>
                                                                    <span
                                                                        class="px-2 py-0.5 rounded-md bg-primary-500/10 text-primary-400 text-[9px] font-black uppercase tracking-wider"
                                                                        x-text="it.status || 'Active'"></span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </a>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </div>
                @endif

                <!-- User Profile Menu -->
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" id="userMenuButton"
                        class="flex items-center gap-3 p-2 bg-white/5 hover:bg-white/10 rounded-xl transition-all border border-white/5 group">
                        <div
                            class="w-8 h-8 rounded-lg bg-gradient-to-br from-primary-400 to-indigo-500 flex items-center justify-center text-white font-bold text-xs shadow-lg">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                        <div class="hidden md:block text-left">
                            <p class="text-xs font-extrabold text-slate-900 leading-none">{{ $user->name }}</p>
                            <p class="text-[10px] font-medium text-slate-500 mt-1 uppercase tracking-tighter">
                                {{ $user->role }}
                            </p>
                        </div>
                        <i
                            class="fas fa-chevron-down text-[10px] text-slate-500 group-hover:text-white transition-colors"></i>
                    </button>

                    <div x-show="open" @click.away="open = false" x-cloak
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 translate-y-2"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 translate-y-2"
                        class="absolute right-0 mt-3 w-56 bg-white dark:bg-slate-800 rounded-2xl shadow-2xl border border-gray-200 dark:border-white/10 py-2 z-50 overflow-hidden"
                        id="userMenu">
                        <div class="px-5 py-3 border-b border-gray-100 dark:border-white/5">
                            <p class="text-sm font-bold text-gray-900 dark:text-white truncate">{{ $user->email }}</p>
                        </div>
                        <a href="{{ route('profile.edit') }}"
                            class="flex items-center gap-3 px-5 py-3 text-sm font-medium text-gray-500 dark:text-slate-400 hover:text-primary-500 dark:hover:text-white hover:bg-gray-50 dark:hover:bg-white/5 transition-all">
                            <i class="fas fa-user-circle"></i>
                            <span>Profile Protocol</span>
                        </a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                class="w-full flex items-center gap-3 px-5 py-3 text-sm font-bold text-red-500 hover:bg-red-500/10 transition-all">
                                <i class="fas fa-power-off"></i>
                                <span>Deactivate Session</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        {{-- Page Content Container --}}
        <div class="px-8 pb-10">
            @if (session('error'))
                <div
                    class="mb-6 bg-red-500/10 border border-red-500/20 text-red-400 px-6 py-4 rounded-2xl shadow-xl animate-slide-down flex items-center gap-4">
                    <i class="fas fa-exclamation-triangle"></i>
                    <p class="font-bold text-sm">{{ session('error') }}</p>
                </div>
            @endif

            @if (session('status'))
                <div
                    class="mb-6 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 px-6 py-4 rounded-2xl shadow-xl animate-slide-down flex items-center gap-4">
                    <i class="fas fa-check-circle"></i>
                    <p class="font-bold text-sm">
                        @if (session('status') === 'profile-updated') Protocol: Profile updated successfully!
                        @elseif(session('status') === 'password-updated') Protocol: Access credentials secure!
                        @else {{ session('status') }}
                        @endif
                    </p>
                </div>
            @endif

            @if ($errors->any())
                <div
                    class="mb-6 bg-amber-500/10 border border-amber-500/20 text-amber-400 px-6 py-4 rounded-2xl shadow-xl animate-slide-down">
                    <div class="flex items-start gap-4">
                        <i class="fas fa-shield-alt mt-1"></i>
                        <ul class="list-none font-bold text-sm space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <div class="min-h-[calc(100vh-200px)]">
                @yield('content')
            </div>
        </div>
    </main>
    </div>
    </div>

    <!-- Overlay for mobile sidebar -->
    <div id="sidebarOverlay" class="fixed inset-0 bg-gray-600/75 backdrop-blur-sm z-40 lg:hidden hidden"></div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const sidebar = document.getElementById('sidebar');
            const openSidebarBtn = document.getElementById('openSidebar');
            const closeSidebarBtn = document.getElementById('closeSidebar');
            const sidebarOverlay = document.getElementById('sidebarOverlay');
            const userMenuButton = document.getElementById('userMenuButton');
            const userMenu = document.getElementById('userMenu');

            // --- Mobile drawer toggle ---
            if (openSidebarBtn) {
                openSidebarBtn.addEventListener('click', function () {
                    sidebar.classList.remove('-translate-x-full'); // show
                    sidebarOverlay.classList.remove('hidden');
                });
            }
            if (closeSidebarBtn) {
                closeSidebarBtn.addEventListener('click', function () {
                    sidebar.classList.add('-translate-x-full'); // hide
                    sidebarOverlay.classList.add('hidden');
                });
            }
            if (sidebarOverlay) {
                sidebarOverlay.addEventListener('click', function () {
                    sidebar.classList.add('-translate-x-full');
                    sidebarOverlay.classList.add('hidden');
                });
            }

            // --- User menu toggle ---
            if (userMenuButton && userMenu) {
                userMenuButton.addEventListener('click', function () {
                    userMenu.classList.toggle('hidden');
                });
                document.addEventListener('click', function (event) {
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

                    timeAgo(iso) {
                        const d = new Date(iso);
                        const sec = Math.floor((Date.now() - d.getTime()) / 1000);
                        if (sec < 60) return `${sec}s ago`;
                        const m = Math.floor(sec / 60);
                        if (m < 60) return `${m}m ago`;
                        const h = Math.floor(m / 60);
                        if (h < 24) return `${h}h ago`;
                        return `${Math.floor(h / 24)}d ago`;
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
    (function () {
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
            }).catch(() => { });
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