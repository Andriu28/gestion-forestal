@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="flex items-center justify-between">
        <div class="flex justify-between flex-1 sm:hidden">
            @if ($paginator->onFirstPage())
                <span class="relative inline-flex items-center px-3 hover:bg-gray-300/20 hover:dark:bg-gray-600/30 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 cursor-default leading-5 rounded-md dark:text-gray-600 dark:bg-gray-800/30 dark:border-gray-600">
                    {!! __('pagination.previous') !!}
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="relative inline-flex items-center px-3 hover:bg-gray-300/20 hover:dark:bg-gray-600/30 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 leading-5 rounded-md hover:text-gray-500 focus:outline-none focus:ring ring-gray-300 focus:border-blue-300 active:bg-gray-100 active:text-gray-700 dark:bg-gray-800/30 dark:border-gray-600 dark:text-gray-300 dark:focus:border-blue-700 dark:active:bg-gray-700 dark:active:text-gray-300">
                    {!! __('pagination.previous') !!}
                </a>
            @endif

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="relative inline-flex items-center px-3 hover:bg-gray-300/20 hover:dark:bg-gray-600/30 py-2 ml-3 text-sm font-medium text-gray-700 bg-white border border-gray-300 leading-5 rounded-md hover:text-gray-500 focus:outline-none focus:ring ring-gray-300 focus:border-blue-300 active:bg-gray-100 active:text-gray-700 dark:bg-gray-800/30 dark:border-gray-600 dark:text-gray-300 dark:focus:border-blue-700 dark:active:bg-gray-700 dark:active:text-gray-300">
                    {!! __('pagination.next') !!}
                </a>
            @else
                <span class="relative inline-flex items-center px-3 hover:bg-gray-300/20 hover:dark:bg-gray-600/30 py-2 ml-3 text-sm font-medium text-gray-500 bg-white border border-gray-300 cursor-default leading-5 rounded-md dark:text-gray-600 dark:bg-gray-800/30 dark:border-gray-600">
                    {!! __('pagination.next') !!}
                </span>
            @endif
        </div>

        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
            <div>
                <p class="text-sm text-gray-700 leading-5 dark:text-gray-400">
                    {!! __('Showing') !!}
                    @if ($paginator->firstItem())
                        <span class="font-medium">{{ $paginator->firstItem() }}</span>
                        {!! __('to') !!}
                        <span class="font-medium">{{ $paginator->lastItem() }}</span>
                    @else
                        {{ $paginator->count() }}
                    @endif
                    {!! __('of') !!}
                    <span class="font-medium">{{ $paginator->total() }}</span>
                    {!! __('results') !!}
                </p>
            </div>

            <div>
                <span class="relative z-0 inline-flex rtl:flex-row-reverse shadow-sm rounded-md">
                    {{-- Previous Page Link --}}
                    @if ($paginator->onFirstPage())
                        <span aria-disabled="true" aria-label="{{ __('pagination.previous') }}">
                            <span class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-gray-500 bg-gray-50 hover:bg-gray-300/40 hover:dark:bg-gray-500/40 border border-gray-300 cursor-default rounded-l-md leading-5 dark:bg-gray-800/30 dark:border-gray-600" aria-hidden="true">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            </span>
                        </span>
                    @else
                        <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-gray-500 bg-gray-200/30 hover:bg-gray-300/40 hover:dark:bg-gray-500/40 border border-gray-300 rounded-l-md leading-5 hover:text-gray-600 dark:hover:text-gray-200 focus:z-10 focus:outline-none focus:ring ring-gray-300 focus:border-blue-300 active:bg-gray-100 active:text-gray-500 dark:bg-gray-700/50 dark:border-gray-600 dark:active:bg-gray-700 dark:focus:border-blue-800" aria-label="{{ __('pagination.previous') }}">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                        </a>
                    @endif

                    {{-- PAGINACIÓN PERSONALIZADA --}}
                    @php
                        $currentPage = $paginator->currentPage();
                        $lastPage = $paginator->lastPage();
                    @endphp

                    {{-- Primera página --}}
                    @if ($currentPage == 1)
                        <span aria-current="page" class="relative inline-flex items-center px-3 hover:bg-gray-300/20 hover:bg-gray-300/40 hover:dark:bg-gray-500/40 py-2 -ml-px text-sm font-medium text-gray-900 bg-gray-300/20 border border-gray-300 cursor-default leading-5 dark:bg-gray-700/70 dark:border-gray-600">
                            1
                        </span>
                    @else
                        <a href="{{ $paginator->url(1) }}" class="relative inline-flex items-center px-3 hover:bg-gray-300/20 hover:dark:bg-gray-600/30 py-2 -ml-px text-sm font-medium text-gray-700 bg-white border border-gray-300 leading-5 hover:text-gray-500 focus:z-10 focus:outline-none focus:ring ring-gray-300 focus:border-blue-300 active:bg-gray-100 active:text-gray-700 dark:bg-gray-800/30 dark:border-gray-600 dark:text-gray-400 dark:hover:text-gray-300 dark:active:bg-gray-700 dark:focus:border-blue-800" aria-label="{{ __('Go to page :page', ['page' => 1]) }}">
                            1
                        </a>
                    @endif

                    {{-- Puntos suspensivos después de la primera página --}}
                    @if ($currentPage > 3)
                        <span aria-disabled="true">
                            <span class="relative inline-flex items-center px-3 hover:bg-gray-300/20 hover:dark:bg-gray-600/30 py-2 -ml-px text-sm font-medium text-gray-700 bg-white border border-gray-300 cursor-default leading-5 dark:bg-gray-800/30 dark:border-gray-600">...</span>
                        </span>
                    @endif

                    {{-- Página anterior a la actual --}}
                    @if ($currentPage > 2 && $currentPage - 1 != 1)
                        <a href="{{ $paginator->url($currentPage - 1) }}" class="relative inline-flex items-center px-3 hover:bg-gray-300/20 hover:dark:bg-gray-600/30 py-2 -ml-px text-sm font-medium text-gray-700 bg-white border border-gray-300 leading-5 hover:text-gray-500 focus:z-10 focus:outline-none focus:ring ring-gray-300 focus:border-blue-300 active:bg-gray-100 active:text-gray-700 dark:bg-gray-800/30 dark:border-gray-600 dark:text-gray-400 dark:hover:text-gray-300 dark:active:bg-gray-700 dark:focus:border-blue-800" aria-label="{{ __('Go to page :page', ['page' => $currentPage - 1]) }}">
                            {{ $currentPage - 1 }}
                        </a>
                    @endif

                    {{-- Página actual (si no es la primera ni la última) --}}
                    @if ($currentPage > 1 && $currentPage < $lastPage)
                        <span aria-current="page" class="relative inline-flex items-center px-3 hover:bg-gray-300/40 hover:dark:bg-gray-500/40 py-2 -ml-px text-sm font-medium text-gray-900 dark:text-gray-100 bg-gray-300/20 border border-gray-300 cursor-default leading-5 dark:bg-gray-700/70 dark:border-gray-600">
                                {{ $currentPage }}
                        </span>
                    @endif

                    {{-- Página siguiente a la actual --}}
                    @if ($currentPage < $lastPage - 1 && $currentPage + 1 != $lastPage)
                        <a href="{{ $paginator->url($currentPage + 1) }}" class="relative inline-flex items-center px-3 hover:bg-gray-300/20 hover:dark:bg-gray-600/30 py-2 -ml-px text-sm font-medium text-gray-700 bg-white border border-gray-300 leading-5 hover:text-gray-500 focus:z-10 focus:outline-none focus:ring ring-gray-300 focus:border-blue-300 active:bg-gray-100 active:text-gray-700 dark:bg-gray-800/30 dark:border-gray-600 dark:text-gray-400 dark:hover:text-gray-300 dark:active:bg-gray-700 dark:focus:border-blue-800" aria-label="{{ __('Go to page :page', ['page' => $currentPage + 1]) }}">
                            {{ $currentPage + 1 }}
                        </a>
                    @endif

                    {{-- Puntos suspensivos antes de la última página --}}
                    @if ($currentPage < $lastPage - 2)
                        <span aria-disabled="true">
                            <span class="relative inline-flex items-center px-3 hover:bg-gray-300/20 hover:dark:bg-gray-600/30 py-2 -ml-px text-sm font-medium text-gray-700 bg-white border border-gray-300 cursor-default leading-5 dark:bg-gray-800/30 dark:border-gray-600">...</span>
                        </span>
                    @endif

                    {{-- Última página (si es diferente de la primera) --}}
                    @if ($lastPage > 1)
                        @if ($currentPage == $lastPage)
                            <span aria-current="page" class="relative inline-flex items-center px-3 hover:bg-gray-300/40 hover:dark:bg-gray-500/40 py-2 -ml-px text-sm font-medium text-gray-900 bg-gray-300/20 border border-gray-300 cursor-default leading-5 dark:bg-gray-700/70 dark:border-gray-600">
                                {{ $lastPage }}
                            </span>
                        @else
                            <a href="{{ $paginator->url($lastPage) }}" class="relative inline-flex items-center px-3 hover:bg-gray-300/20 hover:dark:bg-gray-600/30 py-2 -ml-px text-sm font-medium text-gray-700 bg-white border border-gray-300 leading-5 hover:text-gray-500 focus:z-10 focus:outline-none focus:ring ring-gray-300 focus:border-blue-300 active:bg-gray-100 active:text-gray-700 dark:bg-gray-800/30 dark:border-gray-600 dark:text-gray-400 dark:hover:text-gray-300 dark:active:bg-gray-700 dark:focus:border-blue-800" aria-label="{{ __('Go to page :page', ['page' => $lastPage]) }}">
                                {{ $lastPage }}
                            </a>
                        @endif
                    @endif

                    {{-- Next Page Link --}}
                    @if ($paginator->hasMorePages())
                        <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="relative inline-flex items-center px-2 py-2 -ml-px text-sm font-medium text-gray-500 bg-gray-200/30 hover:bg-gray-300/40 hover:dark:bg-gray-500/40 border border-gray-300 rounded-r-md leading-5 hover:text-gray-600 dark:hover:text-gray-200 focus:z-10 focus:outline-none focus:ring ring-gray-300 focus:border-blue-300 active:bg-gray-100 active:text-gray-500 dark:bg-gray-700/50 dark:border-gray-600 dark:active:bg-gray-700 dark:focus:border-blue-800" aria-label="{{ __('pagination.next') }}">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                            </svg>
                        </a>
                    @else
                        <span aria-disabled="true" aria-label="{{ __('pagination.next') }}">
                            <span class="relative inline-flex items-center px-2 py-2 -ml-px text-sm font-medium text-gray-500 bg-gray-50 hover:bg-gray-300/40 hover:dark:bg-gray-500/40 border border-gray-300 cursor-default rounded-r-md leading-5 dark:bg-gray-800/30 dark:border-gray-600" aria-hidden="true">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                </svg>
                            </span>
                        </span>
                    @endif
                </span>
            </div>
        </div>
    </nav>
@endif