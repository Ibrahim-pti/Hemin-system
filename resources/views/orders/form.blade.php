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
          @js((float) old('discount_percent', $order->discount_percent ?: 0)),
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

    {{-- ١. زانیاری سەرەکی کڕیار و بەروار --}}
    <div class="card">
        <div class="card-head flex items-center justify-between">
            <span>زانیاری وەسڵ و کڕیار</span>
            <a href="{{ route('customers.index') }}" class="btn btn-ghost !py-1 text-xs">گەڕانەوە &larr;</a>
        </div>
        <div class="card-body grid gap-4 sm:grid-cols-3">

            {{-- بەڕێز (کڕیار) --}}
            <div class="sm:col-span-2">
                <label class="label" for="customer_id">بەڕێز (کڕیار) <span class="text-[--color-danger]">*</span></label>
                <select id="customer_id" name="customer_id" class="field font-bold w-full" required
                        x-model="customerId" @change="onCustomerSelectChange($event)">
                    <option value="">— هەڵبژێرە —</option>
                    <option value="__NEW__" class="font-bold text-blue-600 bg-blue-50">➕ زیادکردنی کڕیاری نوێ</option>
                    <template x-for="c in customersList" :key="c.id">
                        <option :value="c.id" x-text="c.name + (c.phone ? ' — ' + c.phone : '')" :selected="c.id == customerId"></option>
                    </template>
                </select>
            </div>

            {{-- بەروار --}}
            <div class="sm:col-span-1">
                <label class="label" for="order_date">بەروار <span class="text-[--color-danger]">*</span></label>
                <input id="order_date" name="order_date" type="date" class="field num" required
                       value="{{ old('order_date', $order->order_date?->toDateString() ?? now()->toDateString()) }}">
            </div>

            {{-- تێبینی --}}
            <div class="sm:col-span-3">
                <label class="label" for="note">تێبینی</label>
                <input id="note" name="note" class="field" value="{{ old('note', $order->note) }}" placeholder="تێبینی گشتی وەسڵ...">
            </div>
        </div>
    </div>

    {{-- ٢. خشتەی شتەکان (تەنها وێنە، ناوەڕۆک/شتەکە، نرخ) لەگەڵ هەڵبژاردنی دراو لە سەرەوەی خشتەکە --}}
    <div class="card mt-4">
        <div class="card-head flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <span class="font-bold text-slate-800 text-sm">ناوەڕۆکی شتە داواکراوەکان</span>

                {{-- هەڵبژاردنی دراو لە تەنیشت ناوەڕۆک --}}
                <div class="flex items-center gap-2 bg-slate-100/90 px-3 py-1 rounded-lg border border-slate-200">
                    <span class="text-xs font-bold text-slate-600">دراو:</span>
                    <select id="currency" name="currency" class="bg-white border border-slate-300 rounded px-2 py-0.5 text-xs font-bold text-slate-800 cursor-pointer outline-none focus:ring-1 focus:ring-blue-500" x-model="currency">
                        <option value="IQD">دینار (IQD)</option>
                        <option value="USD">دۆلار ($ USD)</option>
                    </select>

                    {{-- نرخی دۆلار ئەگەر دۆلار بێت --}}
                    <div x-show="currency === 'USD'" x-cloak class="flex items-center gap-1.5 mr-2">
                        <span class="text-xs text-slate-500 font-medium">نرخی ١٠٠$:</span>
                        <div class="inline-flex items-center gap-1 bg-white rounded border border-slate-300 px-1 py-0.5">
                            <input id="exchange_rate" name="exchange_rate" type="text" class="field num !py-0 !px-1 w-24 text-xs font-bold border-0 focus:ring-0"
                                   x-model="exchangeRate" placeholder="150,000">
                            <button type="button" @click="fetchLiveRate()"
                                    :disabled="fetchingRate"
                                    class="text-slate-400 hover:text-blue-600 p-0.5 rounded transition-all"
                                    title="وەرگرتنی نرخی ئەمڕۆ لە ئینتەرنێت (Live API)">
                                <svg class="size-3.5" :class="fetchingRate && 'animate-spin text-blue-600'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="table w-full">
                <thead>
                    <tr class="bg-slate-50/80 text-xs text-slate-700 font-bold border-b border-[--color-line]">
                        <th style="width: 44px; text-align: center; padding: 10px 6px;">#</th>
                        <th style="width: 60px; text-align: center; padding: 10px 6px;">وێنە</th>
                        <th style="text-align: right; padding: 10px 12px;">ناوەڕۆک / شتەکە (وەک دەرگا، مەحەجەرە...)</th>
                        <th style="width: 220px; text-align: center; padding: 10px 12px;">نرخ (<span x-text="currency === 'USD' ? '$' : 'د.ع'"></span>)</th>
                        <th style="width: 44px; text-align: center; padding: 10px 6px;"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    <template x-for="(line, index) in lines" :key="index">
                        <tr>
                            {{-- # --}}
                            <td style="text-align: center; padding: 6px;" class="text-xs text-slate-400 font-bold" x-text="index + 1"></td>

                            {{-- وێنەی کاڵا / دیزاین --}}
                            <td style="text-align: center; padding: 4px;">
                                <div class="flex items-center justify-center">
                                    <input type="file"
                                           :name="`lines[${index}][image]`"
                                           :id="`order_line_image_${index}`"
                                           accept="image/*"
                                           class="hidden"
                                           @change="onImageChange($event, line)">
                                    <input type="hidden" :name="`lines[${index}][existing_image]`" :value="line.image || ''">

                                    <template x-if="line.preview">
                                        <div class="relative group size-9 rounded-lg overflow-hidden border border-blue-400 shadow-2xs shrink-0">
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
                                                class="size-9 rounded-lg border border-dashed border-slate-300 hover:border-blue-500 bg-slate-50 hover:bg-blue-50 text-slate-400 hover:text-blue-600 flex items-center justify-center transition-all shrink-0"
                                                title="دانانی وێنەی دیزاین">
                                            <svg class="size-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                                                <circle cx="8.5" cy="8.5" r="1.5"/>
                                                <polyline points="21 15 16 10 5 21"/>
                                            </svg>
                                        </button>
                                    </template>
                                </div>
                            </td>

                            {{-- ناوەڕۆک / ناوی شتەکە --}}
                            <td style="padding: 6px 12px;">
                                <input :name="`lines[${index}][description]`" x-model="line.description" required
                                       class="field w-full !py-2 !px-3 text-sm bg-white"
                                       placeholder="ناوی شتەکە بنووسە (وەک: دەرگای ئاسنی هەندەسی، مەحەجەرە...)">
                            </td>

                            {{-- نرخ --}}
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
                                <button type="button" @click="removeLine(index)" class="inline-flex items-center justify-center size-7 rounded-lg text-rose-400 hover:text-rose-600 hover:bg-rose-50 transition-colors"
                                        x-show="lines.length > 1" title="سڕینەوە">✕</button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        {{-- دوگمەی زیادکردنی شتی تر --}}
        <div class="p-3 border-t border-[--color-line] bg-slate-50/50">
            <button type="button" @click="addLine()" class="btn btn-ghost !py-1.5 !px-3 text-xs font-bold text-[--color-brand-700] hover:bg-blue-50 border border-dashed border-blue-300">
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
                    <label class="label" for="discount_percent">داشکاندن (٪)</label>
                    <input id="discount_percent" name="discount_percent" type="number" step="0.01" min="0" max="100"
                           class="field num" x-model.number="discountPercent" value="0">
                    <p class="mt-1 text-xs text-[--color-ink-soft]">
                        ئەگەر داشکاندن هەبێت بە ڕێژەی لەسەدا بنووسە.
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
                <div class="flex justify-between items-center" x-show="discountAmount > 0">
                    <span class="text-rose-600">داشکاندن</span>
                    <span class="num font-semibold text-rose-600" x-text="'- ' + money(discountAmount)">0</span>
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
        discountPercent: initialDiscount,
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
            this.$watch('discountPercent', () => {
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
            const discount = this.customerDiscounts[this.customerId];
            this.discountPercent = discount ? parseFloat(discount) : 0;
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

        get discountAmount() {
            return this.subtotal * (parseFloat(this.discountPercent) || 0) / 100;
        },

        get total() {
            return Math.max(0, this.subtotal - this.discountAmount);
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
