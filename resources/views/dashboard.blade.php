@extends('layouts.menu')
@section('title', 'داشبۆردی سەرەکی')

@section('content')
<div x-data="liveDashboard()" class="flex flex-col gap-4 sm:gap-5">

    {{-- ١. سەردێڕی داشبۆرد و کاتژمێری زیندوو --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        {{-- ڕاست: ناونیشانی داشبۆرد --}}
        <div class="flex items-center gap-2.5">
            <div class="size-10 rounded-2xl bg-sky-100 text-sky-700 flex items-center justify-center border border-sky-200/80 shadow-2xs">
                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <polyline points="12 6 12 12 16 14"/>
                </svg>
            </div>
            <div>
                <h1 class="text-xl sm:text-2xl font-black text-slate-900 leading-tight">داشبۆردی سەرەکی</h1>
                <p class="text-xs text-slate-400 font-medium hidden sm:block">پوختەی کارگە، کۆگا، فرۆشتن و دارایی</p>
            </div>
        </div>

        {{-- چەپ: کاتژمێر و بەرواری زیندوو --}}
        <div class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white px-3.5 py-2 rounded-2xl font-extrabold text-xs sm:text-sm flex items-center justify-between sm:justify-end gap-3 shadow-md shadow-indigo-500/15">
            <div class="flex items-center gap-2">
                <svg class="size-4 opacity-90 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <polyline points="12 6 12 12 16 14"/>
                </svg>
                <div x-text="dateString" class="text-[11px] sm:text-xs font-bold opacity-95"></div>
            </div>
            <span class="opacity-40">|</span>
            <div class="font-mono font-black text-xs sm:text-sm" dir="ltr" x-text="timeString"></div>
        </div>
    </div>

    {{-- ٢. تابلۆی کارتە ئامارییەکان (٢ ستوون لە مۆبایل، ٤ ستوون لە شاشەی گەورە) --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4">

        {{-- کارتی ١: کۆی وەسڵەکان --}}
        <div class="bg-white rounded-2xl p-3.5 sm:p-4 border border-blue-100 border-r-4 border-r-blue-500 shadow-2xs hover:shadow-md transition-all flex items-center justify-between gap-2">
            <div class="min-w-0">
                <div class="font-mono font-black text-xl sm:text-2xl text-slate-900 leading-tight truncate">
                    {{ fmt_num($totalOrdersCount ?? $openOrders) }}
                </div>
                <div class="text-[11px] sm:text-xs font-bold text-slate-500 mt-1 truncate">
                    کۆی وەسڵەکان
                </div>
            </div>
            <div class="size-10 sm:size-11 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0 border border-blue-100">
                <svg class="size-5 sm:size-5.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                    <polyline points="14 2 14 8 20 8"/>
                    <line x1="16" y1="13" x2="8" y2="13"/>
                    <line x1="16" y1="17" x2="8" y2="17"/>
                </svg>
            </div>
        </div>

        {{-- کارتی ٢: کڕیاران --}}
        <div class="bg-white rounded-2xl p-3.5 sm:p-4 border border-sky-100 border-r-4 border-r-sky-500 shadow-2xs hover:shadow-md transition-all flex items-center justify-between gap-2">
            <div class="min-w-0">
                <div class="font-mono font-black text-xl sm:text-2xl text-slate-900 leading-tight truncate">
                    {{ fmt_num($totalCustomersCount ?? 0) }}
                </div>
                <div class="text-[11px] sm:text-xs font-bold text-slate-500 mt-1 truncate">
                    کڕیاران
                </div>
            </div>
            <div class="size-10 sm:size-11 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center shrink-0 border border-sky-100">
                <svg class="size-5 sm:size-5.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M17 11l3-3m-3 0l3 3m-3-3v6"/>
                    <path d="M3 21v-2a4 4 0 0 1 4-4h4a4 4 0 0 1 4 4v2"/>
                </svg>
            </div>
        </div>

        {{-- کارتی ٣: کەلوپەل و مەواد --}}
        <div class="bg-white rounded-2xl p-3.5 sm:p-4 border border-purple-100 border-r-4 border-r-purple-500 shadow-2xs hover:shadow-md transition-all flex items-center justify-between gap-2">
            <div class="min-w-0">
                <div class="font-mono font-black text-xl sm:text-2xl text-slate-900 leading-tight truncate">
                    {{ fmt_num($itemsCount ?? 0) }}
                </div>
                <div class="text-[11px] sm:text-xs font-bold text-slate-500 mt-1 truncate">
                    کەلوپەل و مەواد
                </div>
            </div>
            <div class="size-10 sm:size-11 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center shrink-0 border border-purple-100">
                <svg class="size-5 sm:size-5.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                    <polyline points="3.27 6.96 12 12.01 20.73 6.96"/>
                    <line x1="12" y1="22.08" x2="12" y2="12"/>
                </svg>
            </div>
        </div>

        {{-- کارتی ٤: وەسڵی ئەمڕۆ --}}
        <div class="bg-white rounded-2xl p-3.5 sm:p-4 border border-amber-100 border-r-4 border-r-amber-500 shadow-2xs hover:shadow-md transition-all flex items-center justify-between gap-2">
            <div class="min-w-0">
                <div class="font-mono font-black text-xl sm:text-2xl text-slate-900 leading-tight truncate">
                    {{ fmt_num($todayOrders ?? 0) }}
                </div>
                <div class="text-[11px] sm:text-xs font-bold text-slate-500 mt-1 truncate">
                    وەسڵی ئەمڕۆ
                </div>
            </div>
            <div class="size-10 sm:size-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0 border border-amber-100">
                <svg class="size-5 sm:size-5.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="9"/>
                    <path d="M12 7v5l3 3"/>
                </svg>
            </div>
        </div>

        {{-- کارتی ٥: وەسڵی لە کاردا --}}
        <div class="bg-white rounded-2xl p-3.5 sm:p-4 border border-emerald-100 border-r-4 border-r-emerald-500 shadow-2xs hover:shadow-md transition-all flex items-center justify-between gap-2">
            <div class="min-w-0">
                <div class="font-mono font-black text-xl sm:text-2xl text-slate-900 leading-tight truncate">
                    {{ fmt_num($openOrders ?? 0) }}
                </div>
                <div class="text-[11px] sm:text-xs font-bold text-slate-500 mt-1 truncate">
                    وەسڵی لە کاردا
                </div>
            </div>
            <div class="size-10 sm:size-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 border border-emerald-100">
                <svg class="size-5 sm:size-5.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                    <polyline points="22 4 12 14.01 9 11.01"/>
                </svg>
            </div>
        </div>

        {{-- کارتی ٦: کارمەندان --}}
        <div class="bg-white rounded-2xl p-3.5 sm:p-4 border border-indigo-100 border-r-4 border-r-indigo-500 shadow-2xs hover:shadow-md transition-all flex items-center justify-between gap-2">
            <div class="min-w-0">
                <div class="font-mono font-black text-xl sm:text-2xl text-slate-900 leading-tight truncate">
                    {{ fmt_num($totalEmployees ?? 0) }}
                </div>
                <div class="text-[11px] sm:text-xs font-bold text-slate-500 mt-1 truncate">
                    کارمەندان
                </div>
            </div>
            <div class="size-10 sm:size-11 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0 border border-indigo-100">
                <svg class="size-5 sm:size-5.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
            </div>
        </div>

        {{-- کارتی ٧: فرۆشتنی ئەم مانگە --}}
        <div class="bg-white rounded-2xl p-3.5 sm:p-4 border border-teal-100 border-r-4 border-r-teal-500 shadow-2xs hover:shadow-md transition-all flex items-center justify-between gap-2">
            <div class="min-w-0">
                <div class="font-mono font-black text-lg sm:text-xl text-slate-900 leading-tight truncate">
                    {{ fmt_num($monthSales ?? 0) }}
                </div>
                <div class="text-[11px] sm:text-xs font-bold text-slate-500 mt-1 truncate">
                    فرۆشتنی ئەم مانگە
                </div>
            </div>
            <div class="size-10 sm:size-11 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center shrink-0 border border-teal-100">
                <svg class="size-5 sm:size-5.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/>
                    <polyline points="17 6 23 6 23 12"/>
                </svg>
            </div>
        </div>

        {{-- کارتی ٨: خەرجی ئەم مانگە --}}
        <div class="bg-white rounded-2xl p-3.5 sm:p-4 border border-rose-100 border-r-4 border-r-rose-500 shadow-2xs hover:shadow-md transition-all flex items-center justify-between gap-2">
            <div class="min-w-0">
                <div class="font-mono font-black text-lg sm:text-xl text-slate-900 leading-tight truncate">
                    {{ fmt_num($monthExpenses ?? 0) }}
                </div>
                <div class="text-[11px] sm:text-xs font-bold text-slate-500 mt-1 truncate">
                    خەرجی ئەم مانگە
                </div>
            </div>
            <div class="size-10 sm:size-11 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center shrink-0 border border-rose-100">
                <svg class="size-5 sm:size-5.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="23 18 13.5 8.5 8.5 13.5 1 6"/>
                    <polyline points="17 18 23 18 23 12"/>
                </svg>
            </div>
        </div>

        {{-- کارتی ٩: کۆی قەرزەکان --}}
        <div class="col-span-2 sm:col-span-1 md:col-span-2 bg-white rounded-2xl p-3.5 sm:p-4 border border-rose-100 border-r-4 border-r-rose-500 shadow-2xs hover:shadow-md transition-all flex items-center justify-between gap-3">
            <div class="min-w-0">
                <div class="font-mono font-black text-xl sm:text-2xl text-rose-600 leading-tight truncate">
                    {{ fmt_num($receivables ?? 0) }}
                </div>
                <div class="text-[11px] sm:text-xs font-bold text-slate-500 mt-1 truncate">
                    کۆی قەرزەکان (کڕیاران)
                </div>
            </div>
            <div class="size-10 sm:size-11 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center shrink-0 border border-rose-100">
                <svg class="size-5 sm:size-5.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="2" y="5" width="20" height="14" rx="2"/>
                    <line x1="2" y1="10" x2="22" y2="10"/>
                </svg>
            </div>
        </div>

        {{-- کارتی ١٠: ئامێر و دەرەوە --}}
        <div class="col-span-2 sm:col-span-1 md:col-span-2 bg-white rounded-2xl p-3.5 sm:p-4 border border-amber-100 border-r-4 border-r-amber-500 shadow-2xs hover:shadow-md transition-all flex items-center justify-between gap-3">
            <div class="min-w-0">
                <div class="font-mono font-black text-xl sm:text-2xl text-slate-900 leading-tight truncate">
                    {{ fmt_num(($totalSuppliersCount ?? 0) + ($activeJobsCount ?? 0)) }}
                </div>
                <div class="text-[11px] sm:text-xs font-bold text-slate-500 mt-1 truncate">
                    ئامێر و ئیشی خاریجی
                </div>
            </div>
            <div class="size-10 sm:size-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0 border border-amber-100">
                <svg class="size-5 sm:size-5.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>
                </svg>
            </div>
        </div>

    </div>

    {{-- ٣. کارتی پوختەی فرۆش و ئەمڕۆ --}}
    <div class="bg-white rounded-2xl p-4 sm:p-5 border border-rose-100 border-r-4 border-r-rose-500 shadow-2xs">
        <div class="flex items-center gap-2 font-black text-sm sm:text-base text-slate-900 mb-3 sm:mb-4">
            <span class="text-base">⚠️</span>
            <span>دۆخی گشتی و فرۆش</span>
        </div>

        <div class="grid grid-cols-2 gap-3 sm:gap-4">
            {{-- سندوقی فرۆشی ئەمڕۆ --}}
            <div class="bg-rose-50/80 border border-rose-200/80 rounded-2xl p-3 sm:p-4 text-center">
                <div class="text-[11px] sm:text-xs font-bold text-rose-800 mb-1">فرۆشی ئەمڕۆ</div>
                <div class="font-mono font-black text-xl sm:text-3xl text-rose-600">
                    {{ fmt_num($todaySales ?? 0) }}
                </div>
            </div>

            {{-- سندوقی کۆی گشتی فرۆشی مانگ --}}
            <div class="bg-amber-50/80 border border-amber-200/80 rounded-2xl p-3 sm:p-4 text-center">
                <div class="text-[11px] sm:text-xs font-bold text-amber-800 mb-1">کۆی فرۆشی ئەم مانگە</div>
                <div class="font-mono font-black text-xl sm:text-3xl text-amber-600">
                    {{ fmt_num($monthSales ?? 0) }}
                </div>
            </div>
        </div>
    </div>

    {{-- ٤. بەشی کردارە خێراکان --}}
    <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-2xs">
        <div class="flex items-center gap-2 font-black text-sm sm:text-base text-slate-900 mb-3 sm:mb-4">
            <span class="text-base text-amber-500">⚡</span>
            <span>کردارە خێراکان</span>
        </div>

        <div class="grid grid-cols-2 sm:flex sm:flex-wrap gap-2.5 sm:gap-3">
            @if (auth()->user()->can('manage_orders'))
                <a href="{{ route('orders.create') }}"
                   class="bg-blue-600 hover:bg-blue-700 active:scale-95 text-white py-2.5 px-4 rounded-xl font-bold text-xs sm:text-sm flex items-center justify-center gap-2 transition-all shadow-sm shadow-blue-500/20">
                    <span>➕</span>
                    <span>وەسڵی نوێ</span>
                </a>
            @endif

            @if (auth()->user()->can('manage_purchases'))
                <a href="{{ route('purchases.create') }}"
                   class="bg-sky-600 hover:bg-sky-700 active:scale-95 text-white py-2.5 px-4 rounded-xl font-bold text-xs sm:text-sm flex items-center justify-center gap-2 transition-all shadow-sm shadow-sky-500/20">
                    <span>🛒</span>
                    <span>پسوولەی کڕین</span>
                </a>
            @endif

            @if (auth()->user()->can('manage_payments'))
                <a href="{{ route('payments.create') }}"
                   class="bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white py-2.5 px-4 rounded-xl font-bold text-xs sm:text-sm flex items-center justify-center gap-2 transition-all shadow-sm shadow-emerald-500/20">
                    <span>💵</span>
                    <span>تۆماری پارەدان</span>
                </a>
            @endif

            <a href="{{ route('debts.index') }}"
               class="bg-indigo-600 hover:bg-indigo-700 active:scale-95 text-white py-2.5 px-4 rounded-xl font-bold text-xs sm:text-sm flex items-center justify-center gap-2 transition-all shadow-sm shadow-indigo-500/20">
                <span>💳</span>
                <span>قەرزەکان</span>
            </a>

            <a href="{{ route('statement.index') }}"
               class="col-span-2 sm:col-span-1 bg-purple-600 hover:bg-purple-700 active:scale-95 text-white py-2.5 px-4 rounded-xl font-bold text-xs sm:text-sm flex items-center justify-center gap-2 transition-all shadow-sm shadow-purple-500/20">
                <span>📑</span>
                <span>کەشف حیسابی</span>
            </a>
        </div>
    </div>

    {{-- ٥. خشتەی دوایین وەسڵەکان و دوایین پارەدانەکان --}}
    @if (isset($recentOrders) && $recentOrders->isNotEmpty())
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-4 items-start">

            {{-- دوایین وەسڵەکان --}}
            <div class="lg:col-span-3 bg-white rounded-2xl border border-slate-200/80 shadow-2xs overflow-hidden">
                <div class="p-3.5 sm:p-4 flex items-center justify-between border-b border-slate-100 bg-slate-50/50">
                    <div class="flex items-center gap-2 font-black text-xs sm:text-sm text-slate-900">
                        <span>📋</span>
                        <span>دوایین وەسڵەکان</span>
                    </div>
                    <a href="{{ route('orders.index') }}" class="text-xs font-bold text-blue-600 hover:text-blue-800">
                        هەمووی &larr;
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-right text-xs">
                        <thead>
                            <tr class="border-b border-slate-100 bg-slate-50 text-slate-500 font-bold text-[11px]">
                                <th class="p-3 text-center">ژمارە</th>
                                <th class="p-3 text-right">کڕیار</th>
                                <th class="p-3 text-center">کۆی پارە</th>
                                <th class="p-3 text-center">دۆخ</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($recentOrders as $order)
                                <tr class="hover:bg-slate-50/80 transition-colors">
                                    <td class="p-3 text-center font-mono">
                                        <a href="{{ route('orders.show', $order) }}" class="text-rose-600 font-bold hover:underline">
                                            {{ $order->invoice_no }}
                                        </a>
                                    </td>
                                    <td class="p-3 font-bold text-slate-800 truncate max-w-[120px]">
                                        {{ $order->customer?->name ?? '—' }}
                                    </td>
                                    <td class="p-3 text-center font-mono font-bold text-slate-700">
                                        {{ fmt_num($order->total_iqd) }}
                                    </td>
                                    <td class="p-3 text-center">
                                        <span class="bg-slate-100 text-slate-700 px-2 py-0.5 rounded-md text-[10px] font-bold">
                                            {{ $order->status_label }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- دوایین پارەدانەکان --}}
            @if (isset($recentPayments) && $recentPayments->isNotEmpty())
                <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200/80 shadow-2xs overflow-hidden">
                    <div class="p-3.5 sm:p-4 flex items-center justify-between border-b border-slate-100 bg-slate-50/50">
                        <div class="flex items-center gap-2 font-black text-xs sm:text-sm text-slate-900">
                            <span>💵</span>
                            <span>دوایین پارەدانەکان</span>
                        </div>
                        <a href="{{ route('payments.index') }}" class="text-xs font-bold text-blue-600 hover:text-blue-800">
                            هەمووی &larr;
                        </a>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-right text-xs">
                            <thead>
                                <tr class="border-b border-slate-100 bg-slate-50 text-slate-500 font-bold text-[11px]">
                                    <th class="p-3 text-center">وەسڵ</th>
                                    <th class="p-3 text-center">بڕ</th>
                                    <th class="p-3 text-center">جۆر</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($recentPayments as $payment)
                                    <tr class="hover:bg-slate-50/80 transition-colors">
                                        <td class="p-3 text-center font-mono">
                                            <a href="{{ route('payments.print', $payment) }}" class="text-blue-600 font-bold hover:underline">
                                                {{ $payment->voucher_no }}
                                            </a>
                                        </td>
                                        <td class="p-3 text-center font-mono font-black text-emerald-600">
                                            {{ fmt_num($payment->amount_iqd) }}
                                        </td>
                                        <td class="p-3 text-center">
                                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold {{ $payment->direction === 'in' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200' }}">
                                                {{ $payment->direction === 'in' ? 'وەرگرتن' : 'دان' }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

        </div>
    @endif

</div>

<script>
function liveDashboard() {
    return {
        timeString: '',
        dateString: '',

        init() {
            this.updateClock();
            setInterval(() => this.updateClock(), 1000);
        },

        updateClock() {
            const now = new Date();
            let hours = now.getHours();
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');
            const ampm = hours >= 12 ? 'PM' : 'AM';
            hours = hours % 12;
            hours = hours ? String(hours).padStart(2, '0') : '12';

            this.timeString = `${ampm} ${hours}:${minutes}:${seconds}`;

            const days = ['یەکشەممە', 'دووشەممە', 'سێشەممە', 'چوارشەممە', 'پێنجشەممە', 'هەینی', 'شەممە'];
            const dayName = days[now.getDay()];
            const year = now.getFullYear();
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const day = String(now.getDate()).padStart(2, '0');

            this.dateString = `${dayName} - ${year}/${month}/${day}`;
        }
    }
}
</script>
@endsection
