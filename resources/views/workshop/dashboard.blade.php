@extends('layouts.menu')
@section('title', 'داشبۆردی کارگە')

@section('content')
<div class="space-y-4 sm:space-y-6">

    {{-- ١. هێڵی سەرەوەی داشبۆرد: بەخێرهاتن و کاتژمێری ڕاستەوخۆ --}}
    <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-3.5">
            <div class="w-12 h-12 rounded-2xl bg-blue-600 text-white flex items-center justify-center text-2xl shadow-md shrink-0">
                🏭
            </div>
            <div>
                <div class="flex items-center gap-2 flex-wrap">
                    <h1 class="text-lg sm:text-xl font-black text-slate-900">داشبۆردی سەرەکی کارگە</h1>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200">
                        کارگەی ئاسنگەری
                    </span>
                </div>
                <p class="text-xs text-slate-500 mt-0.5 font-medium">
                    پوختەی ڕۆژانەی دروستکردن، مەخزەن و ئامادەبوونی وەستاکان
                </p>
            </div>
        </div>

        {{-- دوگمە و بەستەرە خێراکان --}}
        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('workshop.orders') }}"
               class="px-3.5 py-2 rounded-xl text-xs font-bold bg-blue-600 hover:bg-blue-700 text-white shadow-xs flex items-center gap-1.5 transition-all">
                <span>⚙️</span>
                <span>داواکارییەکان</span>
            </a>
            <a href="{{ route('workshop.materials') }}"
               class="px-3.5 py-2 rounded-xl text-xs font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 flex items-center gap-1.5 transition-all">
                <span>📦</span>
                <span>مەخزەن</span>
            </a>
            <a href="{{ route('workshop.employees') }}"
               class="px-3.5 py-2 rounded-xl text-xs font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 flex items-center gap-1.5 transition-all">
                <span>👷</span>
                <span>وەستاکان</span>
            </a>
        </div>
    </div>

    {{-- ٢. کارتە سەرەکییەکانی ئامار --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        {{-- ١. لە دروستکردندا --}}
        <a href="{{ route('workshop.orders') }}?status=in_production"
           class="bg-white rounded-2xl p-4 border border-slate-200 hover:border-blue-500 hover:shadow-md transition-all group block">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold text-slate-600">لە ژێر دروستکردندا</span>
                <span class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-base border border-blue-200 shrink-0">⚙️</span>
            </div>
            <div class="text-2xl sm:text-3xl font-black text-blue-600 font-mono">{{ fmt_num($inProductionCount) }}</div>
            <div class="text-[11px] text-blue-600/80 font-semibold mt-1">وەسڵی چالاک</div>
        </a>

        {{-- ٢. چاوەڕوانی دروستکردن --}}
        <a href="{{ route('workshop.orders') }}?status=confirmed"
           class="bg-white rounded-2xl p-4 border border-slate-200 hover:border-amber-500 hover:shadow-md transition-all group block">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold text-slate-600">چاوەڕوانی کارگە</span>
                <span class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-base border border-amber-200 shrink-0">⏳</span>
            </div>
            <div class="text-2xl sm:text-3xl font-black text-amber-600 font-mono">{{ fmt_num($pendingCount) }}</div>
            <div class="text-[11px] text-amber-700/80 font-semibold mt-1">پێویست بە دەستپێکردن</div>
        </a>

        {{-- ٣. ئامادەکراو --}}
        <a href="{{ route('workshop.orders') }}?status=ready"
           class="bg-white rounded-2xl p-4 border border-slate-200 hover:border-emerald-500 hover:shadow-md transition-all group block">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold text-slate-600">ئامادەی ڕادەستکردن</span>
                <span class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-base border border-emerald-200 shrink-0">✅</span>
            </div>
            <div class="text-2xl sm:text-3xl font-black text-emerald-600 font-mono">{{ fmt_num($readyCount) }}</div>
            <div class="text-[11px] text-emerald-700/80 font-semibold mt-1">تەواوکراو بۆ کڕیار</div>
        </a>

        {{-- ٤. وەستاکانی ئەمڕۆ --}}
        <a href="{{ route('workshop.employees') }}"
           class="bg-white rounded-2xl p-4 border border-slate-200 hover:border-indigo-500 hover:shadow-md transition-all group block">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold text-slate-600">ئامادەبووانی ئەمڕۆ</span>
                <span class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-base border border-indigo-200 shrink-0">👷</span>
            </div>
            <div class="text-2xl sm:text-3xl font-black text-slate-800 font-mono">
                {{ $employees->filter(fn($e) => $e->attendances->first()?->status === 'present')->count() }}
                <span class="text-xs font-bold text-slate-400">/ {{ $employees->count() }}</span>
            </div>
            <div class="text-[11px] text-indigo-600/80 font-semibold mt-1">وەستا و کرێکار</div>
        </a>
    </div>

    {{-- ٣. ئاگاداری کەمی مەواد (بە شێوازێکی زۆر پاک و ڕێک) --}}
    @if ($lowStockMaterials->isNotEmpty())
        <div class="bg-rose-50/70 rounded-2xl p-3.5 sm:p-4 border border-rose-200 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <span class="w-9 h-9 rounded-xl bg-rose-100 text-rose-700 flex items-center justify-center font-bold text-lg shrink-0">⚠️</span>
                <div>
                    <div class="text-xs sm:text-sm font-black text-rose-900 flex items-center gap-2 flex-wrap">
                        <span>ئاگاداری: ({{ $lowStockMaterials->count() }}) جۆر مەواد لە مەخزەن کەم بووەتەوە</span>
                    </div>
                    <div class="text-[11px] text-rose-700 font-medium mt-0.5 flex items-center gap-1.5 flex-wrap">
                        @foreach ($lowStockMaterials->take(4) as $lowMat)
                            <span class="bg-white/80 px-2 py-0.5 rounded-md border border-rose-200 font-bold">
                                {{ $lowMat->name }}: <b class="text-rose-800 font-mono">{{ fmt_num($lowMat->stock_qty) }} {{ $lowMat->unit?->name }}</b>
                            </span>
                        @endforeach
                    </div>
                </div>
            </div>

            <a href="{{ route('workshop.materials') }}"
               class="px-3 py-1.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shrink-0 self-end sm:self-center transition-all flex items-center gap-1">
                <span>زیادکردنی مەواد</span>
                <span>←</span>
            </a>
        </div>
    @endif

    {{-- ٤. بەشە سەرەکییەکان: وەسڵە کاراکان + وەستاکانی ئەمڕۆ --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">

        {{-- وەسڵە کاراکان (لە دروستکردندا و چاوەڕوانی) --}}
        <div class="lg:col-span-2 space-y-4">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
                <div class="p-3.5 sm:p-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <div class="flex items-center gap-2">
                        <span class="w-7 h-7 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-sm shrink-0">
                            ⚙️
                        </span>
                        <div>
                            <h3 class="font-black text-xs sm:text-sm text-slate-800">دوایین وەسڵە کاراکانی کارگە</h3>
                            <p class="text-[11px] text-slate-400 font-medium">ئەو داواکارییانەی پێویستیان بە دروستکردن هەیە</p>
                        </div>
                    </div>
                    <a href="{{ route('workshop.orders') }}" class="text-xs font-bold text-blue-600 hover:text-blue-800 flex items-center gap-1">
                        <span>هەموو وەسڵەکان</span>
                        <span>←</span>
                    </a>
                </div>

                @if ($activeOrders->isEmpty())
                    <div class="p-10 text-center text-slate-400 text-xs font-medium">
                        <div class="text-3xl mb-2">🎉</div>
                        هیچ وەسڵێکی چاوەڕوانکراو یان لە کاردا نییە.
                    </div>
                @else
                    <div class="divide-y divide-slate-100">
                        @foreach ($activeOrders as $order)
                            <div class="p-3.5 sm:p-4 hover:bg-slate-50/80 transition-colors flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="font-black text-sm text-slate-900 font-mono">وەسڵی #{{ $order->invoice_no }}</span>
                                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold {{ $order->status === 'in_production' ? 'bg-blue-100 text-blue-800 border border-blue-200' : ($order->status === 'ready' ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : 'bg-amber-100 text-amber-800 border border-amber-200') }}">
                                            {{ $order->status_label }}
                                        </span>
                                    </div>

                                    <div class="text-xs text-slate-600 font-medium mt-1 flex items-center gap-2 flex-wrap">
                                        <span>کڕیار: <b class="text-slate-900">{{ $order->customer?->name ?? 'نەناسراو' }}</b></span>
                                        @if ($order->delivery_date)
                                            <span class="text-slate-300">•</span>
                                            <span class="text-rose-600 font-bold font-mono text-[11px]">گەیاندن: {{ $order->delivery_date->format('Y/m/d') }}</span>
                                        @endif
                                    </div>

                                    <div class="text-[11px] text-slate-500 mt-1 flex flex-wrap gap-1.5">
                                        @foreach ($order->items->take(3) as $it)
                                            <span class="bg-slate-100 px-2 py-0.5 rounded-md text-slate-700 font-medium">
                                                {{ $it->item_name }} ({{ fmt_num($it->qty) }} {{ $it->unit_name }})
                                            </span>
                                        @endforeach
                                        @if ($order->items->count() > 3)
                                            <span class="text-slate-400 text-[10px] self-center">+{{ $order->items->count() - 3 }} دانەی تر</span>
                                        @endif
                                    </div>
                                </div>

                                <div class="self-end sm:self-center shrink-0">
                                    <a href="{{ route('workshop.orders') }}"
                                       class="px-3.5 py-1.5 rounded-xl text-xs font-bold bg-white hover:bg-slate-100 text-slate-700 border border-slate-200 shadow-2xs transition-all flex items-center gap-1">
                                        <span>وردەکاری</span>
                                        <span>←</span>
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        {{-- وەستاکانی ئەمڕۆ --}}
        <div class="space-y-4">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
                <div class="p-3.5 sm:p-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <div class="flex items-center gap-2">
                        <span class="w-7 h-7 rounded-lg bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-sm shrink-0">
                            👷
                        </span>
                        <div>
                            <h3 class="font-black text-xs sm:text-sm text-slate-800">وەستاکانی ئەمڕۆ</h3>
                            <p class="text-[11px] text-slate-400 font-medium">دۆخی ئامادەبوون</p>
                        </div>
                    </div>
                    <a href="{{ route('workshop.employees') }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 flex items-center gap-1">
                        <span>ئامادەبوون</span>
                        <span>←</span>
                    </a>
                </div>

                @if ($employees->isEmpty())
                    <div class="p-8 text-center text-slate-400 text-xs font-medium">
                        هیچ وەستایەک تۆمار نەکراوە.
                    </div>
                @else
                    <div class="divide-y divide-slate-100">
                        @foreach ($employees->take(5) as $emp)
                            @php
                                $todayAtt = $emp->attendances->first();
                            @endphp
                            <div class="p-3 sm:p-3.5 hover:bg-slate-50 transition-colors flex items-center justify-between gap-2">
                                <div class="min-w-0">
                                    <div class="font-bold text-xs text-slate-900 truncate">{{ $emp->name }}</div>
                                    <div class="text-[11px] text-slate-400 font-medium mt-0.5">{{ $emp->job_title_label }}</div>
                                </div>
                                <div class="shrink-0">
                                    @if ($todayAtt && $todayAtt->status === 'present')
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                            ئامادەیە ✔️
                                        </span>
                                    @elseif ($todayAtt && $todayAtt->status === 'leave')
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                            ئیجازە 🏖️
                                        </span>
                                    @elseif ($todayAtt && $todayAtt->status === 'absent')
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-200">
                                            نەهاتووە ❌
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-500 border border-slate-200">
                                            تۆمارنەکراو
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

    </div>

</div>
@endsection
