@extends('layouts.menu')
@section('title', 'داشبۆردی کارگە و وەستاکان')

@section('content')
<div x-data="workshopApp()" class="space-y-6">

    {{-- ١. هێڵی سەرەوە: ناونیشان و کاتژمێری زیندوو هاوشێوەی داشبۆردی بەڕێوەبەر --}}
    <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-3.5">
            <div class="size-12 rounded-2xl bg-indigo-50 border border-indigo-100 text-indigo-600 flex items-center justify-center text-2xl shadow-xs">
                ⚒️
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-xl font-black text-slate-800">داشبۆردی کارگە و وەستاکان</h1>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200">
                        {{ $workshopWarehouse?->name ?? 'شوێنی دروستکردن' }}
                    </span>
                </div>
                <p class="text-xs text-slate-500 mt-0.5 font-medium">
                    چاودێری وەسڵەکانی دروستکردن، مەوادی خاو و لیستی وەستاکان بە شێوازی ڕاستەوخۆ
                </p>
            </div>
        </div>

        {{-- تابی سەرەکی نێوان بەشەکان (بەبێ ڕیفرێش - Zero Reload) --}}
        <div class="flex flex-wrap items-center bg-slate-100 p-1 rounded-xl border border-slate-200 text-xs font-bold gap-1">
            <button type="button" @click="setTab('dashboard')"
                    :class="activeTab === 'dashboard' ? 'bg-white text-blue-600 shadow-xs border border-slate-200/80' : 'text-slate-600 hover:text-slate-900'"
                    class="px-3.5 py-2 rounded-lg transition-all flex items-center gap-2 cursor-pointer">
                <span>📊 پوختەی داشبۆرد</span>
            </button>

            <button type="button" @click="setTab('orders')"
                    :class="activeTab === 'orders' ? 'bg-white text-blue-600 shadow-xs border border-slate-200/80' : 'text-slate-600 hover:text-slate-900'"
                    class="px-3.5 py-2 rounded-lg transition-all flex items-center gap-2 cursor-pointer">
                <span>📋 داواکارییەکان</span>
                <span class="px-1.5 py-0.2 rounded-full text-[11px] font-bold"
                      :class="activeTab === 'orders' ? 'bg-blue-100 text-blue-700' : 'bg-slate-200 text-slate-700'"
                      x-text="pendingCount + inProductionCount"></span>
            </button>

            <button type="button" @click="setTab('materials')"
                    :class="activeTab === 'materials' ? 'bg-white text-blue-600 shadow-xs border border-slate-200/80' : 'text-slate-600 hover:text-slate-900'"
                    class="px-3.5 py-2 rounded-lg transition-all flex items-center gap-2 cursor-pointer">
                <span>📦 مەوادی خاو</span>
                @if ($lowStockMaterials->isNotEmpty())
                    <span class="px-1.5 py-0.2 rounded-full text-[11px] font-bold bg-rose-500 text-white animate-pulse">
                        {{ $lowStockMaterials->count() }} کەمە
                    </span>
                @endif
            </button>

            <button type="button" @click="setTab('employees')"
                    :class="activeTab === 'employees' ? 'bg-white text-blue-600 shadow-xs border border-slate-200/80' : 'text-slate-600 hover:text-slate-900'"
                    class="px-3.5 py-2 rounded-lg transition-all flex items-center gap-2 cursor-pointer">
                <span>👷 وەستا و حەمەڵەکان</span>
                <span class="px-1.5 py-0.2 rounded-full text-[11px] font-bold bg-slate-200 text-slate-700">
                    {{ $employees->count() }}
                </span>
            </button>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- ١. بەشی داشبۆردی سەرەکی (DASHBOARD TAB) --}}
    {{-- ============================================================ --}}
    <div x-show="activeTab === 'dashboard'" class="space-y-6" x-transition.opacity>
        {{-- کارتە ئامارییەکان --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- ١. چاوەڕوانە --}}
            <div @click="setTab('orders'); orderFilter = 'pending'"
                 class="bg-white rounded-2xl p-4.5 border border-slate-200 hover:border-amber-400 transition-all hover:shadow-md group cursor-pointer">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold text-slate-600">چاوەڕوانی دروستکردن</span>
                    <span class="size-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-base font-bold border border-amber-200/60 group-hover:scale-110 transition-transform">⏳</span>
                </div>
                <div class="num text-2xl md:text-3xl font-black text-amber-600" x-text="pendingCount"></div>
                <div class="text-[11px] text-slate-400 font-medium mt-1">ئیشی نوێی پەسەندکراو</div>
            </div>

            {{-- ٢. لە کاردایە (دروستدەکرێت) --}}
            <div @click="setTab('orders'); orderFilter = 'in_production'"
                 class="bg-white rounded-2xl p-4.5 border border-slate-200 hover:border-blue-400 transition-all hover:shadow-md group cursor-pointer">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold text-slate-600">لە ژێر کاردایە (دروستدەکرێت)</span>
                    <span class="size-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-base font-bold border border-blue-200/60 group-hover:scale-110 transition-transform">⚙️</span>
                </div>
                <div class="num text-2xl md:text-3xl font-black text-blue-600" x-text="inProductionCount"></div>
                <div class="text-[11px] text-slate-400 font-medium mt-1">وەستاکان کاری لەسەر دەکەن</div>
            </div>

            {{-- ٣. تەواوبوو (ئامادەیە) --}}
            <div @click="setTab('orders'); orderFilter = 'ready'"
                 class="bg-white rounded-2xl p-4.5 border border-slate-200 hover:border-emerald-400 transition-all hover:shadow-md group cursor-pointer">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold text-slate-600">ئامادەیە بۆ وەرگرتن</span>
                    <span class="size-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-base font-bold border border-emerald-200/60 group-hover:scale-110 transition-transform">✅</span>
                </div>
                <div class="num text-2xl md:text-3xl font-black text-emerald-600" x-text="readyCount"></div>
                <div class="text-[11px] text-slate-400 font-medium mt-1">دروستکراوە و ئامادەیە</div>
            </div>

            {{-- ٤. ڕادەستکراو --}}
            <div @click="setTab('orders'); orderFilter = 'delivered'"
                 class="bg-white rounded-2xl p-4.5 border border-slate-200 hover:border-slate-400 transition-all hover:shadow-md group cursor-pointer">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold text-slate-600">ئەمڕۆ ڕادەستکراوە</span>
                    <span class="size-9 rounded-xl bg-slate-50 text-slate-600 flex items-center justify-center text-base font-bold border border-slate-200/60 group-hover:scale-110 transition-transform">🚚</span>
                </div>
                <div class="num text-2xl md:text-3xl font-black text-slate-800" x-text="deliveredCount"></div>
                <div class="text-[11px] text-slate-400 font-medium mt-1">کارە تەواوکراوەکان</div>
            </div>
        </div>

        {{-- ئاگاداری مەوادە کەمبووەکان (Low Stock Alert Banner) --}}
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
                            <p class="text-xs text-rose-700 font-medium mt-0.5">ئەم مەوادانە لە سنووری کەمترین بڕی پێویست کەمتریان ماوە</p>
                        </div>
                    </div>
                    <button type="button" @click="setTab('materials')" class="text-xs font-bold text-rose-800 hover:text-rose-950 underline flex items-center gap-1 cursor-pointer">
                        <span>بەڕێوەبردنی مەوادەکان</span>
                        <span>←</span>
                    </button>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3">
                    @foreach ($lowStockMaterials as $lowMat)
                        <div class="bg-white rounded-xl p-3 border border-rose-200/80 flex items-center justify-between gap-2 shadow-2xs hover:border-rose-300 transition-all">
                            <div class="min-w-0 flex-1">
                                <div class="font-black text-xs text-slate-800 truncate">{{ $lowMat->name }}</div>
                                <div class="text-[11px] text-slate-400 font-mono font-medium">{{ $lowMat->code }}</div>
                                <div class="text-xs font-black text-rose-600 mt-1 flex items-center gap-1.5">
                                    <span>ماوە: {{ fmt_num($lowMat->stock_qty) }} {{ $lowMat->unit?->name }}</span>
                                    <span class="text-slate-400 font-normal text-[10px]">(سنوور: {{ fmt_num($lowMat->min_qty) }})</span>
                                </div>
                            </div>
                            <button type="button" @click="openStockInModalFor({{ $lowMat->id }})" class="btn btn-ghost !py-1.5 !px-2.5 text-[11px] font-black text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-300 shrink-0 cursor-pointer shadow-2xs">
                                + زیادکردن
                            </button>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- پوختەی کارە چالاکەکان و وەستاکان بە دوو ستوون --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
            {{-- ستوونی ڕاست: کارە چالاکەکان --}}
            <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
                <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                    <div class="flex items-center gap-2.5 font-black text-sm text-slate-800">
                        <span class="size-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-sm">📋</span>
                        <span>کارە چالاکەکانی لە دروستکردندان</span>
                    </div>
                    <button type="button" @click="setTab('orders')" class="text-xs font-bold text-blue-600 hover:text-blue-800 cursor-pointer">
                        هەموو داواکارییەکان &larr;
                    </button>
                </div>

                <div class="p-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                        <template x-for="order in activeOrdersList" :key="order.id">
                            <div class="border border-slate-200 rounded-xl p-3.5 hover:border-blue-300 transition-all flex flex-col justify-between">
                                <div>
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="font-black text-xs text-slate-800" x-text="'وەسڵی #' + order.invoice_no"></span>
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-black"
                                              :class="order.status === 'in_production' ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'bg-amber-50 text-amber-700 border border-amber-200'"
                                              x-text="order.status === 'in_production' ? '⚙️ لە دروستکردندایە' : '⏳ چاوەڕوانە'"></span>
                                    </div>
                                    <div class="text-xs text-slate-600 font-bold mb-2">
                                        کڕیار: <span class="text-blue-600 font-black" x-text="order.customer_name"></span>
                                    </div>

                                    <div class="space-y-1 bg-slate-50 p-2 rounded-lg border border-slate-100 text-[11px]">
                                        <template x-for="it in order.items.slice(0, 2)" :key="it.id">
                                            <div class="flex items-center justify-between text-slate-700">
                                                <span class="font-bold truncate" x-text="it.item_name"></span>
                                                <span class="font-bold text-blue-600 shrink-0" x-text="it.qty + ' ' + (it.unit_name || 'دانە')"></span>
                                            </div>
                                        </template>
                                        <div x-show="order.items.length > 2" class="text-[10px] text-slate-400 font-medium" x-text="'+ ' + (order.items.length - 2) + ' بڕگەی تر'"></div>
                                    </div>
                                </div>

                                <div class="mt-3 pt-2.5 border-t border-slate-100 flex items-center justify-between">
                                    <div>
                                        <button x-show="order.status === 'confirmed'" type="button" @click="changeStatus(order.id, 'in_production')"
                                                class="btn btn-primary !py-1 !px-2.5 text-[11px] font-black bg-blue-600 hover:bg-blue-700 cursor-pointer">
                                            ⚙️ دەستپێکردن
                                        </button>
                                        <button x-show="order.status === 'in_production'" type="button" @click="changeStatus(order.id, 'ready')"
                                                class="btn btn-primary !py-1 !px-2.5 text-[11px] font-black bg-emerald-600 hover:bg-emerald-700 cursor-pointer">
                                            ✅ تەواوبوو
                                        </button>
                                    </div>
                                    <span class="text-[10px] text-slate-400 font-medium" x-text="order.order_date"></span>
                                </div>
                            </div>
                        </template>

                        <div x-show="activeOrdersList.length === 0" class="col-span-2 p-8 text-center text-slate-400 text-xs font-bold">
                            هیچ کارێکی چالاک لە چاوەڕوانیدا نییە.
                        </div>
                    </div>
                </div>
            </div>

            {{-- ستوونی چەپ: وەستاکان --}}
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
                <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                    <div class="flex items-center gap-2.5 font-black text-sm text-slate-800">
                        <span class="size-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-sm">👷</span>
                        <span>وەستاکانی کارگە</span>
                    </div>
                    <button type="button" @click="setTab('employees')" class="text-xs font-bold text-blue-600 hover:text-blue-800 cursor-pointer">
                        هەمووی &larr;
                    </button>
                </div>

                <div class="p-4 space-y-2.5">
                    @forelse ($employees->take(6) as $emp)
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
    </div>

    {{-- ============================================================ --}}
    {{-- ٢. بەشی داواکارییەکان و وەسڵەکانی کارگە (ORDERS TAB) --}}
    {{-- ============================================================ --}}
    <div x-show="activeTab === 'orders'" class="space-y-6" x-transition.opacity x-cloak>
        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-3.5">
                <div class="size-12 rounded-2xl bg-blue-50 border border-blue-100 text-blue-600 flex items-center justify-center text-2xl shadow-xs">
                    📋
                </div>
                <div>
                    <h2 class="text-xl font-black text-slate-800">داواکارییەکان و وەسڵەکانی کارگە</h2>
                    <p class="text-xs text-slate-500 mt-0.5 font-medium">
                        فلتەرکردن و گەڕانی خێرا، چاودێری شتە داواکراوەکان و گۆڕینی قۆناغی کار
                    </p>
                </div>
            </div>
        </div>

        {{-- فلتەری دۆخەکان و گەڕانی زیندوو (Instant Alpine Filter) --}}
        <div class="bg-white rounded-2xl p-3.5 border border-slate-200 shadow-xs flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-2 text-xs font-bold">
                <button type="button" @click="orderFilter = 'all'"
                        :class="orderFilter === 'all' ? 'bg-slate-900 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                        class="px-3.5 py-1.5 rounded-xl transition-all cursor-pointer">
                    هەموو کارەکان (<span x-text="ordersList.length"></span>)
                </button>
                <button type="button" @click="orderFilter = 'in_production'"
                        :class="orderFilter === 'in_production' ? 'bg-blue-600 text-white shadow-xs' : 'bg-blue-50 text-blue-700 hover:bg-blue-100'"
                        class="px-3.5 py-1.5 rounded-xl transition-all cursor-pointer">
                    ⚙️ لە دروستکردندا (<span x-text="inProductionCount"></span>)
                </button>
                <button type="button" @click="orderFilter = 'confirmed'"
                        :class="orderFilter === 'confirmed' ? 'bg-amber-500 text-white shadow-xs' : 'bg-amber-50 text-amber-700 hover:bg-amber-100'"
                        class="px-3.5 py-1.5 rounded-xl transition-all cursor-pointer">
                    ⏳ چاوەڕوانە (<span x-text="pendingCount"></span>)
                </button>
                <button type="button" @click="orderFilter = 'ready'"
                        :class="orderFilter === 'ready' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100'"
                        class="px-3.5 py-1.5 rounded-xl transition-all cursor-pointer">
                    ✅ ئامادەیە (<span x-text="readyCount"></span>)
                </button>
                <button type="button" @click="orderFilter = 'delivered'"
                        :class="orderFilter === 'delivered' ? 'bg-slate-700 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                        class="px-3.5 py-1.5 rounded-xl transition-all cursor-pointer">
                    🚚 ڕادەستکراوە (<span x-text="deliveredCount"></span>)
                </button>
            </div>

            <div class="flex items-center gap-2">
                <input type="text" x-model="orderSearch" placeholder="گەڕانی خێرا بە ناوی کڕیار یان ژمارە..."
                       class="text-xs px-3 py-1.5 rounded-xl border border-slate-200 w-60 focus:outline-hidden focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
            </div>
        </div>

        {{-- کارتی وەسڵەکان بە داتای زیندوو --}}
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
            <template x-for="order in filteredOrders" :key="order.id">
                <div class="rounded-2xl border p-4.5 shadow-xs flex flex-col justify-between transition-all hover:shadow-md bg-white"
                     :class="{
                         'border-blue-300 bg-blue-50/20 ring-1 ring-blue-200': order.status === 'in_production',
                         'border-amber-200': order.status === 'confirmed',
                         'border-emerald-300 bg-emerald-50/20': order.status === 'ready',
                         'border-slate-200 bg-slate-50/50 opacity-80': order.status === 'delivered',
                     }">
                    <div>
                        <div class="flex items-start justify-between gap-2 border-b border-slate-100 pb-3 mb-3">
                            <div>
                                <div class="flex items-center gap-2">
                                    <h3 class="font-black text-slate-900 text-base" x-text="'وەسڵی #' + order.invoice_no"></h3>
                                    <span class="px-2 py-0.5 rounded-full text-[11px] font-bold"
                                          :class="{
                                              'bg-blue-100 text-blue-800 border border-blue-200': order.status === 'in_production',
                                              'bg-amber-100 text-amber-800 border border-amber-200': order.status === 'confirmed',
                                              'bg-emerald-100 text-emerald-800 border border-emerald-200': order.status === 'ready',
                                              'bg-slate-100 text-slate-700 border border-slate-200': order.status === 'delivered',
                                          }"
                                          x-text="order.status_label"></span>
                                </div>
                                <div class="text-xs text-slate-500 font-bold mt-1">
                                    کڕیار: <span class="text-blue-600 font-black" x-text="order.customer_name"></span>
                                    <span x-show="order.customer_phone" class="text-slate-400 font-normal" x-text="'(' + order.customer_phone + ')'"></span>
                                </div>
                            </div>
                            <div class="text-left text-[11px] text-slate-400 font-medium">
                                <div x-text="order.order_date"></div>
                                <div x-show="order.delivery_date" class="text-rose-600 font-bold mt-0.5" x-text="'گەیاندن: ' + order.delivery_date"></div>
                            </div>
                        </div>

                        {{-- کەلوپەلەکان بە وێنە --}}
                        <div class="space-y-2 mb-3">
                            <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">کەلوپەل و قیاسات:</div>
                            <template x-for="it in order.items" :key="it.id">
                                <div class="flex items-center gap-3 p-2 rounded-xl bg-slate-50 border border-slate-100">
                                    <div class="size-12 rounded-lg bg-white border border-slate-200 shrink-0 overflow-hidden flex items-center justify-center">
                                        <template x-if="it.image">
                                            <img :src="it.image" :alt="it.item_name" class="size-full object-cover cursor-pointer hover:scale-110 transition-transform" @click="previewImg = it.image">
                                        </template>
                                        <template x-if="!it.image">
                                            <span class="text-slate-300 text-xs">بێ وێنە</span>
                                        </template>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="font-bold text-xs text-slate-800 truncate" x-text="it.item_name"></div>
                                        <div class="text-[11px] text-slate-500 font-medium mt-0.5">
                                            بڕ: <span class="font-bold text-blue-600" x-text="it.qty + ' ' + (it.unit_name || 'دانە')"></span>
                                            <span x-show="it.width || it.height" class="text-slate-400 font-mono" x-text="'(' + it.width + '×' + it.height + ')'"></span>
                                        </div>
                                        <div x-show="it.note" class="text-[10px] text-amber-700 bg-amber-50/80 px-1.5 py-0.5 rounded-md mt-1 border border-amber-200/50" x-text="'تێبینی: ' + it.note"></div>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <div x-show="order.notes" class="text-xs text-slate-600 bg-amber-50/60 p-2.5 rounded-xl border border-amber-100 mb-3">
                            <span class="font-bold text-amber-800">تێبینی:</span> <span x-text="order.notes"></span>
                        </div>
                    </div>

                    {{-- دوگمەکانی کردار (AJAX Instant Update - Zero Reload) --}}
                    <div class="border-t border-slate-100 pt-3 flex items-center justify-between gap-2">
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <button x-show="order.status === 'confirmed'" type="button" @click="changeStatus(order.id, 'in_production')"
                                    class="btn btn-primary !py-1.5 !px-3 text-xs font-bold bg-blue-600 hover:bg-blue-700 flex items-center gap-1 shadow-xs cursor-pointer">
                                <span>⚙️</span><span>دەستپێکردنی دروستکردن</span>
                            </button>
                            <button x-show="order.status === 'in_production'" type="button" @click="changeStatus(order.id, 'ready')"
                                    class="btn btn-primary !py-1.5 !px-3 text-xs font-bold bg-emerald-600 hover:bg-emerald-700 flex items-center gap-1 shadow-xs cursor-pointer">
                                <span>✅</span><span>تەواوبوو (ئامادەیە)</span>
                            </button>
                            <button x-show="order.status === 'ready'" type="button" @click="changeStatus(order.id, 'delivered')"
                                    class="btn btn-primary !py-1.5 !px-3 text-xs font-bold bg-slate-800 hover:bg-slate-900 flex items-center gap-1 shadow-xs cursor-pointer">
                                <span>🚚</span><span>ڕادەستکرا بە کڕیار</span>
                            </button>
                            <span x-show="order.status === 'delivered'" class="text-xs text-emerald-700 font-bold bg-emerald-50 px-2.5 py-1 rounded-lg border border-emerald-200">
                                تەواوکراوە و ڕادەستکراوە ✔️
                            </span>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <div x-show="filteredOrders.length === 0" class="bg-white rounded-2xl p-12 text-center border border-slate-200 shadow-xs">
            <div class="text-4xl mb-2">📋</div>
            <div class="font-bold text-slate-700 text-base">هیچ وەسڵێک نەدۆزرایەوە</div>
            <div class="text-xs text-slate-400 mt-1">لەگەڵ ئەم فلتەر یان گەڕانەدا هیچ داواکارییەک ناگونجێت.</div>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- ٣. بەشی مەوادی خاو و کۆگا (MATERIALS TAB) --}}
    {{-- ============================================================ --}}
    <div x-show="activeTab === 'materials'" class="space-y-6" x-transition.opacity x-cloak>
        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-3.5">
                <div class="size-12 rounded-2xl bg-amber-50 border border-amber-100 text-amber-600 flex items-center justify-center text-2xl shadow-xs">
                    📦
                </div>
                <div>
                    <h2 class="text-xl font-black text-slate-800">مەوادی خاو و کەرەستەی دروستکردن</h2>
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

        {{-- خشتەی سەرەکی مەوادەکان بە گەڕانی زیندوو --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
            <div class="p-4 border-b border-slate-100 flex flex-wrap items-center justify-between gap-3">
                <div class="text-xs font-bold text-slate-600">
                    کۆی گشتی: <span class="text-slate-900 font-black" x-text="materialsList.length"></span> جۆر مەواد
                </div>
                <div class="flex items-center gap-2">
                    <input type="text" x-model="materialSearch" placeholder="گەڕانی خێرا بە ناوی مەواد یان کۆد..."
                           class="text-xs px-3 py-1.5 rounded-xl border border-slate-200 w-60 focus:outline-hidden focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                </div>
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
                        <template x-for="mat in filteredMaterials" :key="mat.id">
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="p-3.5 font-mono text-slate-500 font-medium" x-text="mat.code"></td>
                                <td class="p-3.5 font-bold text-slate-900" x-text="mat.name"></td>
                                <td class="p-3.5 text-slate-500" x-text="mat.category_name || '—'"></td>
                                <td class="p-3.5 text-center font-black text-sm num"
                                    :class="mat.is_low ? 'text-rose-600' : 'text-slate-800'">
                                    <span x-text="mat.stock_qty"></span> <span class="text-xs font-normal text-slate-500" x-text="mat.unit_name"></span>
                                </td>
                                <td class="p-3.5 text-center font-medium text-slate-500 num" x-text="mat.min_qty + ' ' + (mat.unit_name || '')"></td>
                                <td class="p-3.5 text-center">
                                    <span x-show="mat.is_low" class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-700 border border-rose-200 animate-pulse">
                                        کەمە ⚠️
                                    </span>
                                    <span x-show="!mat.is_low" class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700 border border-emerald-200">
                                        بەردەستە ✔️
                                    </span>
                                </td>
                                <td class="p-3.5 text-center">
                                    <div class="inline-flex items-center gap-1.5">
                                        <button type="button" @click="openStockInModalFor(mat.id)"
                                                class="px-2 py-1 rounded-lg text-[11px] font-bold bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200 cursor-pointer">
                                            + هاتن
                                        </button>
                                        <button type="button" @click="openStockOutModalFor(mat.id)"
                                                class="px-2 py-1 rounded-lg text-[11px] font-bold bg-amber-50 text-amber-700 hover:bg-amber-100 border border-amber-200 cursor-pointer">
                                            - بەکارهێنان
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- ٤. بەشی وەستا و حەمەڵەکان (EMPLOYEES TAB) --}}
    {{-- ============================================================ --}}
    <div x-show="activeTab === 'employees'" class="space-y-6" x-transition.opacity x-cloak>
        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-3.5">
                <div class="size-12 rounded-2xl bg-indigo-50 border border-indigo-100 text-indigo-600 flex items-center justify-center text-2xl shadow-xs">
                    👷
                </div>
                <div>
                    <h2 class="text-xl font-black text-slate-800">لیستی وەستاکان، حەمەڵەکان و کارمەندانی کارگە</h2>
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
    </div>

    {{-- ============================================================ --}}
    {{-- مۆداڵەکان (MODALS) --}}
    {{-- ============================================================ --}}

    {{-- مۆداڵی وێنەی گەورە --}}
    <div x-show="previewImg" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/80 backdrop-blur-xs p-4" @click="previewImg = null">
        <div class="relative max-w-2xl max-h-[85vh] bg-white rounded-2xl overflow-hidden shadow-2xl p-2" @click.stop>
            <button type="button" @click="previewImg = null" class="absolute top-3 right-3 bg-slate-900/70 text-white rounded-full size-8 flex items-center justify-center hover:bg-slate-900 transition-colors cursor-pointer">✕</button>
            <img :src="previewImg" class="max-h-[80vh] w-auto mx-auto object-contain rounded-xl">
        </div>
    </div>

    {{-- مۆداڵی زیادکردنی مەوادی نوێ --}}
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
                    <input type="text" name="name" required placeholder="ناوی مەواد..."
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
                        <label class="block text-xs font-bold text-slate-700 mb-1">کەمترین بڕ</label>
                        <input type="number" step="any" name="min_qty" value="5"
                               class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 font-mono">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">تێبینی</label>
                    <input type="text" name="note" placeholder="تێبینی..."
                           class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200">
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                    <button type="button" @click="showNewMaterialModal = false" class="btn btn-ghost !py-1.5 text-xs">پاشگەزبوونەوە</button>
                    <button type="submit" class="btn btn-primary !py-1.5 text-xs font-bold bg-blue-600 hover:bg-blue-700">تۆمارکردن</button>
                </div>
            </form>
        </div>
    </div>

    {{-- مۆداڵی زیادکردنی بڕ بۆ مەواد (Stock In) --}}
    <div x-show="showStockInModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4" x-transition.opacity>
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-5 border border-slate-200 text-right" @click.away="showStockInModal = false" x-transition.scale>
            <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                <div class="font-bold text-slate-800 text-base flex items-center gap-2">
                    <span class="size-8 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center text-sm font-bold">📥</span>
                    <span>هاتنی مەواد بۆ کارگە</span>
                </div>
                <button type="button" @click="showStockInModal = false" class="text-slate-400 hover:text-slate-600 size-7 rounded-lg flex items-center justify-center cursor-pointer">✕</button>
            </div>

            <form method="POST" action="{{ route('workshop.stock-in') }}" class="space-y-3.5">
                @csrf
                <input type="hidden" name="warehouse_id" value="{{ $workshopWarehouse?->id }}">

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">هەڵبژاردنی مەواد <span class="text-rose-500">*</span></label>
                    <select name="item_id" x-model="selectedItemId" required class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200">
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
                    <label class="block text-xs font-bold text-slate-700 mb-1">تێبینی</label>
                    <input type="text" name="note" placeholder="تێبینی..."
                           class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200">
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                    <button type="button" @click="showStockInModal = false" class="btn btn-ghost !py-1.5 text-xs">پاشگەزبوونەوە</button>
                    <button type="submit" class="btn btn-primary !py-1.5 text-xs font-bold bg-emerald-600 hover:bg-emerald-700">تۆمارکردنی هاتن</button>
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
                    <span>بەکارهێنان و کەمکردنەوەی مەواد</span>
                </div>
                <button type="button" @click="showStockOutModal = false" class="text-slate-400 hover:text-slate-600 size-7 rounded-lg flex items-center justify-center cursor-pointer">✕</button>
            </div>

            <form method="POST" action="{{ route('workshop.stock-out') }}" class="space-y-3.5">
                @csrf
                <input type="hidden" name="warehouse_id" value="{{ $workshopWarehouse?->id }}">

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">هەڵبژاردنی مەواد <span class="text-rose-500">*</span></label>
                    <select name="item_id" x-model="selectedItemId" required class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200">
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
                    <label class="block text-xs font-bold text-slate-700 mb-1">تێبینی</label>
                    <input type="text" name="note" placeholder="تێبینی..."
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

