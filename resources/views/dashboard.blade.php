@extends('layouts.menu')
@section('title', 'داشبۆردی سەرەکی')

@section('content')
<style>
    @media (max-width: 768px) {
        .dash-kpi-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            gap: 0.625rem !important;
        }
        .dash-kpi-grid > div {
            padding: 0.85rem 1rem !important;
            border-radius: 0.85rem !important;
        }
        .dash-kpi-grid .num {
            font-size: 1.35rem !important;
        }
        .dash-tables-grid {
            grid-template-columns: 1fr !important;
        }
        .dash-sales-boxes {
            grid-template-columns: 1fr !important;
            gap: 0.75rem !important;
        }
    }
    @media (max-width: 480px) {
        .dash-kpi-grid {
            grid-template-columns: 1fr !important;
            gap: 0.5rem !important;
        }
        .dash-kpi-grid > div[style*="grid-column: span 2"] {
            grid-column: span 1 !important;
        }
        .dash-actions-grid {
            display: grid !important;
            grid-template-columns: 1fr 1fr !important;
            gap: 0.5rem !important;
        }
        .dash-actions-grid a {
            justify-content: center !important;
            padding: 0.65rem 0.5rem !important;
            font-size: 0.78rem !important;
        }
    }
</style>
<div x-data="liveDashboard()" style="display: flex; flex-direction: column; gap: 1.25rem;">

    {{-- ١. سەردێڕی داشبۆرد و کاتژمێری زیندوو --}}
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
        {{-- ڕاست: ناونیشانی داشبۆرد --}}
        <div style="display: flex; align-items: center; gap: 0.75rem;">
            <div style="width: 2.5rem; height: 2.5rem; border-radius: 0.75rem; background: #e0f2fe; color: #0284c7; display: flex; align-items: center; justify-content: center;">
                <svg style="width: 1.35rem; height: 1.35rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <polyline points="12 6 12 12 16 14"/>
                </svg>
            </div>
            <h1 style="font-size: 1.5rem; font-weight: 800; color: #1e293b; margin: 0;">داشبۆرد</h1>
        </div>

        {{-- چەپ: کاتژمێر و بەرواری زیندوو --}}
        <div style="background: linear-gradient(135deg, #6366f1, #8b5cf6); color: #ffffff; padding: 0.5rem 1.25rem; border-radius: 0.85rem; font-weight: 800; font-size: 0.88rem; display: inline-flex; align-items: center; gap: 0.75rem; box-shadow: 0 4px 12px rgba(99, 102, 241, 0.25);">
            <div class="num" style="direction: ltr; font-size: 0.95rem;" x-text="timeString"></div>
            <span style="opacity: 0.6;">|</span>
            <div x-text="dateString" style="font-size: 0.8rem; font-weight: 700;"></div>
            <svg style="width: 1.1rem; height: 1.1rem; opacity: 0.9;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"/>
                <polyline points="12 6 12 12 16 14"/>
            </svg>
        </div>
    </div>

    {{-- ٢. تابلۆی کارتە ئامارییەکان (٤ ستوون وەک وێنەکە) --}}
    <div class="dash-kpi-grid" style="display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 1rem;">

        {{-- ڕیزی یەکەم --}}

        {{-- کارتی ١ (ڕاست): کۆی وەسڵەکان --}}
        <div style="background: #ffffff; border-radius: 1rem; padding: 1.15rem 1.25rem; border: 1px solid #bfdbfe; border-right: 4px solid #3b82f6; box-shadow: 0 2px 8px rgba(0,0,0,0.03); display: flex; align-items: center; justify-content: space-between;">
            <div>
                <div class="num" style="font-size: 1.65rem; font-weight: 800; color: #0f172a; line-height: 1.2;">
                    {{ fmt_num($totalOrdersCount ?? $openOrders) }}
                </div>
                <div style="font-size: 0.78rem; font-weight: 600; color: #64748b; margin-top: 0.25rem;">
                    کۆی وەسڵەکان
                </div>
            </div>
            <div style="width: 2.6rem; height: 2.6rem; border-radius: 0.75rem; background: #eff6ff; color: #3b82f6; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <svg style="width: 1.3rem; height: 1.3rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                    <polyline points="14 2 14 8 20 8"/>
                    <line x1="16" y1="13" x2="8" y2="13"/>
                    <line x1="16" y1="17" x2="8" y2="17"/>
                </svg>
            </div>
        </div>

        {{-- کارتی ٢: کڕیاران --}}
        <div style="background: #ffffff; border-radius: 1rem; padding: 1.15rem 1.25rem; border: 1px solid #bae6fd; border-right: 4px solid #0ea5e9; box-shadow: 0 2px 8px rgba(0,0,0,0.03); display: flex; align-items: center; justify-content: space-between;">
            <div>
                <div class="num" style="font-size: 1.65rem; font-weight: 800; color: #0f172a; line-height: 1.2;">
                    {{ fmt_num($totalCustomersCount ?? 0) }}
                </div>
                <div style="font-size: 0.78rem; font-weight: 600; color: #64748b; margin-top: 0.25rem;">
                    کڕیاران
                </div>
            </div>
            <div style="width: 2.6rem; height: 2.6rem; border-radius: 0.75rem; background: #f0f9ff; color: #0ea5e9; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <svg style="width: 1.3rem; height: 1.3rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M17 11l3-3m-3 0l3 3m-3-3v6"/>
                    <path d="M3 21v-2a4 4 0 0 1 4-4h4a4 4 0 0 1 4 4v2"/>
                </svg>
            </div>
        </div>

        {{-- کارتی ٣: کەلوپەل و مەواد --}}
        <div style="background: #ffffff; border-radius: 1rem; padding: 1.15rem 1.25rem; border: 1px solid #e9d5ff; border-right: 4px solid #a855f7; box-shadow: 0 2px 8px rgba(0,0,0,0.03); display: flex; align-items: center; justify-content: space-between;">
            <div>
                <div class="num" style="font-size: 1.65rem; font-weight: 800; color: #0f172a; line-height: 1.2;">
                    {{ fmt_num($itemsCount ?? 0) }}
                </div>
                <div style="font-size: 0.78rem; font-weight: 600; color: #64748b; margin-top: 0.25rem;">
                    کەلوپەل و مەواد
                </div>
            </div>
            <div style="width: 2.6rem; height: 2.6rem; border-radius: 0.75rem; background: #faf5ff; color: #a855f7; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <svg style="width: 1.3rem; height: 1.3rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                    <polyline points="3.27 6.96 12 12.01 20.73 6.96"/>
                    <line x1="12" y1="22.08" x2="12" y2="12"/>
                </svg>
            </div>
        </div>

        {{-- کارتی ٤: وەسڵی ئەمڕۆ --}}
        <div style="background: #ffffff; border-radius: 1rem; padding: 1.15rem 1.25rem; border: 1px solid #fde68a; border-right: 4px solid #f59e0b; box-shadow: 0 2px 8px rgba(0,0,0,0.03); display: flex; align-items: center; justify-content: space-between;">
            <div>
                <div class="num" style="font-size: 1.65rem; font-weight: 800; color: #0f172a; line-height: 1.2;">
                    {{ fmt_num($todayOrders ?? 0) }}
                </div>
                <div style="font-size: 0.78rem; font-weight: 600; color: #64748b; margin-top: 0.25rem;">
                    وەسڵی ئەمڕۆ
                </div>
            </div>
            <div style="width: 2.6rem; height: 2.6rem; border-radius: 0.75rem; background: #fefce8; color: #f59e0b; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <svg style="width: 1.3rem; height: 1.3rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="9"/>
                    <path d="M12 7v5l3 3"/>
                </svg>
            </div>
        </div>

        {{-- ڕیزی دووەم --}}

        {{-- کارتی ٥: وەسڵی لە کاردا --}}
        <div style="background: #ffffff; border-radius: 1rem; padding: 1.15rem 1.25rem; border: 1px solid #a7f3d0; border-right: 4px solid #10b981; box-shadow: 0 2px 8px rgba(0,0,0,0.03); display: flex; align-items: center; justify-content: space-between;">
            <div>
                <div class="num" style="font-size: 1.65rem; font-weight: 800; color: #0f172a; line-height: 1.2;">
                    {{ fmt_num($openOrders ?? 0) }}
                </div>
                <div style="font-size: 0.78rem; font-weight: 600; color: #64748b; margin-top: 0.25rem;">
                    وەسڵی لە کاردا
                </div>
            </div>
            <div style="width: 2.6rem; height: 2.6rem; border-radius: 0.75rem; background: #f0fdf4; color: #10b981; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <svg style="width: 1.3rem; height: 1.3rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                    <polyline points="22 4 12 14.01 9 11.01"/>
                </svg>
            </div>
        </div>

        {{-- کارتی ٦: کارمەندان --}}
        <div style="background: #ffffff; border-radius: 1rem; padding: 1.15rem 1.25rem; border: 1px solid #c7d2fe; border-right: 4px solid #6366f1; box-shadow: 0 2px 8px rgba(0,0,0,0.03); display: flex; align-items: center; justify-content: space-between;">
            <div>
                <div class="num" style="font-size: 1.65rem; font-weight: 800; color: #0f172a; line-height: 1.2;">
                    {{ fmt_num($totalEmployees ?? 0) }}
                </div>
                <div style="font-size: 0.78rem; font-weight: 600; color: #64748b; margin-top: 0.25rem;">
                    کارمەندان
                </div>
            </div>
            <div style="width: 2.6rem; height: 2.6rem; border-radius: 0.75rem; background: #eef2ff; color: #6366f1; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <svg style="width: 1.3rem; height: 1.3rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
            </div>
        </div>

        {{-- کارتی ٧: فرۆشتنی ئەم مانگە --}}
        <div style="background: #ffffff; border-radius: 1rem; padding: 1.15rem 1.25rem; border: 1px solid #99f6e4; border-right: 4px solid #14b8a6; box-shadow: 0 2px 8px rgba(0,0,0,0.03); display: flex; align-items: center; justify-content: space-between;">
            <div>
                <div class="num" style="font-size: 1.45rem; font-weight: 800; color: #0f172a; line-height: 1.2;">
                    {{ fmt_num($monthSales ?? 0) }}
                </div>
                <div style="font-size: 0.78rem; font-weight: 600; color: #64748b; margin-top: 0.25rem;">
                    فرۆشتنی ئەم مانگە
                </div>
            </div>
            <div style="width: 2.6rem; height: 2.6rem; border-radius: 0.75rem; background: #f0fdfa; color: #14b8a6; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <svg style="width: 1.3rem; height: 1.3rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/>
                    <polyline points="17 6 23 6 23 12"/>
                </svg>
            </div>
        </div>

        {{-- کارتی ٨: خەرجی ئەم مانگە --}}
        <div style="background: #ffffff; border-radius: 1rem; padding: 1.15rem 1.25rem; border: 1px solid #fecdd3; border-right: 4px solid #f43f5e; box-shadow: 0 2px 8px rgba(0,0,0,0.03); display: flex; align-items: center; justify-content: space-between;">
            <div>
                <div class="num" style="font-size: 1.45rem; font-weight: 800; color: #0f172a; line-height: 1.2;">
                    {{ fmt_num($monthExpenses ?? 0) }}
                </div>
                <div style="font-size: 0.78rem; font-weight: 600; color: #64748b; margin-top: 0.25rem;">
                    خەرجی ئەم مانگە
                </div>
            </div>
            <div style="width: 2.6rem; height: 2.6rem; border-radius: 0.75rem; background: #fff1f2; color: #f43f5e; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <svg style="width: 1.3rem; height: 1.3rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="23 18 13.5 8.5 8.5 13.5 1 6"/>
                    <polyline points="17 18 23 18 23 12"/>
                </svg>
            </div>
        </div>

        {{-- ڕیزی سێیەم --}}

        {{-- کارتی ٩: کۆی قەرزەکان --}}
        <div style="grid-column: span 2; background: #ffffff; border-radius: 1rem; padding: 1.15rem 1.25rem; border: 1px solid #fecdd3; border-right: 4px solid #f43f5e; box-shadow: 0 2px 8px rgba(0,0,0,0.03); display: flex; align-items: center; justify-content: space-between;">
            <div>
                <div class="num" style="font-size: 1.65rem; font-weight: 800; color: #dc2626; line-height: 1.2;">
                    {{ fmt_num($receivables ?? 0) }}
                </div>
                <div style="font-size: 0.78rem; font-weight: 600; color: #64748b; margin-top: 0.25rem;">
                    کۆی قەرزەکان (کڕیاران)
                </div>
            </div>
            <div style="width: 2.6rem; height: 2.6rem; border-radius: 0.75rem; background: #ffe4e6; color: #f43f5e; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <svg style="width: 1.3rem; height: 1.3rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="2" y="5" width="20" height="14" rx="2"/>
                    <line x1="2" y1="10" x2="22" y2="10"/>
                </svg>
            </div>
        </div>

        {{-- کارتی ١٠: ئامێر و دەرەوە --}}
        <div style="grid-column: span 2; background: #ffffff; border-radius: 1rem; padding: 1.15rem 1.25rem; border: 1px solid #fde68a; border-right: 4px solid #f59e0b; box-shadow: 0 2px 8px rgba(0,0,0,0.03); display: flex; align-items: center; justify-content: space-between;">
            <div>
                <div class="num" style="font-size: 1.65rem; font-weight: 800; color: #0f172a; line-height: 1.2;">
                    {{ fmt_num(($totalSuppliersCount ?? 0) + ($activeJobsCount ?? 0)) }}
                </div>
                <div style="font-size: 0.78rem; font-weight: 600; color: #64748b; margin-top: 0.25rem;">
                    ئامێر و ئیشی خاریجی
                </div>
            </div>
            <div style="width: 2.6rem; height: 2.6rem; border-radius: 0.75rem; background: #fefce8; color: #f59e0b; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <svg style="width: 1.3rem; height: 1.3rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>
                </svg>
            </div>
        </div>

    </div>

    {{-- ٣. کارتی پوختەی فرۆش و ئەمڕۆ (وەک وێنەکە) --}}
    <div style="background: #ffffff; border-radius: 1.25rem; padding: 1.25rem 1.5rem; border: 1px solid #fecdd3; border-right: 4px solid #f43f5e; box-shadow: 0 2px 10px rgba(0,0,0,0.03);">
        <div style="display: flex; align-items: center; gap: 0.5rem; font-weight: 800; font-size: 1rem; color: #1e293b; margin-bottom: 1rem;">
            <span>⚠️</span>
            <span>دۆخی گشتی و فرۆش</span>
        </div>

        <div class="dash-sales-boxes" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            {{-- سندوقی فرۆشی ئەمڕۆ --}}
            <div style="background: #fff1f2; border: 1px solid #fecdd3; border-radius: 0.85rem; padding: 1.25rem; text-align: center;">
                <div style="font-size: 0.8rem; font-weight: 700; color: #9f1239; margin-bottom: 0.35rem;">فرۆشی ئەمڕۆ</div>
                <div class="num" style="font-size: 1.75rem; font-weight: 900; color: #e11d48;">
                    {{ fmt_num($todaySales ?? 0) }}
                </div>
            </div>

            {{-- سندوقی کۆی گشتی فرۆشی مانگ --}}
            <div style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 0.85rem; padding: 1.25rem; text-align: center;">
                <div style="font-size: 0.8rem; font-weight: 700; color: #92400e; margin-bottom: 0.35rem;">کۆی فرۆشی ئەم مانگە</div>
                <div class="num" style="font-size: 1.75rem; font-weight: 900; color: #d97706;">
                    {{ fmt_num($monthSales ?? 0) }}
                </div>
            </div>
        </div>
    </div>

    {{-- ٤. بەشی کردارە خێراکان --}}
    <div style="background: #ffffff; border-radius: 1.25rem; padding: 1.25rem 1.5rem; border: 1px solid #f1f5f9; box-shadow: 0 2px 10px rgba(0,0,0,0.03);">
        <div style="display: flex; align-items: center; gap: 0.5rem; font-weight: 800; font-size: 1rem; color: #1e293b; margin-bottom: 1rem;">
            <span>⚡</span>
            <span>کردارە خێراکان</span>
        </div>

        <div class="dash-actions-grid" style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
            @if (auth()->user()->can('manage_orders'))
                <a href="{{ route('orders.create') }}"
                   style="background: #2563eb; color: #ffffff; padding: 0.6rem 1.2rem; border-radius: 0.75rem; font-weight: 700; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 0.4rem; text-decoration: none; box-shadow: 0 2px 6px rgba(37, 99, 235, 0.25);">
                    <span>➕</span>
                    <span>وەسڵی نوێ</span>
                </a>
            @endif

            @if (auth()->user()->can('manage_purchases'))
                <a href="{{ route('purchases.create') }}"
                   style="background: #0284c7; color: #ffffff; padding: 0.6rem 1.2rem; border-radius: 0.75rem; font-weight: 700; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 0.4rem; text-decoration: none; box-shadow: 0 2px 6px rgba(2, 132, 199, 0.25);">
                    <span>🛒</span>
                    <span>پسوولەی کڕین</span>
                </a>
            @endif

            @if (auth()->user()->can('manage_payments'))
                <a href="{{ route('payments.create') }}"
                   style="background: #10b981; color: #ffffff; padding: 0.6rem 1.2rem; border-radius: 0.75rem; font-weight: 700; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 0.4rem; text-decoration: none; box-shadow: 0 2px 6px rgba(16, 185, 129, 0.25);">
                    <span>💵</span>
                    <span>تۆماری پارەدان</span>
                </a>
            @endif

            <a href="{{ route('debts.index') }}"
               style="background: #4f46e5; color: #ffffff; padding: 0.6rem 1.2rem; border-radius: 0.75rem; font-weight: 700; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 0.4rem; text-decoration: none; box-shadow: 0 2px 6px rgba(79, 70, 229, 0.25);">
                <span>💳</span>
                <span>قەرزەکان</span>
            </a>

            <a href="{{ route('statement.index') }}"
               style="background: #9333ea; color: #ffffff; padding: 0.6rem 1.2rem; border-radius: 0.75rem; font-weight: 700; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 0.4rem; text-decoration: none; box-shadow: 0 2px 6px rgba(147, 51, 234, 0.25);">
                <span>📑</span>
                <span>کەشف حیسابی</span>
            </a>
        </div>
    </div>

    {{-- ٥. خشتەی دوایین وەسڵەکان و دوایین پارەدانەکان --}}
    @if (isset($recentOrders) && $recentOrders->isNotEmpty())
        <div class="dash-tables-grid" style="display: grid; grid-template-columns: 3fr 2fr; gap: 1.25rem; align-items: start;">

            {{-- دوایین وەسڵەکان --}}
            <div style="background: #ffffff; border-radius: 1.25rem; border: 1px solid #f1f5f9; box-shadow: 0 2px 10px rgba(0,0,0,0.03); overflow: hidden;">
                <div style="padding: 1.1rem 1.25rem; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #f8fafc;">
                    <div style="display: flex; align-items: center; gap: 0.5rem; font-weight: 800; font-size: 0.95rem; color: #1e293b;">
                        <span>📋</span>
                        <span>دوایین وەسڵەکان</span>
                    </div>
                    <a href="{{ route('orders.index') }}" style="font-size: 0.75rem; font-weight: 700; color: #2563eb; text-decoration: none;">
                        هەمووی &larr;
                    </a>
                </div>

                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse; text-align: right; font-size: 0.85rem;">
                        <thead>
                            <tr style="border-bottom: 1px solid #f1f5f9; color: #64748b; font-size: 0.75rem; font-weight: 700;">
                                <th style="padding: 0.75rem 1rem; text-align: center;">ژمارە</th>
                                <th style="padding: 0.75rem 1rem; text-align: right;">کڕیار</th>
                                <th style="padding: 0.75rem 1rem; text-align: center;">کۆی پارە</th>
                                <th style="padding: 0.75rem 1rem; text-align: center;">دۆخ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($recentOrders as $order)
                                <tr style="border-bottom: 1px solid #f8fafc;">
                                    <td class="num" style="padding: 0.75rem 1rem; text-align: center;">
                                        <a href="{{ route('orders.print', $order) }}" style="color: #e11d48; font-weight: 700; text-decoration: none;">
                                            {{ $order->invoice_no }}
                                        </a>
                                    </td>
                                    <td style="padding: 0.75rem 1rem; font-weight: 700; color: #1e293b;">
                                        {{ $order->customer?->name ?? '—' }}
                                    </td>
                                    <td class="num" style="padding: 0.75rem 1rem; text-align: center; font-weight: 700; color: #334155;">
                                        {{ fmt_num($order->total_iqd) }}
                                    </td>
                                    <td style="padding: 0.75rem 1rem; text-align: center;">
                                        <span style="background: #f1f5f9; color: #475569; padding: 0.2rem 0.6rem; border-radius: 0.4rem; font-size: 0.72rem; font-weight: 700;">
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
                <div style="background: #ffffff; border-radius: 1.25rem; border: 1px solid #f1f5f9; box-shadow: 0 2px 10px rgba(0,0,0,0.03); overflow: hidden;">
                    <div style="padding: 1.1rem 1.25rem; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #f8fafc;">
                        <div style="display: flex; align-items: center; gap: 0.5rem; font-weight: 800; font-size: 0.95rem; color: #1e293b;">
                            <span>💵</span>
                            <span>دوایین پارەدانەکان</span>
                        </div>
                        <a href="{{ route('payments.index') }}" style="font-size: 0.75rem; font-weight: 700; color: #2563eb; text-decoration: none;">
                            هەمووی &larr;
                        </a>
                    </div>

                    <div style="overflow-x: auto;">
                        <table style="width: 100%; border-collapse: collapse; text-align: right; font-size: 0.85rem;">
                            <thead>
                                <tr style="border-bottom: 1px solid #f1f5f9; color: #64748b; font-size: 0.75rem; font-weight: 700;">
                                    <th style="padding: 0.75rem 1rem; text-align: center;">وەسڵ</th>
                                    <th style="padding: 0.75rem 1rem; text-align: center;">بڕ</th>
                                    <th style="padding: 0.75rem 1rem; text-align: center;">جۆر</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($recentPayments as $payment)
                                    <tr style="border-bottom: 1px solid #f8fafc;">
                                        <td class="num" style="padding: 0.75rem 1rem; text-align: center;">
                                            <a href="{{ route('payments.print', $payment) }}" style="color: #2563eb; font-weight: 700; text-decoration: none;">
                                                {{ $payment->voucher_no }}
                                            </a>
                                        </td>
                                        <td class="num" style="padding: 0.75rem 1rem; text-align: center; font-weight: 800; color: #10b981;">
                                            {{ fmt_num($payment->amount_iqd) }}
                                        </td>
                                        <td style="padding: 0.75rem 1rem; text-align: center;">
                                            <span style="background: {{ $payment->direction === 'in' ? '#dcfce7' : '#fee2e2' }}; color: {{ $payment->direction === 'in' ? '#16a34a' : '#dc2626' }}; padding: 0.2rem 0.6rem; border-radius: 0.4rem; font-size: 0.72rem; font-weight: 700;">
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
