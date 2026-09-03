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
                        <div class="text-xs text-slate-400 mt-0.5" x-text="selectedCustomer ? 'تەواوی باڵانسی ماوە' : 'موشتەری هەڵبژێرە'"></div>
                    </div>
                    <div class="num text-xl font-black"
                         :class="currentCustomerDebt > 0 ? 'text-rose-600' : 'text-emerald-700'"
                         x-text="money(currentCustomerDebt)">
                        0 د.ع
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
                                x-text="'وەسڵی #' + ord.invoice_no + ' — ماوە: ' + (ord.remaining > 0 ? Number(ord.remaining).toLocaleString('en-US') + ' د.ع' : 'تەواوی پارەکەی دراوە') + ' (کۆی گشتی: ' + Number(ord.total).toLocaleString('en-US') + ' ' + (ord.currency === 'USD' ? '$' : 'د.ع') + ')'"
                                :selected="selectedOrder == ord.id">
                        </option>
                    </template>
                </select>
            </div>

            {{-- ٣. بڕی پارەی حەقدی و دراو --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                {{-- بڕی پارە --}}
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5" for="amount">
                        بڕی پارەی وەرگیراو <span class="text-rose-500">*</span>
                    </label>
                    <input id="amount" name="amount" type="number" step="any" min="0.01" required
                           class="field num w-full !py-2 text-2xl font-black text-center text-emerald-700 bg-white"
                           x-model="amount"
                           placeholder="0">

                    {{-- دوگمە خێراکان بۆ دیاریکردنی بڕ --}}
                    <div class="grid grid-cols-3 gap-2 mt-2" x-show="currentCustomerDebt > 0 || selectedOrder">
                        <button type="button" @click="setFullDebt()"
                                class="py-1 px-2 rounded-lg text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100 transition-colors cursor-pointer">
                            هەموو قەرزەکە
                        </button>
                        <button type="button" @click="setHalfDebt()"
                                class="py-1 px-2 rounded-lg text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200 hover:bg-blue-100 transition-colors cursor-pointer">
                            نیوەی قەرزەکە
                        </button>
                        <button type="button" @click="amount = ''"
                                class="py-1 px-2 rounded-lg text-xs font-bold bg-slate-100 text-slate-600 hover:bg-slate-200 transition-colors cursor-pointer">
                            پاککردنەوە
                        </button>
                    </div>
                </div>

                {{-- دراو --}}
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5" for="currency">
                        دراو <span class="text-rose-500">*</span>
                    </label>
                    <select id="currency" name="currency" class="field w-full !py-2.5 text-sm font-bold bg-white" x-model="currency">
                        <option value="IQD">دیناری عێراقی (د.ع)</option>
                        <option value="USD">دۆلاری ئەمریکی ($ USD)</option>
                    </select>

                    {{-- حیساباتی دۆلار ئەگەر USD بێت --}}
                    <div x-show="currency === 'USD'" x-cloak class="mt-3 bg-amber-50/70 border border-amber-200 rounded-xl p-3 space-y-2">
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-bold text-slate-700">نرخی ١٠٠$ دۆلار:</span>
                            <div class="flex items-center gap-1.5 bg-white border border-slate-300 rounded-lg px-2 py-1">
                                <input type="text" name="exchange_rate" x-model="exchangeRate"
                                       class="w-20 text-center font-mono font-bold text-xs outline-none">
                                <span class="text-[10px] text-slate-500">د.ع</span>
                                <button type="button" @click="fetchLiveRate()" :disabled="fetchingRate"
                                        class="text-blue-600 hover:text-blue-700 cursor-pointer" title="وەرگرتنی نرخی ئەمڕۆ">
                                    🔄
                                </button>
                            </div>
                        </div>
                        <div class="flex items-center justify-between text-xs pt-1 border-t border-amber-200/60">
                            <span class="font-bold text-emerald-800">کۆی گشتی بە دینار:</span>
                            <span class="num font-black text-emerald-700 text-sm" x-text="money(amountIqd)">0 د.ع</span>
                        </div>
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
function customerPaymentForm(customers, orders, initialCustomer, initialOrder, initialAmount, initialRate) {
    return {
        customers: customers,
        orders: orders,
        selectedCustomer: initialCustomer || '',
        selectedOrder: initialOrder || '',
        amount: initialAmount || '',
        currency: 'IQD',
        exchangeRate: initialRate || '150,000',
        fetchingRate: false,

        get currentCustomer() {
            return this.customers.find(c => String(c.id) === String(this.selectedCustomer)) || null;
        },

        get currentCustomerDebt() {
            return this.currentCustomer ? Math.max(0, parseFloat(this.currentCustomer.balance) || 0) : 0;
        },

        get selectedOrderObj() {
            return this.orders.find(o => String(o.id) === String(this.selectedOrder)) || null;
        },

        get filteredOrders() {
            if (!this.selectedCustomer) return this.orders;
            return this.orders.filter(o => String(o.customer_id) === String(this.selectedCustomer));
        },

        get cleanRate() {
            const r = parseFloat(this.exchangeRate.toString().replace(/[^0-9.]/g, ''));
            return isNaN(r) || r <= 0 ? 150000 : r;
        },

        get amountIqd() {
            const amt = parseFloat(this.amount);
            if (isNaN(amt) || amt <= 0) return 0;
            if (this.currency === 'USD') {
                return amt * (this.cleanRate / 100);
            }
            return amt;
        },

        get currentTargetDebt() {
            if (this.selectedOrderObj) {
                const rem = parseFloat(this.selectedOrderObj.remaining);
                if (!isNaN(rem) && rem >= 0) {
                    return rem;
                }
                return parseFloat(this.selectedOrderObj.total) || 0;
            }
            if (this.currency === 'USD') {
                const ratePer1 = this.cleanRate > 0 ? (this.cleanRate / 100) : 1500;
                return Math.round(this.currentCustomerDebt / ratePer1);
            }
            return this.currentCustomerDebt;
        },

        setFullDebt() {
            this.amount = this.currentTargetDebt;
        },

        setHalfDebt() {
            this.amount = Math.round(this.currentTargetDebt / 2);
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
                        this.amount = ord.remaining;
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

        money(val) {
            const n = parseFloat(val) || 0;
            return n.toLocaleString('en-US') + ' د.ع';
        }
    };
}
</script>
@endsection
