@if ($paginator->hasPages())
    <nav role="navigation" aria-label="ڕێنیشاندەری لاپەڕەکان" class="flex gap-2 items-center justify-between">

        @if ($paginator->onFirstPage())
            <span class="inline-flex items-center px-4 py-2 text-sm font-medium text-slate-400 bg-slate-50 border border-slate-200 cursor-not-allowed leading-5 rounded-md">
                پێشوو
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex items-center px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 leading-5 rounded-md hover:bg-slate-50 hover:text-slate-900 focus:outline-none focus:border-brand-500 active:bg-slate-100 transition ease-in-out duration-150">
                پێشوو
            </a>
        @endif

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex items-center px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 leading-5 rounded-md hover:bg-slate-50 hover:text-slate-900 focus:outline-none focus:border-brand-500 active:bg-slate-100 transition ease-in-out duration-150">
                دواتر
            </a>
        @else
            <span class="inline-flex items-center px-4 py-2 text-sm font-medium text-slate-400 bg-slate-50 border border-slate-200 cursor-not-allowed leading-5 rounded-md">
                دواتر
            </span>
        @endif

    </nav>
@endif
