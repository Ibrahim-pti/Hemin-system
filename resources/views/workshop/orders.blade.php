@extends('layouts.app')
@section('title', 'داواکارییەکانی کارگە')

@section('content')
<div x-data="{ previewImg: null }" class="space-y-6">

    {{-- ١. سەردێڕی داواکارییەکان --}}
    <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-3.5">
            <div class="size-12 rounded-2xl bg-blue-50 border border-blue-100 text-blue-600 flex items-center justify-center text-2xl shadow-xs">
                📋
            </div>
            <div>
                <h1 class="text-xl font-black text-slate-800">داواکارییەکان و وەسڵەکانی کارگە</h1>
                <p class="text-xs text-slate-500 mt-0.5 font-medium">
                    چاودێری شتە داواکراوەکانی وەسڵەکان بە وێنە، گۆڕینی قۆناغی کار و دەستپێکردن/تەواوکردن
                </p>
            </div>
        </div>
    </div>

    {{-- ٢. فلتەری دۆخەکان و گەڕان --}}
    <div class="bg-white rounded-2xl p-3.5 border border-slate-200 shadow-xs flex flex-wrap items-center justify-between gap-3">
        <div class="flex flex-wrap items-center gap-2 text-xs font-bold">
            <a href="{{ route('workshop.orders', ['tab' => 'all']) }}"
               class="px-3.5 py-1.5 rounded-xl transition-all {{ $tab === 'all' ? 'bg-slate-900 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                هەموو کارەکان
            </a>
            <a href="{{ route('workshop.orders', ['tab' => 'in_production']) }}"
               class="px-3.5 py-1.5 rounded-xl transition-all {{ $tab === 'in_production' ? 'bg-blue-600 text-white shadow-xs' : 'bg-blue-50 text-blue-700 hover:bg-blue-100' }}">
                ⚙️ لە دروستکردندا ({{ $inProductionCount }})
            </a>
            <a href="{{ route('workshop.orders', ['tab' => 'pending']) }}"
               class="px-3.5 py-1.5 rounded-xl transition-all {{ $tab === 'pending' ? 'bg-amber-500 text-white shadow-xs' : 'bg-amber-50 text-amber-700 hover:bg-amber-100' }}">
                ⏳ چاوەڕوانە ({{ $pendingCount }})
            </a>
            <a href="{{ route('workshop.orders', ['tab' => 'ready']) }}"
               class="px-3.5 py-1.5 rounded-xl transition-all {{ $tab === 'ready' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100' }}">
                ✅ ئامادەیە ({{ $readyCount }})
            </a>
            <a href="{{ route('workshop.orders', ['tab' => 'delivered']) }}"
               class="px-3.5 py-1.5 rounded-xl transition-all {{ $tab === 'delivered' ? 'bg-slate-700 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                🚚 ڕادەستکراوە
            </a>
        </div>

        <form method="GET" action="{{ route('workshop.orders') }}" class="flex items-center gap-2">
            <input type="hidden" name="tab" value="{{ $tab }}">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="گەڕان بە ناوی کڕیار یان ژمارە وەسڵ..."
                   class="text-xs px-3 py-1.5 rounded-xl border border-slate-200 w-60 focus:outline-hidden focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
            <button type="submit" class="btn btn-primary !py-1.5 !px-3 text-xs">گەڕان</button>
        </form>
    </div>

    {{-- ٣. کارتەکانی وەسڵەکان --}}
    @if ($orders->isEmpty())
        <div class="bg-white rounded-2xl p-12 text-center border border-slate-200 shadow-xs">
            <div class="text-4xl mb-2">📋</div>
            <div class="font-bold text-slate-700 text-base">هیچ وەسڵێک نەدۆزرایەوە</div>
            <div class="text-xs text-slate-400 mt-1">لە دۆخی هەڵبژێردراودا هیچ داواکارییەکی دروستکردن نییە.</div>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
            @foreach ($orders as $order)
                @php
                    $statusColors = [
                        'confirmed' => 'border-amber-200 bg-white hover:border-amber-400',
                        'in_production' => 'border-blue-300 bg-blue-50/20 hover:border-blue-400 ring-1 ring-blue-200',
                        'ready' => 'border-emerald-300 bg-emerald-50/20 hover:border-emerald-400',
                        'delivered' => 'border-slate-200 bg-slate-50/50 opacity-80',
                    ];
                @endphp
                <div class="rounded-2xl border p-4.5 shadow-xs flex flex-col justify-between transition-all hover:shadow-md {{ $statusColors[$order->status] ?? 'border-slate-200 bg-white' }}">
                    <div>
                        {{-- هێڵی سەرەوەی کارتەکە --}}
                        <div class="flex items-start justify-between gap-2 border-b border-slate-100 pb-3 mb-3">
                            <div>
                                <div class="flex items-center gap-2">
                                    <h3 class="font-black text-slate-900 text-base">وەسڵی #{{ $order->invoice_no }}</h3>
                                    <span class="px-2 py-0.5 rounded-full text-[11px] font-bold {{ $order->status_color }}">
                                        {{ $order->status_label }}
                                    </span>
                                </div>
                                <div class="text-xs text-slate-500 font-bold mt-1">
                                    کڕیار: <span class="text-blue-600 font-black">{{ $order->customer?->name ?? 'نەناسراو' }}</span>
                                    @if ($order->customer?->phone)
                                        <span class="text-slate-400 font-normal">({{ $order->customer->phone }})</span>
                                    @endif
                                </div>
                            </div>
                            <div class="text-left text-[11px] text-slate-400 font-medium">
                                <div>{{ $order->order_date?->format('Y/m/d') }}</div>
                                @if ($order->delivery_date)
                                    <div class="text-rose-600 font-bold mt-0.5">گەیاندن: {{ $order->delivery_date->format('Y/m/d') }}</div>
                                @endif
                            </div>
                        </div>

                        {{-- کەلوپەلە داواکراوەکان بە وێنە و قیاسات --}}
                        <div class="space-y-2 mb-3">
                            <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">کەلوپەل و وێنەکان:</div>
                            @foreach ($order->items as $it)
                                @php
                                    $itemModel = \App\Models\Item::find($it->item_id);
                                    $imgUrl = $itemModel?->imageUrl();
                                @endphp
                                <div class="flex items-center gap-3 p-2 rounded-xl bg-slate-50 border border-slate-100">
                                    <div class="size-12 rounded-lg bg-white border border-slate-200 shrink-0 overflow-hidden flex items-center justify-center">
                                        @if ($imgUrl)
                                            <img src="{{ $imgUrl }}" alt="{{ $it->item_name }}"
                                                 class="size-full object-cover cursor-pointer hover:scale-110 transition-transform"
                                                 @click="previewImg = '{{ $imgUrl }}'">
                                        @else
                                            <span class="text-slate-300 text-xs">بێ وێنە</span>
                                        @endif
                                    </div>

                                    <div class="min-w-0 flex-1">
                                        <div class="font-bold text-xs text-slate-800 truncate">{{ $it->item_name }}</div>
                                        <div class="text-[11px] text-slate-500 font-medium mt-0.5">
                                            بڕ: <span class="font-bold text-blue-600 num">{{ fmt_num($it->qty) }}</span> {{ $it->unit_name }}
                                            @if ($it->width || $it->height)
                                                <span class="text-slate-400 font-mono">({{ $it->width }}×{{ $it->height }})</span>
                                            @endif
                                        </div>
                                        @if ($it->note)
                                            <div class="text-[10px] text-amber-700 bg-amber-50/80 px-1.5 py-0.5 rounded-md mt-1 border border-amber-200/50">
                                                تێبینی: {{ $it->note }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        @if ($order->notes)
                            <div class="text-xs text-slate-600 bg-amber-50/60 p-2.5 rounded-xl border border-amber-100 mb-3">
                                <span class="font-bold text-amber-800">تێبینی گشتی:</span> {{ $order->notes }}
                            </div>
                        @endif
                    </div>

                    {{-- دوگمەکانی کردار و گۆڕینی دۆخ --}}
                    <div class="border-t border-slate-100 pt-3 flex items-center justify-between gap-2">
                        <form method="POST" action="{{ route('workshop.status', $order) }}" class="flex items-center gap-1.5 flex-wrap">
                            @csrf
                            @if ($order->status === 'confirmed')
                                <input type="hidden" name="status" value="in_production">
                                <button type="submit" class="btn btn-primary !py-1.5 !px-3 text-xs font-bold bg-blue-600 hover:bg-blue-700 flex items-center gap-1 shadow-xs cursor-pointer">
                                    <span>⚙️</span>
                                    <span>دەستپێکردنی دروستکردن</span>
                                </button>
                            @elseif ($order->status === 'in_production')
                                <input type="hidden" name="status" value="ready">
                                <button type="submit" class="btn btn-primary !py-1.5 !px-3 text-xs font-bold bg-emerald-600 hover:bg-emerald-700 flex items-center gap-1 shadow-xs cursor-pointer">
                                    <span>✅</span>
                                    <span>تەواوبوو (ئامادەیە)</span>
                                </button>
                            @elseif ($order->status === 'ready')
                                <input type="hidden" name="status" value="delivered">
                                <button type="submit" class="btn btn-primary !py-1.5 !px-3 text-xs font-bold bg-slate-800 hover:bg-slate-900 flex items-center gap-1 shadow-xs cursor-pointer">
                                    <span>🚚</span>
                                    <span>ڕادەستکرا بە کڕیار</span>
                                </button>
                            @elseif ($order->status === 'delivered')
                                <span class="text-xs text-emerald-700 font-bold bg-emerald-50 px-2.5 py-1 rounded-lg border border-emerald-200">
                                    تەواوکراوە و ڕادەستکراوە ✔️
                                </span>
                            @endif
                        </form>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-4">
            {{ $orders->links() }}
        </div>
    @endif

    {{-- مۆداڵی وێنەی گەورە --}}
    <div x-show="previewImg" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/80 backdrop-blur-xs p-4" @click="previewImg = null">
        <div class="relative max-w-2xl max-h-[85vh] bg-white rounded-2xl overflow-hidden shadow-2xl p-2" @click.stop>
            <button type="button" @click="previewImg = null" class="absolute top-3 right-3 bg-slate-900/70 text-white rounded-full size-8 flex items-center justify-center hover:bg-slate-900 transition-colors cursor-pointer">✕</button>
            <img :src="previewImg" class="max-h-[80vh] w-auto mx-auto object-contain rounded-xl">
        </div>
    </div>

</div>
@endsection
