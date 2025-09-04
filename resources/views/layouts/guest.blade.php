<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Leads Portal — @yield('title', 'Sign in')</title>

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
    @endif
    <style>
        .glass {
            background: rgba(17, 24, 39, .75);
            border: 1px solid rgba(255, 255, 255, .08);
            backdrop-filter: blur(12px) saturate(120%);
        }
    </style>
</head>

<body class="min-h-screen bg-gradient-to-br from-gray-900 via-gray-900 to-slate-900 text-gray-100">
    <div class="min-h-screen flex items-center justify-center p-6">
        @yield('content')
    </div>
    @stack('scripts')
</body>

</html>
