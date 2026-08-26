@extends('layouts.menu')
@section('title', 'داواکارییەکانی کارگە')

@section('content')
<div x-data="workshopOrdersApp()" class="space-y-6">

    {{-- ١. هێڵی سەرەوە: سەردێڕ و کورتە --}}
    <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-3.5">
            <div class="size-12 rounded-2xl bg-blue-50 border border-blue-100 text-blue-600 flex items-center justify-center text-2xl shadow-xs">
                📋
            </div>
            <div>
                <h1 class="text-xl font-black text-slate-800">داواکارییەکان و وەسڵەکانی کارگە</h1>
                <p class="text-xs text-slate-500 mt-0.5 font-medium">
                    فلتەرکردن و گەڕانی خێرا، چاودێری قیاسات و کەرەستە داواکراوەکان و گۆڕینی قۆناغی کار
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('workshop.index') }}" class="btn btn-ghost !py-1.5 !px-3 text-xs font-bold border border-slate-200 hover:bg-slate-50 text-slate-700">
                <span>📊 پوختەی کارگە</span>
            </a>
        </div>
    </div>

    {{-- ٢. فلتەری دۆخەکان و گەڕانی زیندوو --}}
    <div class="bg-white rounded-2xl p-3.5 border border-slate-200 shadow-xs flex flex-wrap items-center justify-between gap-3">
        <div class="flex flex-wrap items-center gap-2 text-xs font-bold">
            <button type="button" @click="setFilter('all')"
                    :class="orderFilter === 'all' ? 'bg-slate-900 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                    class="px-3.5 py-1.5 rounded-xl transition-all cursor-pointer">
                هەموو کارەکان (<span x-text="ordersList.length"></span>)
            </button>
            <button type="button" @click="setFilter('in_production')"
                    :class="orderFilter === 'in_production' ? 'bg-blue-600 text-white shadow-xs' : 'bg-blue-50 text-blue-700 hover:bg-blue-100'"
                    class="px-3.5 py-1.5 rounded-xl transition-all cursor-pointer">
                ⚙️ لە دروستکردندا (<span x-text="inProductionCount"></span>)
            </button>
            <button type="button" @click="setFilter('confirmed')"
                    :class="orderFilter === 'confirmed' ? 'bg-amber-500 text-white shadow-xs' : 'bg-amber-50 text-amber-700 hover:bg-amber-100'"
                    class="px-3.5 py-1.5 rounded-xl transition-all cursor-pointer">
                ⏳ چاوەڕوانە (<span x-text="pendingCount"></span>)
            </button>
            <button type="button" @click="setFilter('ready')"
                    :class="orderFilter === 'ready' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100'"
                    class="px-3.5 py-1.5 rounded-xl transition-all cursor-pointer">
                ✅ ئامادەیە (<span x-text="readyCount"></span>)
            </button>
            <button type="button" @click="setFilter('delivered')"
                    :class="orderFilter === 'delivered' ? 'bg-slate-700 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                    class="px-3.5 py-1.5 rounded-xl transition-all cursor-pointer">
                🚚 ڕادەستکراوە (<span x-text="deliveredCount"></span>)
            </button>
        </div>

        <div class="flex items-center gap-2">
            <input type="text" x-model="orderSearch" placeholder="گەڕانی خێرا بە ناوی کڕیار یان ژمارە..."
                   class="text-xs px-3 py-1.5 rounded-xl border border-slate-200 w-64 focus:outline-hidden focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
        </div>
    </div>

    {{-- ٣. کارتی وەسڵەکان بە داتای زیندوو --}}
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

                {{-- دوگمەکانی کردار --}}
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
        previewImg: null,
        ordersList: @json($ordersData),

        setFilter(status) {
            this.orderFilter = status;
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
