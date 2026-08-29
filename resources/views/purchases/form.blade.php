@extends('layouts.app')
@section('title', $purchase->exists ? 'دەستکاری پسوولەی کڕین' : 'پسوولەی کڕینی نوێ')

@section('content')

@php
    $workshopWarehouse = \App\Models\Warehouse::where('name', 'like', '%دروستکردن%')->first()
        ?? \App\Models\Warehouse::where('is_default', false)->first()
        ?? \App\Models\Warehouse::first();

    // دێڕەکان بۆ Alpine
    $initialLines = old('lines', $purchase->exists
        ? $purchase->items->map(fn ($l) => [
            'item_name' => (string) ($l->item?->name ?: $l->item_id),
            'qty' => (string) (float) $l->qty,
            'unit_price' => (string) (float) $l->unit_price,
            'note' => $l->note ?? '',
          ])->all()
        : [['item_name' => '', 'qty' => '1', 'unit_price' => '', 'note' => '']]);
@endphp

<div class="space-y-4 sm:space-y-6">

    {{-- ١. هێڵی سەرەوە: ناونیشان --}}
    <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-xs flex items-center justify-between gap-4">
        <div class="flex items-center gap-3.5">
            <div class="size-12 rounded-2xl bg-linear-to-br from-teal-500 to-emerald-600 text-white flex items-center justify-center text-2xl shadow-md shadow-emerald-500/20 shrink-0">
                🛒
            </div>
            <div>
                <div class="flex items-center gap-2 flex-wrap">
                    <h1 class="text-lg sm:text-xl font-black text-slate-900">
                        {{ $purchase->exists ? 'دەستکاری پسوولەی کڕینی #' . $purchase->invoice_no : 'پسوولەی کڕینی نوێ' }}
                    </h1>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-teal-50 text-teal-800 border border-teal-200/80">
                        کڕینی مەواد و کاڵا
                    </span>
                </div>
                <p class="text-xs text-slate-500 mt-1 font-medium">
                    تۆمارکردنی کڕین بۆ شوێنی دروستکردن و کارگە
                </p>
            </div>
        </div>

        <a href="{{ route('purchases.index') }}"
           class="px-4 py-2 rounded-xl text-xs font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 transition-all inline-flex items-center gap-1.5">
            <span>←</span>
            <span>گەڕانەوە بۆ پسوولەکان</span>
        </a>
    </div>

    {{-- فۆڕمی سەرەکی --}}
    <form method="POST"
          action="{{ $purchase->exists ? route('purchases.update', $purchase) : route('purchases.store') }}"
          x-data="purchaseForm(@js($initialLines), @js((float) old('discount_amount', $purchase->discount_amount ?: 0)))"
          class="space-y-4 sm:space-y-6">
        @csrf
        @if ($purchase->exists) @method('PUT') @endif
        <input type="hidden" name="currency" value="IQD">

        @if ($errors->any())
            <div class="bg-rose-50 border border-rose-200 rounded-2xl p-4 text-xs text-rose-800">
                <div class="font-bold mb-1.5">⚠️ تکایە ئەم هەڵانە چاک بکە:</div>
                <ul class="list-inside list-disc space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- ٢. کارتی زانیاری سەرەکی پسوولە --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="p-4 border-b border-slate-100 bg-slate-50/60 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="text-base">📝</span>
                    <h3 class="font-black text-sm text-slate-800">زانیارییەکانی پسوولە</h3>
                </div>
            </div>

            <div class="p-4 sm:p-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                {{-- فرۆشیار (دەستنووس یان هەڵبژاردن) --}}
                <div class="sm:col-span-2">
                    <label class="block font-bold text-xs text-slate-700 mb-1.5" for="supplier_name">
                        ناوی فرۆشیار / کۆمپانیا <span class="text-rose-500">*</span>
                    </label>
                    <input id="supplier_name" name="supplier_name" type="text" list="suppliers_list"
                           class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white focus:outline-hidden focus:border-blue-500 font-black text-slate-900"
                           value="{{ old('supplier_name', $purchase->supplier?->name) }}"
                           placeholder="ناوی فرۆشیار بنووسە یان لە لیستەکە هەڵیبژێرە..." required autocomplete="off">
                    <datalist id="suppliers_list">
                        @foreach ($suppliers as $supplier)
                            <option value="{{ $supplier->name }}">
                                {{ $supplier->phone ? '(' . $supplier->phone . ')' : '' }}
                            </option>
                        @endforeach
                    </datalist>
                </div>

                {{-- کۆگا (بە بنەڕەت: شوێنی دروستکردن) --}}
                <div>
                    <label class="block font-bold text-xs text-slate-700 mb-1.5" for="warehouse_id">
                        کۆگای وەرگرتن <span class="text-rose-500">*</span>
                    </label>
                    <select id="warehouse_id" name="warehouse_id"
                            class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white focus:outline-hidden focus:border-blue-500 font-bold text-slate-800" required>
                        @foreach ($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}" @selected(old('warehouse_id', $purchase->warehouse_id ?? $workshopWarehouse?->id) == $warehouse->id)>
                                {{ $warehouse->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- بەروار --}}
                <div>
                    <label class="block font-bold text-xs text-slate-700 mb-1.5" for="purchase_date">
                        بەرواری کڕین <span class="text-rose-500">*</span>
                    </label>
                    <input id="purchase_date" name="purchase_date" type="date"
                           class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white focus:outline-hidden focus:border-blue-500 font-mono font-bold text-slate-800" required
                           value="{{ old('purchase_date', $purchase->purchase_date?->toDateString() ?? now()->toDateString()) }}">
                </div>

                {{-- تێبینی --}}
                <div class="sm:col-span-2 lg:col-span-4">
                    <label class="block font-bold text-xs text-slate-700 mb-1.5" for="note">تێبینی پسوولە</label>
                    <input id="note" name="note" type="text"
                           class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white focus:outline-hidden focus:border-blue-500 font-medium text-slate-800"
                           placeholder="تێبینی، ژمارەی پسوولەی فرۆشیار یان مەرجەکان بنووسە..."
                           value="{{ old('note', $purchase->note) }}">
                </div>
            </div>
        </div>

        {{-- ٣. کارتی کاڵا و مەوادەکان --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="p-4 border-b border-slate-100 bg-slate-50/60 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="text-base">📦</span>
                    <h3 class="font-black text-sm text-slate-800">کاڵا و مەوادە کڕدراوەکان</h3>
                </div>
                <button type="button" @click="addLine()"
                        class="px-3.5 py-1.5 rounded-xl text-xs font-black bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 transition-all cursor-pointer inline-flex items-center gap-1">
                    <span>➕</span>
                    <span>زیادکردنی دێڕ</span>
                </button>
            </div>

            {{-- Datalist for items autocomplete --}}
            <datalist id="items_list">
                @foreach ($items as $item)
                    <option value="{{ $item->name }}" data-price="{{ $item->last_cost }}">
                        {{ $item->unit?->name ? '(' . $item->unit->name . ')' : '' }}
                    </option>
                @endforeach
            </datalist>

            <div class="overflow-x-auto">
                <table class="w-full text-right text-xs">
                    <thead class="bg-slate-50 text-slate-600 border-b border-slate-200 font-black">
                        <tr>
                            <th class="p-3.5 w-12 text-center">#</th>
                            <th class="p-3.5">ناوی کاڵا / مەواد (دەستنووس یان هەڵبژاردن)</th>
                            <th class="p-3.5 text-center w-28">بڕ / ژمارە</th>
                            <th class="p-3.5 text-left w-36">نرخی یەکە (د.ع)</th>
                            <th class="p-3.5 text-left w-36">کۆی گشتی (د.ع)</th>
                            <th class="p-3.5">تێبینی دێڕ</th>
                            <th class="p-3.5 w-12 text-center"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <template x-for="(line, index) in lines" :key="index">
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="p-3 text-center font-mono font-bold text-slate-400" x-text="index + 1"></td>
                                
                                {{-- ناوی کاڵا --}}
                                <td class="p-3">
                                    <input type="text" list="items_list"
                                           :name="`lines[${index}][item_name]`"
                                           x-model="line.item_name"
                                           @input="fillPrice(line)"
                                           class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 bg-white focus:outline-hidden focus:border-blue-500 font-bold text-slate-900"
                                           placeholder="ناوی کاڵا یان مەواد بنووسە..." required autocomplete="off">
                                </td>

                                {{-- بڕ --}}
                                <td class="p-3">
                                    <input type="number" step="0.001" min="0.001" required
                                           :name="`lines[${index}][qty]`"
                                           x-model.number="line.qty"
                                           class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 bg-white focus:outline-hidden focus:border-blue-500 font-mono font-bold text-slate-800 text-center">
                                </td>

                                {{-- نرخی یەکە --}}
                                <td class="p-3">
                                    <input type="number" step="1" min="0" required
                                           :name="`lines[${index}][unit_price]`"
                                           x-model.number="line.unit_price"
                                           class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 bg-white focus:outline-hidden focus:border-blue-500 font-mono font-bold text-slate-800 text-left">
                                </td>

                                {{-- کۆی دێڕ --}}
                                <td class="p-3 text-left font-mono font-black text-slate-900 text-xs" x-text="money(lineTotal(line))"></td>

                                {{-- تێبینی دێڕ --}}
                                <td class="p-3">
                                    <input type="text" :name="`lines[${index}][note]`"
                                           x-model="line.note"
                                           class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 bg-white focus:outline-hidden focus:border-blue-500 font-medium text-slate-700"
                                           placeholder="تێبینی...">
                                </td>

                                {{-- سڕینەوە --}}
                                <td class="p-3 text-center">
                                    <button type="button" @click="removeLine(index)"
                                            class="text-rose-500 hover:text-rose-700 font-bold text-base p-1"
                                            x-show="lines.length > 1" title="سڕینەوەی دێڕ">
                                        ✕
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ٤. پارەدان و حیساباتی کۆتایی --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">
            {{-- داشکاندن و پارەی دراو --}}
            <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-xs lg:col-span-2 space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-bold text-xs text-slate-700 mb-1.5" for="discount_amount">
                            داشکاندن (د.ع)
                        </label>
                        <input id="discount_amount" name="discount_amount" type="number" step="1" min="0"
                               class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white focus:outline-hidden focus:border-blue-500 font-mono font-bold text-slate-800"
                               x-model.number="discount" placeholder="0">
                    </div>

                    <div>
                        <label class="block font-bold text-xs text-slate-700 mb-1.5" for="paid_amount">
                            پارەی دراو ئێستا (د.ع)
                        </label>
                        <input id="paid_amount" name="paid_amount" type="number" step="1" min="0"
                               class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white focus:outline-hidden focus:border-blue-500 font-mono font-bold text-emerald-800"
                               value="{{ old('paid_amount', $purchase->paid_amount ?: 0) }}" placeholder="0">
                        <p class="text-[10px] text-slate-400 mt-1 font-medium">ئەگەر پارە بدەیت، تۆماری پارەدان و حەقدی دروست دەبێت.</p>
                    </div>
                </div>

                {{-- پەسەندکردن --}}
                <div class="pt-3 border-t border-slate-100 flex items-center gap-3">
                    <label class="inline-flex items-center gap-2 cursor-pointer text-xs font-bold text-slate-800">
                        <input type="checkbox" name="confirm" value="1"
                               class="size-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                               @checked($purchase->status === 'confirmed')>
                        <span>پەسەندکردن و زیادکردنی مەوادەکان بۆ کۆگا و مەخزەن</span>
                    </label>
                </div>
            </div>

            {{-- پوختەی پارە --}}
            <div class="bg-slate-900 text-white rounded-2xl p-5 shadow-md flex flex-col justify-between space-y-4">
                <div class="space-y-3">
                    <div class="text-xs font-bold text-slate-400 border-b border-slate-800 pb-2">
                        پوختەی کۆی گشتی
                    </div>
                    <div class="flex items-center justify-between text-xs text-slate-300">
                        <span>کۆی دێڕەکان:</span>
                        <span class="font-mono font-bold" x-text="money(subtotal())"></span>
                    </div>
                    <div class="flex items-center justify-between text-xs text-slate-300">
                        <span>داشکاندن:</span>
                        <span class="font-mono font-bold text-amber-400" x-text="money(discount || 0)"></span>
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-800">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-bold text-slate-300">کۆی گشتی:</span>
                        <span class="text-xl sm:text-2xl font-black font-mono text-emerald-400" x-text="money(total())"></span>
                    </div>
                </div>

                <div class="pt-2 flex gap-2">
                    <button type="submit"
                            class="w-full py-3 rounded-xl text-xs font-black bg-blue-600 hover:bg-blue-500 text-white shadow-md transition-all cursor-pointer text-center">
                        💾 {{ $purchase->exists ? 'نوێکردنەوەی پسوولە' : 'تۆمارکردنی پسوولەی کڕین' }}
                    </button>
                </div>
            </div>
        </div>
    </form>

</div>

<script>
function purchaseForm(initialLines, initialDiscount) {
    return {
        lines: initialLines,
        discount: initialDiscount,

        addLine() {
            this.lines.push({ item_name: '', qty: 1, unit_price: '', note: '' });
        },

        removeLine(index) {
            if (this.lines.length > 1) {
                this.lines.splice(index, 1);
            }
        },

        fillPrice(line) {
            // Find option in items_list datalist if exists
            const datalist = document.getElementById('items_list');
            if (datalist && line.item_name) {
                const option = Array.from(datalist.options).find(opt => opt.value.trim().toLowerCase() === line.item_name.trim().toLowerCase());
                if (option && option.dataset.price && (!line.unit_price || line.unit_price == 0)) {
                    line.unit_price = parseFloat(option.dataset.price);
                }
            }
        },

        lineTotal(line) {
            return (parseFloat(line.qty) || 0) * (parseFloat(line.unit_price) || 0);
        },

        subtotal() {
            return this.lines.reduce((sum, line) => sum + this.lineTotal(line), 0);
        },

        total() {
            return Math.max(0, this.subtotal() - (parseFloat(this.discount) || 0));
        },

        money(val) {
            const num = parseFloat(val) || 0;
            return num.toLocaleString('en-US') + ' د.ع';
        }
    };
}
</script>
@endsection
