{{-- ==== MODALS (Unified styling) ==== --}}

{{-- Schedule Callback --}}
<div id="scheduleModal" class="modal-root fixed inset-0 z-50 hidden" role="dialog" aria-modal="true"
    aria-labelledby="scheduleTitle">
    <!-- Overlay -->
    <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-xs animate-fade-in"></div>

    <!-- Panel -->
    <div class="relative mx-auto mt-16 w-full max-w-xl animate-scale-in">
        <div
            class="rounded-2xl bg-white/95 dark:bg-gray-800/95 border border-gray-200/50 dark:border-gray-700/50 shadow-2xl">
            <!-- Header -->
            <div class="flex items-start justify-between px-6 pt-6">
                <div class="flex items-center space-x-3">
                    <div
                        class="h-10 w-10 rounded-xl bg-gradient-to-br from-primary-500 to-primary-600 text-white flex items-center justify-center shadow-medium">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 5h6l2 3h10M4 15h8m-8 4h12M4 7v14" />
                        </svg>
                    </div>
                    <div>
                        <h4 id="scheduleTitle" class="text-lg font-bold text-gray-900 dark:text-white">Schedule Call
                            Back</h4>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Pick a time and assign the follow-up.</p>
                    </div>
                </div>
                <button type="button" data-close-modal
                    class="p-2 rounded-lg text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100/70 dark:hover:bg-gray-700/70 transition">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Body -->
            <form action="{{ route('callbacks.store') }}" method="POST" class="px-6 pb-6 pt-4 space-y-5">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Lead</label>
                    <select name="lead_id"
                        class="w-full px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 bg-white/80 dark:bg-gray-700/80 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        @foreach ($leads ?? [] as $l)
                            <option value="{{ $l->id }}">{{ $l->first_name }} {{ $l->surname }}
                                ({{ $l->id }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Date &
                            Time</label>
                        <div class="relative">
                            <input type="datetime-local" name="scheduled_at"
                                class="w-full pl-3 pr-10 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 bg-white/80 dark:bg-gray-700/80 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            <svg class="w-5 h-5 absolute right-3 top-3.5 text-gray-400" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7V3m8 4V3M3 11h18M5 19h14a2 2 0 002-2v-8H3v8a2 2 0 002 2z" />
                            </svg>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Assign
                            To</label>
                        <select name="assigned_to"
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 bg-white/80 dark:bg-gray-700/80 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            @foreach ($users ?? [] as $u)
                                <option value="{{ $u->id }}">{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Notes</label>
                    <textarea name="notes" rows="3"
                        class="w-full px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 bg-white/80 dark:bg-gray-700/80 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                        placeholder="Reason for call back…"></textarea>
                </div>

                <!-- Footer -->
                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="button" data-close-modal
                        class="px-4 py-2 rounded-xl border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-100/80 dark:hover:bg-gray-700/70 transition">
                        Cancel
                    </button>
                    <button
                        class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-primary-500 to-primary-600 text-white shadow-soft hover:shadow-medium transform hover:-translate-y-0.5 transition">
                        Schedule
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Reschedule --}}
<div id="rescheduleModal" class="modal-root fixed inset-0 z-50 hidden" role="dialog" aria-modal="true"
    aria-labelledby="rescheduleTitle">
    <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-xs animate-fade-in"></div>

    <div class="relative mx-auto mt-16 w-full max-w-md animate-scale-in">
        <div
            class="rounded-2xl bg-white/95 dark:bg-gray-800/95 border border-gray-200/50 dark:border-gray-700/50 shadow-2xl">
            <div class="flex items-start justify-between px-6 pt-6">
                <div class="flex items-center space-x-3">
                    <div
                        class="h-10 w-10 rounded-xl bg-gradient-to-br from-warning-500 to-warning-600 text-white flex items-center justify-center shadow-medium">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3M12 22C6.477 22 2 17.523 2 12S6.477 2 12 2s10 4.477 10 10-4.477 10-10 10z" />
                        </svg>
                    </div>
                    <div>
                        <h4 id="rescheduleTitle" class="text-lg font-bold text-gray-900 dark:text-white">Reschedule</h4>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Choose a new follow-up time.</p>
                    </div>
                </div>
                <button type="button" data-close-modal
                    class="p-2 rounded-lg text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100/70 dark:hover:bg-gray-700/70 transition">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form method="POST" action="{{ route('callbacks.update', 0) }}" class="px-6 pb-6 pt-4 space-y-5">
                @csrf @method('PUT')
                <input type="hidden" name="callback_id" value="">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">New Date &
                        Time</label>
                    <div class="relative">
                        <input type="datetime-local" name="scheduled_at"
                            class="w-full pl-3 pr-10 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 bg-white/80 dark:bg-gray-700/80 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        <svg class="w-5 h-5 absolute right-3 top-3.5 text-gray-400" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3M3 11h18M5 19h14a2 2 0 002-2v-8H3v8a2 2 0 002 2z" />
                        </svg>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="button" data-close-modal
                        class="px-4 py-2 rounded-xl border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-100/80 dark:hover:bg-gray-700/70 transition">
                        Cancel
                    </button>
                    <button
                        class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-primary-500 to-primary-600 text-white shadow-soft hover:shadow-medium transform hover:-translate-y-0.5 transition">
                        Save
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Complete --}}
<div id="completeModal" class="modal-root fixed inset-0 z-50 hidden" role="dialog" aria-modal="true"
    aria-labelledby="completeTitle">
    <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-xs animate-fade-in"></div>

    <div class="relative mx-auto mt-16 w-full max-w-md animate-scale-in">
        <div
            class="rounded-2xl bg-white/95 dark:bg-gray-800/95 border border-gray-200/50 dark:border-gray-700/50 shadow-2xl">
            <div class="flex items-start justify-between px-6 pt-6">
                <div class="flex items-center space-x-3">
                    <div
                        class="h-10 w-10 rounded-xl bg-gradient-to-br from-success-500 to-success-600 text-white flex items-center justify-center shadow-medium">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <div>
                        <h4 id="completeTitle" class="text-lg font-bold text-gray-900 dark:text-white">Complete Call
                            Back</h4>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Record the outcome and close the task.</p>
                    </div>
                </div>
                <button type="button" data-close-modal
                    class="p-2 rounded-lg text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100/70 dark:hover:bg-gray-700/70 transition">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- <form method="POST" action="{{ route('callbacks.complete', 0) }}" class="px-6 pb-6 pt-4 space-y-5"> --}}
            <form method="POST" action="#" class="px-6 pb-6 pt-4 space-y-5">
                @csrf
                <input type="hidden" name="callback_id" value="">

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Outcome</label>
                    <select name="outcome"
                        class="w-full px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 bg-white/80 dark:bg-gray-700/80 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        <option>Reached</option>
                        <option>No Answer</option>
                        <option>Disconnected Number</option>
                        <option>Not Interested</option>
                        <option>Deal</option>
                        <option>Rescheduled</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Notes</label>
                    <textarea name="notes" rows="3"
                        class="w-full px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 bg-white/80 dark:bg-gray-700/80 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                        placeholder="What happened on the call?"></textarea>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="button" data-close-modal
                        class="px-4 py-2 rounded-xl border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-100/80 dark:hover:bg-gray-700/70 transition">
                        Cancel
                    </button>
                    <button
                        class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-success-600 to-success-700 text-white shadow-soft hover:shadow-medium transform hover:-translate-y-0.5 transition">
                        Complete
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
