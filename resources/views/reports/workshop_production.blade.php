@extends('layouts.app')
@section('title', 'ڕاپۆرتی دروستکردن و کارگە')

@section('content')
<div class="space-y-4 sm:space-y-6">

    {{-- ١. هێڵی سەرەوە: ناونیشان --}}
    <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-xs flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div class="flex items-center gap-3.5">
            <div class="size-12 rounded-2xl bg-linear-to-br from-indigo-500 to-blue-600 text-white flex items-center justify-center text-2xl shadow-md shadow-indigo-500/20 shrink-0">
                ⚙️
            </div>
            <div>
                <div class="flex items-center gap-2 flex-wrap">
                    <h1 class="text-lg sm:text-xl font-black text-slate-900">ڕاپۆرتی دروستکردن و کارگە</h1>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200/80">
                        بەرهەمهێنان و قیاسات
                    </span>
                </div>
                <p class="text-xs text-slate-500 mt-1 font-medium">
                    چاودێریکردنی وەسڵەکانی ژێر کارکردن، تەواوکراوەکان، وێنەی بابەتەکان و قیاساتی دروستکردن لە مەعمەل
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('warehouses.index') }}"
               class="px-4 py-2 rounded-xl text-xs font-bold bg-slate-100 hover:bg-slate-200 text-slate-800 border border-slate-300 inline-flex items-center gap-1.5 transition-all">
                <span>🏭</span>
                <span>پەیجی کۆگا</span>
            </a>
        </div>
    </div>

    {{-- پاڵاوتنی بەروار --}}
    @include('reports._filter')

    {{-- ٢. ٤ کارتی ئاماری سەرەکی --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs">
            <div class="text-xs font-bold text-slate-500 mb-1 flex items-center gap-1.5">
                <span>📋</span>
                <span>کۆی وەسڵەکانی کارگە</span>
            </div>
            <div class="text-2xl sm:text-3xl font-black text-slate-900 font-mono">{{ fmt_num($totalCount) }}</div>
        </div>

        <div class="bg-emerald-50/70 rounded-2xl p-4 border border-emerald-200/80 shadow-xs">
            <div class="text-xs font-bold text-emerald-800 mb-1 flex items-center gap-1.5">
                <span class="size-2 rounded-full bg-emerald-500"></span>
                <span>تەواوکراو و ڕادەستکراو</span>
            </div>
            <div class="text-2xl sm:text-3xl font-black text-emerald-900 font-mono">{{ fmt_num($deliveredCount) }}</div>
        </div>

        <div class="bg-indigo-50/70 rounded-2xl p-4 border border-indigo-200/80 shadow-xs">
            <div class="text-xs font-bold text-indigo-800 mb-1 flex items-center gap-1.5">
                <span class="size-2 rounded-full bg-indigo-500 animate-pulse"></span>
                <span>لە ژێر دروستکردندا</span>
            </div>
            <div class="text-2xl sm:text-3xl font-black text-indigo-900 font-mono">{{ fmt_num($inProductionCount) }}</div>
        </div>

        <div class="bg-amber-50/70 rounded-2xl p-4 border border-amber-200/80 shadow-xs">
            <div class="text-xs font-bold text-amber-800 mb-1 flex items-center gap-1.5">
                <span>⏳</span>
                <span>چاوەڕوانکراو / ئامادە</span>
            </div>
            <div class="text-2xl sm:text-3xl font-black text-amber-900 font-mono">{{ fmt_num($pendingCount + $readyCount) }}</div>
        </div>
    </div>

    {{-- ٣. خشتەی سەرەکی وەسڵەکان بە دیزاینێکی ڕێک و خاوێن --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-4 border-b border-slate-100 bg-slate-50/60 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="text-base">📋</span>
                <h3 class="font-black text-sm text-slate-800">لیستی وەسڵەکان و دۆخی دروستکردن</h3>
            </div>
            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-200/80 text-slate-700 font-mono">
                کۆی گشتی: {{ fmt_num($orders->count()) }} وەسڵ
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead class="bg-slate-50 text-slate-600 border-b border-slate-200 font-black">
                    <tr>
                        <th class="p-3.5 text-center w-16">وەسڵ</th>
                        <th class="p-3.5 w-48">کڕیار</th>
                        <th class="p-3.5 w-36">بەرواری داواکاری / گەیاندن</th>
                        <th class="p-3.5">کەلوپەل، وێنە و قیاسات بۆ دروستکردن</th>
                        <th class="p-3.5 text-center w-32">دۆخی کارگە</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($orders as $order)
                        <tr class="hover:bg-slate-50/80 transition-colors align-middle">
                            {{-- ژمارەی وەسڵ --}}
                            <td class="p-3.5 text-center font-mono font-black">
                                <a href="{{ route('orders.show', $order) }}"
                                   class="px-2.5 py-1.5 rounded-xl bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 inline-block transition-colors shadow-2xs font-bold">
                                    #{{ $order->id }}
                                </a>
                            </td>

                            {{-- کڕیار --}}
                            <td class="p-3.5">
                                <div class="font-black text-slate-900 text-xs">
                                    {{ $order->customer?->name ?: 'کڕیاری گشتی' }}
                                </div>
                                @if($order->customer?->phone)
                                    <div class="text-[11px] text-slate-500 font-mono mt-0.5">
                                        {{ $order->customer->phone }}
                                    </div>
                                @endif
                                @if($order->address_snapshot)
                                    <div class="text-[10px] text-slate-400 mt-0.5 line-clamp-1">
                                        📍 {{ $order->address_snapshot }}
                                    </div>
                                @endif
                            </td>

                            {{-- بەروار --}}
                            <td class="p-3.5 font-mono text-xs whitespace-nowrap">
                                <div class="text-slate-600">
                                    <span class="text-slate-400 text-[10px] font-sans">داواکاری:</span> {{ fmt_date($order->order_date) }}
                                </div>
                                @if($order->delivery_date)
                                    <div class="mt-1 font-bold {{ $order->delivery_date->isPast() && $order->status !== 'delivered' ? 'text-rose-600' : 'text-indigo-600' }}">
                                        <span class="text-slate-400 text-[10px] font-sans">گەیاندن:</span> {{ fmt_date($order->delivery_date) }}
                                    </div>
                                @endif
                            </td>

                            {{-- کەلوپەل و وێنە و قیاسات --}}
                            <td class="p-3.5">
                                <div class="flex flex-wrap gap-2">
                                    @foreach($order->items as $it)
                                        @php
                                            $imgUrl = $it->imageUrl();
                                        @endphp
                                        <div class="inline-flex items-center gap-2.5 p-2 rounded-xl bg-slate-50 border border-slate-200/80 shadow-2xs">
                                            {{-- وێنەی داواکاری --}}
                                            <div class="size-11 rounded-lg bg-white border border-slate-200 shrink-0 overflow-hidden flex items-center justify-center">
                                                @if($imgUrl)
                                                    <a href="{{ $imgUrl }}" target="_blank" title="بینینی وێنەی گەورە">
                                                        <img src="{{ $imgUrl }}" alt="{{ $it->item_name }}"
                                                             class="size-11 object-cover hover:scale-110 transition-transform cursor-pointer">
                                                    </a>
                                                @else
                                                    <span class="text-slate-300 text-base">🖼️</span>
                                                @endif
                                            </div>

                                            {{-- ناوی بابەت و قیاس --}}
                                            <div>
                                                <div class="flex items-center gap-1.5">
                                                    <span class="font-black text-xs text-slate-900">{{ $it->item_name }}</span>
                                                    <span class="font-mono font-bold text-[11px] text-indigo-700 bg-indigo-50 px-1.5 py-0.5 rounded border border-indigo-100">
                                                        {{ fmt_num($it->quantity ?? $it->qty) }} {{ $it->unit_label ?: $it->unit_name }}
                                                    </span>
                                                </div>

                                                <div class="flex items-center gap-2 text-[11px] mt-0.5 text-slate-500">
                                                    @if($it->width && $it->height)
                                                        <span class="font-mono font-bold text-slate-700">
                                                            {{ $it->width }} × {{ $it->height }} م
                                                        </span>
                                                    @elseif($it->measurement_label && $it->measurement_label !== '—')
                                                        <span class="font-mono font-bold text-slate-700">
                                                            {{ $it->measurement_label }}
                                                        </span>
                                                    @endif

                                                    @if($it->note)
                                                        <span class="text-slate-400 italic">({{ $it->note }})</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </td>

                            {{-- دۆخی کارگە --}}
                            <td class="p-3.5 text-center">
                                @if($order->status === 'delivered')
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200 inline-block">
                                        ڕادەستکراو ✔️
                                    </span>
                                @elseif($order->status === 'ready')
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200 inline-block">
                                        ئامادەیە ✅
                                    </span>
                                @elseif($order->status === 'in_production')
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-indigo-100 text-indigo-800 border border-indigo-200 inline-block animate-pulse">
                                        لە دروستکردندا ⚙️
                                    </span>
                                @elseif($order->status === 'confirmed')
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800 border border-amber-200 inline-block">
                                        چاوەڕوانە ⏳
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-slate-100 text-slate-700 border border-slate-200 inline-block">
                                        {{ $order->status_label ?? $order->status }}
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-10 text-center text-xs text-slate-400 font-medium">
                                هیچ وەسڵێک لەم ماوەیەدا تۆمار نەکراوە.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
