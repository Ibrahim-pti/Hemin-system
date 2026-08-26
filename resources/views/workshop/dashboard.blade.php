@extends('layouts.app')
@php
    $currentSection = request('section', 'dashboard');
    $titles = [
        'dashboard' => 'داشبۆردی سەرەکی کارگە',
        'orders' => 'داواکارییەکانی کارگە',
        'materials' => 'مەوادی خاو و کۆگا',
        'employees' => 'وەستا و حەمەڵەکان',
    ];
@endphp
@section('title', $titles[$currentSection] ?? 'بەشی کارگە و دروستکردن')

@if ($currentSection === 'materials')
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
@endif

@section('content')
<div x-data="{
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

    {{-- ============================================================ --}}
    {{-- ١. بەشی داشبۆردی سەرەکی (DASHBOARD OVERVIEW) --}}
    {{-- ============================================================ --}}
    @if ($currentSection === 'dashboard')
        {{-- سەرپەڕەی داشبۆرد --}}
        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-3.5">
                <div class="size-12 rounded-2xl bg-indigo-50 border border-indigo-100 text-indigo-600 flex items-center justify-center text-2xl shadow-xs">
                    ⚒️
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-xl font-black text-slate-800">داشبۆردی سەرەکی کارگە</h1>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200">
                            {{ $workshopWarehouse?->name ?? 'شوێنی دروستکردن' }}
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 mt-0.5 font-medium">
                        پوختەی گشتی کارەکانی دروستکردن، ئاگاداری مەوادە کەمبووەکان و دۆخی وەستاکان
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2 text-xs font-bold text-slate-600 bg-slate-100 px-3.5 py-2 rounded-xl border border-slate-200">
                <span>📅 ئەمڕۆ: {{ date('Y/m/d') }}</span>
            </div>
        </div>

        {{-- کارتە ئامارییەکان --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- ١. چاوەڕوانە --}}
            <a href="{{ route('workshop.index', ['section' => 'orders', 'tab' => 'pending']) }}"
               class="bg-white rounded-2xl p-4.5 border border-slate-200 hover:border-amber-400 transition-all hover:shadow-md group cursor-pointer">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold text-slate-600">چاوەڕوانی دروستکردن</span>
                    <span class="size-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-base font-bold border border-amber-200/60 group-hover:scale-110 transition-transform">⏳</span>
                </div>
                <div class="num text-2xl md:text-3xl font-black text-amber-600">{{ $pendingCount }}</div>
                <div class="text-[11px] text-slate-400 font-medium mt-1">ئیشی نوێی پەسەندکراو</div>
            </a>

            {{-- ٢. لە کاردایە (دروستدەکرێت) --}}
            <a href="{{ route('workshop.index', ['section' => 'orders', 'tab' => 'in_production']) }}"
               class="bg-white rounded-2xl p-4.5 border border-slate-200 hover:border-blue-400 transition-all hover:shadow-md group cursor-pointer">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold text-slate-600">لە ژێر کاردایە (دروستدەکرێت)</span>
                    <span class="size-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-base font-bold border border-blue-200/60 group-hover:scale-110 transition-transform">⚙️</span>
                </div>
                <div class="num text-2xl md:text-3xl font-black text-blue-600">{{ $inProductionCount }}</div>
                <div class="text-[11px] text-slate-400 font-medium mt-1">وەستاکان کاری لەسەر دەکەن</div>
            </a>

            {{-- ٣. تەواوبوو (ئامادەیە) --}}
            <a href="{{ route('workshop.index', ['section' => 'orders', 'tab' => 'ready']) }}"
               class="bg-white rounded-2xl p-4.5 border border-slate-200 hover:border-emerald-400 transition-all hover:shadow-md group cursor-pointer">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold text-slate-600">ئامادەیە بۆ وەرگرتن</span>
                    <span class="size-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-base font-bold border border-emerald-200/60 group-hover:scale-110 transition-transform">✅</span>
                </div>
                <div class="num text-2xl md:text-3xl font-black text-emerald-600">{{ $readyCount }}</div>
                <div class="text-[11px] text-slate-400 font-medium mt-1">دروستکراوە و ئامادەیە</div>
            </a>

            {{-- ٤. ڕادەستکراو --}}
            <a href="{{ route('workshop.index', ['section' => 'orders', 'tab' => 'delivered']) }}"
               class="bg-white rounded-2xl p-4.5 border border-slate-200 hover:border-slate-400 transition-all hover:shadow-md group cursor-pointer">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold text-slate-600">ئەمڕۆ ڕادەستکراوە</span>
                    <span class="size-9 rounded-xl bg-slate-50 text-slate-600 flex items-center justify-center text-base font-bold border border-slate-200/60 group-hover:scale-110 transition-transform">🚚</span>
                </div>
                <div class="num text-2xl md:text-3xl font-black text-slate-800">{{ $deliveredCount }}</div>
                <div class="text-[11px] text-slate-400 font-medium mt-1">کارە تەواوکراوەکان</div>
            </a>
        </div>

        {{-- ئاگاداری مەوادە کەمبووەکان (Low Stock Banner) --}}
        @if ($lowStockMaterials->isNotEmpty())
            <div class="bg-gradient-to-r from-rose-50 via-amber-50 to-orange-50 rounded-2xl p-4.5 border border-rose-200 shadow-xs">
                <div class="flex flex-wrap items-center justify-between gap-3 mb-3">
                    <div class="flex items-center gap-2.5">
                        <span class="flex size-9 items-center justify-center rounded-xl bg-rose-600 text-white font-bold text-base shadow-xs animate-bounce">
                            ⚠️
                        </span>
                        <div>
                            <h3 class="font-black text-sm text-rose-900 flex items-center gap-2">
                                <span>ئاگاداری: مەوادە کەمبووەکانی کارگە</span>
                                <span class="px-2 py-0.2 rounded-full bg-rose-100 text-rose-800 text-[11px] font-black border border-rose-200">
                                    {{ $lowStockMaterials->count() }} جۆر مەواد
                                </span>
                            </h3>
                            <p class="text-xs text-rose-700 font-medium mt-0.5">ئەم مەوادانە لە سنووری کەمترین بڕی پێویست کەمتریان ماوە و پێویستە پڕبکرێنەوە</p>
                        </div>
                    </div>
                    <a href="{{ route('workshop.index', ['section' => 'materials']) }}" class="text-xs font-bold text-rose-800 hover:text-rose-950 underline flex items-center gap-1 cursor-pointer">
                        <span>بەڕێوەبردنی مەوادەکان</span>
                        <span>←</span>
                    </a>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3">
                    @foreach ($lowStockMaterials as $lowMat)
                        <div class="bg-white rounded-xl p-3 border border-rose-200/80 flex items-center justify-between gap-2 shadow-2xs hover:border-rose-300 transition-all">
                            <div class="min-w-0 flex-1">
                                <div class="font-black text-xs text-slate-800 truncate" title="{{ $lowMat->name }}">{{ $lowMat->name }}</div>
                                <div class="text-[11px] text-slate-400 font-mono font-medium">{{ $lowMat->code }}</div>
                                <div class="text-xs font-black text-rose-600 mt-1 flex items-center gap-1.5">
                                    <span>ماوە: {{ fmt_num($lowMat->stock_qty) }} {{ $lowMat->unit?->name }}</span>
                                    <span class="text-slate-400 font-normal text-[10px]">(سنوور: {{ fmt_num($lowMat->min_qty) }})</span>
                                </div>
                            </div>
                            <button type="button" @click="showStockInModal = true" class="btn btn-ghost !py-1.5 !px-2.5 text-[11px] font-black text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-300 shrink-0 cursor-pointer shadow-2xs">
                                + زیادکردن
                            </button>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- پوختەی کارە چالاکەکان و وەستاکان بە دوو ستوون --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
            {{-- ستوونی ڕاست (٢ ستوون): دوایین داواکارییەکانی لە کاردان --}}
            <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
                <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                    <div class="flex items-center gap-2.5 font-black text-sm text-slate-800">
                        <span class="size-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-sm">📋</span>
                        <span>کارە چالاکەکانی لە دروستکردندان</span>
                    </div>
                    <a href="{{ route('workshop.index', ['section' => 'orders']) }}" class="text-xs font-bold text-blue-600 hover:text-blue-800">
                        هەموو داواکارییەکان &larr;
                    </a>
                </div>

                <div class="p-4">
                    @php
                        $activeOrders = $orders->filter(fn($o) => in_array($o->status, ['in_production', 'confirmed']))->take(6);
                    @endphp

                    @if ($activeOrders->isEmpty())
                        <div class="p-8 text-center text-slate-400 text-xs font-bold">هیچ کارێکی چالاک لە چاوەڕوانیدا نییە.</div>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                            @foreach ($activeOrders as $order)
                                <div class="border border-slate-200 rounded-xl p-3.5 hover:border-blue-300 transition-all flex flex-col justify-between">
                                    <div>
                                        <div class="flex items-center justify-between mb-2">
                                            <span class="font-black text-xs text-slate-800">وەسڵی #{{ $order->invoice_no }}</span>
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-black {{ $order->status === 'in_production' ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'bg-amber-50 text-amber-700 border border-amber-200' }}">
                                                {{ $order->status === 'in_production' ? '⚙️ لە دروستکردندایە' : '⏳ چاوەڕوانە' }}
                                            </span>
                                        </div>
                                        <div class="text-xs text-slate-600 font-bold mb-2">
                                            کڕیار: {{ $order->customer?->name ?? 'نەناسراو' }}
                                        </div>

                                        {{-- کەلوپەلەکان --}}
                                        <div class="space-y-1 bg-slate-50 p-2 rounded-lg border border-slate-100 text-[11px]">
                                            @foreach ($order->items->take(2) as $it)
                                                <div class="flex items-center justify-between text-slate-700">
                                                    <span class="font-bold truncate">{{ $it->item_name }}</span>
                                                    <span class="font-bold text-blue-600 shrink-0">{{ fmt_num($it->qty) }} دانا</span>
                                                </div>
                                            @endforeach
                                            @if ($order->items->count() > 2)
                                                <div class="text-[10px] text-slate-400 font-medium">+ {{ $order->items->count() - 2 }} بڕگەی تر</div>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="mt-3 pt-2.5 border-t border-slate-100 flex items-center justify-between">
                                        <form method="POST" action="{{ route('workshop.status', $order) }}">
                                            @csrf
                                            @if ($order->status === 'confirmed')
                                                <input type="hidden" name="status" value="in_production">
                                                <button type="submit" class="btn btn-primary !py-1 !px-2.5 text-[11px] font-black bg-blue-600 hover:bg-blue-700 cursor-pointer">
                                                    ⚙️ دەستپێکردن
                                                </button>
                                            @elseif ($order->status === 'in_production')
                                                <input type="hidden" name="status" value="ready">
                                                <button type="submit" class="btn btn-primary !py-1 !px-2.5 text-[11px] font-black bg-emerald-600 hover:bg-emerald-700 cursor-pointer">
                                                    ✅ تەواوبوو
                                                </button>
                                            @endif
                                        </form>

                                        <span class="text-[10px] text-slate-400 font-medium">
                                            {{ $order->order_date?->format('Y/m/d') }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            {{-- ستوونی چەپ (١ ستوون): وەستاکان و حەمەڵەکان --}}
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
                <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                    <div class="flex items-center gap-2.5 font-black text-sm text-slate-800">
                        <span class="size-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-sm">👷</span>
                        <span>وەستاکانی کارگە</span>
                    </div>
                    <a href="{{ route('workshop.index', ['section' => 'employees']) }}" class="text-xs font-bold text-blue-600 hover:text-blue-800">
                        هەمووی &larr;
                    </a>
                </div>

                <div class="p-4 space-y-2.5">
                    @forelse ($employees->take(5) as $emp)
                        @php
                            $todayAtt = $emp->attendances->first();
                        @endphp
                        <div class="p-3 rounded-xl border border-slate-100 bg-slate-50/70 flex items-center justify-between gap-2">
                            <div>
                                <div class="font-bold text-xs text-slate-800">{{ $emp->name }}</div>
                                <div class="text-[11px] text-slate-500 font-medium mt-0.5">{{ $emp->job_title_label }}</div>
                            </div>
                            <div>
                                @if ($todayAtt && $todayAtt->status === 'present')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700 border border-emerald-200">ئامادەیە ✅</span>
                                @elseif ($todayAtt && $todayAtt->status === 'absent')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-700 border border-rose-200">نەهاتووە ❌</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-medium bg-slate-200 text-slate-600">چالاکە</span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="p-6 text-center text-slate-400 text-xs font-medium">هیچ وەستایەک تۆمار نەکراوە.</div>
                    @endforelse
                </div>
            </div>
        </div>

    {{-- ============================================================ --}}
    {{-- ٢. بەشی داواکارییەکان و وەسڵەکانی کارگە (ORDERS ONLY) --}}
    {{-- ============================================================ --}}
    @elseif ($currentSection === 'orders')
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

        {{-- فلتەری دۆخەکان و گەڕان --}}
        <div class="bg-white rounded-2xl p-3.5 border border-slate-200 shadow-xs flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-2 text-xs font-bold">
                <a href="{{ route('workshop.index', ['section' => 'orders', 'tab' => 'all']) }}"
                   class="px-3.5 py-1.5 rounded-xl transition-all {{ $tab === 'all' ? 'bg-slate-900 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    هەموو کارەکان
                </a>
                <a href="{{ route('workshop.index', ['section' => 'orders', 'tab' => 'in_production']) }}"
                   class="px-3.5 py-1.5 rounded-xl transition-all {{ $tab === 'in_production' ? 'bg-blue-600 text-white shadow-xs' : 'bg-blue-50 text-blue-700 hover:bg-blue-100' }}">
                    ⚙️ لە دروستکردندا ({{ $inProductionCount }})
                </a>
                <a href="{{ route('workshop.index', ['section' => 'orders', 'tab' => 'pending']) }}"
                   class="px-3.5 py-1.5 rounded-xl transition-all {{ $tab === 'pending' ? 'bg-amber-500 text-white shadow-xs' : 'bg-amber-50 text-amber-700 hover:bg-amber-100' }}">
                    ⏳ چاوەڕوانە ({{ $pendingCount }})
                </a>
                <a href="{{ route('workshop.index', ['section' => 'orders', 'tab' => 'ready']) }}"
                   class="px-3.5 py-1.5 rounded-xl transition-all {{ $tab === 'ready' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100' }}">
                    ✅ ئامادەیە ({{ $readyCount }})
                </a>
                <a href="{{ route('workshop.index', ['section' => 'orders', 'tab' => 'delivered']) }}"
                   class="px-3.5 py-1.5 rounded-xl transition-all {{ $tab === 'delivered' ? 'bg-slate-700 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    🚚 ڕادەستکراوە
                </a>
            </div>

            <form method="GET" action="{{ route('workshop.index') }}" class="flex items-center gap-2">
                <input type="hidden" name="section" value="orders">
                <input type="hidden" name="tab" value="{{ $tab }}">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="گەڕان بە ناوی کڕیار یان ژمارە وەسڵ..."
                       class="text-xs px-3 py-1.5 rounded-xl border border-slate-200 w-60 focus:outline-hidden focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                <button type="submit" class="btn btn-primary !py-1.5 !px-3 text-xs">گەڕان</button>
            </form>
        </div>

        {{-- خشتە/کارتەکانی وەسڵەکان --}}
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
                                        {{-- وێنەی کاڵا --}}
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

    {{-- ============================================================ --}}
    {{-- ٣. بەشی مەوادی خاو و کۆگا (MATERIALS ONLY) --}}
    {{-- ============================================================ --}}
    @elseif ($currentSection === 'materials')
        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-3.5">
                <div class="size-12 rounded-2xl bg-amber-50 border border-amber-100 text-amber-600 flex items-center justify-center text-2xl shadow-xs">
                    📦
                </div>
                <div>
                    <h1 class="text-xl font-black text-slate-800">مەوادی خاو و کۆگای کارگە</h1>
                    <p class="text-xs text-slate-500 mt-0.5 font-medium">
                        کەرەستەی بەردەست لە شوێنی دروستکردن، زیادکردن، بەکارهێنان و ئاگاداری کەمبوونەوە
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <button type="button" @click="showStockInModal = true" class="btn btn-ghost !py-1.5 !px-3 text-xs font-bold border border-emerald-300 text-emerald-700 bg-emerald-50 hover:bg-emerald-100 cursor-pointer">
                    📥 هاتنی مەواد
                </button>
                <button type="button" @click="showStockOutModal = true" class="btn btn-ghost !py-1.5 !px-3 text-xs font-bold border border-amber-300 text-amber-700 bg-amber-50 hover:bg-amber-100 cursor-pointer">
                    📤 بەکارهێنانی مەواد
                </button>
                <button type="button" @click="showNewMaterialModal = true" class="btn btn-primary !py-1.5 !px-3.5 text-xs font-bold bg-blue-600 hover:bg-blue-700 cursor-pointer">
                    + مەوادی نوێ
                </button>
            </div>
        </div>

        {{-- ئاگاداری مەوادە کەمبووەکان لەناو پەڕەی مەواد --}}
        @if ($lowStockMaterials->isNotEmpty())
            <div class="bg-gradient-to-r from-rose-50 to-amber-50 rounded-2xl p-4 border border-rose-200 shadow-xs">
                <div class="flex items-center gap-2.5 mb-2.5">
                    <span class="flex size-7 items-center justify-center rounded-lg bg-rose-600 text-white font-bold text-xs shadow-xs">⚠️</span>
                    <h3 class="font-black text-xs text-rose-900">مەوادە کەمبووەکانی کارگە ({{ $lowStockMaterials->count() }} جۆر)</h3>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2.5">
                    @foreach ($lowStockMaterials as $lowMat)
                        <div class="bg-white rounded-xl p-2.5 border border-rose-200 flex items-center justify-between gap-2 shadow-2xs">
                            <div class="min-w-0">
                                <div class="font-bold text-xs text-slate-800 truncate">{{ $lowMat->name }}</div>
                                <div class="text-[11px] font-black text-rose-600">ماوە: {{ fmt_num($lowMat->stock_qty) }} {{ $lowMat->unit?->name }}</div>
                            </div>
                            <button type="button" @click="showStockInModal = true" class="btn btn-ghost !py-1 !px-2 text-[10px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-300 cursor-pointer">
                                + زیادکردن
                            </button>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- خشتەی سەرەکی مەوادەکان --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
            <div class="p-4 border-b border-slate-100 flex flex-wrap items-center justify-between gap-3">
                <div class="text-xs font-bold text-slate-600">
                    کۆی گشتی: <span class="text-slate-900 font-black">{{ $rawMaterials->total() }}</span> جۆر مەواد
                </div>
                <form method="GET" action="{{ route('workshop.index') }}" class="flex items-center gap-2">
                    <input type="hidden" name="section" value="materials">
                    <input type="text" name="mat_q" value="{{ request('mat_q') }}" placeholder="گەڕان بە ناوی مەواد یان کۆد..."
                           class="text-xs px-3 py-1.5 rounded-xl border border-slate-200 w-60 focus:outline-hidden focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                    <button type="submit" class="btn btn-primary !py-1.5 !px-3 text-xs">گەڕان</button>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-right text-xs">
                    <thead class="bg-slate-50 text-slate-500 border-b border-slate-200">
                        <tr>
                            <th class="p-3.5 font-bold">کۆد</th>
                            <th class="p-3.5 font-bold">ناوی مەواد</th>
                            <th class="p-3.5 font-bold">جۆر (بەش)</th>
                            <th class="p-3.5 font-bold text-center">بڕی بەردەست</th>
                            <th class="p-3.5 font-bold text-center">کەمترین بڕ</th>
                            <th class="p-3.5 font-bold text-center">دۆخ</th>
                            <th class="p-3.5 font-bold text-center">کردار</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($rawMaterials as $mat)
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="p-3.5 font-mono text-slate-500 font-medium">{{ $mat->code }}</td>
                                <td class="p-3.5 font-bold text-slate-900">{{ $mat->name }}</td>
                                <td class="p-3.5 text-slate-500">{{ $mat->category?->name ?? '—' }}</td>
                                <td class="p-3.5 text-center font-black text-sm num {{ $mat->is_low ? 'text-rose-600' : 'text-slate-800' }}">
                                    {{ fmt_num($mat->stock_qty) }} <span class="text-xs font-normal text-slate-500">{{ $mat->unit?->name }}</span>
                                </td>
                                <td class="p-3.5 text-center font-medium text-slate-500 num">
                                    {{ fmt_num($mat->min_qty) }} {{ $mat->unit?->name }}
                                </td>
                                <td class="p-3.5 text-center">
                                    @if ($mat->is_low)
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-700 border border-rose-200 animate-pulse">
                                            کەمە ⚠️
                                        </span>
                                    @else
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700 border border-emerald-200">
                                            بەردەستە ✔️
                                        </span>
                                    @endif
                                </td>
                                <td class="p-3.5 text-center">
                                    <div class="inline-flex items-center gap-1.5">
                                        <button type="button" @click="showStockInModal = true"
                                                class="px-2 py-1 rounded-lg text-[11px] font-bold bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200 cursor-pointer">
                                            + هاتن
                                        </button>
                                        <button type="button" @click="showStockOutModal = true"
                                                class="px-2 py-1 rounded-lg text-[11px] font-bold bg-amber-50 text-amber-700 hover:bg-amber-100 border border-amber-200 cursor-pointer">
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

    {{-- ============================================================ --}}
    {{-- ٤. بەشی وەستا و حەمەڵەکان (EMPLOYEES ONLY) --}}
    {{-- ============================================================ --}}
    @elseif ($currentSection === 'employees')
        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-3.5">
                <div class="size-12 rounded-2xl bg-indigo-50 border border-indigo-100 text-indigo-600 flex items-center justify-center text-2xl shadow-xs">
                    👷
                </div>
                <div>
                    <h1 class="text-xl font-black text-slate-800">لیستی وەستاکان، حەمەڵەکان و کارمەندانی کارگە</h1>
                    <p class="text-xs text-slate-500 mt-0.5 font-medium">
                        کۆی گشتی: {{ $employees->count() }} کەسی چالاک (زیادکردن و بەڕێوەبردن لە ئۆفیسی بەڕێوەبەرەوەیە)
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2 text-xs font-bold text-slate-600 bg-slate-100/80 px-3.5 py-2 rounded-xl border border-slate-200">
                <span>📅 ئەمڕۆ: {{ date('Y/m/d') }}</span>
            </div>
        </div>

        @if ($employees->isEmpty())
            <div class="bg-white rounded-2xl p-12 text-center border border-slate-200 shadow-xs">
                <div class="text-4xl mb-2.5">👷‍♂️</div>
                <div class="font-bold text-slate-700 text-base">هیچ وەستا یان کارمەندێک تۆمار نەکراوە</div>
                <div class="text-xs text-slate-400 mt-1">بەڕێوەبەر لە بەشی کارمەندان دەتوانێت وەستا و حەمەڵەکان زیاد بکات.</div>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                @foreach ($employees as $emp)
                    @php
                        $todayAtt = $emp->attendances->first();
                        $roleIcons = [
                            'master' => '⚒️',
                            'porter' => '📦',
                            'helper' => '🤝',
                            'driver' => '🚚',
                            'other' => '👤',
                        ];
                        $roleColors = [
                            'master' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                            'porter' => 'bg-amber-50 text-amber-700 border-amber-200',
                            'helper' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                            'driver' => 'bg-blue-50 text-blue-700 border-blue-200',
                            'other' => 'bg-slate-100 text-slate-700 border-slate-200',
                        ];
                    @endphp
                    <div class="bg-white rounded-2xl p-4.5 border border-slate-200 shadow-xs flex flex-col justify-between hover:shadow-md transition-all">
                        <div>
                            <div class="flex items-center justify-between mb-3">
                                <span class="px-2.5 py-1 rounded-xl text-xs font-bold border flex items-center gap-1.5 {{ $roleColors[$emp->job_title] ?? 'bg-slate-100 text-slate-700 border-slate-200' }}">
                                    <span>{{ $roleIcons[$emp->job_title] ?? '👤' }}</span>
                                    <span>{{ $emp->job_title_label }}</span>
                                </span>

                                {{-- دۆخی ئامادەبوونی ئەمڕۆ --}}
                                @if ($todayAtt)
                                    @if ($todayAtt->status === 'present')
                                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-700 border border-emerald-200 flex items-center gap-1">
                                            <span class="size-1.5 rounded-full bg-emerald-500"></span>
                                            <span>ئامادەیە</span>
                                        </span>
                                    @elseif ($todayAtt->status === 'absent')
                                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-rose-100 text-rose-700 border border-rose-200 flex items-center gap-1">
                                            <span class="size-1.5 rounded-full bg-rose-500"></span>
                                            <span>نەهاتووە</span>
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-amber-100 text-amber-700 border border-amber-200">
                                            {{ $todayAtt->status }}
                                        </span>
                                    @endif
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-500 border border-slate-200">
                                        چالاکە
                                    </span>
                                @endif
                            </div>

                            <h4 class="font-black text-slate-800 text-base mb-1.5">{{ $emp->name }}</h4>
                            
                            @if ($emp->phone)
                                <a href="tel:{{ $emp->phone }}" class="text-xs text-blue-600 hover:text-blue-700 font-mono font-medium flex items-center gap-1.5 direction-ltr text-right mb-2">
                                    <span>📞</span>
                                    <span dir="ltr">{{ $emp->phone }}</span>
                                </a>
                            @else
                                <div class="text-xs text-slate-400 font-medium mb-2">ژمارەی مۆبایل نییە</div>
                            @endif

                            @if ($emp->note)
                                <p class="text-xs text-slate-500 bg-slate-50 p-2.5 rounded-xl border border-slate-100 line-clamp-2 mt-2">
                                    {{ $emp->note }}
                                </p>
                            @endif
                        </div>

                        <div class="mt-4 pt-3 border-t border-slate-100 text-[11px] text-slate-400 flex items-center justify-between">
                            <span>دەستپێکی کار:</span>
                            <span class="font-medium text-slate-700">{{ $emp->hire_date?->format('Y/m/d') ?? '—' }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    @endif

    {{-- ============================================================ --}}
    {{-- مۆداڵەکان (MODALS) --}}
    {{-- ============================================================ --}}

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
                    <label class="block text-xs font-bold text-slate-700 mb-1">ناوی مەواد <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" required placeholder="بۆ نموونە: شیشی ١٢ ملیم، پەڕەی ئاسن..."
                           class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:outline-hidden focus:border-blue-500">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">جۆر / بەش</label>
                        <select name="item_category_id" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200">
                            <option value="">دیارینەکراوە</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">یەکە <span class="text-rose-500">*</span></label>
                        <select name="unit_id" required class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200">
                            @foreach ($units as $u)
                                <option value="{{ $u->id }}">{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">بڕی سەرەتایی</label>
                        <input type="number" step="any" name="initial_qty" value="0"
                               class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 font-mono">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">کەمترین بڕ (ئاگاداری)</label>
                        <input type="number" step="any" name="min_qty" value="5"
                               class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 font-mono">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">تێبینی</label>
                    <input type="text" name="note" placeholder="تێبینییەکی کورت..."
                           class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200">
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                    <button type="button" @click="showNewMaterialModal = false" class="btn btn-ghost !py-1.5 text-xs">پاشگەزبوونەوە</button>
                    <button type="submit" class="btn btn-primary !py-1.5 text-xs font-bold bg-blue-600 hover:bg-blue-700">تۆمارکردن</button>
                </div>
            </form>
        </div>
    </div>

    {{-- مۆداڵی زیادکردنی بڕ بۆ مەواد (هاتن - Stock In) --}}
    <div x-show="showStockInModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4" x-transition.opacity>
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-5 border border-slate-200 text-right" @click.away="showStockInModal = false" x-transition.scale>
            <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                <div class="font-bold text-slate-800 text-base flex items-center gap-2">
                    <span class="size-8 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center text-sm font-bold">📥</span>
                    <span>هاتنی مەواد بۆ کارگە (زیادکردنی بڕ)</span>
                </div>
                <button type="button" @click="showStockInModal = false" class="text-slate-400 hover:text-slate-600 size-7 rounded-lg flex items-center justify-center cursor-pointer">✕</button>
            </div>

            <form method="POST" action="{{ route('workshop.stock-in') }}" class="space-y-3.5">
                @csrf
                <input type="hidden" name="warehouse_id" value="{{ $workshopWarehouse?->id }}">

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">هەڵبژاردنی مەواد <span class="text-rose-500">*</span></label>
                    <select name="item_id" required class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200">
                        <option value="">-- مەوادەکە هەڵبژێرە --</option>
                        @foreach ($allItems as $it)
                            <option value="{{ $it->id }}">{{ $it->name }} ({{ $it->code }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">بڕی هاتووە <span class="text-rose-500">*</span></label>
                    <input type="number" step="any" name="qty" required placeholder="0.00"
                           class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 font-mono text-base font-bold text-emerald-700">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">تێبینی (سەرچاوە یان وەسڵ)</label>
                    <input type="text" name="note" placeholder="بۆ نموونە: کڕین لە بازاڕ، گواستنەوە..."
                           class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200">
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                    <button type="button" @click="showStockInModal = false" class="btn btn-ghost !py-1.5 text-xs">پاشگەزبوونەوە</button>
                    <button type="submit" class="btn btn-primary !py-1.5 text-xs font-bold bg-emerald-600 hover:bg-emerald-700">تۆمارکردنی هاتن</button>
                </div>
            </form>
        </div>
    </div>

    {{-- مۆداڵی بەکارهێنانی مەواد (ڕۆشتن - Stock Out) --}}
    <div x-show="showStockOutModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4" x-transition.opacity>
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-5 border border-slate-200 text-right" @click.away="showStockOutModal = false" x-transition.scale>
            <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                <div class="font-bold text-slate-800 text-base flex items-center gap-2">
                    <span class="size-8 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center text-sm font-bold">📤</span>
                    <span>بەکارهێنان و کەمکردنەوەی مەواد</span>
                </div>
                <button type="button" @click="showStockOutModal = false" class="text-slate-400 hover:text-slate-600 size-7 rounded-lg flex items-center justify-center cursor-pointer">✕</button>
            </div>

            <form method="POST" action="{{ route('workshop.stock-out') }}" class="space-y-3.5">
                @csrf
                <input type="hidden" name="warehouse_id" value="{{ $workshopWarehouse?->id }}">

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">هەڵبژاردنی مەواد <span class="text-rose-500">*</span></label>
                    <select name="item_id" required class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200">
                        <option value="">-- مەوادەکە هەڵبژێرە --</option>
                        @foreach ($allItems as $it)
                            <option value="{{ $it->id }}">{{ $it->name }} ({{ $it->code }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">بڕی بەکارهاتوو <span class="text-rose-500">*</span></label>
                    <input type="number" step="any" name="qty" required placeholder="0.00"
                           class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 font-mono text-base font-bold text-amber-700">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">تێبینی یان ژمارەی وەسڵ</label>
                    <input type="text" name="note" placeholder="بۆ نموونە: بەکارهات لە دروستکردنی دەرگای وەسڵی #12..."
                           class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200">
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                    <button type="button" @click="showStockOutModal = false" class="btn btn-ghost !py-1.5 text-xs">پاشگەزبوونەوە</button>
                    <button type="submit" class="btn btn-primary !py-1.5 text-xs font-bold bg-amber-600 hover:bg-amber-700">تۆمارکردنی بەکارهێنان</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
