@extends('layouts.menu')
@section('title', 'داواکارییەکانی کارگە')

@section('content')
<div x-data="workshopOrdersApp()" class="space-y-4 sm:space-y-6">

    {{-- ١. هێڵی سەرەوە: ناونیشان و ئامارەکان --}}
    <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-2xl bg-blue-600 text-white flex items-center justify-center text-2xl shadow-md shrink-0">
                ⚙️
            </div>
            <div>
                <div class="flex items-center gap-2 flex-wrap">
                    <h1 class="text-lg sm:text-xl font-black text-slate-900">داواکاری و وەسڵەکانی کارگە</h1>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200">
                        کارگەی ئاسنگەری
                    </span>
                </div>
                <p class="text-xs text-slate-500 mt-0.5 font-medium">
                    بەڕێوەبردنی قۆناغەکانی دروستکردن و قیاسات بۆ وەستاکان
                </p>
            </div>
        </div>

        {{-- ئامارە خێراکان --}}
        <div class="flex items-center gap-2 flex-wrap">
            <div class="flex items-center gap-2 bg-slate-50 border border-slate-200 px-3 py-1.5 rounded-xl">
                <span class="text-sm">📋</span>
                <span class="text-xs font-bold text-slate-600">کۆی وەسڵ: <b class="text-slate-900 font-mono" x-text="ordersList.length"></b></span>
            </div>
            <div class="flex items-center gap-2 bg-blue-50 border border-blue-200 px-3 py-1.5 rounded-xl">
                <span class="text-sm">⚙️</span>
                <span class="text-xs font-bold text-blue-800">لە دروستکردندا: <b class="text-blue-900 font-mono" x-text="inProductionCount"></b></span>
            </div>
            <div class="flex items-center gap-2 bg-emerald-50 border border-emerald-200 px-3 py-1.5 rounded-xl">
                <span class="text-sm">✅</span>
                <span class="text-xs font-bold text-emerald-800">ئامادەیە: <b class="text-emerald-900 font-mono" x-text="readyCount"></b></span>
            </div>
        </div>
    </div>

    {{-- ٢. فلتەری دۆخەکان و گەڕانی خێرا --}}
    <div class="bg-white rounded-2xl p-3 sm:p-4 border border-slate-200 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-3">
        {{-- دوگمەکانی فلتەر --}}
        <div class="flex flex-wrap items-center gap-1.5 sm:gap-2 text-xs font-bold">
            <button type="button" @click="setFilter('all')"
                    :class="orderFilter === 'all' ? 'bg-slate-900 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                    class="px-3 py-1.5 rounded-xl transition-all cursor-pointer text-xs">
                هەموو (<span x-text="ordersList.length"></span>)
            </button>
            <button type="button" @click="setFilter('in_production')"
                    :class="orderFilter === 'in_production' ? 'bg-blue-600 text-white shadow-xs' : 'bg-blue-50 text-blue-700 hover:bg-blue-100 border border-blue-200'"
                    class="px-3 py-1.5 rounded-xl transition-all cursor-pointer text-xs">
                ⚙️ لە دروستکردندا (<span x-text="inProductionCount"></span>)
            </button>
            <button type="button" @click="setFilter('confirmed')"
                    :class="orderFilter === 'confirmed' ? 'bg-amber-500 text-white shadow-xs' : 'bg-amber-50 text-amber-700 hover:bg-amber-100 border border-amber-200'"
                    class="px-3 py-1.5 rounded-xl transition-all cursor-pointer text-xs">
                ⏳ چاوەڕوانە (<span x-text="pendingCount"></span>)
            </button>
            <button type="button" @click="setFilter('ready')"
                    :class="orderFilter === 'ready' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200'"
                    class="px-3 py-1.5 rounded-xl transition-all cursor-pointer text-xs">
                ✅ ئامادەیە (<span x-text="readyCount"></span>)
            </button>
            <button type="button" @click="setFilter('delivered')"
                    :class="orderFilter === 'delivered' ? 'bg-slate-700 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 border border-slate-200'"
                    class="px-3 py-1.5 rounded-xl transition-all cursor-pointer text-xs">
                🚚 ڕادەستکراوە (<span x-text="deliveredCount"></span>)
            </button>
        </div>

        {{-- گەڕان و گۆڕینی شێواز --}}
        <div class="flex items-center gap-2">
            <div class="w-full md:w-64">
                <input type="text" x-model="orderSearch" placeholder="🔍 گەڕان بە ناوی کڕیار یان ژمارە..."
                       class="text-xs px-3.5 py-2 rounded-xl border border-slate-200 w-full focus:outline-hidden focus:border-blue-500 bg-slate-50 md:bg-white text-right">
            </div>

            <div class="flex items-center p-1 bg-slate-100 rounded-xl border border-slate-200 shrink-0">
                <button type="button" @click="viewMode = 'cards'"
                        :class="viewMode === 'cards' ? 'bg-white text-slate-900 font-bold shadow-xs' : 'text-slate-500'"
                        class="px-2.5 py-1 rounded-lg text-xs transition-all cursor-pointer flex items-center gap-1">
                    <span>🎴</span>
                    <span class="hidden sm:inline">کارتەکان</span>
                </button>
                <button type="button" @click="viewMode = 'table'"
                        :class="viewMode === 'table' ? 'bg-white text-slate-900 font-bold shadow-xs' : 'text-slate-500'"
                        class="px-2.5 py-1 rounded-lg text-xs transition-all cursor-pointer flex items-center gap-1">
                    <span>📊</span>
                    <span class="hidden sm:inline">جەدوەل</span>
                </button>
            </div>
        </div>
    </div>

    {{-- ٣.١ شێوازی کارتەکان (Card Grid) --}}
    <div x-show="viewMode === 'cards'" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3.5 sm:gap-4">
        <template x-for="order in paginatedOrders" :key="order.id">
            <div class="rounded-2xl border p-4 sm:p-4.5 shadow-xs flex flex-col justify-between transition-all hover:shadow-md bg-white"
                 :class="{
                     'border-blue-400 bg-blue-50/20 ring-1 ring-blue-200': order.status === 'in_production',
                     'border-amber-200': order.status === 'confirmed',
                     'border-emerald-300 bg-emerald-50/20': order.status === 'ready',
                     'border-slate-200 bg-slate-50/50 opacity-80': order.status === 'delivered',
                 }">
                <div>
                    {{-- هێڵی سەرەوەی کارت --}}
                    <div class="flex items-start justify-between gap-2 border-b border-slate-100 pb-3 mb-3">
                        <div class="min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <h3 class="font-black text-slate-900 text-sm sm:text-base font-mono" x-text="'وەسڵی #' + order.invoice_no"></h3>
                                <span class="px-2 py-0.5 rounded-full text-[10px] sm:text-[11px] font-bold"
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
                                <span x-show="order.customer_phone" class="text-slate-400 font-normal font-mono" x-text="'(' + order.customer_phone + ')'"></span>
                            </div>
                        </div>
                        <div class="text-left text-[11px] text-slate-400 font-medium shrink-0">
                            <div class="font-mono" x-text="order.order_date"></div>
                            <div x-show="order.delivery_date" class="text-rose-600 font-bold font-mono mt-0.5" x-text="'گەیاندن: ' + order.delivery_date"></div>
                        </div>
                    </div>

                    {{-- کەلوپەلەکان بە وێنەی قەبارە دیاریکراو --}}
                    <div class="space-y-2 mb-3">
                        <div class="text-[11px] font-bold text-slate-400 uppercase">کەلوپەل و قیاسات:</div>
                        <template x-for="it in order.items" :key="it.id">
                            <div class="flex items-center gap-3 p-2 rounded-xl bg-slate-50 border border-slate-100">
                                {{-- وێنەی کەلوپەل (بە قەبارەی تەواو دیاریکراو 56px بە 56px) --}}
                                <div style="width: 56px; height: 56px; min-width: 56px; min-height: 56px;"
                                     class="rounded-xl bg-white border border-slate-200 shrink-0 overflow-hidden flex items-center justify-center">
                                    <template x-if="it.image">
                                        <img :src="it.image" :alt="it.item_name"
                                             style="width: 56px; height: 56px; object-fit: cover;"
                                             class="cursor-pointer hover:scale-105 transition-transform"
                                             @click="previewImg = it.image">
                                    </template>
                                    <template x-if="!it.image">
                                        <div class="flex flex-col items-center justify-center text-slate-300">
                                            <span class="text-sm">🖼️</span>
                                            <span class="text-[9px]">بێ وێنە</span>
                                        </div>
                                    </template>
                                </div>

                                <div class="min-w-0 flex-1">
                                    <div class="font-bold text-xs text-slate-800 truncate" x-text="it.item_name"></div>
                                    <div class="text-[11px] text-slate-500 font-medium mt-0.5 flex items-center gap-1.5 flex-wrap">
                                        <span>بڕ: <span class="font-bold text-blue-600 font-mono" x-text="it.qty + ' ' + (it.unit_name || 'دانە')"></span></span>
                                        <span x-show="it.measurement && it.measurement !== '—'" class="text-indigo-600 font-bold font-mono text-[10px] bg-indigo-50 px-1.5 py-0.5 rounded border border-indigo-100" x-text="it.measurement"></span>
                                        <span x-show="!it.measurement && (it.width || it.height)" class="text-slate-400 font-mono text-[10px]" x-text="'(' + (it.width || '') + '×' + (it.height || '') + ')'"></span>
                                    </div>
                                    <div x-show="it.note" class="text-[10px] text-amber-700 bg-amber-50 px-1.5 py-0.5 rounded-md mt-1 border border-amber-200" x-text="'تێبینی: ' + it.note"></div>
                                </div>
                            </div>
                        </template>
                    </div>

                    <div x-show="order.notes" class="text-xs text-slate-600 bg-amber-50/60 p-2.5 rounded-xl border border-amber-100 mb-3">
                        <span class="font-bold text-amber-800">تێبینی:</span> <span x-text="order.notes"></span>
                    </div>
                </div>

                {{-- دوگمەکانی کردار لە خوارەوە --}}
                <div class="border-t border-slate-100 pt-3 flex items-center justify-between gap-2">
                    <div class="flex items-center gap-1.5 flex-wrap flex-1">
                        <button x-show="order.status === 'confirmed'" type="button" @click="changeStatus(order.id, 'in_production')"
                                class="w-full sm:w-auto px-4 py-2 rounded-xl text-xs font-bold bg-blue-600 hover:bg-blue-700 text-white flex items-center justify-center gap-1 shadow-xs cursor-pointer">
                            <span>⚙️</span><span>دەستپێکردنی دروستکردن</span>
                        </button>
                        <button x-show="order.status === 'in_production'" type="button" @click="changeStatus(order.id, 'ready')"
                                class="w-full sm:w-auto px-4 py-2 rounded-xl text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white flex items-center justify-center gap-1 shadow-xs cursor-pointer">
                            <span>✅</span><span>تەواوبوو (ئامادەیە)</span>
                        </button>
                        <button x-show="order.status === 'ready'" type="button" @click="changeStatus(order.id, 'delivered')"
                                class="w-full sm:w-auto px-4 py-2 rounded-xl text-xs font-bold bg-slate-800 hover:bg-slate-900 text-white flex items-center justify-center gap-1 shadow-xs cursor-pointer">
                            <span>🚚</span><span>ڕادەستکرا بە کڕیار</span>
                        </button>
                        <span x-show="order.status === 'delivered'" class="text-xs text-emerald-700 font-bold bg-emerald-50 px-2.5 py-1 rounded-lg border border-emerald-200 w-full text-center sm:w-auto">
                            تەواوکراوە و ڕادەستکراوە ✔️
                        </span>
                    </div>

                    <a :href="order.print_url" target="_blank"
                       class="p-2 rounded-xl text-xs font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 cursor-pointer"
                       title="چاپی وەسڵ">
                        🖨️
                    </a>
                </div>
            </div>
        </template>
    </div>

    {{-- ٣.٢ شێوازی خشتە (Table View) --}}
    <div x-show="viewMode === 'table'" class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto scrollbar-none">
            <table class="w-full text-right text-xs">
                <thead class="bg-slate-50 text-slate-600 border-b border-slate-200 font-bold">
                    <tr>
                        <th class="p-3.5 w-14 text-center">#وەسڵ</th>
                        <th class="p-3.5">کڕیار</th>
                        <th class="p-3.5">کەلوپەل و قیاسات</th>
                        <th class="p-3.5 text-center">بەرواری داواکاری</th>
                        <th class="p-3.5 text-center">بەرواری گەیاندن</th>
                        <th class="p-3.5 text-center">دۆخ</th>
                        <th class="p-3.5 text-center">کردار</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <template x-for="order in paginatedOrders" :key="order.id">
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="p-3.5 text-center font-mono font-black text-blue-600 text-sm" x-text="'#' + order.invoice_no"></td>
                            <td class="p-3.5">
                                <div class="font-bold text-slate-900" x-text="order.customer_name"></div>
                                <div x-show="order.customer_phone" class="text-[11px] text-slate-400 font-mono" dir="ltr" x-text="order.customer_phone"></div>
                            </td>
                            <td class="p-3.5">
                                <div class="space-y-1">
                                    <template x-for="it in order.items" :key="it.id">
                                        <div class="flex items-center gap-1.5 text-xs">
                                            <span class="font-bold text-slate-800" x-text="it.item_name"></span>
                                            <span class="text-blue-600 font-mono font-bold text-[11px]" x-text="'(' + it.qty + ' ' + (it.unit_name || 'دانە') + ')'"></span>
                                            <span x-show="it.measurement" class="bg-slate-100 text-slate-700 px-1 py-0.5 rounded text-[10px] font-mono" x-text="it.measurement"></span>
                                        </div>
                                    </template>
                                </div>
                            </td>
                            <td class="p-3.5 text-center font-mono text-slate-500" x-text="order.order_date"></td>
                            <td class="p-3.5 text-center font-mono font-bold text-rose-600" x-text="order.delivery_date || '—'"></td>
                            <td class="p-3.5 text-center">
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold"
                                      :class="{
                                          'bg-blue-100 text-blue-800': order.status === 'in_production',
                                          'bg-amber-100 text-amber-800': order.status === 'confirmed',
                                          'bg-emerald-100 text-emerald-800': order.status === 'ready',
                                          'bg-slate-100 text-slate-700': order.status === 'delivered',
                                      }"
                                      x-text="order.status_label"></span>
                            </td>
                            <td class="p-3.5 text-center">
                                <div class="inline-flex items-center gap-1.5">
                                    <button x-show="order.status === 'confirmed'" type="button" @click="changeStatus(order.id, 'in_production')"
                                            class="px-2.5 py-1 rounded-lg text-xs font-bold bg-blue-600 hover:bg-blue-700 text-white cursor-pointer">
                                        ⚙️ دەستپێکردن
                                    </button>
                                    <button x-show="order.status === 'in_production'" type="button" @click="changeStatus(order.id, 'ready')"
                                            class="px-2.5 py-1 rounded-lg text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white cursor-pointer">
                                        ✅ تەواوبوو
                                    </button>
                                    <button x-show="order.status === 'ready'" type="button" @click="changeStatus(order.id, 'delivered')"
                                            class="px-2.5 py-1 rounded-lg text-xs font-bold bg-slate-800 hover:bg-slate-900 text-white cursor-pointer">
                                        🚚 ڕادەستکردن
                                    </button>
                                    <span x-show="order.status === 'delivered'" class="text-xs text-emerald-600 font-bold">تەواو ✔️</span>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    {{-- ئەگەر هیچ نەدۆزرایەوە --}}
    <div x-show="filteredOrders.length === 0" class="bg-white rounded-2xl p-8 sm:p-12 text-center border border-slate-200 shadow-xs">
        <div class="text-4xl mb-2">📋</div>
        <div class="font-bold text-slate-700 text-base">هیچ وەسڵێک نەدۆزرایەوە</div>
        <div class="text-xs text-slate-400 mt-1">لەگەڵ ئەم فلتەر یان گەڕانەدا هیچ داواکارییەک ناگونجێت.</div>
    </div>

    {{-- ٤. پەیجینەیشن --}}
    <div x-show="filteredOrders.length > 0" class="bg-white rounded-2xl p-3.5 sm:p-4 border border-slate-200 shadow-xs flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
        <div class="flex items-center gap-2 text-slate-500 font-medium text-[11px] sm:text-xs">
            <span>نیشاندانی</span>
            <span class="font-bold text-slate-800 font-mono" x-text="((currentPage - 1) * perPage) + 1"></span>
            <span>تا</span>
            <span class="font-bold text-slate-800 font-mono" x-text="Math.min(currentPage * perPage, filteredOrders.length)"></span>
            <span>لە کۆی</span>
            <span class="font-bold text-blue-600 font-mono" x-text="filteredOrders.length"></span>
            <span>داواکاری</span>
        </div>

        <div class="flex items-center gap-2 flex-wrap justify-center">
            <button type="button" @click="prevPage" :disabled="currentPage === 1"
                    :class="currentPage === 1 ? 'opacity-40 cursor-not-allowed bg-slate-100 text-slate-400' : 'bg-slate-100 text-slate-700 hover:bg-slate-200 cursor-pointer'"
                    class="px-2.5 sm:px-3 py-1.5 rounded-xl font-bold transition-all flex items-center gap-1">
                <span>←</span>
                <span>پێشتر</span>
            </button>

            <div class="flex items-center gap-1 font-mono font-bold px-1">
                <span class="text-blue-600 font-bold" x-text="currentPage"></span>
                <span class="text-slate-400">/</span>
                <span class="text-slate-600" x-text="totalPages"></span>
            </div>

            <button type="button" @click="nextPage" :disabled="currentPage === totalPages"
                    :class="currentPage === totalPages ? 'opacity-40 cursor-not-allowed bg-slate-100 text-slate-400' : 'bg-slate-100 text-slate-700 hover:bg-slate-200 cursor-pointer'"
                    class="px-2.5 sm:px-3 py-1.5 rounded-xl font-bold transition-all flex items-center gap-1">
                <span>دواتر</span>
                <span>→</span>
            </button>

            <div class="flex items-center gap-1 mr-2 border-r border-slate-200 pr-2">
                <span class="text-[11px] text-slate-400">ژمارە:</span>
                <select x-model.number="perPage" @change="currentPage = 1"
                        class="text-xs px-2 py-1 rounded-lg border border-slate-200 bg-slate-50 font-bold font-mono focus:outline-hidden focus:border-blue-500">
                    <option :value="6">6</option>
                    <option :value="9">9</option>
                    <option :value="12">12</option>
                    <option :value="24">24</option>
                </select>
            </div>
        </div>
    </div>

    {{-- مۆداڵی وێنەی گەورە --}}
    <div x-show="previewImg" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/80 backdrop-blur-xs p-4" @click="previewImg = null">
        <div class="relative max-w-2xl max-h-[85vh] bg-white rounded-2xl overflow-hidden shadow-2xl p-2" @click.stop>
            <button type="button" @click="previewImg = null" class="absolute top-4 left-4 size-8 rounded-full bg-slate-900/70 text-white flex items-center justify-center hover:bg-slate-900 cursor-pointer">
                ✕
            </button>
            <img :src="previewImg" alt="وێنەی کەلوپەل" class="max-h-[80vh] w-auto mx-auto rounded-xl object-contain">
        </div>
    </div>

