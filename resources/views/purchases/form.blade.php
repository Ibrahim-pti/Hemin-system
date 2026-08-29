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
        : [['item_name' => '', 'qty' => '1', 'unit_price' => '', 'note' => '']]);

    $initialDiscount = old('discount_amount', $purchase->discount_amount ? number_format((float)$purchase->discount_amount) : '');
    $initialPaid = old('paid_amount', $purchase->paid_amount ? number_format((float)$purchase->paid_amount) : '');
@endphp

<form method="POST"
      action="{{ $purchase->exists ? route('purchases.update', $purchase) : route('purchases.store') }}"
      x-data="purchaseForm(@js($initialLines), @js($initialDiscount), @js($initialPaid))"
      class="space-y-4">
    @csrf
    @if ($purchase->exists) @method('PUT') @endif
    <input type="hidden" name="currency" value="IQD">

    @if ($errors->any())
        <div class="card mb-4 border-r-4 !border-r-[--color-danger] px-4 py-3 text-sm">
            <div class="font-bold text-red-700 mb-1">تکایە ئەم هەڵانە چاک بکە:</div>
            <ul class="list-inside list-disc space-y-1 text-red-600 text-xs">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    {{-- ١. زانیاری سەرەکی کڕین و کۆمپانیا --}}
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
        </div>
    </div>

    {{-- ٢. خشتەی کاڵا و مەوادەکان --}}
    <div class="card">
        <div class="card-head flex items-center justify-between">
            <span class="font-bold text-slate-800 text-sm">کاڵا و مەوادە کڕدراوەکان</span>
            <span class="text-xs text-slate-500 font-medium">نرخەکان بە دینار (د.ع)</span>
        </div>

        {{-- لیستی کاڵاکان بۆ autocomplete --}}
        <datalist id="items_list">
            @foreach ($items as $item)
                <option value="{{ $item->name }}" data-price="{{ $item->last_cost }}">
                    {{ $item->unit?->name ? '(' . $item->unit->name . ')' : '' }}
                </option>
            @endforeach
        </datalist>

        <div class="overflow-x-auto">
            <table class="table w-full">
                <thead>
                    <tr class="bg-slate-50/80 text-xs text-slate-700 font-bold border-b border-[--color-line]">
                        <th style="width: 44px; text-align: center;">#</th>
                        <th style="text-align: right; padding: 10px 12px;">ناوی کاڵا / مەواد (دەستنووس یان لە لیست)</th>
                        <th style="width: 130px; text-align: center;">بڕ / ژمارە</th>
                        <th style="width: 190px; text-align: center;">نرخی یەکە (د.ع)</th>
                        <th style="width: 190px; text-align: center;">کۆی گشتی (د.ع)</th>
                        <th style="text-align: right; padding: 10px 12px;">تێبینی</th>
                        <th style="width: 44px; text-align: center;"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    <template x-for="(line, index) in lines" :key="index">
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            {{-- # --}}
                            <td class="text-center num text-slate-400 font-medium text-xs" x-text="index + 1"></td>

                            {{-- ناوی کاڵا --}}
                            <td style="padding: 6px 12px;">
                                <input type="text" list="items_list"
                                       :name="`lines[${index}][item_name]`"
                                       x-model="line.item_name"
                                       @input="fillPrice(line)"
                                       class="field w-full !py-2 !px-3 text-sm font-bold bg-white"
                                       placeholder="ناوی کاڵا بنووسە..." required autocomplete="off">
                            </td>

                            {{-- بڕ --}}
                            <td style="padding: 6px 12px;">
                                <input type="number" step="0.001" min="0.001" required
                                       :name="`lines[${index}][qty]`"
                                       x-model.number="line.qty"
                                       class="field num w-full !py-2 !px-3 text-sm font-bold text-center bg-white">
                            </td>

                            {{-- نرخی یەکە بە فاریزە --}}
                            <td style="padding: 6px 12px;">
                                <input type="text" inputmode="numeric" required
                                       :name="`lines[${index}][unit_price]`"
                                       x-model="line.unit_price"
                                       @input="formatLinePrice($event, line)"
                                       class="field num w-full !py-2 !px-3 text-sm font-bold text-center bg-white"
                                       dir="ltr"
                                       placeholder="0">
                            </td>

                            {{-- کۆی دێڕ --}}
                            <td style="padding: 6px 12px;" class="text-center num font-bold text-slate-900" x-text="money(lineTotal(line))"></td>

                            {{-- تێبینی دێڕ --}}
                            <td style="padding: 6px 12px;">
                                <input type="text" :name="`lines[${index}][note]`"
                                       x-model="line.note"
                                       class="field w-full !py-2 !px-3 text-xs bg-white"
                                       placeholder="تێبینی دێڕ...">
                            </td>

                            {{-- سڕینەوە --}}
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

        {{-- دوگمەی زیادکردنی دێڕ --}}
        <div class="p-3 border-t border-[--color-line] bg-slate-50/50">
            <button type="button" @click="addLine()"
                    class="btn btn-ghost !py-1.5 !px-3 text-xs font-bold text-blue-700 hover:bg-blue-50 border border-dashed border-blue-300 cursor-pointer">
                + زیادکردنی مەواد یان کاڵای تر
            </button>
        </div>
    </div>

    {{-- ٣. دارایی، داشکاندن، پارەی دراو و پوختەی کۆتایی --}}
    <div class="grid gap-4 lg:grid-cols-3">
        {{-- داشکاندن و پارەی دراو --}}
        <div class="card lg:col-span-2">
            <div class="card-body grid gap-4 sm:grid-cols-2">
                {{-- داشکاندن --}}
                <div>
                    <label class="label" for="discount_amount">داشکاندن (د.ع)</label>
                    <input id="discount_amount" name="discount_amount" type="text" inputmode="numeric"
                           class="field num font-bold w-full"
                           dir="ltr"
                           x-model="discount"
                           @input="formatDiscount($event)"
                           placeholder="0">
                    <p class="mt-1 text-xs text-[--color-ink-soft]">
                        ئەگەر فرۆشیار داشکاندنی کردووە، بڕی پارەکەی لێرە بنووسە.
                    </p>
                </div>

                {{-- پارەی دراو ئێستا --}}
                <div>
                    <label class="label" for="paid_amount">پارەی دراو ئێستا (د.ع)</label>
                    <input id="paid_amount" name="paid_amount" type="text" inputmode="numeric"
                           class="field num font-bold text-emerald-700 w-full"
                           dir="ltr"
                           x-model="paid"
                           @input="formatPaid($event)"
                           placeholder="0">
                    <p class="mt-1 text-xs text-[--color-ink-soft]">
                        ئەگەر پارە بدەیت، تۆماری حەقدی و پارەدان دروست دەبێت.
                    </p>
                </div>
            </div>
        </div>

        {{-- کارتی پوختەی کۆی گشتی و ماوە --}}
        <div class="card">
            <div class="card-body space-y-2.5 text-sm">
                <div class="flex justify-between items-center">
                    <span class="text-[--color-ink-soft]">کۆی کاڵاکان</span>
                    <span class="num font-semibold text-slate-800" x-text="money(subtotal())">0 د.ع</span>
                </div>

                <div class="flex justify-between items-center" x-show="cleanNum(discount) > 0">
                    <span class="text-rose-600">داشکاندن</span>
                    <span class="num font-semibold text-rose-600" x-text="'- ' + money(cleanNum(discount))">0 د.ع</span>
                </div>

                <div class="flex justify-between items-center border-t border-[--color-line] pt-2 text-base font-bold text-slate-900">
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
        <button type="submit" class="btn btn-primary !py-2.5 !px-6 text-sm font-bold shadow-sm bg-blue-600 hover:bg-blue-700">
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
function purchaseForm(initialLines, initialDiscount, initialPaid) {
    return {
        lines: initialLines,
        discount: initialDiscount || '',
        paid: initialPaid || '',

        addLine() {
            this.lines.push({ item_name: '', qty: 1, unit_price: '', note: '' });
        },

        removeLine(index) {
            if (this.lines.length > 1) {
                this.lines.splice(index, 1);
            }
        },

        formatLinePrice(e, line) {
            let clean = e.target.value.replace(/[^0-9.]/g, '');
            let parts = clean.split('.');
            if (parts.length > 2) parts = [parts[0], parts.slice(1).join('')];
            let int = parts[0] ? parseInt(parts[0], 10).toLocaleString('en-US') : '';
            let dec = parts.length > 1 ? '.' + parts[1] : '';
            line.unit_price = int ? int + dec : '';
        },

        formatDiscount(e) {
            let clean = e.target.value.replace(/[^0-9.]/g, '');
            let parts = clean.split('.');
            if (parts.length > 2) parts = [parts[0], parts.slice(1).join('')];
            let int = parts[0] ? parseInt(parts[0], 10).toLocaleString('en-US') : '';
            let dec = parts.length > 1 ? '.' + parts[1] : '';
            this.discount = int ? int + dec : '';
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
                }
            }
        },

        lineTotal(line) {
            return (parseFloat(line.qty) || 0) * this.cleanNum(line.unit_price);
        },

        subtotal() {
            return this.lines.reduce((sum, line) => sum + this.lineTotal(line), 0);
        },

        total() {
            return Math.max(0, this.subtotal() - this.cleanNum(this.discount));
        },

        remaining() {
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
