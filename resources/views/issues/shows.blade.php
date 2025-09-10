@extends('layouts.app')

@section('title', 'Issue #' . $issue->id)
@section('page-title', 'Issue #' . $issue->id)

@push('styles')
    <style>
        /* Disable text selection and drag in the preview modal */
        #previewModal,
        #previewModal * {
            user-select: none;
            -webkit-user-select: none;
            -webkit-touch-callout: none;
        }

        /* Hide the whole page on print */
        @media print {
            body {
                display: none !important;
            }
        }
    </style>
@endpush

@section('content')
    <div class="mx-auto space-y-6 animate-on-load">

        <!-- Header Card -->
        <div
            class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl shadow-2xl rounded-2xl border border-gray-200/50 dark:border-gray-700/50 overflow-hidden">
            <div class="px-8 py-6 bg-gradient-to-r from-primary-500 to-primary-600 text-white">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h1 class="text-2xl font-bold">{{ $issue->title }}</h1>
                        <div class="mt-1 text-white/90 flex items-center gap-2">
                            <span>
                                Lead #{{ $issue->lead_id }} • Reported by
                                {{ $issue->reporter_id === auth()->id() ? 'You' : $issue->reporter->name ?? 'Unknown' }} •
                                {{ $issue->created_at->diffForHumans() }}
                            </span>

                            <!-- View Lead Details Button -->
                            <button onclick="openLeadModal()"
                                class="ml-4 inline-flex items-center gap-1 px-3 py-1.5 bg-white/20 hover:bg-white/30 rounded-lg text-sm font-medium transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                    </path>
                                </svg>
                                View Lead Details
                            </button>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-white/20">
                            Priority: <span class="ml-1 font-semibold">{{ ucfirst($issue->priority) }}</span>
                        </span>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-white/20">
                            Status: <span
                                class="ml-1 font-semibold">{{ ucwords(str_replace('_', ' ', $issue->status)) }}</span>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Body -->
            <div class="px-8 py-6 space-y-6">
                <!-- Description -->
                <div
                    class="rounded-2xl border border-gray-200/60 dark:border-gray-700/60 bg-white/70 dark:bg-gray-800/50 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Description</h3>
                    <div class="prose dark:prose-invert max-w-none">
                        <p class="whitespace-pre-wrap">{{ $issue->description }}</p>
                    </div>
                </div>

                <!-- Attachments (Preview-only; no direct download links) -->
                @if ($issue->attachments && $issue->attachments->count())
                    <div
                        class="rounded-2xl border border-gray-200/60 dark:border-gray-700/60 bg-white/70 dark:bg-gray-800/50 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Attachments</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            @foreach ($issue->attachments as $att)
                                @php
                                    // Build a clean public storage URL manually
                                    $storageUrl = url('storage/app/public/' . ltrim($att->file_path, '/'));
                                @endphp
                        
                                <button type="button"
                                    onclick="openPreview('{{ $storageUrl }}', '{{ e($att->file_name) }}')"
                                    class="inline-flex items-center gap-2 px-4 py-3 rounded-xl border border-gray-200/70 
                                           dark:border-gray-700/70 hover:bg-gray-50 dark:hover:bg-gray-700/40 transition">
                                    <svg class="w-5 h-5 text-gray-600 dark:text-gray-300" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor">
                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"
                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                        <polyline points="7 10 12 15 17 10" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round" />
                                        <line x1="12" y1="15" x2="12" y2="3"
                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <span class="truncate">{{ $att->file_name }}</span>
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Resolution Information (if resolved) -->
                @if ($issue->status === 'resolved' || $issue->status === 'closed')
                    <div
                        class="rounded-2xl border border-gray-200/60 dark:border-gray-700/60 bg-white/70 dark:bg-gray-800/50 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Resolution</h3>
                        <div class="prose dark:prose-invert max-w-none">
                            <p class="whitespace-pre-wrap">{{ $issue->resolution }}</p>
                        </div>
                        <div class="mt-3 text-sm text-gray-600 dark:text-gray-400">
                            Resolved by
                            {{ ($issue->resolver_id ?? null) === auth()->id() ? 'You' : $issue->resolver->name ?? 'Unknown' }}
                            •
                            {{ $issue->resolved_at ? $issue->resolved_at->diffForHumans() : 'Resolution date not available' }}
                        </div>
                    </div>
                @endif

                <!-- Solution Files (Preview-only) -->
                @if ($issue->solutions && $issue->solutions->count() > 0)
                    <div
                        class="rounded-2xl border border-gray-200/60 dark:border-gray-700/60 bg-white/70 dark:bg-gray-800/50 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Solution Files</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            @foreach ($issue->solutions as $solution)
                                <button type="button"
                                    onclick="openPreview('{{ url('storage/app/public/' . ltrim($solution->file_path, '/')) }}', '{{ e($solution->file_name) }}')"
                                    class="inline-flex items-center gap-2 px-4 py-3 rounded-xl border border-gray-200/70 dark:border-gray-700/70 hover:bg-gray-50 dark:hover:bg-gray-700/40 transition">
                                    <svg class="w-5 h-5 text-gray-600 dark:text-gray-300" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor">
                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round" />
                                        <polyline points="7 10 12 15 17 10" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round" />
                                        <line x1="12" y1="15" x2="12" y2="3" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <span class="truncate">{{ $solution->file_name }}</span>
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Actions -->
                @can('update', $issue)
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Update Status -->
                        <div
                            class="rounded-2xl border border-gray-200/60 dark:border-gray-700/60 bg-white/70 dark:bg-gray-800/50 p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Update Status</h3>
                            <form method="POST" action="{{ route('issues.updateStatus', $issue) }}" class="space-y-4"
                                enctype="multipart/form-data">
                                @csrf
                                @method('PATCH')

                                <div>
                                    <select name="status" id="status-select"
                                        class="w-full px-3 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 bg-white/80 dark:bg-gray-700/80 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                        @foreach (['open', 'triaged', 'in_progress', 'resolved', 'closed'] as $s)
                                            <option value="{{ $s }}" @selected($issue->status === $s)>
                                                {{ ucwords(str_replace('_', ' ', $s)) }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div id="resolution-fields" class="space-y-3" style="display: none;">
                                    <div>
                                        <label for="resolution"
                                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Resolution
                                            Details</label>
                                        <textarea name="resolution" id="resolution" rows="4"
                                            class="w-full px-3 py-2 rounded-xl border border-gray-300 dark:border-gray-600 bg-white/80 dark:bg-gray-700/80 focus:ring-2 focus:ring-primary-500 focus:border-primary-500"></textarea>
                                    </div>
                                </div>

                                <button
                                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-primary-500 to-primary-600 text-white shadow hover:shadow-lg transition">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <path d="M5 13l4 4L19 7" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round" />
                                    </svg>
                                    Save
                                </button>
                            </form>
                        </div>

                        <!-- Update Priority -->
                        <div
                            class="rounded-2xl border border-gray-200/60 dark:border-gray-700/60 bg-white/70 dark:bg-gray-800/50 p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Update Priority</h3>
                            <form method="POST" action="{{ route('issues.updatePriority', $issue) }}"
                                class="flex items-center gap-3">
                                @csrf
                                @method('PATCH')
                                <select name="priority"
                                    class="px-3 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 bg-white/80 dark:bg-gray-700/80 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                    @foreach (['low', 'normal', 'high', 'urgent'] as $p)
                                        <option value="{{ $p }}" @selected($issue->priority === $p)>{{ ucfirst($p) }}
                                        </option>
                                    @endforeach
                                </select>
                                <button
                                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-primary-500 to-primary-600 text-white shadow hover:shadow-lg transition">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <path d="M5 13l4 4L19 7" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round" />
                                    </svg>
                                    Save
                                </button>
                            </form>
                        </div>
                    </div>
                @endcan
            </div>
        </div>

        <!-- Comments Section -->
        <div
            class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl shadow-2xl rounded-2xl border border-gray-200/50 dark:border-gray-700/50 overflow-hidden mt-6">
            <div class="px-8 py-6 space-y-6">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white">Comments</h3>

                <!-- Comment List -->
                <div class="space-y-4">
                    @forelse ($comments as $comment)
                        <div
                            class="rounded-xl border border-gray-200/60 dark:border-gray-700/60 bg-white/70 dark:bg-gray-800/50 p-4">
                            <div class="flex items-start justify-between">
                                <div>
                                    <span
                                        class="font-medium">{{ $comment->author_id === auth()->id() ? 'You' : $comment->author->name ?? 'Unknown' }}</span>
                                    <span
                                        class="text-sm text-gray-500 dark:text-gray-400 ml-2">{{ $comment->created_at->diffForHumans() }}</span>
                                </div>
                            </div>
                            <div class="mt-2 prose dark:prose-invert max-w-none">
                                <p class="whitespace-pre-wrap">{{ $comment->body }}</p>
                            </div>

                            <!-- Comment Attachments (Preview-only) -->
                            @if ($comment->attachments && $comment->attachments->count())
                                <div class="mt-3 pt-3 border-t border-gray-200/60 dark:border-gray-700/60">
                                    <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Attachments</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                        @foreach ($comment->attachments as $attachment)
                                            <button type="button"
                                                onclick="openPreview('{{ url('storage/app/public/' . ltrim($attachment->file_path, '/')) }}', '{{ e($attachment->file_name) }}')"
                                                class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-gray-200/70 dark:border-gray-700/70 hover:bg-gray-50 dark:hover:bg-gray-700/40 transition text-sm">
                                                <svg class="w-4 h-4 text-gray-600 dark:text-gray-300" viewBox="0 0 24 24"
                                                    fill="none" stroke="currentColor">
                                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" stroke-width="2"
                                                        stroke-linecap="round" stroke-linejoin="round" />
                                                    <polyline points="7 10 12 15 17 10" stroke-width="2"
                                                        stroke-linecap="round" stroke-linejoin="round" />
                                                    <line x1="12" y1="15" x2="12" y2="3"
                                                        stroke-width="2" stroke-linecap="round"
                                                        stroke-linejoin="round" />
                                                </svg>
                                                <span class="truncate">{{ $attachment->file_name }}</span>
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="text-center py-6 text-gray-500 dark:text-gray-400">
                            No comments yet.
                        </div>
                    @endforelse
                </div>

                <!-- Add Comment Form -->
                <div
                    class="rounded-xl border border-gray-200/60 dark:border-gray-700/60 bg-white/70 dark:bg-gray-800/50 p-4">
                    <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Add a Comment</h4>
                    <form method="POST" action="{{ route('issues.comments.store', $issue) }}" class="space-y-4"
                        enctype="multipart/form-data">
                        @csrf

                        <div>
                            <textarea name="body" rows="3" required
                                class="w-full px-3 py-2 rounded-xl border border-gray-300 dark:border-gray-600 bg-white/80 dark:bg-gray-700/80 focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                                placeholder="Write your comment here..."></textarea>
                        </div>

                        <div>
                            <label for="comment_attachments"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Attachments (Optional)
                            </label>
                            <input type="file" name="comment_attachments[]" id="comment_attachments" multiple
                                class="w-full px-3 py-2 rounded-xl border border-gray-300 dark:border-gray-600 bg-white/80 dark:bg-gray-700/80 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Upload screenshots or relevant files
                                (max 10MB each)</p>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit"
                                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gradient-to-r from-primary-500 to-primary-600 text-white shadow hover:shadow-lg transition">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                    <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                Post Comment
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Preview Modal (no download UI; disables right-click, print shortcuts, no cache-busting param) -->
    <div id="previewModal" class="fixed inset-0 z-[60] hidden">
        <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" onclick="closePreview()"></div>
        <div class="relative z-10 flex items-center justify-center min-h-screen p-4">
            <div
                class="relative w-full max-w-5xl rounded-2xl bg-white dark:bg-gray-800 shadow-2xl border border-gray-200/50 dark:border-gray-700/50">
                <div class="flex items-center justify-between p-3 border-b border-gray-200/50 dark:border-gray-700/50">
                    <h5 id="previewTitle" class="font-semibold text-gray-900 dark:text-white">Preview</h5>
                    <button onclick="closePreview()"
                        class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg">✕</button>
                </div>
                <div class="p-4">
                    <img id="previewImg" class="max-h-[75vh] mx-auto rounded-xl select-none hidden" draggable="false"
                        oncontextmenu="return false;">
                    <iframe id="previewPdf" class="w-full h-[75vh] rounded-xl hidden"
                        sandbox="allow-scripts allow-same-origin" referrerpolicy="no-referrer">
                    </iframe>
                </div>
            </div>
        </div>
    </div>

    <!-- Lead Details Modal (unchanged UI) -->
    <div id="leadModal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm"></div>
        <div class="relative z-10 flex items-center justify-center min-h-screen p-4">
            <div class="relative w-full max-w-4xl max-h-[90vh] overflow-auto">
                <div
                    class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl border border-gray-200/50 dark:border-gray-700/50">
                    <div
                        class="flex items-center justify-between p-6 border-b border-gray-200/50 dark:border-gray-700/50 bg-gradient-to-r from-primary-500 to-primary-600 text-white rounded-t-2xl">
                        <div>
                            <h2 class="text-xl font-bold">Lead Details #{{ $issue->lead_id }}</h2>
                            <p class="text-white/90 mt-1">Complete lead information</p>
                        </div>
                        <button onclick="closeLeadModal()" class="p-2 hover:bg-white/20 rounded-lg transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <div class="p-6 space-y-6">
                        @php $lead = $issue->lead; @endphp

                        <!-- Identity -->
                        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-4">
                            <h3 class="font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                                <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                Identity
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="text-sm font-medium text-gray-600 dark:text-gray-400">First Name</label>
                                    <p class="text-gray-900 dark:text-white">{{ $lead->first_name ?? '—' }}</p>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Middle
                                        Initial</label>
                                    <p class="text-gray-900 dark:text-white">{{ $lead->middle_initial ?? '—' }}</p>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Surname</label>
                                    <p class="text-gray-900 dark:text-white">{{ $lead->surname ?? '—' }}</p>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Gen Code</label>
                                    <p class="text-gray-900 dark:text-white">{{ $lead->gen_code ?? '—' }}</p>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Age</label>
                                    <p class="text-gray-900 dark:text-white">{{ $lead->age ?? '—' }}</p>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-gray-600 dark:text-gray-400">SSN</label>
                                    <p class="text-gray-900 dark:text-white">{{ $lead->ssn ? '***-**-****' : '—' }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Address -->
                        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-4">
                            <h3 class="font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                                <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z">
                                    </path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                Address
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Street</label>
                                    <p class="text-gray-900 dark:text-white">{{ $lead->street ?? '—' }}</p>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-gray-600 dark:text-gray-400">City</label>
                                    <p class="text-gray-900 dark:text-white">{{ $lead->city ?? '—' }}</p>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-gray-600 dark:text-gray-400">State</label>
                                    <p class="text-gray-900 dark:text-white">{{ $lead->state_abbreviation ?? '—' }}</p>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Zip Code</label>
                                    <p class="text-gray-900 dark:text-white">{{ $lead->zip_code ?? '—' }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Contact -->
                        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-4">
                            <h3 class="font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                                <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 0 0 5.516 5.516l1.13-2.257a1 1 0 0 1 1.21-.502l4.493 1.498a1 1 0 0 1 .684.949V19a2 2 0 0 1-2 2h-1C9.716 21 3 14.284 3 6V5z">
                                    </path>
                                </svg>
                                Contact Information
                            </h3>
                            <div>
                                <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Phone Numbers</label>
                                @php
                                    $numbers = is_array($lead->numbers)
                                        ? $lead->numbers
                                        : (json_decode($lead->numbers ?? '[]', true) ?:
                                        []);
                                @endphp
                                @if (count($numbers) > 0)
                                    <div class="space-y-1 mt-1">
                                        @foreach ($numbers as $number)
                                            <p class="text-gray-900 dark:text-white">{{ $number }}</p>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-gray-900 dark:text-white">—</p>
                                @endif
                            </div>
                        </div>

                        <!-- Financial -->
                        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-4">
                            <h3 class="font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                                <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0 1 18 0z">
                                    </path>
                                </svg>
                                Financial Information
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="text-sm font-medium text-gray-600 dark:text-gray-400">FICO Score</label>
                                    <p class="text-gray-900 dark:text-white">{{ $lead->fico ?? '—' }}</p>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Balance</label>
                                    <p class="text-gray-900 dark:text-white">
                                        {{ $lead->balance ? '$' . number_format($lead->balance / 100, 2) : '—' }}</p>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Credits</label>
                                    <p class="text-gray-900 dark:text-white">
                                        {{ $lead->credits ? '$' . number_format($lead->credits / 100, 2) : '—' }}</p>
                                </div>
                            </div>

                            <div class="mt-4">
                                <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Cards</label>
                                @php
                                    $cards = is_array($lead->cards_json)
                                        ? $lead->cards_json
                                        : (json_decode($lead->cards_json ?? '[]', true) ?:
                                        []);
                                @endphp
                                @if (count($cards) > 0)
                                    <div class="flex flex-wrap gap-2 mt-1">
                                        @foreach ($cards as $card)
                                            @if ($card)
                                                <span
                                                    class="px-2 py-1 bg-primary-100 text-primary-800 dark:bg-primary-900/30 dark:text-primary-300 text-xs rounded-lg">{{ $card }}</span>
                                            @endif
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-gray-900 dark:text-white">—</p>
                                @endif
                            </div>
                        </div>

                        <!-- Custom Fields -->
                        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-4">
                            <h3 class="font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                                <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                    </path>
                                </svg>
                                Custom Fields
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="text-sm font-medium text-gray-600 dark:text-gray-400">XFC06</label>
                                    <p class="text-gray-900 dark:text-white">{{ $lead->xfc06 ?? '—' }}</p>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-gray-600 dark:text-gray-400">XFC07</label>
                                    <p class="text-gray-900 dark:text-white">{{ $lead->xfc07 ?? '—' }}</p>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-gray-600 dark:text-gray-400">DEMO7</label>
                                    <p class="text-gray-900 dark:text-white">{{ $lead->demo7 ?? '—' }}</p>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-gray-600 dark:text-gray-400">DEMO9</label>
                                    <p class="text-gray-900 dark:text-white">{{ $lead->demo9 ?? '—' }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Status & Assignment -->
                        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-4">
                            <h3 class="font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                                <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4">
                                    </path>
                                </svg>
                                Status & Assignment
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Status</label>
                                    <p class="text-gray-900 dark:text-white">
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $lead->status === 'Deal' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                            {{ $lead->status ?? 'Submitted' }}
                                        </span>
                                    </p>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Assigned To</label>
                                    <p class="text-gray-900 dark:text-white">{{ $lead->assignee->name ?? '—' }}</p>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Super Agent</label>
                                    <p class="text-gray-900 dark:text-white">{{ $lead->superAgent->name ?? '—' }}</p>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Closer</label>
                                    <p class="text-gray-900 dark:text-white">{{ $lead->closer->name ?? '—' }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Notes -->
                        @if ($lead->notes)
                            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-4">
                                <h3 class="font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                                    <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                        </path>
                                    </svg>
                                    Notes
                                </h3>
                                <div class="prose dark:prose-invert max-w-none">
                                    <p class="text-gray-900 dark:text-white whitespace-pre-wrap">{{ $lead->notes }}</p>
                                </div>
                            </div>
                        @endif

                        <!-- Timestamps -->
                        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-4">
                            <h3 class="font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                                <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0 1 18 0z"></path>
                                </svg>
                                Timestamps
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Created</label>
                                    <p class="text-gray-900 dark:text-white">
                                        {{ $lead->created_at->format('M d, Y g:i A') }}</p>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Last
                                        Updated</label>
                                    <p class="text-gray-900 dark:text-white">
                                        {{ $lead->updated_at->format('M d, Y g:i A') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div
                        class="flex justify-end gap-3 p-6 border-t border-gray-200/50 dark:border-gray-700/50 bg-gray-50/50 dark:bg-gray-700/20 rounded-b-2xl">
                        <button onclick="closeLeadModal()"
                            class="px-4 py-2 bg-gray-300 hover:bg-gray-400 dark:bg-gray-600 dark:hover:bg-gray-500 text-gray-700 dark:text-gray-200 rounded-xl transition-colors">
                            Close
                        </button>
                        {{-- Download Lead Details stays as-is (separate from attachments policy) --}}
                        <a href="{{ route('leads.pdf', $lead) }}" target="_blank"
                            class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-xl transition-colors inline-flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10 6H6a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-4M14 4h6m0 0v6m0-6L10 14">
                                </path>
                            </svg>
                            Download Lead Details
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const statusSelect = document.getElementById('status-select');
            const resolutionFields = document.getElementById('resolution-fields');
            const commentsList = document.querySelector('.space-y-4');
            const currentUserId = {{ auth()->id() }};

            function toggleResolutionFields() {
                if (statusSelect && statusSelect.value === 'resolved') {
                    resolutionFields.style.display = 'block';
                } else if (resolutionFields) {
                    resolutionFields.style.display = 'none';
                }
            }
            toggleResolutionFields();
            if (statusSelect) statusSelect.addEventListener('change', toggleResolutionFields);

            // Optional real-time listeners (no-op if Echo not present)
            if (window.Echo) {
                const issueId = {{ $issue->id }};
                window.Echo.private(`issues.${issueId}`)
                    .listen('.issue.status.updated', (e) => {
                        const statusBadge = document.querySelector(
                            '.inline-flex.items-center.px-3.py-1.rounded-full.text-xs.font-medium.bg-white\\/20:nth-of-type(2) span'
                        );
                        if (statusBadge) {
                            statusBadge.textContent = e.status.charAt(0).toUpperCase() + e.status.slice(1)
                                .replace('_', ' ');
                        }
                    })
                    .listen('.issue.comment.added', (e) => {
                        const noCommentsMessage = commentsList?.querySelector(
                            '.text-center.py-6.text-gray-500');
                        if (noCommentsMessage) noCommentsMessage.remove();
                        const el = createCommentElement(e);
                        commentsList?.appendChild(el);
                    });
            }

            // Build a new comment element (used by Echo)
            function createCommentElement(comment) {
                const wrap = document.createElement('div');
                wrap.className =
                    'rounded-xl border border-gray-200/60 dark:border-gray-700/60 bg-white/70 dark:bg-gray-800/50 p-4';
                const authorName = comment.user?.id === currentUserId ? 'You' : (comment.user?.name || 'Unknown');
                let html = `
        <div class="flex items-start justify-between">
          <div>
            <span class="font-medium">${authorName}</span>
            <span class="text-sm text-gray-500 dark:text-gray-400 ml-2">Just now</span>
          </div>
        </div>
        <div class="mt-2 prose dark:prose-invert max-w-none">
          <p class="whitespace-pre-wrap">${comment.body || ''}</p>
        </div>
      `;
                if (comment.attachments && comment.attachments.length > 0) {
                    html += `
          <div class="mt-3 pt-3 border-t border-gray-200/60 dark:border-gray-700/60">
            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Attachments</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
        `;
                    comment.attachments.forEach(a => {
                        html += `
            <button type="button"
              onclick="openPreview('${a.preview_route}', '${(a.file_name || '').replace(/'/g, "\\'")}')"
              class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-gray-200/70 dark:border-gray-700/70 hover:bg-gray-50 dark:hover:bg-gray-700/40 transition text-sm">
              <svg class="w-4 h-4 text-gray-600 dark:text-gray-300" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <polyline points="7 10 12 15 17 10" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <line x1="12" y1="15" x2="12" y2="3" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
              <span class="truncate">${a.file_name || 'Attachment'}</span>
            </button>
          `;
                    });
                    html += `</div></div>`;
                }
                wrap.innerHTML = html;
                return wrap;
            }
        });

        // Preview modal helpers
        function openPreview(fileUrl, title) {
            const modal = document.getElementById('previewModal');
            const img   = document.getElementById('previewImg');
            const pdf   = document.getElementById('previewPdf');
        
            document.getElementById('previewTitle').textContent = title || 'Preview';
        
            // reset
            img.classList.add('hidden');  img.removeAttribute('src');
            pdf.classList.add('hidden');  pdf.removeAttribute('src');
        
            // use URL as-is (already absolute and un-encoded by Blade)
            const url = fileUrl;
            const base = url.split('?')[0].toLowerCase();
        
            const isImage = /\.(png|jpe?g|gif|webp|bmp|svg)$/i.test(base);
            const isPdf   = /\.pdf$/i.test(base);
        
            if (isPdf) {
              // load Chrome's PDF viewer (no sandbox, or Chrome will block)
              pdf.removeAttribute('sandbox');
              pdf.setAttribute('allow', 'clipboard-read; clipboard-write');
              pdf.src = url;                 // e.g. /storage/app/public/issue-attachments/xxx.pdf
              pdf.classList.remove('hidden');
            } else if (isImage) {
              img.src = url;                 // e.g. /storage/app/public/issue-attachments/xxx.png
              img.classList.remove('hidden');
            } else {
              // Fallback: just try to show in iframe
              pdf.removeAttribute('sandbox');
              pdf.src = url;
              pdf.classList.remove('hidden');
            }
        
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
          }


        function closePreview() {
            const m = document.getElementById('previewModal');
            const img = document.getElementById('previewImg');
            const pdf = document.getElementById('previewPdf');
            img.src = '';
            pdf.src = '';
            m.classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        // Block right-click and common save/print keys inside preview
        document.addEventListener('contextmenu', e => {
            if (e.target.closest('#previewModal')) e.preventDefault();
        }, {
            capture: true
        });
        document.addEventListener('keydown', e => {
            const open = !document.getElementById('previewModal').classList.contains('hidden');
            if (!open) return;
            const k = e.key.toLowerCase();
            if ((e.ctrlKey || e.metaKey) && ['s', 'p', 'c', 'x', 'v'].includes(k)) e.preventDefault();
            if (e.key === 'PrintScreen') {
                e.preventDefault();
            }
        });

        // Lead modal helpers
        function openLeadModal() {
            document.getElementById('leadModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeLeadModal() {
            document.getElementById('leadModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }
        document.getElementById('leadModal')?.addEventListener('click', function(e) {
            if (e.target === this) closeLeadModal();
        });
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && !document.getElementById('leadModal').classList.contains('hidden')) {
                closeLeadModal();
            }
        });
    </script>
@endpush
