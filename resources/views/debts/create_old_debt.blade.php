@extends('layouts.app')
@section('title', 'تۆمارکردنی حیسابی پێشوو')

@section('content')

@php
    $initialCustomerId = old('customer_id', $selectedCustomer?->id ?? '');
    $initialCustomerName = old('new_customer_name', $selectedCustomer?->name ?? '');
    $initialCustomerPhone = old('new_customer_phone', $selectedCustomer?->phone ?? '');
    $initialAmount = old('amount', '');
    $initialPaid = old('paid_amount', '');
    $initialPaymentType = old('status', 'debt');
    $initialCurrency = old('currency', 'IQD');
@endphp

<form method="POST"
      action="{{ route('debts.old-debt') }}"
      enctype="multipart/form-data"
      x-data="oldDebtForm(@js($initialCustomerName), @js($initialCustomerPhone), @js($initialAmount), @js($initialPaid), @js($initialPaymentType), @js($initialCurrency))"
      class="space-y-4">
    @csrf
    <input type="hidden" name="currency" :value="currency">
    <input type="hidden" name="status" :value="paymentType">
    <input type="hidden" name="customer_id" :value="customerId">
    <input type="hidden" name="image_base64" :value="attachmentsList[0]?.base64 || ''">
    <template x-for="(item, idx) in attachmentsList" :key="'b64_' + item.id">
        <input type="hidden" name="attachments_base64[]" :value="item.base64">
    </template>

    @if ($errors->any())
        <div class="card mb-4 border-r-4 !border-r-[--color-danger] px-4 py-3 text-sm">
            <div class="font-bold text-red-700 mb-1">تکایە ئەم هەڵانە چاک بکە:</div>
            <ul class="list-inside list-disc space-y-1 text-red-600 text-xs">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    {{-- ١. زانیاری سەرەکی کڕیار و حیسابی پێشوو لەگەڵ وێنەی وەسڵ --}}
    <div class="card">
        <div class="card-head flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="text-lg">📜</span>
                <span class="font-bold text-slate-800 text-sm">زانیاری کڕیار و حیسابی پێشوو</span>
            </div>
            <a href="{{ url()->previous() ?: route('customers.index') }}" class="btn btn-ghost !py-1 text-xs">گەڕانەوە &larr;</a>
        </div>
        <div class="card-body grid gap-4 sm:grid-cols-2 lg:grid-cols-4">

            {{-- ناوی کڕیار --}}
            <div class="sm:col-span-2">
                <label class="label" for="customer_input">
                    ناوی کڕیار <span class="text-[--color-danger]">*</span>
                </label>
                <input id="customer_input" type="text" list="customers_datalist"
                       class="field font-bold text-sm"
                       x-model="customerName"
                       @input="onCustomerInput($event)"
                       placeholder="ناوی کڕیار بنووسە یان هەڵیبژێرە..." required autocomplete="off">
                <datalist id="customers_datalist">
                    @foreach ($customers as $c)
                        <option data-id="{{ $c->id }}" data-phone="{{ $c->phone }}" value="{{ $c->name }}">
                            {{ $c->phone ? '(' . $c->phone . ')' : '' }}
                        </option>
                    @endforeach
                </datalist>
                <input type="hidden" name="new_customer_name" :value="isNewCustomer ? customerName : ''">
            </div>

            {{-- ژمارەی مۆبایل --}}
            <div>
                <label class="label" for="phone">ژمارەی مۆبایل</label>
                <input id="phone" name="new_customer_phone" type="text"
                       class="field num font-bold text-left" dir="ltr"
                       x-model="customerPhone"
                       placeholder="0750XXXXXXX">
            </div>

            {{-- بەرواری حیساب --}}
            <div>
                <label class="label" for="date">
                    بەرواری حیساب <span class="text-[--color-danger]">*</span>
                </label>
                <input id="date" name="date" type="date" class="field num font-bold" required
                       value="{{ old('date', now()->toDateString()) }}">
            </div>

            {{-- هەڵبژاردنی دراو (دینار یان دۆلار) --}}
            <div>
                <label class="label">
                    دراوی حیساب <span class="text-[--color-danger]">*</span>
                </label>
                <div class="grid grid-cols-2 gap-1.5 p-1 bg-slate-100 rounded-xl border border-slate-200 text-xs font-bold">
                    <button type="button" @click="setCurrency('IQD')"
                            :class="currency === 'IQD' ? 'bg-white text-teal-800 shadow-xs ring-1 ring-slate-200' : 'text-slate-600 hover:text-slate-900'"
                            class="py-2 px-2 rounded-lg text-center transition-all cursor-pointer flex items-center justify-center gap-1.5">
                        <span class="text-sm">🇮🇶</span>
                        <span>دینار (IQD)</span>
                    </button>
                    <button type="button" @click="setCurrency('USD')"
                            :class="currency === 'USD' ? 'bg-white text-teal-800 shadow-xs ring-1 ring-slate-200' : 'text-slate-600 hover:text-slate-900'"
                            class="py-2 px-2 rounded-lg text-center transition-all cursor-pointer flex items-center justify-center gap-1.5">
                        <span class="text-sm">💵</span>
                        <span>دۆلار ($ USD)</span>
                    </button>
                </div>
            </div>

            {{-- تێبینی --}}
            <div class="sm:col-span-2 lg:col-span-3">
                <label class="label" for="note">تێبینی / هۆکاری حیسابەکە</label>
                <input id="note" name="note" type="text" class="field"
                       placeholder="تێبینی، قەرزی ساڵی پار لەسەر کاغەز، لاپەڕەی دەفتەر..."
                       value="{{ old('note') }}">
            </div>

            {{-- وێنە یان فایلی وەسڵ / دەفتەری حیسابات (چەندین وێنە و چەندین فایلی PDF پێکەوە) --}}
            <div class="sm:col-span-2 lg:col-span-4 bg-slate-50/80 p-3.5 rounded-2xl border border-dashed border-slate-300">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-2">
                    <div class="flex items-center gap-2.5">
                        <span class="text-2xl">📑</span>
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="block text-xs font-bold text-slate-800">وێنە و فایلی وەسڵ / دەفتەری حیسابات</span>
                                <template x-if="attachmentsList.length > 0">
                                    <span class="px-2 py-0.5 rounded-full text-[11px] font-black bg-emerald-100 text-emerald-800 border border-emerald-300"
                                          x-text="attachmentsList.length + ' فایل هەڵبژێردراوە'"></span>
                                </template>
                            </div>
                            <span class="block text-[11px] text-slate-500">دەتوانیت چەندین وێنە و چەندین فایلی PDF پێکەوە هەڵبژێریت یان بە کامێرا بگریت.</span>
                        </div>
                    </div>

                    {{-- دوگمەکانی کامێرا، هەڵبژاردنی وێنەکان، و PDF --}}
                    <div class="flex flex-wrap items-center gap-2">
                        {{-- فایل ئینپووتی کامێرا --}}
                        <input type="file" id="old_debt_image_camera" accept="image/*" capture="environment" class="sr-only" @change="onFilesAdded($event, 'camera')">
                        {{-- فایل ئینپووتی ستۆدیۆ (multiple images) --}}
                        <input type="file" id="old_debt_image_input" accept="image/*" multiple class="sr-only" @change="onFilesAdded($event, 'gallery')">
                        {{-- فایل ئینپووتی PDF (multiple PDFs) --}}
                        <input type="file" id="old_debt_pdf_input" accept="application/pdf" multiple class="sr-only" @change="onFilesAdded($event, 'pdf')">
                        {{-- ئینپووتی سەرەکی فۆڕم بۆ ناردن --}}
                        <input type="file" id="old_debt_form_attachments" name="attachments[]" multiple class="sr-only">

                        <label for="old_debt_image_camera"
                               class="px-3 py-1.5 rounded-xl text-xs font-black bg-white hover:bg-amber-50 text-amber-900 border border-amber-500/40 shadow-2xs flex items-center gap-1.5 transition-all cursor-pointer active:scale-95"
                               title="گرتنی وێنەی نوێ بە کامێرا">
                            <span class="text-base">📸</span>
                            <span>دانانی وێنەی وەسڵەکە (کامێرا)</span>
                        </label>

                        <label for="old_debt_image_input"
                               class="px-3 py-1.5 rounded-xl text-xs font-black bg-white hover:bg-slate-100 text-slate-700 border border-slate-300 shadow-2xs flex items-center gap-1.5 transition-all cursor-pointer active:scale-95"
                               title="هەڵبژاردنی یەک یان چەندین وێنە لە ستۆدیۆ">
                            <span class="text-base">🖼️</span>
                            <span>هەڵبژاردن لە مۆبایل</span>
                        </label>

                        <label for="old_debt_pdf_input"
                               class="px-3 py-1.5 rounded-xl text-xs font-black bg-white hover:bg-rose-50 text-rose-800 border border-rose-300 shadow-2xs flex items-center gap-1.5 transition-all cursor-pointer active:scale-95"
                               title="هەڵبژاردنی یەک یان چەندین فایلی PDF">
                            <span class="text-base">📄</span>
                            <span>+ فایلی PDF</span>
                        </label>

                        <template x-if="attachmentsList.length > 0">
                            <button type="button" @click="clearAllAttachments()" class="px-2.5 py-1.5 rounded-xl text-xs font-bold text-rose-600 bg-rose-50 hover:bg-rose-100 border border-rose-200 transition-all cursor-pointer">
                                ✕ سڕینەوەی هەمووی
                            </button>
                        </template>
                    </div>
                </div>

                {{-- خشتەی پیشاندانی هەموو وێنە و فایلە هەڵبژێردراوەکان --}}
                <template x-if="attachmentsList.length > 0">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2.5 pt-2.5 border-t border-slate-200/80 mt-2">
                        <template x-for="(item, idx) in attachmentsList" :key="item.id">
                            <div class="flex items-center justify-between gap-2.5 bg-white p-2 rounded-xl border border-slate-200 shadow-2xs hover:border-blue-400 transition-all">
                                <div class="flex items-center gap-2 min-w-0">
                                    {{-- ئەگەر وێنە بێت --}}
                                    <template x-if="!item.isPdf">
                                        <div class="relative size-11 rounded-lg overflow-hidden border border-slate-200 group shrink-0 cursor-pointer" @click="openAttachmentPreview(item)" title="کلیک بکە بۆ بینینی گەورە">
                                            <img :src="item.previewUrl" class="size-full object-cover group-hover:scale-110 transition-transform">
                                        </div>
                                    </template>
                                    {{-- ئەگەر PDF بێت --}}
                                    <template x-if="item.isPdf">
                                        <div @click="openAttachmentPreview(item)" class="relative size-11 rounded-lg bg-rose-50 border border-rose-300 flex flex-col items-center justify-center cursor-pointer hover:bg-rose-100 transition-colors shrink-0" title="کلیک بکە بۆ کردنەوەی فایلی PDF">
                                            <span class="text-base">📄</span>
                                            <span class="text-[8px] font-black text-rose-700 uppercase">PDF</span>
                                        </div>
                                    </template>

                                    <div class="min-w-0">
                                        <div class="text-xs font-bold text-slate-800 truncate max-w-[150px]" x-text="item.name" :title="item.name"></div>
                                        <div class="text-[10px] font-mono text-slate-400" x-text="item.size"></div>
                                    </div>
                                </div>

                                <div class="flex items-center gap-1 shrink-0">
                                    <button type="button" @click="openAttachmentPreview(item)" class="p-1 rounded-md text-slate-500 hover:text-blue-600 hover:bg-blue-50 transition-colors cursor-pointer" title="پیشاندان">
                                        👁️
                                    </button>
                                    <button type="button" @click="removeAttachment(idx)" class="p-1 rounded-md text-rose-500 hover:text-rose-700 hover:bg-rose-50 transition-colors cursor-pointer" title="سڕینەوەی ئەم فایلە">
                                        ✕
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>
            </div>
        </div>
    </div>

    {{-- ٢. بڕی پارە و شێوازی پارەدان و پوختەی کۆتایی --}}
    <div class="grid gap-4 lg:grid-cols-3">
        {{-- بڕی حیساب و شێوازی پارەدان --}}
        <div class="card lg:col-span-2">
            <div class="card-head flex items-center justify-between">
                <span class="font-bold text-slate-800 text-sm">بڕی حیساب و شێوازی پارەدان</span>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold transition-all"
                      :class="currency === 'USD' ? 'bg-amber-100 text-amber-800 border border-amber-200' : 'bg-blue-50 text-blue-700 border border-blue-200'"
                      x-text="currency === 'USD' ? 'بە دۆلار ($ USD)' : 'بە دینار (IQD)'"></span>
            </div>

            <div class="card-body space-y-4">
                {{-- بڕی گشتی حیسابەکە --}}
                <div>
                    <label class="label font-bold text-xs text-slate-800 mb-1" for="amount_field">
                        بڕی گشتی حیسابەکە (<span x-text="currencySymbol()"></span>) <span class="text-[--color-danger]">*</span>
                    </label>
                    <div class="relative">
                        <input id="amount_field" name="amount" type="text" inputmode="numeric" required
                               class="field num font-black text-xl text-slate-900 w-full !py-3 !px-4"
                               dir="ltr"
                               x-model="rawAmount"
                               @input="formatAmount($event)"
                               placeholder="0">
                    </div>
                </div>

                {{-- هەڵبژاردنی شێوازی پارەدان: حازری، بە قەرز، بەشێکی دراوە --}}
                <div>
                    <label class="label font-bold text-xs text-slate-800 mb-1.5">دۆخی پارەدان:</label>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 sm:gap-3">
                        {{-- حازری (نەقد) / پارەدانی تەواو بووە --}}
                        <button type="button" @click="setPaymentType('paid')"
                                :class="paymentType === 'paid' ? 'bg-emerald-600 text-white shadow-sm ring-2 ring-emerald-600/30' : 'bg-slate-50 hover:bg-slate-100 text-slate-700 border border-slate-200'"
                                class="py-2.5 px-3 rounded-xl font-bold text-xs text-center transition-all cursor-pointer flex flex-row sm:flex-col items-center justify-between sm:justify-center gap-2 sm:gap-1">
                            <div class="flex items-center gap-2">
                                <span class="text-base">💵</span>
                                <span>حازری (پارەدراو)</span>
                            </div>
                            <span class="text-[10px] font-normal" :class="paymentType === 'paid' ? 'text-emerald-100' : 'text-slate-400'">تەواوی پارەکە دراوە</span>
                        </button>

                        {{-- بە قەرز --}}
                        <button type="button" @click="setPaymentType('debt')"
                                :class="paymentType === 'debt' ? 'bg-rose-600 text-white shadow-sm ring-2 ring-rose-600/30' : 'bg-slate-50 hover:bg-slate-100 text-slate-700 border border-slate-200'"
                                class="py-2.5 px-3 rounded-xl font-bold text-xs text-center transition-all cursor-pointer flex flex-row sm:flex-col items-center justify-between sm:justify-center gap-2 sm:gap-1">
                            <div class="flex items-center gap-2">
                                <span class="text-base">⏳</span>
                                <span>بە قەرز (نەدراوە)</span>
                            </div>
                            <span class="text-[10px] font-normal" :class="paymentType === 'debt' ? 'text-rose-100' : 'text-slate-400'">پارە نەدراوە (قەرز)</span>
                        </button>

                        {{-- بەشێکی دراوە --}}
                        <button type="button" @click="setPaymentType('partial')"
                                :class="paymentType === 'partial' ? 'bg-amber-500 text-white shadow-sm ring-2 ring-amber-500/30' : 'bg-slate-50 hover:bg-slate-100 text-slate-700 border border-slate-200'"
                                class="py-2.5 px-3 rounded-xl font-bold text-xs text-center transition-all cursor-pointer flex flex-row sm:flex-col items-center justify-between sm:justify-center gap-2 sm:gap-1">
                            <div class="flex items-center gap-2">
                                <span class="text-base">⚖️</span>
                                <span>بەشێکی دراوە</span>
                            </div>
                            <span class="text-[10px] font-normal" :class="paymentType === 'partial' ? 'text-amber-100' : 'text-slate-400'">نیوە قەرز / پێشەکی</span>
                        </button>
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-100">
                    {{-- بڕی پارەی دراو ئەگەر بەشێکی دراوە بوو --}}
                    <div x-show="paymentType === 'partial'" x-transition class="max-w-md">
                        <label class="label text-xs font-bold text-amber-800" for="paid_amount">
                            بڕی پارەی دراو ئێستا (<span x-text="currencySymbol()"></span>)
                        </label>
                        <input id="paid_amount" name="paid_amount" type="text" inputmode="numeric"
                               class="field num font-bold text-amber-800 w-full"
                               dir="ltr"
                               x-model="paid"
                               @input="formatPaid($event)"
                               placeholder="0">
                    </div>

                    {{-- پەیامی ڕوونکردنەوە --}}
                    <div x-show="paymentType === 'paid'" class="bg-emerald-50 text-emerald-800 border border-emerald-200 rounded-xl p-3 text-xs font-bold flex items-center gap-2">
                        <span>✓</span>
                        <span>ئەم حیسابە بە پارەدراوی تەواو تۆمار دەکرێت و هیچ قەرزێک لە ئەستۆی کڕیاردا نامێنێتەوە.</span>
                    </div>

                    <div x-show="paymentType === 'debt'" class="bg-rose-50 text-rose-800 border border-rose-200 rounded-xl p-3 text-xs font-bold flex items-center gap-2">
                        <span>⚠️</span>
                        <span>تەواوی ئەم بڕە وەک حیسابی پێشوو لە ئەستۆی کڕیاردا تۆمار دەکرێت.</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- کارتی پوختەی کۆی گشتی و ماوە --}}
        <div class="card h-fit">
            <div class="card-head">
                <span class="font-bold text-slate-800 text-sm">پوختەی حیسابات</span>
            </div>
            <div class="card-body space-y-3 text-sm">
                <div class="flex justify-between items-center text-base font-bold text-slate-900">
                    <span>کۆی گشتی حیساب</span>
                    <span class="num text-lg font-black" x-text="money(total())">0 د.ع</span>
                </div>

                <div class="flex justify-between items-center text-emerald-700 font-semibold" x-show="cleanNum(paid) > 0">
                    <span>پارەی دراو</span>
                    <span class="num font-bold" x-text="money(cleanNum(paid))">0 د.ع</span>
                </div>

                <div class="flex justify-between items-center font-bold border-t border-slate-100 pt-2.5 text-base"
                     :class="remaining() > 0 ? 'text-[--color-danger]' : 'text-emerald-600'">
                    <span>ماوە (قەرز)</span>
                    <span class="num font-black text-xl" x-text="money(remaining())">0 د.ع</span>
                </div>

                <div class="pt-2 text-[11px] text-slate-500 font-medium">
                    ئەم حیسابە بە شێوەیەکی ڕاستەوخۆ لە ئەستۆی کڕیاردا دادەنرێت و وەسڵی دروستکردنی لە کارگە بۆ دەرناچێت.
                </div>
            </div>
        </div>
    </div>

    {{-- ٣. دوگمەکانی خوارەوە --}}
    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 pt-2">
        <button type="submit" class="btn btn-primary !py-3 sm:!py-2.5 !px-8 text-sm font-bold shadow-sm bg-blue-600 hover:bg-blue-700 cursor-pointer w-full sm:w-auto">
            ✓ تۆمارکردنی حیسابی پێشوو
        </button>

        <a href="{{ url()->previous() ?: route('customers.index') }}" class="btn btn-ghost w-full sm:w-auto text-center">پاشگەزبوونەوە</a>
    </div>
