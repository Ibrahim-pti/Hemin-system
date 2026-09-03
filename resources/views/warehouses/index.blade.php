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

        <div class="flex items-center gap-2 flex-wrap shrink-0">
            <a href="{{ route('counts.index') }}"
               class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 transition-all shadow-2xs">
                <span>📋</span>
                <span>جەردی کۆگا</span>
            </a>
            <a href="{{ route('warehouses.create') }}"
               class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold bg-blue-600 hover:bg-blue-700 text-white transition-all shadow-xs">
                <span>+</span>
                <span>کۆگای نوێ</span>
            </a>
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
                <a href="{{ route('reports.show', ['report' => 'workshop_production', 'status' => 'in_production']) }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-800">
                    هەموو کارەکان ←
                </a>
            </div>

            <div class="p-4 space-y-3 flex-1 overflow-y-auto max-h-[380px]">
                @forelse($ordersInProduction as $order)
                    <div class="p-3.5 rounded-xl border border-indigo-100 bg-indigo-50/20 hover:bg-indigo-50/50 transition-colors flex flex-col gap-2.5">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="font-mono font-black text-xs text-indigo-700 bg-white px-2 py-0.5 rounded-md border border-indigo-200 shadow-2xs">
                                    #{{ $order->invoice_no ?? $order->id }}
                                </span>
                                <span class="font-black text-xs text-slate-900">{{ $order->customer?->name ?: 'کڕیاری گشتی' }}</span>
                            </div>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-100 text-indigo-800 animate-pulse">
                                ⚙️ ژێر کارکردن
                            </span>
                        </div>

                        {{-- بڕگەکانی دروستکردن بە وێنە و قیاس --}}
                        <div class="space-y-1.5 bg-white p-2.5 rounded-xl border border-slate-100 shadow-2xs">
                            @foreach($order->items as $item)
                                @php $imgUrl = $item->imageUrl(); @endphp
                                <div class="flex items-center justify-between gap-2 text-xs">
                                    <div class="flex items-center gap-2 min-w-0">
                                        @if($imgUrl)
                                            <img src="{{ $imgUrl }}" class="size-8 rounded-lg object-cover border border-slate-200 shrink-0">
                                        @else
                                            <div class="size-8 rounded-lg bg-slate-100 text-slate-400 flex items-center justify-center text-xs shrink-0">🖼️</div>
                                        @endif
                                        <span class="font-bold text-slate-800 truncate">{{ $item->item_name }}</span>
                                    </div>
                                    <div class="flex items-center gap-1.5 shrink-0 font-mono text-[11px]">
                                        @if($item->width && $item->height)
                                            <span class="text-indigo-600 font-bold">[{{ $item->width }} × {{ $item->height }}]</span>
                                        @endif
                                        <span class="font-black text-slate-700 bg-slate-100 px-1.5 py-0.5 rounded">
                                            {{ fmt_num($item->qty ?? $item->quantity) }} {{ $item->unit_label ?: $item->unit_name ?: 'دانە' }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="flex items-center justify-between text-[11px] text-slate-500 font-medium pt-1 border-t border-indigo-100/60">
                            <span>📅 داواکاری: <b class="font-mono text-slate-700">{{ fmt_date($order->order_date) }}</b></span>
                            @if($order->delivery_date)
                                <span class="text-indigo-700 font-bold font-mono">گەیاندن: {{ fmt_date($order->delivery_date) }}</span>
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
                <a href="{{ route('reports.show', ['report' => 'workshop_production', 'status' => 'ready']) }}" class="text-xs font-bold text-emerald-700 hover:text-emerald-900">
                    هەموو وەسڵەکان ←
                </a>
            </div>

            <div class="p-4 space-y-3 flex-1 overflow-y-auto max-h-[380px]">
                @forelse($ordersReady as $order)
                    <div class="p-3.5 rounded-xl border border-emerald-100 bg-emerald-50/20 hover:bg-emerald-50/50 transition-colors flex flex-col gap-2.5">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="font-mono font-black text-xs text-emerald-700 bg-white px-2 py-0.5 rounded-md border border-emerald-200 shadow-2xs">
                                    #{{ $order->invoice_no ?? $order->id }}
                                </span>
                                <span class="font-black text-xs text-slate-900">{{ $order->customer?->name ?: 'کڕیاری گشتی' }}</span>
                            </div>
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                ✅ تەواوکراوە
                            </span>
                        </div>

                        {{-- بڕگەکان بە وێنە و قیاس --}}
                        <div class="space-y-1.5 bg-white p-2.5 rounded-xl border border-slate-100 shadow-2xs">
                            @foreach($order->items as $item)
                                @php $imgUrl = $item->imageUrl(); @endphp
                                <div class="flex items-center justify-between gap-2 text-xs">
                                    <div class="flex items-center gap-2 min-w-0">
                                        @if($imgUrl)
                                            <img src="{{ $imgUrl }}" class="size-8 rounded-lg object-cover border border-slate-200 shrink-0">
                                        @else
                                            <div class="size-8 rounded-lg bg-slate-100 text-slate-400 flex items-center justify-center text-xs shrink-0">🖼️</div>
                                        @endif
                                        <span class="font-bold text-slate-800 truncate">{{ $item->item_name }}</span>
                                    </div>
                                    <div class="flex items-center gap-1.5 shrink-0 font-mono text-[11px]">
                                        @if($item->width && $item->height)
                                            <span class="text-indigo-600 font-bold">[{{ $item->width }} × {{ $item->height }}]</span>
                                        @endif
                                        <span class="font-black text-slate-700 bg-slate-100 px-1.5 py-0.5 rounded">
                                            {{ fmt_num($item->qty ?? $item->quantity) }} {{ $item->unit_label ?: $item->unit_name ?: 'دانە' }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="flex items-center justify-between text-[11px] text-slate-500 font-medium pt-1 border-t border-emerald-100/60">
                            <span>📞 <span class="font-mono">{{ $order->customer?->phone ?: 'بێ ژمارە' }}</span></span>
                            <span class="text-emerald-700 font-bold bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-200/70">ئامادەی ڕادەستکردنە</span>
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


    {{-- ٥. خشتەی کەلوپەل و مەوادە بەردەستەکانی کۆگا --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden"
         x-data="{ search: '' }">
        <div class="p-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-slate-50/60">
            <div class="flex items-center gap-2">
                <span class="text-base">📦</span>
                <h3 class="font-black text-sm text-slate-800">کەلوپەل و مەوادە بەردەستەکان لە کۆگا</h3>
            </div>

            <div class="flex items-center gap-2 flex-wrap">
                <input type="text" x-model="search"
                       class="text-xs px-3.5 py-2 rounded-xl border border-slate-200 bg-white focus:outline-hidden focus:border-blue-500 font-medium text-right w-full sm:w-64"
                       placeholder="🔍 گەڕان بە ناو...">
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead class="bg-slate-50 text-slate-600 border-b border-slate-200 font-black">
                    <tr>
                        <th class="p-3.5 w-14 text-center">#</th>
                        <th class="p-3.5">ناوی کەلوپەل / مەواد</th>
                        <th class="p-3.5 text-center w-28">یەکە</th>
                        <th class="p-3.5 text-center w-40">بڕی بەردەست لە کۆگا</th>
                        <th class="p-3.5 text-center w-36">دۆخی مەخزەن</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($allItems as $index => $item)
                        @php
                            $isLow = $item->is_low;
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition-colors"
                            x-show="!search || '{{ strtolower($item->name) }}'.includes(search.toLowerCase()) || '{{ strtolower($item->barcode) }}'.includes(search.toLowerCase())">
                            <td class="p-3.5 text-center font-mono font-bold text-slate-400">
                                {{ $allItems->firstItem() ? ($allItems->firstItem() + $index) : ($index + 1) }}
                            </td>
                            <td class="p-3.5 font-black text-slate-900">
                                <div class="flex items-center gap-2">
                                    <span>{{ $item->name }}</span>
                                    @if($item->barcode)
                                        <span class="font-mono text-[10px] text-slate-400 bg-slate-100 px-1.5 py-0.5 rounded">[{{ $item->barcode }}]</span>
                                    @endif
                                </div>
                            </td>
                            <td class="p-3.5 text-center text-slate-600 font-bold">
                                {{ $item->unit?->name ?: 'دانە' }}
                            </td>
                            <td class="p-3.5 text-center">
                                <span class="font-mono font-black text-xs px-2.5 py-1 rounded-lg {{ (float) $item->current_stock <= 0 ? 'bg-slate-100 text-slate-500 border border-slate-200' : ($isLow ? 'bg-amber-100 text-amber-800 border border-amber-200' : 'bg-slate-100 text-slate-900 border border-slate-200/60') }}">
                                    {{ fmt_num($item->current_stock) }}
                                </span>
                            </td>
                            <td class="p-3.5 text-center">
                                @if((float) $item->current_stock <= 0)
                                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-rose-100 text-rose-800 border border-rose-200 inline-block">
                                        ❌ لە کۆگا نەماوە
                                    </span>
                                @elseif($isLow)
                                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800 border border-amber-200 inline-block">
                                        ⚠️ کەمبووە
                                    </span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200 inline-block">
                                        بەردەستە ✔️
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-10 text-center text-slate-400 font-medium">هیچ کەلوپەلێک لە کۆگا تۆمار نەکراوە.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- بەشی پەڕەبەندی (Pagination) --}}
        @if ($allItems->hasPages())
            <div class="p-4 border-t border-slate-100 bg-slate-50/60 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
                <div class="font-bold text-slate-600">
                    پێشاندانی <span class="font-mono text-slate-900 font-black">{{ $allItems->firstItem() ?? 0 }}</span> تا <span class="font-mono text-slate-900 font-black">{{ $allItems->lastItem() ?? 0 }}</span> لە کۆی <span class="font-mono text-indigo-700 font-black">{{ $allItems->total() }}</span> ماددە
                </div>

                <div class="flex items-center gap-1.5 self-center sm:self-auto">
                    {{-- پەڕەی پێشوو --}}
                    @if ($allItems->onFirstPage())
                        <span class="px-3 py-1.5 rounded-xl text-xs font-bold bg-slate-100 text-slate-400 cursor-not-allowed">
                            → پێشوو
                        </span>
                    @else
                        <a href="{{ $allItems->previousPageUrl() }}"
                           class="px-3 py-1.5 rounded-xl text-xs font-bold bg-white hover:bg-slate-100 text-slate-700 border border-slate-200 transition-all shadow-2xs">
                            → پێشوو
                        </a>
                    @endif

                    {{-- ژمارەی پەڕەکان --}}
                    @foreach ($allItems->getUrlRange(1, $allItems->lastPage()) as $page => $url)
                        @if ($page == $allItems->currentPage())
                            <span style="min-width: 34px; height: 34px;"
                                  class="px-2.5 py-1 rounded-xl bg-blue-600 text-white font-mono font-black text-xs inline-flex items-center justify-center shadow-xs">
                                {{ $page }}
                            </span>
                        @else
                            <a href="{{ $url }}"
                               style="min-width: 34px; height: 34px;"
                               class="px-2.5 py-1 rounded-xl bg-white hover:bg-slate-100 text-slate-800 font-mono font-bold text-xs border border-slate-300 inline-flex items-center justify-center transition-all">
                                {{ $page }}
                            </a>
                        @endif
                    @endforeach

                    {{-- پەڕەی دواتر --}}
                    @if ($allItems->hasMorePages())
                        <a href="{{ $allItems->nextPageUrl() }}"
                           class="px-3 py-1.5 rounded-xl text-xs font-bold bg-white hover:bg-slate-100 text-slate-700 border border-slate-200 transition-all shadow-2xs">
                            دواتر ←
                        </a>
                    @else
                        <span class="px-3 py-1.5 rounded-xl text-xs font-bold bg-slate-100 text-slate-400 cursor-not-allowed">
                            دواتر ←
                        </span>
                    @endif
                </div>
            </div>
        @endif
    </div>

</div>
@endsection
