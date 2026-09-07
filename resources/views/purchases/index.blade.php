@extends('layouts.app')
@section('title', 'کڕینەکان')

@section('actions')
    <a href="{{ route('purchases.create') }}"
       class="btn btn-primary !py-2 !px-4 text-xs font-bold gap-1.5 shadow-sm bg-blue-600 hover:bg-blue-700">
        <span>+</span>
        <span>کڕینی نوێ</span>
    </a>
@endsection

@section('content')

{{-- ١. کارتەکانی ئاماری سەرەوە (هاوشێوەی بەشی فرۆشتن) --}}
<div class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 mb-6">
    {{-- کۆی کڕینەکان --}}
    <div class="bg-white rounded-2xl p-5 shadow-xs border border-slate-100 border-r-4 border-r-blue-500 relative flex items-center justify-between overflow-hidden">
        <div>
            <div class="text-3xl font-black text-slate-800 num tracking-tight">{{ fmt_num($totalPurchasesCount) }}</div>
            <div class="text-xs font-bold text-slate-500 mt-1">کۆی پسوولەکانی کڕین</div>
        </div>
        <div class="size-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl shrink-0">
            🛒
        </div>
    </div>

    {{-- کۆی پارەی کڕین --}}
    <div class="bg-white rounded-2xl p-5 shadow-xs border border-slate-100 border-r-4 border-r-emerald-500 relative flex items-center justify-between overflow-hidden">
        <div>
            <div class="text-2xl font-black text-slate-800 num tracking-tight">{{ fmt_money($totalPurchasesAmount) }}</div>
            <div class="text-xs font-bold text-slate-500 mt-1">کۆی پارەی کڕین (د.ع)</div>
        </div>
        <div class="size-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl shrink-0">
            💵
        </div>
    </div>

    {{-- پارەی دراو بە فرۆشیاران --}}
    <div class="bg-white rounded-2xl p-5 shadow-xs border border-slate-100 border-r-4 border-r-teal-500 relative flex items-center justify-between overflow-hidden">
        <div>
            <div class="text-2xl font-black text-teal-700 num tracking-tight">{{ fmt_money($totalPurchasesPaid) }}</div>
            <div class="text-xs font-bold text-slate-500 mt-1">پارەی دراو بە فرۆشیاران (د.ع)</div>
        </div>
        <div class="size-12 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center text-xl shrink-0">
            💳
        </div>
    </div>

    {{-- قەرزی ماوە بۆ کۆمپانیاکان --}}
    <div class="bg-white rounded-2xl p-5 shadow-xs border border-slate-100 border-r-4 border-r-rose-500 relative flex items-center justify-between overflow-hidden">
        <div>
            <div class="text-2xl font-black text-rose-600 num tracking-tight">{{ fmt_money($totalCompanyDebt > 0 ? $totalCompanyDebt : $totalRemainingDebt) }}</div>
            <div class="text-xs font-bold text-slate-500 mt-1">قەرزی ماوە بۆ کۆمپانیاکان (د.ع)</div>
        </div>
        <div class="size-12 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-xl shrink-0">
            ⚠️
        </div>
    </div>
</div>

