{{-- resources/views/users/edit.blade.php --}}
@extends('layouts.app')

@section('title', 'Modify Agent')
@section('page-title', 'Modify Agent')

@section('content')
    <div class="space-y-6 animate-on-load">
        {{-- Header --}}
        <div
            class="card-premium p-8 flex flex-col md:flex-row md:items-center justify-between gap-6 overflow-hidden relative">
            <div class="relative z-10">
                <h1 class="text-4xl font-black tracking-tight text-white mb-2">Modify <span
                        class="gradient-text">Credentials</span></h1>
                <p class="text-slate-400 font-medium">Updating security profile for agent: <span
                        class="text-white font-black">{{ $user->name }}</span></p>
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
                        <h3 class="text-[10px] font-black uppercase tracking-widest text-slate-500">Identity Modification
                            Protocol</h3>
                    </div>

                    <form action="{{ route('users.update', $user) }}" method="POST" class="p-8 space-y-8">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label for="name"
                                    class="text-[10px] font-black uppercase tracking-widest text-slate-500 ml-1">Legal
                                    Designation (Name)</label>
                                <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required
                                    class="w-full px-5 py-4 bg-white/5 border border-white/10 rounded-2xl text-white placeholder:text-slate-600 focus:border-indigo-500/50 focus:ring-0 transition-all"
                                    placeholder="e.g. Alexander Vance">
                                @error('name') <p class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="space-y-2">
                                <label for="email"
                                    class="text-[10px] font-black uppercase tracking-widest text-slate-500 ml-1">Network
                                    Identifier (Email)</label>
                                <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}"
                                    required
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
                                        <option value="user" {{ old('role', $user->role) === 'user' ? 'selected' : '' }}>
                                            Standard Agent</option>
                                        <option value="closer" {{ old('role', $user->role) === 'closer' ? 'selected' : '' }}>
                                            Closer Authority</option>
                                        <option value="super_agent" {{ old('role', $user->role) === 'super_agent' ? 'selected' : '' }}>Supervisor</option>
                                        <option value="report_manager" {{ old('role', $user->role) === 'report_manager' ? 'selected' : '' }}>Intel Analyst</option>
                                        <option value="lead_manager" {{ old('role', $user->role) === 'lead_manager' ? 'selected' : '' }}>Logistics Manager</option>
                                        <option value="max_out" {{ old('role', $user->role) === 'max_out' ? 'selected' : '' }}>VM Protocol</option>
                                        <option value="death_submitted" {{ old('role', $user->role) === 'death_submitted' ? 'selected' : '' }}>Archive Specialist</option>
                                        <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>
                                            System Administrator</option>
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
                                <input type="tel" id="phone" name="phone" value="{{ old('phone', $user->phone) }}"
                                    class="w-full px-5 py-4 bg-white/5 border border-white/10 rounded-2xl text-white placeholder:text-slate-600 focus:border-indigo-500/50 focus:ring-0 transition-all"
                                    placeholder="+1 (555) 000-0000">
                            </div>

                            <div class="space-y-2">
                                <label for="password"
                                    class="text-[10px] font-black uppercase tracking-widest text-slate-500 ml-1">New Access
                                    Key (Optional)</label>
                                <input type="password" id="password" name="password"
                                    class="w-full px-5 py-4 bg-white/5 border border-white/10 rounded-2xl text-white placeholder:text-slate-700 focus:border-indigo-500/50 focus:ring-0 transition-all"
                                    placeholder="Leave blank to preserve current key">
                                @error('password') <p class="text-[10px] text-rose-500 font-bold ml-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="space-y-2">
                                <div class="flex items-center gap-3 p-4 bg-white/5 rounded-2xl border border-white/5 mt-6 cursor-pointer group"
                                    onclick="document.getElementById('is_admin').click()">
                                    <div class="relative">
                                        <input type="checkbox" id="is_admin" name="is_admin" {{ old('is_admin', $user->is_admin) ? 'checked' : '' }} class="peer sr-only">
                                        <div
                                            class="w-6 h-6 border-2 border-white/10 rounded-lg group-hover:border-indigo-500/50 peer-checked:bg-indigo-600 peer-checked:border-indigo-500 transition-all flex items-center justify-center">
                                            <i
                                                class="fas fa-check text-[10px] text-white opacity-0 peer-checked:opacity-100 transition-opacity"></i>
                                        </div>
                                    </div>
                                    <span
                                        class="text-[10px] font-black uppercase tracking-widest text-slate-500 group-hover:text-slate-300 transition-colors">Grant
                                        Global Override (Admin)</span>
                                </div>
                            </div>
                        </div>

                        <div class="pt-8 border-t border-white/5 flex items-center justify-end gap-4">
                            <a href="{{ route('users.index') }}"
                                class="px-8 py-4 text-slate-500 hover:text-white font-black uppercase tracking-widest text-[10px] transition-colors">
                                Discard Changes
                            </a>
                            <button type="submit"
                                class="px-10 py-4 bg-indigo-600 hover:bg-indigo-500 text-white rounded-2xl font-black transition-all shadow-xl shadow-indigo-600/20 flex items-center gap-3">
                                <i class="fas fa-save"></i>
                                <span>Update Protocol</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Sidebar Info --}}
            <div class="space-y-6">
                <div class="card-premium p-6">
                    <h4 class="text-[10px] font-black uppercase tracking-widest text-slate-500 mb-6">Security Breakdown</h4>
                    <div class="space-y-4">
                        <div class="p-4 bg-white/5 rounded-2xl border border-white/5 text-center">
                            <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Last Updated</p>
                            <p class="text-xs font-bold text-white">{{ $user->updated_at->diffForHumans() }}</p>
                        </div>
                        <div class="p-4 bg-white/5 rounded-2xl border border-white/5 text-center">
                            <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Account Age</p>
                            <p class="text-xs font-bold text-white">{{ $user->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                </div>

                <div class="card-premium p-6 overflow-hidden relative group">
                    <div class="relative z-10 text-center space-y-4">
                        <div class="w-20 h-20 rounded-3xl overflow-hidden mx-auto border-2 border-indigo-500/30 shadow-2xl">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=6366f1&color=fff&bold=true&size=128"
                                class="w-full h-full object-cover">
                        </div>
                        <div>
                            <p class="text-xs font-black text-white uppercase tracking-wider">{{ $user->name }}</p>
                            <p class="text-[10px] font-black text-indigo-400 uppercase tracking-[0.2em]">
                                {{ str_replace('_', ' ', $user->role) }}</p>
                        </div>
                    </div>
                    <div class="absolute -right-10 -bottom-10 w-32 h-32 bg-indigo-500/5 blur-3xl rounded-full"></div>
                </div>
            </div>
        </div>
    </div>
@endsection