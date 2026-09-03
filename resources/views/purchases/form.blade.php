@extends('layouts.app')
@section('title', $purchase->exists ? 'دەستکاری پسوولەی کڕین' : 'پسوولەی کڕینی نوێ')

@section('content')

@php
    $workshopWarehouse = \App\Models\Warehouse::where('name', 'like', '%دروستکردن%')->first()
        ?? \App\Models\Warehouse::where('is_default', false)->first()
        ?? \App\Models\Warehouse::first();

    $initialLines = old('lines', $purchase->exists
        ? $purchase->items->map(fn ($l) => [
            'item_name' => (string) ($l->item?->name ?: $l->item_id),
            'qty' => (string) (float) $l->qty,
            'unit_price' => $l->unit_price !== null ? number_format((float)$l->unit_price) : '',
            'note' => $l->note ?? '',
          ])->all()
        : [['item_name' => '', 'qty' => '', 'unit_price' => '', 'note' => '']]);

    $initialDiscount = old('discount_amount', $purchase->discount_amount ? number_format((float)$purchase->discount_amount) : '');
    $initialPaid = old('paid_amount', $purchase->paid_amount ? number_format((float)$purchase->paid_amount) : '');

    if ($purchase->exists) {
        if ((float) $purchase->total > 0 && (float) $purchase->paid_amount >= (float) $purchase->total) {
            $initialPaymentType = 'cash';
        } elseif ((float) $purchase->paid_amount <= 0) {
            $initialPaymentType = 'debt';
        } else {
            $initialPaymentType = 'partial';
        }
    } else {
        $initialPaymentType = old('payment_type', 'cash');
    }

    $initialImage = $purchase->imageUrl();
@endphp

