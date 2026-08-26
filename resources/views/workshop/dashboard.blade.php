@extends('layouts.app')
@section('title', 'داشبۆردی وەستاکان و کارگە')

@section('actions')
    <div class="flex items-center gap-2">
        <button type="button" @click="$dispatch('open-stock-in-modal')" class="btn btn-ghost !py-1.5 !px-3 text-xs font-bold border border-emerald-300 text-emerald-700 bg-emerald-50 hover:bg-emerald-100 transition-all cursor-pointer">
            📥 هاتنی مەواد
        </button>
        <button type="button" @click="$dispatch('open-stock-out-modal')" class="btn btn-ghost !py-1.5 !px-3 text-xs font-bold border border-amber-300 text-amber-700 bg-amber-50 hover:bg-amber-100 transition-all cursor-pointer">
            📤 بەکارهێنانی مەواد
        </button>
        <button type="button" @click="$dispatch('open-new-material-modal')" class="btn btn-primary !py-1.5 !px-3.5 text-xs font-bold bg-blue-600 hover:bg-blue-700 shadow-xs cursor-pointer">
            + مەوادی نوێ
        </button>
    </div>
@endsection

@section('content')
<div x-data="{
    activeSection: 'orders',
    showNewMaterialModal: false,
    showStockInModal: false,
    showStockOutModal: false,
    previewImg: null,
    init() {
        window.addEventListener('open-new-material-modal', () => this.showNewMaterialModal = true);
        window.addEventListener('open-stock-in-modal', () => this.showStockInModal = true);
        window.addEventListener('open-stock-out-modal', () => this.showStockOutModal = true);
    }
}" class="space-y-6">

    {{-- ١. هێڵی سەرەوە: ناونیشان و دوگمەکانی گۆڕینی بەش --}}
    <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-3.5">
            <div class="size-12 rounded-2xl bg-indigo-50 border border-indigo-100 text-indigo-600 flex items-center justify-center text-2xl shadow-xs">
                ⚒️
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-xl font-black text-slate-800">بەشی کارگە و دروستکردن</h1>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200">
                        {{ $workshopWarehouse?->name ?? 'شوێنی دروستکردن' }}
                    </span>
                </div>
                <p class="text-xs text-slate-500 mt-0.5 font-medium">
                    چاودێری شتە داواکراوەکانی وەسڵەکان بە وێنە، گۆڕینی قۆناغی کار، و مەوادی دروستکردن
                </p>
            </div>
        </div>

        {{-- تابی سەرەکی نێوان (ئیشەکانی دروستکردن) و (مەوادی خاو) --}}
        <div class="flex items-center bg-slate-100 p-1 rounded-xl border border-slate-200 text-xs font-bold">
            <button type="button" @click="activeSection = 'orders'"
                    :class="activeSection === 'orders' ? 'bg-white text-blue-600 shadow-xs border border-slate-200/80' : 'text-slate-600 hover:text-slate-900'"
                    class="px-4 py-2 rounded-lg transition-all flex items-center gap-2 cursor-pointer">
                <span>📋 کارەکانی دروستکردن</span>
                <span class="px-1.5 py-0.2 rounded-full text-[11px] font-bold"
                      :class="activeSection === 'orders' ? 'bg-blue-100 text-blue-700' : 'bg-slate-200 text-slate-700'">
                    {{ $pendingCount + $inProductionCount }}
                </span>
            </button>
            <button type="button" @click="activeSection = 'materials'"
                    :class="activeSection === 'materials' ? 'bg-white text-blue-600 shadow-xs border border-slate-200/80' : 'text-slate-600 hover:text-slate-900'"
                    class="px-4 py-2 rounded-lg transition-all flex items-center gap-2 cursor-pointer">
                <span>📦 مەوادی خاو و کۆگا</span>
            </button>
        </div>
    </div>

    {{-- ٢. کارتە ئامارییەکان بە دیزاینی پاک و مۆدێرن --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- ١. چاوەڕوانە --}}
        <a href="{{ route('workshop.index', ['tab' => 'pending']) }}"
           class="bg-white rounded-2xl p-4.5 border transition-all hover:shadow-md group cursor-pointer {{ request('tab') === 'pending' ? 'border-amber-500 ring-2 ring-amber-200' : 'border-slate-200 hover:border-amber-300' }}">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold text-slate-600">چاوەڕوانی دروستکردن</span>
                <span class="size-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-base font-bold border border-amber-200/60 group-hover:scale-110 transition-transform">⏳</span>
            </div>
            <div class="num text-2xl md:text-3xl font-black text-amber-600">{{ $pendingCount }}</div>
            <div class="text-[11px] text-slate-400 font-medium mt-1">ئیشی نوێی پەسەندکراو</div>
        </a>

        {{-- ٢. لە کاردایە (دروستدەکرێت) --}}
        <a href="{{ route('workshop.index', ['tab' => 'in_production']) }}"
           class="bg-white rounded-2xl p-4.5 border transition-all hover:shadow-md group cursor-pointer {{ request('tab') === 'in_production' ? 'border-blue-500 ring-2 ring-blue-200' : 'border-slate-200 hover:border-blue-300' }}">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold text-slate-600">لە ژێر کاردایە (دروستدەکرێت)</span>
                <span class="size-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-base font-bold border border-blue-200/60 group-hover:scale-110 transition-transform">⚙️</span>
            </div>
            <div class="num text-2xl md:text-3xl font-black text-blue-600">{{ $inProductionCount }}</div>
            <div class="text-[11px] text-slate-400 font-medium mt-1">وەستاکان کاری لەسەر دەکەن</div>
        </a>

        {{-- ٣. تەواوبوو (ئامادەیە) --}}
        <a href="{{ route('workshop.index', ['tab' => 'ready']) }}"
           class="bg-white rounded-2xl p-4.5 border transition-all hover:shadow-md group cursor-pointer {{ request('tab') === 'ready' ? 'border-emerald-500 ring-2 ring-emerald-200' : 'border-slate-200 hover:border-emerald-300' }}">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold text-slate-600">ئامادەیە بۆ وەرگرتن</span>
                <span class="size-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-base font-bold border border-emerald-200/60 group-hover:scale-110 transition-transform">✅</span>
            </div>
            <div class="num text-2xl md:text-3xl font-black text-emerald-600">{{ $readyCount }}</div>
            <div class="text-[11px] text-slate-400 font-medium mt-1">دروستکراوە و ئامادەیە</div>
        </a>

        {{-- ٤. ڕادەستکراو --}}
        <a href="{{ route('workshop.index', ['tab' => 'delivered']) }}"
           class="bg-white rounded-2xl p-4.5 border transition-all hover:shadow-md group cursor-pointer {{ request('tab') === 'delivered' ? 'border-slate-500 ring-2 ring-slate-200' : 'border-slate-200 hover:border-slate-400' }}">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold text-slate-600">ئەمڕۆ ڕادەستکراوە</span>
                <span class="size-9 rounded-xl bg-slate-50 text-slate-600 flex items-center justify-center text-base font-bold border border-slate-200/60 group-hover:scale-110 transition-transform">🚚</span>
            </div>
            <div class="num text-2xl md:text-3xl font-black text-slate-800">{{ $deliveredCount }}</div>
            <div class="text-[11px] text-slate-400 font-medium mt-1">کارە تەواوکراوەکان</div>
        </a>
    </div>

    {{-- ٣. ناوەڕۆکی بەشی ئیشەکانی دروستکردن --}}
    <div x-show="activeSection === 'orders'" class="space-y-4">
        {{-- فلتەری دۆخەکان و گەڕان --}}
        <div class="bg-white rounded-2xl p-3.5 border border-slate-200 shadow-xs flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-2 text-xs font-bold">
                <a href="{{ route('workshop.index', ['tab' => 'all']) }}"
                   class="px-3.5 py-1.5 rounded-xl transition-all {{ $tab === 'all' ? 'bg-slate-900 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    هەموو کارەکان
                </a>
                <a href="{{ route('workshop.index', ['tab' => 'in_production']) }}"
                   class="px-3.5 py-1.5 rounded-xl transition-all {{ $tab === 'in_production' ? 'bg-blue-600 text-white shadow-xs' : 'bg-blue-50 text-blue-700 hover:bg-blue-100' }}">
                    ⚙️ لە دروستکردندا ({{ $inProductionCount }})
                </a>
                <a href="{{ route('workshop.index', ['tab' => 'pending']) }}"
                   class="px-3.5 py-1.5 rounded-xl transition-all {{ $tab === 'pending' ? 'bg-amber-500 text-white shadow-xs' : 'bg-amber-50 text-amber-700 hover:bg-amber-100' }}">
                    ⏳ چاوەڕوانە ({{ $pendingCount }})
                </a>
                <a href="{{ route('workshop.index', ['tab' => 'ready']) }}"
                   class="px-3.5 py-1.5 rounded-xl transition-all {{ $tab === 'ready' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100' }}">
                    ✅ ئامادەیە ({{ $readyCount }})
                </a>
                <a href="{{ route('workshop.index', ['tab' => 'delivered']) }}"
                   class="px-3.5 py-1.5 rounded-xl transition-all {{ $tab === 'delivered' ? 'bg-slate-700 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    🚚 ڕادەستکراوە
                </a>
            </div>

            {{-- گەڕان --}}
            <form method="GET" action="{{ route('workshop.index') }}" class="flex items-center gap-2">
                <input type="hidden" name="tab" value="{{ $tab }}">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="گەڕان بە ناوی کڕیار یان ژمارە وەسڵ..."
                       class="field text-xs !py-1.5 !px-3 w-60 rounded-xl">
                <button class="btn btn-ghost !py-1.5 text-xs font-bold">گەڕان</button>
            </form>
        </div>

        {{-- کارتی ئیشەکان (Grid Card Layout) --}}
        @if ($orders->isEmpty())
            <div class="bg-white rounded-2xl p-12 text-center border border-slate-200 shadow-xs">
                <div class="text-4xl mb-2.5">🎉</div>
                <div class="font-bold text-slate-700 text-base">هیچ کارێک لەم بەشەدا نییە</div>
                <div class="text-xs text-slate-400 mt-1">هەموو داواکارییەکان تەواوکراون یان لە بەشەکانی تردا بەردەستن.</div>
            </div>
        @else
            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($orders as $order)
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs flex flex-col justify-between hover:shadow-md transition-all overflow-hidden">
                        {{-- سەری کارتی وەسڵ --}}
                        <div class="p-4 bg-slate-50/80 border-b border-slate-100 flex items-center justify-between">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="font-black text-slate-900 text-base">وەسڵی #{{ $order->invoice_no }}</span>
                                    @if ($order->status === 'in_production')
                                        <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-blue-100 text-blue-700 border border-blue-200 flex items-center gap-1 animate-pulse">
                                            <span>⚙️</span><span>لە دروستکردندایە</span>
                                        </span>
                                    @elseif ($order->status === 'ready')
                                        <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-700 border border-emerald-200 flex items-center gap-1">
                                            <span>✅</span><span>ئامادەیە</span>
                                        </span>
                                    @elseif ($order->status === 'confirmed')
                                        <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800 border border-amber-200 flex items-center gap-1">
                                            <span>⏳</span><span>چاوەڕوانە</span>
                                        </span>
                                    @elseif ($order->status === 'delivered')
                                        <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-slate-200 text-slate-700 border border-slate-300">
                                            <span>🚚</span><span>ڕادەستکراوە</span>
                                        </span>
                                    @endif
                                </div>
                                <div class="text-xs font-bold text-slate-800 mt-1">
                                    کڕیار: <span class="text-blue-700">{{ $order->customer?->name }}</span>
                                    @if ($order->customer?->phone)
                                        <span class="text-slate-400 font-normal mr-1" dir="ltr">({{ $order->customer->phone }})</span>
                                    @endif
                                </div>
                            </div>

                            <div class="text-left text-[11px] text-slate-500">
                                <div class="font-bold">{{ $order->order_date?->format('Y/m/d') }}</div>
                                @if ($order->delivery_date)
                                    <div class="text-rose-600 font-bold mt-0.5">گەیاندن: {{ $order->delivery_date->format('Y/m/d') }}</div>
                                @endif
                            </div>
                        </div>

                        {{-- ناوەڕۆکی شتە دروستکراوەکان بە وێنە --}}
                        <div class="p-4 space-y-3 flex-1">
                            <div class="text-[11px] font-bold text-slate-400">ناوەڕۆک و شتەکان:</div>
                            <div class="space-y-2.5">
                                @foreach ($order->items as $item)
                                    <div class="flex items-center gap-3 p-2.5 rounded-xl bg-slate-50 border border-slate-100">
                                        {{-- وێنە --}}
                                        @if ($item->imageUrl())
                                            <div class="size-14 rounded-lg overflow-hidden border border-slate-200 shrink-0 cursor-pointer shadow-xs hover:scale-105 transition-transform"
                                                 @click="previewImg = '{{ $item->imageUrl() }}'">
                                                <img src="{{ $item->imageUrl() }}" class="size-full object-cover" alt="{{ $item->description }}">
                                            </div>
                                        @else
                                            <div class="size-14 rounded-lg bg-slate-200/80 text-slate-400 flex items-center justify-center text-[10px] font-bold shrink-0">
                                                بێ وێنە
                                            </div>
                                        @endif

                                        {{-- ناوی شتەکە --}}
                                        <div class="min-w-0 flex-1">
                                            <div class="font-bold text-slate-800 text-sm leading-tight">{{ $item->description }}</div>
                                            @if ($item->note)
                                                <div class="text-[11px] text-amber-800 font-medium mt-1 bg-amber-50 px-2 py-0.5 rounded border border-amber-200/80 inline-block">
                                                    تێبینی: {{ $item->note }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            @if ($order->note)
                                <div class="mt-2 text-xs text-slate-600 bg-slate-100 p-2.5 rounded-xl border border-slate-200">
                                    <span class="font-bold text-slate-700">تێبینی گشتی وەسڵ:</span> {{ $order->note }}
                                </div>
                            @endif
                        </div>

                        {{-- دوگمەی گۆڕینی دۆخی خێرا بۆ وەستا --}}
                        <div class="p-3 bg-slate-50/90 border-t border-slate-100 flex items-center justify-between gap-2">
                            <div class="flex-1">
                                @if ($order->status === 'confirmed')
                                    <form method="POST" action="{{ route('workshop.status', $order) }}">
                                        @csrf
                                        <input type="hidden" name="status" value="in_production">
                                        <button class="btn w-full !py-2 text-xs font-bold bg-blue-600 text-white hover:bg-blue-700 shadow-xs cursor-pointer">
                                            ⚙️ دەستپێکردنی دروستکردن
                                        </button>
                                    </form>
                                @elseif ($order->status === 'in_production')
                                    <form method="POST" action="{{ route('workshop.status', $order) }}">
                                        @csrf
                                        <input type="hidden" name="status" value="ready">
                                        <button class="btn w-full !py-2 text-xs font-bold bg-emerald-600 text-white hover:bg-emerald-700 shadow-xs cursor-pointer">
                                            ✅ تەواوبوو (ئامادەیە بۆ وەرگرتن)
                                        </button>
                                    </form>
                                @elseif ($order->status === 'ready')
                                    <form method="POST" action="{{ route('workshop.status', $order) }}">
                                        @csrf
                                        <input type="hidden" name="status" value="delivered">
                                        <button class="btn w-full !py-2 text-xs font-bold bg-slate-800 text-white hover:bg-slate-900 shadow-xs cursor-pointer">
                                            🚚 ڕادەستکردنی کارەکە
                                        </button>
                                    </form>
                                @elseif ($order->status === 'delivered')
                                    <div class="text-center text-xs font-bold text-slate-500 py-1">
                                        کارەکە ڕادەستکراوە ✔️
                                    </div>
                                @endif
                            </div>

                            @if ($order->status !== 'confirmed')
                                <form method="POST" action="{{ route('workshop.status', $order) }}">
                                    @csrf
                                    <input type="hidden" name="status" value="confirmed">
                                    <button class="btn btn-ghost !py-2 !px-2.5 text-xs text-slate-400 hover:text-slate-700 hover:bg-slate-200 cursor-pointer" title="گەڕاندنەوە بۆ چاوەڕوانی">
                                        ↩️
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-5">
                {{ $orders->links() }}
            </div>
        @endif
    </div>

    {{-- ٤. بەشی مەوادی خاو و کۆگای شوێنی دروستکردن --}}
    <div x-show="activeSection === 'materials'" class="space-y-4" x-cloak>
        <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-xs flex flex-wrap items-center justify-between gap-3">
            <div>
                <div class="font-bold text-slate-800 text-base">مەوادی خاو و کەرەستەی بەردەست لە شوێنی دروستکردن</div>
                <div class="text-xs text-slate-500 font-medium mt-0.5">کۆی جۆرەکانی مەواد: {{ $rawMaterials->total() }}</div>
            </div>

            <div class="flex items-center gap-2">
                <form method="GET" action="{{ route('workshop.index') }}" class="flex items-center gap-2">
                    <input type="text" name="mat_q" value="{{ request('mat_q') }}" placeholder="گەڕان بە ناوی مەواد..."
                           class="field text-xs !py-1.5 !px-3 w-56 rounded-xl">
                    <button class="btn btn-ghost !py-1.5 text-xs font-bold">گەڕان</button>
                </form>

                <button type="button" @click="showNewMaterialModal = true" class="btn btn-primary !py-1.5 !px-3.5 text-xs font-bold bg-blue-600 hover:bg-blue-700 shadow-xs cursor-pointer">
                    + مەوادی نوێ
                </button>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="table w-full">
                    <thead>
                        <tr class="bg-slate-50 text-xs text-slate-700 font-bold border-b border-slate-200">
                            <th class="p-3 text-right">کۆد</th>
                            <th class="p-3 text-right">ناوی مەواد</th>
                            <th class="p-3 text-right">جۆر / کاتەگۆری</th>
                            <th class="p-3 text-center">بڕی بەردەست لە کارگە</th>
                            <th class="p-3 text-center">یەکە</th>
                            <th class="p-3 text-center">دۆخ</th>
                            <th class="p-3 text-center">کردارەکان</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        @forelse ($rawMaterials as $mat)
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="p-3 font-mono text-xs text-slate-500 font-bold">{{ $mat->code }}</td>
                                <td class="p-3 font-bold text-slate-800">{{ $mat->name }}</td>
                                <td class="p-3 text-xs text-slate-600">{{ $mat->category?->name ?? '—' }}</td>
                                <td class="p-3 text-center font-black text-base num {{ $mat->stock_qty <= $mat->min_qty && $mat->min_qty > 0 ? 'text-rose-600' : 'text-slate-800' }}">
                                    {{ fmt_num($mat->stock_qty) }}
                                </td>
                                <td class="p-3 text-center text-xs font-medium text-slate-600">{{ $mat->unit?->name }}</td>
                                <td class="p-3 text-center">
                                    @if ($mat->min_qty > 0 && $mat->stock_qty <= $mat->min_qty)
                                        <span class="px-2 py-0.5 rounded-md text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200">⚠️ کەمە</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-md text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">بەردەستە</span>
                                    @endif
                                </td>
                                <td class="p-3 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <button type="button" @click="showStockInModal = true" class="btn btn-ghost !py-1 !px-2.5 text-xs font-bold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 cursor-pointer" title="زیادکردنی بڕ">
                                            + زیادکردن
                                        </button>
                                        <button type="button" @click="showStockOutModal = true" class="btn btn-ghost !py-1 !px-2.5 text-xs font-bold text-amber-700 bg-amber-50 hover:bg-amber-100 border border-amber-200 cursor-pointer" title="بەکارهێنان">
                                            - بەکارهێنان
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="p-8 text-center text-slate-400 text-xs font-bold">هیچ مەوادێک لەم کۆگایەدا نییە.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-3 border-t border-slate-100">
                {{ $rawMaterials->links() }}
            </div>
        </div>
    </div>

    {{-- مۆداڵی پێشاندانی وێنەی گەورە --}}
    <div x-show="previewImg" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/80 backdrop-blur-xs p-4" @click="previewImg = null">
        <div class="relative max-w-2xl max-h-[85vh] bg-white rounded-2xl overflow-hidden shadow-2xl p-2" @click.stop>
            <button type="button" @click="previewImg = null" class="absolute top-3 right-3 bg-slate-900/70 text-white rounded-full size-8 flex items-center justify-center hover:bg-slate-900 transition-colors cursor-pointer">✕</button>
            <img :src="previewImg" class="max-h-[80vh] w-auto mx-auto object-contain rounded-xl">
        </div>
    </div>

    {{-- مۆداڵی زیادکردنی مەوادی خاوی نوێ --}}
    <div x-show="showNewMaterialModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4" x-transition.opacity>
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-5 border border-slate-200 text-right" @click.away="showNewMaterialModal = false" x-transition.scale>
            <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                <div class="font-bold text-slate-800 text-base flex items-center gap-2">
                    <span class="size-8 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center text-sm font-bold">📦</span>
                    <span>زیادکردنی مەوادی خاوی نوێ</span>
                </div>
                <button type="button" @click="showNewMaterialModal = false" class="text-slate-400 hover:text-slate-600 size-7 rounded-lg flex items-center justify-center cursor-pointer">✕</button>
            </div>

            <form method="POST" action="{{ route('workshop.store-material') }}" class="space-y-3.5">
                @csrf
                <input type="hidden" name="warehouse_id" value="{{ $workshopWarehouse?->id }}">

                <div>
                    <label class="label text-xs" for="new_mat_name">ناوی مەواد <span class="text-rose-500">*</span></label>
                    <input id="new_mat_name" name="name" class="field text-sm font-bold w-full" required placeholder="وەک: بۆری ٤×٨، قوفڵی تورکی، بۆیاخی ڕەش...">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="label text-xs" for="new_mat_cat">جۆر / بەش</label>
                        <select id="new_mat_cat" name="item_category_id" class="field text-xs w-full">
                            <option value="">— دیاری نەکراو —</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="label text-xs" for="new_mat_unit">یەکە <span class="text-rose-500">*</span></label>
                        <select id="new_mat_unit" name="unit_id" class="field text-xs font-bold w-full" required>
                            @foreach ($units as $u)
                                <option value="{{ $u->id }}">{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="label text-xs" for="new_mat_qty">بڕی سەرەتایی لە کارگە</label>
                        <input id="new_mat_qty" name="initial_qty" type="number" step="any" min="0" class="field num text-xs w-full" placeholder="0">
                    </div>
                    <div>
                        <label class="label text-xs" for="new_mat_min">سنووری ئاگاداری (کەمترین)</label>
                        <input id="new_mat_min" name="min_qty" type="number" step="any" min="0" class="field num text-xs w-full" placeholder="0">
                    </div>
                </div>

                <div>
                    <label class="label text-xs" for="new_mat_note">تێبینی</label>
                    <input id="new_mat_note" name="note" class="field text-xs w-full" placeholder="تێبینی سەبارەت بەم مەوادە...">
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="showNewMaterialModal = false" class="btn btn-ghost !py-1.5 text-xs cursor-pointer">پاشگەزبوونەوە</button>
                    <button type="submit" class="btn btn-primary !py-1.5 !px-5 text-xs font-bold shadow-sm cursor-pointer">تۆمارکردنی مەواد</button>
                </div>
            </form>
        </div>
    </div>

    {{-- مۆداڵی هاتنی بڕی مەواد بۆ کارگە (Stock In) --}}
    <div x-show="showStockInModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4" x-transition.opacity>
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-5 border border-slate-200 text-right" @click.away="showStockInModal = false" x-transition.scale>
            <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                <div class="font-bold text-slate-800 text-base flex items-center gap-2">
                    <span class="size-8 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center text-sm font-bold">📥</span>
                    <span>زیادکردنی بڕ بۆ مەواد لە کارگە</span>
                </div>
                <button type="button" @click="showStockInModal = false" class="text-slate-400 hover:text-slate-600 size-7 rounded-lg flex items-center justify-center cursor-pointer">✕</button>
            </div>

            <form method="POST" action="{{ route('workshop.stock-in') }}" class="space-y-3.5">
                @csrf
                <input type="hidden" name="warehouse_id" value="{{ $workshopWarehouse?->id }}">

                <div>
                    <label class="label text-xs" for="stock_in_item">مەواد هەڵبژێرە <span class="text-rose-500">*</span></label>
                    <select id="stock_in_item" name="item_id" class="field text-xs font-bold w-full" required>
                        <option value="">— مەواد دیاری بکە —</option>
                        @foreach ($allItems as $it)
                            <option value="{{ $it->id }}">{{ $it->name }} ({{ $it->code }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="label text-xs" for="stock_in_qty">بڕی زیادکراو <span class="text-rose-500">*</span></label>
                    <input id="stock_in_qty" name="qty" type="number" step="any" min="0.01" class="field num text-sm font-bold text-emerald-700 w-full" required placeholder="0">
                </div>

                <div>
                    <label class="label text-xs" for="stock_in_note">تێبینی</label>
                    <input id="stock_in_note" name="note" class="field text-xs w-full" placeholder="هۆکاری زیادکردن یان ژمارە پسوولە...">
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="showStockInModal = false" class="btn btn-ghost !py-1.5 text-xs cursor-pointer">پاشگەزبوونەوە</button>
                    <button type="submit" class="btn btn-primary !py-1.5 !px-5 text-xs font-bold bg-emerald-600 hover:bg-emerald-700 shadow-sm cursor-pointer">تۆمارکردن</button>
                </div>
            </form>
        </div>
    </div>

    {{-- مۆداڵی بەکارهێنانی مەواد (Stock Out) --}}
    <div x-show="showStockOutModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4" x-transition.opacity>
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-5 border border-slate-200 text-right" @click.away="showStockOutModal = false" x-transition.scale>
            <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                <div class="font-bold text-slate-800 text-base flex items-center gap-2">
                    <span class="size-8 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center text-sm font-bold">📤</span>
                    <span>بەکارهێنانی مەواد لە دروستکردندا</span>
                </div>
                <button type="button" @click="showStockOutModal = false" class="text-slate-400 hover:text-slate-600 size-7 rounded-lg flex items-center justify-center cursor-pointer">✕</button>
            </div>

            <form method="POST" action="{{ route('workshop.stock-out') }}" class="space-y-3.5">
                @csrf
                <input type="hidden" name="warehouse_id" value="{{ $workshopWarehouse?->id }}">

                <div>
                    <label class="label text-xs" for="stock_out_item">مەواد هەڵبژێرە <span class="text-rose-500">*</span></label>
                    <select id="stock_out_item" name="item_id" class="field text-xs font-bold w-full" required>
                        <option value="">— مەواد دیاری بکە —</option>
                        @foreach ($allItems as $it)
                            <option value="{{ $it->id }}">{{ $it->name }} ({{ $it->code }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="label text-xs" for="stock_out_qty">بڕی بەکارهاتوو <span class="text-rose-500">*</span></label>
                    <input id="stock_out_qty" name="qty" type="number" step="any" min="0.01" class="field num text-sm font-bold text-amber-700 w-full" required placeholder="0">
                </div>

                <div>
                    <label class="label text-xs" for="stock_out_note">تێبینی / بۆ چ کارێک بەکارهات</label>
                    <input id="stock_out_note" name="note" class="field text-xs w-full" placeholder="بۆ نموونە: بەکارهات بۆ دەرگای وەسڵی #12...">
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="showStockOutModal = false" class="btn btn-ghost !py-1.5 text-xs cursor-pointer">پاشگەزبوونەوە</button>
                    <button type="submit" class="btn btn-primary !py-1.5 !px-5 text-xs font-bold bg-amber-600 hover:bg-amber-700 shadow-sm cursor-pointer">تۆمارکردنی بەکارهێنان</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
