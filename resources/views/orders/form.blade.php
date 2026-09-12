@extends('layouts.app')
@section('title', $order->exists ? 'دەستکاری وەسڵ ' . $order->invoice_no : 'وەسڵی نوێ')

@section('content')

@php
    $initialLines = old('lines', $order->exists
        ? $order->items->map(function ($l) {
            $imgs = [];
            foreach ($l->allImages() as $p) {
                $imgs[] = [
                    'id' => uniqid('img_', true),
                    'path' => $p,
                    'preview' => asset('storage/' . $p),
                ];
            }
            return [
                'description' => $l->description,
                'image' => $l->image,
                'images' => $imgs,
                'preview' => $l->imageUrl(),
                'meter' => $l->meter !== null ? (float)$l->meter : '',
                'meter_price' => $l->meter_price !== null ? number_format((float)$l->meter_price) : '',
                'unit_price' => $l->unit_price !== null ? number_format((float)$l->unit_price) : '',
                'line_total' => $l->line_total !== null ? number_format((float)$l->line_total) : ($l->unit_price !== null ? number_format((float)$l->unit_price) : ''),
                'note' => $l->note ?? '',
            ];
        })->all()
        : [['description' => '', 'image' => '', 'images' => [], 'preview' => null, 'meter' => '', 'meter_price' => '', 'unit_price' => '', 'line_total' => '', 'note' => '']]);
@endphp

