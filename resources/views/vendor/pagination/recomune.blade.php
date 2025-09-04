@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination" class="flex items-center justify-center md:justify-end">
        <ul
            class="inline-flex items-center gap-2 rounded-2xl border border-gray-200/50 dark:border-gray-700/50 bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl px-2 py-2 shadow-2xl">

            {{-- Previous --}}
            @if ($paginator->onFirstPage())
                <li aria-disabled="true" aria-label="@lang('pagination.previous')">
                    <span
                        class="inline-flex items-center px-4 py-2 text-sm rounded-xl text-gray-400 dark:text-gray-500 cursor-not-allowed">
                        ‹ Prev
                    </span>
                </li>
            @else
                <li>
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="@lang('pagination.previous')"
                        class="inline-flex items-center px-4 py-2 text-sm rounded-xl border border-transparent text-gray-700 dark:text-gray-300 hover:bg-gray-100/80 dark:hover:bg-gray-700/80 transition">
                        ‹ Prev
                    </a>
                </li>
            @endif

            {{-- Page Numbers --}}
            @foreach ($elements as $element)
                {{-- Ellipsis --}}
                @if (is_string($element))
                    <li aria-disabled="true">
                        <span
                            class="inline-flex items-center px-3 py-2 text-sm rounded-xl text-gray-400 dark:text-gray-500">…</span>
                    </li>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li aria-current="page">
                                <span
                                    class="inline-flex items-center px-4 py-2 text-sm font-semibold rounded-xl bg-primary-600 text-white shadow">
                                    {{ $page }}
                                </span>
                            </li>
                        @else
                            <li>
                                <a href="{{ $url }}"
                                    class="inline-flex items-center px-4 py-2 text-sm rounded-xl border border-transparent text-gray-700 dark:text-gray-300 hover:bg-gray-100/80 dark:hover:bg-gray-700/80 transition">
                                    {{ $page }}
                                </a>
                            </li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next --}}
            @if ($paginator->hasMorePages())
                <li>
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="@lang('pagination.next')"
                        class="inline-flex items-center px-4 py-2 text-sm rounded-xl border border-transparent text-gray-700 dark:text-gray-300 hover:bg-gray-100/80 dark:hover:bg-gray-700/80 transition">
                        Next ›
                    </a>
                </li>
            @else
                <li aria-disabled="true" aria-label="@lang('pagination.next')">
                    <span
                        class="inline-flex items-center px-4 py-2 text-sm rounded-xl text-gray-400 dark:text-gray-500 cursor-not-allowed">
                        Next ›
                    </span>
                </li>
            @endif
        </ul>
    </nav>
@endif
