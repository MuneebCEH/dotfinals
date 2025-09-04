@extends('layouts.app')
@section('title', 'Lead Details')
@section('page-title', 'Lead Details')

@section('content')
    @php
        $card =
            'bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl rounded-2xl shadow-2xl border border-gray-200/50 dark:border-gray-700/50';
        $dt = 'text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400';
        $dd = 'text-[15px] font-medium text-gray-900 dark:text-gray-100';
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
                        <span
                            class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold
                                bg-primary-600/15 text-primary-700 dark:text-primary-400">
                            {{ $lead->status ?: '—' }}
                        </span>

                        @if ($lead->created_at)
                            <span class="text-xs text-gray-500 dark:text-gray-400">• Created
                                {{ $lead->created_at->diffForHumans() }}</span>
                        @endif
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    @if (auth()->user()->isAdmin() || $lead->assigned_to === auth()->id())
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
            {{-- Identity --}}
            <div class="{{ $card }} p-6">
                <h3 class="text-lg font-bold mb-4">Identity</h3>
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
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
                </dl>
            </div>

            {{-- Address --}}
            <div class="{{ $card }} p-6">
                <h3 class="text-lg font-bold mb-4">Address</h3>
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <dt class="{{ $dt }}">Street</dt>
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

            {{-- Contact & Sensitive --}}
            <div class="{{ $card }} p-6">
                <h3 class="text-lg font-bold mb-4">Contact & Sensitive</h3>
                <dl class="space-y-3">
                    <div>
                        <dt class="{{ $dt }} mb-1">SSN</dt>
                        <dd class="{{ $dd }}">
                            @php $ssn = $lead->ssn; @endphp
                            <span x-data="{ show: false }" class="inline-flex items-center gap-2" x-cloak>
                                <span
                                    x-text="show ? '{{ $ssn ?? '—' }}' : '{{ $ssn ? str_repeat('•', max(strlen($ssn) - 4, 0)) . substr($ssn, -4) : '—' }}'"></span>
                                @if ($ssn)
                                    <button type="button"
                                        class="text-xs px-2 py-0.5 rounded-lg border border-gray-300/60 dark:border-gray-600/60"
                                        x-on:click="show = !show" x-text="show ? 'Hide' : 'Show'"></button>
                                @endif
                            </span>
                        </dd>
                    </div>
                    <div>
                        
                        
                    </div>
                </dl>
            </div>

            {{-- Custom & Demographics --}}
            <div class="{{ $card }} p-6">
                <h3 class="text-lg font-bold mb-4">Custom & Demographics</h3>
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <dt class="{{ $dt }}">Age</dt>
                        <dd class="{{ $dd }}">{{ $lead->age ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="{{ $dt }}">XFC06</dt>
                        <dd class="{{ $dd }}">{{ $lead->xfc06 ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="{{ $dt }}">XFC07</dt>
                        <dd class="{{ $dd }}">{{ $lead->xfc07 ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="{{ $dt }}">DEMO7</dt>
                        <dd class="{{ $dd }}">{{ $lead->demo7 ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="{{ $dt }}">DEMO9</dt>
                        <dd class="{{ $dd }}">{{ $lead->demo9 ?? '—' }}</dd>
                    </div>
                </dl>
            </div>

            {{-- Financial --}}
            <div class="{{ $card }} p-6">
                <h3 class="text-lg font-bold mb-4">Financial</h3>
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <dt class="{{ $dt }}">FICO</dt>
                        <dd class="{{ $dd }}">{{ $lead->fico ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="{{ $dt }}">Cards</dt>
                        @if (is_array($lead->cards_json) && count($lead->cards_json) > 0)
                            @php $cards = implode(', ', $lead->cards_json); @endphp
                            <dd class="{{ $dd }}">{{ $cards ?? '—' }}</dd>
                        @else
                            <dd class="{{ $dd }}">—</dd>
                        @endif
                    </div>
                    <div>
                        <dt class="{{ $dt }}">Balance</dt>
                        <dd class="{{ $dd }}">{{ $lead->balance ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="{{ $dt }}">Credits</dt>
                        <dd class="{{ $dd }}">{{ $lead->credits ?? '—' }}</dd>
                    </div>
                </dl>
            </div>

            {{-- Assignment --}}
            <div class="{{ $card }} p-6">
                <h3 class="text-lg font-bold mb-4">Assignment</h3>
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <dt class="{{ $dt }}">TO</dt>
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