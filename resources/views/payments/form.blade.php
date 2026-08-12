@extends('layouts.app')
@section('title', $direction === 'in' ? 'حەقدی — وەرگرتنی پارە' : 'حەقدی — دانی پارە')

@section('content')

<form method="POST" action="{{ route('payments.store') }}" class="mx-auto max-w-2xl"
      x-data="{
          kind: '{{ old('party_kind', $direction === 'in' ? 'customer' : 'supplier') }}',
          currency: '{{ old('currency', 'IQD') }}',
          amount: {{ (float) old('amount', 0) }},
          rate: {{ $rate }},
          get amountIqd() { return this.currency === 'USD' ? this.amount * this.rate : this.amount; }
      }">
    @csrf
    <input type="hidden" name="direction" value="{{ $direction }}">
    @if ($selected['order']) <input type="hidden" name="order_id" value="{{ $selected['order'] }}"> @endif
    @if ($selected['purchase']) <input type="hidden" name="purchase_id" value="{{ $selected['purchase'] }}"> @endif

    @if ($errors->any())
        <div class="card mb-4 border-r-4 !border-r-[--color-danger] px-4 py-3 text-sm">
            <ul class="list-inside list-disc">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    {{-- پەیوەندی بە بەڵگەنامەوە --}}
    @if ($order)
        <div class="card mb-4">
            <div class="card-body flex flex-wrap items-center justify-between gap-3 text-sm">
                <div>
                    <span class="text-[--color-ink-soft]">لەسەر حسابی وەسڵی ژمارە</span>
                    <span class="num font-semibold">{{ $order->invoice_no }}</span>
                </div>
                <div>
                    <span class="text-[--color-ink-soft]">ماوە:</span>
                    <span class="num font-semibold text-[--color-danger]">{{ fmt_money($order->remaining()) }}</span>
                </div>
            </div>
        </div>
    @elseif ($purchase)
        <div class="card mb-4">
            <div class="card-body flex flex-wrap items-center justify-between gap-3 text-sm">
                <div>
                    <span class="text-[--color-ink-soft]">لەسەر حسابی پسوولەی کڕینی</span>
                    <span class="num font-semibold">{{ $purchase->invoice_no }}</span>
                </div>
                <div>
                    <span class="text-[--color-ink-soft]">ماوە:</span>
                    <span class="num font-semibold text-[--color-danger]">{{ fmt_money($purchase->remaining()) }}</span>
                </div>
            </div>
        </div>
    @endif

    <div class="card">
        <div class="card-head">
            {{ $direction === 'in' ? 'پارە لە کێ وەردەگیرێت؟' : 'پارە بە کێ دەدرێت؟' }}
        </div>
        <div class="card-body grid gap-4 sm:grid-cols-2">

            {{-- جۆری لایەن --}}
            <div class="sm:col-span-2">
                <label class="label">جۆر</label>
                <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
                    @foreach (['customer' => 'کڕیار', 'supplier' => 'فرۆشیار', 'employee' => 'کارمەند', 'other' => 'هیتر'] as $value => $label)
                        <label class="cursor-pointer rounded-md border px-3 py-2 text-center text-sm"
                               :class="kind === '{{ $value }}'
                                   ? 'border-[--color-brand-700] bg-[--color-brand-700] text-white'
                                   : 'border-[--color-line-strong] bg-white hover:bg-[--color-canvas]'">
                            <input type="radio" name="party_kind" value="{{ $value }}" x-model="kind" class="sr-only">
                            {{ $label }}
                        </label>
                    @endforeach
                </div>
            </div>

            {{-- لایەن --}}
            <div class="sm:col-span-2" x-show="kind !== 'other'">
                <label class="label" for="party_id">ناو</label>
                {{-- ناوەکان لە JSـەوە پڕ دەکرێنەوە بەپێی جۆری هەڵبژێردراو --}}
                <select id="party_id" name="party_id" class="field">
                    <option value="">— هەڵبژێرە —</option>
                </select>
            </div>

            <div class="sm:col-span-2" x-show="kind === 'other'" x-cloak>
                <label class="label" for="party_name">ناو</label>
                <input id="party_name" name="party_name" class="field" value="{{ old('party_name') }}">
            </div>

            <div>
                <label class="label" for="amount">بڕی پارە <span class="text-[--color-danger]">*</span></label>
                <input id="amount" name="amount" type="number" step="0.01" min="0.01" required
                       class="field num" x-model.number="amount" value="{{ old('amount') }}">
            </div>

            <div>
                <label class="label" for="currency">دراو</label>
                <select id="currency" name="currency" class="field" x-model="currency">
                    <option value="IQD">دینار</option>
                    <option value="USD">دۆلار</option>
                </select>
            </div>

            <div x-show="currency === 'USD'" x-cloak class="sm:col-span-2">
                <div class="rounded-md border border-[--color-line] bg-[--color-canvas] px-3 py-2 text-sm">
                    بە نرخی <span class="num" x-text="rate.toLocaleString()"></span> =
                    <span class="num font-semibold" x-text="amountIqd.toLocaleString() + ' د.ع'"></span>
                </div>
            </div>

            <div>
                <label class="label" for="cash_box_id">قاسە</label>
                <select id="cash_box_id" name="cash_box_id" class="field">
                    <option value="">— خۆکار بەپێی دراو —</option>
                    @foreach ($cashBoxes as $box)
                        <option value="{{ $box->id }}">{{ $box->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="label" for="paid_at">بەروار <span class="text-[--color-danger]">*</span></label>
                <input id="paid_at" name="paid_at" type="date" class="field num" required
                       value="{{ old('paid_at', now()->toDateString()) }}">
            </div>

            <div class="sm:col-span-2">
                <label class="label" for="note">تێبینی</label>
                <input id="note" name="note" class="field" value="{{ old('note') }}">
            </div>
        </div>
    </div>

    <div class="mt-4 flex gap-2">
        <button class="btn btn-primary">تۆمارکردن و چاپ</button>
        <a href="{{ route('payments.index') }}" class="btn btn-ghost">پاشگەزبوونەوە</a>
    </div>
</form>

@push('scripts')
<script>
// لیستی ناوەکان بەپێی جۆری لایەن دەگۆڕدرێت.
document.addEventListener('DOMContentLoaded', () => {
    const groups = {
        customer: @js($customers->map(fn ($c) => ['id' => $c->id, 'name' => $c->name.($c->phone ? ' — '.$c->phone : '')])),
        supplier: @js($suppliers->map(fn ($s) => ['id' => $s->id, 'name' => $s->name])),
        employee: @js($employees->map(fn ($e) => ['id' => $e->id, 'name' => $e->name])),
    };

    const select = document.getElementById('party_id');
    const preselect = {
        customer: @js($selected['customer']),
        supplier: @js($selected['supplier']),
    };

    const render = (kind) => {
        if (! groups[kind]) return;

        select.innerHTML = '<option value="">— هەڵبژێرە —</option>';
        groups[kind].forEach((row) => {
            const option = new Option(row.name, row.id);
            if (preselect[kind] == row.id) option.selected = true;
            select.add(option);
        });
    };

    // گوێگرتن لە گۆڕینی جۆر.
    document.querySelectorAll('input[name="party_kind"]').forEach((input) => {
        input.addEventListener('change', () => render(input.value));
    });

    render('{{ old('party_kind', $direction === 'in' ? 'customer' : 'supplier') }}');
});
</script>
@endpush

@endsection