</form>

<script>
function oldDebtForm(initCustomerName, initCustomerPhone, initAmount, initPaid, initPaymentType, initCurrency) {
    const customersMap = {
        @foreach ($customers as $c)
            @js($c->name): { id: @js($c->id), phone: @js($c->phone) },
        @endforeach
    };

    return {
        customerName: initCustomerName || '',
        customerPhone: initCustomerPhone || '',
        customerId: customersMap[initCustomerName] ? customersMap[initCustomerName].id : '',
        isNewCustomer: !customersMap[initCustomerName],
        rawAmount: initAmount ? Number(initAmount).toLocaleString() : '',
        paid: initPaid ? Number(initPaid).toLocaleString() : '',
        paymentType: initPaymentType || 'debt',
        currency: initCurrency || 'IQD',
        attachmentsList: [],
        fileSelected: false,
        filePreview: null,
        imagePreview: null,
        imageBase64: '',
        isPdf: false,
        fileName: '',
        fileSize: '',

        onCustomerInput(e) {
            const val = e.target.value.trim();
            if (customersMap[val]) {
                this.customerId = customersMap[val].id;
                this.customerPhone = customersMap[val].phone || this.customerPhone;
                this.isNewCustomer = false;
            } else {
                this.customerId = '';
                this.isNewCustomer = true;
            }
        },

        setCurrency(c) {
            this.currency = c;
        },

        currencySymbol() {
            return this.currency === 'USD' ? '$' : 'د.ع';
        },

        setPaymentType(type) {
            this.paymentType = type;
            if (type === 'paid') {
                this.paid = this.rawAmount;
            } else if (type === 'debt') {
                this.paid = '0';
            }
        },

        cleanNum(val) {
            if (!val) return 0;
            const cleaned = String(val).replace(/,/g, '').trim();
            const n = parseFloat(cleaned);
            return isNaN(n) ? 0 : n;
        },

        total() {
            return this.cleanNum(this.rawAmount);
        },

        remaining() {
            if (this.paymentType === 'paid') return 0;
            if (this.paymentType === 'debt') return this.total();
            return Math.max(0, this.total() - this.cleanNum(this.paid));
        },

        money(val) {
            return Number(val).toLocaleString() + ' ' + this.currencySymbol();
        },

        formatAmount(e) {
            const raw = e.target.value.replace(/,/g, '').replace(/[^\d.]/g, '');
            if (!raw) {
                this.rawAmount = '';
                if (this.paymentType === 'paid') this.paid = '';
                return;
            }
            const parts = raw.split('.');
            parts[0] = Number(parts[0]).toLocaleString();
            this.rawAmount = parts.join('.');
            if (this.paymentType === 'paid') {
                this.paid = this.rawAmount;
            }
        },

        formatPaid(e) {
            const raw = e.target.value.replace(/,/g, '').replace(/[^\d.]/g, '');
            if (!raw) {
                this.paid = '';
                return;
            }
            const parts = raw.split('.');
            parts[0] = Number(parts[0]).toLocaleString();
            this.paid = parts.join('.');
        },

        onFilesAdded(e, source) {
            const files = e.target.files;
            if (!files || files.length === 0) return;

            Array.from(files).forEach((file) => {
                const isPdf = file.type === 'application/pdf' || file.name.toLowerCase().endsWith('.pdf');
                const sizeKb = file.size / 1024;
                const formattedSize = sizeKb > 1024 ? (sizeKb / 1024).toFixed(1) + ' MB' : Math.round(sizeKb) + ' KB';
                const item = {
                    id: Date.now() + '_' + Math.random().toString(36).substr(2, 9),
                    file: file,
                    name: file.name,
                    size: formattedSize,
                    isPdf: isPdf,
                    previewUrl: '',
                    base64: ''
                };

                const reader = new FileReader();
                reader.onload = (ev) => {
                    item.previewUrl = ev.target.result;
                    if (!isPdf) {
                        item.base64 = ev.target.result;
                        this.compressAttachment(item);
                    }
                };
                reader.readAsDataURL(file);

                this.attachmentsList.push(item);
            });

            e.target.value = '';
            this.syncFilesToForm();
        },

        compressAttachment(item) {
            try {
                const img = new Image();
                img.onload = () => {
                    const maxDim = 1920;
                    let w = img.width, h = img.height;
                    if (w > maxDim || h > maxDim) {
                        if (w > h) { h = Math.round((h * maxDim) / w); w = maxDim; }
                        else { w = Math.round((w * maxDim) / h); h = maxDim; }
                    }
                    const canvas = document.createElement('canvas');
                    canvas.width = w;
                    canvas.height = h;
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(img, 0, 0, w, h);
                    item.base64 = canvas.toDataURL('image/jpeg', 0.85);

                    canvas.toBlob((blob) => {
                        if (blob) {
                            const newSizeKb = blob.size / 1024;
                            item.size = newSizeKb > 1024 ? (newSizeKb / 1024).toFixed(1) + ' MB' : Math.round(newSizeKb) + ' KB';
                            const cleanName = (item.name || 'receipt').replace(/\.[^/.]+$/, "") + ".jpg";
                            item.file = new File([blob], cleanName, { type: 'image/jpeg', lastModified: Date.now() });
                            this.syncFilesToForm();
                        }
                    }, 'image/jpeg', 0.85);
                };
                img.src = item.previewUrl;
            } catch (err) {
                console.warn('Compress error:', err);
            }
        },

        syncFilesToForm() {
            try {
                const formInput = document.getElementById('old_debt_form_attachments');
                if (formInput && window.DataTransfer) {
                    const dt = new DataTransfer();
                    this.attachmentsList.forEach((att) => {
                        if (att.file) dt.items.add(att.file);
                    });
                    formInput.files = dt.files;
                }
            } catch (e) {}
        },

        removeAttachment(index) {
            this.attachmentsList.splice(index, 1);
            this.syncFilesToForm();
        },

        clearAllAttachments() {
            this.attachmentsList = [];
            this.syncFilesToForm();
        },

        openAttachmentPreview(item) {
            if (!item.previewUrl) return;
            const win = window.open();
            if (win) {
                if (item.isPdf) {
                    win.document.write('<!DOCTYPE html><html><head><title>' + (item.name || 'PDF') + '</title><style>html,body{margin:0;padding:0;height:100%;overflow:hidden;background:#333;}</style></head><body><iframe src="' + item.previewUrl + '" frameborder="0" style="border:0;width:100%;height:100%;" allowfullscreen></iframe></body></html>');
                } else {
                    win.document.write('<!DOCTYPE html><html><head><title>' + (item.name || 'Image') + '</title><style>body{margin:0;padding:20px;display:flex;align-items:center;justify-content:center;min-height:90vh;background:#0f172a;}img{max-width:95vw;max-height:90vh;object-fit:contain;border-radius:10px;box-shadow:0 10px 25px rgba(0,0,0,0.5);}</style></head><body><img src="' + item.previewUrl + '"></body></html>');
                }
            }
        },

        onImageChange(e, source) {
            this.onFilesAdded(e, source);
        },

        removeFile() {
            this.clearAllAttachments();
        },

        removeImage() {
            this.clearAllAttachments();
        }
    };
}
</script>

@endsection
