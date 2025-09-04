{{-- resources/views/auth/login.blade.php --}}
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign in — Leads Portal</title>

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
        <script>
            tailwind.config = {
                theme: {
                    extend: {
                        colors: {
                            primary: {
                                500: '#3b82f6',
                                600: '#2563eb',
                                700: '#1d4ed8',
                            }
                        },
                        boxShadow: {
                            'elev': '0 10px 30px -10px rgba(0,0,0,.45), 0 12px 14px -10px rgba(0,0,0,.35)'
                        }
                    }
                }
            }
        </script>
    @endif
</head>

<body class="min-h-screen bg-[#0b1220] text-white selection:bg-primary-600 selection:text-white">

    {{-- background decoration --}}
    <div class="pointer-events-none fixed inset-0 overflow-hidden">
        <div
            class="absolute -top-32 left-1/2 -translate-x-1/2 h-[34rem] w-[34rem] rounded-full bg-primary-600/20 blur-3xl">
        </div>
        <div class="absolute bottom-0 -right-20 h-[28rem] w-[28rem] rounded-full bg-indigo-900/40 blur-3xl"></div>
    </div>

    <main class="relative z-10 flex items-center justify-center py-10 px-4">
        <div class="w-full max-w-md">
            <div class="rounded-2xl bg-white/5 border border-white/10 shadow-elev backdrop-blur-xl">
                {{-- Header --}}
                <div class="px-8 pt-8 text-center">
                    <div
                        class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-primary-600/20 ring-1 ring-primary-600/30">
                        <svg class="h-7 w-7 text-primary-500" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <h1 class="text-2xl font-bold tracking-tight">Leads Portal</h1>
                    <p class="mt-1 text-sm text-white/60">Sign in to continue</p>
                </div>

                {{-- Alerts --}}
                @if (session('status'))
                    <div
                        class="mx-8 mt-6 rounded-lg border border-emerald-500/30 bg-emerald-500/10 px-4 py-2 text-sm text-emerald-200">
                        {{ session('status') }}
                    </div>
                @endif

                {{-- Form --}}
                <form method="POST" action="{{ route('login') }}" class="px-8 pb-8 pt-6 space-y-5">
                    @csrf

                    {{-- Email --}}
                    <div>
                        <label for="email" class="mb-2 block text-sm font-medium text-white/80">Email</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-3 flex items-center text-white/40">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 12H8m8 4H8m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </span>
                            <input id="email" type="email" name="email" value="{{ old('email') }}" required
                                autofocus
                                class="w-full rounded-xl bg-white/5 border border-white/10 pl-10 pr-3 py-3 text-white placeholder-white/30 outline-none focus:ring-2 focus:ring-primary-600 focus:border-primary-600"
                                placeholder="you@example.com">
                        </div>
                        @error('email')
                            <p class="mt-2 text-xs text-rose-300">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Password --}}
                    <div>
                        <div class="mb-2 flex items-center justify-between">
                            <label for="password" class="block text-sm font-medium text-white/80">Password</label>
                            {{-- @if (Route::has('password.request')) --}}
                                <a href="#"
                                    class="text-xs text-primary-500 hover:text-primary-400">Forgot password?</a>
                            {{-- @endif --}}
                        </div>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-3 flex items-center text-white/40">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 11c1.657 0 3-1.343 3-3S13.657 5 12 5 9 6.343 9 8s1.343 3 3 3zM19 21H5a2 2 0 01-2-2v-2a7 7 0 0114 0v2a2 2 0 012 2z" />
                                </svg>
                            </span>
                            <input id="password" type="password" name="password" required
                                class="w-full rounded-xl bg-white/5 border border-white/10 pl-10 pr-12 py-3 text-white placeholder-white/30 outline-none focus:ring-2 focus:ring-primary-600 focus:border-primary-600"
                                placeholder="••••••••">
                            <button type="button" aria-label="Toggle password"
                                class="absolute inset-y-0 right-3 flex items-center text-white/40 hover:text-white/70"
                                onclick="const i=document.getElementById('password'); i.type = i.type==='password' ? 'text' : 'password'">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>
                        </div>
                        @error('password')
                            <p class="mt-2 text-xs text-rose-300">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Remember --}}
                    <div class="flex items-center justify-between">
                        <label class="inline-flex items-center text-sm text-white/70">
                            <input type="checkbox" name="remember"
                                class="mr-2 h-4 w-4 rounded border-white/20 bg-white/5 text-primary-600 focus:ring-primary-600">
                            Remember me
                        </label>
                        <span class="text-[10px] text-white/40">v1.0</span>
                    </div>

                    {{-- Submit --}}
                    <button
                        class="mt-1 w-full rounded-xl bg-primary-600 py-3 font-semibold text-white shadow-lg shadow-primary-900/30 transition hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-0 focus:ring-primary-600">
                        Sign in
                    </button>

                    {{-- Errors (global) --}}
                    @if ($errors->any())
                        <div
                            class="mt-3 rounded-lg border border-rose-500/30 bg-rose-500/10 px-4 py-2 text-xs text-rose-200">
                            {{ $errors->first() }}
                        </div>
                    @endif
                </form>
            </div>

            <p class="mt-4 text-center text-[11px] text-white/50">
                Use your portal credentials. Admins can manage users & leads; users can update assigned leads.
            </p>
        </div>
    </main>
</body>

</html>
