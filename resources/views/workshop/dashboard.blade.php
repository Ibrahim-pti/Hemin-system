@extends('layouts.menu')
@section('title', 'داشبۆردی سەرەکی کارگە')

@section('content')
<div class="space-y-4 sm:space-y-6">


    {{-- ٢. کارتە ئامارییەکان --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-2.5 sm:gap-4">
        {{-- ١. چاوەڕوانە --}}
        <a href="{{ route('workshop.orders') }}?status=confirmed"
           class="bg-white rounded-2xl p-3.5 sm:p-4.5 border border-slate-200 hover:border-amber-400 transition-all hover:shadow-md group">
            <div class="flex items-center justify-between mb-1.5 sm:mb-2">
                <span class="text-[11px] sm:text-xs font-bold text-slate-600 truncate">چاوەڕوانی دروستکردن</span>
                <span class="size-7 sm:size-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-sm sm:text-base font-bold border border-amber-200/60 group-hover:scale-110 transition-transform shrink-0">⏳</span>
            </div>
            <div class="num text-xl sm:text-2xl md:text-3xl font-black text-amber-600">{{ fmt_num($pendingCount) }}</div>
            <div class="text-[10px] sm:text-[11px] text-slate-400 font-medium mt-0.5 sm:mt-1 truncate">ئیشی نوێی پەسەندکراو</div>
        </a>

        {{-- ٢. لە کاردایە (دروستدەکرێت) --}}
        <a href="{{ route('workshop.orders') }}?status=in_production"
           class="bg-white rounded-2xl p-3.5 sm:p-4.5 border border-slate-200 hover:border-blue-400 transition-all hover:shadow-md group">
            <div class="flex items-center justify-between mb-1.5 sm:mb-2">
                <span class="text-[11px] sm:text-xs font-bold text-slate-600 truncate">لە ژێر کاردایە</span>
                <span class="size-7 sm:size-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-sm sm:text-base font-bold border border-blue-200/60 group-hover:scale-110 transition-transform shrink-0">⚙️</span>
            </div>
            <div class="num text-xl sm:text-2xl md:text-3xl font-black text-blue-600">{{ fmt_num($inProductionCount) }}</div>
            <div class="text-[10px] sm:text-[11px] text-slate-400 font-medium mt-0.5 sm:mt-1 truncate">وەستاکان کاری لەسەر دەکەن</div>
        </a>

        {{-- ٣. تەواوبوو (ئامادەیە) --}}
        <a href="{{ route('workshop.orders') }}?status=ready"
           class="bg-white rounded-2xl p-3.5 sm:p-4.5 border border-slate-200 hover:border-emerald-400 transition-all hover:shadow-md group">
            <div class="flex items-center justify-between mb-1.5 sm:mb-2">
                <span class="text-[11px] sm:text-xs font-bold text-slate-600 truncate">ئامادەیە بۆ وەرگرتن</span>
                <span class="size-7 sm:size-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-sm sm:text-base font-bold border border-emerald-200/60 group-hover:scale-110 transition-transform shrink-0">✅</span>
            </div>
            <div class="num text-xl sm:text-2xl md:text-3xl font-black text-emerald-600">{{ fmt_num($readyCount) }}</div>
            <div class="text-[10px] sm:text-[11px] text-slate-400 font-medium mt-0.5 sm:mt-1 truncate">دروستکراوە و ئامادەیە</div>
        </a>

        {{-- ٤. ڕادەستکراو --}}
        <a href="{{ route('workshop.orders') }}?status=delivered"
           class="bg-white rounded-2xl p-3.5 sm:p-4.5 border border-slate-200 hover:border-slate-400 transition-all hover:shadow-md group">
            <div class="flex items-center justify-between mb-1.5 sm:mb-2">
                <span class="text-[11px] sm:text-xs font-bold text-slate-600 truncate">ئەمڕۆ ڕادەستکراوە</span>
                <span class="size-7 sm:size-9 rounded-xl bg-slate-50 text-slate-600 flex items-center justify-center text-sm sm:text-base font-bold border border-slate-200/60 group-hover:scale-110 transition-transform shrink-0">🚚</span>
            </div>
            <div class="num text-xl sm:text-2xl md:text-3xl font-black text-slate-800">{{ fmt_num($deliveredCount) }}</div>
            <div class="text-[10px] sm:text-[11px] text-slate-400 font-medium mt-0.5 sm:mt-1 truncate">کارە تەواوکراوەکان</div>
        </a>
    </div>

    {{-- ٣. ئاگاداری مەوادە کەمبووەکان --}}
    @if ($lowStockMaterials->isNotEmpty())
        <div class="bg-gradient-to-r from-rose-50 via-amber-50 to-orange-50 rounded-2xl p-4 sm:p-4.5 border border-rose-200 shadow-xs">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 mb-3">
                <div class="flex items-center gap-2.5">
                    <span class="flex size-8 sm:size-9 items-center justify-center rounded-xl bg-rose-600 text-white font-bold text-sm sm:text-base shadow-xs shrink-0">
                        ⚠️
                    </span>
                    <div>
                        <h3 class="font-black text-xs sm:text-sm text-rose-900 flex items-center gap-2 flex-wrap">
                            <span>ئاگاداری: مەوادە کەمبووەکانی کارگە</span>
                            <span class="px-2 py-0.5 rounded-full bg-rose-100 text-rose-800 text-[10px] sm:text-[11px] font-black border border-rose-200">
                                {{ $lowStockMaterials->count() }} جۆر مەواد
                            </span>
                        </h3>
                        <p class="text-[11px] sm:text-xs text-rose-700 font-medium mt-0.5">ئەم مەوادانە لە سنووری کەمترین بڕی پێویست کەمتریان ماوە</p>
                    </div>
                </div>
                <a href="{{ route('workshop.materials') }}" class="text-xs font-bold text-rose-800 hover:text-rose-950 underline flex items-center gap-1 self-end sm:self-auto">
                    <span>بەڕێوەبردنی مەوادەکان</span>
                    <span>←</span>
                </a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-2.5 sm:gap-3">
                @foreach ($lowStockMaterials as $lowMat)
                    <div class="bg-white rounded-xl p-3 border border-rose-200/80 flex items-center justify-between gap-2 shadow-2xs hover:border-rose-300 transition-all">
                        <div class="min-w-0 flex-1">
                            <div class="font-black text-xs text-slate-800 truncate">{{ $lowMat->name }}</div>
                            <div class="text-[11px] text-slate-400 font-mono font-medium">{{ $lowMat->code }}</div>
                            <div class="text-xs font-black text-rose-600 mt-1 flex items-center gap-1.5 flex-wrap">
                                <span>ماوە: {{ fmt_num($lowMat->stock_qty) }} {{ $lowMat->unit?->name }}</span>
                                <span class="text-slate-400 font-normal text-[10px]">(سنوور: {{ fmt_num($lowMat->min_qty) }})</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- ٤. بەشە سەرەکییەکان: وەسڵە کاراکان + وەستاکانی ئەمڕۆ --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">

        {{-- وەسڵە کاراکان (لە دروستکردندا و چاوەڕوانی) --}}
        <div class="lg:col-span-2 space-y-4">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
                <div class="p-3.5 sm:p-4 border-b border-slate-100 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="size-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-sm shrink-0">
                            ⚙️
                        </span>
                        <div>
                            <h3 class="font-black text-xs sm:text-sm text-slate-800">دوایین وەسڵە چالاکەکانی کارگە</h3>
                            <p class="text-[10px] sm:text-[11px] text-slate-400 font-medium">ئەو کارانەی ئێستا لە دروستکردندا یان چاوەڕوانیدان</p>
                        </div>
                    </div>
                    <a href="{{ route('workshop.orders') }}" class="text-xs font-bold text-blue-600 hover:text-blue-800">
                        هەمووان ←
                    </a>
                </div>

                @if ($activeOrders->isEmpty())
                    <div class="p-8 text-center text-slate-400 text-xs font-medium">
                        هیچ وەسڵێکی کارا لە کارگەدا نییە لەم کاتەدا.
                    </div>
                @else
                    <div class="divide-y divide-slate-100">
                        @foreach ($activeOrders as $order)
                            <div class="p-3.5 sm:p-4 hover:bg-slate-50/80 transition-colors flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="font-black text-sm text-slate-900">وەسڵی #{{ $order->invoice_no }}</span>
                                        <span class="px-2 py-0.5 rounded-full text-[10px] sm:text-[11px] font-bold {{ $order->status === 'in_production' ? 'bg-blue-100 text-blue-800 border border-blue-200' : ($order->status === 'ready' ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : 'bg-amber-100 text-amber-800 border border-amber-200') }}">
                                            {{ $order->status_label }}
                                        </span>
                                    </div>
                                    <div class="text-xs text-slate-500 font-medium mt-1">
                                        کڕیار: <span class="font-bold text-slate-800">{{ $order->customer?->name ?? 'نەناسراو' }}</span>
                                        @if ($order->delivery_date)
                                            <span class="text-rose-600 font-bold mr-2">گەیاندن: {{ $order->delivery_date->format('Y/m/d') }}</span>
                                        @endif
                                    </div>
                                    <div class="text-[11px] text-slate-400 mt-1 flex flex-wrap gap-1.5">
                                        @foreach ($order->items->take(3) as $it)
                                            <span class="bg-slate-100 px-2 py-0.5 rounded-md text-slate-700">
                                                {{ $it->item_name }} ({{ fmt_num($it->qty) }} {{ $it->unit_name }})
                                            </span>
                                        @endforeach
                                        @if ($order->items->count() > 3)
                                            <span class="text-slate-400 text-[10px] self-center">+{{ $order->items->count() - 3 }} دانەی تر</span>
                                        @endif
                                    </div>
                                </div>

                                <div class="self-end sm:self-center shrink-0">
                                    <a href="{{ route('workshop.orders') }}" class="btn btn-ghost !py-1.5 !px-3 text-xs font-bold border border-slate-200 text-slate-700 hover:bg-slate-100">
                                        بینینی وردەکاری
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
                <div class="p-3.5 sm:p-4 border-b border-slate-100 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="size-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-sm shrink-0">
                            👷
                        </span>
                        <div>
                            <h3 class="font-black text-xs sm:text-sm text-slate-800">وەستاکانی کارگە</h3>
                            <p class="text-[10px] sm:text-[11px] text-slate-400 font-medium">{{ $employees->count() }} کەسی چالاک</p>
                        </div>
                    </div>
                    <a href="{{ route('workshop.employees') }}" class="text-xs font-bold text-blue-600 hover:text-blue-800">
                        هەمووان ←
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
                                    <div class="font-bold text-xs text-slate-800 truncate">{{ $emp->name }}</div>
                                    <div class="text-[11px] text-slate-400 font-medium mt-0.5">{{ $emp->job_title_label }}</div>
                                </div>
                                <div class="shrink-0">
                                    @if ($todayAtt && $todayAtt->status === 'present')
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700 border border-emerald-200">
                                            ئامادەیە ✔️
                                        </span>
                                    @elseif ($todayAtt && $todayAtt->status === 'absent')
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-700 border border-rose-200">
                                            نەهاتووە ❌
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-500 border border-slate-200">
                                            چالاکە
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

