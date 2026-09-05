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

            {{-- وێنەی وەسڵ / دەفتەری حیسابات (فایل یان کامێرا) --}}
            <div class="sm:col-span-2 lg:col-span-4 bg-slate-50/80 p-3.5 rounded-2xl border border-dashed border-slate-300">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-center gap-2.5">
                        <span class="text-2xl">📸</span>
                        <div>
                            <span class="block text-xs font-bold text-slate-800">وێنەی وەسڵ / دەفتەری حیسابات</span>
                            <span class="block text-[11px] text-slate-500">دەتوانیت وێنەی وەسڵەکە یان لاپەڕەی دەفتەر بە کامێرا بگریت یان فایلەکەی دابنێیت.</span>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <input type="file" id="old_debt_image_input" name="image" accept="image/*" class="hidden" @change="onImageChange($event)">

                        <template x-if="imagePreview">
                            <div class="flex items-center gap-2">
                                <div class="relative size-12 rounded-xl overflow-hidden border-2 border-amber-600 shadow-xs group">
                                    <img :src="imagePreview" class="size-full object-cover cursor-pointer hover:scale-110 transition-transform" @click="window.open(imagePreview, '_blank')" title="کلیک بکە بۆ بینینی تەواوی وێنەکە">
                                </div>
                                <button type="button" @click="removeImage()" class="btn btn-ghost !py-1 !px-2.5 text-xs text-rose-600 border border-rose-200 hover:bg-rose-50 cursor-pointer">
                                    لابردنی وێنە
                                </button>
                                <button type="button" @click="document.getElementById('old_debt_image_input').click()" class="btn btn-ghost !py-1 !px-2.5 text-xs text-slate-700 bg-white border border-slate-200 cursor-pointer">
                                    گۆڕینی وێنە
                                </button>
                            </div>
                        </template>

                        <template x-if="!imagePreview">
                            <button type="button" @click="document.getElementById('old_debt_image_input').click()"
                                    class="px-4 py-2 rounded-xl text-xs font-black bg-white hover:bg-amber-50 text-amber-900 border border-amber-500/40 shadow-2xs flex items-center gap-1.5 transition-all cursor-pointer">
                                <span>📷</span>
                                <span>دانانی وێنەی وەسڵەکە</span>
                            </button>
                        </template>
                    </div>
                </div>
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
                        <span>تەواوی ئەم بڕە وەک قەرزی پێشوو لە ئەستۆی کڕیاردا تۆمار دەکرێت.</span>
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
            ✓ تۆمارکردنی حیساباتی پێشوو
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
        imagePreview: null,

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

        onImageChange(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (ev) => {
                    this.imagePreview = ev.target.result;
                };
                reader.readAsDataURL(file);
            }
        },

        removeImage() {
            this.imagePreview = null;
            const input = document.getElementById('old_debt_image_input');
            if (input) input.value = '';
        }
    };
}
</script>

@endsection
