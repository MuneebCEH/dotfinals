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
                return true;
            }
        }">
        <!-- Header -->
        <div class="card-premium rounded-2xl p-8 border border-gray-200/50 dark:border-gray-700/50">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-3xl font-extrabold tracking-tight text-gray-900 dark:text-white mb-2">Create New Lead
                    </h2>
                    <p class="text-lg font-medium text-gray-600 dark:text-gray-400">Add a new entry to your leads ecosystem.
                    </p>
                </div>
                <a href="{{ route('leads.index') }}"
                    class="inline-flex items-center px-6 py-3 border border-gray-200/50 dark:border-gray-700/50 text-gray-700 dark:text-gray-300 bg-white/50 dark:bg-gray-800/50 backdrop-blur-xl font-bold rounded-xl hover:shadow-lg transition-all duration-300">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to Leads
                </a>
            </div>
        </div>

        <!-- Form Card -->
        <div class="card-premium rounded-2xl border border-gray-200/50 dark:border-gray-700/50 overflow-hidden">

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

                <div class="space-y-6">
                    {{-- Row: Date / Name --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div>
                            <label class="block text-sm font-semibold mb-3">Date (MM-DD-YYYY)</label>
                            <input name="created_at_display" value="{{ now()->format('m-d-Y') }}" readonly
                                   class="block w-full px-4 py-3 border rounded-xl bg-gray-100 dark:bg-gray-800 border-gray-300 dark:border-gray-600 focus:ring-0 cursor-not-allowed">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold mb-3">Name</label>
                            <div class="grid grid-cols-3 gap-2">
                                <input name="first_name" value="{{ old('first_name') }}" placeholder="First Name" required
                                       class="block w-full px-4 py-3 border rounded-xl bg-white/80 dark:bg-gray-700/80 border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                <input name="middle_initial" value="{{ old('middle_initial') }}" placeholder="M.I." maxlength="2"
                                       class="block w-full px-4 py-3 border rounded-xl bg-white/80 dark:bg-gray-700/80 border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                <input name="surname" value="{{ old('surname') }}" placeholder="Last Name" required
                                       class="block w-full px-4 py-3 border rounded-xl bg-white/80 dark:bg-gray-700/80 border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            </div>
                        </div>
                    </div>

                    <div class="h-px w-full bg-gray-200/70 dark:bg-gray-700/70"></div>

                    {{-- Row: Phone / Cell --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div>
                            <label class="block text-sm font-semibold mb-3">Phone (000-000-0000)</label>
                            <input name="numbers[]" value="{{ old('numbers.0') }}" placeholder="000-000-0000"
                                   class="block w-full px-4 py-3 border rounded-xl bg-white/80 dark:bg-gray-700/80 border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold mb-3">Cell (000-000-0000)</label>
                            <input name="cell" value="{{ old('cell') }}" placeholder="000-000-0000"
                                   class="block w-full px-4 py-3 border rounded-xl bg-white/80 dark:bg-gray-700/80 border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        </div>
                    </div>

                    {{-- Row: Address / City / State / Zip --}}
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                        <div class="md:col-span-1">
                            <label class="block text-sm font-semibold mb-3">Address</label>
                            <input name="street" value="{{ old('street') }}"
                                   class="block w-full px-4 py-3 border rounded-xl bg-white/80 dark:bg-gray-700/80 border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold mb-3">City</label>
                            <input name="city" value="{{ old('city') }}"
                                   class="block w-full px-4 py-3 border rounded-xl bg-white/80 dark:bg-gray-700/80 border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold mb-3">State</label>
                            <input name="state_abbreviation" value="{{ old('state_abbreviation') }}" maxlength="5"
                                   class="uppercase block w-full px-4 py-3 border rounded-xl bg-white/80 dark:bg-gray-700/80 border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold mb-3">Zip code</label>
                            <input name="zip_code" value="{{ old('zip_code') }}"
                                   class="block w-full px-4 py-3 border rounded-xl bg-white/80 dark:bg-gray-700/80 border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        </div>
                    </div>

                    {{-- Row: SSN / DOB / MMN / Email --}}
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                        <div>
                            <label class="block text-sm font-semibold mb-3">SSN (000-00-0000)</label>
                            <input name="ssn" value="{{ old('ssn') }}" placeholder="000-00-0000"
                                   class="block w-full px-4 py-3 border rounded-xl bg-white/80 dark:bg-gray-700/80 border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold mb-3">DOB (MM-DD-YYYY)</label>
                            <input name="dob" value="{{ old('dob') }}" placeholder="MM-DD-YYYY"
                                   class="block w-full px-4 py-3 border rounded-xl bg-white/80 dark:bg-gray-700/80 border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold mb-3">MMN</label>
                            <input name="mmn" value="{{ old('mmn') }}"
                                   class="block w-full px-4 py-3 border rounded-xl bg-white/80 dark:bg-gray-700/80 border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold mb-3">Email</label>
                            <input type="email" name="email" value="{{ old('email') }}"
                                   class="block w-full px-4 py-3 border rounded-xl bg-white/80 dark:bg-gray-700/80 border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        </div>
                    </div>

                    <div class="h-px w-full bg-gray-200/70 dark:bg-gray-700/70"></div>

                    {{-- Section: Financial Summary --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                        <div>
                            <label class="block text-sm font-semibold mb-3">Credit Score</label>
                            <input type="number" name="fico" value="{{ old('fico') }}"
                                   class="block w-full px-4 py-3 border rounded-xl bg-white/80 dark:bg-gray-700/80 border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-primary-500 focus:border-primary-500" placeholder="0">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold mb-3">Total Cards</label>
                            <input type="number" name="total_cards" value="{{ old('total_cards') }}"
                                   class="block w-full px-4 py-3 border rounded-xl bg-white/80 dark:bg-gray-700/80 border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-primary-500 focus:border-primary-500" placeholder="0">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold mb-3">Total Debt ($)</label>
                            <input name="total_debt" value="{{ old('total_debt') }}"
                                   class="block w-full px-4 py-3 border rounded-xl bg-white/80 dark:bg-gray-700/80 border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-primary-500 focus:border-primary-500" placeholder="$0.00">
                        </div>
                    </div>

                    <div class="h-px w-full bg-gray-200/70 dark:bg-gray-700/70"></div>

                    <div class="h-px w-full bg-gray-200/70 dark:bg-gray-700/70"></div>

                    {{-- SECTION: Bank Details Repeater --}}
                    <div id="banksContainer" class="space-y-10">
                        @php
                            $banks = old('bank_details', [[]]);
                        @endphp

                        @foreach ($banks as $index => $bank)
                            <div class="bank-row space-y-6 relative p-6 rounded-2xl bg-gray-50/50 dark:bg-gray-800/30 border border-gray-100 dark:border-gray-700">
                                <div class="flex items-center justify-between mb-2">
                                    <h4 class="text-lg font-bold text-primary-600 flex items-center gap-2">
                                        <i class="fas fa-university"></i>
                                        Bank #<span class="bank-index">{{ $index + 1 }}</span>
                                    </h4>
                                    @if($index > 0)
                                        <button type="button" onclick="removeBankRow(this)" class="text-red-500 hover:text-red-700 transition font-semibold text-sm flex items-center gap-1">
                                            <i class="fas fa-trash-alt"></i> Remove Bank
                                        </button>
                                    @endif
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                                    <div>
                                        <label class="block text-sm font-semibold mb-3">Bank Name</label>
                                        <input name="bank_details[{{ $index }}][bank_name]" value="{{ $bank['bank_name'] ?? '' }}"
                                               class="block w-full px-4 py-3 border rounded-xl bg-white/80 dark:bg-gray-700/80 border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold mb-3">Name on Card</label>
                                        <input name="bank_details[{{ $index }}][name_on_card]" value="{{ $bank['name_on_card'] ?? '' }}"
                                               class="block w-full px-4 py-3 border rounded-xl bg-white/80 dark:bg-gray-700/80 border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold mb-3">Card Number</label>
                                        <div class="relative">
                                            <input name="bank_details[{{ $index }}][card_number]" value="{{ $bank['card_number'] ?? '' }}"
                                                   class="card-number-input block w-full px-4 py-3 border rounded-xl bg-white/80 dark:bg-gray-700/80 border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 pr-12"
                                                   placeholder="0000 0000 0000 0000">
                                            <div class="card-status absolute right-3 top-1/2 -translate-y-1/2 flex items-center gap-2">
                                                <i class="card-type-icon text-gray-400 text-lg"></i>
                                                <i class="card-valid-icon hidden text-sm"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                                    <div>
                                        <label class="block text-sm font-semibold mb-3">Exp Date (MM-YYYY)</label>
                                        <input name="bank_details[{{ $index }}][exp_date]" value="{{ $bank['exp_date'] ?? '' }}" placeholder="MM-YYYY"
                                               class="block w-full px-4 py-3 border rounded-xl bg-white/80 dark:bg-gray-700/80 border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold mb-3">CVC</label>
                                        <input name="bank_details[{{ $index }}][cvc]" value="{{ $bank['cvc'] ?? '' }}"
                                               class="block w-full px-4 py-3 border rounded-xl bg-white/80 dark:bg-gray-700/80 border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold mb-3">Balance ($)</label>
                                        <input name="bank_details[{{ $index }}][balance]" value="{{ $bank['balance'] ?? '' }}"
                                               class="block w-full px-4 py-3 border rounded-xl bg-white/80 dark:bg-gray-700/80 border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                                    <div>
                                        <label class="block text-sm font-semibold mb-3">Available ($)</label>
                                        <input name="bank_details[{{ $index }}][available]" value="{{ $bank['available'] ?? '' }}"
                                               class="block w-full px-4 py-3 border rounded-xl bg-white/80 dark:bg-gray-700/80 border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                    </div>
                                    <div class="flex gap-2">
                                        <div class="flex-1">
                                            <label class="block text-sm font-semibold mb-3">Last Payment ($)</label>
                                            <input name="bank_details[{{ $index }}][last_payment_amount]" value="{{ $bank['last_payment_amount'] ?? '' }}"
                                                   class="block w-full px-4 py-3 border rounded-xl bg-white/80 dark:bg-gray-700/80 border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                        </div>
                                        <div class="flex-1">
                                            <label class="block text-sm font-semibold mb-3">Date (DD-MM-YY)</label>
                                            <input name="bank_details[{{ $index }}][last_payment_date]" value="{{ $bank['last_payment_date'] ?? '' }}" placeholder="DD-MM-YY"
                                                   class="block w-full px-4 py-3 border rounded-xl bg-white/80 dark:bg-gray-700/80 border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                        </div>
                                    </div>
                                    <div class="flex gap-2">
                                        <div class="flex-1">
                                            <label class="block text-sm font-semibold mb-3">Next Payment ($)</label>
                                            <input name="bank_details[{{ $index }}][next_payment_amount]" value="{{ $bank['next_payment_amount'] ?? '' }}"
                                                   class="block w-full px-4 py-3 border rounded-xl bg-white/80 dark:bg-gray-700/80 border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                        </div>
                                        <div class="flex-1">
                                            <label class="block text-sm font-semibold mb-3">Date (DD-MM-YY)</label>
                                            <input name="bank_details[{{ $index }}][next_payment_date]" value="{{ $bank['next_payment_date'] ?? '' }}" placeholder="DD-MM-YY"
                                                   class="block w-full px-4 py-3 border rounded-xl bg-white/80 dark:bg-gray-700/80 border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                        </div>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                                    <div>
                                        <label class="block text-sm font-semibold mb-3">Credit Limit ($)</label>
                                        <input name="bank_details[{{ $index }}][credit_limit]" value="{{ $bank['credit_limit'] ?? '' }}"
                                               class="block w-full px-4 py-3 border rounded-xl bg-white/80 dark:bg-gray-700/80 border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold mb-3">Apr (%)</label>
                                        <input name="bank_details[{{ $index }}][apr]" value="{{ $bank['apr'] ?? '' }}" placeholder="0.00%"
                                               class="block w-full px-4 py-3 border rounded-xl bg-white/80 dark:bg-gray-700/80 border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold mb-3">Charge ($)</label>
                                        <input name="bank_details[{{ $index }}][charge]" value="{{ $bank['charge'] ?? '' }}" placeholder="$0.00"
                                               class="block w-full px-4 py-3 border rounded-xl bg-white/80 dark:bg-gray-700/80 border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold mb-3">Tollfree</label>
                                        <input name="bank_details[{{ $index }}][tollfree]" value="{{ $bank['tollfree'] ?? '' }}" placeholder="1-8xx-xxx-xxxx"
                                               class="block w-full px-4 py-3 border rounded-xl bg-white/80 dark:bg-gray-700/80 border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="flex justify-center mt-6">
                        <button type="button" id="addBankBtn" class="inline-flex items-center gap-2 px-6 py-3 bg-white dark:bg-gray-800 border-2 border-dashed border-primary-500 text-primary-600 font-bold rounded-xl hover:bg-primary-50 dark:hover:bg-primary-900/20 transition-all duration-300 group">
                            <i class="fas fa-plus-circle group-hover:scale-110 transition-transform"></i>
                            Add More Bank
                        </button>
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
                                        'Death Submitted'
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

                        @if (auth()->user()?->isAdmin() || auth()->user()?->isSuperAgent() || auth()->user()?->role === 'user' || auth()->user()?->role === 'lead_manager')
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Select
                                    TO</label>
                                <select name="assigned_to"
                                    class="block w-full px-4 py-3 border rounded-xl bg-white/80 dark:bg-gray-700/80 border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                    <option value="">Select TO</option>
                                    @foreach ($tos ?? [] as $to)
                                        <option value="{{ $to->id }}" {{ old('assigned_to') == $to->id ? 'selected' : '' }}>
                                            {{ $to->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('assigned_to')
                                    <p class="text-danger-600 text-sm mt-2">{{ $message }}</p>
                                @enderror
                            </div>

                            @if (auth()->user()?->isAdmin() || auth()->user()?->isSuperAgent() || auth()->user()?->isLeadManager())
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Select
                                        Super Agent</label>
                                    <select name="super_agent_id"
                                        class="block w-full px-4 py-3 border rounded-xl bg-white/80 dark:bg-gray-700/80 border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                        <option value="">Select Super Agent</option>
                                        @foreach ($superAgents ?? [] as $agent)
                                            <option value="{{ $agent->id }}" {{ old('super_agent_id') == $agent->id ? 'selected' : '' }}>
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
                                            <option value="{{ $closer->id }}" {{ old('closer_id') == $closer->id ? 'selected' : '' }}>
                                                {{ $closer->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('closer_id')
                                        <p class="text-danger-600 text-sm mt-2">{{ $message }}</p>
                                    @enderror
                                </div>
                            @endif
                        @endif
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-5 gap-6 mt-6">
                        <div>
                            <label class="block text-sm font-semibold mb-3">Agent Name</label>
                            <input name="agent_name" value="{{ old('agent_name') }}"
                                   class="block w-full px-4 py-3 border rounded-xl bg-white/80 dark:bg-gray-700/80 border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold mb-3">TL Name</label>
                            <input name="tl_name" value="{{ old('tl_name') }}"
                                   class="block w-full px-4 py-3 border rounded-xl bg-white/80 dark:bg-gray-700/80 border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold mb-3">Closer Name</label>
                            <input name="closer_name" value="{{ old('closer_name') }}"
                                   class="block w-full px-4 py-3 border rounded-xl bg-white/80 dark:bg-gray-700/80 border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold mb-3">Verifier Name</label>
                            <input name="verifier_name" value="{{ old('verifier_name') }}"
                                   class="block w-full px-4 py-3 border rounded-xl bg-white/80 dark:bg-gray-700/80 border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold mb-3">Combined Charge ($)</label>
                            <input name="combined_charge" value="{{ old('combined_charge') }}" placeholder="$ 0.00"
                                   class="block w-full px-4 py-3 border rounded-xl bg-white/80 dark:bg-gray-700/80 border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        </div>
                    </div>
                </div>

                <!-- Sticky Actions -->
                <div
                    class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200/50 dark:border-gray-700/50">
                    <a href="{{ route('leads.index') }}"
                        class="inline-flex items-center px-6 py-3 border border-gray-200/50 dark:border-gray-700/50 text-gray-700 dark:text-gray-300 bg-white/50 dark:bg-gray-800/50 backdrop-blur-xl font-bold rounded-xl transition-all duration-300">
                        Cancel
                    </a>
                    <button type="submit"
                        class="inline-flex items-center px-8 py-3 bg-gradient-to-br from-primary-500 to-primary-600 text-white font-bold rounded-xl shadow-lg hover:shadow-primary-500/25 transform hover:-translate-y-0.5 transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed"
                        :disabled="isSubmitting" :class="{ 'btn-loading': isSubmitting }">
                        <span class="btn-text flex items-center gap-2">
                            <i class="fas fa-plus"></i>
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

            // ===== Banks repeater =====
            const banksContainer = document.getElementById('banksContainer');
            const addBankBtn = document.getElementById('addBankBtn');

            addBankBtn?.addEventListener('click', () => {
                const index = banksContainer.querySelectorAll('.bank-row').length;
                const template = banksContainer.querySelector('.bank-row').cloneNode(true);

                // Update index numbers in title and input names
                template.querySelector('.bank-index').textContent = index + 1;

                // Clear input values
                template.querySelectorAll('input').forEach(input => {
                    input.value = '';
                    // Update field name: bank_details[0][...] -> bank_details[X][...]
                    const name = input.getAttribute('name');
                    if (name) {
                        input.setAttribute('name', name.replace(/bank_details\[\d+\]/, `bank_details[${index}]`));
                    }
                });

                // Ensure there's a remove button if it's not the first one
                if (!template.querySelector('button[onclick="removeBankRow(this)"]')) {
                    const header = template.querySelector('.flex.items-center.justify-between');
                    const removeBtn = document.createElement('button');
                    removeBtn.type = 'button';
                    removeBtn.setAttribute('onclick', 'removeBankRow(this)');
                    removeBtn.className = 'text-red-500 hover:text-red-700 transition font-semibold text-sm flex items-center gap-1';
                    removeBtn.innerHTML = '<i class="fas fa-trash-alt"></i> Remove Bank';
                    header.appendChild(removeBtn);
                }

                banksContainer.appendChild(template);
            });

            window.removeBankRow = (btn) => {
                const row = btn.closest('.bank-row');
                row.remove();

                // Re-index remaining banks
                banksContainer.querySelectorAll('.bank-row').forEach((row, i) => {
                    row.querySelector('.bank-index').textContent = i + 1;
                    row.querySelectorAll('input').forEach(input => {
                        const name = input.getAttribute('name');
                        if (name) {
                            input.setAttribute('name', name.replace(/bank_details\[\d+\]/, `bank_details[${i}]`));
                        }
                    });
                });
            };

            // ===== Card Checker Logic =====
            const cardTypes = [
                { name: 'visa', icon: 'fab fa-cc-visa', re: /^4/ },
                { name: 'mastercard', icon: 'fab fa-cc-mastercard', re: /^(5[1-5]|222[1-9]|22[3-9]|2[3-6]|27[01]|2720)/ },
                { name: 'amex', icon: 'fab fa-cc-amex', re: /^3[47]/ },
                { name: 'discover', icon: 'fab fa-cc-discover', re: /^(6011|622|64|65)/ },
                { name: 'jcb', icon: 'fab fa-cc-jcb', re: /^35/ },
                { name: 'diners', icon: 'fab fa-cc-diners-club', re: /^3(0[0-5]|[68])/ }
            ];

            function validateLuhn(number) {
                let sum = 0;
                let shouldDouble = false;
                for (let i = number.length - 1; i >= 0; i--) {
                    let digit = parseInt(number.charAt(i));
                    if (shouldDouble) {
                        if ((digit *= 2) > 9) digit -= 9;
                    }
                    sum += digit;
                    shouldDouble = !shouldDouble;
                }
                return (sum % 10) === 0;
            }

            function updateCardStatus(input) {
                const value = input.value.replace(/\D/g, '');
                const wrapper = input.closest('.relative');
                const typeIcon = wrapper.querySelector('.card-type-icon');
                const validIcon = wrapper.querySelector('.card-valid-icon');

                // Detect Type
                let detected = cardTypes.find(t => t.re.test(value));
                typeIcon.className = 'card-type-icon text-lg ' + (detected ? detected.icon + ' text-primary-500' : 'fas fa-credit-card text-gray-400');

                // Validate Luhn
                if (value.length >= 13) {
                    const isValid = validateLuhn(value);
                    validIcon.className = 'card-valid-icon text-sm ' + (isValid ? 'fas fa-check-circle text-green-500' : 'fas fa-times-circle text-red-500');
                    validIcon.classList.remove('hidden');
                    input.classList.toggle('border-green-500', isValid);
                    input.classList.toggle('border-red-500', !isValid);
                } else {
                    validIcon.classList.add('hidden');
                    input.classList.remove('border-green-500', 'border-red-500');
                }
            }

            // Event delegation for card inputs
            banksContainer?.addEventListener('input', (e) => {
                if (e.target.classList.contains('card-number-input')) {
                    // Format input: add spaces every 4 digits
                    let value = e.target.value.replace(/\D/g, '').substring(0, 16);
                    let formatted = value.match(/.{1,4}/g)?.join(' ') || value;
                    e.target.value = formatted;
                    updateCardStatus(e.target);
                }
            });

            // Initial validation for existing inputs
            document.querySelectorAll('.card-number-input').forEach(updateCardStatus);
        </script>
    @endpush
@endsection