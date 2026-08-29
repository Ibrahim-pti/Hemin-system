{{-- پاڵاوتنی ماوەی کات — بۆ هەموو راپۆرتەکان --}}
<form method="GET" class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-xs mb-4 sm:mb-6 no-print">
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
        <div class="flex flex-wrap items-end gap-3 sm:gap-4">
            <div>
                <label class="block font-bold text-xs text-slate-600 mb-1.5">لە بەرواری</label>
                <input type="date" name="from" value="{{ $from }}"
                       class="text-xs px-3.5 py-2 rounded-xl border border-slate-200 bg-white focus:outline-hidden focus:border-blue-500 font-mono font-bold text-slate-800">
            </div>

            <div>
                <label class="block font-bold text-xs text-slate-600 mb-1.5">تا بەرواری</label>
                <input type="date" name="to" value="{{ $to }}"
                       class="text-xs px-3.5 py-2 rounded-xl border border-slate-200 bg-white focus:outline-hidden focus:border-blue-500 font-mono font-bold text-slate-800">
            </div>

            <button type="submit"
                    class="px-5 py-2 rounded-xl text-xs font-black bg-blue-600 hover:bg-blue-700 text-white shadow-xs transition-all cursor-pointer">
                🔍 پیشاندان
            </button>

            <button type="button" onclick="window.print()"
                    class="px-4 py-2 rounded-xl text-xs font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 transition-all cursor-pointer inline-flex items-center gap-1.5">
                <span>🖨️</span>
                <span>چاپکردن</span>
            </button>
        </div>

        <a href="{{ route('reports.index') }}"
           class="px-4 py-2 rounded-xl text-xs font-bold bg-slate-50 hover:bg-slate-100 text-slate-600 border border-slate-200 transition-all inline-flex items-center gap-1.5 self-start md:self-auto">
            <span>←</span>
            <span>سەرجەم ڕاپۆرتەکان</span>
        </a>
    </div>
</form>