<div x-data="{
    tab: '{{ request('tab', 'invoices') }}',
    payModal: false,
    payPurchase: { id: null, invoice_no: '', supplier_name: '', remaining: 0, currency: 'IQD', total: 0, paid: 0 },
    payForm: { amount: '', cash_box_id: '{{ $cashBoxes->first()?->id ?? '' }}', paid_at: '{{ now()->toDateString() }}', note: '' },
    openPayment(p) {
        this.payPurchase = p;
        this.payForm.amount = p.remaining.toLocaleString('en-US');
        this.payForm.note = 'پارەدانی قەرزی پسوولەی #' + p.invoice_no;
        this.payModal = true;
    },
    closePayment() {
        this.payModal = false;
    },
    fillFullAmount() {
        this.payForm.amount = this.payPurchase.remaining.toLocaleString('en-US');
    },
    formatAmount(e) {
        let clean = e.target.value.replace(/[^0-9.]/g, '');
        let parts = clean.split('.');
        if (parts.length > 2) parts = [parts[0], parts.slice(1).join('')];
        let int = parts[0] ? parseInt(parts[0], 10).toLocaleString('en-US') : '';
        let dec = parts.length > 1 ? '.' + parts[1] : '';
        e.target.value = int ? int + dec : '';
        this.payForm.amount = e.target.value;
    }
}">
    {{-- ٢. سویچەری نێوان تابەکان --}}
    <div class="flex items-center gap-2 mb-4">
        <button @click="tab = 'invoices'"
                :class="tab === 'invoices' ? 'bg-blue-600 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-50 border border-slate-200'"
                class="px-5 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2 cursor-pointer">
            <span>🧾 هەموو پسوولەکانی کڕین</span>
            <span :class="tab === 'invoices' ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600'"
                  class="px-2 py-0.5 rounded-full text-xs font-mono font-bold">{{ $totalPurchasesCount }}</span>
        </button>

        <button @click="tab = 'suppliers'"
                :class="tab === 'suppliers' ? 'bg-blue-600 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-50 border border-slate-200'"
                class="px-5 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2 cursor-pointer">
            <span>🏢 کۆمپانیا و فرۆشیارەکان</span>
            <span :class="tab === 'suppliers' ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600'"
                  class="px-2 py-0.5 rounded-full text-xs font-mono font-bold">{{ $totalSuppliersCount }}</span>
        </button>
    </div>

    {{-- ٣. تابی یەکەم: هەموو پسوولەکانی کڕین لەگەڵ وێنەی وەسڵ --}}
    <div x-show="tab === 'invoices'" class="bg-white rounded-2xl shadow-xs border border-slate-100 overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex flex-wrap items-center justify-between gap-3">
            <div class="font-bold text-slate-800 text-sm flex items-center gap-2">
                <span>🧾</span>
                <span>هەموو پسوولەکانی کڕین</span>
            </div>

            {{-- فۆرمی گەڕان و فلتەر --}}
            <form method="GET" class="w-full sm:w-72">
                <input type="hidden" name="tab" value="invoices">
                <div class="relative">
                    <input type="search" name="q" value="{{ request('tab') === 'invoices' ? request('q') : '' }}"
                           class="w-full pl-8 pr-3 py-1.5 text-xs rounded-xl border border-slate-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 bg-slate-50/50"
                           placeholder="ژمارەی پسوولە یان فرۆشیار...">
                    <span class="absolute left-2.5 top-2 text-slate-400 text-xs">🔍</span>
                </div>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="table w-full text-right">
                <thead>
                    <tr class="text-xs text-slate-500 border-b border-slate-100 bg-slate-50/40">
                        <th class="py-3 px-4 w-12 text-center">#</th>
                        <th class="py-3 px-4 text-center">پسوولە</th>
                        <th class="py-3 px-4 text-center">وێنەی وەسڵ</th>
                        <th class="py-3 px-4">کۆمپانیا / فرۆشیار</th>
                        <th class="py-3 px-4 text-center">کۆگا</th>
                        <th class="py-3 px-4 text-center">بەروار</th>
                        <th class="py-3 px-4 text-center">کۆی گشتی</th>
                        <th class="py-3 px-4 text-center">دراوە</th>
                        <th class="py-3 px-4 text-center">ماوە (قەرز)</th>
                        <th class="py-3 px-4 text-center w-24">کردار</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse ($purchases as $index => $purchase)
                        @php
                            $remaining = $purchase->remaining();
                            $paid = $purchase->paidTotal();
                        @endphp
                        <tr class="hover:bg-blue-50/40 transition-colors cursor-pointer"
                            onclick="if (!event.target.closest('a') && !event.target.closest('img')) window.location.href='{{ route('purchases.show', $purchase) }}'">
                            {{-- # --}}
                            <td class="py-3.5 px-4 text-center num text-slate-400 font-medium">
                                {{ $purchases->firstItem() + $index }}
                            </td>

                            {{-- پسوولە --}}
                            <td class="py-3.5 px-4 text-center">
                                <a href="{{ route('purchases.show', $purchase) }}"
                                   class="inline-flex items-center justify-center min-w-8 px-3 py-1 rounded-lg text-xs font-mono font-bold text-blue-700 bg-blue-50 hover:bg-blue-600 hover:text-white border border-blue-200 shadow-2xs hover:shadow-xs transition-all cursor-pointer">
                                    {{ $purchase->invoice_no }}
                                </a>
                            </td>

                            {{-- وێنەی وەسڵ --}}
                            <td class="py-2.5 px-3 text-center">
                                @if ($purchase->imageUrl())
                                    <div class="inline-flex items-center justify-center group relative">
                                        <img src="{{ $purchase->imageUrl() }}"
                                             class="size-12 rounded-xl object-cover border-2 border-slate-200 shadow-2xs group-hover:scale-125 group-hover:border-teal-500 transition-all cursor-pointer bg-white"
                                             onclick="event.stopPropagation(); window.open('{{ $purchase->imageUrl() }}', '_blank')"
                                             title="کرتە بکە بۆ بینینی تەواوی وێنەکە">
                                        <span class="absolute -bottom-1 -right-1 size-4 bg-teal-600 text-white rounded-full flex items-center justify-center text-[9px] shadow-xs pointer-events-none">🔍</span>
                                    </div>
                                @else
                                    <span class="inline-flex items-center gap-1 text-[11px] text-slate-400 font-medium bg-slate-50 px-2 py-1 rounded-md border border-slate-100">
                                        <span>📷</span>
                                        <span>بێ وێنە</span>
                                    </span>
                                @endif
                            </td>

                            {{-- کۆمپانیا / فرۆشیار --}}
                            <td class="py-3.5 px-4">
                                @if ($purchase->supplier)
                                    <a href="{{ route('suppliers.show', $purchase->supplier) }}" class="font-bold text-slate-800 hover:text-blue-600 transition-colors">
                                        {{ $purchase->supplier->name }}
                                    </a>
                                    @if ($purchase->supplier->phone)
                                        <span class="num block text-xs text-slate-400" dir="ltr">{{ $purchase->supplier->phone }}</span>
                                    @endif
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>

                            {{-- کۆگا --}}
                            <td class="py-3.5 px-4 text-center text-xs text-slate-600">
                                <span class="px-2 py-0.5 rounded-md bg-slate-100 font-medium">
                                    {{ $purchase->warehouse?->name ?: 'سەرەکی' }}
                                </span>
                            </td>

                            {{-- بەروار --}}
                            <td class="py-3.5 px-4 text-center num text-xs text-slate-600">
                                {{ fmt_date($purchase->purchase_date) }}
                            </td>

                            {{-- کۆی گشتی --}}
                            <td class="py-3.5 px-4 text-center num font-bold text-slate-900">
                                {{ fmt_money($purchase->total, $purchase->currency) }}
                            </td>

                            {{-- دراوە --}}
                            <td class="py-3.5 px-4 text-center num font-semibold text-emerald-700">
                                {{ fmt_money($paid, $purchase->currency) }}
                            </td>

                            {{-- ماوە --}}
                            <td class="py-3.5 px-4 text-center num font-bold {{ $remaining > 0 ? 'text-rose-600' : 'text-slate-400' }}">
                                {{ $remaining > 0 ? fmt_money($remaining, $purchase->currency) : '-' }}
                            </td>

                            {{-- کردار --}}
                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                <div class="inline-flex items-center gap-1.5" onclick="event.stopPropagation()">
                                    <a href="{{ route('purchases.show', $purchase) }}"
                                       class="btn btn-ghost !py-1 !px-2.5 text-xs font-bold text-slate-700 hover:bg-slate-100">
                                        بینین
                                    </a>

                                    @if ($remaining > 0)
                                        <button type="button"
                                                @click="openPayment({
                                                    id: {{ $purchase->id }},
                                                    invoice_no: '{{ $purchase->invoice_no }}',
                                                    supplier_name: '{{ addslashes($purchase->supplier?->name ?? 'نەناسراو') }}',
                                                    remaining: {{ (float) $remaining }},
                                                    currency: '{{ $purchase->currency }}',
                                                    total: {{ (float) $purchase->total }},
                                                    paid: {{ (float) $paid }}
                                                })"
                                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 shadow-2xs transition-all cursor-pointer">
                                            <span>💳</span>
                                            <span>پارەدان</span>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="py-10 text-center text-slate-400 text-sm font-medium">
                                هیچ پسوولەیەکی کڕین نەدۆزرایەوە.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($purchases->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $purchases->links() }}
            </div>
        @endif
    </div>

    {{-- ٤. تابی دووەم: کۆمپانیا و فرۆشیارەکان و قەرزەکانیان --}}
    <div x-show="tab === 'suppliers'" class="bg-white rounded-2xl shadow-xs border border-slate-100 overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex flex-wrap items-center justify-between gap-3">
            <div class="font-bold text-slate-800 text-sm flex items-center gap-2">
                <span>🏢</span>
                <span>کۆمپانیا و فرۆشیارەکان و قەرزەکانیان</span>
            </div>

            {{-- فۆرمی گەڕان لە کۆمپانیاکان --}}
            <form method="GET" class="w-full sm:w-72">
                <input type="hidden" name="tab" value="suppliers">
                <div class="relative">
                    <input type="search" name="q" value="{{ request('tab') === 'suppliers' ? request('q') : '' }}"
                           class="w-full pl-8 pr-3 py-1.5 text-xs rounded-xl border border-slate-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 bg-slate-50/50"
                           placeholder="گەڕان لە کۆمپانیا و فرۆشیاران...">
                    <span class="absolute left-2.5 top-2 text-slate-400 text-xs">🔍</span>
                </div>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="table w-full text-right">
                <thead>
                    <tr class="text-xs text-slate-500 border-b border-slate-100 bg-slate-50/40">
                        <th class="py-3 px-4 w-12 text-center">#</th>
                        <th class="py-3 px-4">کۆمپانیا / فرۆشیار</th>
                        <th class="py-3 px-4 text-center">تەلەفۆن</th>
                        <th class="py-3 px-4 text-center">ژمارەی کڕین</th>
                        <th class="py-3 px-4 text-center">کۆی کڕین</th>
                        <th class="py-3 px-4 text-center">دراوە</th>
                        <th class="py-3 px-4 text-center">قەرزی ماوە</th>
                        <th class="py-3 px-4 text-center">دوایین کڕین</th>
                        <th class="py-3 px-4 text-center w-24">کردار</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse ($suppliersSummary as $index => $sup)
                        @php
                            $firstLetter = mb_substr($sup->name, 0, 1, 'UTF-8');
                        @endphp
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            {{-- # --}}
                            <td class="py-3.5 px-4 text-center num text-slate-400 font-medium">
                                {{ $index + 1 }}
                            </td>

                            {{-- ناوی فرۆشیار --}}
                            <td class="py-3.5 px-4">
                                <a href="{{ route('suppliers.show', $sup->id) }}" class="flex items-center gap-2.5 group">
                                    <span class="size-7 rounded-full bg-blue-50 text-blue-600 font-bold text-xs flex items-center justify-center shrink-0 group-hover:bg-blue-600 group-hover:text-white transition-colors">
                                        {{ $firstLetter }}
                                    </span>
                                    <span class="font-bold text-slate-800 group-hover:text-blue-600 transition-colors">
                                        {{ $sup->name }}
                                    </span>
                                </a>
                            </td>

                            {{-- تەلەفۆن --}}
                            <td class="py-3.5 px-4 text-center num text-xs text-slate-600" dir="ltr">
                                @if ($sup->phone)
                                    <span>{{ $sup->phone }}</span> <span class="text-slate-400">📞</span>
                                @else
                                    <span class="text-slate-300">-</span>
                                @endif
                            </td>

                            {{-- ژمارەی کڕین --}}
                            <td class="py-3.5 px-4 text-center">
                                <a href="{{ route('purchases.index', ['tab' => 'invoices', 'supplier_id' => $sup->id]) }}"
                                   class="inline-block px-2.5 py-0.5 rounded-full text-xs font-bold text-blue-700 bg-blue-50 border border-blue-100 hover:bg-blue-100 transition-colors">
                                    {{ fmt_num($sup->purchases_count) }} پسوولە
                                </a>
                            </td>

                            {{-- کۆی کڕین --}}
                            <td class="py-3.5 px-4 text-center num font-bold text-slate-800">
                                {{ fmt_money($sup->total_purchases) }}
                            </td>

                            {{-- دراوە --}}
                            <td class="py-3.5 px-4 text-center num font-semibold text-emerald-700">
                                <span class="inline-block size-1.5 rounded-full bg-emerald-500 ml-1"></span>
                                {{ fmt_money($sup->total_paid) }}
                            </td>

                            {{-- قەرزی ماوە --}}
                            <td class="py-3.5 px-4 text-center">
                                @if ($sup->balance <= 0)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <span>✓</span> <span>بێ قەرز</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200 num">
                                        <span>{{ fmt_money($sup->balance) }}</span>
                                    </span>
                                @endif
                            </td>

                            {{-- دوایین کڕین --}}
                            <td class="py-3.5 px-4 text-center num text-xs text-slate-500">
                                {{ $sup->last_purchase_date ? fmt_date($sup->last_purchase_date) : '-' }}
                            </td>

                            {{-- کردار --}}
                            <td class="py-3.5 px-4 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <a href="{{ route('suppliers.show', $sup->id) }}"
                                       class="size-8 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-600 inline-flex items-center justify-center transition-colors shadow-2xs"
                                       title="پڕۆفایل و کەشف حیساب">
                                        👁️
                                    </a>

                                    @if ($sup->balance > 0)
                                        <a href="{{ route('payments.create', ['type' => 'out', 'supplier' => $sup->id]) }}"
                                           class="size-8 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 inline-flex items-center justify-center transition-colors shadow-2xs"
                                           title="تۆمارکردنی حەقدی و پارەدان">
                                            💳
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-10 text-center text-slate-400 text-sm font-medium">
                                هیچ فرۆشیار یان کۆمپانیایەک نەدۆزرایەوە.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- مۆداڵی پارەدانی قەرزی پسوولە --}}
    <div x-show="payModal"
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs transition-opacity"
         @keydown.escape.window="closePayment()">
        
        <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl border border-slate-100 overflow-hidden transform transition-all text-right"
             @click.outside="closePayment()"
             dir="rtl">
            
            {{-- سەردێڕی مۆداڵ --}}
            <div class="bg-gradient-to-l from-emerald-600 to-teal-700 p-4 sm:p-5 text-white flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="size-10 rounded-xl bg-white/20 flex items-center justify-center text-xl shrink-0">
                        💳
                    </div>
                    <div>
                        <h3 class="font-black text-sm sm:text-base">تۆمارکردنی پارەدان بە فرۆشیار</h3>
                        <p class="text-xs text-emerald-100 mt-0.5">
                            پسوولەی <span class="font-mono font-bold text-white">#<span x-text="payPurchase.invoice_no"></span></span>
                            • فرۆشیار: <span class="font-bold text-white" x-text="payPurchase.supplier_name"></span>
                        </p>
                    </div>
                </div>
                <button type="button" @click="closePayment()" class="size-8 rounded-lg bg-white/10 hover:bg-white/20 text-white flex items-center justify-center text-lg transition-colors cursor-pointer">
                    &times;
                </button>
            </div>

            {{-- کارتی زانیاری قەرز --}}
            <div class="p-4 sm:p-5 bg-slate-50/80 border-b border-slate-100">
                <div class="grid grid-cols-3 gap-2 text-center text-xs">
                    <div class="bg-white p-2.5 rounded-xl border border-slate-200">
                        <span class="text-slate-400 block text-[11px] font-medium mb-0.5">کۆی پسوولە</span>
                        <span class="font-mono font-bold text-slate-800" x-text="payPurchase.total.toLocaleString('en-US')"></span>
                    </div>
                    <div class="bg-white p-2.5 rounded-xl border border-slate-200">
                        <span class="text-slate-400 block text-[11px] font-medium mb-0.5">دراوە</span>
                        <span class="font-mono font-bold text-emerald-600" x-text="payPurchase.paid.toLocaleString('en-US')"></span>
                    </div>
                    <div class="bg-rose-50 p-2.5 rounded-xl border border-rose-200">
                        <span class="text-rose-600 block text-[11px] font-bold mb-0.5">ماوە (قەرز)</span>
                        <span class="font-mono font-black text-rose-700" x-text="payPurchase.remaining.toLocaleString('en-US')"></span>
                    </div>
                </div>
            </div>

            {{-- فۆڕمی پارەدان --}}
            <form method="POST" :action="'/purchases/' + payPurchase.id + '/payments'" class="p-4 sm:p-5 space-y-4">
                @csrf

                {{-- بڕی پارەی دراو --}}
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="label !mb-0 font-bold" for="modal_pay_amount">
                            بڕی پارەی دراو <span class="text-rose-500">*</span>
                        </label>
                        <button type="button" @click="fillFullAmount()" class="text-xs text-teal-700 hover:text-teal-800 font-bold underline cursor-pointer">
                            دانەوەی هەمووی (<span x-text="payPurchase.remaining.toLocaleString('en-US')"></span>)
                        </button>
                    </div>
                    <div class="relative">
                        <input id="modal_pay_amount"
                               name="amount"
                               type="text"
                               inputmode="numeric"
                               required
                               x-model="payForm.amount"
                               @input="formatAmount($event)"
                               class="field num font-black text-emerald-700 text-base !py-2.5 pl-14 w-full"
                               placeholder="0">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-xs font-bold text-slate-400 pointer-events-none" x-text="payPurchase.currency">
                            د.ع
                        </span>
                    </div>
                </div>

                {{-- هەڵبژاردنی قاسە --}}
                <div>
                    <label class="label font-bold" for="modal_pay_box">دەرهێنان لە قاسەی <span class="text-rose-500">*</span></label>
                    <select id="modal_pay_box" name="cash_box_id" x-model="payForm.cash_box_id" class="field w-full cursor-pointer font-medium" required>
                        @foreach ($cashBoxes as $box)
                            <option value="{{ $box->id }}">{{ $box->name }} ({{ $box->currency }}) — باڵانس: {{ fmt_num($box->balance()) }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- بەرواری پارەدان --}}
                <div>
                    <label class="label font-bold" for="modal_pay_date">بەرواری پارەدان <span class="text-rose-500">*</span></label>
                    <input id="modal_pay_date" name="paid_at" type="date" x-model="payForm.paid_at" class="field num w-full" required>
                </div>

                {{-- تێبینی --}}
                <div>
                    <label class="label" for="modal_pay_note">تێبینی (ئارەزوومەندانە)</label>
                    <input id="modal_pay_note" name="note" type="text" x-model="payForm.note" class="field w-full text-xs" placeholder="تێبینی بنووسە...">
                </div>

                {{-- دوگمەکان --}}
                <div class="flex items-center justify-end gap-2.5 pt-2">
                    <button type="button" @click="closePayment()" class="btn btn-ghost !py-2 !px-4 text-xs font-bold text-slate-600">
                        پاشگەزبوونەوە
                    </button>
                    <button type="submit" class="btn !py-2 !px-5 text-xs font-black text-white bg-emerald-600 hover:bg-emerald-700 shadow-sm cursor-pointer">
                        تۆمارکردنی پارەدان ✔️
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

@endsection
