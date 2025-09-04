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

        /* PDF.js canvas container */
        #pdfViewer {
            max-height: 75vh;
            overflow: auto;
        }

        #pdfViewer canvas {
            display: block;
            margin: 0 auto 12px auto;
            border-radius: 12px;
        }

        /* Plain text viewer */
        #txtViewer {
            max-height: 75vh;
            overflow: auto;
        }

        #txtContent {
            white-space: pre-wrap;
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
                                @php $storageUrl = url('storage/' . ltrim($att->file_path, '/')); @endphp
                                <button type="button"
                                    onclick="openPreview('{{ $storageUrl }}', '{{ e($att->file_name) }}')"
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
                                    <span class="truncate">{{ $att->file_name }}</span>
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Resolution -->
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

                <!-- Solution Files -->
                @if ($issue->solutions && $issue->solutions->count() > 0)
                    <div
                        class="rounded-2xl border border-gray-200/60 dark:border-gray-700/60 bg-white/70 dark:bg-gray-800/50 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Solution Files</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            @foreach ($issue->solutions as $solution)
                                @php $solutionUrl = url('storage/' . ltrim($solution->file_path, '/')); @endphp
                                <button type="button"
                                    onclick="openPreview('{{ $solutionUrl }}', '{{ e($solution->file_name) }}')"
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
                                @csrf @method('PATCH')
                                <div>
                                    <select name="status" id="status-select"
                                        class="w-full px-3 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 bg-white/80 dark:bg-gray-700/80 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                        @foreach (['open', 'triaged', 'in_progress', 'resolved', 'closed'] as $s)
                                            <option value="{{ $s }}" @selected($issue->status === $s)>
                                                {{ ucwords(str_replace('_', ' ', $s)) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div id="resolution-fields" class="space-y-3" style="display:none;">
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
                                @csrf @method('PATCH')
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

                            @if ($comment->attachments && $comment->attachments->count())
                                <div class="mt-3 pt-3 border-t border-gray-200/60 dark:border-gray-700/60">
                                    <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Attachments</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                        @foreach ($comment->attachments as $attachment)
                                            @php $commentAttUrl = url('storage/' . ltrim($attachment->file_path, '/')); @endphp
                                            <button type="button"
                                                onclick="openPreview('{{ $commentAttUrl }}', '{{ e($attachment->file_name) }}')"
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
                        <div class="text-center py-6 text-gray-500 dark:text-gray-400">No comments yet.</div>
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
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Attachments
                                (Optional)</label>
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

    <!-- Preview Modal -->
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
                    <!-- PDF.js canvas viewer (no toolbar, no download) -->
                    <div id="pdfViewer" class="hidden">
                        <div id="pdfViewport"></div>
                    </div>
                    <!-- Plain text viewer -->
                    <div id="txtViewer" class="hidden">
                        <pre id="txtContent" class="text-sm text-gray-800 dark:text-gray-100"></pre>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Lead Details Modal (unchanged pieces omitted for brevity) -->
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
                        {{-- keep your existing lead sections here --}}
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
                            class="px-4 py-2 bg-gray-300 hover:bg-gray-400 dark:bg-gray-600 dark:hover:bg-gray-500 text-gray-700 dark:text-gray-200 rounded-xl transition-colors">Close</button>
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
    {{-- pdf.js (CDN). If you vendor locally, update workerSrc below accordingly. --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.6.347/pdf.min.js"></script>
    <script>
        if (window.pdfjsLib) {
            pdfjsLib.GlobalWorkerOptions.workerSrc =
                "https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.6.347/pdf.worker.min.js";
        }
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const statusSelect = document.getElementById('status-select');
            const resolutionFields = document.getElementById('resolution-fields');
            const commentsList = document.querySelector('.space-y-4');
            const currentUserId = {{ auth()->id() }};

            function toggleResolutionFields() {
                resolutionFields.style.display = (statusSelect && statusSelect.value === 'resolved') ? 'block' :
                    'none';
            }
            toggleResolutionFields();
            statusSelect?.addEventListener('change', toggleResolutionFields);

            if (window.Echo) {
                const issueId = {{ $issue->id }};
                window.Echo.private(`issues.${issueId}`)
                    .listen('.issue.status.updated', (e) => {
                        const statusBadge = document.querySelector(
                            '.inline-flex.items-center.px-3.py-1.rounded-full.text-xs.font-medium.bg-white\\/20:nth-of-type(2) span'
                            );
                        if (statusBadge) statusBadge.textContent = e.status.charAt(0).toUpperCase() + e.status
                            .slice(1).replace('_', ' ');
                    })
                    .listen('.issue.comment.added', (e) => {
                        const noCommentsMessage = commentsList?.querySelector(
                        '.text-center.py-6.text-gray-500');
                        if (noCommentsMessage) noCommentsMessage.remove();
                        const el = createCommentElement(e);
                        commentsList?.appendChild(el);
                    });
            }

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
                if (comment.attachments?.length) {
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

        // ---------- Renderers ----------
        async function renderPdfInto(url) {
            const viewer = document.getElementById('pdfViewer');
            const container = document.getElementById('pdfViewport');
            container.innerHTML = '';

            if (!window.pdfjsLib) {
                container.innerHTML =
                    '<div class="text-center text-sm text-gray-500 dark:text-gray-400">PDF viewer failed to load.</div>';
                return;
            }
            try {
                const resp = await fetch(url, {
                    credentials: 'same-origin',
                    cache: 'no-store'
                });
                if (!resp.ok) throw new Error('HTTP ' + resp.status);
                const buf = await resp.arrayBuffer();
                const pdf = await pdfjsLib.getDocument({
                    data: buf
                }).promise;

                for (let pageNumber = 1; pageNumber <= pdf.numPages; pageNumber++) {
                    const page = await pdf.getPage(pageNumber);
                    const baseViewport = page.getViewport({
                        scale: 1
                    });
                    const targetWidth = Math.min(1400, (viewer.clientWidth || 1000) - 32);
                    const scale = Math.min(2, targetWidth / baseViewport.width);
                    const viewport = page.getViewport({
                        scale
                    });

                    const canvas = document.createElement('canvas');
                    const ctx = canvas.getContext('2d', {
                        alpha: false
                    });
                    canvas.width = Math.floor(viewport.width);
                    canvas.height = Math.floor(viewport.height);
                    container.appendChild(canvas);

                    await page.render({
                        canvasContext: ctx,
                        viewport
                    }).promise;
                }
                viewer.classList.remove('hidden');
            } catch (err) {
                console.error('PDF render error:', err);
                container.innerHTML =
                    '<div class="text-center text-sm text-red-600 dark:text-red-400">Unable to preview this PDF (' + (
                        err?.message || 'unknown error') + ').</div>';
            }
        }

        async function renderTxtInto(url) {
            const viewer = document.getElementById('txtViewer');
            const pre = document.getElementById('txtContent');
            pre.textContent = '';
            try {
                const resp = await fetch(url, {
                    credentials: 'same-origin',
                    cache: 'no-store'
                });
                if (!resp.ok) throw new Error('HTTP ' + resp.status);
                let text = await resp.text();
                const MAX_CHARS = 200000; // safety cap for very large files (~200 KB)
                if (text.length > MAX_CHARS) {
                    text = text.slice(0, MAX_CHARS) + '\n\n… (truncated for preview)';
                }
                pre.textContent = text;
                viewer.classList.remove('hidden');
            } catch (err) {
                pre.textContent = 'Unable to preview this text file (' + (err?.message || 'unknown error') + ').';
                viewer.classList.remove('hidden');
            }
        }

        // ---------- Modal controls ----------
        function openPreview(fileUrl, title) {
            const modal = document.getElementById('previewModal');
            const img = document.getElementById('previewImg');
            const pdfDiv = document.getElementById('pdfViewer');
            const pdfViewport = document.getElementById('pdfViewport');
            const txtDiv = document.getElementById('txtViewer');
            const txtPre = document.getElementById('txtContent');

            document.getElementById('previewTitle').textContent = title || 'Preview';

            // reset
            img.classList.add('hidden');
            img.removeAttribute('src');
            pdfDiv.classList.add('hidden');
            pdfViewport.innerHTML = '';
            txtDiv.classList.add('hidden');
            txtPre.textContent = '';

            const url = fileUrl;
            const base = url.split('?')[0].toLowerCase();
            const isImage = /\.(png|jpe?g|gif|webp|bmp|svg)$/i.test(base);
            const isPdf = /\.pdf$/i.test(base);
            const isTxt = /\.txt$/i.test(base); // preview .txt files

            if (isPdf) {
                renderPdfInto(url); // PDF via pdf.js
            } else if (isImage) {
                img.src = url;
                img.classList.remove('hidden');
            } else if (isTxt) {
                renderTxtInto(url); // Plain text preview
            } else {
                // Unknown: try text, then fallback to pdf
                renderTxtInto(url);
            }

            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closePreview() {
            const m = document.getElementById('previewModal');
            const img = document.getElementById('previewImg');
            const pdfViewport = document.getElementById('pdfViewport');
            const txtPre = document.getElementById('txtContent');
            img.src = '';
            pdfViewport.innerHTML = '';
            txtPre.textContent = '';
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
            if (e.key === 'PrintScreen') e.preventDefault();
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
            if (e.key === 'Escape' && !document.getElementById('leadModal').classList.contains('hidden'))
                closeLeadModal();
        });
    </script>
@endpush
