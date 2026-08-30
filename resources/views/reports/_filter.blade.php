{{-- پاڵاوتنی ماوەی کات — بۆ هەموو راپۆرتەکان --}}
@php
    $currentPeriod = request('period', ($from === null && $to === null) ? 'all' : '');
    $today = now()->toDateString();
    $thisMonthStart = now()->startOfMonth()->toDateString();
    $thisMonthEnd = now()->endOfMonth()->toDateString();
    $lastMonthStart = now()->subMonth()->startOfMonth()->toDateString();
    $lastMonthEnd = now()->subMonth()->endOfMonth()->toDateString();
    $thisYearStart = now()->startOfYear()->toDateString();
    $thisYearEnd = now()->endOfYear()->toDateString();
@endphp

<div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-xs mb-4 sm:mb-6 no-print">
    <div class="flex flex-col gap-3.5">
        {{-- دوگمەکانی فلتەری خێرا --}}
        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 pb-3">
            <div class="flex flex-wrap items-center gap-1.5 sm:gap-2">
                <span class="text-xs font-bold text-slate-500 ml-1">ماوەی خێرا:</span>
                <a href="{{ request()->fullUrlWithQuery(['period' => 'all', 'from' => null, 'to' => null]) }}"
                   class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all {{ ($currentPeriod === 'all' || (!$from && !$to)) ? 'bg-blue-600 text-white shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                    📊 هەموو داتاکان
                </a>
                <a href="{{ request()->fullUrlWithQuery(['period' => 'today', 'from' => $today, 'to' => $today]) }}"
                   class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all {{ ($currentPeriod === 'today' || ($from === $today && $to === $today)) ? 'bg-blue-600 text-white shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                    ئەمڕۆ
                </a>
                <a href="{{ request()->fullUrlWithQuery(['period' => 'this_month', 'from' => $thisMonthStart, 'to' => $thisMonthEnd]) }}"
                   class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all {{ ($currentPeriod === 'this_month' || ($from === $thisMonthStart && $to === $thisMonthEnd)) ? 'bg-blue-600 text-white shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                    ئەم مانگە
                </a>
                <a href="{{ request()->fullUrlWithQuery(['period' => 'last_month', 'from' => $lastMonthStart, 'to' => $lastMonthEnd]) }}"
                   class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all {{ ($currentPeriod === 'last_month' || ($from === $lastMonthStart && $to === $lastMonthEnd)) ? 'bg-blue-600 text-white shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                    مانگی پێشوو
                </a>
                <a href="{{ request()->fullUrlWithQuery(['period' => 'this_year', 'from' => $thisYearStart, 'to' => $thisYearEnd]) }}"
                   class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all {{ ($currentPeriod === 'this_year' || ($from === $thisYearStart && $to === $thisYearEnd)) ? 'bg-blue-600 text-white shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                    ئەم ساڵە
                </a>
            </div>

            <div class="flex items-center gap-2">
                <button onclick="window.print()" class="px-3 py-1.5 rounded-lg text-xs font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 transition-all inline-flex items-center gap-1 cursor-pointer">
                    🖨️ چاپکردن
                </button>
                <a href="{{ route('reports.index') }}"
                   class="px-3 py-1.5 rounded-lg text-xs font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 transition-all inline-flex items-center gap-1">
                    ← راپۆرتەکان
                </a>
            </div>
        </div>

        {{-- فۆڕمی بەرواری دیاریکراو --}}
        <form method="GET" class="flex flex-wrap items-end gap-3 sm:gap-4">
            <div>
                <label class="block font-bold text-xs text-slate-600 mb-1">لە بەرواری</label>
                <input type="date" name="from" value="{{ $from }}"
                       class="text-xs px-3.5 py-2 rounded-xl border border-slate-200 bg-white focus:outline-hidden focus:border-blue-500 font-mono font-bold text-slate-800">
            </div>

            <div>
                <label class="block font-bold text-xs text-slate-600 mb-1">تا بەرواری</label>
                <input type="date" name="to" value="{{ $to }}"
                       class="text-xs px-3.5 py-2 rounded-xl border border-slate-200 bg-white focus:outline-hidden focus:border-blue-500 font-mono font-bold text-slate-800">
            </div>

            <button type="submit"
                    class="px-5 py-2 rounded-xl text-xs font-black bg-blue-600 hover:bg-blue-700 text-white shadow-xs transition-all cursor-pointer">
                🔍 پیشاندان
            </button>

            @if ($from || $to)
                <a href="{{ request()->url() }}?period=all"
                   class="px-4 py-2 rounded-xl text-xs font-bold bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 transition-all">
                    سڕینەوەی فلتەر (هەموو داتاکان)
                </a>
            @endif
        </form>
    </div>
</div>