</div>

<script>
function workshopOrdersApp() {
    return {
        orderFilter: '{{ request('status', 'all') }}',
        orderSearch: '',
        viewMode: 'cards',
        previewImg: null,
        ordersList: @json($ordersData),
        currentPage: 1,
        perPage: 9,

        init() {
            this.$watch('orderFilter', () => { this.currentPage = 1; });
            this.$watch('orderSearch', () => { this.currentPage = 1; });
        },

        setFilter(status) {
            this.orderFilter = status;
            this.currentPage = 1;
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

        get filteredOrders() {
            return this.ordersList.filter(o => {
                const matchStatus = this.orderFilter === 'all' || o.status === this.orderFilter;
                const q = this.orderSearch.trim().toLowerCase();
                const matchSearch = !q || o.invoice_no.toString().includes(q) || (o.customer_name && o.customer_name.toLowerCase().includes(q));
                return matchStatus && matchSearch;
            });
        },

        get totalPages() {
            return Math.ceil(this.filteredOrders.length / this.perPage) || 1;
        },

        get paginatedOrders() {
            const start = (this.currentPage - 1) * this.perPage;
            return this.filteredOrders.slice(start, start + this.perPage);
        },

        goToPage(p) {
            if (p >= 1 && p <= this.totalPages) {
                this.currentPage = p;
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        },

        nextPage() {
            this.goToPage(this.currentPage + 1);
        },

        prevPage() {
            this.goToPage(this.currentPage - 1);
        },

        async changeStatus(orderId, newStatus) {
            const labels = {
                'confirmed': 'چاوەڕوانی',
                'in_production': 'لە دروستکردندا',
                'ready': 'ئامادەیە',
                'delivered': 'ڕادەستکراو'
            };

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