<form method="POST"
      action="{{ $order->exists ? route('orders.update', $order) : route('orders.store') }}"
      enctype="multipart/form-data"
      x-data="orderForm(
          @js($initialLines),
          @js(old('discount_amount', $order->discount_amount ? (float)$order->discount_amount : '')),
          @js(old('currency', $order->currency ?: 'USD')),
          @js(collect($customers)->mapWithKeys(fn ($c) => [$c->id => (float) $c->discount_percent])->all()),
          @js($customers->map(fn ($c) => ['id' => $c->id, 'name' => $c->name, 'phone' => $c->phone, 'address' => $c->address, 'discount_percent' => (float) $c->discount_percent])->values()->all())
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

    {{-- ١. زانیاری سەرەکی کڕیار، ناونیشان و بەروار --}}
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

            {{-- ناونیشانی شوێنی کار / کڕیار --}}
            <div class="sm:col-span-2">
                <label class="label" for="address_snapshot">ناونیشانی شوێنی کار / کڕیار</label>
                <input id="address_snapshot" name="address_snapshot" type="text" class="field"
                       x-model="address"
                       placeholder="بۆ نموونە: هەولێر — گوندی ئیتاڵی، پیرمام...">
            </div>

            {{-- تێبینی --}}
            <div class="sm:col-span-1">
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
                                    class="text-slate-400 hover:text-blue-600 p-0.5 rounded transition-all cursor-pointer"
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
            <table class="table w-full min-w-[650px]">
                <thead>
                    <tr class="bg-slate-50/80 text-xs text-slate-700 font-bold border-b border-[--color-line]">
                        <th style="width: 54px; text-align: center; padding: 10px 4px;">وێنە</th>
                        <th style="text-align: right; padding: 10px 10px;">ناوەڕۆک / شتەکە (وەک دەرگا، مەحەجەرە...)</th>
                        <th style="width: 100px; text-align: center; padding: 10px 4px;">مەتر</th>
                        <th style="width: 140px; text-align: center; padding: 10px 6px;">نرخی مەتر (<span x-text="currency === 'USD' ? '$' : 'د.ع'"></span>)</th>
                        <th style="width: 160px; text-align: center; padding: 10px 8px;">کۆی گشتی (<span x-text="currency === 'USD' ? '$' : 'د.ع'"></span>)</th>
                        <th style="width: 38px; text-align: center; padding: 10px 4px;"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    <template x-for="(line, index) in lines" :key="index">
                        <tr>
                            {{-- وێنەی کاڵا / دیزاین (چەندین وێنە) --}}
                            <td style="text-align: center; padding: 4px; width: 56px;">
                                <div class="flex items-center justify-center">
                                    {{-- فایل ئینپووتی خێرا بۆ هەڵبژاردنی یەک یان چەندین وێنە --}}
                                    <input type="file"
                                           :id="`order_line_file_${index}`"
                                           multiple
                                           accept="image/*"
                                           class="sr-only"
                                           @change="onLineFilesSelected($event, line)">

                                    {{-- Hidden inputs بۆ وێنە تۆمارکراوەکان و base64 --}}
                                    <template x-for="(img, imgIdx) in (line.images || [])" :key="img.id || imgIdx">
                                        <div>
                                            <template x-if="img.path">
                                                <input type="hidden" :name="`lines[${index}][existing_images][]`" :value="img.path">
                                            </template>
                                            <template x-if="img.base64">
                                                <input type="hidden" :name="`lines[${index}][images_base64][]`" :value="img.base64">
                                            </template>
                                        </div>
                                    </template>
                                    <input type="hidden" :name="`lines[${index}][existing_image]`" :value="(line.images && line.images[0]?.path) ? line.images[0].path : (line.image || '')">

                                    {{-- کاتێک وێنە هەیە --}}
                                    <template x-if="line.images && line.images.length > 0">
                                        <div class="relative group size-9 rounded-lg overflow-hidden border-2 border-blue-500 shadow-2xs shrink-0 cursor-pointer bg-slate-100"
                                             @click="openLineImageManager(index)"
                                             :title="line.images.length + ' وێنە زیادکراوە — کرتە بکە بۆ پیشاندان و بەڕێوەبردن'">
                                            <img :src="line.images[0].preview || line.preview" class="size-full object-cover group-hover:scale-110 transition-transform">
                                            
                                            {{-- نیشاندانی ژمارەی وێنەکان ئەگەر لە یەک زیاتر بوو --}}
                                            <template x-if="line.images.length > 1">
                                                <span class="absolute bottom-0 inset-x-0 bg-blue-900/90 text-white font-black text-[9px] py-0.5 leading-none text-center"
                                                      x-text="'+' + line.images.length"></span>
                                            </template>

                                            {{-- هۆڤەر ئایکۆن بۆ دەستکاری --}}
                                            <div class="absolute inset-0 bg-blue-900/40 opacity-0 group-hover:opacity-100 flex items-center justify-center text-xs text-white font-bold transition-opacity">
                                                ✎
                                            </div>
                                        </div>
                                    </template>

                                    {{-- کاتێک وێنە نییە --}}
                                    <template x-if="!line.images || line.images.length === 0">
                                        <label :for="`order_line_file_${index}`"
                                               class="size-9 rounded-lg border border-dashed border-slate-300 hover:border-blue-500 bg-slate-50 hover:bg-blue-50 text-slate-400 hover:text-blue-600 flex items-center justify-center transition-all shrink-0 cursor-pointer active:scale-95"
                                               title="دانانی چەندین وێنەی دیزاین (مۆبایل، گەلەری، کامێرا)">
                                            <svg class="size-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                                                <circle cx="8.5" cy="8.5" r="1.5"/>
                                                <polyline points="21 15 16 10 5 21"/>
                                            </svg>
                                        </label>
                                    </template>
                                </div>
                            </td>

                            {{-- ناوەڕۆک / ناوی شتەکە --}}
                            <td style="padding: 6px 10px;">
                                <input :name="`lines[${index}][description]`" x-model="line.description" required
                                       class="field w-full !py-2 !px-3 text-sm bg-white"
                                       :placeholder="'ناوەڕۆک / شتەکە ' + (index + 1) + ' (وەک: دەرگای ئاسن، مەحەجەرە...)'">
                            </td>

                            {{-- بڕی مەتر --}}
                            <td style="padding: 6px 4px;">
                                <input type="number" step="any" min="0"
                                       :name="`lines[${index}][meter]`"
                                       x-model="line.meter"
                                       @input="calculateLineTotal(line)"
                                       class="field num w-full !py-2 !px-2 text-sm font-bold text-center bg-white"
                                       dir="ltr"
                                       placeholder="مەتر">
                            </td>

                            {{-- نرخی بە مەتر --}}
                            <td style="padding: 6px 4px;">
                                <input type="text" inputmode="numeric"
                                       :name="`lines[${index}][meter_price]`"
                                       x-model="line.meter_price"
                                       @input="formatMeterPriceInput($event, line)"
                                       class="field num w-full !py-2 !px-2 text-sm font-bold text-center bg-white"
                                       dir="ltr"
                                       placeholder="نرخ / م">
                            </td>

                            {{-- کۆی گشتی نرخ --}}
                            <td style="padding: 6px 6px;">
                                <div class="relative">
                                    <input type="text" inputmode="numeric" required
                                           :name="`lines[${index}][line_total]`"
                                           x-model="line.line_total"
                                           @input="formatLineTotalInput($event, line)"
                                           class="field num w-full !py-2 !px-3 text-sm font-black text-center bg-slate-50 focus:bg-white text-blue-700"
                                           dir="ltr"
                                           placeholder="0">
                                    <input type="hidden" :name="`lines[${index}][unit_price]`" :value="line.line_total || line.meter_price || ''">
                                </div>
                            </td>

                            {{-- سڕینەوە --}}
                            <td style="text-align: center; padding: 6px 2px;">
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
                {{-- داشکاندن بە ژمارە --}}
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
                <div>
                    <label class="label text-xs" for="modal_customer_address">ناونیشان</label>
                    <input id="modal_customer_address" x-model="newCustomer.address" class="field text-sm w-full" placeholder="هەولێر، پیرمام، گوندی ئیتاڵی..." @keydown.enter.prevent="saveQuickCustomer()">
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

    {{-- مۆداڵی بەڕێوەبردنی وێنەکانی دێڕی وەسڵ (چەندین وێنە) --}}
    <div x-show="activeImageLineIndex !== null" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-3 sm:p-4"
         @keydown.escape.window="activeImageLineIndex = null"
         x-transition.opacity>
        <div class="bg-white rounded-3xl shadow-2xl max-w-xl w-full p-5 border border-slate-200 flex flex-col max-h-[90vh] overflow-hidden"
             @click.away="activeImageLineIndex = null"
             x-transition.scale>
            
            {{-- سەرپەڕەی مۆداڵ --}}
            <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-3 shrink-0">
                <div class="flex items-center gap-2.5">
                    <span class="size-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg font-bold border border-blue-100">🖼️</span>
                    <div>
                        <div class="font-black text-slate-800 text-sm sm:text-base flex items-center gap-2">
                            <span>وێنەکانی دیزاینی ئەم بەشە</span>
                            <template x-if="activeImageLineIndex !== null && lines[activeImageLineIndex]?.images?.length > 0">
                                <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-blue-100 text-blue-800"
                                      x-text="lines[activeImageLineIndex].images.length + ' وێنە'"></span>
                            </template>
                        </div>
                        <div class="text-xs text-slate-500 font-medium"
                             x-text="activeImageLineIndex !== null ? (lines[activeImageLineIndex]?.description ? 'بۆ: ' + lines[activeImageLineIndex].description : 'دێڕی ' + (activeImageLineIndex + 1)) : ''"></div>
                    </div>
                </div>
                <button type="button" @click="activeImageLineIndex = null"
                        class="text-slate-400 hover:text-slate-700 size-8 rounded-xl flex items-center justify-center text-lg hover:bg-slate-100 transition-colors cursor-pointer">✕</button>
            </div>

            {{-- دوگمەکانی زیادکردنی وێنە (کامێرا و گەلەری) --}}
            <div class="flex flex-wrap items-center gap-2 p-3 bg-slate-50 rounded-2xl border border-slate-200 mb-3 shrink-0">
                {{-- کامێرا --}}
                <input type="file" id="modal_line_camera" accept="image/*" capture="environment" class="sr-only" @change="onModalFilesSelected($event, 'camera')">
                <label for="modal_line_camera"
                       class="px-3.5 py-2 rounded-xl text-xs font-black bg-white hover:bg-amber-50 text-amber-900 border border-amber-300 shadow-2xs flex items-center gap-1.5 transition-all cursor-pointer active:scale-95">
                    <span>📸</span>
                    <span>گرتنی وێنە بە کامێرا</span>
                </label>

                {{-- گەلەری / چەندین وێنە بەدڵی خۆت --}}
                <input type="file" id="modal_line_gallery" accept="image/*" multiple class="sr-only" @change="onModalFilesSelected($event, 'gallery')">
                <label for="modal_line_gallery"
                       class="px-3.5 py-2 rounded-xl text-xs font-black bg-blue-600 hover:bg-blue-700 text-white shadow-xs flex items-center gap-1.5 transition-all cursor-pointer active:scale-95">
                    <span>🖼️</span>
                    <span>زیادکردنی چەند وێنە لە گەلەری</span>
                </label>
            </div>

            {{-- خشتەی پیشاندانی وێنەکان --}}
            <div class="flex-1 overflow-y-auto min-h-0 pr-1 space-y-2">
                <template x-if="activeImageLineIndex !== null && (!lines[activeImageLineIndex]?.images || lines[activeImageLineIndex]?.images.length === 0)">
                    <div class="py-12 flex flex-col items-center justify-center text-center text-slate-400 border-2 border-dashed border-slate-200 rounded-2xl">
                        <span class="text-4xl mb-2">📸</span>
                        <span class="text-xs font-bold text-slate-600">هیچ وێنەیەک بۆ ئەم دێڕە دانەنراوە</span>
                        <span class="text-[11px] text-slate-400 mt-1">دەتوانیت بە کامێرا وێنە بگریت یان چەندین وێنە لە گەلەری هەڵبژێریت.</span>
                    </div>
                </template>

                <template x-if="activeImageLineIndex !== null && lines[activeImageLineIndex]?.images?.length > 0">
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        <template x-for="(img, imgIdx) in lines[activeImageLineIndex].images" :key="img.id || imgIdx">
                            <div class="relative group rounded-2xl overflow-hidden border border-slate-200 bg-slate-100 shadow-xs aspect-4/3 flex items-center justify-center">
                                <img :src="img.preview" class="size-full object-cover">
                                
                                {{-- overlay controls --}}
                                <div class="absolute inset-0 bg-slate-900/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2 p-2">
                                    <button type="button" @click="viewLargeImage(img.preview)"
                                            class="size-8 rounded-xl bg-white/90 hover:bg-white text-slate-800 flex items-center justify-center text-xs shadow-xs cursor-pointer active:scale-90 transition-transform"
                                            title="گەورەکردن">
                                        🔍
                                    </button>
                                    <button type="button" @click="removeImageFromLine(lines[activeImageLineIndex], imgIdx)"
                                            class="size-8 rounded-xl bg-rose-600 hover:bg-rose-700 text-white flex items-center justify-center text-xs shadow-xs cursor-pointer active:scale-90 transition-transform"
                                            title="سڕینەوە">
                                        🗑️
                                    </button>
                                </div>

                                {{-- ژمارەی وێنە لە گۆشە --}}
                                <div class="absolute top-1.5 right-1.5 bg-slate-900/70 text-white rounded-md px-1.5 py-0.5 text-[9px] font-black pointer-events-none"
                                     x-text="imgIdx + 1"></div>
                            </div>
                        </template>
                    </div>
                </template>
            </div>

            {{-- ژێرەوەی مۆداڵ --}}
            <div class="pt-3 border-t border-slate-100 flex items-center justify-between mt-3 shrink-0">
                <span class="text-[11px] text-slate-400 font-medium">وێنەکان دەمێننەوە و پاشەکەوت دەکرێن لە وەسڵدا</span>
                <button type="button" @click="activeImageLineIndex = null"
                        class="btn btn-primary !py-2 !px-5 text-xs font-black shadow-xs">
                    تەواو
                </button>
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
        address: '{{ old('address_snapshot', $order->address_snapshot ?: ($order->customer?->address ?? '')) }}',
        customerDiscounts: customerDiscounts,
        customersList: initialCustomersList,
        showCustomerModal: false,
        savingCustomer: false,
        customerModalError: '',
        newCustomer: { name: '', phone: '', address: '' },
        prepaid: '{{ old('prepaid_amount', $order->exists ? ($order->prepaid_amount ? number_format($order->prepaid_amount) : '') : '') }}',
        prepaidManuallySet: {{ ($order->exists || old('prepaid_amount') !== null) ? 'true' : 'false' }},
        exchangeRate: '{{ (float) old('exchange_rate', $order->exchange_rate ? ($order->exchange_rate > 5000 ? $order->exchange_rate : $order->exchange_rate * 100) : ($rate > 5000 ? $rate : ($rate > 0 ? $rate * 100 : 150000))) }}',
        fetchingRate: false,
        activeImageLineIndex: null,

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
            this.newCustomer = { name: '', phone: '', address: '' };
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
                const cust = this.customersList.find(c => String(c.id) === String(this.customerId));
                if (cust && cust.address) {
                    this.address = cust.address;
                }
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
                    this.address = data.customer.address || '';
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
            // دڵنیابوونەوە لە ڕێکی پەیکەری وێنەکانی هەموو دێڕەکان
            if (Array.isArray(this.lines)) {
                this.lines.forEach((l) => {
                    if (!Array.isArray(l.images)) {
                        l.images = [];
                        if (l.preview || l.image) {
                            l.images.push({
                                id: 'img_' + Math.random().toString(36).substring(2, 9),
                                preview: l.preview || (l.image ? '/storage/' + l.image : null),
                                path: l.image || null,
                                base64: null,
                            });
                        }
                    }
                });
            }

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

        openLineImageManager(index) {
            this.activeImageLineIndex = index;
        },

        closeLineImageManager() {
            this.activeImageLineIndex = null;
        },

        onLineFilesSelected(e, line) {
            const files = Array.from(e.target.files || []);
            if (!files.length) return;
            this.addFilesToLine(files, line);
            e.target.value = '';
        },

        onModalFilesSelected(e, source) {
            if (this.activeImageLineIndex === null) return;
            const line = this.lines[this.activeImageLineIndex];
            if (!line) return;
            const files = Array.from(e.target.files || []);
            if (!files.length) return;
            this.addFilesToLine(files, line);
            e.target.value = '';
        },

        addFilesToLine(files, line) {
            if (!Array.isArray(line.images)) line.images = [];
            files.forEach(file => {
                const tempId = 'img_' + Math.random().toString(36).substring(2, 9);
                const previewUrl = URL.createObjectURL(file);
                const imgObj = {
                    id: tempId,
                    preview: previewUrl,
                    base64: null,
                    path: null,
                    file: file,
                };
                line.images.push(imgObj);
                this.compressImageForLine(imgObj);
            });
            if (line.images.length > 0) {
                line.preview = line.images[0].preview;
            }
        },

        compressImageForLine(imgObj) {
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
                    imgObj.base64 = canvas.toDataURL('image/jpeg', 0.82);
                };
                img.src = imgObj.preview;
            } catch (err) {
                console.warn('Image compression error:', err);
            }
        },

        removeImageFromLine(line, imageIndex) {
            if (Array.isArray(line.images)) {
                line.images.splice(imageIndex, 1);
                line.preview = line.images.length > 0 ? line.images[0].preview : null;
                line.image = line.images.length > 0 ? (line.images[0].path || '') : '';
            }
        },

        viewLargeImage(previewUrl) {
            if (!previewUrl) return;
            const win = window.open();
            if (win) {
                win.document.write('<!DOCTYPE html><html><head><title>وێنەی وەسڵ</title><style>body{margin:0;padding:20px;display:flex;align-items:center;justify-content:center;min-height:90vh;background:#0f172a;}img{max-width:95vw;max-height:90vh;object-fit:contain;border-radius:12px;box-shadow:0 10px 30px rgba(0,0,0,0.5);}</style></head><body><img src="' + previewUrl + '"></body></html>');
            }
        },

        addLine() {
            this.lines.push({
                description: '',
                image: '',
                images: [],
                preview: null,
                meter: '',
                meter_price: '',
                unit_price: '',
                line_total: '',
                note: '',
            });
        },

        removeLine(index) {
            if (this.lines.length > 1) {
                this.lines.splice(index, 1);
            } else {
                this.lines[0] = { description: '', image: '', images: [], preview: null, meter: '', meter_price: '', unit_price: '', line_total: '', note: '' };
            }
        },

        calculateLineTotal(line) {
            let m = parseFloat(line.meter) || 0;
            let mp = parseFloat((line.meter_price || '0').toString().replace(/,/g, '')) || 0;
            if (m > 0 && mp > 0) {
                let tot = Math.round(m * mp);
                line.line_total = tot.toLocaleString('en-US');
                line.unit_price = line.line_total;
            }
        },

        formatMeterPriceInput(e, line) {
            let clean = e.target.value.replace(/[^0-9.]/g, '');
            let parts = clean.split('.');
            if (parts.length > 2) parts = [parts[0], parts.slice(1).join('')];
            let int = parts[0] ? parseInt(parts[0], 10).toLocaleString('en-US') : '';
            let dec = parts.length > 1 ? '.' + parts[1] : '';
            line.meter_price = int ? int + dec : '';
            this.calculateLineTotal(line);
        },

        formatLineTotalInput(e, line) {
            let clean = e.target.value.replace(/[^0-9.]/g, '');
            let parts = clean.split('.');
            if (parts.length > 2) parts = [parts[0], parts.slice(1).join('')];
            let int = parts[0] ? parseInt(parts[0], 10).toLocaleString('en-US') : '';
            let dec = parts.length > 1 ? '.' + parts[1] : '';
            line.line_total = int ? int + dec : '';
            line.unit_price = line.line_total;
        },

        applyCustomerDiscount() {
            const percent = this.customerDiscounts[this.customerId];
            if (percent && parseFloat(percent) > 0 && this.subtotal > 0) {
                const calculated = Math.round(this.subtotal * parseFloat(percent) / 100);
                this.discountAmount = calculated > 0 ? calculated.toLocaleString('en-US') : '';
            }
        },

        linePrice(line) {
            let p = parseFloat((line.line_total || line.unit_price || '0').toString().replace(/,/g, '')) || 0;
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
