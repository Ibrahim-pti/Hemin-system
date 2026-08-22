@extends('layouts.app')
@section('title', $order->exists ? 'دەستکاری وەسڵ ' . $order->invoice_no : 'وەسڵی نوێ')

@section('content')

@php
    $initialLines = old('lines', $order->exists
        ? $order->items->map(fn ($l) => [
            'description' => $l->description,
            'image' => $l->image,
            'preview' => $l->imageUrl(),
            'item_id' => (string) ($l->item_id ?? ''),
            'pricing_mode' => $l->pricing_mode,
            'width' => $l->width !== null ? (string) (float) $l->width : '',
            'height' => $l->height !== null ? (string) (float) $l->height : '',
            'qty' => (string) (float) $l->qty,
            'unit_price' => (string) (float) $l->unit_price,
            'note' => $l->note ?? '',
          ])->all()
        : [['description' => '', 'image' => '', 'preview' => null, 'item_id' => '', 'pricing_mode' => 'area', 'width' => '', 'height' => '', 'qty' => '1', 'unit_price' => '', 'note' => '']]);
@endphp

<form method="POST"
      action="{{ $order->exists ? route('orders.update', $order) : route('orders.store') }}"
      enctype="multipart/form-data"
      x-data="orderForm(
          @js($initialLines),
          @js((float) old('discount_percent', $order->discount_percent ?: 0)),
          @js(old('currency', $order->currency ?: 'IQD')),
          @js(collect($customers)->mapWithKeys(fn ($c) => [$c->id => (float) $c->discount_percent])->all())
      )">
    @csrf
    @if ($order->exists) @method('PUT') @endif

    @if ($errors->any())
        <div class="card mb-4 border-r-4 !border-r-[--color-danger] px-4 py-3 text-sm">
            <ul class="list-inside list-disc space-y-1">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    {{-- سەرەوەی وەسڵ --}}
    <div class="card">
        <div class="card-head">زانیاری وەسڵ</div>
        <div class="card-body grid gap-4 sm:grid-cols-2 lg:grid-cols-4">

            <div>
                <label class="label" for="invoice_no">ژمارەی وەسڵ</label>
                <input id="invoice_no" name="invoice_no" class="field num" dir="ltr"
                       value="{{ old('invoice_no', $order->invoice_no ?: $nextNo) }}">
                <p class="mt-1 text-xs text-[--color-ink-soft]">وەک ژمارەی دەفتەری چاپکراو.</p>
            </div>

            <div class="sm:col-span-1 lg:col-span-2">
                <label class="label" for="customer_id">بەڕێز (کڕیار) <span class="text-[--color-danger]">*</span></label>
                <select id="customer_id" name="customer_id" class="field" required
                        x-model="customerId" @change="applyCustomerDiscount()">
                    <option value="">— هەڵبژێرە —</option>
                    @foreach ($customers as $customer)
                        <option value="{{ $customer->id }}" @selected(old('customer_id', $order->customer_id) == $customer->id)>
                            {{ $customer->name }}{{ $customer->phone ? ' — '.$customer->phone : '' }}
                        </option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-[--color-ink-soft]">
                    <a href="{{ route('customers.create') }}" class="text-[--color-brand-700]">کڕیاری نوێ زیاد بکە</a>
                </p>
            </div>

            <div>
                <label class="label" for="order_date">بەروار <span class="text-[--color-danger]">*</span></label>
                <input id="order_date" name="order_date" type="date" class="field num" required
                       value="{{ old('order_date', $order->order_date?->toDateString() ?? now()->toDateString()) }}">
            </div>

            <div>
                <label class="label" for="delivery_date">بەرواری گەیاندن</label>
                <input id="delivery_date" name="delivery_date" type="date" class="field num"
                       value="{{ old('delivery_date', $order->delivery_date?->toDateString()) }}">
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
                       value="{{ old('exchange_rate', $order->exchange_rate ?: $rate) }}">
            </div>

            <div class="sm:col-span-2 lg:col-span-1">
                <label class="label" for="note">تێبینی</label>
                <input id="note" name="note" class="field" value="{{ old('note', $order->note) }}">
            </div>
        </div>
    </div>

    {{-- دێڕەکان + قیاس --}}
    <div class="card mt-4">
        <div class="card-head flex items-center justify-between">
            <span>ناوەڕۆک و قیاس</span>
            <button type="button" @click="addLine()" class="btn btn-ghost !py-1">+ دێڕ</button>
        </div>

        <div class="overflow-x-auto">
            <table class="table" style="min-width: 950px">
                <thead>
                    <tr>
                        <th class="w-14 text-center">وێنە</th>
                        <th class="w-[22%]">ناوەڕۆک</th>
                        <th class="w-32">شێواز</th>
                        <th class="num w-24">پانی (م)</th>
                        <th class="num w-24">بەرزی (م)</th>
                        <th class="num w-20">ژمارە</th>
                        <th class="num w-24">بڕ</th>
                        <th class="num w-28">نرخ</th>
                        <th class="num w-32">بڕی پارە</th>
                        <th class="w-10"></th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="(line, index) in lines" :key="index">
                        <tr>
                            {{-- وێنەی کاڵا / دیزاین --}}
                            <td class="text-center !py-1">
                                <div class="flex items-center justify-center">
                                    <input type="file"
                                           :name="`lines[${index}][image]`"
                                           :id="`order_line_image_${index}`"
                                           accept="image/*"
                                           class="hidden"
                                           @change="onImageChange($event, line)">
                                    <input type="hidden" :name="`lines[${index}][existing_image]`" :value="line.image || ''">

                                    <template x-if="line.preview">
                                        <div class="relative group size-8 rounded-lg overflow-hidden border border-blue-400 shadow-2xs shrink-0">
                                            <img :src="line.preview" class="size-full object-cover cursor-pointer"
                                                 @click="document.getElementById(`order_line_image_${index}`).click()"
                                                 title="گۆڕینی وێنە">
                                            <button type="button" @click="removeImage(line, index)"
                                                    class="absolute -top-1 -right-1 bg-rose-600 text-white rounded-full size-3.5 flex items-center justify-center text-[9px] shadow"
                                                    title="لابردنی وێنە">×</button>
                                        </div>
                                    </template>

                                    <template x-if="!line.preview">
                                        <button type="button"
                                                @click="document.getElementById(`order_line_image_${index}`).click()"
                                                class="size-8 rounded-lg border border-dashed border-slate-300 hover:border-blue-500 bg-slate-50 hover:bg-blue-50 text-slate-400 hover:text-blue-600 flex items-center justify-center transition-all shrink-0"
                                                title="دانانی وێنەی دیزاین">
                                            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                                                <circle cx="8.5" cy="8.5" r="1.5"/>
                                                <polyline points="21 15 16 10 5 21"/>
                                            </svg>
                                        </button>
                                    </template>
                                </div>
                            </td>

                            <td>
                                <input :name="`lines[${index}][description]`" x-model="line.description" required
                                       class="field !py-1" placeholder="دەرگای ئاسنی هەندەسی">
                                <input type="hidden" :name="`lines[${index}][item_id]`" :value="line.item_id">
                            </td>
                            <td>
                                <select :name="`lines[${index}][pricing_mode]`" x-model="line.pricing_mode" class="field !py-1">
                                    <option value="area">مەتر دووجا</option>
                                    <option value="length">مەتر</option>
                                    <option value="count">دانە</option>
                                </select>
                            </td>
                            <td>
                                <input type="number" step="0.001" min="0" :name="`lines[${index}][width]`"
                                       x-model="line.width" class="field num !py-1"
                                       :disabled="line.pricing_mode === 'count'"
                                       :class="line.pricing_mode === 'count' && 'opacity-40'">
                            </td>
                            <td>
                                <input type="number" step="0.001" min="0" :name="`lines[${index}][height]`"
                                       x-model="line.height" class="field num !py-1"
                                       :disabled="line.pricing_mode !== 'area'"
                                       :class="line.pricing_mode !== 'area' && 'opacity-40'">
                            </td>
                            <td>
                                <input type="number" step="0.001" min="0.001" required :name="`lines[${index}][qty]`"
                                       x-model="line.qty" class="field num !py-1">
                            </td>
                            <td class="num text-[--color-ink-soft]">
                                <span x-text="computed(line).toFixed(3).replace(/\.?0+$/, '')"></span>
                                <span class="text-xs" x-text="unitOf(line)"></span>
                            </td>
                            <td>
                                <input type="number" step="0.01" min="0" required :name="`lines[${index}][unit_price]`"
                                       x-model="line.unit_price" class="field num !py-1">
                            </td>
                            <td class="num font-medium" x-text="money(lineTotal(line))"></td>
                            <td>
                                <button type="button" @click="removeLine(index)" class="text-[--color-danger]"
                                        x-show="lines.length > 1">✕</button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <div class="border-t border-[--color-line] px-4 py-2 text-xs text-[--color-ink-soft]">
            مەتر دووجا = پانی × بەرزی × ژمارە &nbsp;·&nbsp; مەتر = پانی × ژمارە &nbsp;·&nbsp; دانە = ژمارە
        </div>
    </div>

    {{-- کۆکان --}}
    <div class="mt-4 grid gap-4 lg:grid-cols-3">
        <div class="card lg:col-span-2">
            <div class="card-body grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="label" for="discount_percent">داشکاندن (٪)</label>
                    <input id="discount_percent" name="discount_percent" type="number" step="0.01" min="0" max="100"
                           class="field num" x-model.number="discountPercent">
                    <p class="mt-1 text-xs text-[--color-ink-soft]">
                        داشکاندنی هەمیشەیی کڕیار خۆکار دادەنرێت.
                    </p>
                </div>
                <div>
                    <label class="label" for="prepaid_amount">پێشەکی</label>
                    <input id="prepaid_amount" name="prepaid_amount" type="number" step="0.01" min="0" class="field num"
                           value="{{ old('prepaid_amount', 0) }}" x-model.number="prepaid">
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
                    <span class="num" x-text="money(discountAmount)"></span>
                </div>
                <div class="flex justify-between border-t border-[--color-line] pt-2 text-base font-semibold">
                    <span>کۆی گشتی</span>
                    <span class="num" x-text="money(total)"></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-[--color-ink-soft]">پێشەکی</span>
                    <span class="num" x-text="money(prepaid)"></span>
                </div>
                <div class="flex justify-between font-semibold">
                    <span>ماوە</span>
                    <span class="num" :class="remaining > 0 && 'text-[--color-danger]'" x-text="money(remaining)"></span>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4 flex flex-wrap items-center gap-3">
        <button class="btn btn-primary">{{ $order->exists ? 'نوێکردنەوە' : 'تۆمارکردن' }}</button>

        @unless ($order->exists)
            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" name="confirm" value="1" checked class="size-4 rounded border-[--color-line-strong]">
                پەسەندکردن
            </label>
        @endunless

        <a href="{{ route('orders.index') }}" class="btn btn-ghost">پاشگەزبوونەوە</a>
    </div>
