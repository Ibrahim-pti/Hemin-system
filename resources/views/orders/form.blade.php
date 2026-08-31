@extends('layouts.app')
@section('title', $order->exists ? 'دەستکاری وەسڵ ' . $order->invoice_no : 'وەسڵی نوێ')

@section('content')

@php
    $initialLines = old('lines', $order->exists
        ? $order->items->map(fn ($l) => [
            'description' => $l->description,
            'image' => $l->image,
            'preview' => $l->imageUrl(),
            'unit_price' => $l->unit_price !== null ? (float)$l->unit_price : '',
            'note' => $l->note ?? '',
          ])->all()
        : [['description' => '', 'image' => '', 'preview' => null, 'unit_price' => '', 'note' => '']]);
@endphp

<form method="POST"
      action="{{ $order->exists ? route('orders.update', $order) : route('orders.store') }}"
      enctype="multipart/form-data"
      x-data="orderForm(
          @js($initialLines),
          @js(old('discount_amount', $order->discount_amount ? (float)$order->discount_amount : '')),
          @js(old('currency', $order->currency ?: 'IQD')),
          @js(collect($customers)->mapWithKeys(fn ($c) => [$c->id => (float) $c->discount_percent])->all()),
          @js($customers->map(fn ($c) => ['id' => $c->id, 'name' => $c->name, 'phone' => $c->phone, 'discount_percent' => (float) $c->discount_percent])->values()->all())
      )">
    @csrf
    @if ($order->exists) @method('PUT') @endif
    <input type="hidden" name="invoice_no" value="{{ old('invoice_no', $order->invoice_no ?: $nextNo) }}">

    @if ($errors->any())
        <div class="card mb-4 border-r-4 !border-r-[--color-danger] px-4 py-3 text-sm">
            <div class="font-bold text-red-700 mb-1">تکایە ئەم هەڵانە چاک بکە:</div>
            <ul class="list-inside list-disc space-y-1 text-red-600 text-xs">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <div class="card">
        <div class="card-head flex items-center justify-between">
            <span>زانیاری وەسڵ و کڕیار</span>
            <a href="{{ route('customers.index') }}" class="btn btn-ghost !py-1 text-xs">گەڕانەوە &larr;</a>
        </div>
        <div class="card-body grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            {{-- کڕیار --}}
            <div class="sm:col-span-2">
                <div class="flex items-center justify-between mb-1">
                    <label class="label !mb-0" for="customer_id">
                        بەڕێز (کڕیار) <span class="text-[--color-danger]">*</span>
                    </label>
                    <button type="button" @click="openCustomerModal()" class="text-xs text-blue-600 hover:text-blue-700 font-bold flex items-center gap-1 cursor-pointer">
                        <span>+</span>
                        <span>زیادکردنی کڕیاری نوێ</span>
                    </button>
                </div>
                <div class="relative">
                    <select id="customer_id" name="customer_id" class="field font-bold text-sm" required
                            x-model="customerId"
                            @change="onCustomerSelectChange($event)">
                        <option value="">کڕیار هەڵبژێرە...</option>
                        <option value="__NEW__" class="font-bold text-blue-600">+ زیادکردنی کڕیاری نوێ...</option>
                        <template x-for="c in customersList" :key="c.id">
                            <option :value="c.id" :selected="c.id == customerId" x-text="c.name + (c.phone ? ' — ' + c.phone : '')"></option>
                        </template>
                    </select>
                </div>
            </div>

            {{-- بەروار --}}
            <div>
                <label class="label" for="order_date">
                    بەروار <span class="text-[--color-danger]">*</span>
                </label>
                <input id="order_date" name="order_date" type="date" class="field num font-bold" required
                       value="{{ old('order_date', $order->order_date?->toDateString() ?? now()->toDateString()) }}">
            </div>

            {{-- تێبینی --}}
            <div class="sm:col-span-2 lg:col-span-4">
                <label class="label" for="note">تێبینی</label>
                <input id="note" name="note" type="text" class="field"
                       placeholder="تێبینی گشتی وەسڵ..."
                       value="{{ old('note', $order->note) }}">
            </div>
        </div>
    </div>

    {{-- ٢. خشتەی شتە داواکراوەکان بە پێکهاتەی وەسڵ --}}
    <div class="card mt-4">
        <div class="card-head flex items-center justify-between">
            <span>ناوەڕۆکی شتە داواکراوەکان</span>

            {{-- دراو و نرخی گۆڕینەوە --}}
            <div class="flex items-center gap-2 text-xs">
                <label class="font-semibold text-slate-700">دراو:</label>
                <select name="currency" class="field !py-1 !px-2 text-xs font-bold" x-model="currency">
                    <option value="IQD">دینار (IQD)</option>
                    <option value="USD">دۆلار ($ USD)</option>
                </select>

                <template x-if="currency === 'USD'">
                    <div class="flex items-center gap-1 bg-slate-50 border border-slate-200 rounded-lg px-2 py-1">
                        <span class="text-slate-500 font-medium">نرخی $١٠٠:</span>
                        <input type="text" name="exchange_rate" x-model="exchangeRate"
                               class="w-16 text-center font-mono font-bold text-xs bg-white border border-slate-300 rounded px-1 py-0.5 outline-none">
                        <button type="button" @click="fetchLiveRate()" :disabled="fetchingRate" class="text-blue-600 hover:text-blue-700 cursor-pointer" title="وەرگرتنی نرخی ئەمڕۆ">
                            <span :class="fetchingRate ? 'animate-spin inline-block' : ''">🔄</span>
                        </button>
                    </div>
                </template>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="table w-full">
                <thead>
                    <tr class="bg-slate-50 text-xs text-slate-600 font-bold border-b border-[--color-line]">
                        <th style="width: 70px; text-align: center;">وێنە</th>
                        <th style="text-align: right; padding: 8px 12px;">ناوەڕۆک / شتەکە (وەک: دەرگا، مەحەجەرە...)</th>
                        <th style="width: 220px; text-align: center;">
                            نرخ (<span x-text="currency === 'USD' ? '$' : 'د.ع'"></span>)
                        </th>
                        <th style="width: 44px; text-align: center;"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <template x-for="(line, index) in lines" :key="index">
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            {{-- وێنەی شتەکە --}}
                            <td style="text-align: center; vertical-align: middle; padding: 6px;">
                                <div class="relative flex items-center justify-center">
                                    <template x-if="line.preview">
                                        <div class="relative size-11 rounded-lg border border-slate-200 overflow-hidden group shadow-2xs">
                                            <img :src="line.preview" class="size-full object-cover">
                                            <button type="button" @click="removeImage(line, index)"
                                                    class="absolute inset-0 bg-black/60 text-white text-xs opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity"
                                                    title="سڕینەوەی وێنە">✕</button>
                                        </div>
                                    </template>
                                    <template x-if="!line.preview">
                                        <label :for="`order_line_image_${index}`"
                                               class="size-11 rounded-lg border-2 border-dashed border-slate-200 hover:border-blue-400 bg-slate-50/70 flex flex-col items-center justify-center cursor-pointer transition-colors text-slate-400 hover:text-blue-600"
                                               title="وێنە دانێ">
                                            <span class="text-base leading-none">🖼️</span>
                                        </label>
                                    </template>
                                    <input type="file"
                                           :id="`order_line_image_${index}`"
                                           :name="`lines[${index}][image]`"
                                           accept="image/*"
                                           class="sr-only"
                                           @change="onImageChange($event, line)">
                                    <input type="hidden" :name="`lines[${index}][existing_image]`" :value="line.image || ''">
                                </div>
                            </td>

                            {{-- ناوەڕۆک و تێبینی --}}
                            <td style="padding: 6px 12px;">
                                <div>
                                    <input type="text"
                                           :name="`lines[${index}][description]`"
                                           x-model="line.description"
                                           class="field w-full !py-2 !px-3 text-sm font-bold bg-white"
                                           placeholder="ناوی شتەکە یان ناوەڕۆک بنووسە..."
                                           required>
                                    <input type="text"
                                           :name="`lines[${index}][note]`"
                                           x-model="line.note"
                                           class="field w-full !py-1 !px-2 mt-1 text-xs text-slate-500 bg-white"
                                           placeholder="تێبینی زیاتر (ئارەزوومەندانە)...">
                                </div>
                            </td>

                            {{-- نرخ بە فاریزە --}}
                            <td style="padding: 6px 12px;">
                                <div class="relative">
                                    <input type="text" inputmode="numeric" required
                                           :name="`lines[${index}][unit_price]`"
                                           x-model="line.unit_price"
                                           @input="formatInput($event, line)"
                                           class="field num w-full !py-2 !px-3 text-sm font-bold text-center bg-white"
                                           dir="ltr"
                                           placeholder="0">
                                </div>
                            </td>

                            {{-- سڕینەوە --}}
                            <td style="text-align: center; padding: 6px;">
                                <button type="button" @click="removeLine(index)" class="inline-flex items-center justify-center size-7 rounded-lg text-rose-400 hover:text-rose-600 hover:bg-rose-50 transition-colors cursor-pointer"
                                        x-show="lines.length > 1" title="سڕینەوە">✕</button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        {{-- دوگمەی زیادکردنی شتی تر --}}
        <div class="p-3 border-t border-[--color-line] bg-slate-50/50">
            <button type="button" @click="addLine()" class="btn btn-ghost !py-1.5 !px-3 text-xs font-bold text-[--color-brand-700] hover:bg-blue-50 border border-dashed border-blue-300 cursor-pointer">
                + زیادکردنی هی تر
            </button>
        </div>
    </div>

    {{-- ٣. کۆی پارە، داشکاندن، پێشەکی و ماوە --}}
    <div class="mt-4 grid gap-4 lg:grid-cols-3">
        <div class="card lg:col-span-2">
            <div class="card-body grid gap-4 sm:grid-cols-2">
                {{-- داشکاندن --}}
                <div>
                    <label class="label" for="discount_amount">
                        داشکاندن <span class="text-xs font-normal text-slate-500" x-text="'(' + (currency === 'USD' ? 'دۆلار $' : 'دینار د.ع') + ')'"></span>
                    </label>
                    <div class="relative">
                        <input id="discount_amount" name="discount_amount" type="text" inputmode="numeric"
                               class="field num font-bold text-rose-600 w-full"
                               dir="ltr"
                               @input="formatDiscountInput($event)"
                               x-model="discountAmount"
                               placeholder="0">
                    </div>
                    <p class="mt-1 text-xs text-[--color-ink-soft]">
                        ئەگەر داشکاندن هەبێت، بڕی پارەکەی لێرە بنووسە (وەک: ٥,٠٠٠ یان ٢٥,٠٠٠).
                    </p>
                </div>

                {{-- پێشەکی / پارەی دراو --}}
                <div>
                    <label class="label" for="prepaid_amount">پێشەکی (بڕی پارەی دراو)</label>
                    <div class="relative">
                        <input id="prepaid_amount" name="prepaid_amount" type="text" inputmode="numeric" class="field num font-bold text-emerald-700 w-full"
                               dir="ltr"
                               @input="formatPrepaidInput($event)"
                               x-model="prepaid"
                               placeholder="0">
                    </div>
                    <p class="mt-1 text-xs text-[--color-ink-soft]">بە شێوەی خۆکار تەواوی پارەکەیە (ئەگەر قەرز بوو دەتوانیت دەستکاری بکەیت).</p>
                </div>
            </div>
        </div>

        {{-- کارتی کۆی گشتی و باڵانس --}}
        <div class="card">
            <div class="card-body space-y-2.5 text-sm">
                <div class="flex justify-between items-center">
                    <span class="text-[--color-ink-soft]">کۆی شتەکان</span>
                    <span class="num font-semibold text-slate-800" x-text="money(subtotal)">0</span>
                </div>
                <div class="flex justify-between items-center" x-show="cleanDiscount > 0">
                    <span class="text-rose-600">داشکاندن</span>
                    <span class="num font-semibold text-rose-600" x-text="'- ' + money(cleanDiscount)">0</span>
                </div>
                <div class="flex justify-between items-center border-t border-[--color-line] pt-2 text-base font-bold text-slate-900">
                    <span>کۆی گشتی</span>
                    <span class="num text-lg" x-text="money(total)">0</span>
                </div>
                <div class="flex justify-between items-center text-emerald-700 font-semibold" x-show="cleanPrepaid > 0">
                    <span>پێشەکی / دراو</span>
                    <span class="num" x-text="money(cleanPrepaid)">0</span>
                </div>
                <div class="flex justify-between items-center font-bold border-t border-slate-100 pt-2 text-base" :class="remaining > 0 ? 'text-[--color-danger]' : 'text-emerald-600'">
                    <span>ماوە (قەرز)</span>
                    <span class="num" x-text="money(remaining)">0</span>
                </div>
            </div>
        </div>
    </div>

    {{-- دوگمەکانی بنەوە --}}
    <div class="mt-4 flex flex-wrap items-center gap-3">
        <button class="btn btn-primary !py-2.5 !px-6 text-sm font-bold shadow-sm">
            {{ $order->exists ? 'نوێکردنەوەی وەسڵ' : 'تۆمارکردنی وەسڵ' }}
        </button>

        @unless ($order->exists)
            <label class="flex items-center gap-2 text-sm text-slate-700 cursor-pointer font-medium">
                <input type="checkbox" name="confirm" value="1" checked class="size-4 rounded border-[--color-line-strong]">
                پەسەندکردن و جێبەجێکردن
            </label>
        @endunless

        <a href="{{ route('customers.index') }}" class="btn btn-ghost">پاشگەزبوونەوە</a>
    </div>

    {{-- مۆداڵی خێرای دروستکردنی کڕیار بەبێ بەجێهێشتنی فۆرمی وەسڵ --}}
    <div x-show="showCustomerModal" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4"
         x-transition.opacity>
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-5 border border-slate-200"
             @click.away="showCustomerModal = false"
             x-transition.scale>
            <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                <div class="font-bold text-slate-800 text-base flex items-center gap-2">
                    <span class="size-8 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center text-sm font-bold">👤</span>
                    <span>زیادکردنی کڕیاری نوێ</span>
                </div>
                <button type="button" @click="showCustomerModal = false" class="text-slate-400 hover:text-slate-600 size-7 rounded-lg flex items-center justify-center text-lg hover:bg-slate-100 transition-colors">✕</button>
            </div>

            <div class="space-y-3.5 text-right">
                <div>
                    <label class="label text-xs" for="modal_customer_name">ناوی کڕیار <span class="text-red-500">*</span></label>
                    <input id="modal_customer_name" x-model="newCustomer.name" class="field text-sm font-bold w-full" placeholder="ناوی تەواوی کڕیار بنووسە..." @keydown.enter.prevent="saveQuickCustomer()">
                </div>
                <div>
                    <label class="label text-xs" for="modal_customer_phone">ژمارەی مۆبایل</label>
                    <input id="modal_customer_phone" x-model="newCustomer.phone" class="field num text-sm w-full" dir="ltr" placeholder="0750..." @keydown.enter.prevent="saveQuickCustomer()">
                </div>

                <div x-show="customerModalError" class="text-xs text-rose-600 font-semibold bg-rose-50 p-2.5 rounded-lg border border-rose-200" x-text="customerModalError"></div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="showCustomerModal = false" class="btn btn-ghost !py-1.5 text-xs">پاشگەزبوونەوە</button>
                    <button type="button" @click="saveQuickCustomer()" :disabled="savingCustomer" class="btn btn-primary !py-1.5 !px-5 text-xs font-bold shadow-sm">
                        <span x-show="!savingCustomer">تۆمارکردن</span>
                        <span x-show="savingCustomer">تۆماردەکرێت...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

