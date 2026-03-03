@extends('layouts.app')

@section('title', 'Users')
@section('page-title', 'Users')

@section('content')
    <div class="space-y-6 animate-on-load">
        {{-- Personnel Header --}}
        <div
            class="card-premium p-8 flex flex-col md:flex-row md:items-center justify-between gap-6 overflow-hidden relative">
            <div class="relative z-10">
                <h1 class="text-4xl font-black tracking-tight text-white mb-2">Personnel <span
                        class="gradient-text">Matrix</span></h1>
                <p class="text-slate-400 font-medium">Managing authorized credentials and security protocols.</p>
            </div>

            <div class="flex items-center gap-3 relative z-10">
                <a href="{{ route('users.create') }}"
                    class="px-8 py-4 bg-indigo-600 hover:bg-indigo-500 text-white rounded-2xl font-black transition-all shadow-xl shadow-indigo-600/20 flex items-center gap-3 group">
                    <i class="fas fa-user-plus group-hover:rotate-12 transition-transform"></i>
                    <span>Onboard Agent</span>
                </a>
            </div>

            <div class="absolute -right-20 -top-20 w-64 h-64 bg-indigo-500/10 blur-[100px] rounded-full"></div>
        </div>

        {{-- Workspace Grid --}}
        <div class="grid grid-cols-1 xl:grid-cols-4 gap-6">
            {{-- Control Panel --}}
            <div class="xl:col-span-1 space-y-6">
                <div class="card-premium p-6">
                    <form id="usersFilterForm" method="GET" action="{{ route('users.index') }}" class="space-y-6">
                        <div>
                            <label
                                class="text-[10px] font-black uppercase tracking-widest text-slate-500 mb-3 block">Identity
                                Search</label>
                            <div class="relative">
                                <i class="fas fa-fingerprint absolute left-4 top-1/2 -translate-y-1/2 text-slate-500"></i>
                                <input id="userSearchInput" type="text" name="q" value="{{ request('q') }}"
                                    class="w-full pl-12 pr-4 py-4 bg-white/5 border border-white/10 rounded-2xl text-white placeholder:text-slate-600 focus:border-indigo-500/50 focus:ring-0 transition-all"
                                    placeholder="Name or Email...">
                            </div>
                        </div>

                        <button type="submit"
                            class="w-full py-4 bg-white/10 hover:bg-white/20 text-white rounded-2xl font-black transition-all border border-white/10">
                            Execute Search
                        </button>

                        @if(request()->has('q'))
                            <a id="clearUserFilters" href="{{ route('users.index') }}"
                                class="block text-center text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 hover:text-indigo-400 transition-colors">
                                Clear Parameters
                            </a>
                        @endif
                    </form>
                </div>

                {{-- Access Breakdown --}}
                <div class="card-premium p-6">
                    <h3 class="text-[10px] font-black uppercase tracking-widest text-slate-500 mb-6">Security Breakdown</h3>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between p-4 bg-white/5 rounded-2xl border border-white/5">
                            <span class="text-xs font-bold text-slate-400">Total Nodes</span>
                            <span class="text-sm font-black text-white">{{ $users->total() }}</span>
                        </div>
                        <div
                            class="flex items-center justify-between p-4 bg-indigo-500/10 rounded-2xl border border-indigo-500/20">
                            <span class="text-xs font-bold text-indigo-400/80">Active Sessions</span>
                            <span class="text-sm font-black text-indigo-400">{{ $users->count() }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Agent Stream --}}
            <div class="xl:col-span-3 card-premium overflow-hidden flex flex-col relative" id="usersTableCard">
                <div id="ajaxOverlayUsers"
                    class="hidden absolute inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center">
                    <div class="w-12 h-12 border-4 border-indigo-500/20 border-t-indigo-500 rounded-full animate-spin">
                    </div>
                </div>

                <div class="p-6 border-b border-white/5 bg-white/[0.02] flex items-center justify-between">
                    <h3 class="text-[10px] font-black uppercase tracking-widest text-slate-500">Live Identity Stream</h3>
                    <div class="flex gap-2">
                        <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div>
                        <span class="text-[10px] font-black uppercase tracking-widest text-emerald-500">Encrypted</span>
                    </div>
                </div>

                <div id="usersTableContainer" class="flex-1 flex flex-col">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="bg-black/20 text-[10px] font-black uppercase tracking-[0.2em] text-slate-500">
                                    <th class="px-8 py-5">Ident Agent</th>
                                    <th class="px-8 py-5">Protocol Role</th>
                                    <th class="px-8 py-5">Auth Status</th>
                                    <th class="px-8 py-5">Joined System</th>
                                    <th class="px-8 py-5 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/5">
                                @forelse($users as $user)
                                                            <tr class="hover:bg-white/[0.03] transition-all group">
                                                                <td class="px-8 py-6">
                                                                    <div class="flex items-center gap-4">
                                                                        <div
                                                                            class="w-12 h-12 rounded-2xl overflow-hidden border-2 border-white/5 bg-white/5 shadow-xl group-hover:border-indigo-500/30 transition-all">
                                                                            <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=6366f1&color=fff&bold=true"
                                                                                class="w-full h-full object-cover">
                                                                        </div>
                                                                        <div>
                                                                            <p
                                                                                class="text-sm font-black text-white group-hover:text-indigo-400 transition-colors">
                                                                                {{ $user->name }}</p>
                                                                            <p class="text-[10px] font-bold text-slate-500 uppercase tracking-tighter">
                                                                                {{ $user->email }}</p>
                                                                        </div>
                                                                    </div>
                                                                </td>
                                                                <td class="px-8 py-6">
                                                                    @php
                                                                        $roleColors = [
                                                                            'admin' => 'rose',
                                                                            'super_agent' => 'indigo',
                                                                            'closer' => 'emerald',
                                                                            'agent' => 'slate'
                                                                        ];
                                                                        $rColor = $roleColors[$user->role] ?? 'slate';
                                                                    @endphp
                                     <span
                                                                        class="inline-flex items-center px-3 py-1 rounded-xl text-[10px] font-black uppercase tracking-wider bg-{{ $rColor }}-500/10 text-{{ $rColor }}-400 border border-{{ $rColor }}-500/20">
                                                                        {{ str_replace('_', ' ', $user->role) }}
                                                                    </span>
                                                                </td>
                                                                <td class="px-8 py-6">
                                                                    <div class="flex items-center gap-2">
                                                                        <div class="w-1.5 h-1.5 rounded-full bg-emerald-500"></div>
                                                                        <span
                                                                            class="text-[10px] font-black uppercase text-emerald-400 tracking-widest">Active</span>
                                                                    </div>
                                                                </td>
                                                                <td class="px-8 py-6">
                                                                    <p class="text-[11px] font-black text-slate-400">
                                                                        {{ $user->created_at->format('d M Y') }}</p>
                                                                    <p class="text-[10px] text-slate-600 font-bold uppercase tracking-tighter">
                                                                        {{ $user->created_at->diffForHumans() }}</p>
                                                                </td>
                                                                <td class="px-8 py-6">
                                                                    <div class="flex items-center justify-end gap-2">
                                                                        <a href="{{ route('users.edit', $user) }}"
                                                                            class="w-10 h-10 flex items-center justify-center bg-white/5 hover:bg-indigo-600 rounded-xl text-slate-400 hover:text-white transition-all border border-white/5 hover:border-indigo-500 shadow-lg">
                                                                            <i class="fas fa-user-edit"></i>
                                                                        </a>
                                                                        @if($user->id !== auth()->id())
                                                                            <button onclick="deleteUser({{ $user->id }})"
                                                                                class="w-10 h-10 flex items-center justify-center bg-white/5 hover:bg-rose-600 rounded-xl text-slate-400 hover:text-white transition-all border border-white/5 hover:border-rose-500 shadow-lg">
                                                                                <i class="fas fa-user-minus"></i>
                                                                            </button>
                                                                        @endif
                                                                    </div>
                                                                </td>
                                                            </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-8 py-32 text-center">
                                            <p class="text-slate-500 font-black uppercase tracking-[0.3em] text-xs">No Agents
                                                Found in Sector</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="p-8 border-t border-white/5 bg-black/10 mt-auto" id="usersPaginationWrap">
                        {{ $users->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Delete Confirmation Modal --}}
    <div id="deleteModal" class="fixed inset-0 bg-black/80 backdrop-blur-xl z-[100] hidden items-center justify-center p-6">
        <div class="w-full max-w-md card-premium p-10 space-y-8 animate-on-load">
            <div class="text-center space-y-4">
                <div
                    class="w-20 h-20 bg-rose-500/20 rounded-3xl flex items-center justify-center text-rose-500 mx-auto border border-rose-500/30">
                    <i class="fas fa-user-times text-3xl"></i>
                </div>
                <div>
                    <h3 class="text-2xl font-black text-white">Revoke Access?</h3>
                    <p class="text-slate-500 font-medium">This protocol will permanently terminate the agent's credentials.
                    </p>
                </div>
            </div>

            <div class="flex gap-4">
                <button id="cancelDelete"
                    class="flex-1 py-4 bg-white/5 hover:bg-white/10 text-white rounded-2xl font-black uppercase tracking-widest text-xs transition-all">
                    Abort
                </button>
                <form id="deleteForm" method="POST" class="flex-1">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        class="w-full py-4 bg-rose-600 hover:bg-rose-500 text-white rounded-2xl font-black uppercase tracking-widest text-xs transition-all shadow-xl shadow-rose-600/20">
                        Confirm Termination
                    </button>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    {{-- jQuery (match your Leads page setup) --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        // Debounce helper (same pattern as Leads)
        function debounce(fn, delay) {
            let t;
            return (...args) => {
                clearTimeout(t);
                t = setTimeout(() => fn(...args), delay);
            };
        }

        function initAjaxUserFiltering() {
            const $form = $('#usersFilterForm');
            const $q = $('#userSearchInput');
            const $container = $('#usersTableContainer');
            const $overlay = $('#ajaxOverlayUsers');

            function showOverlay() {
                $overlay.removeClass('hidden');
            }

            function hideOverlay() {
                $overlay.addClass('hidden');
            }

            function serializeFormToQuery($f, overrides = {}) {
                const params = new URLSearchParams($f.serialize());
                Object.entries(overrides).forEach(([k, v]) => {
                    if (v === '' || v == null) params.delete(k);
                    else params.set(k, v);
                });
                return params.toString();
            }

            function ajaxLoad(url) {
                showOverlay();
                $.get(url)
                    .done(function (html) {
                        const $html = $(html);
                        $container.html($html.find('#usersTableContainer').html());
                        window.history.pushState({}, '', url);
                    })
                    .fail(function () {
                        window.location.href = url; // fallback
                    })
                    .always(hideOverlay);
            }

            // Submit click (GET)
            $form.on('submit', function (e) {
                e.preventDefault();
                const s = serializeFormToQuery($form);
                ajaxLoad($form.attr('action') + (s ? '?' + s : ''));
            });

            // Realtime typing
            $q.on('keyup', debounce(function () {
                const s = serializeFormToQuery($form, {
                    q: $q.val(),
                    page: 1
                });
                ajaxLoad($form.attr('action') + (s ? '?' + s : ''));
            }, 350));

            // Clear button (delegated, appears conditionally)
            $(document).on('click', '#clearUserFilters', function (e) {
                e.preventDefault();
                ajaxLoad($(this).attr('href'));
            });

            // Pagination links (delegated)
            $(document).on('click', '#usersPaginationWrap a', function (e) {
                e.preventDefault();
                ajaxLoad($(this).attr('href'));
            });

            // Back/forward support
            window.addEventListener('popstate', function () {
                ajaxLoad(location.href);
            });
        }

        // Initialize
        $(document).ready(function () {
            initAjaxUserFiltering();

            // Delete modal (kept from your original)
            window.deleteUser = function (userId) {
                document.getElementById('deleteForm').action = `/users/${userId}`;
                document.getElementById('deleteModal').classList.remove('hidden');
            };
            document.getElementById('cancelDelete').addEventListener('click', function () {
                document.getElementById('deleteModal').classList.add('hidden');
            });
        });
    </script>
@endpush