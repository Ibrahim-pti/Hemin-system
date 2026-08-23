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

<div x-data="{ showDeleteModal: false }" style="display: flex; flex-direction: column; gap: 1.25rem; width: 100%;">

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

        <a href="{{ route('counts.index') }}"
           style="background: #ffffff; border: 1px solid #cbd5e1; color: #475569; padding: 0.55rem 1.25rem; border-radius: 0.65rem; font-weight: 700; font-size: 0.85rem; text-decoration: none; display: inline-flex; align-items: center; gap: 0.4rem; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
            <span>&larr;</span>
            <span>گەڕانەوە بۆ جەردەکان</span>
        </a>
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
                {{ fmt_num($grandTotal) }} <span style="font-size: 0.85rem; font-weight: 700;">دینار</span>
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
            <div style="font-size: 1.2rem; font-weight: 900; color: {{ $count->status === 'posted' ? '#059669' : '#b45309' }};">
                {{ $count->status === 'posted' ? '✓ پەسەندکراو و قفڵکراو' : '⏳ لە کاردا (دەستکاری دەکرێت)' }}
            </div>
        </div>

    </div>

    {{-- ٣. فۆڕم و خشتەی جەرد و خەمڵاندن --}}
    <form method="POST" action="{{ route('counts.update', $count) }}" id="count-form" style="width: 100%;">
        @csrf @method('PUT')

        <div style="background: #ffffff; border-radius: 1.25rem; border: 1px solid #f1f5f9; box-shadow: 0 2px 10px rgba(0,0,0,0.03); overflow: hidden; width: 100%;">

            {{-- سەردێڕی ناو کارت --}}
            <div style="padding: 1.1rem 1.5rem; background: #f8fafc; border-bottom: 1px solid #f1f5f9; display: flex; align-items: center; justify-content: space-between;">
                <div style="display: flex; align-items: center; gap: 0.5rem; font-weight: 800; font-size: 1rem; color: #1e293b;">
                    <span style="color: #2563eb;">📋</span>
                    <span>خشتەی دیاریکردنی ژمارە و نرخی خەمڵێنراوی کاڵاکان</span>
                </div>
            </div>

            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; text-align: right; font-size: 0.875rem;" id="count-table">
                    <thead>
                        <tr style="border-bottom: 1px solid #f1f5f9; background: #fafcff; color: #64748b; font-size: 0.78rem; font-weight: 700;">
                            <th style="width: 3.5rem; padding: 0.9rem 1rem; text-align: center;">#</th>
                            <th style="padding: 0.9rem 1.25rem; text-align: right;">جۆری سەروەت / کاڵا</th>
                            <th style="width: 12rem; padding: 0.9rem 1.25rem; text-align: center;">ژمارە (ژمێردراو)</th>
                            <th style="width: 14rem; padding: 0.9rem 1.25rem; text-align: center;">نرخی خەمڵاندراو (تاک)</th>
                            <th style="width: 14rem; padding: 0.9rem 1.25rem; text-align: center;">کۆی گشتی</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($count->items as $idx => $line)
                            @if (!$line->item) @continue @endif
                            @php
                                $qtyForVal = $line->counted_qty !== null ? (float) $line->counted_qty : (float) $line->system_qty;
                                $price = (float) ($line->unit_price ?? 0);
                                $total = $qtyForVal * $price;
                            @endphp
                            <tr data-sys="{{ (float) $line->system_qty }}"
                                style="border-bottom: 1px solid #f8fafc; transition: background 0.15s;"
                                onmouseover="this.style.background='#fbfcfd'"
                                onmouseout="this.style.background='transparent'">

                                {{-- # --}}
                                <td class="num" style="padding: 0.9rem 1rem; text-align: center; color: #94a3b8; font-weight: 700; font-size: 0.8rem;">
                                    {{ $idx + 1 }}
                                </td>

                                {{-- جۆری سەروەت / کاڵا --}}
                                <td style="padding: 0.9rem 1.25rem; text-align: right;">
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

                                {{-- ژمارە (ژمێردراو) --}}
                                <td style="padding: 0.9rem 1.25rem; text-align: center;">
                                    @if ($count->status === 'posted')
                                        <span class="num" style="font-weight: 800; color: #0f172a; font-size: 1.05rem;">
                                            {{ (float) $line->counted_qty }}
                                        </span>
                                    @else
                                        <input type="number" step="any" name="counted[{{ $line->id }}]"
                                               value="{{ $line->counted_qty !== null ? (float) $line->counted_qty : ((float) $line->system_qty > 0 ? (float) $line->system_qty : '') }}"
                                               class="field num counted-input"
                                               style="width: 7.5rem; padding: 0.55rem 0.75rem; text-align: center; font-weight: 800; font-size: 1.05rem; display: inline-block;"
                                               placeholder="{{ (float) $line->system_qty }}">
                                    @endif
                                </td>

                                {{-- نرخی خەمڵاندراو (تاک) --}}
                                <td style="padding: 0.9rem 1.25rem; text-align: center;">
                                    @if ($count->status === 'posted')
                                        <span class="num" style="font-weight: 700; color: #334155; font-size: 0.95rem;">
                                            {{ fmt_money($line->unit_price ?? 0) }}
                                        </span>
                                    @else
                                        <div style="display: inline-flex; align-items: center; gap: 0.35rem; position: relative;">
                                            <input type="text" inputmode="numeric" name="unit_price[{{ $line->id }}]"
                                                   value="{{ (float) ($line->unit_price ?? 0) > 0 ? number_format((float) ($line->unit_price ?? 0), 0, '.', ',') : '' }}"
                                                   class="field num price-input"
                                                   style="width: 9rem; padding: 0.55rem 1.75rem 0.55rem 0.75rem; text-align: center; font-weight: 700; font-size: 1rem; display: inline-block;"
                                                   placeholder="0">
                                            <span style="position: absolute; left: 0.5rem; font-size: 0.7rem; font-weight: 700; color: #94a3b8; pointer-events: none;">
                                                د.ع
                                            </span>
                                        </div>
                                    @endif
                                </td>

                                {{-- کۆی گشتی نرخ --}}
                                <td class="num row-total" data-value="{{ $total }}"
                                    style="padding: 0.9rem 1.25rem; text-align: center; font-weight: 900; color: #0f172a; font-size: 1.05rem;">
                                    {{ fmt_money($total) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>

                    {{-- کۆی گشتی ژێر خشتە --}}
                    <tfoot>
                        <tr style="border-top: 2px solid #e2e8f0; background: #fafafa; font-weight: 900;">
                            <td colspan="4" style="padding: 1rem 1.5rem; text-align: right; color: #1e293b; font-size: 1rem;">
                                کۆی گشتی سەروەت:
                            </td>
                            <td class="num" id="grand-total-display"
                                style="padding: 1rem 1.5rem; text-align: center; color: #15803d; font-size: 1.3rem;">
                                {{ fmt_money($grandTotal) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {{-- دوگمەکانی خوارەوە --}}
            <div style="background: #f8fafc; padding: 1.25rem 2rem; border-top: 1px solid #f1f5f9; display: flex; align-items: center; justify-content: space-between;">
                @if ($count->status !== 'posted')
                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                        <button type="submit"
                                style="background: #2563eb; color: #ffffff; padding: 0.65rem 2rem; border-radius: 0.65rem; font-weight: 800; font-size: 0.9rem; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 0.4rem; box-shadow: 0 2px 6px rgba(37, 99, 235, 0.25);">
                            <span>✓</span>
                            <span>پاشەکەوتکردن</span>
                        </button>
                        <a href="{{ route('counts.index') }}"
                           style="padding: 0.65rem 1.5rem; border-radius: 0.65rem; background: #ffffff; border: 1px solid #cbd5e1; color: #64748b; font-weight: 700; font-size: 0.9rem; text-decoration: none;">
                            گەڕانەوە
                        </a>
                    </div>

                    <button type="button" @click="showDeleteModal = true"
                            style="background: #fff1f2; color: #e11d48; border: 1px solid #fecdd3; padding: 0.65rem 1.25rem; border-radius: 0.65rem; font-weight: 700; font-size: 0.85rem; cursor: pointer;">
                        سڕینەوە
                    </button>
                @else
                    <a href="{{ route('counts.index') }}"
                       style="padding: 0.65rem 1.5rem; border-radius: 0.65rem; background: #ffffff; border: 1px solid #cbd5e1; color: #64748b; font-weight: 700; font-size: 0.9rem; text-decoration: none;">
                        &larr; گەڕانەوە بۆ لیست
                    </a>
                @endif
            </div>

        </div>
    </form>

    {{-- ── ٤. مۆداڵی سڕینەوە لە ناوەڕاست ── --}}
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

                    if (!countInput || !priceInput) return;

                    const cntRaw = countInput.value.trim().replace(/,/g, '');
                    const priceRaw = priceInput.value.trim().replace(/,/g, '');
                    
                    const priceVal = parseFloat(priceRaw) || 0;
                    let qty = cntRaw === '' ? sys : (parseFloat(cntRaw) || 0);
                    
                    let rowTotal = qty * priceVal;
                    grandTotal += rowTotal;

                    totalCell.textContent = formatMoney(rowTotal);
                });

                const grandTotalDisplay = document.getElementById('grand-total-display');
                if (grandTotalDisplay) {
                    grandTotalDisplay.textContent = formatMoney(grandTotal);
                }

                const topGrandTotal = document.getElementById('top-grand-total');
                if (topGrandTotal) {
                    topGrandTotal.innerHTML = grandTotal.toLocaleString('en-US') + ' <span style="font-size: 0.85rem; font-weight: 700;">دینار</span>';
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
    @endif

</div>
@endsection
