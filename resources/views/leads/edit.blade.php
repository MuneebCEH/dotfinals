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
            class="card-premium rounded-2xl p-8 border border-gray-200/50 dark:border-gray-700/50">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-3xl font-extrabold tracking-tight text-gray-900 dark:text-white mb-2">Edit Lead</h2>
                    <p class="text-lg font-medium text-gray-600 dark:text-gray-400">Refine and update lead intelligence data.</p>
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

        {{-- Uploaded PDF (preview, if present) --}}
        @if ($lead->lead_pdf_path)
            <div
                class="card-premium rounded-2xl border border-gray-200/50 dark:border-gray-700/50 overflow-hidden">
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
            class="card-premium rounded-2xl border border-gray-200/50 dark:border-gray-700/50 overflow-hidden">

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

                <div class="space-y-6">
                    {{-- Row: Date / Name --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div>
                            <label class="block text-sm font-semibold mb-3">Date (MM-DD-YYYY)</label>
                            <input name="created_at_display" value="{{ $lead->created_at ? $lead->created_at->format('m-d-Y') : now()->format('m-d-Y') }}" readonly
                                   class="block w-full px-4 py-3 border rounded-xl bg-gray-100 dark:bg-gray-800 border-gray-300 dark:border-gray-600 focus:ring-0 cursor-not-allowed">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold mb-3">Name</label>
                            <div class="grid grid-cols-3 gap-2">
                                <input name="first_name" value="{{ old('first_name', $lead->first_name) }}" placeholder="First Name"
                                       class="block w-full px-4 py-3 border rounded-xl bg-white/80 dark:bg-gray-700/80 border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                <input name="middle_initial" value="{{ old('middle_initial', $lead->middle_initial) }}" placeholder="M.I." maxlength="2"
                                       class="block w-full px-4 py-3 border rounded-xl bg-white/80 dark:bg-gray-700/80 border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                <input name="surname" value="{{ old('surname', $lead->surname) }}" placeholder="Last Name"
                                       class="block w-full px-4 py-3 border rounded-xl bg-white/80 dark:bg-gray-700/80 border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            </div>
                        </div>
                    </div>

                    <div class="h-px w-full bg-gray-200/70 dark:bg-gray-700/70"></div>

                    {{-- Row: Phone / Cell --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div>
                            @php
                                $primaryPhone = is_array($lead->numbers) ? ($lead->numbers[0] ?? '') : '';
                            @endphp
                            <label class="block text-sm font-semibold mb-3">Phone (000-000-0000)</label>
                            <input name="numbers[]" value="{{ old('numbers.0', $primaryPhone) }}" placeholder="000-000-0000"
                                   class="block w-full px-4 py-3 border rounded-xl bg-white/80 dark:bg-gray-700/80 border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold mb-3">Cell (000-000-0000)</label>
                            <input name="cell" value="{{ old('cell', $lead->cell) }}" placeholder="000-000-0000"
                                   class="block w-full px-4 py-3 border rounded-xl bg-white/80 dark:bg-gray-700/80 border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        </div>
                    </div>

                    {{-- Row: Address / City / State / Zip --}}
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                        <div class="md:col-span-1">
                            <label class="block text-sm font-semibold mb-3">Address</label>
                            <input name="street" value="{{ old('street', $lead->street) }}"
                                   class="block w-full px-4 py-3 border rounded-xl bg-white/80 dark:bg-gray-700/80 border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold mb-3">City</label>
                            <input name="city" value="{{ old('city', $lead->city) }}"
                                   class="block w-full px-4 py-3 border rounded-xl bg-white/80 dark:bg-gray-700/80 border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold mb-3">State</label>
                            <input name="state_abbreviation" value="{{ old('state_abbreviation', $lead->state_abbreviation) }}" maxlength="5"
                                   class="uppercase block w-full px-4 py-3 border rounded-xl bg-white/80 dark:bg-gray-700/80 border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold mb-3">Zip code</label>
                            <input name="zip_code" value="{{ old('zip_code', $lead->zip_code) }}"
                                   class="block w-full px-4 py-3 border rounded-xl bg-white/80 dark:bg-gray-700/80 border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        </div>
                    </div>

                    {{-- Row: SSN / DOB / MMN / Email --}}
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                        <div>
                            <label class="block text-sm font-semibold mb-3">SSN (000-00-0000)</label>
                            <input name="ssn" value="{{ old('ssn', $lead->ssn) }}" placeholder="000-00-0000"
                                   class="block w-full px-4 py-3 border rounded-xl bg-white/80 dark:bg-gray-700/80 border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold mb-3">DOB (MM-DD-YYYY)</label>
                            <input name="dob" value="{{ old('dob', $lead->dob) }}" placeholder="MM-DD-YYYY"
                                   class="block w-full px-4 py-3 border rounded-xl bg-white/80 dark:bg-gray-700/80 border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold mb-3">MMN</label>
                            <input name="mmn" value="{{ old('mmn', $lead->mmn) }}"
                                   class="block w-full px-4 py-3 border rounded-xl bg-white/80 dark:bg-gray-700/80 border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold mb-3">Email</label>
                            <input type="email" name="email" value="{{ old('email', $lead->email) }}"
                                   class="block w-full px-4 py-3 border rounded-xl bg-white/80 dark:bg-gray-700/80 border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        </div>
                    </div>

                    <div class="h-px w-full bg-gray-200/70 dark:bg-gray-700/70"></div>

                    {{-- Section: Financial Summary --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                        <div>
                            <label class="block text-sm font-semibold mb-3">Credit Score</label>
                            <input type="number" name="fico" value="{{ old('fico', $lead->fico) }}"
                                   class="block w-full px-4 py-3 border rounded-xl bg-white/80 dark:bg-gray-700/80 border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-primary-500 focus:border-primary-500" placeholder="0">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold mb-3">Total Cards</label>
                            <input type="number" name="total_cards" value="{{ old('total_cards', $lead->total_cards) }}"
                                   class="block w-full px-4 py-3 border rounded-xl bg-white/80 dark:bg-gray-700/80 border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-primary-500 focus:border-primary-500" placeholder="0">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold mb-3">Total Debt ($)</label>
                            <input name="total_debt" value="{{ old('total_debt', $lead->total_debt) }}"
                                   class="block w-full px-4 py-3 border rounded-xl bg-white/80 dark:bg-gray-700/80 border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-primary-500 focus:border-primary-500" placeholder="$0.00">
                        </div>
                    </div>

                    <div class="h-px w-full bg-gray-200/70 dark:bg-gray-700/70"></div>

                    <div class="h-px w-full bg-gray-200/70 dark:bg-gray-700/70"></div>

                    {{-- SECTION: Bank Details Repeater --}}
                    <div id="banksContainer" class="space-y-10">
                        @php
                            $banks = old('bank_details', $lead->bank_details ?? []);
                            if (empty($banks)) {
                                $banks = [[]]; // Default to one empty bank if none exist
                            }
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
                        $canReport = auth()->user()->isAdmin() || auth()->user()->isLeadManager() || auth()->user()->isSuperAgent() || auth()->user()->role === 'user' || $lead->assigned_to === auth()->id();
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

                    @if (auth()->user()?->isAdmin() || auth()->user()?->isLeadManager() || auth()->user()?->isSuperAgent() || auth()->user()?->role === 'user' || auth()->user()?->role === 'report_manager')
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

                        @if (auth()->user()?->isAdmin() || auth()->user()?->isSuperAgent() || auth()->user()?->isLeadManager() || auth()->user()?->role === 'report_manager')
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
                        @endif
                    @endif
                </div>

                <div class="grid grid-cols-1 md:grid-cols-5 gap-6 mt-6">
                    <div>
                        <label class="block text-sm font-semibold mb-3">Agent Name</label>
                        <input name="agent_name" value="{{ old('agent_name', $lead->agent_name) }}"
                               class="block w-full px-4 py-3 border rounded-xl bg-white/80 dark:bg-gray-700/80 border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-3">TL Name</label>
                        <input name="tl_name" value="{{ old('tl_name', $lead->tl_name) }}"
                               class="block w-full px-4 py-3 border rounded-xl bg-white/80 dark:bg-gray-700/80 border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-3">Closer Name</label>
                        <input name="closer_name" value="{{ old('closer_name', $lead->closer_name) }}"
                               class="block w-full px-4 py-3 border rounded-xl bg-white/80 dark:bg-gray-700/80 border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-3">Verifier Name</label>
                        <input name="verifier_name" value="{{ old('verifier_name', $lead->verifier_name) }}"
                               class="block w-full px-4 py-3 border rounded-xl bg-white/80 dark:bg-gray-700/80 border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-3">Combined Charge ($)</label>
                        <input name="combined_charge" value="{{ old('combined_charge', $lead->combined_charge) }}" placeholder="$ 0.00"
                               class="block w-full px-4 py-3 border rounded-xl bg-white/80 dark:bg-gray-700/80 border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    </div>
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
                           class="inline-flex items-center px-6 py-3 border border-gray-200/50 dark:border-gray-700/50 text-gray-700 dark:text-gray-300 bg-white/50 dark:bg-gray-800/50 backdrop-blur-xl font-bold rounded-xl transition-all duration-300">
                            Cancel
                        </a>
                        <button type="submit"
                                class="inline-flex items-center px-8 py-3 bg-gradient-to-br from-primary-500 to-primary-600 text-white font-bold rounded-xl shadow-lg hover:shadow-primary-500/25 transform hover:-translate-y-0.5 transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed"
                                :disabled="isSubmitting" :class="{ 'btn-loading': isSubmitting }">
                            <span class="btn-text flex items-center gap-2">
                                <i class="fas fa-save"></i>
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
                if (!wrapper) return;
                
                const typeIcon = wrapper.querySelector('.card-type-icon');
                const validIcon = wrapper.querySelector('.card-valid-icon');
                if (!typeIcon || !validIcon) return;

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
