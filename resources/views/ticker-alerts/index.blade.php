@extends('layouts.app')

@section('content')
<div class="p-6">
    <div class="flex items-center justify-between mb-8">
        <div>
            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Current View</p>
            <h1 class="text-3xl font-black text-slate-900 tracking-tight">Alert Ticker Control</h1>
        </div>
        <div class="flex items-center gap-4">
            <div class="flex items-center gap-3 bg-white p-2 pr-4 rounded-2xl shadow-sm border border-slate-100">
                <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center text-primary font-bold">
                    {{ substr(Auth::user()->name, 0, 1) }}
                </div>
                <div>
                    <p class="text-sm font-bold text-slate-900 leading-none">{{ Auth::user()->name }}</p>
                    <p class="text-[10px] font-bold text-slate-400 uppercase leading-none mt-1">{{ Auth::user()->role }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-8">
        <!-- Control Card -->
        <div class="bg-white rounded-[2rem] p-8 shadow-sm border border-slate-100 relative overflow-hidden">
            <div class="absolute top-0 right-0 p-8 opacity-10">
                <i class="fas fa-bullhorn text-8xl text-primary"></i>
            </div>
            
            <div class="flex items-center gap-4 mb-8">
                <div class="w-12 h-12 rounded-2xl bg-primary/10 flex items-center justify-center">
                    <i class="fas fa-exclamation-circle text-primary text-xl"></i>
                </div>
                <div>
                    <h2 class="text-xl font-black text-slate-900 uppercase tracking-tight">Push Live Alert</h2>
                    <p class="text-sm text-slate-500 font-medium">Update the scrolling ticker message for all users.</p>
                </div>
            </div>

            <form action="{{ route('ticker-alerts.store') }}" method="POST" class="space-y-6">
                @csrf
                <div class="space-y-2">
                    <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">Ticker Message</label>
                    <textarea name="message" rows="3" 
                        class="w-full bg-slate-50 border-none rounded-2xl p-6 text-slate-700 font-medium focus:ring-2 focus:ring-primary/20 transition-all placeholder:text-slate-300"
                        placeholder="Type the alert message here..."></textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-end">
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">Theme Color</label>
                        <select name="theme_color" class="w-full bg-slate-50 border-none rounded-2xl p-4 text-slate-700 font-medium focus:ring-2 focus:ring-primary/20 transition-all appearance-none cursor-pointer">
                            <option value="red">Urgent (Red)</option>
                            <option value="green">Productive (Green)</option>
                            <option value="blue">Info (Blue)</option>
                            <option value="yellow">Warning (Yellow)</option>
                            <option value="purple">Special (Purple)</option>
                        </select>
                    </div>

                    <div class="flex items-center gap-3 p-4">
                        <input type="checkbox" name="disable_others" value="1" id="disable_others" checked
                            class="w-5 h-5 rounded-lg border-none bg-slate-100 text-primary focus:ring-offset-0 focus:ring-2 focus:ring-primary/20 cursor-pointer">
                        <label for="disable_others" class="text-sm font-bold text-slate-600 cursor-pointer select-none">Disable all previous alerts</label>
                    </div>
                </div>

                <div class="pt-4">
                    <button type="submit" class="bg-primary hover:bg-primary-600 text-white px-8 py-4 rounded-2xl font-black uppercase tracking-widest text-xs shadow-lg shadow-primary/30 transform transition-all active:scale-95 flex items-center gap-3 group">
                        <span>Update Live Ticker</span>
                        <i class="fas fa-paper-plane text-xs transition-transform group-hover:translate-x-1 group-hover:-translate-y-1"></i>
                    </button>
                </div>
            </form>
        </div>

        <!-- History List -->
        <div class="space-y-4">
            <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest ml-1">Ticker History</h3>
            
            @forelse($alerts as $alert)
            <div class="bg-white rounded-[1.5rem] p-6 shadow-sm border border-slate-100 flex items-center justify-between group h-24">
                <div class="flex items-center gap-6">
                    <div class="w-3 h-3 rounded-full {{ $alert->is_active ? 'bg-'.$alert->theme_color.'-500 animate-pulse shadow-lg shadow-'.$alert->theme_color.'-500/50' : 'bg-slate-200' }}"></div>
                    <div>
                        <p class="text-slate-800 font-bold leading-tight line-clamp-1 group-hover:line-clamp-none transition-all duration-300">
                            {{ $alert->message }}
                        </p>
                        <p class="text-[10px] font-bold text-slate-400 uppercase mt-1 tracking-wider">
                            {{ $alert->created_at->format('M d, Y - H:i') }}
                        </p>
                    </div>
                </div>
                
                <div class="flex items-center gap-3 opacity-0 group-hover:opacity-100 transition-opacity">
                    <form action="{{ route('ticker-alerts.toggle', $alert) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all {{ $alert->is_active ? 'bg-rose-50 text-rose-500 hover:bg-rose-100' : 'bg-emerald-50 text-emerald-500 hover:bg-emerald-100' }}">
                            {{ $alert->is_active ? 'Disable' : 'Enable' }}
                        </button>
                    </form>
                    
                    <form action="{{ route('ticker-alerts.destroy', $alert) }}" method="POST" onsubmit="return confirm('Silni k umeed hai? Is alert ko hamesha k liye delete kar diya jayega.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-10 h-10 rounded-xl bg-slate-50 text-slate-400 hover:bg-rose-50 hover:text-rose-500 flex items-center justify-center transition-all">
                            <i class="fas fa-trash-alt text-sm"></i>
                        </button>
                    </form>
                </div>
            </div>
            @empty
            <div class="bg-slate-50 border-2 border-dashed border-slate-100 rounded-[2rem] p-12 text-center">
                <i class="fas fa-bullhorn text-4xl text-slate-200 mb-4 block"></i>
                <p class="text-sm font-bold text-slate-400 uppercase tracking-widest">No alerts in history</p>
            </div>
            @endforelse

            <div class="mt-8">
                {{ $alerts->links() }}
            </div>
        </div>
    </div>
</div>

<style>
    /* Custom utility classes for colors if not in Tailwind config */
    .bg-red-500 { background-color: #ef4444; }
    .bg-green-500 { background-color: #10b981; }
    .bg-blue-500 { background-color: #3b82f6; }
    .bg-yellow-500 { background-color: #f59e0b; }
    .bg-purple-500 { background-color: #8b5cf6; }
    .bg-slate-500 { background-color: #64748b; }

    .shadow-red-500\/50 { --tw-shadow-color: rgba(239, 68, 68, 0.5); shadow: 0 10px 15px -3px var(--tw-shadow-color); }
    /* ... and so on ... */
</style>
@endsection
