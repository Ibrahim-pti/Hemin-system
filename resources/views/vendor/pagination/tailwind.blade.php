@if ($paginator->hasPages())
    <nav role="navigation" aria-label="ڕێنیشاندەری لاپەڕەکان">

        <div class="flex gap-2 items-center justify-between sm:hidden">

            @if ($paginator->onFirstPage())
                <span class="inline-flex items-center px-3.5 py-2 text-xs font-medium text-slate-400 bg-slate-50 border border-slate-200 cursor-not-allowed leading-5 rounded-md">
                    پێشوو
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex items-center px-3.5 py-2 text-xs font-medium text-slate-700 bg-white border border-slate-300 leading-5 rounded-md hover:bg-slate-50 hover:text-slate-900 focus:outline-none focus:border-brand-500 active:bg-slate-100 transition ease-in-out duration-150">
                    پێشوو
                </a>
            @endif

            <span class="text-xs font-bold text-slate-500">
                لاپەڕە {{ $paginator->currentPage() }} لە {{ $paginator->lastPage() }}
            </span>

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex items-center px-3.5 py-2 text-xs font-medium text-slate-700 bg-white border border-slate-300 leading-5 rounded-md hover:bg-slate-50 hover:text-slate-900 focus:outline-none focus:border-brand-500 active:bg-slate-100 transition ease-in-out duration-150">
                    دواتر
                </a>
            @else
                <span class="inline-flex items-center px-3.5 py-2 text-xs font-medium text-slate-400 bg-slate-50 border border-slate-200 cursor-not-allowed leading-5 rounded-md">
                    دواتر
                </span>
            @endif

        </div>

        <div class="hidden sm:flex-1 sm:flex sm:gap-2 sm:items-center sm:justify-between">

            <div>
                <p class="text-sm text-slate-600 leading-5">
                    پیشاندانی
                    @if ($paginator->firstItem())
                        <span class="font-bold text-slate-800">{{ $paginator->firstItem() }}</span>
                        تا
                        <span class="font-bold text-slate-800">{{ $paginator->lastItem() }}</span>
                    @else
                        <span class="font-bold text-slate-800">{{ $paginator->count() }}</span>
                    @endif
                    لە کۆی
                    <span class="font-bold text-slate-800">{{ $paginator->total() }}</span>
                    ئەنجام
                </p>
            </div>

            <div>
                <span class="inline-flex rtl:flex-row-reverse rounded-md">

                    {{-- لاپەڕەی پێشوو --}}
                    @if ($paginator->onFirstPage())
                        <span aria-disabled="true" aria-label="پێشوو">
                            <span class="inline-flex items-center px-2 py-2 text-sm font-medium text-slate-300 bg-slate-50 border border-slate-300 cursor-not-allowed rounded-l-md leading-5" aria-hidden="true">
                                <svg class="w-5 h-5" width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            </span>
                        </span>
                    @else
                        <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex items-center px-2 py-2 text-sm font-medium text-slate-600 bg-white border border-slate-300 rounded-l-md leading-5 hover:bg-slate-50 hover:text-slate-800 focus:outline-none focus:border-brand-500 active:bg-slate-100 transition ease-in-out duration-150" aria-label="پێشوو">
                            <svg class="w-5 h-5" width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                        </a>
                    @endif

                    {{-- ژمارەی لاپەڕەکان --}}
                    @foreach ($elements as $element)
                        {{-- سێ خاڵ (...) --}}
                        @if (is_string($element))
                            <span aria-disabled="true">
                                <span class="inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-slate-500 bg-white border border-slate-300 cursor-default leading-5">{{ $element }}</span>
                            </span>
                        @endif

                        {{-- ڕیزی لینکەکان --}}
                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                @if ($page == $paginator->currentPage())
                                    <span aria-current="page">
                                        <span class="relative z-10 inline-flex items-center px-4 py-2 -ml-px text-sm font-bold text-brand-700 bg-brand-50 border border-brand-300 cursor-default leading-5">{{ $page }}</span>
                                    </span>
                                @else
                                    <a href="{{ $url }}" class="inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-slate-700 bg-white border border-slate-300 leading-5 hover:bg-slate-50 hover:text-slate-900 focus:outline-none focus:border-brand-500 active:bg-slate-100 transition ease-in-out duration-150" aria-label="چوون بۆ لاپەڕە {{ $page }}">
                                        {{ $page }}
                                    </a>
                                @endif
                            @endforeach
                        @endif
                    @endforeach

                    {{-- لاپەڕەی دواتر --}}
                    @if ($paginator->hasMorePages())
                        <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex items-center px-2 py-2 -ml-px text-sm font-medium text-slate-600 bg-white border border-slate-300 rounded-r-md leading-5 hover:bg-slate-50 hover:text-slate-800 focus:outline-none focus:border-brand-500 active:bg-slate-100 transition ease-in-out duration-150" aria-label="دواتر">
                            <svg class="w-5 h-5" width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                            </svg>
                        </a>
                    @else
                        <span aria-disabled="true" aria-label="دواتر">
                            <span class="inline-flex items-center px-2 py-2 -ml-px text-sm font-medium text-slate-300 bg-slate-50 border border-slate-300 cursor-not-allowed rounded-r-md leading-5" aria-hidden="true">
                                <svg class="w-5 h-5" width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
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