</form>

@push('scripts')
<script>
function orderForm(initialLines, initialDiscount, initialCurrency, customerDiscounts) {
    return {
        lines: initialLines,
        discountPercent: initialDiscount,
        currency: initialCurrency,
        customerId: '{{ old('customer_id', $order->customer_id) }}',
        customerDiscounts: customerDiscounts,
        prepaid: {{ (float) old('prepaid_amount', 0) }},

        addLine() {
            // شێوازی دێڕی نوێ لە دوایین دێڕەوە وەردەگیرێت — خێراتر بۆ نووسین.
            const last = this.lines[this.lines.length - 1];
            this.lines.push({
                description: '', image: '', preview: null, item_id: '',
                pricing_mode: last ? last.pricing_mode : 'area',
                width: '', height: '', qty: '1', unit_price: '', note: '',
            });
        },

        onImageChange(e, line) {
            const file = e.target.files[0];
            if (file) {
                line.preview = URL.createObjectURL(file);
            } else {
                line.preview = null;
            }
        },

        removeImage(line, index) {
            line.preview = null;
            line.image = '';
            const input = document.getElementById('order_line_image_' + index);
            if (input) input.value = '';
        },

        removeLine(index) {
            this.lines.splice(index, 1);
        },

        applyCustomerDiscount() {
            const discount = this.customerDiscounts[this.customerId];
            if (discount) this.discountPercent = discount;
        },

        computed(line) {
            const width = parseFloat(line.width) || 0;
            const height = parseFloat(line.height) || 0;
            const qty = parseFloat(line.qty) || 0;

            if (line.pricing_mode === 'area') return width * height * qty;
            if (line.pricing_mode === 'length') return width * qty;
            return qty;
        },

        unitOf(line) {
            return { area: 'م²', length: 'م', count: 'دانە' }[line.pricing_mode] || '';
        },

        lineTotal(line) {
            return this.computed(line) * (parseFloat(line.unit_price) || 0);
        },

        get subtotal() {
            return this.lines.reduce((sum, line) => sum + this.lineTotal(line), 0);
        },

        get discountAmount() {
            return this.subtotal * (parseFloat(this.discountPercent) || 0) / 100;
        },

        get total() {
            return Math.max(0, this.subtotal - this.discountAmount);
        },

        get remaining() {
            return this.total - (parseFloat(this.prepaid) || 0);
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
