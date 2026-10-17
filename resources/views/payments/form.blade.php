@extends('layouts.app')
@section('title', 'وەرگرتنی حەقدی لە موشتەری')

@section('actions')
    <a href="{{ route('payments.index') }}" class="btn btn-ghost !py-1.5 !px-3 text-xs gap-1 border border-slate-200 hover:bg-slate-100 font-bold text-slate-700 bg-white shadow-2xs">
        <span>&larr;</span>
        <span>گەڕانەوە بۆ لیستی حەقدی</span>
    </a>
@endsection

@section('content')

<div class="max-w-4xl mx-auto flex flex-col gap-4">

    @if ($errors->any())
        <div class="bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl p-4 text-xs font-bold shadow-xs">
            <div class="flex items-center gap-2 text-rose-700 font-extrabold text-sm mb-1">
                <span>⚠️</span>
                <span>تکایە ئەم هەڵانە چاک بکە:</span>
            </div>
            <ul class="list-disc list-inside space-y-1 text-rose-600 pr-2">
                @foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- ئەگەر لەسەر وەسڵێکی دیاریکراوەوە هاتبن --}}
    @if ($order)
        <div class="bg-blue-50 border border-blue-200 rounded-2xl p-4 flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-3">
                <span class="text-2xl">📄</span>
                <div>
                    <div class="text-xs font-bold text-blue-800">حەقدی تایبەت بە وەسڵی فرۆشتن</div>
                    <div class="text-sm font-mono font-black text-blue-900">#{{ $order->invoice_no }} — {{ $order->customer?->name }}</div>
                </div>
            </div>
            <div class="text-left">
                <div class="text-xs font-bold text-slate-500">قەرزی ماوەی وەسڵ:</div>
                <div class="num text-base font-black text-rose-600">{{ fmt_money($order->remaining(), $order->currency) }}</div>
            </div>
        </div>
    @endif

    {{-- کارتی سەرەکی فۆرم --}}
    <div class="card"
         x-data="customerPaymentForm(
            @js($customers),
            @js($orders),
            '{{ old('customer_id', $selectedCustomer) }}',
            '{{ old('order_id', $selectedOrder) }}',
            '{{ old('amount', $order ? (float)$order->remaining() : '') }}',
            '{{ old('discount', '') }}',
            '{{ number_format($rate * 100) }}'
         )">

        <form method="POST" action="{{ route('payments.store') }}" class="p-5 sm:p-7 space-y-6">
            @csrf

            {{-- ١. دیاریکردنی موشتەری و پیشاندانی قەرزی ئێستا --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-start">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5" for="customer_id">
                        ناوی موشتەری (کڕیار) <span class="text-rose-500">*</span>
                    </label>
                    <select id="customer_id" name="customer_id" class="field w-full !py-2.5 text-sm font-bold bg-white"
                            x-model="selectedCustomer"
                            @change="handleCustomerChange()"
                            required>
                        <option value="">— موشتەری دیاری بکە —</option>
                        <template x-for="cust in customers" :key="cust.id">
                            <option :value="cust.id"
                                    x-text="cust.name"
                                    :selected="selectedCustomer == cust.id">
                            </option>
                        </template>
                    </select>
                </div>

                {{-- کارتی باڵانس و قەرزی موشتەری --}}
                <div class="bg-slate-50 border border-slate-200 rounded-xl p-3.5 flex items-center justify-between"
                     :class="currentCustomerDebt > 0 ? 'bg-rose-50/60 border-rose-200' : 'bg-emerald-50/60 border-emerald-200'">
                    <div>
                        <div class="text-xs font-bold text-slate-500">قەرزی ئێستای ئەم موشتەرییە:</div>
                        <div class="text-xs text-slate-400 mt-0.5" x-text="selectedCustomer ? (currentCustomer?.balances_formatted || 'تەواوی باڵانسی ماوە') : 'موشتەری هەڵبژێرە'"></div>
                    </div>
                    <div class="num text-xl font-black"
                         :class="currentCustomerDebt > 0 ? 'text-rose-600' : 'text-emerald-700'">
                        <span x-text="formatNumber(currentCustomerDebt)">0</span>
                        <span class="text-xs" x-text="currency === 'USD' ? ' $' : ' د.ع'"></span>
                    </div>
                </div>
            </div>

            {{-- ٢. بەستنەوە بە وەسڵی فرۆشتن (ئیختیاری) --}}
            <div class="bg-slate-50/80 border border-slate-200/80 rounded-xl p-4">
                <label class="block text-xs font-bold text-slate-700 mb-1" for="order_id">
                    بەستنەوە بە وەسڵێکی فرۆشتنی دیاریکراو
                </label>
                <select id="order_id" name="order_id" class="field w-full text-xs font-semibold bg-white !py-2"
                        x-model="selectedOrder"
                        @change="handleOrderChange()">
                    <option value="">— تەواوی حسابی موشتەری (گشتی) —</option>
                    <template x-for="ord in filteredOrders" :key="ord.id">
                        <option :value="ord.id"
                                x-text="'وەسڵی #' + ord.invoice_no + ' — ماوە: ' + (ord.remaining > 0 ? Number(ord.remaining).toLocaleString('en-US') + ' ' + (ord.currency === 'USD' ? '$' : 'د.ع') : 'تەواوی پارەکەی دراوە') + ' (کۆی گشتی: ' + Number(ord.total).toLocaleString('en-US') + ' ' + (ord.currency === 'USD' ? '$' : 'د.ع') + ')'"
                                :selected="selectedOrder == ord.id">
                        </option>
                    </template>
                </select>
            </div>

            {{-- ٣. بڕی پارەی حەقدی، داشکان و دراو --}}
            <div class="space-y-3">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
                    {{-- ١. بڕی پارەی وەرگیراو --}}
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-xs font-bold text-slate-700" for="amount">
                                بڕی پارەی وەرگیراو <span class="text-rose-500">*</span>
                            </label>
                            <span class="text-[10px] font-bold text-emerald-600 bg-emerald-50 px-1.5 py-0.5 rounded">کاش</span>
                        </div>
                        <input id="amount" name="amount" type="number" step="any" min="0" required
                               class="field num w-full !py-2 text-2xl font-black text-center text-emerald-700 bg-white"
                               x-model="amount"
                               placeholder="0">

                        {{-- دوگمە خێراکان بۆ دیاریکردنی بڕ --}}
                        <div class="grid grid-cols-3 gap-1.5 mt-2" x-show="currentTargetDebt > 0">
                            <button type="button" @click="setFullDebt()"
                                    class="py-1 px-1.5 rounded-lg text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100 transition-colors cursor-pointer text-center"
                                    title="دانانی تەواوی قەرز بۆ پارەی وەرگیراو">
                                هەموو قەرزەکە
                            </button>
                            <button type="button" @click="setHalfDebt()"
                                    class="py-1 px-1.5 rounded-lg text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-200 hover:bg-blue-100 transition-colors cursor-pointer text-center"
                                    title="دانانی نیوەی قەرز بۆ پارەی وەرگیراو">
                                نیوەی قەرزەکە
                            </button>
                            <button type="button" @click="amount = ''"
                                    class="py-1 px-1.5 rounded-lg text-[11px] font-bold bg-slate-100 text-slate-600 hover:bg-slate-200 transition-colors cursor-pointer text-center">
                                پاککردنەوە
                            </button>
                        </div>
                    </div>

                    {{-- ٢. داشکان (لێخۆشبوون لە قەرز) --}}
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-xs font-bold text-slate-700" for="discount">
                                داشکان (داشکاندن)
                            </label>
                            <span class="text-[10px] font-bold text-amber-600 bg-amber-50 px-1.5 py-0.5 rounded">لێخۆشبوون</span>
                        </div>
                        <input id="discount" name="discount" type="number" step="any" min="0"
                               class="field num w-full !py-2 text-2xl font-black text-center text-amber-600 bg-white"
                               x-model="discount"
                               placeholder="0">

                        {{-- دوگمە خێراکان بۆ داشکان --}}
                        <div class="grid grid-cols-2 gap-1.5 mt-2" x-show="currentTargetDebt > 0">
                            <button type="button" @click="setDiscountRemainder()"
                                    class="py-1 px-1.5 rounded-lg text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200 hover:bg-amber-100 transition-colors cursor-pointer text-center"
                                    title="داشکاندنی ئەو بەشەی قەرز کە بە کاش نادرێت">
                                داشکاندنی ماوەکە
                            </button>
                            <button type="button" @click="discount = ''"
                                    class="py-1 px-1.5 rounded-lg text-[11px] font-bold bg-slate-100 text-slate-600 hover:bg-slate-200 transition-colors cursor-pointer text-center">
                                پاککردنەوە
                            </button>
                        </div>
                    </div>

                    {{-- ٣. دراو --}}
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-xs font-bold text-slate-700" for="currency">
                                دراو <span class="text-rose-500">*</span>
                            </label>
                        </div>
                        <select id="currency" name="currency" class="field w-full !py-2.5 text-sm font-bold bg-white" x-model="currency">
                            <option value="IQD">دیناری عێراقی (د.ع)</option>
                            <option value="USD">دۆلاری ئەمریکی ($ USD)</option>
                        </select>

                        {{-- زانیاری نرخی دۆلار ئەگەر USD بێت --}}
                        <div x-show="currency === 'USD'" x-cloak class="mt-2.5 bg-amber-50/70 border border-amber-200 rounded-xl p-2.5 space-y-1.5">
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-bold text-slate-700">نرخی ١٠٠$:</span>
                                <div class="flex items-center gap-1 bg-white border border-slate-300 rounded-lg px-2 py-0.5">
                                    <input type="text" name="exchange_rate" x-model="exchangeRate"
                                           class="w-16 text-center font-mono font-bold text-xs outline-none">
                                    <span class="text-[10px] text-slate-500">د.ع</span>
                                    <button type="button" @click="fetchLiveRate()" :disabled="fetchingRate"
                                            class="text-blue-600 hover:text-blue-700 cursor-pointer" title="وەرگرتنی نرخی ئەمڕۆ">
                                        🔄
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- کارتی وردەکاری حیساباتی داشکان و کاش پێکەوە --}}
                <div class="p-3.5 rounded-xl border flex flex-wrap items-center justify-between gap-3 text-xs"
                     :class="cleanDiscount > 0 ? 'bg-amber-50/80 border-amber-300' : 'bg-slate-50 border-slate-200'">
                    <div class="flex items-center gap-3 sm:gap-5 flex-wrap">
                        <div class="flex items-center gap-1.5">
                            <span class="text-slate-500 font-bold">بڕی وەرگیراو (کاش):</span>
                            <span class="num font-black text-emerald-700 text-sm" x-text="money(cleanAmount)">0</span>
                        </div>
                        <template x-if="cleanDiscount > 0">
                            <div class="flex items-center gap-1.5">
                                <span class="text-amber-800 font-bold">+ داشکان:</span>
                                <span class="num font-black text-amber-700 text-sm" x-text="money(cleanDiscount)">0</span>
                            </div>
                        </template>
                        <div class="flex items-center gap-1.5 border-r border-slate-300 pr-3 sm:pr-5">
                            <span class="text-slate-800 font-extrabold">کۆی داشکێنراو لە قەرز:</span>
                            <span class="num font-black text-blue-800 text-sm" x-text="money(totalSettled)">0</span>
                        </div>
                    </div>
                    <div x-show="currentTargetDebt > 0" class="text-left font-bold">
                        <span class="text-slate-500">قەرزی ماوە دوای ئەم حەقدییە:</span>
                        <span class="num font-black text-sm mr-1"
                              :class="remainingAfterSettled > 0 ? 'text-rose-600' : 'text-emerald-700'"
                              x-text="money(remainingAfterSettled)">
                            0
                        </span>
                    </div>
                </div>
            </div>

            {{-- ٤. بەرواری وەرگرتن و تێبینی --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5" for="paid_at">
                        بەرواری وەرگرتن <span class="text-rose-500">*</span>
                    </label>
                    <input id="paid_at" name="paid_at" type="date" required
                           class="field num w-full !py-2 text-sm font-bold bg-white"
                           value="{{ old('paid_at', now()->toDateString()) }}">
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 mb-1.5" for="note">
                        تێبینی سەنەد
                    </label>
                    <input id="note" name="note" type="text"
                           class="field w-full !py-2 text-xs bg-white"
                           placeholder="پێشەکی، قیستی یەکەم، واسڵکردنی دەستی..."
                           value="{{ old('note') }}">
                </div>
            </div>

            {{-- ٥. دوگمەی تۆمارکردن و چاپ --}}
            <div class="pt-3 border-t border-slate-100 flex items-center justify-between gap-3">
                <button type="submit"
                        class="btn btn-primary !py-2.5 !px-8 text-sm font-black bg-emerald-600 hover:bg-emerald-700 text-white shadow-sm flex items-center gap-2 cursor-pointer">
                    <span>✓</span>
                    <span>تۆمارکردنی حەقدی و چاپکردنی سەنەد</span>
                </button>

                <a href="{{ route('payments.index') }}" class="btn btn-ghost !py-2 text-xs font-bold text-slate-600">
                    پاشگەزبوونەوە
                </a>
            </div>

        </form>
    </div>

</div>

<script>
function customerPaymentForm(customers, orders, initialCustomer, initialOrder, initialAmount, initialDiscount, initialRate) {
    return {
        customers: customers,
        orders: orders,
        selectedCustomer: initialCustomer || '',
        selectedOrder: initialOrder || '',
        amount: initialAmount || '',
        discount: initialDiscount || '',
        currency: 'IQD',
        exchangeRate: initialRate || '150,000',
        fetchingRate: false,

        get cleanAmount() {
            return parseFloat(this.amount) || 0;
        },

        get cleanDiscount() {
            return parseFloat(this.discount) || 0;
        },

        get totalSettled() {
            return this.cleanAmount + this.cleanDiscount;
        },

        get currentCustomer() {
            return this.customers.find(c => String(c.id) === String(this.selectedCustomer)) || null;
        },

        get currentCustomerDebt() {
            if (!this.currentCustomer) return 0;
            if (this.currency === 'USD') {
                return Math.max(0, parseFloat(this.currentCustomer.balance_usd) || 0);
            }
            return Math.max(0, parseFloat(this.currentCustomer.balance_iqd) || 0);
        },

        get selectedOrderObj() {
            return this.orders.find(o => String(o.id) === String(this.selectedOrder)) || null;
        },

        get filteredOrders() {
            if (!this.selectedCustomer) return this.orders;
            return this.orders.filter(o => String(o.customer_id) === String(this.selectedCustomer));
        },

        get currentTargetDebt() {
            if (this.selectedOrderObj) {
                const rem = parseFloat(this.selectedOrderObj.remaining);
                if (!isNaN(rem) && rem >= 0) {
                    return rem;
                }
                return parseFloat(this.selectedOrderObj.total) || 0;
            }
            return this.currentCustomerDebt;
        },

        get remainingAfterSettled() {
            return Math.max(0, this.currentTargetDebt - this.totalSettled);
        },

        setFullDebt() {
            this.amount = Math.max(0, this.currentTargetDebt - this.cleanDiscount);
        },

        setHalfDebt() {
            this.amount = Math.max(0, Math.round(this.currentTargetDebt / 2) - this.cleanDiscount);
        },

        setDiscountRemainder() {
            this.discount = Math.max(0, this.currentTargetDebt - this.cleanAmount);
        },

        handleCustomerChange() {
            this.selectedOrder = '';
        },

        handleOrderChange() {
            if (this.selectedOrder) {
                const ord = this.orders.find(o => String(o.id) === String(this.selectedOrder));
                if (ord) {
                    if (!this.selectedCustomer) {
                        this.selectedCustomer = ord.customer_id;
                    }
                    if (ord.currency) {
                        this.currency = ord.currency;
                    }
                    if (ord.remaining > 0 && (!this.amount || this.amount == 0)) {
                        this.amount = Math.max(0, ord.remaining - this.cleanDiscount);
                    }
                }
            }
        },

        fetchLiveRate() {
            this.fetchingRate = true;
            fetch('/api/exchange-rate/live')
                .then(res => res.json())
                .then(data => {
                    if (data.ok && data.rate_per_100) {
                        this.exchangeRate = data.rate_per_100.toLocaleString('en-US');
                    }
                })
                .catch(() => {})
                .finally(() => {
                    this.fetchingRate = false;
                });
        },

        formatNumber(val) {
            const n = parseFloat(val) || 0;
            return n.toLocaleString('en-US');
        },

        money(val) {
            const n = parseFloat(val) || 0;
            return n.toLocaleString('en-US') + (this.currency === 'USD' ? ' $' : ' د.ع');
        }
    };
}
</script>
@endsection