{{-- سکریپتی کارایی داشبۆرد بەبێ ڕیفرێش (Zero Reload Dashboard Engine) --}}
<script>
function workshopApp() {
    return {
        activeTab: '{{ request('tab', 'dashboard') }}',
        orderFilter: 'all',
        orderSearch: '',
        materialSearch: '',
        showNewMaterialModal: false,
        showStockInModal: false,
        showStockOutModal: false,
        selectedItemId: '',
        previewImg: null,

        ordersList: @json($orders->map(fn($o) => [
            'id' => $o->id,
            'invoice_no' => $o->invoice_no,
            'status' => $o->status,
            'status_label' => $o->status_label,
            'customer_name' => $o->customer?->name ?? 'نەناسراو',
            'customer_phone' => $o->customer?->phone ?? '',
            'order_date' => $o->order_date?->format('Y/m/d') ?? '',
            'delivery_date' => $o->delivery_date?->format('Y/m/d') ?? '',
            'notes' => $o->notes ?? '',
            'items' => $o->items->map(fn($it) => [
                'id' => $it->id,
                'item_name' => $it->item_name,
                'qty' => (float) $it->qty,
                'unit_name' => $it->unit_name,
                'width' => $it->width,
                'height' => $it->height,
                'note' => $it->note,
                'image' => \App\Models\Item::find($it->item_id)?->imageUrl(),
            ]),
        ])),

        materialsList: @json($rawMaterials->map(fn($m) => [
            'id' => $m->id,
            'code' => $m->code,
            'name' => $m->name,
            'category_name' => $m->category?->name,
            'stock_qty' => (float) $m->stock_qty,
            'min_qty' => (float) $m->min_qty,
            'unit_name' => $m->unit?->name ?? '',
            'is_low' => $m->is_low,
        ])),

        init() {
            // گۆڕینی تاب لەڕێگەی URL ئەگەر دەستکاری کرا
            const urlParams = new URLSearchParams(window.location.search);
            const tabParam = urlParams.get('tab');
            if (tabParam && ['dashboard', 'orders', 'materials', 'employees'].includes(tabParam)) {
                this.activeTab = tabParam;
            }
        },

        setTab(tab) {
            this.activeTab = tab;
            const newUrl = window.location.pathname + (tab === 'dashboard' ? '' : '?tab=' + tab);
            window.history.pushState({ tab: tab }, '', newUrl);
        },

        get pendingCount() {
            return this.ordersList.filter(o => o.status === 'confirmed').length;
        },

        get inProductionCount() {
            return this.ordersList.filter(o => o.status === 'in_production').length;
        },

        get readyCount() {
            return this.ordersList.filter(o => o.status === 'ready').length;
        },

        get deliveredCount() {
            return this.ordersList.filter(o => o.status === 'delivered').length;
        },

        get activeOrdersList() {
            return this.ordersList.filter(o => ['in_production', 'confirmed'].includes(o.status)).slice(0, 6);
        },

        get filteredOrders() {
            return this.ordersList.filter(o => {
                const matchStatus = this.orderFilter === 'all' || o.status === this.orderFilter;
                const q = this.orderSearch.trim().toLowerCase();
                const matchSearch = !q || o.invoice_no.toString().includes(q) || (o.customer_name && o.customer_name.toLowerCase().includes(q));
                return matchStatus && matchSearch;
            });
        },

        get filteredMaterials() {
            const q = this.materialSearch.trim().toLowerCase();
            if (!q) return this.materialsList;
            return this.materialsList.filter(m => 
                (m.name && m.name.toLowerCase().includes(q)) || 
                (m.code && m.code.toLowerCase().includes(q))
            );
        },

        openStockInModalFor(itemId) {
            this.selectedItemId = itemId;
            this.showStockInModal = true;
        },

        openStockOutModalFor(itemId) {
            this.selectedItemId = itemId;
            this.showStockOutModal = true;
        },

        async changeStatus(orderId, newStatus) {
            const labels = {
                'confirmed': 'چاوەڕوانی',
                'in_production': 'لە دروستکردندا',
                'ready': 'ئامادەیە',
                'delivered': 'ڕادەستکراو'
            };

            // نوێکردنەوەی خێرا لە میمۆریدا بەبێ ڕیفرێش
            const order = this.ordersList.find(o => o.id === orderId);
            if (order) {
                const oldStatus = order.status;
                order.status = newStatus;
                order.status_label = labels[newStatus] || newStatus;

                try {
                    const response = await fetch(`/workshop/orders/${orderId}/status`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ status: newStatus })
                    });

                    if (!response.ok) {
                        order.status = oldStatus;
                        order.status_label = labels[oldStatus];
                        alert('هەڵەیەک ڕوویدا لە گۆڕینی دۆخەکە.');
                    }
                } catch (e) {
                    order.status = oldStatus;
                    order.status_label = labels[oldStatus];
                    alert('هەڵەی پەیوەندی.');
                }
            }
        }
    };
}
</script>
@endsection
