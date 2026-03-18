@extends('layouts.app')
@section('title', 'Lead Details')
@section('page-title', 'Lead Details')

@section('content')
    @php
        $card = 'card-premium rounded-2xl p-6 border border-gray-200/50 dark:border-gray-700/50';
        $dt = 'text-[10px] font-bold uppercase tracking-widest text-gray-500 dark:text-gray-400 mb-1';
        $dd = 'text-base font-semibold text-gray-900 dark:text-white';
        $hasCloserColumn = \Illuminate\Support\Facades\Schema::hasColumn('leads', 'closer_id');
    @endphp

    <div class="space-y-8">
        {{-- Header --}}
        <div class="{{ $card }} p-6 lg:p-8">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h1 class="text-2xl lg:text-3xl font-bold text-gray-900 dark:text-white">
                        {{ trim($lead->first_name . ' ' . $lead->surname) ?: '—' }}
                    </h1>
                    <div class="mt-2 flex items-center gap-2">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold
                                    bg-primary-600/15 text-primary-700 dark:text-primary-400">
                            {{ $lead->status ?: '—' }}
                        </span>

                        @if ($lead->created_at)
                            <span class="text-xs text-gray-500 dark:text-gray-400">• Created
                                {{ $lead->created_at->diffForHumans() }}</span>
                        @endif
                    </div>
                </div>

                <div class="flex items-center gap-3"
                     x-data="{
                        issueOpen: false,
                        isSubmitting: false
                     }"
                     @keydown.escape.window="issueOpen=false">
                    @php
                        $canReport = auth()->user()->isAdmin() || auth()->user()->isLeadManager() || auth()->user()->isSuperAgent();
                        $hasExistingIssue = $lead->issues()->exists();
                    @endphp

                    @if ($canReport && !$hasExistingIssue)
                        <button type="button" @click="issueOpen = true"
                                class="px-4 py-2 rounded-xl bg-gradient-to-r from-red-500 to-red-600 text-white shadow-lg hover:shadow-xl hover:-translate-y-0.5 transition flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Request Report
                        </button>

                        {{-- Modal teleported --}}
                        <template x-teleport="body">
                            <div x-cloak x-show="issueOpen" x-transition.opacity
                                 class="fixed inset-0 z-[100] flex items-center justify-center p-4">
                                <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="issueOpen=false"></div>
                                <div role="dialog" aria-modal="true" class="relative w-full max-w-2xl max-h-[90vh] overflow-hidden rounded-2xl border border-gray-200/50 dark:border-gray-700/50 bg-white/90 dark:bg-gray-800/90 shadow-2xl">
                                    <div class="px-6 py-4 bg-gradient-to-r from-primary-500 to-primary-600 text-white flex items-center justify-between">
                                        <h3 class="text-lg font-semibold">Report a Request</h3>
                                        <button @click="issueOpen=false" class="p-1 rounded hover:bg-white/20"><i class="fas fa-times"></i></button>
                                    </div>
                                    <form method="POST" action="{{ route('leads.issues.store', $lead) }}" class="p-6 space-y-4 overflow-y-auto" style="max-height: calc(90vh - 140px);">
                                        @csrf
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Title</label>
                                            <input name="title" required maxlength="160" class="mt-1 w-full px-4 py-2 rounded-xl border border-gray-300 dark:border-gray-600 bg-white/80 dark:bg-gray-700/80">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Priority</label>
                                            <select name="priority" class="mt-1 w-full px-4 py-2 rounded-xl border border-gray-300 dark:border-gray-600 bg-white/80 dark:bg-gray-700/80">
                                                <option value="normal" selected>Normal</option>
                                                <option value="low">Low</option>
                                                <option value="high">High</option>
                                                <option value="urgent">Urgent</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Description</label>
                                            <textarea name="description" required maxlength="5000" rows="5" class="mt-1 w-full px-4 py-2 rounded-xl border border-gray-300 dark:border-gray-600 bg-white/80 dark:bg-gray-700/80"></textarea>
                                        </div>
                                        <div class="flex justify-end gap-2 pt-4">
                                            <button type="button" @click="issueOpen=false" class="px-4 py-2 rounded-xl border border-gray-300 dark:border-gray-600 bg-white/80 dark:bg-gray-700/80 hover:bg-gray-50 dark:hover:bg-gray-700 transition">Cancel</button>
                                            <button type="submit" class="px-4 py-2 rounded-xl bg-gradient-to-r from-primary-500 to-primary-600 text-white shadow hover:shadow-md transition">Submit Request</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </template>
                    @endif

                    @if (auth()->user()->isAdmin() || auth()->user()->isLeadManager() || auth()->user()->isReportManager() || ($lead->assigned_to === auth()->id() && auth()->user()->role !== 'user'))
                        <a href="{{ route('leads.edit', $lead) }}"
                            class="px-4 py-2 rounded-xl bg-warning-600 text-white hover:opacity-90 transition">Edit</a>
                    @endif
                    @if (auth()->user()->isAdmin())
                        <form method="POST" action="{{ route('leads.destroy', $lead) }}"
                            onsubmit="return confirm('Delete this lead?')">
                            @csrf @method('DELETE')
                            <button
                                class="px-4 py-2 rounded-xl bg-danger-600 text-white hover:opacity-90 transition">Delete</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        {{-- Content Grid --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            {{-- Personal Information --}}
            <div class="{{ $card }}">
                <h3 class="text-lg font-bold mb-6 flex items-center gap-2">
                    <span class="w-2 h-6 bg-primary-600 rounded-full"></span>
                    Personal Information
                </h3>
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <dt class="{{ $dt }}">First Name</dt>
                        <dd class="{{ $dd }}">{{ $lead->first_name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="{{ $dt }}">Middle Initial</dt>
                        <dd class="{{ $dd }}">{{ $lead->middle_initial ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="{{ $dt }}">Surname</dt>
                        <dd class="{{ $dd }}">{{ $lead->surname ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="{{ $dt }}">Gen Code</dt>
                        <dd class="{{ $dd }}">{{ $lead->gen_code ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="{{ $dt }}">SSN</dt>
                        <dd class="{{ $dd }}">
                            @php $ssn = $lead->ssn; @endphp
                            <span x-data="{ show: false }" class="inline-flex items-center gap-2" x-cloak>
                                <span x-text="show ? '{{ $ssn ?? '—' }}' : '{{ $ssn ? str_repeat('•', max(strlen($ssn) - 4, 0)) . substr($ssn, -4) : '—' }}'"></span>
                                @if ($ssn)
                                    <button type="button" class="text-[10px] px-2 py-0.5 rounded border border-gray-300 dark:border-gray-600"
                                            x-on:click="show = !show" x-text="show ? 'Hide' : 'Show'"></button>
                                @endif
                            </span>
                        </dd>
                    </div>
                    <div>
                        <dt class="{{ $dt }}">DOB</dt>
                        <dd class="{{ $dd }}">{{ $lead->dob ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="{{ $dt }}">MMN</dt>
                        <dd class="{{ $dd }}">{{ $lead->mmn ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="{{ $dt }}">Age</dt>
                        <dd class="{{ $dd }}">{{ $lead->age ?? '—' }}</dd>
                    </div>
                </dl>
            </div>

            {{-- Contact Information --}}
            <div class="{{ $card }}">
                <h3 class="text-lg font-bold mb-6 flex items-center gap-2">
                    <span class="w-2 h-6 bg-green-600 rounded-full"></span>
                    Contact Information
                </h3>
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <dt class="{{ $dt }}">Email Address</dt>
                        <dd class="{{ $dd }}">{{ $lead->email ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="{{ $dt }}">Primary Phone</dt>
                        @php
                            $numbers = is_array($lead->numbers) ? $lead->numbers : json_decode($lead->numbers ?? '[]', true);
                        @endphp
                        <dd class="{{ $dd }}">{{ $numbers[0] ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="{{ $dt }}">Cell Phone</dt>
                        <dd class="{{ $dd }}">{{ $lead->cell ?? '—' }}</dd>
                    </div>
                    <div class="md:col-span-2">
                        <dt class="{{ $dt }}">Additional Numbers</dt>
                        <dd class="{{ $dd }}">
                            @if(count($numbers ?? []) > 1)
                                <div class="flex flex-wrap gap-2 mt-1">
                                    @foreach(array_slice($numbers, 1) as $num)
                                        <span class="px-2 py-1 bg-gray-100 dark:bg-gray-800 rounded text-sm">{{ $num }}</span>
                                    @endforeach
                                </div>
                            @else
                                —
                            @endif
                        </dd>
                    </div>
                </dl>
            </div>

            {{-- Address Information --}}
            <div class="{{ $card }}">
                <h3 class="text-lg font-bold mb-6 flex items-center gap-2">
                    <span class="w-2 h-6 bg-blue-600 rounded-full"></span>
                    Address Details
                </h3>
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <dt class="{{ $dt }}">Street Address</dt>
                        <dd class="{{ $dd }}">{{ $lead->street ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="{{ $dt }}">City</dt>
                        <dd class="{{ $dd }}">{{ $lead->city ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="{{ $dt }}">State</dt>
                        <dd class="{{ $dd }}">{{ $lead->state_abbreviation ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="{{ $dt }}">Zip Code</dt>
                        <dd class="{{ $dd }}">{{ $lead->zip_code ?? '—' }}</dd>
                    </div>
                </dl>
            </div>

            {{-- Financial Overview --}}
            <div class="{{ $card }}">
                <h3 class="text-lg font-bold mb-6 flex items-center gap-2">
                    <span class="w-2 h-6 bg-purple-600 rounded-full"></span>
                    Financial Summary
                </h3>
                <dl class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <dt class="{{ $dt }}">Credit Score</dt>
                        <dd class="{{ $dd }}">{{ $lead->fico ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="{{ $dt }}">Total Cards</dt>
                        <dd class="{{ $dd }}">{{ $lead->total_cards ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="{{ $dt }}">Total Debt</dt>
                        <dd class="{{ $dd }}">{{ $lead->total_debt ? '$' . number_format($lead->total_debt, 2) : '—' }}</dd>
                    </div>
                </dl>
            </div>

            {{-- Bank & Card Details --}}
            <div class="lg:col-span-2 {{ $card }}">
                <h3 class="text-lg font-bold mb-6 flex items-center gap-2">
                    <span class="w-2 h-6 bg-amber-600 rounded-full"></span>
                    Bank & Card Information
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <dl class="space-y-4">
                        <div>
                            <dt class="{{ $dt }}">Bank Name</dt>
                            <dd class="{{ $dd }}">{{ $lead->bank_name ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="{{ $dt }}">Name on Card</dt>
                            <dd class="{{ $dd }}">{{ $lead->name_on_card ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="{{ $dt }}">Card Number</dt>
                            <dd class="{{ $dd }}">{{ $lead->card_number ?? '—' }}</dd>
                        </div>
                    </dl>
                    <dl class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <dt class="{{ $dt }}">Exp Date</dt>
                                <dd class="{{ $dd }}">{{ $lead->exp_date ?? '—' }}</dd>
                            </div>
                            <div>
                                <dt class="{{ $dt }}">CVC</dt>
                                <dd class="{{ $dd }}">{{ $lead->cvc ?? '—' }}</dd>
                            </div>
                        </div>
                        <div>
                            <dt class="{{ $dt }}">Balance</dt>
                            <dd class="{{ $dd }}">{{ $lead->balance ? '$' . number_format($lead->balance, 2) : '—' }}</dd>
                        </div>
                        <div>
                            <dt class="{{ $dt }}">Available</dt>
                            <dd class="{{ $dd }}">{{ $lead->available ? '$' . number_format($lead->available, 2) : '—' }}</dd>
                        </div>
                    </dl>
                    <dl class="space-y-4">
                        <div>
                            <dt class="{{ $dt }}">Last Payment</dt>
                            <dd class="{{ $dd }}">
                                @if($lead->last_payment_amount)
                                    ${{ number_format($lead->last_payment_amount, 2) }} <span class="text-xs text-gray-500 font-normal">on {{ $lead->last_payment_date }}</span>
                                @else
                                    —
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="{{ $dt }}">Next Payment</dt>
                            <dd class="{{ $dd }}">
                                @if($lead->next_payment_amount)
                                    ${{ number_format($lead->next_payment_amount, 2) }} <span class="text-xs text-gray-500 font-normal">on {{ $lead->next_payment_date }}</span>
                                @else
                                    —
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="{{ $dt }}">Credit Limit</dt>
                            <dd class="{{ $dd }}">{{ $lead->credit_limit ? '$' . number_format($lead->credit_limit, 2) : '—' }}</dd>
                        </div>
                    </dl>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mt-6 pt-6 border-t border-gray-100 dark:border-gray-800">
                    <div>
                        <dt class="{{ $dt }}">APR</dt>
                        <dd class="{{ $dd }}">{{ $lead->apr ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="{{ $dt }}">Monthly Charge</dt>
                        <dd class="{{ $dd }}">{{ $lead->charge ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="{{ $dt }}">Tollfree Number</dt>
                        <dd class="{{ $dd }}">{{ $lead->tollfree ?? '—' }}</dd>
                    </div>
                </div>
            </div>

            {{-- Assignment Info --}}
            <div class="lg:col-span-2 {{ $card }}">
                <h3 class="text-lg font-bold mb-6 flex items-center gap-2">
                    <span class="w-2 h-6 bg-gray-600 rounded-full"></span>
                    System & Assignment
                </h3>
                <dl class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div>
                        <dt class="{{ $dt }}">Assigned TO</dt>
                        <dd class="{{ $dd }}">{{ optional($lead->assignee)->name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="{{ $dt }}">Super Agent</dt>
                        <dd class="{{ $dd }}">{{ optional($lead->superAgent)->name ?? '—' }}</dd>
                    </div>
                    @if ($hasCloserColumn)
                    <div>
                        <dt class="{{ $dt }}">Closer</dt>
                        <dd class="{{ $dd }}">{{ optional($lead->closer)->name ?? '—' }}</dd>
                    </div>
                    @endif
                    <div>
                        <dt class="{{ $dt }}">Created By</dt>
                        <dd class="{{ $dd }}">{{ optional($lead->creator)->name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="{{ $dt }}">Agent Name</dt>
                        <dd class="{{ $dd }}">{{ $lead->agent_name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="{{ $dt }}">TL Name</dt>
                        <dd class="{{ $dd }}">{{ $lead->tl_name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="{{ $dt }}">Closer Name</dt>
                        <dd class="{{ $dd }}">{{ $lead->closer_name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="{{ $dt }}">Verifier Name</dt>
                        <dd class="{{ $dd }}">{{ $lead->verifier_name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="{{ $dt }}">Combined Charge</dt>
                        <dd class="{{ $dd }}">{{ $lead->combined_charge ?? '—' }}</dd>
                    </div>
                </dl>
            </div>

            {{-- PDF Document (Full width below) --}}
            <div class="lg:col-span-2 {{ $card }} p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold">Uploaded Document</h3>
                    @if ($lead->lead_pdf_path)
                        <div class="flex items-center gap-2">
                            <a href="{{ Storage::url($lead->lead_pdf_path) }}" target="_blank"
                                class="px-3 py-2 rounded-lg bg-primary-600 text-white hover:bg-primary-700 transition">
                                View / Download
                            </a>
                            <a href="{{ route('leads.pdf', $lead) }}"
                                class="px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                Force Download
                            </a>
                        </div>
                    @endif
                </div>

                @if ($lead->lead_pdf_path)
                    <div class="rounded-xl overflow-hidden border border-gray-200/70 dark:border-gray-700/70">
                        <iframe src="{{ Storage::url($lead->lead_pdf_path) }}" class="w-full h-[70vh] min-h-[420px]"
                            frameborder="0" loading="lazy">
                        </iframe>
                    </div>
                @else
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                        No document uploaded for this lead.
                    </div>
                @endif
            </div>
        </div>
    </div>

    @push('scripts')
        {{-- Alpine for SSN toggle (skip if already loaded globally) --}}
        <script src="//unpkg.com/alpinejs" defer></script>
    @endpush
@endsection