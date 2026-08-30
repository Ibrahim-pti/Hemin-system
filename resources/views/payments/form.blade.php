@extends('layouts.app')
@section('title', $direction === 'in' ? 'وەرگرتنی پارە (حەقدی)' : 'دانی پارە')

@section('actions')
    <a href="{{ route('payments.index') }}" class="btn btn-ghost !py-1.5 !px-3 text-xs gap-1 border border-slate-200 hover:bg-slate-100 font-bold text-slate-700">
        <span>&larr;</span>
        <span>گەڕانەوە بۆ لیست</span>
    </a>
@endsection

@section('content')

<div style="width: 100%; display: flex; flex-direction: column; gap: 1rem; align-items: center;">

    @if ($errors->any())
        <div style="width: 100%; max-width: 32rem; background: #fff1f2; border: 1.5px solid #fecdd3; color: #9f1239; border-radius: 1rem; padding: 0.85rem 1.25rem; font-size: 0.8rem; font-weight: 700;">
            <ul style="margin: 0; padding-right: 1.2rem; list-style-type: disc;">
                @foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- پێشاندان ئەگەر بەستراوە بە وەسڵ یان پسوولەوە --}}
    @if ($order)
        <div style="width: 100%; max-width: 32rem; background: #f0f9ff; border: 1.5px solid #7dd3fc; border-radius: 1rem; padding: 0.85rem 1.25rem; display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 0.6rem;">
                <span style="font-size: 1.25rem;">📄</span>
                <div>
                    <div style="font-size: 0.75rem; font-weight: 800; color: #0369a1;">حەقدی لەسەر وەسڵی فرۆشتن</div>
                    <div style="font-size: 0.85rem; font-family: monospace; font-weight: 900; color: #0284c7;">#{{ $order->invoice_no }} ({{ $order->customer?->name }})</div>
                </div>
            </div>
            <div style="text-align: left;">
                <div style="font-size: 0.7rem; font-weight: 700; color: #64748b;">ماوە:</div>
                <div class="num" style="font-size: 0.95rem; font-weight: 900; color: #e11d48;">{{ fmt_money($order->remaining(), $order->currency) }}</div>
            </div>
        </div>
    @elseif ($purchase)
        <div style="width: 100%; max-width: 32rem; background: #faf5ff; border: 1.5px solid #d8b4fe; border-radius: 1rem; padding: 0.85rem 1.25rem; display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 0.6rem;">
                <span style="font-size: 1.25rem;">🛒</span>
                <div>
                    <div style="font-size: 0.75rem; font-weight: 800; color: #6b21a8;">دانی پارە لەسەر پسوولەی کڕین</div>
                    <div style="font-size: 0.85rem; font-family: monospace; font-weight: 900; color: #7e22ce;">#{{ $purchase->invoice_no }} ({{ $purchase->supplier?->name }})</div>
                </div>
            </div>
            <div style="text-align: left;">
                <div style="font-size: 0.7rem; font-weight: 700; color: #64748b;">ماوە:</div>
                <div class="num" style="font-size: 0.95rem; font-weight: 900; color: #e11d48;">{{ fmt_money($purchase->remaining(), $purchase->currency) }}</div>
            </div>
        </div>
    @endif

    {{-- کارتی سەرەکی هاوشێوەی کارتی مۆداڵی قاسە --}}
    <div style="background: #ffffff; border-radius: 1.25rem; width: 100%; max-width: 32rem; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.08), 0 8px 10px -6px rgba(0, 0, 0, 0.04); border: 1px solid #e2e8f0; overflow: hidden;">

        {{-- سەری کارت بە سەوز بۆ وەرگرتن یان سوور بۆ دان (وەک قاسە) --}}
        <div style="padding: 1.1rem 1.5rem; background: {{ $direction === 'in' ? '#10b981' : '#e11d48' }}; color: #ffffff; display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 0.5rem; font-weight: 800; font-size: 1.05rem;">
                <span>{{ $direction === 'in' ? '📥 وەرگرتنی پارە (حەقدی)' : '📤 دانی پارە (خەرجی)' }}</span>
            </div>
            <span style="font-size: 0.75rem; font-weight: 700; background: rgba(255,255,255,0.2); padding: 0.2rem 0.6rem; border-radius: 0.5rem;">
                {{ $direction === 'in' ? 'داهات' : 'خەرجی' }}
            </span>
        </div>

        <form method="POST" action="{{ route('payments.store') }}"
              style="padding: 1.5rem; display: flex; flex-direction: column; gap: 1.15rem;"
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
              }">
            @csrf
            <input type="hidden" name="direction" value="{{ $direction }}">

            {{-- ١. لایەنی مامەڵە --}}
            <div>
                <label style="font-size: 0.825rem; font-weight: 700; color: #334155; margin-bottom: 0.4rem; display: block;">لایەنی مامەڵە</label>
                <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 0.4rem;">
                    @php
                        $partyOptions = [
                            'customer' => ['label' => 'کڕیار', 'icon' => '👤'],
                            'supplier' => ['label' => 'فرۆشیار', 'icon' => '🏢'],
                            'employee' => ['label' => 'کارمەند', 'icon' => '👷'],
                            'other' => ['label' => 'هیتر', 'icon' => '🏷️'],
                        ];
                    @endphp
                    @foreach ($partyOptions as $val => $info)
                        <label style="cursor: pointer; border-radius: 0.65rem; border: 1.5px solid; padding: 0.5rem 0.25rem; text-align: center; font-size: 0.75rem; font-weight: 700; display: flex; flex-direction: column; align-items: center; gap: 0.2rem; transition: all 0.15s ease;"
                               :style="kind === '{{ $val }}'
                                   ? 'border-color: #2563eb; background: #eff6ff; color: #1d4ed8;'
                                   : 'border-color: #e2e8f0; background: #f8fafc; color: #64748b;'">
                            <input type="radio" name="party_kind" value="{{ $val }}" x-model="kind" class="sr-only">
                            <span style="font-size: 1rem;">{{ $info['icon'] }}</span>
                            <span>{{ $info['label'] }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            {{-- ٢. ناوی لایەن --}}
            <div x-show="kind !== 'other'">
                <label style="font-size: 0.825rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem; display: block;" for="party_id">
                    <span x-text="kind === 'customer' ? 'ناوی کڕیار' : (kind === 'supplier' ? 'ناوی فرۆشیار' : 'ناوی کارمەند')"></span>
                    <span style="color: #ef4444;">*</span>
                </label>
                <select id="party_id" name="party_id" class="field" style="width: 100%; font-weight: 600; padding: 0.55rem 0.75rem; border-radius: 0.6rem;"
                        x-model="selectedPartyId"
                        @change="handlePartyChange($event.target.value)">
                    <option value="">— ناو هەڵبژێرە —</option>
                </select>
            </div>

            {{-- دیاریکردنی وەسڵی فرۆشتن بۆ کڕیار --}}
            <div x-show="kind === 'customer'" x-cloak style="background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 0.75rem; padding: 0.75rem;">
                <label style="font-size: 0.775rem; font-weight: 700; color: #0369a1; margin-bottom: 0.25rem; display: block;" for="order_id">
                    <span>دیاریکردنی وەسڵی فرۆشتن</span>
                    <span style="font-size: 0.7rem; color: #64748b; font-weight: 500;">(ئارەزوومەندانە)</span>
                </label>
                <select id="order_id" name="order_id" class="field" style="width: 100%; font-size: 0.775rem; background: #ffffff; border-radius: 0.5rem; padding: 0.45rem;"
                        x-model="selectedOrderId">
                    <option value="">— تەواوی حسابی کڕیار (گشتی) —</option>
                    @foreach ($orders as $ord)
                        <option value="{{ $ord->id }}" data-customer="{{ $ord->customer_id }}" {{ $selected['order'] == $ord->id ? 'selected' : '' }}>
                            وەسڵی {{ $ord->invoice_no }} — بڕ: {{ fmt_money($ord->total, $ord->currency) }} ({{ fmt_date($ord->order_date) }})
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- دیاریکردنی پسوولەی کڕین بۆ فرۆشیار --}}
            <div x-show="kind === 'supplier'" x-cloak style="background: #faf5ff; border: 1px solid #e9d5ff; border-radius: 0.75rem; padding: 0.75rem;">
                <label style="font-size: 0.775rem; font-weight: 700; color: #6b21a8; margin-bottom: 0.25rem; display: block;" for="purchase_id">
                    <span>دیاریکردنی پسوولەی کڕین</span>
                    <span style="font-size: 0.7rem; color: #64748b; font-weight: 500;">(ئارەزوومەندانە)</span>
                </label>
                <select id="purchase_id" name="purchase_id" class="field" style="width: 100%; font-size: 0.775rem; background: #ffffff; border-radius: 0.5rem; padding: 0.45rem;"
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
                <label style="font-size: 0.825rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem; display: block;" for="party_name">ناوی لایەن / کەس</label>
                <input id="party_name" name="party_name" class="field" style="width: 100%;" value="{{ old('party_name') }}" placeholder="ناوی کەس یان لایەن...">
            </div>

            {{-- ٣. بڕی پارە و دراو بە شێوازی قەشەنگ وەک قاسە --}}
            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 0.6rem;">
                <div>
                    <label style="font-size: 0.825rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem; display: block;" for="amount">
                        <span>بڕی پارە</span>
                        <span style="color: #ef4444;">*</span>
                    </label>
                    <input id="amount" name="amount" type="number" step="any" min="0.01" required
                           class="field num"
                           style="width: 100%; padding: 0.65rem 1rem; font-size: 1.3rem; font-weight: 800; text-align: center; color: {{ $direction === 'in' ? '#15803d' : '#dc2626' }}; border-radius: 0.65rem;"
                           x-model="amount" placeholder="0">
                </div>

                <div>
                    <label style="font-size: 0.825rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem; display: block;" for="currency">دراو</label>
                    <select id="currency" name="currency" class="field" style="width: 100%; font-weight: 700; height: 3rem; border-radius: 0.65rem;" x-model="currency">
                        <option value="IQD">دینار (IQD)</option>
                        <option value="USD">دۆلار ($ USD)</option>
                    </select>
                </div>
            </div>

            {{-- گۆڕینەوەی دۆلار ئەگەر USD بێت --}}
            <div x-show="currency === 'USD'" x-cloak style="background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 0.85rem; padding: 0.85rem; display: flex; flex-direction: column; gap: 0.6rem;">
                <div style="display: flex; align-items: center; justify-content: space-between; gap: 0.5rem;">
                    <span style="font-size: 0.8rem; font-weight: 700; color: #334155;">نرخی ١٠٠$ دۆلار:</span>
                    <div style="display: flex; align-items: center; gap: 0.35rem; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 0.5rem; padding: 0.25rem 0.5rem;">
                        <input id="exchange_rate" name="exchange_rate" type="text"
                               style="width: 5.5rem; font-size: 0.85rem; font-family: monospace; font-weight: 800; color: #1e293b; border: none; outline: none; text-align: center; padding: 0;"
                               x-model="exchangeRate" placeholder="150,000">
                        <span style="font-size: 0.75rem; font-weight: 700; color: #64748b;">د.ع</span>
                        <button type="button" @click="fetchLiveRate()"
                                :disabled="fetchingRate"
                                style="background: none; border: none; cursor: pointer; color: #64748b; padding: 0.15rem; display: flex; align-items: center;"
                                title="وەرگرتنی نرخی ئەمڕۆ لە ئینتەرنێت (Live API)">
                            <svg style="width: 1rem; height: 1rem;" :class="fetchingRate ? 'animate-spin' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 0.6rem; padding: 0.5rem 0.75rem; display: flex; align-items: center; justify-content: space-between;">
                    <span style="font-size: 0.75rem; font-weight: 700; color: #166534;">کۆی گشتی بە دینار:</span>
                    <span class="num" style="font-size: 0.95rem; font-weight: 900; color: #15803d;" x-text="amountIqd ? amountIqd.toLocaleString('en-US') + ' د.ع' : '0 د.ع'"></span>
                </div>
            </div>

            {{-- ٤. بەروار --}}
            <div>
                <label style="font-size: 0.825rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem; display: block;" for="paid_at">
                    <span>بەروار</span>
                    <span style="color: #ef4444;">*</span>
                </label>
                <input id="paid_at" name="paid_at" type="date" class="field num" required
                       style="width: 100%; font-weight: 600; padding: 0.55rem 0.75rem; border-radius: 0.6rem;"
                       value="{{ old('paid_at', now()->toDateString()) }}">
            </div>

            {{-- ٥. تێبینی --}}
            <div>
                <label style="font-size: 0.825rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem; display: block;" for="note">تێبینی</label>
                <textarea id="note" name="note" rows="2" class="field"
                          placeholder="پێشەکی، قیست، وەرگرتنی کاش..."
                          style="width: 100%; font-size: 0.825rem; border-radius: 0.6rem;">{{ old('note') }}</textarea>
            </div>

            {{-- ٦. دوگمەکانی تۆمارکردن و داخستن وەک قاسە --}}
            <div style="display: flex; gap: 0.6rem; padding-top: 0.5rem;">
                <button type="submit"
                        style="background: {{ $direction === 'in' ? '#10b981' : '#e11d48' }}; color: #ffffff; padding: 0.65rem 1.5rem; border-radius: 0.55rem; font-weight: 800; font-size: 0.875rem; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 0.4rem; box-shadow: 0 2px 6px {{ $direction === 'in' ? 'rgba(16, 185, 129, 0.3)' : 'rgba(225, 29, 72, 0.3)' }};">
                    <span>✓</span>
                    <span>{{ $direction === 'in' ? 'تۆمارکردنی حەقدی و چاپ' : 'تۆمارکردنی پارەدان و چاپ' }}</span>
                </button>
                <a href="{{ route('payments.index') }}"
                   style="padding: 0.65rem 1.25rem; border-radius: 0.55rem; background: #ffffff; border: 1px solid #cbd5e1; color: #64748b; font-weight: 700; font-size: 0.875rem; text-decoration: none; display: inline-flex; align-items: center; justify-content: center;">
                    پاشگەزبوونەوە
                </a>
            </div>
        </form>
    </div>

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
