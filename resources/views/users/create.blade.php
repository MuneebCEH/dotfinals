{{-- resources/views/users/create.blade.php --}}
@extends('layouts.app')

@section('title', 'Onboard Agent')
@section('page-title', 'Onboard Agent')

@section('content')
    <div class="space-y-6 animate-on-load">
        {{-- Header --}}
        <div
            class="card-premium p-8 flex flex-col md:flex-row md:items-center justify-between gap-6 overflow-hidden relative">
            <div class="relative z-10">
                <h1 class="text-4xl font-black tracking-tight text-white mb-2">Agent <span
                        class="gradient-text">Onboarding</span></h1>
                <p class="text-slate-400 font-medium">Initializing new security credentials for the intelligence network.
                </p>
            </div>

            <a href="{{ route('users.index') }}"
                class="px-8 py-4 bg-white/5 hover:bg-white/10 text-white rounded-2xl font-black transition-all border border-white/10 flex items-center gap-3 group relative z-10">
                <i class="fas fa-arrow-left group-hover:-translate-x-1 transition-transform"></i>
                <span>Return to Matrix</span>
            </a>

            <div class="absolute -right-20 -top-20 w-64 h-64 bg-indigo-500/10 blur-[100px] rounded-full"></div>
        </div>

        {{-- Form Workspace --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2">
                <div class="card-premium overflow-hidden">
                    <div class="p-6 border-b border-white/5 bg-white/[0.02]">
                        <h3 class="text-[10px] font-black uppercase tracking-widest text-slate-500">Core Identity Protocol
                        </h3>
                    </div>

                    <form action="{{ route('users.store') }}" method="POST" class="p-8 space-y-8">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label for="name"
                                    class="text-[10px] font-black uppercase tracking-widest text-slate-500 ml-1">Legal
                                    Designation (Name)</label>
                                <input type="text" id="name" name="name" value="{{ old('name') }}" required
                                    class="w-full px-5 py-4 bg-white/5 border border-white/10 rounded-2xl text-white placeholder:text-slate-600 focus:border-indigo-500/50 focus:ring-0 transition-all"
                                    placeholder="e.g. Alexander Vance">
                                @error('name') <p class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="space-y-2">
                                <label for="email"
                                    class="text-[10px] font-black uppercase tracking-widest text-slate-500 ml-1">Network
                                    Identifier (Email)</label>
                                <input type="email" id="email" name="email" value="{{ old('email') }}" required
                                    class="w-full px-5 py-4 bg-white/5 border border-white/10 rounded-2xl text-white placeholder:text-slate-600 focus:border-indigo-500/50 focus:ring-0 transition-all"
                                    placeholder="agent@nexus.system">
                                @error('email') <p class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="space-y-2">
                                <label for="role"
                                    class="text-[10px] font-black uppercase tracking-widest text-slate-500 ml-1">Protocol
                                    Authority (Role)</label>
                                <div class="relative">
                                    <select id="role" name="role" required
                                        class="w-full px-5 py-4 bg-white/5 border border-white/10 rounded-2xl text-white appearance-none focus:border-indigo-500/50 focus:ring-0 transition-all cursor-pointer">
                                        <option value="user" {{ old('role') === 'user' ? 'selected' : '' }}>Standard Agent
                                        </option>
                                        <option value="closer" {{ old('role') === 'closer' ? 'selected' : '' }}>Closer</option>
                                        <option value="super_agent" {{ old('role') === 'super_agent' ? 'selected' : '' }}>
                                            Super agent</option>
                                        <option value="report_manager" {{ old('role') === 'report_manager' ? 'selected' : '' }}>Report Manager</option>
                                        <option value="lead_manager" {{ old('role') === 'lead_manager' ? 'selected' : '' }}>
                                            TL Manager</option>
                                        <option value="max_out" {{ old('role') === 'max_out' ? 'selected' : '' }}>VM Protocol
                                        </option>
                                        <option value="death_submitted" {{ old('role') === 'death_submitted' ? 'selected' : '' }}>verification manager</option>
                                        <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>System
                                            Administrator</option>
                                    </select>
                                    <i
                                        class="fas fa-chevron-down absolute right-5 top-1/2 -translate-y-1/2 text-slate-500 pointer-events-none"></i>
                                </div>
                                @error('role') <p class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="space-y-2">
                                <label for="phone"
                                    class="text-[10px] font-black uppercase tracking-widest text-slate-500 ml-1">Comm Link
                                    (Phone)</label>
                                <input type="tel" id="phone" name="phone" value="{{ old('phone') }}"
                                    class="w-full px-5 py-4 bg-white/5 border border-white/10 rounded-2xl text-white placeholder:text-slate-600 focus:border-indigo-500/50 focus:ring-0 transition-all"
                                    placeholder="+1 (555) 000-0000">
                            </div>

                            <div class="space-y-2">
                                <label for="password"
                                    class="text-[10px] font-black uppercase tracking-widest text-slate-500 ml-1">Access Key
                                    (Password)</label>
                                <input type="password" id="password" name="password" required
                                    class="w-full px-5 py-4 bg-white/5 border border-white/10 rounded-2xl text-white placeholder:text-slate-600 focus:border-indigo-500/50 focus:ring-0 transition-all"
                                    placeholder="••••••••••••">
                                @error('password') <p class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="space-y-2">
                                <label for="password_confirmation"
                                    class="text-[10px] font-black uppercase tracking-widest text-slate-500 ml-1">Verify
                                    Key</label>
                                <input type="password" id="password_confirmation" name="password_confirmation" required
                                    class="w-full px-5 py-4 bg-white/5 border border-white/10 rounded-2xl text-white placeholder:text-slate-600 focus:border-indigo-500/50 focus:ring-0 transition-all"
                                    placeholder="••••••••••••">
                            </div>
                        </div>

                        <div class="pt-8 border-t border-white/5 flex items-center justify-end gap-4">
                            <a href="{{ route('users.index') }}"
                                class="px-8 py-4 text-slate-500 hover:text-white font-black uppercase tracking-widest text-[10px] transition-colors">
                                Cancel Protocol
                            </a>
                            <button type="submit"
                                class="px-10 py-4 bg-indigo-600 hover:bg-indigo-500 text-white rounded-2xl font-black transition-all shadow-xl shadow-indigo-600/20 flex items-center gap-3">
                                <i class="fas fa-user-shield"></i>
                                <span>Authorize Agent</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Sidebar Info --}}
            <div class="space-y-6">
                <div class="card-premium p-6">
                    <h4 class="text-[10px] font-black uppercase tracking-widest text-slate-500 mb-6">Security Notice</h4>
                    <div class="space-y-4">
                        <div class="flex gap-4 p-4 bg-indigo-500/10 rounded-2xl border border-indigo-500/20">
                            <i class="fas fa-info-circle text-indigo-400 mt-1"></i>
                            <p class="text-[11px] text-indigo-300 font-medium leading-relaxed">
                                New agents will receive system-generated clearance based on their assigned role. Ensure
                                email addresses are unique across the network.
                            </p>
                        </div>
                        <div class="flex gap-4 p-4 bg-white/5 rounded-2xl border border-white/5">
                            <i class="fas fa-key text-slate-500 mt-1"></i>
                            <p class="text-[11px] text-slate-400 font-medium leading-relaxed">
                                Access keys must be at least 8 characters and include complex patterns for maximum security.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="card-premium p-6 overflow-hidden relative group">
                    <div class="relative z-10">
                        <h4 class="text-[10px] font-black uppercase tracking-widest text-slate-500 mb-4">Onboarding Status
                        </h4>
                        <div class="flex items-center gap-4">
                            <div
                                class="w-12 h-12 rounded-2xl bg-emerald-500/20 flex items-center justify-center text-emerald-500 border border-emerald-500/30">
                                <i class="fas fa-satellite-dish animate-pulse"></i>
                            </div>
                            <div>
                                <p class="text-xs font-black text-white uppercase tracking-wider">System Ready</p>
                                <p class="text-[10px] font-bold text-slate-500">Awaiting input data...</p>
                            </div>
                        </div>
                    </div>
                    <div class="absolute -right-10 -bottom-10 w-32 h-32 bg-emerald-500/5 blur-3xl rounded-full"></div>
                </div>
            </div>
        </div>
    </div>
@endsection