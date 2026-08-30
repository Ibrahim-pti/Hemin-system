@extends('layouts.app')
@section('title', $direction === 'in' ? 'وەرگرتنی پارە (حەقدی)' : 'دانی پارە')

@section('actions')
    <a href="{{ route('payments.index') }}" class="btn btn-ghost !py-1.5 !px-3 text-xs gap-1 border border-slate-200 hover:bg-slate-100 font-bold text-slate-700">
        <span>&larr;</span>
        <span>گەڕانەوە بۆ لیست</span>
    </a>
@endsection

@section('content')

<div class="max-w-2xl mx-auto space-y-4">

    @if ($errors->any())
        <div class="bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl p-4 text-xs font-bold space-y-1">
            <ul class="list-inside list-disc">
                @foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- پێشاندان ئەگەر بەستراوە بە وەسڵ یان پسوولەوە --}}
    @if ($order)
        <div class="bg-blue-50/70 border border-blue-200 rounded-2xl p-4 flex items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <span class="size-10 rounded-xl bg-blue-600 text-white flex items-center justify-center text-lg">📄</span>
                <div>
                    <div class="text-xs font-bold text-blue-950">وەرگرتنی حەقدی لەسەر وەسڵی فرۆشتن</div>
                    <div class="text-sm font-mono font-black text-blue-700">#{{ $order->invoice_no }} ({{ $order->customer?->name }})</div>
                </div>
            </div>
            <div class="text-left">
                <div class="text-[11px] font-bold text-slate-500">قەرزی ماوەی وەسڵ:</div>
                <div class="text-base font-black text-rose-600 num">{{ fmt_money($order->remaining(), $order->currency) }}</div>
            </div>
        </div>
    @elseif ($purchase)
        <div class="bg-indigo-50/70 border border-indigo-200 rounded-2xl p-4 flex items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <span class="size-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center text-lg">🛒</span>
                <div>
                    <div class="text-xs font-bold text-indigo-950">دانی پارە لەسەر پسوولەی کڕین</div>
                    <div class="text-sm font-mono font-black text-indigo-700">#{{ $purchase->invoice_no }} ({{ $purchase->supplier?->name }})</div>
                </div>
            </div>
            <div class="text-left">
                <div class="text-[11px] font-bold text-slate-500">ماوەی سەر کارگە:</div>
                <div class="text-base font-black text-rose-600 num">{{ fmt_money($purchase->remaining(), $purchase->currency) }}</div>
            </div>
        </div>
    @endif

    {{-- فۆرمی سەرەکی --}}
    <form method="POST" action="{{ route('payments.store') }}"
          x-data="{
              kind: '{{ old('party_kind', $direction === 'in' ? 'customer' : 'supplier') }}',
              currency: '{{ old('currency', 'IQD') }}',
              amount: {{ (float) old('amount', $order ? $order->remaining() : ($purchase ? $purchase->remaining() : 0)) }},
              rate: {{ $rate }},
              selectedPartyId: '{{ old('party_id', $selected['customer'] ?? ($selected['supplier'] ?? '')) }}',
              selectedOrderId: '{{ old('order_id', $selected['order'] ?? '') }}',
              selectedPurchaseId: '{{ old('purchase_id', $selected['purchase'] ?? '') }}',
              get amountIqd() { return this.currency === 'USD' ? this.amount * this.rate : this.amount; }
          }"
          class="bg-white rounded-2xl p-6 border border-slate-100 shadow-xs space-y-5">
        @csrf
        <input type="hidden" name="direction" value="{{ $direction }}">

        <div class="border-b border-slate-100 pb-3 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="text-xl">{{ $direction === 'in' ? '📥' : '📤' }}</span>
                <h2 class="text-sm font-black text-slate-900">
                    {{ $direction === 'in' ? 'تۆمارکردنی حەقدی و وەرگرتنی پارە لە کڕیار' : 'تۆمارکردنی پارەدان بە فرۆشیار یان کارمەند' }}
                </h2>
            </div>
            <span class="text-xs font-bold px-2.5 py-0.5 rounded-full {{ $direction === 'in' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200' }}">
                {{ $direction === 'in' ? 'حەقدی (داهات)' : 'پارەدان (خەرجی)' }}
            </span>
        </div>

        {{-- جۆری لایەن --}}
        <div>
            <label class="block text-xs font-bold text-slate-700 mb-2">لایەنی مامەڵە</label>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                @php
                    $partyOptions = [
                        'customer' => ['label' => 'کڕیار', 'icon' => '👤'],
                        'supplier' => ['label' => 'فرۆشیار', 'icon' => '🏢'],
                        'employee' => ['label' => 'کارمەند', 'icon' => '👷'],
                        'other' => ['label' => 'هیتر / گشتی', 'icon' => '🏷️'],
                    ];
                @endphp
                @foreach ($partyOptions as $val => $info)
                    <label class="cursor-pointer rounded-xl border p-2.5 text-center text-xs font-bold flex flex-col items-center gap-1 transition-all"
                           :class="kind === '{{ $val }}'
                               ? 'border-blue-600 bg-blue-50 text-blue-700 shadow-2xs'
                               : 'border-slate-200 bg-slate-50/60 hover:bg-slate-100 text-slate-700'">
                        <input type="radio" name="party_kind" value="{{ $val }}" x-model="kind" class="sr-only">
                        <span class="text-base">{{ $info['icon'] }}</span>
                        <span>{{ $info['label'] }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        {{-- ناوی کەس / لایەن --}}
        <div x-show="kind !== 'other'">
            <label class="block text-xs font-bold text-slate-700 mb-1.5" for="party_id">
                <span x-text="kind === 'customer' ? 'ناوی کڕیار' : (kind === 'supplier' ? 'ناوی فرۆشیار' : 'ناوی کارمەند')"></span>
                <span class="text-rose-500">*</span>
            </label>
            <select id="party_id" name="party_id" class="w-full px-3 py-2.5 text-xs font-bold rounded-xl border border-slate-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 bg-slate-50/50"
                    x-model="selectedPartyId"
                    @change="handlePartyChange($event.target.value)">
                <option value="">— ناو هەڵبژێرە —</option>
            </select>
        </div>

        {{-- کاتێک کڕیار هەڵبژێردرا، دیاریکردنی وەسڵی فرۆشتن --}}
        <div x-show="kind === 'customer'" x-cloak class="p-3.5 bg-blue-50/40 rounded-xl border border-blue-100 space-y-1.5">
            <label class="block text-xs font-bold text-slate-700" for="order_id">
                <span>دیاریکردنی وەسڵی فرۆشتن</span>
                <span class="text-slate-400 font-normal text-[11px]">(ئارەزوومەندانە — بۆ دانەوەی قەرزی وەسڵێکی دیاریکراو)</span>
            </label>
            <select id="order_id" name="order_id" class="w-full px-3 py-2 text-xs rounded-lg border border-slate-200 bg-white"
                    x-model="selectedOrderId">
                <option value="">— تەواوی حسابی کڕیار (گشتی) —</option>
                @foreach ($orders as $ord)
                    <option value="{{ $ord->id }}" data-customer="{{ $ord->customer_id }}" {{ $selected['order'] == $ord->id ? 'selected' : '' }}>
                        وەسڵی {{ $ord->invoice_no }} — بڕ: {{ fmt_money($ord->total, $ord->currency) }} ({{ fmt_date($ord->order_date) }})
                    </option>
                @endforeach
            </select>
        </div>

        {{-- کاتێک فرۆشیار هەڵبژێردرا، دیاریکردنی پسوولەی کڕین --}}
        <div x-show="kind === 'supplier'" x-cloak class="p-3.5 bg-indigo-50/40 rounded-xl border border-indigo-100 space-y-1.5">
            <label class="block text-xs font-bold text-slate-700" for="purchase_id">
                <span>دیاریکردنی پسوولەی کڕین</span>
                <span class="text-slate-400 font-normal text-[11px]">(ئارەزوومەندانە — بۆ دانەوەی پسوولەیەکی کڕین)</span>
            </label>
            <select id="purchase_id" name="purchase_id" class="w-full px-3 py-2 text-xs rounded-lg border border-slate-200 bg-white"
                    x-model="selectedPurchaseId">
                <option value="">— تەواوی حسابی فرۆشیار (گشتی) —</option>
                @foreach ($purchases as $pch)
                    <option value="{{ $pch->id }}" data-supplier="{{ $pch->supplier_id }}" {{ $selected['purchase'] == $pch->id ? 'selected' : '' }}>
                        پسوولەی {{ $pch->invoice_no }} — بڕ: {{ fmt_money($pch->total, $pch->currency) }} ({{ fmt_date($pch->purchase_date) }})
                    </option>
                @endforeach
            </select>
        </div>

        <div x-show="kind === 'other'" x-cloak>
            <label class="block text-xs font-bold text-slate-700 mb-1.5" for="party_name">ناوی لایەن / کەس</label>
            <input id="party_name" name="party_name" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:border-blue-500 bg-slate-50/50" value="{{ old('party_name') }}" placeholder="ناوی کەس یان لایەن...">
        </div>

        {{-- بڕی پارە و دراو --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div class="sm:col-span-2">
                <label class="block text-xs font-bold text-slate-700 mb-1.5" for="amount">
                    <span>بڕی پارە</span>
                    <span class="text-rose-500">*</span>
                </label>
                <input id="amount" name="amount" type="number" step="any" min="0.01" required
                       class="w-full px-3 py-2.5 text-sm font-black rounded-xl border border-slate-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 bg-slate-50/50 num"
                       x-model.number="amount" value="{{ old('amount') }}" placeholder="0">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5" for="currency">دراو</label>
                <select id="currency" name="currency" class="w-full px-3 py-2.5 text-xs font-bold rounded-xl border border-slate-200 focus:border-blue-500 bg-slate-50/50" x-model="currency">
                    <option value="IQD">دیناری عێراقی (د.ع)</option>
                    <option value="USD">دۆلاری ئەمریکی ($)</option>
                </select>
            </div>
        </div>

        {{-- گۆڕینەوەی دۆلار ئەگەر USD بێت --}}
        <div x-show="currency === 'USD'" x-cloak class="p-3 bg-blue-50/50 border border-blue-200 rounded-xl text-xs flex items-center justify-between">
            <span class="text-blue-900 font-medium">بە نرخی <b class="num" x-text="rate.toLocaleString()"></b> د.ع بۆ هەر 100$:</span>
            <span class="num font-black text-blue-700 text-sm" x-text="amountIqd.toLocaleString() + ' د.ع'"></span>
        </div>

        {{-- قاسە و بەروار --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5" for="cash_box_id">قاسە</label>
                <select id="cash_box_id" name="cash_box_id" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:border-blue-500 bg-slate-50/50">
                    <option value="">— خۆکار بەپێی دراو دیاری دەکرێت —</option>
                    @foreach ($cashBoxes as $box)
                        <option value="{{ $box->id }}">{{ $box->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5" for="paid_at">
                    <span>بەروار</span>
                    <span class="text-rose-500">*</span>
                </label>
                <input id="paid_at" name="paid_at" type="date" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:border-blue-500 bg-slate-50/50 num" required
                       value="{{ old('paid_at', now()->toDateString()) }}">
            </div>
        </div>

        {{-- تێبینی --}}
        <div>
            <label class="block text-xs font-bold text-slate-700 mb-1.5" for="note">تێبینی</label>
            <input id="note" name="note" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:border-blue-500 bg-slate-50/50" value="{{ old('note') }}" placeholder="پێشەکی، قیستی مانگانە، وەرگرتنی کاش...">
        </div>

        {{-- دوگمەکان --}}
        <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
            <a href="{{ route('payments.index') }}" class="btn btn-ghost !py-2 !px-4 text-xs font-bold text-slate-600">
                پاشگەزبوونەوە
            </a>
            <button type="submit" class="btn !py-2 !px-6 text-xs font-bold {{ $direction === 'in' ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-blue-600 hover:bg-blue-700' }} text-white rounded-xl shadow-xs cursor-pointer">
                <span>✓</span>
                <span>تۆمارکردنی حەقدی و چاپکردن</span>
            </button>
        </div>
    </form>

</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const groups = {
        customer: @js($customers->map(fn ($c) => ['id' => $c->id, 'name' => $c->name.($c->phone ? ' — '.$c->phone : '')])),
        supplier: @js($suppliers->map(fn ($s) => ['id' => $s->id, 'name' => $s->name])),
        employee: @js($employees->map(fn ($e) => ['id' => $e->id, 'name' => $e->name])),
    };

    const select = document.getElementById('party_id');
    const orderSelect = document.getElementById('order_id');
    const purchaseSelect = document.getElementById('purchase_id');
    const preselect = {
        customer: @js($selected['customer']),
        supplier: @js($selected['supplier']),
    };

    const render = (kind) => {
        if (! groups[kind]) return;

        select.innerHTML = '<option value="">— ناو هەڵبژێرە —</option>';
        groups[kind].forEach((row) => {
            const option = new Option(row.name, row.id);
            if (preselect[kind] == row.id) option.selected = true;
            select.add(option);
        });

        if (kind === 'customer') {
            filterOrders(select.value || preselect['customer']);
        } else if (kind === 'supplier') {
            filterPurchases(select.value || preselect['supplier']);
        }
    };

    window.handlePartyChange = (id) => {
        const activeKind = document.querySelector('input[name="party_kind"]:checked')?.value;
        if (activeKind === 'customer') {
            filterOrders(id);
        } else if (activeKind === 'supplier') {
            filterPurchases(id);
        }
    };

    function filterOrders(custId) {
        if (!orderSelect) return;
        const options = orderSelect.querySelectorAll('option');
        options.forEach(opt => {
            if (!opt.value) return;
            const cId = opt.getAttribute('data-customer');
            if (!custId || cId === String(custId)) {
                opt.style.display = '';
            } else {
                opt.style.display = 'none';
            }
        });
    }

    function filterPurchases(suppId) {
        if (!purchaseSelect) return;
        const options = purchaseSelect.querySelectorAll('option');
        options.forEach(opt => {
            if (!opt.value) return;
            const sId = opt.getAttribute('data-supplier');
            if (!suppId || sId === String(suppId)) {
                opt.style.display = '';
            } else {
                opt.style.display = 'none';
            }
        });
    }

    // گوێگرتن لە گۆڕینی جۆر.
    document.querySelectorAll('input[name="party_kind"]').forEach((input) => {
        input.addEventListener('change', () => render(input.value));
    });

    render('{{ old('party_kind', $direction === 'in' ? 'customer' : 'supplier') }}');
});
</script>
@endpush

@endsection
