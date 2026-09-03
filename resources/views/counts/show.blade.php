@extends('layouts.app')
@section('title', 'جەرد و خەمڵاندنی سەروەت')

@section('content')
@php
    $grandTotal = 0;
    foreach ($count->items as $line) {
        if (!$line->item) continue;
        $qtyForVal = $line->counted_qty !== null ? (float) $line->counted_qty : (float) $line->system_qty;
        $price = (float) ($line->unit_price ?? 0);
        $grandTotal += ($qtyForVal * $price);
    }
@endphp

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
    .card-sheet {
        border: none !important;
        box-shadow: none !important;
        padding: 0 !important;
    }
}
</style>

<div x-data="{ showDeleteModal: false, showPostModal: false }" style="display: flex; flex-direction: column; gap: 1.25rem; width: 100%;">

    {{-- ١. سەردێڕی سەرەوە --}}
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
        <div style="display: flex; align-items: center; gap: 0.75rem;">
            <div style="width: 2.5rem; height: 2.5rem; border-radius: 0.75rem; background: #e0f2fe; color: #0284c7; display: flex; align-items: center; justify-content: center;">
                <svg style="width: 1.35rem; height: 1.35rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/>
                    <rect x="9" y="3" width="6" height="4" rx="2"/>
                    <path d="M9 14l2 2 4-4"/>
                </svg>
            </div>
            <div>
                <h1 style="font-size: 1.5rem; font-weight: 800; color: #1e293b; margin: 0;">
                    جەرد و خەمڵاندنی سەروەت
                </h1>
                <p style="font-size: 0.8rem; color: #64748b; font-weight: 600; margin: 0.15rem 0 0 0;">
                    {{ $count->warehouse?->name ?? 'کۆگای سەرەکی' }} &nbsp;•&nbsp; بەروار: <span class="num">{{ fmt_date($count->count_date) }}</span>
                    @if($count->note) &nbsp;•&nbsp; {{ $count->note }} @endif
                </p>
            </div>
        </div>

        <div class="no-print" style="display: flex; align-items: center; gap: 0.5rem;">
            <button type="button" onclick="window.print()"
                    style="background: #ffffff; border: 1px solid #cbd5e1; color: #334155; padding: 0.55rem 1.1rem; border-radius: 0.65rem; font-weight: 700; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 0.4rem; cursor: pointer;">
                <span>🖨️</span>
                <span>چاپکردن</span>
            </button>
            <a href="{{ route('counts.index') }}"
               style="background: #ffffff; border: 1px solid #cbd5e1; color: #475569; padding: 0.55rem 1.1rem; border-radius: 0.65rem; font-weight: 700; font-size: 0.85rem; text-decoration: none; display: inline-flex; align-items: center; gap: 0.4rem;">
                <span>&larr;</span>
                <span>گەڕانەوە بۆ جەردەکان</span>
            </a>
        </div>
    </div>

    {{-- ٢. ٣ کارتی ئاماری سەرەوە --}}
    <div style="display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 1rem;">

        {{-- ١. کۆی گشتی سەروەت --}}
        <div style="background: #f0fdf4; border: 1.5px solid #86efac; border-radius: 1rem; padding: 1.15rem 1.25rem; text-align: center; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 0.35rem;">
            <div style="color: #16a34a; margin-bottom: 0.15rem;">
                <svg style="width: 1.6rem; height: 1.6rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="1" x2="12" y2="23"/>
                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                </svg>
            </div>
            <div style="font-size: 0.8rem; font-weight: 700; color: #166534;">کۆی گشتی بەهای سەروەت</div>
            <div class="num" id="top-grand-total" style="font-size: 1.55rem; font-weight: 900; color: #15803d; line-height: 1.2;">
                {{ fmt_num($grandTotal) }} <span style="font-size: 0.85rem; font-weight: 700;">د.ع</span>
            </div>
        </div>

        {{-- ٢. جۆری کاڵاکان --}}
        <div style="background: #f0f9ff; border: 1.5px solid #7dd3fc; border-radius: 1rem; padding: 1.15rem 1.25rem; text-align: center; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 0.35rem;">
            <div style="color: #0284c7; margin-bottom: 0.15rem;">
                <svg style="width: 1.6rem; height: 1.6rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                    <polyline points="3.27 6.96 12 12.01 20.73 6.96"/>
                    <line x1="12" y1="22.08" x2="12" y2="12"/>
                </svg>
            </div>
            <div style="font-size: 0.8rem; font-weight: 700; color: #075985;">ژمارەی جۆری کاڵاکان</div>
            <div class="num" style="font-size: 1.55rem; font-weight: 900; color: #0369a1; line-height: 1.2;">
                {{ fmt_num($count->items->count()) }} <span style="font-size: 0.85rem; font-weight: 700;">کاڵا</span>
            </div>
        </div>

        {{-- ٣. دۆخی جەرد --}}
        <div style="background: {{ $count->status === 'posted' ? '#f0fdf4' : '#fefce8' }}; border: 1.5px solid {{ $count->status === 'posted' ? '#a7f3d0' : '#fde047' }}; border-radius: 1rem; padding: 1.15rem 1.25rem; text-align: center; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 0.35rem;">
            <div style="color: {{ $count->status === 'posted' ? '#10b981' : '#ca8a04' }}; margin-bottom: 0.15rem;">
                <svg style="width: 1.6rem; height: 1.6rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <polyline points="12 6 12 12 14 14"/>
                </svg>
            </div>
            <div style="font-size: 0.8rem; font-weight: 700; color: {{ $count->status === 'posted' ? '#065f46' : '#854d0e' }};">دۆخی تۆمارکردن</div>
            <div style="font-size: 1.15rem; font-weight: 900; color: {{ $count->status === 'posted' ? '#059669' : '#b45309' }};">
                {{ $count->status === 'posted' ? '✓ پەسەندکراو و قفڵکراو' : '⏳ لە کاردا (دەستکاری دەکرێت)' }}
            </div>
        </div>

    </div>

    {{-- ٣. فۆڕم و خشتەی جەرد و خەمڵاندن --}}
    <form method="POST" action="{{ route('counts.update', $count) }}" id="count-form" style="width: 100%;">
        @csrf @method('PUT')

        <div class="card-sheet" style="background: #ffffff; border-radius: 1.25rem; border: 1px solid #f1f5f9; box-shadow: 0 2px 10px rgba(0,0,0,0.03); overflow: hidden; width: 100%;">

            {{-- سەردێڕی ناو کارت --}}
            <div style="padding: 1.1rem 1.5rem; background: #f8fafc; border-bottom: 1px solid #f1f5f9; display: flex; align-items: center; justify-content: space-between;">
                <div style="display: flex; align-items: center; gap: 0.5rem; font-weight: 800; font-size: 1rem; color: #1e293b;">
                    <span style="color: #2563eb;">📋</span>
                    <span>خشتەی بەراوردکردنی ژمارە و نرخی کاڵاکان</span>
                </div>
            </div>

            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; text-align: right; font-size: 0.875rem;" id="count-table">
                    <thead>
                        <tr style="border-bottom: 1px solid #f1f5f9; background: #fafcff; color: #64748b; font-size: 0.78rem; font-weight: 700;">
                            <th style="width: 3rem; padding: 0.9rem 0.75rem; text-align: center;">#</th>
                            <th style="padding: 0.9rem 1rem; text-align: right;">کاڵا / ماددە</th>
                            <th style="width: 7.5rem; padding: 0.9rem 0.75rem; text-align: center;">ژمارەی سیستەم</th>
                            <th style="width: 9rem; padding: 0.9rem 0.75rem; text-align: center;">ژمارە (ژمێردراو)</th>
                            <th style="width: 7.5rem; padding: 0.9rem 0.75rem; text-align: center;">جیاوازی</th>
                            <th style="width: 11rem; padding: 0.9rem 0.75rem; text-align: center;">نرخی تاک</th>
                            <th style="width: 11rem; padding: 0.9rem 1rem; text-align: center;">کۆی گشتی بەها</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($count->items as $idx => $line)
                            @if (!$line->item) @continue @endif
                            @php
                                $sysQty = (float) $line->system_qty;
                                $countedQty = $line->counted_qty !== null ? (float) $line->counted_qty : $sysQty;
                                $diff = $countedQty - $sysQty;
                                $price = (float) ($line->unit_price ?? 0);
                                $total = $countedQty * $price;
                            @endphp
                            <tr data-sys="{{ $sysQty }}"
                                style="border-bottom: 1px solid #f8fafc; transition: background 0.15s;"
                                onmouseover="this.style.background='#fbfcfd'"
                                onmouseout="this.style.background='transparent'">

                                {{-- # --}}
                                <td class="num" style="padding: 0.9rem 0.75rem; text-align: center; color: #94a3b8; font-weight: 700; font-size: 0.8rem;">
                                    {{ $idx + 1 }}
                                </td>

                                {{-- کاڵا / ماددە --}}
                                <td style="padding: 0.9rem 1rem; text-align: right;">
                                    <div style="display: flex; align-items: center; gap: 0.65rem;">
                                        <div style="width: 2.25rem; height: 2.25rem; border-radius: 0.5rem; background: #eff6ff; color: #2563eb; display: flex; align-items: center; justify-content: center; flex-shrink: 0; border: 1px solid #dbeafe;">
                                            <svg style="width: 1.15rem; height: 1.15rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <div style="font-weight: 800; color: #1e293b; font-size: 0.92rem;">
                                                {{ $line->item?->name }}
                                            </div>
                                            @if($line->item?->unit)
                                                <div style="font-size: 0.72rem; color: #64748b; font-weight: 600;">
                                                    یەکە: {{ $line->item->unit->name }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                {{-- ژمارەی سیستەم --}}
                                <td class="num" style="padding: 0.9rem 0.75rem; text-align: center; font-weight: 800; color: #475569; font-size: 0.95rem;">
                                    {{ $sysQty }}
                                </td>

                                {{-- ژمارە (ژمێردراو) --}}
                                <td style="padding: 0.9rem 0.75rem; text-align: center;">
                                    @if ($count->status === 'posted')
                                        <span class="num" style="font-weight: 800; color: #0f172a; font-size: 1rem;">
                                            {{ $countedQty }}
                                        </span>
                                    @else
                                        <input type="number" step="any" name="counted[{{ $line->id }}]"
                                               value="{{ $countedQty }}"
                                               class="field num counted-input"
                                               style="width: 6.5rem; padding: 0.55rem 0.6rem; text-align: center; font-weight: 800; font-size: 1rem; display: inline-block;"
                                               placeholder="{{ $sysQty }}">
                                    @endif
                                </td>

                                {{-- جیاوازی --}}
                                <td style="padding: 0.9rem 0.75rem; text-align: center;">
                                    <span class="num diff-cell"
                                          style="font-weight: 800; font-size: 0.85rem; padding: 0.2rem 0.6rem; border-radius: 0.35rem; display: inline-block; {{ $diff > 0 ? 'background: #dcfce7; color: #15803d;' : ($diff < 0 ? 'background: #fee2e2; color: #b91c1c;' : 'background: #f1f5f9; color: #64748b;') }}">
                                        {{ $diff > 0 ? '+' . $diff : ($diff < 0 ? $diff : '0') }}
                                    </span>
                                </td>

                                {{-- نرخی خەمڵاندراو (تاک) --}}
                                <td style="padding: 0.9rem 0.75rem; text-align: center;">
                                    @if ($count->status === 'posted')
                                        <span class="num" style="font-weight: 700; color: #334155; font-size: 0.92rem;">
                                            {{ fmt_money($line->unit_price ?? 0) }}
                                        </span>
                                    @else
                                        <div style="display: inline-flex; align-items: center; gap: 0.35rem; position: relative;">
                                            <input type="text" inputmode="numeric" name="unit_price[{{ $line->id }}]"
                                                   value="{{ (float) ($line->unit_price ?? 0) > 0 ? number_format((float) ($line->unit_price ?? 0), 0, '.', ',') : '' }}"
                                                   class="field num price-input"
                                                   style="width: 8.5rem; padding: 0.55rem 1.75rem 0.55rem 0.6rem; text-align: center; font-weight: 700; font-size: 0.92rem; display: inline-block;"
                                                   placeholder="0">
                                            <span style="position: absolute; left: 0.5rem; font-size: 0.7rem; font-weight: 700; color: #94a3b8; pointer-events: none;">
                                                د.ع
                                            </span>
                                        </div>
                                    @endif
                                </td>

                                {{-- کۆی گشتی نرخ --}}
                                <td class="num row-total" data-value="{{ $total }}"
                                    style="padding: 0.9rem 1rem; text-align: center; font-weight: 900; color: #0f172a; font-size: 1rem;">
                                    {{ fmt_money($total) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>

                    {{-- کۆی گشتی ژێر خشتە --}}
                    <tfoot>
                        <tr style="border-top: 2px solid #e2e8f0; background: #fafafa; font-weight: 900;">
                            <td colspan="6" style="padding: 1rem 1.5rem; text-align: right; color: #1e293b; font-size: 1rem;">
                                کۆی گشتی سەروەت:
                            </td>
                            <td class="num" id="grand-total-display"
                                style="padding: 1rem 1rem; text-align: center; color: #15803d; font-size: 1.25rem;">
                                {{ fmt_money($grandTotal) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {{-- دوگمەکانی خوارەوە --}}
            <div class="no-print" style="background: #f8fafc; padding: 1.25rem 2rem; border-top: 1px solid #f1f5f9; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
                @if ($count->status !== 'posted')
                    <div style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
                        {{-- دوگمەی پاشەکەوتکردن --}}
                        <button type="submit"
                                style="background: #2563eb; color: #ffffff; padding: 0.65rem 1.75rem; border-radius: 0.65rem; font-weight: 800; font-size: 0.9rem; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 0.4rem; box-shadow: 0 2px 6px rgba(37, 99, 235, 0.25);">
                            <span>💾</span>
                            <span>پاشەکەوتکردنی گۆڕانکارییەکان</span>
                        </button>

                        {{-- دوگمەی پەسەندکردن --}}
                        <button type="button" @click="showPostModal = true"
                                style="background: #059669; color: #ffffff; padding: 0.65rem 1.75rem; border-radius: 0.65rem; font-weight: 800; font-size: 0.9rem; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 0.4rem; box-shadow: 0 2px 6px rgba(5, 150, 105, 0.25);">
                            <span>✓</span>
                            <span>پەسەندکردن و جێبەجێکردن لە کۆگا</span>
                        </button>

                        <a href="{{ route('counts.index') }}"
                           style="padding: 0.65rem 1.25rem; border-radius: 0.65rem; background: #ffffff; border: 1px solid #cbd5e1; color: #64748b; font-weight: 700; font-size: 0.88rem; text-decoration: none;">
                            گەڕانەوە
                        </a>
                    </div>

                    <button type="button" @click="showDeleteModal = true"
                            style="background: #fff1f2; color: #e11d48; border: 1px solid #fecdd3; padding: 0.65rem 1.25rem; border-radius: 0.65rem; font-weight: 700; font-size: 0.85rem; cursor: pointer;">
                        سڕینەوەی ئەم جەردە
                    </button>
                @else
                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                        <button type="button" onclick="window.print()"
                                style="background: #2563eb; color: #ffffff; padding: 0.65rem 1.75rem; border-radius: 0.65rem; font-weight: 800; font-size: 0.9rem; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 0.4rem; box-shadow: 0 2px 6px rgba(37, 99, 235, 0.25);">
                            <span>🖨️</span>
                            <span>چاپکردنی ڕاپۆرتی فەرمی جەرد</span>
                        </button>
                        <a href="{{ route('counts.index') }}"
                           style="padding: 0.65rem 1.5rem; border-radius: 0.65rem; background: #ffffff; border: 1px solid #cbd5e1; color: #64748b; font-weight: 700; font-size: 0.9rem; text-decoration: none;">
                            &larr; گەڕانەوە بۆ لیست
                        </a>
                    </div>
                @endif
            </div>

        </div>
    </form>

    {{-- ── ٤. مۆداڵی پەسەندکردنی جەرد ── --}}
    @if ($count->status !== 'posted')
        <template x-teleport="body">
            <div x-show="showPostModal"
                 x-cloak
                 style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; width: 100vw; height: 100vh; z-index: 99999; display: flex; align-items: center; justify-content: center; padding: 1rem; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(2px);"
                 @keydown.escape.window="showPostModal = false">

                <div style="background: #ffffff; border-radius: 1.25rem; width: 100%; max-width: 26rem; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); overflow: hidden; margin: auto; padding: 1.75rem; text-align: center; display: flex; flex-direction: column; gap: 1.1rem;"
                     @click.outside="showPostModal = false">

                    <div style="width: 3.5rem; height: 3.5rem; border-radius: 50%; background: #d1fae5; color: #059669; display: flex; align-items: center; justify-content: center; margin: 0 auto; font-size: 1.6rem; font-weight: 900;">
                        ✓
                    </div>

                    <div>
                        <h3 style="font-size: 1.15rem; font-weight: 800; color: #1e293b; margin: 0;">پەسەندکردنی جەرد و ڕێکخستنی کۆگا</h3>
                        <p style="font-size: 0.85rem; color: #64748b; margin: 0.5rem 0 0 0; line-height: 1.5;">
                            ئایا دڵنیایت لە پەسەندکردنی ئەم جەردە؟ هەموو بڕە جیاوازەکان ڕاستەوخۆ دەخرێنە سەر باڵانسی مەخزەن و ئەم جەردە قفڵ دەکرێت.
                        </p>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-top: 0.5rem;">
                        <button type="button" @click="showPostModal = false"
                                style="padding: 0.65rem 1rem; border-radius: 0.6rem; background: #f8fafc; border: 1px solid #cbd5e1; color: #64748b; font-weight: 700; font-size: 0.85rem; cursor: pointer;">
                            پاشگەزبوونەوە
                        </button>
                        <form action="{{ route('counts.post', $count) }}" method="POST" style="margin: 0;">
                            @csrf
                            <button type="submit"
                                    style="width: 100%; padding: 0.65rem 1rem; border-radius: 0.6rem; background: #059669; color: #ffffff; font-weight: 800; font-size: 0.85rem; border: none; cursor: pointer; box-shadow: 0 2px 6px rgba(5, 150, 105, 0.25);">
                                بەڵێ، پەسەندی بکە
                            </button>
                        </form>
                    </div>

                </div>
            </div>
        </template>
    @endif

    {{-- ── ٥. مۆداڵی سڕینەوە لە ناوەڕاست ── --}}
    @if ($count->status !== 'posted')
        <template x-teleport="body">
            <div x-show="showDeleteModal"
                 x-cloak
                 style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; width: 100vw; height: 100vh; z-index: 99999; display: flex; align-items: center; justify-content: center; padding: 1rem; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(2px);"
                 @keydown.escape.window="showDeleteModal = false">

                <div style="background: #ffffff; border-radius: 1.25rem; width: 100%; max-width: 24rem; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); overflow: hidden; margin: auto; padding: 1.5rem; text-align: center; display: flex; flex-direction: column; gap: 1rem;"
                     @click.outside="showDeleteModal = false">

                    <div style="width: 3.25rem; height: 3.25rem; border-radius: 50%; background: #fee2e2; color: #dc2626; display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                        <svg style="width: 1.5rem; height: 1.5rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                            <line x1="10" y1="11" x2="10" y2="17"/>
                            <line x1="14" y1="11" x2="14" y2="17"/>
                        </svg>
                    </div>

                    <div>
                        <h3 style="font-size: 1.1rem; font-weight: 800; color: #1e293b; margin: 0;">ئایا دڵنیایت لە سڕینەوە؟</h3>
                        <p style="font-size: 0.8rem; color: #64748b; margin: 0.25rem 0 0 0;">ئەم جەردە بە تەواوی دەسڕدرێتەوە.</p>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-top: 0.5rem;">
                        <button type="button" @click="showDeleteModal = false"
                                style="padding: 0.6rem 1rem; border-radius: 0.6rem; background: #f8fafc; border: 1px solid #cbd5e1; color: #64748b; font-weight: 700; font-size: 0.85rem; cursor: pointer;">
                            پاشگەزبوونەوە
                        </button>
                        <form action="{{ route('counts.destroy', $count) }}" method="POST" style="margin: 0;">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    style="width: 100%; padding: 0.6rem 1rem; border-radius: 0.6rem; background: #e11d48; color: #ffffff; font-weight: 800; font-size: 0.85rem; border: none; cursor: pointer; box-shadow: 0 2px 6px rgba(225, 29, 72, 0.25);">
                                بیسڕەوە
                            </button>
                        </form>
                    </div>

                </div>
            </div>
        </template>
    @endif

</div>

<script>
    function formatMoney(num) {
        return num.toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 2 }) + ' د.ع';
    }

    function formatPriceInput(e) {
        let clean = e.target.value.replace(/[^0-9.]/g, '');
        let parts = clean.split('.');
        if (parts.length > 2) parts = [parts[0], parts.slice(1).join('')];
        let int = parts[0] ? parseInt(parts[0], 10).toLocaleString('en-US') : '';
        let dec = parts.length > 1 ? '.' + parts[1] : '';
        e.target.value = int ? int + dec : '';
    }

    function recalculateTotals() {
        let grandTotal = 0;
        document.querySelectorAll('#count-table tbody tr').forEach(function(tr) {
            const sys = parseFloat(tr.dataset.sys) || 0;
            const countInput = tr.querySelector('.counted-input');
            const priceInput = tr.querySelector('.price-input');
            const totalCell = tr.querySelector('.row-total');
            const diffCell = tr.querySelector('.diff-cell');

            let qty = sys;
            if (countInput) {
                const cntRaw = countInput.value.trim().replace(/,/g, '');
                qty = cntRaw === '' ? sys : (parseFloat(cntRaw) || 0);
            }

            let priceVal = 0;
            if (priceInput) {
                const priceRaw = priceInput.value.trim().replace(/,/g, '');
                priceVal = parseFloat(priceRaw) || 0;
            }

            let diff = qty - sys;
            if (diffCell) {
                if (diff > 0) {
                    diffCell.textContent = '+' + diff;
                    diffCell.style.background = '#dcfce7';
                    diffCell.style.color = '#15803d';
                } else if (diff < 0) {
                    diffCell.textContent = diff;
                    diffCell.style.background = '#fee2e2';
                    diffCell.style.color = '#b91c1c';
                } else {
                    diffCell.textContent = '0';
                    diffCell.style.background = '#f1f5f9';
                    diffCell.style.color = '#64748b';
                }
            }

            let rowTotal = qty * priceVal;
            grandTotal += rowTotal;

            if (totalCell) {
                totalCell.textContent = formatMoney(rowTotal);
            }
        });

        const grandTotalDisplay = document.getElementById('grand-total-display');
        if (grandTotalDisplay) {
            grandTotalDisplay.textContent = formatMoney(grandTotal);
        }

        const topGrandTotal = document.getElementById('top-grand-total');
        if (topGrandTotal) {
            topGrandTotal.innerHTML = grandTotal.toLocaleString('en-US') + ' <span style="font-size: 0.85rem; font-weight: 700;">د.ع</span>';
        }
    }

    document.querySelectorAll('.price-input').forEach(function(input) {
        input.addEventListener('input', function(e) {
            formatPriceInput(e);
            recalculateTotals();
        });
    });

    document.querySelectorAll('.counted-input').forEach(function(input) {
        input.addEventListener('input', recalculateTotals);
    });
</script>
@endsection
