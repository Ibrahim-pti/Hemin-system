@extends('layouts.app')
@section('title', $customer ? 'کەشف حیسابی — ' . $customer->name : 'کەشف حیسابی')

@section('content')
<style>
@media print {
    .no-print {
        display: none !important;
    }
    body {
        background: #ffffff !important;
        padding: 0 !important;
        margin: 0 !important;
    }
}
</style>
<div style="display: flex; flex-direction: column; gap: 1.25rem;">

    {{-- ١. سەردێڕی سەرەوە: کەشف حیسابی --}}
    <div class="no-print" style="display: flex; align-items: center; gap: 0.75rem;">
        <div style="width: 2.5rem; height: 2.5rem; border-radius: 0.75rem; background: #e0e7ff; color: #4f46e5; display: flex; align-items: center; justify-content: center;">
            <svg style="width: 1.35rem; height: 1.35rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="4" y="2" width="16" height="20" rx="2"/>
                <line x1="8" y1="6" x2="16" y2="6"/>
                <line x1="16" y1="14" x2="16" y2="18"/>
                <path d="M16 10h.01M12 10h.01M8 10h.01M12 14h.01M8 14h.01M12 18h.01M8 18h.01"/>
            </svg>
        </div>
        <h1 style="font-size: 1.5rem; font-weight: 800; color: #1e293b; margin: 0;">کەشف حیسابی</h1>
    </div>

    {{-- ٢. کارتی فلتەری سەرەوە (کڕیار، لە بەرواری، بۆ بەرواری) --}}
    <div class="no-print" style="background: #ffffff; border-radius: 1rem; padding: 1.25rem 1.5rem; border: 1px solid #f1f5f9; box-shadow: 0 2px 8px rgba(0,0,0,0.03);">
        <form method="GET" action="{{ $customer ? route('customers.statement', $customer) : route('statement.index') }}" style="display: flex; flex-direction: column; gap: 1rem;">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 items-end">

                {{-- کڕیار هەڵبژێرە --}}
                <div>
                    <label style="font-size: 0.8rem; font-weight: 700; color: #475569; display: flex; align-items: center; gap: 0.35rem; margin-bottom: 0.35rem;">
                        <span>👤</span>
                        <span>کڕیار هەڵبژێرە</span>
                    </label>
                    <select name="customer_id"
                            onchange="const f = document.querySelector('input[name=from]')?.value || ''; const t = document.querySelector('input[name=to]')?.value || ''; if(this.value) { window.location.href='/customers/' + this.value + '/statement?from=' + f + '&to=' + t; } else { window.location.href='{{ route('statement.index') }}'; }"
                            class="field"
                            style="width: 100%; font-weight: 700; color: #1e293b;">
                        <option value="">-- کڕیار هەڵبژێرە --</option>
                        @foreach ($allCustomers as $c)
                            <option value="{{ $c->id }}" {{ $customer && $c->id == $customer->id ? 'selected' : '' }}>
                                {{ $c->name }} {{ $c->phone ? ' - ' . $c->phone : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- لە مانگی / بەرواری --}}
                <div>
                    <label style="font-size: 0.8rem; font-weight: 700; color: #475569; display: flex; align-items: center; gap: 0.35rem; margin-bottom: 0.35rem;">
                        <span>📅</span>
                        <span>لە مانگی / بەرواری</span>
                    </label>
                    <input type="date" name="from" value="{{ $from ? $from->toDateString() : '' }}" class="field num" style="width: 100%;">
                </div>

                {{-- بۆ مانگی / بەرواری --}}
                <div>
                    <label style="font-size: 0.8rem; font-weight: 700; color: #475569; display: flex; align-items: center; gap: 0.35rem; margin-bottom: 0.35rem;">
                        <span>📅</span>
                        <span>بۆ مانگی / بەرواری</span>
                    </label>
                    <input type="date" name="to" value="{{ $to ? $to->toDateString() : '' }}" class="field num" style="width: 100%;">
                </div>

                {{-- دوگمەی نیشاندان --}}
                <div>
                    <button type="submit"
                            style="background: #2563eb; color: #ffffff; padding: 0.6rem 1.5rem; border-radius: 0.65rem; font-weight: 700; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 0.4rem; border: none; cursor: pointer; box-shadow: 0 2px 6px rgba(37, 99, 235, 0.25);">
                        <svg style="width: 1rem; height: 1rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8"/>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                        </svg>
                        <span>نیشاندان</span>
                    </button>
                </div>

            </div>

            {{-- تابی فلتەری چالاک ئەگەر کڕیار دیاریکرابێت --}}
            @if ($customer && (request('from') || request('to')))
                <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap; margin-top: 0.25rem;">
                    <span class="num" style="background: #0284c7; color: #ffffff; padding: 0.3rem 0.85rem; border-radius: 0.5rem; font-size: 0.78rem; font-weight: 700; display: inline-flex; align-items: center; gap: 0.4rem;">
                        <svg style="width: 0.85rem; height: 0.85rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/>
                        </svg>
                        <span>فلتەر: {{ fmt_date($from) }} بۆ {{ fmt_date($to) }}</span>
                    </span>

                    <a href="{{ route('customers.statement', $customer) }}"
                       style="background: #ffffff; border: 1px solid #cbd5e1; color: #64748b; padding: 0.25rem 0.75rem; border-radius: 0.5rem; font-size: 0.75rem; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 0.3rem;">
                        <span>✕</span>
                        <span>لابردنی فلتەر</span>
                    </a>
                </div>
            @endif
        </form>
    </div>

    @if (! $customer)
        {{-- ════════════════════════════════════════════════════════════════ --}}
        {{-- دۆخی سەرەتا (Empty State): تکایە کڕیارێک هەڵبژێرە وەک وێنەکە --}}
        {{-- ════════════════════════════════════════════════════════════════ --}}
        <div style="background: #ffffff; border-radius: 1.25rem; border: 1px solid #f1f5f9; box-shadow: 0 2px 10px rgba(0,0,0,0.03); padding: 5rem 2rem; text-align: center; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 0.75rem;">
            {{-- ئایکۆنی ناسینەوەی کڕیار / سکان --}}
            <div style="width: 5.5rem; height: 5.5rem; border-radius: 1.25rem; background: #f8fafc; color: #94a3b8; display: flex; align-items: center; justify-content: center; margin-bottom: 0.5rem;">
                <svg style="width: 3.5rem; height: 3.5rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 7V5a2 2 0 0 1 2-2h2M17 3h2a2 2 0 0 1 2 2v2M21 17v2a2 2 0 0 1-2 2h-2M7 21H5a2 2 0 0 1-2-2v-2"/>
                    <circle cx="12" cy="10" r="3"/>
                    <path d="M7 18a5 5 0 0 1 10 0"/>
                </svg>
            </div>

            <h2 style="font-size: 1.35rem; font-weight: 800; color: #1e293b; margin: 0;">
                تکایە کڕیارێک هەڵبژێرە
            </h2>
            <p style="font-size: 0.875rem; color: #64748b; font-weight: 600; margin: 0;">
                بۆ بینینی کەشف حیسابی، کڕیارێک لە لیستەکەی سەرەوە هەڵبژێرە
            </p>
        </div>

    @else
        {{-- ════════════════════════════════════════════════════════════════ --}}
        {{-- دۆخی هەڵبژاردنی کڕیار: پیشاندانی تەواوی کەشف حسابەکە           --}}
        {{-- ════════════════════════════════════════════════════════════════ --}}

        {{-- ٣. کارتی گەورەی کەشف حیسابی و ٤ کارتی ئامار --}}
        <div style="background: #ffffff; border-radius: 1.25rem; border: 1px solid #f1f5f9; box-shadow: 0 2px 10px rgba(0,0,0,0.03); overflow: hidden; padding: 1.5rem;">

            {{-- دوگمەی داگرتنی PDF لە سەرەوەی کارت --}}
            <div class="no-print" style="display: flex; justify-content: flex-start; margin-bottom: 1rem;">
                <button type="button" onclick="window.print()"
                        style="background: #e11d48; color: #ffffff; padding: 0.55rem 1.25rem; border-radius: 0.6rem; font-weight: 700; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 0.4rem; border: none; cursor: pointer; box-shadow: 0 2px 6px rgba(225, 29, 72, 0.25);">
                    <svg style="width: 1.05rem; height: 1.05rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/>
                    </svg>
                    <span>داگرتنی PDF</span>
                </button>
            </div>

            {{-- سەردێڕی ناوی کڕیار و بەروار --}}
            <div style="background: #f8fafc; border-radius: 0.85rem; padding: 0.85rem 1.25rem; display: flex; align-items: center; justify-content: space-between; border: 1px solid #e2e8f0; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 0.75rem;">
                {{-- ڕاست --}}
                <div style="display: flex; align-items: center; gap: 0.5rem; font-size: 1.15rem; font-weight: 800; color: #1e293b;">
                    <svg style="width: 1.25rem; height: 1.25rem; color: #0284c7;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/>
                    </svg>
                    <span>کەشف حیسابی: {{ $customer->name }}</span>
                </div>

                {{-- چەپ --}}
                <div style="display: flex; align-items: center; gap: 1rem; font-size: 0.85rem; color: #64748b; font-weight: 600;">
                    <span class="num" dir="ltr">{{ $customer->phone ? $customer->phone . ' 📞' : '' }}</span>
                    <span class="num" dir="ltr">📅 {{ fmt_date($from) }} بۆ {{ fmt_date($to) }}</span>
                </div>
            </div>

            {{-- ٤ کارتی ئاماری سەرەکی کەشف حساب --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">

                {{-- ١. قەرزی پێشوو / باڵانسی سەرەتایی (Purple) --}}
                <div style="background: #fdf4ff; border: 1.5px solid #f0abfc; border-radius: 1rem; padding: 1.25rem 1rem; text-align: center; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 0.35rem;">
                    <div style="color: #a855f7; margin-bottom: 0.15rem;">
                        <svg style="width: 1.75rem; height: 1.75rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/>
                            <polyline points="12 6 12 12 16 14"/>
                        </svg>
                    </div>
                    <div style="font-size: 0.82rem; font-weight: 700; color: #86198f;">قەرزی پێشوو (سەرەتایی)</div>
                    <div class="num" style="font-size: 1.45rem; font-weight: 900; color: #9333ea; line-height: 1.2;">
                        {{ fmt_num($openingBalance) }} <span style="font-size: 0.85rem; font-weight: 700;">د.ع</span>
                    </div>
                </div>

                {{-- ٢. فرۆشتنەکان لەم ماوەیەدا (Green) --}}
                <div style="background: #f0fdf4; border: 1.5px solid #86efac; border-radius: 1rem; padding: 1.25rem 1rem; text-align: center; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 0.35rem;">
                    <div style="color: #16a34a; margin-bottom: 0.15rem;">
                        <svg style="width: 1.75rem; height: 1.75rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="9" cy="21" r="1"/>
                            <circle cx="20" cy="21" r="1"/>
                            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
                        </svg>
                    </div>
                    <div style="font-size: 0.82rem; font-weight: 700; color: #166534;">فرۆشتنەکان (لەم ماوەیەدا)</div>
                    <div class="num" style="font-size: 1.45rem; font-weight: 900; color: #15803d; line-height: 1.2;">
                        {{ fmt_num($totalOrdersAmount ?? ($totalPurchases - $openingBalance)) }} <span style="font-size: 0.85rem; font-weight: 700;">د.ع</span>
                    </div>
                </div>

                {{-- ٣. پارەی دراو / حەقدی (Blue) --}}
                <div style="background: #f0f9ff; border: 1.5px solid #7dd3fc; border-radius: 1rem; padding: 1.25rem 1rem; text-align: center; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 0.35rem;">
                    <div style="color: #0284c7; margin-bottom: 0.15rem;">
                        <svg style="width: 1.75rem; height: 1.75rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="2" y="6" width="20" height="12" rx="2"/>
                            <circle cx="12" cy="12" r="2"/>
                            <path d="M6 12h.01M18 12h.01"/>
                        </svg>
                    </div>
                    <div style="font-size: 0.82rem; font-weight: 700; color: #075985;">پارەی دراو (حەقدی)</div>
                    <div class="num" style="font-size: 1.45rem; font-weight: 900; color: #0369a1; line-height: 1.2;">
                        {{ fmt_num($totalPaid) }} <span style="font-size: 0.85rem; font-weight: 700;">د.ع</span>
                    </div>
                </div>

                {{-- ٤. قەرزی ماوە (Red or Green) --}}
                <div style="background: {{ $remainingDebt > 0 ? '#fff1f2' : '#f0fdf4' }}; border: 1.5px solid {{ $remainingDebt > 0 ? '#fecdd3' : '#a7f3d0' }}; border-radius: 1rem; padding: 1.25rem 1rem; text-align: center; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 0.35rem;">
                    <div style="color: {{ $remainingDebt > 0 ? '#e11d48' : '#10b981' }}; margin-bottom: 0.15rem;">
                        <svg style="width: 1.75rem; height: 1.75rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="1" y="4" width="22" height="16" rx="2"/>
                            <line x1="1" y1="10" x2="23" y2="10"/>
                        </svg>
                    </div>
                    <div style="font-size: 0.82rem; font-weight: 700; color: {{ $remainingDebt > 0 ? '#9f1239' : '#166534' }};">قەرزی ماوەی کۆتایی</div>
                    <div class="num" style="font-size: 1.45rem; font-weight: 900; color: {{ $remainingDebt > 0 ? '#dc2626' : '#15803d' }}; line-height: 1.2;">
                        {{ fmt_num($remainingDebt) }} <span style="font-size: 0.85rem; font-weight: 700;">د.ع</span>
                    </div>
                </div>

            </div>

        </div>

        {{-- ٤. دوو خشتەی تەنیشت یەک (فرۆشتنەکان لە دەستە ڕاست، پارەدانی قەرزەکان لە دەستە چەپ) --}}
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-4 items-start">

            {{-- خشتەی دەستە ڕاست: فرۆشتنەکان --}}
            <div class="lg:col-span-3 rounded-2xl border border-slate-200 bg-white overflow-hidden shadow-xs">
                <div style="padding: 1.1rem 1.25rem; display: flex; align-items: center; gap: 0.5rem; font-weight: 800; font-size: 1rem; color: #1e293b; border-bottom: 1px solid #f8fafc;">
                    <span style="color: #10b981;">🛒</span>
                    <span>فرۆشتنەکان</span>
                </div>

                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse; text-align: right; font-size: 0.875rem;">
                        <thead>
                            <tr style="border-bottom: 1px solid #f1f5f9; color: #64748b; font-size: 0.75rem; font-weight: 700;">
                                <th style="padding: 0.75rem 0.85rem; text-align: center;">بەروار</th>
                                <th style="padding: 0.75rem 0.85rem; text-align: right;">جۆر / ناوەڕۆک</th>
                                <th style="padding: 0.75rem 0.85rem; text-align: center;">بڕ</th>
                                <th style="padding: 0.75rem 0.85rem; text-align: center;">نرخ</th>
                                <th style="padding: 0.75rem 0.85rem; text-align: center;">دراو</th>
                                <th style="padding: 0.75rem 0.85rem; text-align: center;">دۆخ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($orders as $order)
                                @php
                                    $paid = $order->paidAmount();
                                    $remaining = $order->remaining();
                                    $itemDesc = $order->items->pluck('description')->join(', ') ?: 'وەسڵی ' . $order->invoice_no;
                                    $itemCount = $order->items->count();
                                @endphp
                                <tr style="border-bottom: 1px solid #f8fafc; transition: background 0.15s;"
                                    onmouseover="this.style.background='#fbfcfd'"
                                    onmouseout="this.style.background='transparent'">

                                    {{-- بەروار --}}
                                    <td class="num" style="padding: 0.85rem 0.85rem; text-align: center; color: #475569; font-size: 0.8rem;">
                                        {{ fmt_date($order->order_date) }}
                                    </td>

                                    {{-- جۆر / ناوەڕۆک --}}
                                    <td style="padding: 0.85rem 0.85rem; text-align: right;">
                                        <a href="{{ route('orders.print', $order) }}"
                                           style="color: #1e293b; font-weight: 700; text-decoration: none; font-size: 0.85rem;">
                                            {{ $itemDesc }}
                                        </a>
                                    </td>

                                    {{-- بڕ --}}
                                    <td class="num" style="padding: 0.85rem 0.85rem; text-align: center; font-weight: 600; color: #64748b; font-size: 0.85rem;">
                                        {{ $itemCount }}
                                    </td>

                                    {{-- نرخ (کۆی وەسڵ) --}}
                                    <td class="num" style="padding: 0.85rem 0.85rem; text-align: center; font-weight: 700; color: #334155; font-size: 0.85rem;">
                                        {{ fmt_num($order->total_iqd) }}
                                    </td>

                                    {{-- دراو --}}
                                    <td class="num" style="padding: 0.85rem 0.85rem; text-align: center; font-weight: 800; color: #10b981; font-size: 0.85rem;">
                                        {{ fmt_num($paid) }}
                                    </td>

                                    {{-- دۆخ --}}
                                    <td style="padding: 0.85rem 0.85rem; text-align: center;">
                                        @if ($remaining <= 0.5)
                                            <span style="background: #dcfce7; color: #16a34a; padding: 0.2rem 0.65rem; border-radius: 0.4rem; font-weight: 700; font-size: 0.72rem; display: inline-block;">
                                                پاردراو
                                            </span>
                                        @else
                                            <span style="background: #fee2e2; color: #dc2626; padding: 0.2rem 0.65rem; border-radius: 0.4rem; font-weight: 700; font-size: 0.72rem; display: inline-block;">
                                                قەرز
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" style="padding: 2.5rem 1rem; text-align: center; color: #94a3b8; font-size: 0.85rem;">
                                        هیچ فرۆشتنێک لەم ماوەیەدا نییە.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- خشتەی دەستە چەپ: پارەدانی قەرزەکان --}}
            <div class="lg:col-span-2 rounded-2xl border border-slate-200 bg-white overflow-hidden shadow-xs">
                <div style="padding: 1.1rem 1.25rem; display: flex; align-items: center; gap: 0.5rem; font-weight: 800; font-size: 1rem; color: #1e293b; border-bottom: 1px solid #f8fafc;">
                    <span style="color: #ca8a04;">📁</span>
                    <span>پارەدانی قەرزەکان</span>
                </div>

                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse; text-align: right; font-size: 0.875rem;">
                        <thead>
                            <tr style="border-bottom: 1px solid #f1f5f9; color: #64748b; font-size: 0.75rem; font-weight: 700;">
                                <th style="padding: 0.75rem 0.85rem; text-align: center;">بەروار</th>
                                <th style="padding: 0.75rem 0.85rem; text-align: center;">بڕی پارەدان</th>
                                <th style="padding: 0.75rem 0.85rem; text-align: center;">وەسڵی پەیوەندیدار</th>
                                <th style="padding: 0.75rem 0.85rem; text-align: center;">تێبینی</th>
                                <th style="padding: 0.75rem 0.85rem; width: 3rem; text-align: center;">وەسڵ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($payments as $payment)
                                <tr style="border-bottom: 1px solid #f8fafc; transition: background 0.15s;"
                                    onmouseover="this.style.background='#fbfcfd'"
                                    onmouseout="this.style.background='transparent'">

                                    {{-- بەروار --}}
                                    <td class="num" style="padding: 0.85rem 0.85rem; text-align: center; color: #475569; font-size: 0.8rem;">
                                        {{ fmt_date($payment->paid_at) }}
                                    </td>

                                    {{-- بڕی پارەدان --}}
                                    <td class="num" style="padding: 0.85rem 0.85rem; text-align: center; font-weight: 800; color: #10b981; font-size: 0.85rem;">
                                        {{ fmt_num($payment->amount_iqd) }}
                                    </td>

                                    {{-- وەسڵی پەیوەندیدار --}}
                                    <td style="padding: 0.85rem 0.85rem; text-align: center; font-weight: 700; color: #2563eb; font-size: 0.85rem;">
                                        @if ($payment->order)
                                            <a href="{{ route('orders.print', $payment->order) }}" style="color: #2563eb; text-decoration: none;">
                                                وەسڵی #{{ $payment->order->invoice_no }}
                                            </a>
                                        @else
                                            <span style="color: #64748b; font-weight: 500;">حسابی گشتی</span>
                                        @endif
                                    </td>

                                    {{-- تێبینی --}}
                                    <td style="padding: 0.85rem 0.85rem; text-align: center; color: #64748b; font-size: 0.8rem;">
                                        {{ $payment->note ?: '-' }}
                                    </td>

                                    {{-- وەسڵ (ئایکۆنی چاپی حەقدی) --}}
                                    <td style="padding: 0.85rem 0.85rem; text-align: center;">
                                        <a href="{{ route('payments.print', $payment) }}"
                                           style="width: 1.85rem; height: 1.85rem; border-radius: 0.45rem; background: #eff6ff; color: #2563eb; display: inline-flex; align-items: center; justify-content: center; text-decoration: none; border: 1px solid #bfdbfe;"
                                           title="چاپی حەقدی">
                                            <svg style="width: 1rem; height: 1rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                                <polyline points="14 2 14 8 20 8"/>
                                            </svg>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" style="padding: 2.5rem 1rem; text-align: center; color: #94a3b8; font-size: 0.85rem;">
                                        هیچ پارەدانێک تۆمارنەکراوە.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>

                        {{-- کۆی پارەدانەکان لە خوارەوە --}}
                        <tfoot>
                            <tr style="border-top: 2px solid #f1f5f9; background: #fafafa; font-weight: 800;">
                                <td style="padding: 0.85rem 1rem; text-align: center; color: #1e293b; font-size: 0.9rem;">
                                    کۆ
                                </td>
                                <td class="num" style="padding: 0.85rem 1rem; text-align: center; color: #10b981; font-size: 0.95rem;">
                                    {{ fmt_num($debtPayments) }}
                                </td>
                                <td colspan="3"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

        </div>
    @endif

</div>
@endsection
