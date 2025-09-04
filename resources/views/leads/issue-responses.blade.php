@extends('layouts.app')

@section('title', 'Issue Responses')
@section('page-title', 'Issue Responses')

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

        <div
            class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl shadow-2xl rounded-2xl border border-gray-200/50 dark:border-gray-700/50 overflow-hidden">
            <div class="px-8 py-6 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div
                        class="w-12 h-12 rounded-2xl bg-gradient-to-br from-primary-500 to-primary-600 flex items-center justify-center shadow-lg">
                        <svg class="w-7 h-7 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $issue->title }}</h1>
                        <p class="text-gray-600 dark:text-gray-400">
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $issue->priority === 'high' ? 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300' : ($issue->priority === 'medium' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300' : 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300') }}">
                                {{ ucfirst($issue->priority) }} Priority
                            </span>
                            <span
                                class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $issue->status === 'open' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300' : ($issue->status === 'in_progress' ? 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300' : 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300') }}">
                                {{ ucfirst(str_replace('_', ' ', $issue->status)) }}
                            </span>
                            <span class="ml-2 text-sm">Reported {{ $issue->created_at->diffForHumans() }}</span>
                        </p>
                    </div>
                </div>
                <a href="{{ route('leads.edit', $issue->lead_id) }}"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 bg-white/80 dark:bg-gray-700/80 backdrop-blur-xl font-medium rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to Lead
                </a>
            </div>

            <div class="px-8 pb-6 space-y-6">
                <!-- Issue Description -->
                <div
                    class="p-4 rounded-xl bg-gray-50/70 dark:bg-gray-700/50 border border-gray-200/70 dark:border-gray-600/50">
                    <h3 class="font-semibold text-gray-900 dark:text-white mb-2">Description</h3>
                    <div class="text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ $issue->description }}</div>
                </div>

                <!-- Issue Attachments (Preview-only) -->
                @if (isset($issue) &&
                        $issue->attachments &&
                        $issue->attachments->where('is_solution', false)->where('issue_comment_id', null)->count() > 0)
                    <div>
                        <h3 class="font-semibold text-gray-900 dark:text-white mb-2">Attachments</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                            @foreach ($issue->attachments->where('is_solution', false)->where('issue_comment_id', null) as $attachment)
                                @php $attUrl = url( 'storage/app/public/' . $attachment->file_path); @endphp
                                <button type="button"
                                    onclick="openPreview('{{ $attUrl }}', '{{ e($attachment->file_name) }}')"
                                    class="flex items-center w-full text-left p-3 rounded-lg border border-gray-200/70 dark:border-gray-600/50 bg-white/70 dark:bg-gray-700/50 hover:bg-gray-50 dark:hover:bg-gray-600/50 transition">
                                    <div
                                        class="shrink-0 w-10 h-10 rounded-lg bg-gray-100 dark:bg-gray-600 flex items-center justify-center mr-3">
                                        <svg class="w-6 h-6 text-gray-500 dark:text-gray-400" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                            {{ $attachment->file_name }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ number_format($attachment->file_size / 1024, 1) }} KB</p>
                                    </div>
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Resolution Information (if resolved) -->
                @if ($issue->status === 'resolved')
                    <div
                        class="p-4 rounded-xl bg-green-50/70 dark:bg-green-900/20 border border-green-200/70 dark:border-green-800/50">
                        <h3 class="font-semibold text-gray-900 dark:text-white mb-2">Resolution</h3>
                        <div class="text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ $issue->resolution }}</div>
                    </div>
                @endif

                <!-- Comments Section -->
                <div class="mt-8">
                    <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Comments</h3>

                    <div id="commentsList" class="space-y-4">
                        @forelse($issue->comments as $comment)
                            <div
                                class="p-4 rounded-xl bg-white/70 dark:bg-gray-700/50 border border-gray-200/70 dark:border-gray-600/50">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center">
                                        <div
                                            class="w-8 h-8 rounded-full bg-gray-200 dark:bg-gray-600 flex items-center justify-center mr-2">
                                            <span
                                                class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ substr($comment->author->name, 0, 1) }}</span>
                                        </div>
                                        <div>
                                            <span
                                                class="font-medium text-gray-900 dark:text-white">{{ $comment->author->name }}</span>
                                            @if (in_array($comment->author->role, ['admin', 'report_manager']))
                                                <span
                                                    class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300">
                                                    {{ $comment->author->role === 'admin' ? 'Admin' : 'Report Manager' }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    <span
                                        class="text-sm text-gray-500 dark:text-gray-400">{{ $comment->created_at->diffForHumans() }}</span>
                                </div>
                                <div class="text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ $comment->body }}
                                </div>

                                <!-- Comment Attachments (Preview-only) -->
                                @if ($comment->attachments->count() > 0)
                                    <div class="mt-3 pt-3 border-t border-gray-200/70 dark:border-gray-600/50">
                                        <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-2">Attachments</h4>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                            @foreach ($comment->attachments as $attachment)
                                                @php $commentAttUrl = url('storage/app/public/' . $attachment->file_path); @endphp
                                                <button type="button"
                                                    onclick="openPreview('{{ $commentAttUrl }}', '{{ e($attachment->file_name) }}')"
                                                    class="flex items-center w-full text-left p-2 rounded-lg border border-gray-200/70 dark:border-gray-600/50 bg-gray-50/70 dark:bg-gray-600/50 hover:bg-gray-100 dark:hover:bg-gray-500/50 transition">
                                                    <div
                                                        class="shrink-0 w-8 h-8 rounded-lg bg-gray-100 dark:bg-gray-500 flex items-center justify-center mr-2">
                                                        <svg class="w-5 h-5 text-gray-500 dark:text-gray-300" fill="none"
                                                            stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                        </svg>
                                                    </div>
                                                    <div class="min-w-0 flex-1">
                                                        <p
                                                            class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                                            {{ $attachment->file_name }}</p>
                                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                                            {{ number_format($attachment->file_size / 1024, 1) }} KB</p>
                                                    </div>
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @empty
                            <div id="noComments" class="text-center py-8 text-gray-500 dark:text-gray-400">No comments yet.
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
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Upload screenshots or relevant
                                    files (max 10MB each)</p>
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
    </div>

    <!-- Preview Modal (no download UI) -->
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
                    <div id="pdfViewer" class="hidden">
                        <div id="pdfViewport"></div>
                    </div>
                    <div id="txtViewer" class="hidden">
                        <pre id="txtContent" class="text-sm text-gray-800 dark:text-gray-100"></pre>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    {{-- pdf.js via CDN --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.6.347/pdf.min.js"></script>
    <script>
        if (window.pdfjsLib) {
            pdfjsLib.GlobalWorkerOptions.workerSrc =
                "https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.6.347/pdf.worker.min.js";
        }
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (window.Echo) {
                window.Echo.private('issue.{{ $issue->id }}')
                    .listen('.issue.status.updated', () => {
                        window.location.reload();
                    })
                    .listen('.issue.comment.added', (e) => {
                        const noCommentsEl = document.getElementById('noComments');
                        if (noCommentsEl) noCommentsEl.remove();
                        const commentsList = document.getElementById('commentsList');
                        const commentEl = createCommentElement(e);
                        commentsList.insertAdjacentHTML('beforeend', commentEl);
                    });
            }

            function createCommentElement(data) {
                const roleTag = (data.author_role === 'admin' || data.author_role === 'report_manager') ?
                    `<span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300">
                         ${data.author_role === 'admin' ? 'Admin' : 'Report Manager'}
                       </span>` : '';

                const attachmentsHtml = (data.attachments && data.attachments.length) ?
                    `
                        <div class="mt-3 pt-3 border-t border-gray-200/70 dark:border-gray-600/50">
                          <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-2">Attachments</h4>
                          <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            ${data.attachments.map(att => `
                                              <button type="button"
                                                      onclick="openPreview('${att.url}', '${(att.file_name || '').replace(/'/g, "\\'")}')"
                                                      class="flex items-center w-full text-left p-2 rounded-lg border border-gray-200/70 dark:border-gray-600/50 bg-gray-50/70 dark:bg-gray-600/50 hover:bg-gray-100 dark:hover:bg-gray-500/50 transition">
                                                <div class="shrink-0 w-8 h-8 rounded-lg bg-gray-100 dark:bg-gray-500 flex items-center justify-center mr-2">
                                                  <svg class="w-5 h-5 text-gray-500 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                  </svg>
                                                </div>
                                                <div class="min-w-0 flex-1">
                                                  <p class="text-sm font-medium text-gray-900 dark:text-white truncate">${att.file_name}</p>
                                                  <p class="text-xs text-gray-500 dark:text-gray-400">${(att.file_size/1024).toFixed(1)} KB</p>
                                                </div>
                                              </button>
                                            `).join('')}
                          </div>
                        </div>
                      ` :
                    '';

                return `
                    <div class="p-4 rounded-xl bg-white/70 dark:bg-gray-700/50 border border-gray-200/70 dark:border-gray-600/50">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center">
                                <div class="w-8 h-8 rounded-full bg-gray-200 dark:bg-gray-600 flex items-center justify-center mr-2">
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">${data.author_name.charAt(0)}</span>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-900 dark:text-white">${data.author_name}</span>
                                    ${roleTag}
                                </div>
                            </div>
                            <span class="text-sm text-gray-500 dark:text-gray-400">Just now</span>
                        </div>
                        <div class="text-gray-700 dark:text-gray-300 whitespace-pre-wrap">${data.body}</div>
                        ${attachmentsHtml}
                    </div>
                `;
            }
        });

        // ----------- Preview helpers -----------
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

                for (let i = 1; i <= pdf.numPages; i++) {
                    const page = await pdf.getPage(i);
                    const baseViewport = page.getViewport({
                        scale: 1
                    });
                    const targetWidth = Math.min(1400, (document.getElementById('pdfViewer').clientWidth || 1000) - 32);
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
                const MAX_CHARS = 200000; // ~200 KB safety cap
                if (text.length > MAX_CHARS) text = text.slice(0, MAX_CHARS) + '\n\n… (truncated for preview)';
                pre.textContent = text;
                viewer.classList.remove('hidden');
            } catch (err) {
                pre.textContent = 'Unable to preview this text file (' + (err?.message || 'unknown error') + ').';
                viewer.classList.remove('hidden');
            }
        }

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
            const isTxt = /\.txt$/i.test(base);

            if (isPdf) renderPdfInto(url);
            else if (isImage) {
                img.src = url;
                img.classList.remove('hidden');
            } else if (isTxt) renderTxtInto(url);
            else renderTxtInto(url); // fallback

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

        // ---------- GLOBAL "CONFIDENTIAL PAGE" GUARDS ----------
        // NOTE: These are client-side deterrents only; they can't fully stop
        // determined users from saving, but they remove common save paths.
        (function() {
            // Block right-click everywhere on this page
            document.addEventListener('contextmenu', function(e) {
                e.preventDefault();
            }, {
                capture: true
            });

            // Block Ctrl/Cmd+S (Save), Ctrl/Cmd+P (Print), Ctrl/Cmd+U (View Source), and PrintScreen
            document.addEventListener('keydown', function(e) {
                const k = (e.key || '').toLowerCase();
                const mod = e.ctrlKey || e.metaKey;
                if (mod && (k === 's' || k === 'p' || k === 'u')) {
                    e.preventDefault();
                    e.stopPropagation();
                    return false;
                }
                if (e.key === 'PrintScreen') {
                    e.preventDefault();
                    e.stopPropagation();
                    return false;
                }
            }, {
                capture: true
            });

            // Prevent drag-save of assets
            document.addEventListener('dragstart', function(e) {
                e.preventDefault();
            }, {
                capture: true
            });

            // Make images non-draggable and block their context menu individually
            document.querySelectorAll('img').forEach(el => {
                el.setAttribute('draggable', 'false');
                el.setAttribute('oncontextmenu', 'return false');
            });
        })();

        // Keep the stricter modal-only guards too
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
    </script>
@endpush
