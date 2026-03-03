{{-- resources/views/auth/login.blade.php --}}
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Intelligence Access — Leads Portal</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Outfit:wght@100..900&family=Plus+Jakarta+Sans:ital,wght@0,200..800;1,200..800&display=swap"
        rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        'sans': ['Plus Jakarta Sans', 'Outfit', 'system-ui', 'sans-serif'],
                        'display': ['Outfit', 'sans-serif']
                    },
                    colors: {
                        primary: {
                            500: '#00bf63',
                            600: '#009e52',
                            700: '#007b40',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        .glass-card {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(40px) saturate(180%);
            -webkit-backdrop-filter: blur(40px) saturate(180%);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }

        .gradient-text {
            background: linear-gradient(135deg, #00bf63 0%, #059669 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .mesh-bg {
            background-color: #020617;
            background-image:
                radial-gradient(at 0% 0%, hsla(152, 100%, 37%, 0.1) 0px, transparent 50%),
                radial-gradient(at 100% 0%, hsla(152, 100%, 37%, 0.1) 0px, transparent 50%),
                radial-gradient(at 100% 100%, hsla(152, 100%, 37%, 0.05) 0px, transparent 50%),
                radial-gradient(at 0% 100%, hsla(152, 100%, 37%, 0.05) 0px, transparent 50%);
        }
    </style>
</head>

<body class="mesh-bg min-h-screen text-slate-200 selection:bg-indigo-500/30 font-sans overflow-hidden">
    {{-- Animated Orbs --}}
    <div class="fixed inset-0 pointer-events-none">
        <div
            class="absolute top-[20%] left-[10%] w-[500px] h-[500px] bg-emerald-600/5 blur-[120px] rounded-full animate-pulse">
        </div>
        <div class="absolute bottom-[20%] right-[10%] w-[600px] h-[600px] bg-green-600/5 blur-[150px] rounded-full animate-pulse"
            style="animation-delay: 2s"></div>
    </div>

    <main class="relative z-10 flex min-h-screen items-center justify-center p-6">
        <div class="w-full max-w-[480px] space-y-8">
            {{-- Logo Area --}}
            <div class="text-center space-y-6">
                <div class="flex justify-center">
                    <div class="relative group">
                        <div class="absolute -inset-4 bg-primary-500/20 blur-2xl rounded-full opacity-0 group-hover:opacity-100 transition-opacity"></div>
                        <img src="/logo.png" alt="DOT Logo" class="w-24 h-24 object-contain relative z-10 transition-transform group-hover:scale-110" onerror="this.onerror=null; this.src='https://via.placeholder.com/100?text=DOT'">
                    </div>
                </div>
                <div>
                    <h1 class="text-5xl font-black tracking-tighter text-white mb-2 font-display">
                        DOT<span class="text-primary-500">.</span>
                    </h1>
                    <p class="text-slate-500 font-bold tracking-[0.3em] uppercase text-[10px]">Digital Operations Team</p>
                </div>
            </div>

            {{-- Login Card --}}
            <div class="glass-card rounded-[40px] p-10 shadow-2xl relative overflow-hidden">
                {{-- Abstract Accent --}}
                <div
                    class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-transparent via-indigo-500/50 to-transparent">
                </div>

                <form method="POST" action="{{ route('login') }}" class="space-y-6">
                    @csrf

                    <div class="space-y-2">
                        <label for="email"
                            class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 ml-1">Terminal ID
                            (Email)</label>
                        <div class="relative group">
                            <i
                                class="fas fa-envelope absolute left-5 top-1/2 -translate-y-1/2 text-slate-600 group-focus-within:text-indigo-400 transition-colors"></i>
                            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                                class="w-full pl-14 pr-6 py-4 bg-white/[0.03] border border-white/10 rounded-2xl text-white placeholder:text-slate-700 focus:bg-white/[0.05] focus:border-indigo-500/50 focus:ring-0 outline-none transition-all"
                                placeholder="name@salestech.online">
                        </div>
                        @error('email')
                            <p class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <div class="flex items-center justify-between ml-1">
                            <label for="password"
                                class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-500">Access
                                Key</label>
                            <a href="#"
                                class="text-[10px] font-black uppercase tracking-[0.2em] text-indigo-400 hover:text-indigo-300 transition-colors">Lost
                                Key?</a>
                        </div>
                        <div class="relative group">
                            <i
                                class="fas fa-lock absolute left-5 top-1/2 -translate-y-1/2 text-slate-600 group-focus-within:text-indigo-400 transition-colors"></i>
                            <input id="password" type="password" name="password" required
                                class="w-full pl-14 pr-14 py-4 bg-white/[0.03] border border-white/10 rounded-2xl text-white placeholder:text-slate-700 focus:bg-white/[0.05] focus:border-indigo-500/50 focus:ring-0 outline-none transition-all"
                                placeholder="••••••••••••">
                            <button type="button"
                                class="absolute right-5 top-1/2 -translate-y-1/2 text-slate-600 hover:text-white transition-colors"
                                onclick="const i=document.getElementById('password'); i.type = i.type==='password' ? 'text' : 'password'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <div class="relative">
                                <input type="checkbox" name="remember" class="peer sr-only">
                                <div
                                    class="w-5 h-5 bg-white/5 border border-white/10 rounded-lg peer-checked:bg-indigo-600 peer-checked:border-indigo-500 transition-all">
                                </div>
                                <i
                                    class="fas fa-check absolute inset-0 flex items-center justify-center text-[10px] text-white opacity-0 peer-checked:opacity-100 transition-opacity"></i>
                            </div>
                            <span
                                class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 group-hover:text-slate-300 transition-colors">Persist
                                Session</span>
                        </label>
                    </div>

                    <button type="submit"
                        class="w-full py-5 bg-primary-600 hover:bg-primary-500 text-white rounded-2xl font-black uppercase tracking-[0.3em] text-xs transition-all shadow-2xl shadow-primary-600/20 active:scale-[0.98]">
                        Authorize Access
                    </button>
                </form>

                @if ($errors->any())
                    <div class="mt-6 p-4 bg-rose-500/10 border border-rose-500/20 rounded-2xl">
                        <p class="text-[10px] font-black text-rose-400 uppercase tracking-widest text-center">
                            {{ $errors->first() }}</p>
                    </div>
                @endif
            </div>

            <div class="text-center text-[10px] font-black uppercase tracking-[0.4em] text-slate-600">
                SalesTech Services Intelligence <span class="text-indigo-500/50">v2.4.0</span>
            </div>
        </div>
    </main>

    {{-- Font Awesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</body>

</html>