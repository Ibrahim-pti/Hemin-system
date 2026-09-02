@extends('layouts.menu')
@section('title', 'داشبۆردی کارگە')

@section('content')
<div class="space-y-4 sm:space-y-6">

    {{-- ١. هێڵی سەرەوەی داشبۆرد: ناونیشان و دوگمە سەرەکییەکان --}}
    <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-3.5">
            <div style="width: 48px; height: 48px; min-width: 48px; min-height: 48px;"
                 class="rounded-2xl bg-blue-600 text-white flex items-center justify-center text-2xl shadow-md shrink-0">
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

        {{-- بەتنە خێراکان بە چوارچێوەی ڕێک و تەواو --}}
        <div class="flex items-center gap-2.5 flex-wrap shrink-0">
            <a href="{{ route('workshop.orders') }}"
               class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold bg-blue-600 hover:bg-blue-700 text-white shadow-xs transition-all border border-blue-700">
                <span>⚙️</span>
                <span>داواکارییەکان</span>
            </a>
            <a href="{{ route('workshop.materials') }}"
               class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-300 shadow-2xs transition-all">
                <span>📦</span>
                <span>مەخزەن</span>
            </a>
            <a href="{{ route('workshop.employees') }}"
               class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-300 shadow-2xs transition-all">
                <span>👷</span>
                <span>کارمەندان</span>
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

    {{-- ٣. ئاگاداری کەمی مەواد (تەواو هاوشێوەی کۆنتەینەری سەرەوە بە دوگمەی سوور) --}}
    @if ($lowStockMaterials->isNotEmpty())
        <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-3.5">
                <div style="width: 48px; height: 48px; min-width: 48px; min-height: 48px;"
                     class="rounded-2xl bg-rose-600 text-white flex items-center justify-center text-2xl shadow-md shrink-0">
                    ⚠️
                </div>
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <h2 class="text-base sm:text-lg font-black text-slate-900">ئاگاداری: مەوادە کەمبووەکانی مەخزەن</h2>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-50 text-rose-800 border border-rose-200">
                            {{ $lowStockMaterials->count() }} جۆر مەواد
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 mt-0.5 font-medium">
                        بڕی هەندێک مەواد لە مەخزەندا لە سنووری کەمترین پێویست کەمتریان ماوە و پێویستیان بە پڕکردنەوەیە.
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2.5 flex-wrap shrink-0">
                <a href="{{ route('workshop.materials') }}?filter=low"
                   class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold bg-rose-600 hover:bg-rose-700 text-white shadow-xs transition-all border border-rose-700">
                    <span>📦</span>
                    <span>بینینی مەوادە کەمبووەکان</span>
                    <span>←</span>
                </a>
            </div>
        </div>
    @endif

    {{-- ٤. بەشە سەرەکییەکان: وەسڵە کاراکان + وەستاکانی ئەمڕۆ --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">

        {{-- وەسڵە کاراکان (لە دروستکردندا و چاوەڕوانی) --}}
        <div class="lg:col-span-2 space-y-4">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
                <div class="p-4 sm:p-4.5 border-b border-slate-100 flex items-center justify-between bg-slate-50/60">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-xl bg-blue-600 text-white flex items-center justify-center font-bold text-sm shadow-xs shrink-0">
                            ⚙️
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="font-black text-sm text-slate-900">دوایین وەسڵە کاراکانی کارگە</h3>
                                <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-200 font-mono">
                                    {{ $activeOrders->count() }} وەسڵ
                                </span>
                            </div>
                            <p class="text-[11px] text-slate-400 font-medium mt-0.5">ئەو داواکارییانەی پێویستیان بە دروستکردن هەیە لە ناو کارگە</p>
                        </div>
                    </div>
                    <a href="{{ route('workshop.orders') }}"
                       class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl text-xs font-bold text-blue-600 hover:text-blue-800 hover:bg-blue-50 transition-all">
                        <span>هەموو وەسڵەکان</span>
                        <span>←</span>
                    </a>
                </div>

                @if ($activeOrders->isEmpty())
                    <div class="p-12 text-center text-slate-400 text-xs font-medium">
                        <div class="text-3xl mb-2">🎉</div>
                        هیچ وەسڵێکی چاوەڕوانکراو یان لە کاردا نییە.
                    </div>
                @else
                    <div class="divide-y divide-slate-100">
                        @foreach ($activeOrders as $order)
                            <div class="p-4 hover:bg-slate-50/90 transition-all flex flex-col md:flex-row md:items-center justify-between gap-3.5 group">
                                <div class="min-w-0 flex-1">
                                    {{-- دێڕی سەرەوە: ژمارەی وەسڵ و دۆخ و بەروار --}}
                                    <div class="flex items-center gap-2 flex-wrap mb-1.5">
                                        <span class="font-black text-sm text-slate-900 font-mono tracking-tight bg-slate-100 px-2.5 py-0.5 rounded-lg border border-slate-200">
                                            #{{ $order->invoice_no }}
                                        </span>

                                        @if($order->status === 'in_production')
                                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-blue-100 text-blue-800 border border-blue-200 flex items-center gap-1">
                                                <span>⚙️</span>
                                                <span>لە دروستکردندا</span>
                                            </span>
                                        @elseif($order->status === 'ready')
                                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200 flex items-center gap-1">
                                                <span>✅</span>
                                                <span>ئامادەیە</span>
                                            </span>
                                        @else
                                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800 border border-amber-200 flex items-center gap-1">
                                                <span>⏳</span>
                                                <span>چاوەڕوانە</span>
                                            </span>
                                        @endif

                                        <span class="text-slate-300">•</span>
                                        <span class="text-xs text-slate-600 font-medium">
                                            کڕیار: <b class="text-slate-900 font-bold">{{ $order->customer?->name ?? 'نەناسراو' }}</b>
                                        </span>

                                        @if ($order->delivery_date)
                                            <span class="text-slate-300">•</span>
                                            <span class="text-rose-600 font-bold font-mono text-[11px] flex items-center gap-1">
                                                <span>📅</span>
                                                <span>گەیاندن: {{ $order->delivery_date->format('Y/m/d') }}</span>
                                            </span>
                                        @endif
                                    </div>

                                    {{-- کەلوپەلەکان بە شێوازی بۆکسی ڕێک و قیاسی تایبەت --}}
                                    <div class="flex flex-wrap gap-1.5 mt-2">
                                        @foreach ($order->items as $it)
                                            <div class="inline-flex items-center gap-1 bg-white border border-slate-200 px-2.5 py-1 rounded-lg text-xs font-semibold text-slate-800 shadow-2xs">
                                                <span class="text-slate-500">▫️</span>
                                                <span class="font-bold text-slate-900">{{ $it->item_name }}</span>
                                                <span class="text-blue-700 font-mono font-bold">({{ fmt_num($it->qty) }} {{ $it->unit_name }})</span>
                                                @if ($it->measurement_label && $it->measurement_label !== '—')
                                                    <span class="text-indigo-600 font-mono text-[11px] bg-indigo-50 border border-indigo-100 px-1 rounded">
                                                        {{ $it->measurement_label }}
                                                    </span>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                {{-- دوگمەی وردەکاری و دەستپێکردن --}}
                                <div class="self-end md:self-center shrink-0">
                                    <a href="{{ route('workshop.orders') }}"
                                       class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold bg-white group-hover:bg-blue-600 text-slate-700 group-hover:text-white border border-slate-200 group-hover:border-blue-700 shadow-2xs transition-all">
                                        <span>وردەکاری و کارکردن</span>
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
                <div class="p-4 sm:p-4.5 border-b border-slate-100 flex items-center justify-between bg-slate-50/60">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-xl bg-indigo-600 text-white flex items-center justify-center font-bold text-sm shadow-xs shrink-0">
                            👷
                        </div>
                        <div>
                            <h3 class="font-black text-sm text-slate-900">کارمەندانی کارگە</h3>
                            <p class="text-[11px] text-slate-400 font-medium">ئامادەبوونی ئەمڕۆ</p>
                        </div>
                    </div>
                    <a href="{{ route('workshop.employees') }}"
                       class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl text-xs font-bold text-indigo-600 hover:text-indigo-800 hover:bg-indigo-50 transition-all">
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
                        @foreach ($employees->take(6) as $emp)
                            @php
                                $todayAtt = $emp->attendances->first();
                            @endphp
                            <div class="p-3.5 hover:bg-slate-50 transition-colors flex items-center justify-between gap-3">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <div class="w-8 h-8 rounded-xl bg-slate-100 border border-slate-200 flex items-center justify-center font-black text-slate-700 text-xs shrink-0">
                                        {{ mb_substr($emp->name, 0, 1) }}
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-bold text-xs text-slate-900 truncate">{{ $emp->name }}</div>
                                        <div class="text-[11px] text-slate-400 font-medium">{{ $emp->job_title_label }}</div>
                                    </div>
                                </div>
                                <div class="shrink-0">
                                    @if ($todayAtt && $todayAtt->status === 'present')
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200 flex items-center gap-1">
                                            <span>●</span>
                                            <span>ئامادەیە</span>
                                        </span>
                                    @elseif ($todayAtt && $todayAtt->status === 'leave')
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200 flex items-center gap-1">
                                            <span>🏖️</span>
                                            <span>ئیجازە</span>
                                        </span>
                                    @elseif ($todayAtt && $todayAtt->status === 'absent')
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-200 flex items-center gap-1">
                                            <span>✕</span>
                                            <span>نەهاتووە</span>
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