@push('scripts')
<script>
function orderForm(initialLines, initialDiscount, initialCurrency, customerDiscounts, initialCustomersList) {
    return {
        lines: initialLines,
        discountAmount: initialDiscount ? (typeof initialDiscount === 'number' ? initialDiscount.toLocaleString('en-US') : initialDiscount) : '',
        currency: initialCurrency,
        customerId: '{{ old('customer_id', $order->customer_id) }}',
        customerDiscounts: customerDiscounts,
        customersList: initialCustomersList,
        showCustomerModal: false,
        savingCustomer: false,
        customerModalError: '',
        newCustomer: { name: '', phone: '' },
        prepaid: '{{ old('prepaid_amount', $order->exists ? ($order->prepaid_amount ? number_format($order->prepaid_amount) : '') : '') }}',
        prepaidManuallySet: {{ ($order->exists || old('prepaid_amount') !== null) ? 'true' : 'false' }},
        exchangeRate: '{{ (float) old('exchange_rate', $order->exchange_rate ?: ($rate ?: 150000)) }}',
        fetchingRate: false,

        formatDiscountInput(e) {
            let clean = e.target.value.replace(/[^0-9.]/g, '');
            let parts = clean.split('.');
            if (parts.length > 2) parts = [parts[0], parts.slice(1).join('')];
            let int = parts[0] ? parseInt(parts[0], 10).toLocaleString('en-US') : '';
            let dec = parts.length > 1 ? '.' + parts[1] : '';
            this.discountAmount = int ? int + dec : '';
        },

        get cleanDiscount() {
            return parseFloat((this.discountAmount || '0').toString().replace(/,/g, '')) || 0;
        },

        formatPrepaidInput(e) {
            let clean = e.target.value.replace(/[^0-9.]/g, '');
            let parts = clean.split('.');
            if (parts.length > 2) parts = [parts[0], parts.slice(1).join('')];
            let int = parts[0] ? parseInt(parts[0], 10).toLocaleString('en-US') : '';
            let dec = parts.length > 1 ? '.' + parts[1] : '';
            this.prepaid = int ? int + dec : '';
            this.prepaidManuallySet = true;
        },

        get cleanPrepaid() {
            return parseFloat((this.prepaid || '0').toString().replace(/,/g, '')) || 0;
        },

        openCustomerModal() {
            this.newCustomer = { name: '', phone: '' };
            this.customerModalError = '';
            this.showCustomerModal = true;
            this.$nextTick(() => {
                const el = document.getElementById('modal_customer_name');
                if (el) el.focus();
            });
        },

        onCustomerSelectChange(e) {
            if (this.customerId === '__NEW__') {
                this.customerId = '';
                this.openCustomerModal();
            } else {
                this.applyCustomerDiscount();
            }
        },

        saveQuickCustomer() {
            if (!this.newCustomer.name.trim()) {
                this.customerModalError = 'تکایە ناوی کڕیار بنووسە.';
                return;
            }
            this.savingCustomer = true;
            this.customerModalError = '';

            fetch('{{ route('customers.quick') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
                body: JSON.stringify(this.newCustomer)
            })
            .then(res => res.json())
            .then(data => {
                if (data.ok && data.customer) {
                    this.customersList.unshift(data.customer);
                    this.customerId = data.customer.id;
                    this.customerDiscounts[data.customer.id] = data.customer.discount_percent;
                    this.applyCustomerDiscount();
                    this.showCustomerModal = false;
                } else {
                    this.customerModalError = data.message || 'هەڵەیەک ڕوویدا لە کاتی تۆمارکردن.';
                }
            })
            .catch(() => {
                this.customerModalError = 'نەتوانرا پەیوەندی بە سێرڤەرەوە بکرێت.';
            })
            .finally(() => {
                this.savingCustomer = false;
            });
        },

        init() {
            if (!this.prepaidManuallySet) {
                this.prepaid = this.total ? this.total.toLocaleString('en-US') : '';
            }
            if (this.currency === 'USD') {
                this.fetchLiveRate();
            }
            this.$watch('currency', (val) => {
                if (val === 'USD') {
                    this.fetchLiveRate();
                }
            });
            this.$watch('lines', () => {
                if (!this.prepaidManuallySet) {
                    this.$nextTick(() => { this.prepaid = this.total ? this.total.toLocaleString('en-US') : ''; });
                }
            }, { deep: true });
            this.$watch('discountAmount', () => {
                if (!this.prepaidManuallySet) {
                    this.$nextTick(() => { this.prepaid = this.total ? this.total.toLocaleString('en-US') : ''; });
                }
            });
        },

        onPrepaidInput() {
            this.prepaidManuallySet = true;
        },

        setFullPaid() {
            this.prepaid = this.total;
            this.prepaidManuallySet = true;
        },

        setZeroPaid() {
            this.prepaid = 0;
            this.prepaidManuallySet = true;
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
        },

        addLine() {
            this.lines.push({
                description: '', image: '', preview: null, unit_price: '', note: '',
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
            if (this.lines.length > 1) {
                this.lines.splice(index, 1);
            } else {
                this.lines[0] = { description: '', image: '', preview: null, unit_price: '', note: '' };
            }
        },

        applyCustomerDiscount() {
            const percent = this.customerDiscounts[this.customerId];
            if (percent && parseFloat(percent) > 0 && this.subtotal > 0) {
                const calculated = Math.round(this.subtotal * parseFloat(percent) / 100);
                this.discountAmount = calculated > 0 ? calculated.toLocaleString('en-US') : '';
            }
        },

        formatInput(e, line) {
            let clean = e.target.value.replace(/[^0-9.]/g, '');
            let parts = clean.split('.');
            if (parts.length > 2) parts = [parts[0], parts.slice(1).join('')];
            let int = parts[0] ? parseInt(parts[0], 10).toLocaleString('en-US') : '';
            let dec = parts.length > 1 ? '.' + parts[1] : '';
            line.unit_price = int ? int + dec : '';
        },

        linePrice(line) {
            let p = parseFloat(line.unit_price.toString().replace(/,/g, '')) || 0;
            return p;
        },

        get subtotal() {
            return this.lines.reduce((sum, line) => sum + this.linePrice(line), 0);
        },

        get total() {
            return Math.max(0, this.subtotal - this.cleanDiscount);
        },

        get remaining() {
            return this.total - this.cleanPrepaid;
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
