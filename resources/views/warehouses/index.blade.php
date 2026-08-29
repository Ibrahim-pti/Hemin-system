@extends('layouts.app')
@section('title', 'کۆگا')

@section('content')
<div class="space-y-5 sm:space-y-6">

    {{-- ١. هێڵی سەرەوە: ناونیشان --}}
    <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-xs flex items-center justify-between gap-4">
        <div class="flex items-center gap-3.5">
            <div class="size-12 rounded-2xl bg-linear-to-br from-amber-500 via-orange-500 to-indigo-600 text-white flex items-center justify-center text-2xl shadow-md shadow-orange-500/20 shrink-0">
                🏭
            </div>
            <div>
                <div class="flex items-center gap-2 flex-wrap">
                    <h1 class="text-lg sm:text-xl font-black text-slate-900">کۆگا</h1>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200/80">
                        مەعمەل و کۆگا
                    </span>
                </div>
                <p class="text-xs text-slate-500 mt-1 font-medium">
                    بینینی ڕاستەوخۆی مەوادی خاو، وەسڵەکانی ژێر کارکردن، تەواوکراوەکان، و کەلوپەلی بەردەست
                </p>
            </div>
        </div>
    </div>

    {{-- ٢. ٤ کارتی ئاماری سەرەکی و ڕاستەوخۆ --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
        {{-- لە ژێر دروستکردندایە --}}
        <div class="bg-indigo-50/70 rounded-2xl p-4 border border-indigo-200/80 shadow-xs">
            <div class="text-xs font-bold text-indigo-800 mb-1 flex items-center gap-1.5">
                <span class="size-2 rounded-full bg-indigo-500 animate-pulse"></span>
                <span>لە ژێر دروستکردندایە</span>
            </div>
            <div class="text-2xl font-black text-indigo-900 font-mono">{{ $inProductionCount }} <span class="text-xs font-bold text-indigo-600 font-sans">وەسڵ</span></div>
        </div>

        {{-- تەواوکراوە و ئامادەیە --}}
        <div class="bg-emerald-50/70 rounded-2xl p-4 border border-emerald-200/80 shadow-xs">
            <div class="text-xs font-bold text-emerald-800 mb-1 flex items-center gap-1.5">
                <span>✅</span>
                <span>تەواوکراو (ئامادەی بەستن)</span>
            </div>
            <div class="text-2xl font-black text-emerald-900 font-mono">{{ $readyCount }} <span class="text-xs font-bold text-emerald-600 font-sans">وەسڵ</span></div>
        </div>

        {{-- کەلوپەل لە کۆگا --}}
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs">
            <div class="text-xs font-bold text-slate-500 mb-1 flex items-center gap-1.5">
                <span>📦</span>
                <span>جۆری کەلوپەل و مەواد</span>
            </div>
            <div class="text-2xl font-black text-slate-900 font-mono">{{ $totalItemsCount }} <span class="text-xs font-bold text-slate-500 font-sans">جۆر</span></div>
        </div>

        {{-- مەوادی کەمبوو --}}
        <div class="bg-rose-50/70 rounded-2xl p-4 border border-rose-200/80 shadow-xs">
            <div class="text-xs font-bold text-rose-800 mb-1 flex items-center gap-1.5">
                <span>⚠️</span>
                <span>مەواد و کەلوپەلی کەمبوو</span>
            </div>
            <div class="text-2xl font-black text-rose-700 font-mono">{{ $lowStockItems->count() }} <span class="text-xs font-bold text-rose-500 font-sans">کەمبووە</span></div>
        </div>
    </div>

    {{-- ٣. ئاگاداری کەمی مەواد (هاوشێوەی داشبۆردی کارگە) --}}
    @if ($lowStockItems->isNotEmpty())
        <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-3.5">
                <div style="width: 48px; height: 48px; min-width: 48px; min-height: 48px;"
                     class="rounded-2xl bg-rose-600 text-white flex items-center justify-center text-2xl shadow-md shrink-0">
                    ⚠️
                </div>
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <h2 class="text-base sm:text-lg font-black text-slate-900">ئاگاداری: مەوادە کەمبووەکانی مەخزەن و کۆگا</h2>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-50 text-rose-800 border border-rose-200 font-mono">
                            {{ $lowStockItems->count() }} جۆر مەواد
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 mt-0.5 font-medium">
                        بڕی هەندێک مەواد لە مەخزەندا لە سنووری کەمترین پێویست کەمتریان ماوە و پێویستیان بە پڕکردنەوەیە.
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2.5 flex-wrap shrink-0">
                <a href="{{ route('reports.show', 'workshop_materials') }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold bg-rose-600 hover:bg-rose-700 text-white shadow-xs transition-all border border-rose-700">
                    <span>📦</span>
                    <span>بینینی مەوادە کەمبووەکان</span>
                    <span>←</span>
                </a>
            </div>
        </div>
    @endif

    {{-- ٤. چ ئیشێک دەکرێت و چ تەواو کراوە (Live Production Grid) --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
        {{-- بەشی ١: لە ژێر دروستکردندایە (لە کارگە کار دەکرێت) --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden flex flex-col">
            <div class="p-4 border-b border-slate-100 bg-indigo-50/50 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="size-3 rounded-full bg-indigo-600 animate-pulse"></span>
                    <h3 class="font-black text-sm text-slate-900">وەسڵە کاراکان (لە ژێر دروستکردندان)</h3>
                    <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-indigo-100 text-indigo-800 font-mono">
                        {{ $ordersInProduction->count() }} وەسڵ
                    </span>
                </div>
                <a href="{{ route('workshop.orders') }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-800">
                    هەموو کارەکان ←
                </a>
            </div>

            <div class="p-4 space-y-3 flex-1 overflow-y-auto max-h-[380px]">
                @forelse($ordersInProduction as $order)
                    <div class="p-3.5 rounded-xl border border-indigo-100 bg-indigo-50/20 hover:bg-indigo-50/50 transition-colors flex flex-col gap-2">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="font-mono font-black text-xs text-indigo-700 bg-white px-2 py-0.5 rounded-md border border-indigo-200 shadow-2xs">
                                    #{{ $order->id }}
                                </span>
                                <span class="font-black text-xs text-slate-900">{{ $order->customer?->name ?: 'کڕیاری گشتی' }}</span>
                            </div>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-100 text-indigo-800">
                                ⚙️ ژێر کارکردن
                            </span>
                        </div>

                        {{-- بڕگەکانی دروستکردن --}}
                        <div class="text-xs text-slate-600 space-y-1 bg-white p-2 rounded-lg border border-slate-100">
                            @foreach($order->items as $item)
                                <div class="flex items-center justify-between">
                                    <span class="font-medium text-slate-800">• {{ $item->item_name }}</span>
                                    <span class="font-mono font-bold text-slate-500 text-[11px]">
                                        {{ fmt_num($item->quantity) }} {{ $item->unit_label }}
                                        @if($item->width && $item->height)
                                            [{{ $item->width }} × {{ $item->height }}]
                                        @endif
                                    </span>
                                </div>
                            @endforeach
                        </div>

                        <div class="flex items-center justify-between text-[11px] text-slate-400 font-medium pt-1">
                            <span>بەرواری داواکاری: {{ fmt_date($order->order_date) }}</span>
                            @if($order->delivery_date)
                                <span class="text-indigo-600 font-bold">گەیاندن: {{ fmt_date($order->delivery_date) }}</span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="py-10 text-center text-xs text-slate-400">
                        <div class="text-2xl mb-1">🎉</div>
                        <div>لە ئێستادا هیچ وەسڵێک لە ژێر کارکردندا نییە.</div>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- بەشی ٢: تەواوکراوە و ئامادەیە (چاوەڕێی وەرگرتن یان گەیاندنە) --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden flex flex-col">
            <div class="p-4 border-b border-slate-100 bg-emerald-50/50 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="size-3 rounded-full bg-emerald-500"></span>
                    <h3 class="font-black text-sm text-slate-900">وەسڵە تەواوکراوەکان (ئامادەی ڕادەستکردن)</h3>
                    <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 font-mono">
                        {{ $ordersReady->count() }} وەسڵ
                    </span>
                </div>
                <a href="{{ route('workshop.orders') }}" class="text-xs font-bold text-emerald-700 hover:text-emerald-900">
                    هەموو وەسڵەکان ←
                </a>
            </div>

            <div class="p-4 space-y-3 flex-1 overflow-y-auto max-h-[380px]">
                @forelse($ordersReady as $order)
                    <div class="p-3.5 rounded-xl border border-emerald-100 bg-emerald-50/20 hover:bg-emerald-50/50 transition-colors flex flex-col gap-2">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="font-mono font-black text-xs text-emerald-700 bg-white px-2 py-0.5 rounded-md border border-emerald-200 shadow-2xs">
                                    #{{ $order->id }}
                                </span>
                                <span class="font-black text-xs text-slate-900">{{ $order->customer?->name ?: 'کڕیاری گشتی' }}</span>
                            </div>
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                ✅ تەواوکراوە
                            </span>
                        </div>

                        {{-- بڕگەکان --}}
                        <div class="text-xs text-slate-600 space-y-1 bg-white p-2 rounded-lg border border-slate-100">
                            @foreach($order->items as $item)
                                <div class="flex items-center justify-between">
                                    <span class="font-medium text-slate-800">• {{ $item->item_name }}</span>
                                    <span class="font-mono font-bold text-slate-500 text-[11px]">
                                        {{ fmt_num($item->quantity) }} {{ $item->unit_label }}
                                    </span>
                                </div>
                            @endforeach
                        </div>

                        <div class="flex items-center justify-between text-[11px] text-slate-400 font-medium pt-1">
                            <span>کڕیار: {{ $order->customer?->phone ?: '—' }}</span>
                            <span class="text-emerald-700 font-bold">ئامادەی بەستن یان وەرگرتنە</span>
                        </div>
                    </div>
                @empty
                    <div class="py-10 text-center text-xs text-slate-400">
                        <div class="text-2xl mb-1">📦</div>
                        <div>هیچ وەسڵێکی ئامادەکراو چاوەڕێ نییە.</div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- ٥. کارتی تایبەتی مەعمەلی دروستکردن --}}
    <div class="bg-linear-to-br from-white via-indigo-50/20 to-blue-50/30 rounded-3xl p-5 sm:p-6 border-2 border-indigo-200 shadow-xs">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="size-16 rounded-2xl bg-indigo-600 text-white flex items-center justify-center text-3xl shadow-md shadow-indigo-600/30 shrink-0">
                    🏭
                </div>
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <h2 class="text-base sm:text-lg font-black text-slate-900">{{ $workshopWarehouse?->name ?: 'شوێنی دروستکردن' }}</h2>
                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-indigo-100 text-indigo-800 border border-indigo-200">
                            مەعمەلی پیشەسازی و مەوادی خاو
                        </span>
                    </div>
                    <div class="flex items-center gap-4 text-xs text-slate-500 font-medium mt-1">
                        <span>📍 {{ $workshopWarehouse?->location ?: 'شەقامی ١٠٠ مەتری - نزیک نەخۆشخانەی ڕزگاری' }}</span>
                        <span>•</span>
                        <span class="font-mono font-bold text-indigo-700">{{ fmt_num($workshopWarehouse?->movements_count ?? 0) }} جوڵەی مەواد</span>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-2 flex-wrap">
                <a href="{{ route('workshop.employees') }}"
                   class="px-4 py-2.5 rounded-xl text-xs font-bold bg-indigo-600 hover:bg-indigo-700 text-white shadow-xs inline-flex items-center gap-1.5 transition-all">
                    <span>👷</span>
                    <span>بینینی وەستاکان</span>
                </a>

                <a href="{{ route('reports.show', 'workshop_production') }}"
                   class="px-4 py-2.5 rounded-xl text-xs font-bold bg-white hover:bg-indigo-50 text-indigo-700 border border-indigo-200 inline-flex items-center gap-1.5 transition-all">
                    <span>⚙️</span>
                    <span>ڕاپۆرتی دروستکردن</span>
                </a>

                <a href="{{ route('reports.show', 'workshop_materials') }}"
                   class="px-4 py-2.5 rounded-xl text-xs font-bold bg-white hover:bg-indigo-50 text-indigo-700 border border-indigo-200 inline-flex items-center gap-1.5 transition-all">
                    <span>🧱</span>
                    <span>ڕاپۆرتی مەوادی خاو</span>
                </a>
            </div>
        </div>
    </div>

    {{-- ٦. خشتەی کەلوپەل و مەوادە بەردەستەکانی کۆگا --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden"
         x-data="{ search: '', catFilter: 'all' }">
        <div class="p-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-slate-50/60">
            <div class="flex items-center gap-2">
                <span class="text-base">📦</span>
                <h3 class="font-black text-sm text-slate-800">کەلوپەل و مەوادە بەردەستەکان لە کۆگا</h3>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-200/80 text-slate-700 font-mono">
                    {{ $allItems->count() }} ماددە
                </span>
            </div>

            <div class="flex items-center gap-2 flex-wrap">
                <input type="text" x-model="search"
                       class="text-xs px-3.5 py-2 rounded-xl border border-slate-200 bg-white focus:outline-hidden focus:border-blue-500 font-medium text-right w-full sm:w-64"
                       placeholder="🔍 گەڕان بە ناو، جۆر، بارکۆد...">
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead class="bg-slate-50 text-slate-600 border-b border-slate-200 font-black">
                    <tr>
                        <th class="p-3.5 w-12 text-center">#</th>
                        <th class="p-3.5">ناوی کەلوپەل / مەواد</th>
                        <th class="p-3.5">جۆر / بەش</th>
                        <th class="p-3.5 text-center">یەکە</th>
                        <th class="p-3.5 text-center">بڕی بەردەست لە کۆگا</th>
                        <th class="p-3.5 text-center">دۆخی مەخزەن</th>
                        <th class="p-3.5 text-left">نرخی فرۆشتن</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($allItems as $index => $item)
                        @php
                            $isLow = $item->is_low;
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition-colors"
                            x-show="!search || '{{ strtolower($item->name) }}'.includes(search.toLowerCase()) || '{{ strtolower($item->category?->name) }}'.includes(search.toLowerCase())">
                            <td class="p-3.5 text-center font-mono font-bold text-slate-400">
                                {{ $index + 1 }}
                            </td>
                            <td class="p-3.5 font-black text-slate-900">
                                <div class="flex items-center gap-2">
                                    <span>{{ $item->name }}</span>
                                    @if($item->barcode)
                                        <span class="font-mono text-[10px] text-slate-400 bg-slate-100 px-1.5 py-0.5 rounded">[{{ $item->barcode }}]</span>
                                    @endif
                                </div>
                            </td>
                            <td class="p-3.5 text-slate-600 font-medium">
                                <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 text-[11px] font-bold">
                                    {{ $item->category?->name ?: 'گشتی' }}
                                </span>
                            </td>
                            <td class="p-3.5 text-center text-slate-500 font-bold">
                                {{ $item->unit?->name ?: 'دانە' }}
                            </td>
                            <td class="p-3.5 text-center">
                                <span class="font-mono font-black text-xs px-2.5 py-0.5 rounded-lg {{ $isLow ? 'bg-rose-100 text-rose-800 border border-rose-200' : 'bg-slate-100 text-slate-900 border border-slate-200/60' }}">
                                    {{ fmt_num($item->current_stock) }}
                                </span>
                            </td>
                            <td class="p-3.5 text-center">
                                @if($isLow)
                                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-rose-100 text-rose-800 border border-rose-200">
                                        ⚠️ کەمبووە
                                    </span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        بەردەستە ✔️
                                    </span>
                                @endif
                            </td>
                            <td class="p-3.5 text-left font-mono font-bold text-slate-900">
                                {{ fmt_money($item->sale_price, $item->currency ?? 'IQD') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-10 text-center text-slate-400 font-medium">هیچ کەلوپەلێک لە کۆگا تۆمار نەکراوە.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