<form method="POST"
      action="{{ $purchase->exists ? route('purchases.update', $purchase) : route('purchases.store') }}"
      enctype="multipart/form-data"
      x-data="purchaseForm(@js($initialLines), @js($initialDiscount), @js($initialPaid), @js($initialPaymentType), @js($initialImage))"
      class="space-y-4">
    @csrf
    @if ($purchase->exists) @method('PUT') @endif
    <input type="hidden" name="currency" value="IQD">
    <input type="hidden" name="entry_mode" :value="entryMode">
    <input type="hidden" name="payment_type" :value="paymentType">
    <input type="hidden" name="remove_image" :value="removeImageFlag ? '1' : '0'">

    @if ($errors->any())
        <div class="card mb-4 border-r-4 !border-r-[--color-danger] px-4 py-3 text-sm">
            <div class="font-bold text-red-700 mb-1">تکایە ئەم هەڵانە چاک بکە:</div>
            <ul class="list-inside list-disc space-y-1 text-red-600 text-xs">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    {{-- ١. زانیاری سەرەکی کڕین و کۆمپانیا لەگەڵ وێنەی وەسڵ --}}
    <div class="card">
        <div class="card-head flex items-center justify-between">
            <span class="font-bold text-slate-800 text-sm">زانیاری پسوولەی کڕین و فرۆشیار</span>
            <a href="{{ route('purchases.index') }}" class="btn btn-ghost !py-1 text-xs">گەڕانەوە &larr;</a>
        </div>
        <div class="card-body grid gap-4 sm:grid-cols-2 lg:grid-cols-4">

            {{-- فرۆشیار / کۆمپانیا --}}
            <div class="sm:col-span-2">
                <label class="label" for="supplier_name">
                    ناوی فرۆشیار / کۆمپانیا <span class="text-[--color-danger]">*</span>
                </label>
                <input id="supplier_name" name="supplier_name" type="text" list="suppliers_list"
                       class="field font-bold text-sm"
                       value="{{ old('supplier_name', $purchase->supplier?->name) }}"
                       placeholder="ناوی فرۆشیار بنووسە یان هەڵیبژێرە..." required autocomplete="off">
                <datalist id="suppliers_list">
                    @foreach ($suppliers as $supplier)
                        <option value="{{ $supplier->name }}">
                            {{ $supplier->phone ? '(' . $supplier->phone . ')' : '' }}
                        </option>
                    @endforeach
                </datalist>
            </div>

            {{-- کۆگای وەرگرتن --}}
            <div>
                <label class="label" for="warehouse_id">
                    کۆگای وەرگرتن <span class="text-[--color-danger]">*</span>
                </label>
                <select id="warehouse_id" name="warehouse_id" class="field font-bold text-sm cursor-pointer" required>
                    @foreach ($warehouses as $warehouse)
                        <option value="{{ $warehouse->id }}" @selected(old('warehouse_id', $purchase->warehouse_id ?? $workshopWarehouse?->id) == $warehouse->id)>
                            {{ $warehouse->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- بەرواری کڕین --}}
            <div>
                <label class="label" for="purchase_date">
                    بەرواری کڕین <span class="text-[--color-danger]">*</span>
                </label>
                <input id="purchase_date" name="purchase_date" type="date" class="field num font-bold" required
                       value="{{ old('purchase_date', $purchase->purchase_date?->toDateString() ?? now()->toDateString()) }}">
            </div>

            {{-- تێبینی --}}
            <div class="sm:col-span-2 lg:col-span-4">
                <label class="label" for="note">تێبینی پسوولە</label>
                <input id="note" name="note" type="text" class="field"
                       placeholder="تێبینی، ژمارەی پسوولەی فرۆشیار یان مەرجەکان..."
                       value="{{ old('note', $purchase->note) }}">
            </div>

            {{-- وێنەی پسوولەی کڕین (فایل یان کامێرا) --}}
            <div class="sm:col-span-2 lg:col-span-4 bg-slate-50/80 p-3.5 rounded-2xl border border-dashed border-slate-300">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-center gap-2.5">
                        <span class="text-2xl">📸</span>
                        <div>
                            <span class="block text-xs font-bold text-slate-800">وێنەی پسوولەی کڕین (وەسڵی کاغەزی فرۆشیار)</span>
                            <span class="block text-[11px] text-slate-500">دەتوانیت وێنەی وەسڵەکە بە کامێرا بگریت یان فایلەکەی دابنێیت.</span>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <input type="file" id="purchase_image_input" name="image" accept="image/*" class="hidden" @change="onImageChange($event)">

                        <template x-if="imagePreview">
                            <div class="flex items-center gap-2">
                                <div class="relative size-12 rounded-xl overflow-hidden border-2 border-teal-600 shadow-xs group">
                                    <img :src="imagePreview" class="size-full object-cover cursor-pointer hover:scale-110 transition-transform" @click="window.open(imagePreview, '_blank')" title="کلیک بکە بۆ بینینی تەواوی وێنەکە">
                                </div>
                                <button type="button" @click="removeImage()" class="btn btn-ghost !py-1 !px-2.5 text-xs text-rose-600 border border-rose-200 hover:bg-rose-50 cursor-pointer">
                                    لابردنی وێنە
                                </button>
                                <button type="button" @click="document.getElementById('purchase_image_input').click()" class="btn btn-ghost !py-1 !px-2.5 text-xs text-slate-700 bg-white border border-slate-200 cursor-pointer">
                                    گۆڕینی وێنە
                                </button>
                            </div>
                        </template>

                        <template x-if="!imagePreview">
                            <button type="button" @click="document.getElementById('purchase_image_input').click()"
                                    class="px-4 py-2 rounded-xl text-xs font-black bg-white hover:bg-teal-50 text-teal-800 border border-teal-600/40 shadow-2xs flex items-center gap-1.5 transition-all cursor-pointer">
                                <span>📷</span>
                                <span>دانانی وێنەی وەسڵەکە</span>
                            </button>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ٢. شێوازی تۆمارکردنی مەوادەکان (خێرا یان دانە بە دانە) --}}
    <div class="card overflow-hidden">
        <div class="card-head flex flex-wrap items-center justify-between gap-3">
            <span class="font-bold text-slate-800 text-sm">مەوادە کڕدراوەکان</span>

            {{-- دوگمەی گۆڕینی شێواز: خێرا یان دانە بە دانە --}}
            <div class="inline-flex rounded-xl bg-slate-100 p-1 border border-slate-200 text-xs font-bold">
                <button type="button" @click="setEntryMode('quick')"
                        :class="entryMode === 'quick' ? 'bg-white text-teal-800 shadow-xs' : 'text-slate-600 hover:text-slate-900'"
                        class="px-3.5 py-1.5 rounded-lg transition-all cursor-pointer flex items-center gap-1.5">
                    <span>⚡</span>
                    <span>تۆماری خێرا (تەنها کۆی نرخ)</span>
                </button>
                <button type="button" @click="setEntryMode('itemized')"
                        :class="entryMode === 'itemized' ? 'bg-white text-teal-800 shadow-xs' : 'text-slate-600 hover:text-slate-900'"
                        class="px-3.5 py-1.5 rounded-lg transition-all cursor-pointer flex items-center gap-1.5">
                    <span>📋</span>
                    <span>وردەکاری مەوادەکان (دانە بە دانە)</span>
                </button>
            </div>
        </div>

        {{-- ١. شێوازی تۆماری خێرا (بۆ کاتێک کۆمەڵێک مەوادی تێدایە و تەنها کۆی نرخ تۆمار دەکەیت) --}}
        <div x-show="entryMode === 'quick'" class="p-4 sm:p-5 space-y-4 bg-teal-50/20">
            <div class="bg-teal-50 border border-teal-200 rounded-xl p-3 text-xs text-teal-950 flex items-start gap-2.5">
                <span class="text-lg">💡</span>
                <div class="leading-relaxed">
                    <b>تۆماری خێرا:</b> کاتێک وەسڵەکەت کۆمەڵێک مەوادی هەمەجۆری تێدایە و پێویست ناکات دانە بە دانە تۆماریان بکەیت، تەنها کۆی گشتی نرخی پسوولەکە بنووسە.
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="label text-xs font-bold text-slate-800" for="quick_title">ناوی مەوادەکان / باسی کڕین</label>
                    <input id="quick_title" name="quick_title" type="text"
                           class="field font-bold text-sm bg-white"
                           x-model="quickTitle"
                           placeholder="وەک: مەوادی هەمەجۆری کارگە، وەسڵی پەرژین، بۆیاخ و براغی...">
                </div>

                <div>
                    <label class="label text-xs font-bold text-slate-800" for="quick_total">
                        کۆی گشتی نرخی پسوولە (د.ع) <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input id="quick_total" name="quick_total" type="text" inputmode="numeric"
                               class="field num font-black text-base text-teal-800 bg-white w-full"
                               dir="ltr"
                               x-model="quickTotal"
                               @input="formatQuickTotal($event)"
                               placeholder="0">
                    </div>
                </div>
            </div>
        </div>

        {{-- ٢. شێوازی وردەکاری مەوادەکان (دانە بە دانە) --}}
        <div x-show="entryMode === 'itemized'">
            <datalist id="items_list">
                @foreach ($items as $item)
                    <option value="{{ $item->name }}" data-price="{{ $item->last_cost }}">
                        {{ $item->unit?->name ? '(' . $item->unit->name . ')' : '' }}
                    </option>
                @endforeach
            </datalist>

            <div class="overflow-x-auto">
                <table class="table w-full min-w-[700px]">
                    <thead>
                        <tr class="bg-slate-50/80 text-xs text-slate-700 font-bold border-b border-[--color-line]">
                            <th style="width: 44px; text-align: center;">#</th>
                            <th style="text-align: right; padding: 10px 12px;">ناوی کاڵا / مەواد (دەستنووس یان لە لیست)</th>
                            <th style="width: 120px; text-align: center;">بڕ / ژمارە</th>
                            <th style="width: 180px; text-align: center;">نرخی یەکە (د.ع)</th>
                            <th style="width: 180px; text-align: center;">کۆی گشتی (د.ع)</th>
                            <th style="text-align: right; padding: 10px 12px;">تێبینی</th>
                            <th style="width: 44px; text-align: center;"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        <template x-for="(line, index) in lines" :key="index">
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="text-center num text-slate-400 font-medium text-xs" x-text="index + 1"></td>

                                <td style="padding: 6px 12px;">
                                    <input type="text" list="items_list"
                                           :name="`lines[${index}][item_name]`"
                                           x-model="line.item_name"
                                           @input="fillPrice(line)"
                                           class="field w-full !py-2 !px-3 text-sm font-bold bg-white"
                                           placeholder="ناوی کاڵا بنووسە..." autocomplete="off">
                                </td>

                                <td style="padding: 6px 12px;">
                                    <input type="text" inputmode="decimal"
                                           :name="`lines[${index}][qty]`"
                                           x-model="line.qty"
                                           @input="formatQty($event, line)"
                                           class="field num w-full !py-2 !px-3 text-sm font-bold text-center bg-white"
                                           placeholder="1">
                                </td>

                                <td style="padding: 6px 12px;">
                                    <input type="text" inputmode="numeric"
                                           :name="`lines[${index}][unit_price]`"
                                           x-model="line.unit_price"
                                           @input="formatLinePrice($event, line)"
                                           class="field num w-full !py-2 !px-3 text-sm font-bold text-center bg-white"
                                           dir="ltr"
                                           placeholder="0">
                                </td>

                                <td style="padding: 6px 12px;" class="text-center num font-bold text-slate-900" x-text="money(lineTotal(line))"></td>

                                <td style="padding: 6px 12px;">
                                    <input type="text" :name="`lines[${index}][note]`"
                                           x-model="line.note"
                                           class="field w-full !py-2 !px-3 text-xs bg-white"
                                           placeholder="تێبینی...">
                                </td>

                                <td style="text-align: center; padding: 6px;">
                                    <button type="button" @click="removeLine(index)"
                                            class="inline-flex items-center justify-center size-7 rounded-lg text-rose-400 hover:text-rose-600 hover:bg-rose-50 transition-colors cursor-pointer"
                                            x-show="lines.length > 1" title="سڕینەوەی دێڕ">✕</button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <div class="p-3 border-t border-[--color-line] bg-slate-50/50">
                <button type="button" @click="addLine()"
                        class="btn btn-ghost !py-1.5 !px-3 text-xs font-bold text-blue-700 hover:bg-blue-50 border border-dashed border-blue-300 cursor-pointer">
                    + زیادکردنی مەواد یان کاڵای تر
                </button>
            </div>
        </div>
    </div>

    {{-- ٣. دارایی، شێوازی پارەدان (حازری یان بە قەرز)، و پوختەی کۆتایی --}}
    <div class="grid gap-4 lg:grid-cols-3">
        {{-- شێوازی پارەدان و داشکاندن --}}
        <div class="card lg:col-span-2">
            <div class="card-body space-y-4">
                {{-- هەڵبژاردنی حازری یان بە قەرز --}}
                <div>
                    <label class="label font-bold text-xs text-slate-800 mb-1.5">شێوازی پێدانی پارە:</label>
                    <div class="grid grid-cols-3 gap-2 sm:gap-3">
                        {{-- حازری (نەقد) --}}
                        <button type="button" @click="setPaymentType('cash')"
                                :class="paymentType === 'cash' ? 'bg-emerald-600 text-white shadow-sm ring-2 ring-emerald-600/30' : 'bg-slate-50 hover:bg-slate-100 text-slate-700 border border-slate-200'"
                                class="py-2.5 px-3 rounded-xl font-bold text-xs text-center transition-all cursor-pointer flex flex-col items-center justify-center gap-1">
                            <span class="text-base">💵</span>
                            <span>حازری (نەقد)</span>
                            <span class="text-[10px] font-normal" :class="paymentType === 'cash' ? 'text-emerald-100' : 'text-slate-400'">تەواوی پارەکە دراوە</span>
                        </button>

                        {{-- بە قەرز --}}
                        <button type="button" @click="setPaymentType('debt')"
                                :class="paymentType === 'debt' ? 'bg-rose-600 text-white shadow-sm ring-2 ring-rose-600/30' : 'bg-slate-50 hover:bg-slate-100 text-slate-700 border border-slate-200'"
                                class="py-2.5 px-3 rounded-xl font-bold text-xs text-center transition-all cursor-pointer flex flex-col items-center justify-center gap-1">
                            <span class="text-base">⏳</span>
                            <span>بە قەرز</span>
                            <span class="text-[10px] font-normal" :class="paymentType === 'debt' ? 'text-rose-100' : 'text-slate-400'">پارە نەدراوە (قەرز)</span>
                        </button>

                        {{-- بەشێکی دراوە (پێشەکی) --}}
                        <button type="button" @click="setPaymentType('partial')"
                                :class="paymentType === 'partial' ? 'bg-amber-500 text-white shadow-sm ring-2 ring-amber-500/30' : 'bg-slate-50 hover:bg-slate-100 text-slate-700 border border-slate-200'"
                                class="py-2.5 px-3 rounded-xl font-bold text-xs text-center transition-all cursor-pointer flex flex-col items-center justify-center gap-1">
                            <span class="text-base">⚖️</span>
                            <span>بەشێکی دراوە</span>
                            <span class="text-[10px] font-normal" :class="paymentType === 'partial' ? 'text-amber-100' : 'text-slate-400'">نیوە قەرز / پێشەکی</span>
                        </button>
                    </div>
                </div>

                <input type="hidden" name="discount_amount" value="0">

                <div class="pt-3 border-t border-slate-100">
                    {{-- بڕی پارەی دراو ئەگەر بەشێکی دراوە بوو --}}
                    <div x-show="paymentType === 'partial'" x-transition class="max-w-md">
                        <label class="label text-xs font-bold text-amber-700" for="paid_amount">بڕی پارەی دراو ئێستا (د.ع)</label>
                        <input id="paid_amount" name="paid_amount" type="text" inputmode="numeric"
                               class="field num font-bold text-amber-700 w-full"
                               dir="ltr"
                               x-model="paid"
                               @input="formatPaid($event)"
                               placeholder="0">
                    </div>

                    {{-- پەیامی ڕوونکردنەوە ئەگەر نەقد یان قەرز بوو --}}
                    <div x-show="paymentType === 'cash'" class="bg-emerald-50 text-emerald-800 border border-emerald-200 rounded-xl p-3 text-xs font-bold flex items-center gap-2">
                        <span>✓</span>
                        <span>ئەم پسوولەیە بە شێوەی حازری (نەقد) تۆمار دەکرێت و هیچ قەرزێک بۆ فرۆشیار تۆمار نابێت.</span>
                    </div>

                    <div x-show="paymentType === 'debt'" class="bg-rose-50 text-rose-800 border border-rose-200 rounded-xl p-3 text-xs font-bold flex items-center gap-2">
                        <span>⚠️</span>
                        <span>تەواوی بڕی پسوولەکە بە قەرز بۆ فرۆشیار تۆمار دەکرێت لە بەشی قەرزەکاندا.</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- کارتی پوختەی کۆی گشتی و ماوە --}}
        <div class="card">
            <div class="card-body space-y-2.5 text-sm">
                <div class="flex justify-between items-center text-base font-bold text-slate-900">
                    <span>کۆی گشتی کڕین</span>
                    <span class="num text-lg" x-text="money(total())">0 د.ع</span>
                </div>

                <div class="flex justify-between items-center text-emerald-700 font-semibold" x-show="cleanNum(paid) > 0">
                    <span>پارەی دراو</span>
                    <span class="num" x-text="money(cleanNum(paid))">0 د.ع</span>
                </div>

                <div class="flex justify-between items-center font-bold border-t border-slate-100 pt-2 text-base"
                     :class="remaining() > 0 ? 'text-[--color-danger]' : 'text-emerald-600'">
                    <span>ماوە (قەرز)</span>
                    <span class="num" x-text="money(remaining())">0 د.ع</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ٤. دوگمەکانی خوارەوە --}}
    <div class="flex flex-wrap items-center gap-3 pt-2">
        <button type="submit" class="btn btn-primary !py-2.5 !px-6 text-sm font-bold shadow-sm bg-blue-600 hover:bg-blue-700 cursor-pointer">
            {{ $purchase->exists ? 'نوێکردنەوەی پسوولەی کڕین' : 'تۆمارکردنی پسوولەی کڕین' }}
        </button>

        <label class="flex items-center gap-2 text-sm text-slate-700 cursor-pointer font-medium select-none">
            <input type="checkbox" name="confirm" value="1"
                   class="size-4 rounded border-[--color-line-strong] text-blue-600 focus:ring-blue-500"
                   @checked($purchase->status === 'confirmed' || !$purchase->exists)>
            <span>پەسەندکردن و زیادکردنی مەوادەکان بۆ کۆگا و مەخزەن</span>
        </label>

        <a href="{{ route('purchases.index') }}" class="btn btn-ghost">پاشگەزبوونەوە</a>
    </div>
