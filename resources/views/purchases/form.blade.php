@extends('layouts.app')
@section('title', $purchase->exists ? 'دەستکاری پسوولەی کڕین' : 'پسوولەی کڕینی نوێ')

@section('content')

@php
    // دێڕەکان بۆ Alpine — یان لە پسوولەی هەبوو، یان دێڕێکی بەتاڵ.
    $initialLines = old('lines', $purchase->exists
        ? $purchase->items->map(fn ($l) => [
            'item_id' => (string) $l->item_id,
            'qty' => (string) (float) $l->qty,
            'unit_price' => (string) (float) $l->unit_price,
            'note' => $l->note ?? '',
          ])->all()
        : [['item_id' => '', 'qty' => '1', 'unit_price' => '', 'note' => '']]);
@endphp

<form method="POST"
      action="{{ $purchase->exists ? route('purchases.update', $purchase) : route('purchases.store') }}"
      x-data="purchaseForm(@js($initialLines), @js((float) old('discount_amount', $purchase->discount_amount ?: 0)), @js(old('currency', $purchase->currency ?: 'IQD')))">
    @csrf
    @if ($purchase->exists) @method('PUT') @endif

    @if ($errors->any())
        <div class="card mb-4 border-r-4 !border-r-[--color-danger] px-4 py-3 text-sm">
            <ul class="list-inside list-disc space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- سەرەوەی پسوولە --}}
    <div class="card">
        <div class="card-head">زانیاری پسوولە</div>
        <div class="card-body grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="sm:col-span-2">
                <label class="label" for="supplier_id">فرۆشیار <span class="text-[--color-danger]">*</span></label>
                <select id="supplier_id" name="supplier_id" class="field" required>
                    <option value="">— هەڵبژێرە —</option>
                    @foreach ($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" @selected(old('supplier_id', $purchase->supplier_id) == $supplier->id)>
                            {{ $supplier->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="label" for="warehouse_id">کۆگا <span class="text-[--color-danger]">*</span></label>
                <select id="warehouse_id" name="warehouse_id" class="field" required>
                    @foreach ($warehouses as $warehouse)
                        <option value="{{ $warehouse->id }}" @selected(old('warehouse_id', $purchase->warehouse_id) == $warehouse->id)>
                            {{ $warehouse->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="label" for="purchase_date">بەروار <span class="text-[--color-danger]">*</span></label>
                <input id="purchase_date" name="purchase_date" type="date" class="field num" required
                       value="{{ old('purchase_date', $purchase->purchase_date?->toDateString() ?? now()->toDateString()) }}">
            </div>

            <div>
                <label class="label" for="currency">دراو</label>
                <select id="currency" name="currency" class="field" x-model="currency">
                    <option value="IQD">دینار</option>
                    <option value="USD">دۆلار</option>
                </select>
            </div>

            <div x-show="currency === 'USD'" x-cloak>
                <label class="label" for="exchange_rate">نرخی دۆلار</label>
                <input id="exchange_rate" name="exchange_rate" type="number" step="0.01" class="field num"
                       value="{{ old('exchange_rate', $purchase->exchange_rate ?: $rate) }}">
            </div>

            <div class="sm:col-span-2 lg:col-span-2">
                <label class="label" for="note">تێبینی</label>
                <input id="note" name="note" class="field" value="{{ old('note', $purchase->note) }}">
            </div>
        </div>
    </div>

    {{-- دێڕەکان --}}
    <div class="card mt-4">
        <div class="card-head flex items-center justify-between">
            <span>کاڵاکان</span>
            <button type="button" @click="addLine()" class="btn btn-ghost !py-1">+ دێڕ</button>
        </div>

        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th class="w-[35%]">کاڵا</th>
                        <th class="num w-24">بڕ</th>
                        <th class="num w-32">نرخی یەکە</th>
                        <th class="num w-32">کۆ</th>
                        <th>تێبینی</th>
                        <th class="w-10"></th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="(line, index) in lines" :key="index">
                        <tr>
                            <td>
                                <select :name="`lines[${index}][item_id]`" x-model="line.item_id"
                                        @change="fillPrice(line)" class="field !py-1" required>
                                    <option value="">— هەڵبژێرە —</option>
                                    @foreach ($items as $item)
                                        <option value="{{ $item->id }}" data-price="{{ $item->last_cost }}">
                                            {{ $item->name }} ({{ $item->unit?->name }})
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <input type="number" step="0.001" min="0.001" required
                                       :name="`lines[${index}][qty]`" x-model="line.qty" class="field num !py-1">
                            </td>
                            <td>
                                <input type="number" step="0.01" min="0" required
                                       :name="`lines[${index}][unit_price]`" x-model="line.unit_price" class="field num !py-1">
                            </td>
                            <td class="num font-medium" x-text="money(lineTotal(line))"></td>
                            <td>
                                <input :name="`lines[${index}][note]`" x-model="line.note" class="field !py-1">
                            </td>
                            <td>
                                <button type="button" @click="removeLine(index)"
                                        class="text-[--color-danger]" x-show="lines.length > 1">✕</button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    {{-- کۆکان --}}
    <div class="mt-4 grid gap-4 lg:grid-cols-3">
        <div class="card lg:col-span-2">
            <div class="card-body grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="label" for="discount_amount">داشکاندن</label>
                    <input id="discount_amount" name="discount_amount" type="number" step="0.01" min="0"
                           class="field num" x-model.number="discount">
                </div>
                <div>
                    <label class="label" for="paid_amount">پارەی دراو ئێستا</label>
                    <input id="paid_amount" name="paid_amount" type="number" step="0.01" min="0" class="field num"
                           value="{{ old('paid_amount', 0) }}">
                    <p class="mt-1 text-xs text-[--color-ink-soft]">حەقدییەکی خۆکاری بۆ دروست دەبێت.</p>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-[--color-ink-soft]">کۆی دێڕەکان</span>
                    <span class="num" x-text="money(subtotal)"></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-[--color-ink-soft]">داشکاندن</span>
                    <span class="num" x-text="money(discount)"></span>
                </div>
                <div class="flex justify-between border-t border-[--color-line] pt-2 text-base font-semibold">
                    <span>کۆی گشتی</span>
                    <span class="num" x-text="money(total)"></span>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4 flex flex-wrap items-center gap-3">
        <button class="btn btn-primary">{{ $purchase->exists ? 'نوێکردنەوە' : 'تۆمارکردن' }}</button>

        <label class="flex items-center gap-2 text-sm">
            <input type="checkbox" name="confirm" value="1" class="size-4 rounded border-[--color-line-strong]">
            پەسەندکردن و ناردنی کاڵاکان بۆ مەخزەن
        </label>

        <a href="{{ route('purchases.index') }}" class="btn btn-ghost">پاشگەزبوونەوە</a>
    </div>
</form>

@push('scripts')
<script>
function purchaseForm(initialLines, initialDiscount, initialCurrency) {
    return {
        lines: initialLines,
        discount: initialDiscount,
        currency: initialCurrency,

        addLine() {
            this.lines.push({ item_id: '', qty: '1', unit_price: '', note: '' });
        },

        removeLine(index) {
            this.lines.splice(index, 1);
        },

        // نرخی دوایین کڕین خۆکار پڕ دەکرێتەوە — دەکرێت بگۆڕدرێت.
        fillPrice(line) {
            const option = this.$el.querySelector(`option[value="${line.item_id}"]`);
            if (option && option.dataset.price && !line.unit_price) {
                line.unit_price = option.dataset.price;
            }
        },

        lineTotal(line) {
            return (parseFloat(line.qty) || 0) * (parseFloat(line.unit_price) || 0);
        },

        get subtotal() {
            return this.lines.reduce((sum, line) => sum + this.lineTotal(line), 0);
        },

        get total() {
            return Math.max(0, this.subtotal - (parseFloat(this.discount) || 0));
        },

        money(value) {
            const decimals = this.currency === 'USD' ? 2 : 0;
            return (value || 0).toLocaleString('en-US', {
                minimumFractionDigits: decimals,
                maximumFractionDigits: decimals,
            }) + (this.currency === 'USD' ? ' $' : ' د.ع');
        },
    }
}
</script>
@endpush

@endsection
