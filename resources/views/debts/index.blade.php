@extends('layouts.app')
@section('title', $selectedCustomer ? 'قەرزەکانی ' . $selectedCustomer->name : 'قەرزەکان')

@section('content')
<div x-data="debtsPage(@js($customers))" style="display: flex; flex-direction: column; gap: 1.25rem;">

    {{-- ١. بەشی سەرەوە: سەردێڕ و دوگمەی زیادکردنی قەرزی کۆن --}}
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
        {{-- لای ڕاست: ناونیشان و ئایکۆن --}}
        <div style="display: flex; align-items: center; gap: 0.75rem;">
            <div style="width: 2.5rem; height: 2.5rem; border-radius: 0.75rem; background: #e0e7ff; color: #4f46e5; display: flex; align-items: center; justify-content: center;">
                <svg style="width: 1.35rem; height: 1.35rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="2" y="5" width="20" height="14" rx="2"/>
                    <line x1="2" y1="10" x2="22" y2="10"/>
                </svg>
            </div>
            <h1 style="font-size: 1.5rem; font-weight: 800; color: #1e293b; margin: 0;">قەرزەکان</h1>
        </div>

        {{-- لای چەپ: دوگمەی قەرزی کۆن --}}
        <div>
            <button type="button" @click="openOldDebtModal = true"
                    style="background: #4f46e5; color: #ffffff; padding: 0.6rem 1.25rem; border-radius: 0.75rem; font-weight: 700; font-size: 0.875rem; display: inline-flex; align-items: center; gap: 0.5rem; border: none; cursor: pointer; box-shadow: 0 4px 12px rgba(79, 70, 229, 0.25); transition: all 0.2s;">
                <span style="font-size: 1rem; font-weight: bold;">+</span>
                <span>قەرزی کۆن زیادبکە</span>
            </button>
        </div>
    </div>

    {{-- ٢. کارتە ئامارییەکانی سەرەوە (٣ کارت لە تەنیشت یەک وەک وێنەکە) --}}
    <div style="display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 1rem;">

        {{-- کارتی ١ (دەستە ڕاست): کۆی قەرزی ماوە --}}
        <div style="background: #ffffff; border-radius: 1rem; padding: 1.25rem; border: 1px solid #fecdd3; border-right: 4px solid #f43f5e; box-shadow: 0 2px 8px rgba(0,0,0,0.03); display: flex; align-items: center; justify-content: space-between;">
            <div>
                <div class="num" style="font-size: 1.75rem; font-weight: 800; color: #0f172a; line-height: 1.2;">
                    {{ fmt_num($totalRemainingDebt) }}
                </div>
                <div style="font-size: 0.8rem; font-weight: 600; color: #64748b; margin-top: 0.25rem;">
                    کۆی قەرزی ماوە
                </div>
            </div>
            <div style="width: 2.75rem; height: 2.75rem; border-radius: 0.75rem; background: #ffe4e6; color: #f43f5e; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <svg style="width: 1.35rem; height: 1.35rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                    <line x1="12" y1="9" x2="12" y2="13"/>
                    <line x1="12" y1="17" x2="12.01" y2="17"/>
                </svg>
            </div>
        </div>

        {{-- کارتی ٢ (ناوەڕاست): کۆی پارەی دراو --}}
        <div style="background: #ffffff; border-radius: 1rem; padding: 1.25rem; border: 1px solid #a7f3d0; border-right: 4px solid #10b981; box-shadow: 0 2px 8px rgba(0,0,0,0.03); display: flex; align-items: center; justify-content: space-between;">
            <div>
                <div class="num" style="font-size: 1.75rem; font-weight: 800; color: #0f172a; line-height: 1.2;">
                    {{ fmt_num($totalPaid) }}
                </div>
                <div style="font-size: 0.8rem; font-weight: 600; color: #64748b; margin-top: 0.25rem;">
                    کۆی پارەی دراو
                </div>
            </div>
            <div style="width: 2.75rem; height: 2.75rem; border-radius: 0.75rem; background: #d1fae5; color: #10b981; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <svg style="width: 1.35rem; height: 1.35rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/>
                    <polyline points="16 9 10 15 8 13"/>
                </svg>
            </div>
        </div>

        {{-- کارتی ٣ (دەستە چەپ): قەرزداری چالاک --}}
        <div style="background: #ffffff; border-radius: 1rem; padding: 1.25rem; border: 1px solid #fde68a; border-right: 4px solid #f59e0b; box-shadow: 0 2px 8px rgba(0,0,0,0.03); display: flex; align-items: center; justify-content: space-between;">
            <div>
                <div class="num" style="font-size: 1.75rem; font-weight: 800; color: #0f172a; line-height: 1.2;">
                    {{ fmt_num($activeDebtorsCount) }}
                </div>
                <div style="font-size: 0.8rem; font-weight: 600; color: #64748b; margin-top: 0.25rem;">
                    قەرزداری چالاک
                </div>
            </div>
            <div style="width: 2.75rem; height: 2.75rem; border-radius: 0.75rem; background: #fef3c7; color: #f59e0b; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <svg style="width: 1.35rem; height: 1.35rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
            </div>
        </div>

    </div>

    @if ($selectedCustomer)
        {{-- ════════════════════════════════════════════════════════════════ --}}
        {{-- ب) بینینی وردەکاری قەرزەکانی کڕیاری دیاریکراو (وەک وێنەی داواکراو) --}}
        {{-- ════════════════════════════════════════════════════════════════ --}}

        {{-- سەردێڕی کڕیار و دوگمەکانی کردار --}}
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; margin-top: 0.5rem;">
            {{-- ڕاست: ناونیشان --}}
            <div style="display: flex; align-items: center; gap: 0.5rem; font-size: 1.35rem; font-weight: 800; color: #1e293b;">
                <div style="width: 2.25rem; height: 2.25rem; border-radius: 50%; background: #e0f2fe; color: #0284c7; display: flex; align-items: center; justify-content: center;">
                    <svg style="width: 1.25rem; height: 1.25rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                        <circle cx="12" cy="7" r="4"/>
                    </svg>
                </div>
                <span>قەرزەکانی {{ $selectedCustomer->name }}</span>
            </div>

            {{-- چەپ: دوگمەکان --}}
            <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                {{-- دوگمەی دانەوەی قەرز --}}
                <a href="{{ route('payments.create', ['type' => 'in', 'customer' => $selectedCustomer->id]) }}"
                   style="background: #10b981; color: #ffffff; padding: 0.55rem 1rem; border-radius: 0.6rem; font-weight: 700; font-size: 0.82rem; display: inline-flex; align-items: center; gap: 0.4rem; text-decoration: none; box-shadow: 0 2px 6px rgba(16, 185, 129, 0.2);">
                    <svg style="width: 1rem; height: 1rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="2" y="4" width="20" height="16" rx="2"/>
                        <circle cx="12" cy="12" r="3"/>
                    </svg>
                    <span>دانەوەی قەرز</span>
                </a>

                {{-- دوگمەی گەڕانەوە --}}
                <a href="{{ route('debts.index') }}"
                   style="background: #475569; color: #ffffff; padding: 0.55rem 1rem; border-radius: 0.6rem; font-weight: 700; font-size: 0.82rem; display: inline-flex; align-items: center; gap: 0.4rem; text-decoration: none;">
                    <svg style="width: 1rem; height: 1rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M5 12h14M12 5l7 7-7 7"/>
                    </svg>
                    <span>گەڕانەوە</span>
                </a>

                {{-- دوگمەی داگرتنی PDF / چاپکردن --}}
                <a href="{{ route('customers.statement', $selectedCustomer) }}"
                   style="background: #e11d48; color: #ffffff; padding: 0.55rem 1rem; border-radius: 0.6rem; font-weight: 700; font-size: 0.82rem; display: inline-flex; align-items: center; gap: 0.4rem; text-decoration: none; box-shadow: 0 2px 6px rgba(225, 29, 72, 0.2);">
                    <svg style="width: 1rem; height: 1rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/>
                        <line x1="16" y1="13" x2="8" y2="13"/>
                        <line x1="16" y1="17" x2="8" y2="17"/>
                        <polyline points="10 9 9 9 8 9"/>
                    </svg>
                    <span>داگرتنی PDF</span>
                </a>
            </div>
        </div>

        {{-- کارتی زانیاری کڕیار و چوار خانەی ئامار --}}
        <div style="background: #ffffff; border-radius: 1.25rem; padding: 1.25rem 1.5rem; border: 1px solid #f1f5f9; box-shadow: 0 2px 10px rgba(0,0,0,0.03); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1.5rem;">

            {{-- چوار خانەی ئاماری سەرەکی کڕیار (دەستە چەپ/ناوەڕاست) --}}
            <div style="display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 0.75rem; flex: 1; min-width: 20rem;">

                {{-- قەرزی چالاک (Yellow) --}}
                <div style="background: #fefce8; border: 1px solid #fef08a; border-radius: 0.75rem; padding: 0.75rem 0.5rem; text-align: center;">
                    <div style="font-size: 0.72rem; font-weight: 700; color: #854d0e; margin-bottom: 0.25rem;">قەرزی چالاک</div>
                    <div class="num" style="font-size: 1.4rem; font-weight: 800; color: #ca8a04;">
                        {{ $customerStats['active_orders_count'] }}
                    </div>
                </div>

                {{-- قەرزی ماوە (Red) --}}
                <div style="background: #fff1f2; border: 1px solid #fecdd3; border-radius: 0.75rem; padding: 0.75rem 0.5rem; text-align: center;">
                    <div style="font-size: 0.72rem; font-weight: 700; color: #9f1239; margin-bottom: 0.25rem;">قەرزی ماوە</div>
                    <div class="num" style="font-size: 1.4rem; font-weight: 800; color: #e11d48;">
                        {{ fmt_num($customerStats['remaining_debt']) }}
                    </div>
                </div>

                {{-- پارەی دراو (Green) --}}
                <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 0.75rem; padding: 0.75rem 0.5rem; text-align: center;">
                    <div style="font-size: 0.72rem; font-weight: 700; color: #166534; margin-bottom: 0.25rem;">پارەی دراو</div>
                    <div class="num" style="font-size: 1.4rem; font-weight: 800; color: #16a34a;">
                        {{ fmt_num($customerStats['paid_total']) }}
                    </div>
                </div>

                {{-- کۆی قەرز (Blue) --}}
                <div style="background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 0.75rem; padding: 0.75rem 0.5rem; text-align: center;">
                    <div style="font-size: 0.72rem; font-weight: 700; color: #075985; margin-bottom: 0.25rem;">کۆی قەرز</div>
                    <div class="num" style="font-size: 1.4rem; font-weight: 800; color: #0284c7;">
                        {{ fmt_num($customerStats['total_debt']) }}
                    </div>
                </div>

            </div>

            {{-- پرۆفایلی کڕیار (دەستە ڕاست) --}}
            <div style="display: flex; align-items: center; gap: 1rem;">
                <div style="text-align: right;">
                    <div style="font-size: 1.25rem; font-weight: 800; color: #1e293b;">
                        {{ $selectedCustomer->name }}
                    </div>
                    <div style="display: flex; items-center: center; gap: 0.5rem; font-size: 0.78rem; color: #64748b; font-weight: 600; margin-top: 0.2rem;" dir="ltr">
                        <span># C-{{ $selectedCustomer->id }}</span>
                        <span>•</span>
                        <span class="num">{{ $selectedCustomer->phone ?? '—' }} 📞</span>
                    </div>
                </div>
                {{-- ئەڤەتاری سوور بە پیتی یەکەم --}}
                <div style="width: 3.25rem; height: 3.25rem; border-radius: 50%; background: #dc2626; color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 1.35rem; font-weight: 800; flex-shrink: 0; box-shadow: 0 4px 10px rgba(220, 38, 38, 0.25);">
                    {{ mb_substr($selectedCustomer->name, 0, 1) }}
                </div>
            </div>

        </div>

        {{-- خشتەی سەرەکی وەسڵ و قەرزەکانی کڕیار --}}
        <div style="background: #ffffff; border-radius: 1.25rem; border: 1px solid #f1f5f9; box-shadow: 0 2px 10px rgba(0,0,0,0.03); overflow: hidden;">

            {{-- سەردێڕی خشتە --}}
            <div style="padding: 1.25rem 1.5rem; display: flex; align-items: center; gap: 0.5rem; font-weight: 800; font-size: 1.05rem; color: #1e293b; border-bottom: 1px solid #f8fafc;">
                <svg style="width: 1.25rem; height: 1.25rem; color: #475569;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="8" y1="6" x2="21" y2="6"/>
                    <line x1="8" y1="12" x2="21" y2="12"/>
                    <line x1="8" y1="18" x2="21" y2="18"/>
                    <line x1="3" y1="6" x2="3.01" y2="6"/>
                    <line x1="3" y1="12" x2="3.01" y2="12"/>
                    <line x1="3" y1="18" x2="3.01" y2="18"/>
                </svg>
                <span>قەرزەکانی {{ $selectedCustomer->name }}</span>
            </div>

            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; text-align: right; font-size: 0.875rem;">
                    <thead>
                        <tr style="border-bottom: 1px solid #f1f5f9; color: #64748b; font-size: 0.75rem; font-weight: 700;">
                            <th style="padding: 0.75rem 1rem; width: 3rem; text-align: center;">#</th>
                            <th style="padding: 0.75rem 1rem; text-align: center;">ناو (ژمارە)</th>
                            <th style="padding: 0.75rem 1rem; text-align: center;">تێبینی</th>
                            <th style="padding: 0.75rem 1rem; text-align: center;">بەروار</th>
                            <th style="padding: 0.75rem 1rem; text-align: center;">داشکاندن</th>
                            <th style="padding: 0.75rem 1rem; text-align: center;">کۆی قەرز</th>
                            <th style="padding: 0.75rem 1rem; text-align: center;">بڕی واسڵکردن</th>
                            <th style="padding: 0.75rem 1rem; text-align: center;">قەرزی ئێستا</th>
                            <th style="padding: 0.75rem 1rem; width: 6.5rem; text-align: center;">کردار</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- ئەگەر باڵانسی سەرەتایی هەبێت --}}
                        @if ($customerStats['opening_iqd'] > 0)
                            <tr style="border-bottom: 1px solid #f8fafc; background: #fffdf5;">
                                <td class="num" style="padding: 0.85rem 1rem; text-align: center; color: #64748b; font-size: 0.85rem; font-weight: 600;">—</td>
                                <td style="padding: 0.85rem 1rem; text-align: center; color: #d97706; font-weight: 800;">قەرزی کۆن</td>
                                <td style="padding: 0.85rem 1rem; text-align: center; color: #64748b; font-size: 0.8rem;">{{ $selectedCustomer->note ?: 'باڵانسی سەرەتایی' }}</td>
                                <td style="padding: 0.85rem 1rem; text-align: center; color: #64748b; font-size: 0.8rem;">—</td>
                                <td style="padding: 0.85rem 1rem; text-align: center; color: #64748b; font-size: 0.8rem;">-</td>
                                <td class="num" style="padding: 0.85rem 1rem; text-align: center; font-weight: 700; color: #334155;">{{ fmt_num($customerStats['opening_iqd']) }}</td>
                                <td class="num" style="padding: 0.85rem 1rem; text-align: center; font-weight: 700; color: #10b981;">0</td>
                                <td class="num" style="padding: 0.85rem 1rem; text-align: center; font-weight: 800; color: #dc2626;">{{ fmt_num($customerStats['opening_iqd']) }}</td>
                                <td style="padding: 0.85rem 1rem; text-align: center; color: #94a3b8; font-size: 0.8rem;">—</td>
                            </tr>
                        @endif

                        @forelse ($customerOrders as $idx => $row)
                            <tr style="border-bottom: 1px solid #f8fafc; transition: background 0.15s;"
                                onmouseover="this.style.background='#fbfcfd'"
                                onmouseout="this.style.background='transparent'">

                                {{-- # --}}
                                <td class="num" style="padding: 0.85rem 1rem; text-align: center; color: #64748b; font-size: 0.85rem; font-weight: 600;">
                                    {{ $idx + 1 }}
                                </td>

                                {{-- ناو (ژمارەی وەسڵ) بە ڕەنگی سووری پەمەیی تۆخ --}}
                                <td style="padding: 0.85rem 1rem; text-align: center;">
                                    <a href="{{ route('orders.show', $row['order']) }}"
                                       class="num"
                                       style="color: #e11d48; font-weight: 800; text-decoration: none; font-size: 0.9rem;">
                                        {{ $row['invoice_no'] }}
                                    </a>
                                </td>

                                {{-- تێبینی --}}
                                <td style="padding: 0.85rem 1rem; text-align: center; color: #64748b; font-size: 0.8rem;">
                                    {{ $row['note'] ?: '-' }}
                                </td>

                                {{-- بەروار --}}
                                <td class="num" style="padding: 0.85rem 1rem; text-align: center; color: #475569; font-size: 0.8rem;">
                                    {{ $row['order_date'] ? fmt_date($row['order_date']) : '-' }}
                                </td>

                                {{-- داشکاندن --}}
                                <td class="num" style="padding: 0.85rem 1rem; text-align: center; color: #eab308; font-weight: 700;">
                                    {{ $row['discount'] > 0 ? fmt_num($row['discount']) : '-' }}
                                </td>

                                {{-- کۆی قەرز --}}
                                <td class="num" style="padding: 0.85rem 1rem; text-align: center; font-weight: 700; color: #334155;">
                                    {{ fmt_num($row['total']) }}
                                </td>

                                {{-- بڕی واسڵکردن --}}
                                <td class="num" style="padding: 0.85rem 1rem; text-align: center; font-weight: 700; color: #10b981;">
                                    {{ fmt_num($row['paid']) }}
                                </td>

                                {{-- قەرزی ئێستا --}}
                                <td class="num" style="padding: 0.85rem 1rem; text-align: center; font-weight: 800; color: #dc2626;">
                                    {{ fmt_num($row['remaining']) }}
                                </td>

                                {{-- کردار (٣ دوگمەی شیک: سڕینەوە، بینین، دەستکاری) --}}
                                <td style="padding: 0.85rem 1rem; text-align: center;">
                                    <div style="display: inline-flex; align-items: center; gap: 0.35rem; justify-content: center;">
                                        {{-- سڕینەوە --}}
                                        <form method="POST" action="{{ route('orders.destroy', $row['order']) }}"
                                              onsubmit="return confirm('دڵنیایت لە سڕینەوەی ئەم وەسڵە؟');"
                                              style="display: inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    style="width: 1.85rem; height: 1.85rem; border-radius: 0.4rem; background: #ffe4e6; color: #e11d48; border: none; display: inline-flex; align-items: center; justify-content: center; cursor: pointer;"
                                                    title="سڕینەوە">
                                                <svg style="width: 0.95rem; height: 0.95rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <polyline points="3 6 5 6 21 6"/>
                                                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                                </svg>
                                            </button>
                                        </form>

                                        {{-- بینین --}}
                                        <a href="{{ route('orders.show', $row['order']) }}"
                                           style="width: 1.85rem; height: 1.85rem; border-radius: 0.4rem; background: #fef3c7; color: #d97706; display: inline-flex; align-items: center; justify-content: center; text-decoration: none;"
                                           title="بینین">
                                            <svg style="width: 0.95rem; height: 0.95rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                                <circle cx="12" cy="12" r="3"/>
                                            </svg>
                                        </a>

                                        {{-- دەستکاری --}}
                                        <a href="{{ route('orders.edit', $row['order']) }}"
                                           style="width: 1.85rem; height: 1.85rem; border-radius: 0.4rem; background: #fef9c3; color: #ca8a04; display: inline-flex; align-items: center; justify-content: center; text-decoration: none;"
                                           title="دەستکاری">
                                            <svg style="width: 0.95rem; height: 0.95rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                            </svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            @if ($customerStats['opening_iqd'] <= 0)
                                <tr>
                                    <td colspan="9" style="padding: 2.5rem 1rem; text-align: center; color: #94a3b8; font-size: 0.875rem;">
                                        هیچ وەسڵ یان قەرزێک بۆ ئەم کڕیارە تۆمارنەکراوە.
                                    </td>
                                </tr>
                            @endif
                        @endforelse
                    </tbody>

                    {{-- کۆی گشتی خوارەوە --}}
                    <tfoot>
                        <tr style="border-top: 2px solid #f1f5f9; background: #fafafa; font-weight: 800;">
                            <td colspan="4" style="padding: 1rem; text-align: center; color: #1e293b; font-size: 0.95rem;">
                                کۆی گشتی
                            </td>
                            <td class="num" style="padding: 1rem; text-align: center; color: #ca8a04; font-size: 0.9rem;">
                                {{ fmt_num($customerStats['total_discount']) }}
                            </td>
                            <td class="num" style="padding: 1rem; text-align: center; color: #1e293b; font-size: 0.95rem;">
                                {{ fmt_num($customerStats['total_debt']) }}
                            </td>
                            <td class="num" style="padding: 1rem; text-align: center; color: #10b981; font-size: 0.95rem;">
                                {{ fmt_num($customerStats['paid_total']) }}
                            </td>
                            <td class="num" style="padding: 1rem; text-align: center; color: #dc2626; font-size: 1.05rem;">
                                {{ fmt_num($customerStats['remaining_debt']) }}
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

        </div>

    @else
        {{-- ════════════════════════════════════════════════════════════════ --}}
        {{-- ئـ) خشتەی گشتی هەموو قەرزدارەکان (Main Debts List)               --}}
        {{-- ════════════════════════════════════════════════════════════════ --}}

        <div style="background: #ffffff; border-radius: 1.25rem; border: 1px solid #f1f5f9; box-shadow: 0 2px 10px rgba(0,0,0,0.03); overflow: hidden;">

            {{-- سەرپەڕەی خشتە: ناونیشان و گەڕان --}}
            <div style="padding: 1.25rem 1.5rem; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #f8fafc; flex-wrap: wrap; gap: 1rem;">
                {{-- دەستە ڕاست --}}
                <div style="display: flex; align-items: center; gap: 0.5rem; font-weight: 800; font-size: 1.05rem; color: #1e293b;">
                    <svg style="width: 1.25rem; height: 1.25rem; color: #475569;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                    <span>قەرزدارەکان</span>
                </div>

                {{-- دەستە چەپ: گەڕان --}}
                <div style="position: relative; width: 16rem; max-width: 100%;">
                    <input type="text"
                           x-model="searchQuery"
                           placeholder="گەڕان..."
                           style="width: 100%; padding: 0.5rem 2.25rem 0.5rem 1rem; font-size: 0.8rem; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 0.75rem; color: #1e293b; outline: none; transition: border-color 0.15s;"
                           onfocus="this.style.borderColor='#6366f1'"
                           onblur="this.style.borderColor='#e2e8f0'">
                    <div style="position: absolute; right: 0.75rem; top: 50%; transform: translateY(-50%); pointer-events: none; color: #94a3b8; display: flex; align-items: center;">
                        <svg style="width: 1rem; height: 1rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8"/>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- خشتەی سەرەکی --}}
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; text-align: right; font-size: 0.875rem;">
                    <thead>
                        <tr style="border-bottom: 1px solid #f1f5f9; color: #64748b; font-size: 0.75rem; font-weight: 700;">
                            <th style="padding: 0.75rem 1rem; width: 3.5rem; text-align: center;">#</th>
                            <th style="padding: 0.75rem 1.25rem; text-align: right;">کڕیار</th>
                            <th style="padding: 0.75rem 1rem; text-align: center;">تەلەفۆن</th>
                            <th style="padding: 0.75rem 1rem; text-align: center;">ژمارەی قەرز</th>
                            <th style="padding: 0.75rem 1rem; text-align: center;">کۆی بڕ</th>
                            <th style="padding: 0.75rem 1rem; text-align: center;">دراو</th>
                            <th style="padding: 0.75rem 1.25rem; text-align: center;">قەرزی ماوە</th>
                            <th style="padding: 0.75rem 1rem; width: 4.5rem; text-align: center;">کردار</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(row, index) in filteredCustomers" :key="row.id">
                            <tr style="border-bottom: 1px solid #f8fafc; transition: background 0.15s;"
                                onmouseover="this.style.background='#fbfcfd'"
                                onmouseout="this.style.background='transparent'">

                                {{-- # --}}
                                <td class="num" style="padding: 1rem; text-align: center; color: #64748b; font-size: 0.85rem; font-weight: 600;" x-text="index + 1"></td>

                                {{-- کڕیار بە ناوی سوور و ئایکۆنی ڕەساسی/سوور --}}
                                <td style="padding: 1rem 1.25rem; text-align: right;">
                                    <a :href="'{{ route('debts.index') }}?customer=' + row.id"
                                       style="display: inline-flex; align-items: center; gap: 0.5rem; text-decoration: none; color: #dc2626; font-weight: 700; font-size: 0.9rem;">
                                        <svg style="width: 1.1rem; height: 1.1rem; color: #dc2626; flex-shrink: 0;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                            <circle cx="12" cy="7" r="4"/>
                                        </svg>
                                        <span x-text="row.name"></span>
                                    </a>
                                </td>

                                {{-- تەلەفۆن --}}
                                <td class="num" dir="ltr" style="padding: 1rem; text-align: center; color: #334155; font-size: 0.85rem; font-weight: 500;" x-text="row.phone || '—'"></td>

                                {{-- ژمارەی قەرز: باجی شین + باجی سووری چالاک وەک وێنەکە --}}
                                <td style="padding: 1rem; text-align: center;">
                                    <div style="display: inline-flex; align-items: center; gap: 0.4rem; justify-content: center;">
                                        {{-- باجی شین --}}
                                        <span class="num" style="background: #e0f2fe; color: #0284c7; padding: 0.2rem 0.55rem; border-radius: 0.375rem; font-size: 0.72rem; font-weight: 700; display: inline-block; white-space: nowrap;"
                                              x-text="row.orders_count + ' قەرز'">
                                        </span>
                                        {{-- باجی سوور ئەگەر وەسڵی نەدراوی هەبێت --}}
                                        <template x-if="row.active_orders_count > 0">
                                            <span class="num" style="background: #ffe4e6; color: #e11d48; padding: 0.2rem 0.55rem; border-radius: 0.375rem; font-size: 0.72rem; font-weight: 700; display: inline-block; white-space: nowrap;"
                                                  x-text="row.active_orders_count + ' چالاک'">
                                            </span>
                                        </template>
                                    </div>
                                </td>

                                {{-- کۆی بڕ --}}
                                <td class="num" style="padding: 1rem; text-align: center; font-weight: 700; color: #334155; font-size: 0.88rem;" x-text="formatNumber(row.total_amount)"></td>

                                {{-- دراو --}}
                                <td class="num" style="padding: 1rem; text-align: center; font-weight: 700; font-size: 0.88rem;"
                                    :style="{ color: row.total_paid > 0 ? '#10b981' : '#64748b' }"
                                    x-text="formatNumber(row.total_paid)">
                                </td>

                                {{-- قەرزی ماوە: باجی سووری پان یان سەوزی تەواو دراوە --}}
                                <td style="padding: 1rem 1.25rem; text-align: center;">
                                    <template x-if="row.remaining > 0.5">
                                        <span class="num" style="background: #fee2e2; color: #dc2626; font-weight: 800; padding: 0.25rem 0.85rem; border-radius: 0.375rem; font-size: 0.78rem; display: inline-block;"
                                              x-text="formatNumber(row.remaining)">
                                        </span>
                                    </template>
                                    <template x-if="row.remaining <= 0.5">
                                        <span style="background: #dcfce7; color: #16a34a; font-weight: 800; padding: 0.25rem 0.85rem; border-radius: 0.375rem; font-size: 0.75rem; display: inline-block;">
                                            تەواو دراوە
                                        </span>
                                    </template>
                                </td>

                                {{-- کردار: ئایکۆنی چاوی زەرد/پڕتەقاڵی کە دەیباتە پەڕەی قەرزەکانی کڕیار --}}
                                <td style="padding: 1rem; text-align: center;">
                                    <a :href="'{{ route('debts.index') }}?customer=' + row.id"
                                       style="width: 2.1rem; height: 2.1rem; border-radius: 0.5rem; background: #fef3c7; color: #d97706; display: inline-flex; align-items: center; justify-content: center; text-decoration: none; transition: background 0.15s;"
                                       onmouseover="this.style.background='#fde68a'"
                                       onmouseout="this.style.background='#fef3c7'"
                                       title="قەرزەکانی ئەم کڕیارە">
                                        <svg style="width: 1.15rem; height: 1.15rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                            <circle cx="12" cy="12" r="3"/>
                                        </svg>
                                    </a>
                                </td>
                            </tr>
                        </template>

                        <tr x-show="filteredCustomers.length === 0" x-cloak>
                            <td colspan="8" style="padding: 3rem 1rem; text-align: center; color: #94a3b8; font-size: 0.875rem;">
                                هیچ کڕیارێک یان قەرزدارێک نەدۆزرایەوە.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

        </div>
    @endif

    {{-- ٤. مۆداڵی زیادکردنی قەرزی کۆن --}}
    <div x-show="openOldDebtModal"
         x-cloak
         style="position: fixed; inset: 0; z-index: 999; display: flex; align-items: center; justify-content: center; padding: 1rem; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(2px);"
         @keydown.escape.window="openOldDebtModal = false">
        <div style="background: #ffffff; border-radius: 1rem; width: 100%; max-width: 32rem; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); overflow: hidden;"
             @click.outside="openOldDebtModal = false">

            <div style="padding: 1rem 1.25rem; background: #f8fafc; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between;">
                <div style="display: flex; align-items: center; gap: 0.5rem; font-weight: 800; color: #1e293b;">
                    <span style="color: #4f46e5;">💳</span>
                    <span>زیادکردنی قەرزی کۆن (باڵانسی سەرەتایی)</span>
                </div>
                <button type="button" @click="openOldDebtModal = false" style="background: none; border: none; font-size: 1.5rem; color: #94a3b8; cursor: pointer; line-height: 1;">
                    &times;
                </button>
            </div>

            <form method="POST" action="{{ route('debts.old-debt') }}" style="padding: 1.25rem; display: flex; flex-direction: column; gap: 1rem;">
                @csrf

                {{-- کڕیار --}}
                <div x-data="{ isNewCustomer: false }">
                    <label class="label" style="font-weight: 700; font-size: 0.8rem; margin-bottom: 0.35rem; display: block;">کڕیار <span style="color: #ef4444;">*</span></label>
                    <div style="display: flex; gap: 0.5rem; margin-bottom: 0.5rem;">
                        <select name="customer_id" class="field" style="font-weight: 700; flex: 1;"
                                x-show="!isNewCustomer"
                                :required="!isNewCustomer">
                            <option value="">— کڕیارێک هەڵبژێرە —</option>
                            @foreach ($allCustomersList as $c)
                                <option value="{{ $c->id }}">{{ $c->name }} {{ $c->phone ? "({$c->phone})" : '' }}</option>
                            @endforeach
                        </select>
                        <button type="button" @click="isNewCustomer = !isNewCustomer"
                                style="padding: 0.4rem 0.75rem; border: 1px solid #cbd5e1; border-radius: 0.5rem; background: #ffffff; font-size: 0.75rem; font-weight: 700; color: #475569; cursor: pointer; white-space: nowrap;">
                            <span x-text="isNewCustomer ? '👈 هەڵبژاردنی کڕیار' : '➕ کڕیاری نوێ'"></span>
                        </button>
                    </div>

                    {{-- کڕیاری نوێ --}}
                    <div x-show="isNewCustomer" x-cloak style="padding: 0.75rem; background: #f0fdf4; border-radius: 0.5rem; border: 1px solid #bbf7d0; display: flex; flex-direction: column; gap: 0.5rem;">
                        <div>
                            <label style="font-size: 0.75rem; font-weight: 700; color: #166534; display: block; margin-bottom: 0.25rem;">ناوی کڕیار <span style="color: #ef4444;">*</span></label>
                            <input type="text" name="new_customer_name" class="field" style="font-size: 0.8rem;" placeholder="ناوی تەواو..." :required="isNewCustomer">
                        </div>
                        <div>
                            <label style="font-size: 0.75rem; font-weight: 700; color: #166534; display: block; margin-bottom: 0.25rem;">ژمارەی مۆبایل</label>
                            <input type="text" name="new_customer_phone" class="field num" style="font-size: 0.8rem;" placeholder="0750...">
                        </div>
                    </div>
                </div>

                {{-- بڕی پارە و دراو --}}
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                    <div>
                        <label class="label" style="font-weight: 700; font-size: 0.8rem; margin-bottom: 0.35rem; display: block;">بڕی قەرز <span style="color: #ef4444;">*</span></label>
                        <input type="number" step="any" min="0.01" name="amount" class="field num font-bold" placeholder="0" required>
                    </div>
                    <div>
                        <label class="label" style="font-weight: 700; font-size: 0.8rem; margin-bottom: 0.35rem; display: block;">دراو <span style="color: #ef4444;">*</span></label>
                        <select name="currency" class="field" style="font-weight: 700;">
                            <option value="IQD">دینار (IQD)</option>
                            <option value="USD">دۆلار ($ USD)</option>
                        </select>
                    </div>
                </div>

                {{-- تێبینی --}}
                <div>
                    <label class="label" style="font-weight: 700; font-size: 0.8rem; margin-bottom: 0.35rem; display: block;">تێبینی</label>
                    <input type="text" name="note" class="field" style="font-size: 0.85rem;" placeholder="تێبینی گشتی قەرز...">
                </div>

                {{-- دوگمەکان --}}
                <div style="display: flex; justify-content: flex-end; gap: 0.5rem; margin-top: 0.5rem; padding-top: 0.75rem; border-top: 1px solid #f1f5f9;">
                    <button type="button" @click="openOldDebtModal = false"
                            style="padding: 0.5rem 1rem; border-radius: 0.5rem; background: #f1f5f9; color: #475569; font-weight: 700; font-size: 0.85rem; border: none; cursor: pointer;">
                        داخستن
                    </button>
                    <button type="submit"
                            style="padding: 0.5rem 1.25rem; border-radius: 0.5rem; background: #4f46e5; color: #ffffff; font-weight: 700; font-size: 0.85rem; border: none; cursor: pointer;">
                        تۆمارکردن
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
function debtsPage(initialCustomers) {
    return {
        customers: initialCustomers,
        searchQuery: '',
        openOldDebtModal: false,

        get filteredCustomers() {
            if (!this.searchQuery.trim()) {
                return this.customers;
            }
            const q = this.searchQuery.toLowerCase().trim();
            return this.customers.filter(c => {
                const name = (c.name || '').toLowerCase();
                const phone = (c.phone || '').toLowerCase();
                return name.includes(q) || phone.includes(q);
            });
        },

        formatNumber(val) {
            if (val === null || val === undefined || isNaN(val)) return '0';
            return Number(val).toLocaleString('en-US');
        }
    }
}
</script>
@endsection
