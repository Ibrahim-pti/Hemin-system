@extends('layouts.app')
@section('title', $direction === 'in' ? 'وەرگرتنی پارە (حەقدی)' : 'دانی پارە')

@section('actions')
    <a href="{{ route('payments.index') }}" class="btn btn-ghost !py-1.5 !px-3 text-xs gap-1 border border-slate-200 hover:bg-slate-100 font-bold text-slate-700">
        <span>&larr;</span>
        <span>گەڕانەوە بۆ لیست</span>
    </a>
@endsection

@section('content')

<div class="mx-auto max-w-3xl space-y-4">

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
        <div class="bg-blue-50/80 border border-blue-200 rounded-2xl p-4 flex items-center justify-between gap-3 shadow-2xs">
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
        <div class="bg-indigo-50/80 border border-indigo-200 rounded-2xl p-4 flex items-center justify-between gap-3 shadow-2xs">
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
              amount: '{{ old('amount', $order ? (float)$order->remaining() : ($purchase ? (float)$purchase->remaining() : '')) }}',
              exchangeRate: '{{ number_format($rate * 100) }}',
              fetchingRate: false,
              selectedPartyId: '{{ old('party_id', $selected['customer'] ?? ($selected['supplier'] ?? '')) }}',
              selectedOrderId: '{{ old('order_id', $selected['order'] ?? '') }}',
              selectedPurchaseId: '{{ old('purchase_id', $selected['purchase'] ?? '') }}',
              get cleanRate() {
                  const r = parseFloat(this.exchangeRate.toString().replace(/[^0-9.]/g, ''));
                  return isNaN(r) ? 0 : r;
              },
              get amountIqd() {
                  const amt = parseFloat(this.amount);
                  if (isNaN(amt) || amt <= 0) return 0;
                  if (this.currency === 'USD') {
                      return amt * (this.cleanRate / 100);
                  }
                  return amt;
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
              }
          }"
          class="bg-white rounded-2xl border border-slate-200/90 shadow-sm overflow-hidden">
        @csrf
        <input type="hidden" name="direction" value="{{ $direction }}">

        {{-- سەردێڕی فۆرم --}}
        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <span class="text-xl">{{ $direction === 'in' ? '📥' : '📤' }}</span>
                <div>
                    <h2 class="text-sm font-black text-slate-900">
                        {{ $direction === 'in' ? 'تۆمارکردنی حەقدی و وەرگرتنی پارە لە کڕیار' : 'تۆمارکردنی پارەدان بە فرۆشیار یان کارمەند' }}
                    </h2>
                    <p class="text-2xs text-slate-500 font-medium">زانیارییەکانی پارەدان لە خوارەوە پڕبکەوە</p>
                </div>
            </div>
            <span class="text-xs font-bold px-3 py-1 rounded-full {{ $direction === 'in' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                {{ $direction === 'in' ? 'وەرگرتن (داهات)' : 'پارەدان (خەرجی)' }}
            </span>
        </div>

        <div class="p-6 space-y-5">

            {{-- بەشی ١: جۆری لایەن --}}
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-2">لایەنی مامەڵە</label>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
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
                                   ? 'border-blue-600 bg-blue-50/90 text-blue-700 shadow-xs ring-1 ring-blue-600'
                                   : 'border-slate-200 bg-slate-50/60 hover:bg-slate-100 text-slate-600'">
                            <input type="radio" name="party_kind" value="{{ $val }}" x-model="kind" class="sr-only">
                            <span class="text-base">{{ $info['icon'] }}</span>
                            <span>{{ $info['label'] }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            {{-- بەشی ٢: ناوی کەس / لایەن --}}
            <div x-show="kind !== 'other'">
                <label class="block text-xs font-bold text-slate-700 mb-1.5" for="party_id">
                    <span x-text="kind === 'customer' ? 'ناوی کڕیار' : (kind === 'supplier' ? 'ناوی فرۆشیار' : 'ناوی کارمەند')"></span>
                    <span class="text-rose-500">*</span>
                </label>
                <select id="party_id" name="party_id" class="w-full px-3.5 py-2.5 text-xs font-bold rounded-xl border border-slate-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 bg-slate-50/40"
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
                <input id="party_name" name="party_name" class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 focus:border-blue-500 bg-slate-50/40" value="{{ old('party_name') }}" placeholder="ناوی کەس یان لایەن...">
            </div>

            {{-- بەشی ٣: بڕی پارە و دراو --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5" for="amount">
                        <span>بڕی پارە</span>
                        <span class="text-rose-500">*</span>
                    </label>
                    <input id="amount" name="amount" type="number" step="any" min="0.01" required
                           class="w-full px-3.5 py-2.5 text-sm font-black rounded-xl border border-slate-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 bg-slate-50/40 num"
                           x-model="amount" placeholder="0">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5" for="currency">دراو</label>
                    <select id="currency" name="currency" class="w-full px-3.5 py-2.5 text-xs font-bold rounded-xl border border-slate-200 focus:border-blue-500 bg-slate-50/40" x-model="currency">
                        <option value="IQD">دیناری عێراقی (د.ع)</option>
                        <option value="USD">دۆلاری ئەمریکی ($ USD)</option>
                    </select>
                </div>
            </div>

            {{-- گۆڕینەوەی دۆلار ئەگەر USD بێت (دیزاینی خاوێن و ڕێک) --}}
            <div x-show="currency === 'USD'" x-cloak class="p-4 bg-slate-50 rounded-2xl border border-slate-200 space-y-3">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 items-center">
                    {{-- خانەی نرخی ١٠٠ دۆلار لەگەڵ دوگمەی API --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1" for="exchange_rate">نرخی ١٠٠$ دۆلار بە دینار</label>
                        <div class="flex items-center gap-1.5 bg-white rounded-xl border border-slate-200 px-3 py-1.5 shadow-2xs focus-within:border-blue-500">
                            <input id="exchange_rate" name="exchange_rate" type="text"
                                   class="w-full text-xs font-mono font-bold text-slate-800 border-0 outline-none focus:ring-0 p-0"
                                   x-model="exchangeRate" placeholder="150,000">
                            <span class="text-2xs text-slate-400 font-bold shrink-0">د.ع</span>
                            <button type="button" @click="fetchLiveRate()"
                                    :disabled="fetchingRate"
                                    class="text-slate-400 hover:text-blue-600 p-1 rounded-lg hover:bg-slate-100 transition-all shrink-0 cursor-pointer"
                                    title="وەرگرتنی نرخی ئەمڕۆ لە ئینتەرنێت (Live API)">
                                <svg class="size-4" :class="fetchingRate ? 'animate-spin text-blue-600' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- کۆی گشتی بە دینار --}}
                    <div class="bg-blue-50/80 rounded-xl p-3 border border-blue-100 flex flex-col justify-center">
                        <div class="text-[11px] font-bold text-blue-900">کۆی گشتی بە دینار:</div>
                        <div class="text-base font-black text-blue-700 num mt-0.5" x-text="amountIqd ? amountIqd.toLocaleString('en-US') + ' د.ع' : '0 د.ع'"></div>
                    </div>
                </div>
            </div>

            {{-- بەشی ٤: بەروار و تێبینی --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5" for="paid_at">
                        <span>بەروار</span>
                        <span class="text-rose-500">*</span>
                    </label>
                    <input id="paid_at" name="paid_at" type="date" class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 focus:border-blue-500 bg-slate-50/40 num" required
                           value="{{ old('paid_at', now()->toDateString()) }}">
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 mb-1.5" for="note">تێبینی</label>
                    <input id="note" name="note" class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 focus:border-blue-500 bg-slate-50/40" value="{{ old('note') }}" placeholder="پێشەکی، قیستی مانگانە، وەرگرتنی کاش...">
                </div>
            </div>

        </div>

        {{-- بەشی خوارەوە: دوگمەکان --}}
        <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50 flex items-center justify-end gap-2.5">
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
