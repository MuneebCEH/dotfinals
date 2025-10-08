@extends('layouts.app')

@section('title', 'Create Lead')
@section('page-title', 'Create Lead')

@push('styles')
    <style>
        [x-cloak] {
            display: none !important;
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
    <div class="space-y-8 animate-on-load" x-data="{
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
    }">
        <!-- Header -->
        <div
            class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl shadow-2xl rounded-2xl p-8 border border-gray-200/50 dark:border-gray-700/50">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">Create a New Lead</h2>
                    <p class="text-lg text-gray-600 dark:text-gray-400">Enter the client's details as accurately as possible.
                    </p>
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
                                d="M5.121 17.804A13.937 13.937 0 0112 15c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white">Lead Information</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Required fields are marked with <span
                                class="font-semibold">*</span>.</p>
                    </div>
                </div>
            </div>

            <form action="{{ route('leads.store') }}" method="POST" enctype="multipart/form-data" class="p-8 space-y-10"
                @submit="handleSubmit($event)">
                @csrf

                {{-- Global errors --}}
                @if ($errors->any())
                    <div class="bg-danger-50 border border-danger-200 text-danger-800 px-4 py-3 rounded-xl shadow-sm">
                        <ul class="list-disc list-inside text-sm">
                            @foreach ($errors->all() as $e)
                                <li>{{ $e }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- SECTION: Identity --}}
                <div class="space-y-4">
                    <h4 class="text-base font-semibold text-gray-900 dark:text-white">Identity</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">First Name
                                *</label>
                            <input name="first_name" value="{{ old('first_name') }}" required
                                class="block w-full px-4 py-3 border rounded-xl bg-white/80 dark:bg-gray-700/80 border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            @error('first_name')
                                <p class="text-danger-600 text-sm mt-2">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Middle
                                Initial</label>
                            <input name="middle_initial" value="{{ old('middle_initial') }}" maxlength="2"
                                class="block w-full px-4 py-3 border rounded-xl bg-white/80 dark:bg-gray-700/80 border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            @error('middle_initial')
                                <p class="text-danger-600 text-sm mt-2">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Surname
                                *</label>
                            <input name="surname" value="{{ old('surname') }}" required
                                class="block w-full px-4 py-3 border rounded-xl bg-white/80 dark:bg-gray-700/80 border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            @error('surname')
                                <p class="text-danger-600 text-sm mt-2">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Gen
                                Code</label>
                            <input name="gen_code" value="{{ old('gen_code') }}"
                                class="block w-full px-4 py-3 border rounded-xl bg-white/80 dark:bg-gray-700/80 border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            @error('gen_code')
                                <p class="text-danger-600 text-sm mt-2">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Age</label>
                            <input name="age" value="{{ old('age') }}" type="number" min="0"
                                class="block w-full px-4 py-3 border rounded-xl bg-white/80 dark:bg-gray-700/80 border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            @error('age')
                                <p class="text-danger-600 text-sm mt-2">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">SSN</label>
                            <input name="ssn" value="{{ old('ssn') }}"
                                class="block w-full px-4 py-3 border rounded-xl bg-white/80 dark:bg-gray-700/80 border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                                placeholder="(optional)">
                            @error('ssn')
                                <p class="text-danger-600 text-sm mt-2">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="h-px w-full bg-gray-200/70 dark:bg-gray-700/70"></div>

                {{-- SECTION: Address --}}
                <div class="space-y-4">
                    <h4 class="text-base font-semibold text-gray-900 dark:text-white">Address</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Street</label>
                            <input name="street" value="{{ old('street') }}"
                                class="block w-full px-4 py-3 border rounded-xl bg-white/80 dark:bg-gray-700/80 border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            @error('street')
                                <p class="text-danger-600 text-sm mt-2">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">City</label>
                            <input name="city" value="{{ old('city') }}"
                                class="block w-full px-4 py-3 border rounded-xl bg-white/80 dark:bg-gray-700/80 border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            @error('city')
                                <p class="text-danger-600 text-sm mt-2">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">State
                                Abbreviation</label>
                            <input name="state_abbreviation" value="{{ old('state_abbreviation') }}" maxlength="5"
                                class="uppercase block w-full px-4 py-3 border rounded-xl bg-white/80 dark:bg-gray-700/80 border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            @error('state_abbreviation')
                                <p class="text-danger-600 text-sm mt-2">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Zip
                                Code</label>
                            <input name="zip_code" value="{{ old('zip_code') }}"
                                class="block w-full px-4 py-3 border rounded-xl bg-white/80 dark:bg-gray-700/80 border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            @error('zip_code')
                                <p class="text-danger-600 text-sm mt-2">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="md:col-span-2"></div>
                    </div>
                </div>

                <div class="h-px w-full bg-gray-200/70 dark:bg-gray-700/70"></div>

                {{-- SECTION: Contact & Custom Fields --}}
                <div class="space-y-4">
                    <h4 class="text-base font-semibold text-gray-900 dark:text-white">Contact & Custom Fields</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                        {{-- Phone Numbers (dynamic) --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Phone
                                Numbers</label>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">Add multiple numbers if available.</p>
                            <div id="numbersRepeater" class="space-y-3">
                                @php
                                    $oldNumbers = old('numbers', []);
                                    if (empty($oldNumbers)) {
                                        $oldNumbers = [''];
                                    }
                                @endphp
                                @foreach ($oldNumbers as $n)
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

                        {{-- XFC06 --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">XFC06</label>
                            <input name="xfc06" value="{{ old('xfc06') }}"
                                class="block w-full px-4 py-3 border rounded-xl bg-white/80 dark:bg-gray-700/80 border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            @error('xfc06')
                                <p class="text-danger-600 text-sm mt-2">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- XFC07 --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">XFC07</label>
                            <input name="xfc07" value="{{ old('xfc07') }}"
                                class="block w-full px-4 py-3 border rounded-xl bg-white/80 dark:bg-gray-700/80 border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            @error('xfc07')
                                <p class="text-danger-600 text-sm mt-2">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">DEMO7</label>
                            <input name="demo7" value="{{ old('demo7') }}"
                                class="block w-full px-4 py-3 border rounded-xl bg-white/80 dark:bg-gray-700/80 border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            @error('demo7')
                                <p class="text-danger-600 text-sm mt-2">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">DEMO9</label>
                            <input name="demo9" value="{{ old('demo9') }}"
                                class="block w-full px-4 py-3 border rounded-xl bg-white/80 dark:bg-gray-700/80 border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            @error('demo9')
                                <p class="text-danger-600 text-sm mt-2">{{ $message }}</p>
                            @enderror
                        </div>
                        <div></div>
                    </div>
                </div>

                <div class="h-px w-full bg-gray-200/70 dark:bg-gray-700/70"></div>

                {{-- SECTION: Financial --}}
                <div class="space-y-4">
                    <h4 class="text-base font-semibold text-gray-900 dark:text-white">Financial</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                        {{-- FICO --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">FICO</label>
                            <input name="fico" value="{{ old('fico') }}" type="number"
                                class="block w-full px-4 py-3 border rounded-xl bg-white/80 dark:bg-gray-700/80 border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            @error('fico')
                                <p class="text-danger-600 text-sm mt-2">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Cards JSON (dynamic) --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Cards</label>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">Capture card labels (e.g., Visa,
                                Amex). Stored as JSON.</p>
                            <div id="cardsRepeater" class="space-y-3">
                                @php
                                    $oldCardsJson = old('cards_json', []);
                                    if (empty($oldCardsJson)) {
                                        $oldCardsJson = [''];
                                    }
                                @endphp
                                @foreach ($oldCardsJson as $c)
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

                        {{-- Balance --}}
                        <div>
                            <label
                                class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Balance</label>
                            <input name="balance" value="{{ old('balance') }}" type="number" step="0.01"
                                class="block w-full px-4 py-3 border rounded-xl bg-white/80 dark:bg-gray-700/80 border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            @error('balance')
                                <p class="text-danger-600 text-sm mt-2">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                        {{-- Credits --}}
                        <div>
                            <label
                                class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Credits</label>
                            <input name="credits" value="{{ old('credits') }}" type="number" step="0.01"
                                class="block w-full px-4 py-3 border rounded-xl bg-white/80 dark:bg-gray-700/80 border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            @error('credits')
                                <p class="text-danger-600 text-sm mt-2">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="md:col-span-2"></div>
                    </div>
                </div>

                <div class="h-px w-full bg-gray-200/70 dark:bg-gray-700/70"></div>

                {{-- SECTION: Documents & Notes --}}
                <div class="space-y-4">
                    <h4 class="text-base font-semibold text-gray-900 dark:text-white">Documents & Notes</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                        <div class="md:col-span-1">
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                Upload PDF (optional)
                            </label>
                            <input type="file" name="lead_pdf" accept="application/pdf"
                                class="block w-full text-sm file:mr-4 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:bg-primary-600 file:text-white hover:file:bg-primary-700 transition">
                            @error('lead_pdf')
                                <p class="text-danger-600 text-sm mt-2">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Messages /
                                Notes</label>
                            <textarea name="notes" rows="8"
                                class="block w-full px-4 py-3 border rounded-xl bg-white/80 dark:bg-gray-700/80 border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                                placeholder="Paste copied details or write notes...">{{ old('notes') }}</textarea>
                            @error('notes')
                                <p class="text-danger-600 text-sm mt-2">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="h-px w-full bg-gray-200/70 dark:bg-gray-700/70"></div>

                {{-- SECTION: Status & Assignment --}}
                <div class="space-y-4">
                    <h4 class="text-base font-semibold text-gray-900 dark:text-white">Status & Assignment</h4>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Select
                                Status</label>
                            <select name="status"
                                class="block w-full px-4 py-3 border rounded-xl bg-white/80 dark:bg-gray-700/80 border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                <option value="">Select Status</option>
                                @php
                                    $statuses = [
                                        'Voice Mail',
                                        'Wrong Info',
                                        'Not Interested',
                                        'Deal',
                                        'Call Back',
                                        'Disconnected Number',
                                        'Hangup',
                                        'Max Out',
                                        'Paid Off',
                                        'Not Qualified (NQ)',
                                        'Submitted',
                                        'That Submitted'
                                    ];
                                @endphp
                                @foreach ($statuses as $s)
                                    <option value="{{ $s }}" {{ old('status') === $s ? 'selected' : '' }}>
                                        {{ $s }}
                                    </option>
                                @endforeach
                            </select>
                            @error('status')
                                <p class="text-danger-600 text-sm mt-2">{{ $message }}</p>
                            @enderror
                        </div>

                        @if (auth()->user()?->isAdmin())
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Select
                                    TO</label>
                                <select name="assigned_to"
                                    class="block w-full px-4 py-3 border rounded-xl bg-white/80 dark:bg-gray-700/80 border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                    <option value="">Select TO</option>
                                    @foreach ($tos ?? [] as $to)
                                        <option value="{{ $to->id }}"
                                            {{ old('assigned_to') == $to->id ? 'selected' : '' }}>
                                            {{ $to->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('assigned_to')
                                    <p class="text-danger-600 text-sm mt-2">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Select
                                    Super Agent</label>
                                <select name="super_agent_id"
                                    class="block w-full px-4 py-3 border rounded-xl bg-white/80 dark:bg-gray-700/80 border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                    <option value="">Select Super Agent</option>
                                    @foreach ($superAgents ?? [] as $agent)
                                        <option value="{{ $agent->id }}"
                                            {{ old('super_agent_id') == $agent->id ? 'selected' : '' }}>
                                            {{ $agent->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('super_agent_id')
                                    <p class="text-danger-600 text-sm mt-2">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Select
                                    Closer</label>
                                <select name="closer_id"
                                    class="block w-full px-4 py-3 border rounded-xl bg-white/80 dark:bg-gray-700/80 border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                    <option value="">Select Closer</option>
                                    @foreach ($closers ?? [] as $closer)
                                        <option value="{{ $closer->id }}"
                                            {{ old('closer_id') == $closer->id ? 'selected' : '' }}>
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
                </div>

                <!-- Sticky Actions -->
                <div
                    class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200/50 dark:border-gray-700/50">
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
                            Create Lead
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            // ===== Phone Numbers repeater =====
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