</form>

<script>
function purchaseForm(initialLines, initialDiscount, initialPaid, initialPaymentType, initialImagePreview) {
    const hasDetailedItems = initialLines && initialLines.length > 1;

    return {
        entryMode: hasDetailedItems ? 'itemized' : 'quick',
        quickTitle: (initialLines && initialLines[0] && initialLines[0].item_name) ? initialLines[0].item_name : 'مەوادی هەمەجۆری کارگە',
        quickTotal: (initialLines && initialLines[0] && initialLines[0].unit_price) ? initialLines[0].unit_price : '',
        paymentType: initialPaymentType || 'cash',
        lines: initialLines || [{ item_name: '', qty: '', unit_price: '', note: '' }],
        discount: initialDiscount || '',
        paid: initialPaid || '',
        imagePreview: initialImagePreview || null,
        removeImageFlag: false,

        init() {
            if (this.paymentType === 'cash') {
                this.paid = this.total() ? this.total().toLocaleString('en-US') : '';
            } else if (this.paymentType === 'debt') {
                this.paid = '0';
            }
        },

        setEntryMode(mode) {
            this.entryMode = mode;
            if (mode === 'quick' && this.subtotalDetailed() > 0 && !this.quickTotal) {
                this.quickTotal = this.subtotalDetailed().toLocaleString('en-US');
            }
            if (this.paymentType === 'cash') {
                this.$nextTick(() => {
                    this.paid = this.total() ? this.total().toLocaleString('en-US') : '';
                });
            }
        },

        setPaymentType(type) {
            this.paymentType = type;
            if (type === 'cash') {
                this.paid = this.total() ? this.total().toLocaleString('en-US') : '';
            } else if (type === 'debt') {
                this.paid = '0';
            } else {
                if (this.cleanNum(this.paid) === 0 || this.cleanNum(this.paid) === this.total()) {
                    this.paid = '';
                }
            }
        },

        onImageChange(e) {
            const file = e.target.files[0];
            if (file) {
                this.imagePreview = URL.createObjectURL(file);
                this.removeImageFlag = false;
            }
        },

        removeImage() {
            this.imagePreview = null;
            this.removeImageFlag = true;
            const input = document.getElementById('purchase_image_input');
            if (input) input.value = '';
        },

        formatQuickTotal(e) {
            let clean = e.target.value.replace(/[^0-9.]/g, '');
            let parts = clean.split('.');
            if (parts.length > 2) parts = [parts[0], parts.slice(1).join('')];
            let int = parts[0] ? parseInt(parts[0], 10).toLocaleString('en-US') : '';
            let dec = parts.length > 1 ? '.' + parts[1] : '';
            this.quickTotal = int ? int + dec : '';

            if (this.paymentType === 'cash') {
                this.paid = this.total() ? this.total().toLocaleString('en-US') : '';
            }
        },

        addLine() {
            this.lines.push({ item_name: '', qty: '', unit_price: '', note: '' });
        },

        removeLine(index) {
            if (this.lines.length > 1) {
                this.lines.splice(index, 1);
            }
            if (this.paymentType === 'cash') {
                this.paid = this.total() ? this.total().toLocaleString('en-US') : '';
            }
        },

        formatQty(e, line) {
            let clean = e.target.value.replace(/[^0-9.]/g, '');
            let parts = clean.split('.');
            if (parts.length > 2) parts = [parts[0], parts.slice(1).join('')];
            line.qty = clean ? parts.join('.') : '';
            if (this.paymentType === 'cash') {
                this.paid = this.total() ? this.total().toLocaleString('en-US') : '';
            }
        },

        formatLinePrice(e, line) {
            let clean = e.target.value.replace(/[^0-9.]/g, '');
            let parts = clean.split('.');
            if (parts.length > 2) parts = [parts[0], parts.slice(1).join('')];
            let int = parts[0] ? parseInt(parts[0], 10).toLocaleString('en-US') : '';
            let dec = parts.length > 1 ? '.' + parts[1] : '';
            line.unit_price = int ? int + dec : '';
            if (this.paymentType === 'cash') {
                this.paid = this.total() ? this.total().toLocaleString('en-US') : '';
            }
        },

        formatDiscount(e) {
            let clean = e.target.value.replace(/[^0-9.]/g, '');
            let parts = clean.split('.');
            if (parts.length > 2) parts = [parts[0], parts.slice(1).join('')];
            let int = parts[0] ? parseInt(parts[0], 10).toLocaleString('en-US') : '';
            let dec = parts.length > 1 ? '.' + parts[1] : '';
            this.discount = int ? int + dec : '';
            if (this.paymentType === 'cash') {
                this.paid = this.total() ? this.total().toLocaleString('en-US') : '';
            }
        },

        formatPaid(e) {
            let clean = e.target.value.replace(/[^0-9.]/g, '');
            let parts = clean.split('.');
            if (parts.length > 2) parts = [parts[0], parts.slice(1).join('')];
            let int = parts[0] ? parseInt(parts[0], 10).toLocaleString('en-US') : '';
            let dec = parts.length > 1 ? '.' + parts[1] : '';
            this.paid = int ? int + dec : '';
        },

        cleanNum(val) {
            return parseFloat((val || '0').toString().replace(/,/g, '')) || 0;
        },

        fillPrice(line) {
            const datalist = document.getElementById('items_list');
            if (datalist && line.item_name) {
                const option = Array.from(datalist.options).find(opt => opt.value.trim().toLowerCase() === line.item_name.trim().toLowerCase());
                if (option && option.dataset.price && (!line.unit_price || this.cleanNum(line.unit_price) == 0)) {
                    line.unit_price = parseFloat(option.dataset.price).toLocaleString('en-US');
                    if (this.paymentType === 'cash') {
                        this.paid = this.total() ? this.total().toLocaleString('en-US') : '';
                    }
                }
            }
        },

        lineTotal(line) {
            const q = (parseFloat(line.qty) || 1);
            return q * this.cleanNum(line.unit_price);
        },

        subtotalDetailed() {
            return this.lines.reduce((sum, line) => sum + this.lineTotal(line), 0);
        },

        subtotal() {
            if (this.entryMode === 'quick') {
                return this.cleanNum(this.quickTotal);
            }
            return this.subtotalDetailed();
        },

        total() {
            return this.subtotal();
        },

        remaining() {
            if (this.paymentType === 'cash') {
                return 0;
            }
            if (this.paymentType === 'debt') {
                return this.total();
            }
            return Math.max(0, this.total() - this.cleanNum(this.paid));
        },

        money(val) {
            const num = parseFloat(val) || 0;
            return num.toLocaleString('en-US') + ' د.ع';
        }
    };
}
</script>
@endsection
