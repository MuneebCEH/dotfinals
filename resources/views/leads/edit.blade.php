@extends('layouts.app')

@section('title', 'Edit Lead')
@section('page-title', 'Edit Lead')

@push('styles')
    <style>
        [x-cloak] {
            display: none !important
        }

        .btn-loading {
            position: relative;
        }

        .btn-loading .btn-text {
            opacity: 0;
        }

        .btn-loading::after {
            content: '';
            position: absolute;
            width: 20px;
            height: 20px;
            margin: auto;
            border: 2px solid transparent;
            border-top-color: currentColor;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }

        @keyframes spin {
            0% {
                transform: translate(-50%, -50%) rotate(0deg);
            }

            100% {
                transform: translate(-50%, -50%) rotate(360deg);
            }
        }
    </style>
@endpush

@section('content')
    {{-- Alpine wrapper controls modal state for the whole page --}}
    <div class="space-y-8 animate-on-load"
         x-data="{
            issueOpen: false,
            isSubmitting: false,
            handleSubmit(event) {
                if (this.isSubmitting) {
                    event.preventDefault();
                    return false;
                }
                this.isSubmitting = true;

                // Add reload after 2 seconds
                setTimeout(() => {
                    window.location.reload();
                }, 2000);

                return true;
            }
         }"
         x-init="
            // Open modal if there are validation errors
            $nextTick(() => {
                if (@json($errors->has('title') || $errors->has('description') || $errors->has('priority') || $errors->has('attachments.*'))) {
                    issueOpen = true
                }
            });

            // Lock body scroll and focus title when modal opens
            $watch('issueOpen', (open) => {
                document.body.style.overflow = open ? 'hidden' : '';
                if (open) { $nextTick(() => { $refs.issueTitle?.focus(); }); }
            });
         "
         @keydown.escape.window="issueOpen=false">

        <!-- Header -->
        <div
            class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl shadow-2xl rounded-2xl p-8 border border-gray-200/50 dark:border-gray-700/50">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">Edit Lead</h2>
                    <p class="text-lg text-gray-600 dark:text-gray-400">Update the client's details as accurately as
                        possible.</p>
                </div>
                <a href="{{ route('leads.index') }}"
                   class="inline-flex items-center px-6 py-3 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 bg-white/80 dark:bg-gray-700/80 backdrop-blur-xl font-semibold rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition-all duration-200">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to Leads
                </a>
            </div>
        </div>

        {{-- Uploaded PDF (preview, if present) --}}
        @if ($lead->lead_pdf_path)
            <div
                class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl shadow-2xl rounded-2xl border border-gray-200/50 dark:border-gray-700/50 overflow-hidden">
                <div
                    class="px-8 py-6 flex items-center justify-between border-b border-gray-200/50 dark:border-gray-700/50">
                    <div class="flex items-center gap-3">
                        <div
                            class="w-10 h-10 rounded-xl bg-gradient-to-br from-primary-500 to-primary-600 flex items-center justify-center shadow-lg">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v8m4-4H8" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white">Uploaded Document</h3>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="{{ Storage::url($lead->lead_pdf_path) }}" target="_blank"
                           class="px-3 py-2 rounded-lg bg-primary-600 text-white hover:bg-primary-700 transition">
                            View / Download
                        </a>
                        @if (Route::has('leads.pdf'))
                            <a href="{{ route('leads.pdf', $lead) }}"
                               class="px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                Force Download
                            </a>
                        @endif
                    </div>
                </div>

                <div class="p-4">
                    <div class="rounded-xl overflow-hidden border border-gray-200/70 dark:border-gray-700/70 relative z-0">
                        <iframe src="{{ Storage::url($lead->lead_pdf_path) }}" class="w-full h-[70vh] min-h-[420px]"
                                frameborder="0" loading="lazy"></iframe>
                    </div>
                </div>
            </div>
        @endif

        <!-- Form Card -->
        <div
            class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl shadow-2xl rounded-2xl border border-gray-200/50 dark:border-gray-700/50 overflow-hidden">

            <!-- Card Header -->
            <div class="px-8 py-6 border-b border-gray-200/50 dark:border-gray-700/50">
                <div class="flex items-center gap-3">
                    <div
                        class="w-10 h-10 rounded-xl bg-gradient-to-br from-primary-500 to-primary-600 flex items-center justify-center shadow-lg">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M11 17l-5-5m0 0l5-5m-5 5h12" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white">Lead Information</h3>
                </div>
            </div>

            <form method="POST" action="{{ route('leads.update', $lead) }}" class="p-8 space-y-10"
                  enctype="multipart/form-data" @submit="handleSubmit($event)">
                @csrf
                @method('PUT')

                {{-- Row: First / Middle Initial / Surname --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div>
                        <label class="block text-sm font-semibold mb-3">First Name *</label>
                        <input name="first_name" value="{{ old('first_name', $lead->first_name) }}" required
                               class="block w-full px-4 py-3 border rounded-xl bg-white/80 dark:bg-gray-700/80 border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        @error('first_name')
                        <p class="text-danger-600 text-sm mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-3">Middle Initial</label>
                        <input name="middle_initial" value="{{ old('middle_initial', $lead->middle_initial) }}"
                               maxlength="2"
                               class="block w-full px-4 py-3 border rounded-xl bg-white/80 dark:bg-gray-700/80 border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        @error('middle_initial')
                        <p class="text-danger-600 text-sm mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-3">Surname *</label>
                        <input name="surname" value="{{ old('surname', $lead->surname) }}" required
                               class="block w-full px-4 py-3 border rounded-xl bg-white/80 dark:bg-gray-700/80 border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        @error('surname')
                        <p class="text-danger-600 text-sm mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Row: Street / City / State --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div>
                        <label class="block text-sm font-semibold mb-3">Street</label>
                        <input name="street" value="{{ old('street', $lead->street) }}"
                               class="block w-full px-4 py-3 border rounded-xl bg-white/80 dark:bg-gray-700/80 border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        @error('street')
                        <p class="text-danger-600 text-sm mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-3">City</label>
                        <input name="city" value="{{ old('city', $lead->city) }}"
                               class="block w-full px-4 py-3 border rounded-xl bg-white/80 dark:bg-gray-700/80 border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        @error('city')
                        <p class="text-danger-600 text-sm mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-3">State Abbreviation</label>
                        <input name="state_abbreviation" value="{{ old('state_abbreviation', $lead->state_abbreviation) }}"
                               maxlength="5"
                               class="uppercase block w-full px-4 py-3 border rounded-xl bg-white/80 dark:bg-gray-700/80 border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        @error('state_abbreviation')
                        <p class="text-danger-600 text-sm mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Row: Gen Code / Zip Code / Age --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div>
                        <label class="block text-sm font-semibold mb-3">Gen Code</label>
                        <input name="gen_code" value="{{ old('gen_code', $lead->gen_code) }}"
                               class="block w-full px-4 py-3 border rounded-xl bg-white/80 dark:bg-gray-700/80 border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        @error('gen_code')
                        <p class="text-danger-600 text-sm mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-3">Zip Code</label>
                        <input name="zip_code" value="{{ old('zip_code', $lead->zip_code) }}"
                               class="block w-full px-4 py-3 border rounded-xl bg-white/80 dark:bg-gray-700/80 border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        @error('zip_code')
                        <p class="text-danger-600 text-sm mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-3">Age</label>
                        <input type="number" min="0" name="age" value="{{ old('age', $lead->age) }}"
                               class="block w-full px-4 py-3 border rounded-xl bg-white/80 dark:bg-gray-700/80 border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        @error('age')
                        <p class="text-danger-600 text-sm mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Row: SSN / Numbers (dynamic) / XFC06 --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div>
                        <label class="block text-sm font-semibold mb-3">SSN</label>
                        <input name="ssn" value="{{ old('ssn', $lead->ssn) }}"
                               class="block w-full px-4 py-3 border rounded-xl bg-white/80 dark:bg-gray-700/80 border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        @error('ssn')
                        <p class="text-danger-600 text-sm mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Phone Numbers (repeater) --}}
                    <div>
                        <label class="block text-sm font-semibold mb-3">Phone Numbers</label>
                        @php
                            $numberValues = old(
                                'numbers',
                                is_array($lead->numbers)
                                    ? $lead->numbers
                                    : (empty($lead->numbers)
                                        ? []
                                        : (is_string($lead->numbers)
                                            ? json_decode($lead->numbers, true)
                                            : (array) $lead->numbers)),
                            );
                            if (empty($numberValues)) {
                                $numberValues = [''];
                            }
                        @endphp
                        <div id="numbersRepeater" class="space-y-3">
                            @foreach ($numberValues as $n)
                                <div class="flex gap-2">
                                    <input name="numbers[]" value="{{ $n }}"
                                           class="flex-1 px-4 py-3 border rounded-xl bg-white/80 dark:bg-gray-700/80 border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                                           placeholder="(###) ###-####">
                                    <button type="button"
                                            class="shrink-0 px-3 rounded-xl border border-gray-300 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700"
                                            onclick="removeNumberRow(this)" title="Remove">&times;</button>
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-3">
                            <button type="button" id="addNumberBtn"
                                    class="inline-flex items-center px-4 py-2 rounded-xl bg-primary-600 text-white shadow hover:shadow-md">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M12 6v12m6-6H6" />
                                </svg>
                                Add Number
                            </button>
                        </div>
                        @error('numbers')
                        <p class="text-danger-600 text-sm mt-2">{{ $message }}</p>
                        @enderror
                        @error('numbers.*')
                        <p class="text-danger-600 text-sm mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                

                {{-- Row: FICO / CARDS(JSON) / BALANCE --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    {{-- FICO --}}
                    <div>
                        <label class="block text-sm font-semibold mb-3">FICO</label>
                        <input type="number" name="fico" value="{{ old('fico', $lead->fico) }}"
                               class="block w-full px-4 py-3 border rounded-xl bg-white/80 dark:bg-gray-700/80 border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        @error('fico')
                        <p class="text-danger-600 text-sm mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- CARDS as JSON (dynamic repeater) --}}
                    <div>
                        <label class="block text-sm font-semibold mb-3">Cards</label>
                        @php
                            // Support both casted array and raw JSON in DB; fall back to []
                            $cardsValues = old(
                                'cards_json',
                                is_array($lead->cards_json)
                                    ? $lead->cards_json
                                    : (is_string($lead->cards_json)
                                        ? (json_decode($lead->cards_json, true) ?:
                                        [])
                                        : (isset($lead->cards_json)
                                            ? (array) $lead->cards_json
                                            : [])),
                            );
                            if (empty($cardsValues)) {
                                $cardsValues = [''];
                            }
                        @endphp
                        <div id="cardsRepeater" class="space-y-3">
                            @foreach ($cardsValues as $c)
                                <div class="flex gap-2">
                                    <input name="cards_json[]" value="{{ $c }}"
                                           class="flex-1 px-4 py-3 border rounded-xl bg-white/80 dark:bg-gray-700/80 border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                                           placeholder="e.g., Visa / Amex / Mastercard">
                                    <button type="button"
                                            class="shrink-0 px-3 rounded-xl border border-gray-300 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700"
                                            onclick="removeCardRow(this)" title="Remove">&times;</button>
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-3">
                            <button type="button" id="addCardBtn"
                                    class="inline-flex items-center px-4 py-2 rounded-xl bg-primary-600 text-white shadow hover:shadow-md">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M12 6v12m6-6H6" />
                                </svg>
                                Add Card
                            </button>
                        </div>
                        @error('cards_json')
                        <p class="text-danger-600 text-sm mt-2">{{ $message }}</p>
                        @enderror
                        @error('cards_json.*')
                        <p class="text-danger-600 text-sm mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- BALANCE --}}
                    <div>
                        <label class="block text-sm font-semibold mb-3">Balance</label>
                        <input type="number" step="0.01" name="balance"
                               value="{{ old('balance', $lead->balance) }}"
                               class="block w-full px-4 py-3 border rounded-xl bg-white/80 dark:bg-gray-700/80 border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        @error('balance')
                        <p class="text-danger-600 text-sm mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Row: CREDITS --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div>
                        <label class="block text-sm font-semibold mb-3">Credit Score</label>
                        <input type="number" step="0.01" name="credits"
                               value="{{ old('credits', $lead->credits) }}"
                               class="block w-full px-4 py-3 border rounded-xl bg-white/80 dark:bg-gray-700/80 border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        @error('credits')
                        <p class="text-danger-600 text-sm mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="md:col-span-2"></div>
                </div>

                <div class="h-px w-full bg-gray-200/70 dark:bg-gray-700/70"></div>

                {{-- SECTION: Documents & Notes --}}
                <div class="space-y-4">
                    <h4 class="text-base font-semibold text-gray-900 dark:text-white">Documents & Notes</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                        <!--<div class="md:col-span-1">-->
                        <!--    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">-->
                        <!--        {{ $lead->lead_pdf_path ? 'Replace PDF (optional)' : 'Upload PDF (optional)' }}-->
                        <!--    </label>-->
                        <!--    <input type="file" name="lead_pdf" accept="application/pdf"-->
                        <!--        class="block w-full text-sm file:mr-4 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:bg-primary-600 file:text-white hover:file:bg-primary-700 transition">-->
                        <!--    @error('lead_pdf')-->
                        <!--        <p class="text-danger-600 text-sm mt-2">{{ $message }}</p>-->
                        <!--    @enderror-->
                        <!--</div>-->

                        <!--muneeb-->
                        <div class="md:col-span-2">
                            @if ($lead->lead_pdf_path)
                                <label
                                    class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Actions</label>
                                <label class="inline-flex items-center gap-2">
                                    <input type="checkbox" name="remove_lead_pdf" value="1"
                                           class="rounded border-gray-300 dark:border-gray-600">
                                    <span class="text-sm text-gray-700 dark:text-gray-300">Remove current PDF</span>
                                </label>
                            @endif
                        </div>
                    </div>

                    {{-- Notes --}}
                    <div>
                        <label class="block text-sm font-semibold mb-3">Messages / Notes</label>
                        <textarea name="notes" rows="8"
                                  class="block w-full px-4 py-3 border rounded-xl bg-white/80 dark:bg-gray-700/80 border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                                  placeholder="Notes...">{{ old('notes', $lead->notes) }}</textarea>
                        @error('notes')
                        <p class="text-danger-600 text-sm mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Report button (does not submit lead form) - Show only if user can report AND lead doesn't have existing issues --}}
                    @php
                        $canReport = auth()->user()->role === 'admin' || $lead->assigned_to === auth()->id();
                        $hasExistingIssue = $lead->issues()->exists();
                    @endphp

                    @if ($canReport && !$hasExistingIssue)
                        <button type="button" @click="issueOpen = true"
                                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gradient-to-r from-red-500 to-red-600 text-white shadow-lg hover:shadow-xl hover:-translate-y-0.5 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Request Report
                        </button>
                    @endif

                </div>

                {{-- Bottom selectors: Status / TO / Super Agent / Closer (admin only sees assignment fields) --}}
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    {{-- Status --}}
                    <div>
                        <label class="block text-sm font-semibold mb-3">Select Status</label>
                        <select name="status"
                                class="block w-full px-4 py-3 border rounded-xl bg-white/80 dark:bg-gray-700/80 border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            <option value="">Select Status</option>
                            @foreach ($statuses as $s)
                                <option value="{{ $s }}"
                                    {{ old('status', $lead->status) === $s ? 'selected' : '' }}>
                                    {{ $s }}
                                </option>
                            @endforeach
                        </select>
                        @error('status')
                        <p class="text-danger-600 text-sm mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    @if (auth()->user()?->isAdmin() || auth()->user()->isLeadManager())
                        {{-- TO / assignee --}}
                        <div>
                            <label class="block text-sm font-semibold mb-3">Select TO</label>
                            <select name="assigned_to"
                                    class="block w-full px-4 py-3 border rounded-xl bg-white/80 dark:bg-gray-700/80 border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                <option value="">Select TO</option>
                                @foreach ($tos as $to)
                                    <option value="{{ $to->id }}"
                                        {{ old('assigned_to', $lead->assigned_to) == $to->id ? 'selected' : '' }}>
                                        {{ $to->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('assigned_to')
                            <p class="text-danger-600 text-sm mt-2">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Super Agent --}}
                        <div>
                            <label class="block text-sm font-semibold mb-3">Select Super Agent</label>
                            <select name="super_agent_id"
                                    class="block w-full px-4 py-3 border rounded-xl bg-white/80 dark:bg-gray-700/80 border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                <option value="">Select Super Agent</option>
                                @foreach ($superAgents as $agent)
                                    <option value="{{ $agent->id }}"
                                        {{ old('super_agent_id', $lead->super_agent_id) == $agent->id ? 'selected' : '' }}>
                                        {{ $agent->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('super_agent_id')
                            <p class="text-danger-600 text-sm mt-2">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Closer --}}
                        <div>
                            <label class="block text-sm font-semibold mb-3">Select Closer</label>
                            <select name="closer_id"
                                    class="block w-full px-4 py-3 border rounded-xl bg-white/80 dark:bg-gray-700/80 border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                <option value="">Select Closer</option>
                                @foreach ($closers as $closer)
                                    <option value="{{ $closer->id }}"
                                        {{ old('closer_id', $lead->closer_id) == $closer->id ? 'selected' : '' }}>
                                        {{ $closer->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('closer_id')
                            <p class="text-danger-600 text-sm mt-2">{{ $message }}</p>
                            @enderror
                        </div>
                    @else
                        <div class="md:col-span-3"></div>
                    @endif
                </div>

                <!-- Sticky Actions -->
                <div
                    class="flex items-center justify-between space-x-4 pt-6 border-t border-gray-200/50 dark:border-gray-700/50">
                    @php
                        // Get unread notifications related to this lead's issues
                        $leadIssues = $lead->issues()->pluck('id')->toArray();
                        $unreadNotifications = auth()
                            ->user()
                            ->unreadNotifications->filter(function ($notification) use ($leadIssues) {
                                return isset($notification->data['issue_id']) &&
                                    in_array($notification->data['issue_id'], $leadIssues);
                            });
                        $unreadCount = $unreadNotifications->count();

                        // Get issue IDs from notifications, handling potential data structure differences
                        $notificationIssueIds = [];
                        foreach ($unreadNotifications as $notification) {
                            if (isset($notification->data['issue_id'])) {
                                $notificationIssueIds[] = $notification->data['issue_id'];
                            }
                        }

                        // Get the most recent issue with notifications
                        $latestIssueWithNotification =
                            $unreadCount > 0 && !empty($notificationIssueIds)
                                ? $lead->issues()->whereIn('id', $notificationIssueIds)->latest()->first()
                                : null;

                        // Check if lead has any issues at all
                        $hasAnyIssues = $lead->issues()->exists();
                    @endphp

                    @if ($latestIssueWithNotification)
                        <div class="flex space-x-2">
                            <a href="{{ route('leads.issues.show', $latestIssueWithNotification) }}"
                               class="inline-flex items-center px-6 py-3 border border-blue-500 text-blue-700 bg-blue-50 dark:bg-blue-900/30 dark:text-blue-300 font-semibold rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition-all duration-200">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                </svg>
                                View Responses
                                @if ($unreadCount > 0)
                                    <span
                                        class="ml-2 text-xs bg-red-600 text-white px-2 py-0.5 rounded-full">{{ $unreadCount }}</span>
                                @endif
                            </a>
                        </div>
                    @elseif ($hasAnyIssues)
                        <div class="flex space-x-2">
                            <a href="{{ route('leads.issues.show', $lead->issues()->latest()->first()) }}"
                               class="inline-flex items-center px-6 py-3 border border-gray-500 text-gray-700 bg-gray-50 dark:bg-gray-900/30 dark:text-gray-300 font-semibold rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition-all duration-200">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                </svg>
                                View Report
                            </a>
                        </div>
                    @else
                        <div></div> {{-- Empty div to maintain flex layout --}}
                    @endif

                    <div class="flex items-center space-x-4">
                        <a href="{{ route('leads.index') }}"
                           class="inline-flex items-center px-6 py-3 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 bg-white/80 dark:bg-gray-700/80 backdrop-blur-xl font-semibold rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition-all duration-200">
                            Cancel
                        </a>
                        <button type="submit"
                                class="inline-flex items-center px-8 py-3 bg-gradient-to-r from-primary-500 to-primary-600 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                                :disabled="isSubmitting" :class="{ 'btn-loading': isSubmitting }">
                            <span class="btn-text flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M5 13l4 4L19 7" />
                                </svg>
                                Save Changes
                            </span>
                        </button>
                    </div>
                </div>
            </form>
        </div>

        {{-- ===== Issue Report Modal (teleported to <body>) - Show only if no existing issues ===== --}}
        @if (!$hasExistingIssue)
            <template x-teleport="body">
                <div x-cloak x-show="issueOpen" x-transition.opacity
                     class="fixed inset-0 z-[100] flex items-center justify-center p-4">
                    {{-- Backdrop (click to close) --}}
                    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="issueOpen=false"></div>

                    {{-- Dialog --}}
                    <div
                        role="dialog" aria-modal="true" aria-labelledby="issue-dialog-title"
                        class="relative w-full max-w-2xl max-h-[90vh] overflow-hidden rounded-2xl border border-gray-200/50 dark:border-gray-700/50
                               bg-white/90 dark:bg-gray-800/90 shadow-2xl">

                        {{-- Header --}}
                        <div class="px-6 py-4 bg-gradient-to-r from-primary-500 to-primary-600 text-white">
                            <div class="flex items-center justify-between">
                                <h3 id="issue-dialog-title" class="text-lg font-semibold">Report a Request</h3>
                                <button type="button" @click="issueOpen=false" class="p-1 rounded hover:bg-white/20"
                                        aria-label="Close">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        {{-- Body (scrollable on small screens) --}}
                        <form id="issue-report-form" method="POST" action="{{ route('leads.issues.store', $lead) }}"
                              enctype="multipart/form-data"
                              class="p-6 space-y-4 overflow-y-auto"
                              style="max-height: calc(90vh - 64px - 72px);"> {{-- subtract header/footer heights --}}
                            @csrf

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Title</label>
                                <input x-ref="issueTitle" name="title" required maxlength="160"
                                       class="mt-1 w-full px-4 py-2 rounded-xl border border-gray-300 dark:border-gray-600
                                              bg-white/80 dark:bg-gray-700/80 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                @error('title')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Priority</label>
                                    <select name="priority"
                                            class="mt-1 w-full px-4 py-2 rounded-xl border border-gray-300 dark:border-gray-600
                                                   bg-white/80 dark:bg-gray-700/80 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                        <option value="normal" selected>Normal</option>
                                        <option value="low">Low</option>
                                        <option value="high">High</option>
                                        <option value="urgent">Urgent</option>
                                    </select>
                                    @error('priority')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Description</label>
                                <textarea name="description" required maxlength="5000" rows="5"
                                          class="mt-1 w-full px-4 py-2 rounded-xl border border-gray-300 dark:border-gray-600
                                                 bg-white/80 dark:bg-gray-700/80 focus:ring-2 focus:ring-primary-500 focus:border-primary-500"></textarea>
                                @error('description')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!--<div>-->
                            <!--    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Attachments</label>-->
                            <!--    <input type="file" name="attachments[]" multiple-->
                            <!--        class="mt-1 w-full text-sm file:mr-4 file:py-2.5 file:px-4 file:rounded-lg file:border-0-->
                            <!--          file:bg-primary-600 file:text-white hover:file:bg-primary-700 transition" />-->
                            <!--    <p class="text-xs text-gray-500 mt-1">Max 10MB per file. Attach any relevant files that might-->
                            <!--        help the report manager understand the issue.</p>-->
                            <!--    @error('attachments.*')-->
                            <!--        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>-->
                            <!--    @enderror-->
                            <!--</div>-->
                        </form>

                        {{-- Footer (sticks under the scroll area) --}}
                        <div class="px-6 py-4 flex items-center justify-end gap-2 border-t border-gray-200/50 dark:border-gray-700/50">
                            <button type="button" @click="issueOpen=false"
                                    class="px-4 py-2 rounded-xl border border-gray-300 dark:border-gray-600
                                           bg-white/80 dark:bg-gray-700/80 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                Cancel
                            </button>
                            <button type="submit" form="issue-report-form"
                                    class="px-4 py-2 rounded-xl bg-gradient-to-r from-primary-500 to-primary-600 text-white
                                           shadow hover:shadow-md transition">
                                Submit Request
                            </button>
                        </div>
                    </div>
                </div>
            </template>
        @endif
        {{-- ===== /Issue Report Modal ===== --}}

    </div>

    @push('scripts')
        <script>
            // ===== Numbers repeater =====
            const numbersRepeater = document.getElementById('numbersRepeater');
            const addNumberBtn = document.getElementById('addNumberBtn');

            addNumberBtn?.addEventListener('click', () => {
                const row = document.createElement('div');
                row.className = 'flex gap-2';
                row.innerHTML = `
                    <input name="numbers[]" value=""
                           class="flex-1 px-4 py-3 border rounded-xl bg-white/80 dark:bg-gray-700/80 border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                           placeholder="(###) ###-####">
                    <button type="button"
                            class="shrink-0 px-3 rounded-xl border border-gray-300 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700"
                            onclick="removeNumberRow(this)" title="Remove">&times;</button>
                `;
                numbersRepeater.appendChild(row);
            });

            window.removeNumberRow = (btn) => {
                const rows = numbersRepeater.querySelectorAll('.flex.gap-2');
                if (rows.length <= 1) {
                    btn.closest('.flex.gap-2').querySelector('input').value = '';
                    return;
                }
                btn.closest('.flex.gap-2').remove();
            };

            // ===== Cards (JSON) repeater =====
            const cardsRepeater = document.getElementById('cardsRepeater');
            const addCardBtn = document.getElementById('addCardBtn');

            addCardBtn?.addEventListener('click', () => {
                const row = document.createElement('div');
                row.className = 'flex gap-2';
                row.innerHTML = `
                    <input name="cards_json[]" value=""
                           class="flex-1 px-4 py-3 border rounded-xl bg-white/80 dark:bg-gray-700/80 border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                           placeholder="e.g., Visa / Amex / Mastercard">
                    <button type="button"
                            class="shrink-0 px-3 rounded-xl border border-gray-300 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700"
                            onclick="removeCardRow(this)" title="Remove">&times;</button>
                `;
                cardsRepeater.appendChild(row);
            });

            window.removeCardRow = (btn) => {
                const rows = cardsRepeater.querySelectorAll('.flex.gap-2');
                if (rows.length <= 1) {
                    btn.closest('.flex.gap-2').querySelector('input').value = '';
                    return;
                }
                btn.closest('.flex.gap-2').remove();
            };
        </script>
    @endpush
@endsection
