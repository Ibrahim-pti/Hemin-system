@extends('layouts.app')
@section('title', 'جەرد و خەمڵاندنی سەروەت')

@section('content')

<div x-data="{ showDeleteModal: false }">

    {{-- هێدەری سەرەوەی پەڕە --}}
    <div class="card mb-4">
        <div class="card-body flex flex-wrap items-center justify-between gap-3 text-sm py-3 px-4">
            {{-- زانیارییە سەرەکییەکان بە کورتی --}}
            <div class="flex flex-wrap items-center gap-3 sm:gap-5">
                <div class="font-bold text-slate-900 text-sm">
                    {{ $count->warehouse?->name }}
                </div>
                <div class="text-slate-500 text-xs num">
                    {{ fmt_date($count->count_date) }}
                </div>
                <div>
                    <span class="badge {{ $count->status === 'posted' ? 'badge-ok' : 'badge-warn' }} text-xs">
                        {{ $count->status_label }}
                    </span>
                </div>
                @if($count->note)
                    <div class="text-xs text-slate-500 max-w-xs truncate">
                        {{ $count->note }}
                    </div>
                @endif
            </div>

            {{-- کردارەکانی سەرەوە --}}
            <div class="flex items-center gap-2">
                <a href="{{ route('counts.index') }}" class="btn btn-ghost !py-1.5 !px-3 text-xs gap-1 text-slate-700 hover:bg-slate-100">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 12H5M12 19l-7-7 7-7"/>
                    </svg>
                    <span>گەڕانەوە</span>
                </a>
            </div>
        </div>
    </div>

    {{-- خشتەی جەرد و خەمڵاندن --}}
    <form method="POST" action="{{ route('counts.update', $count) }}" id="count-form">
        @csrf @method('PUT')

        <div class="card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="table w-full" id="count-table" style="direction: rtl;">
                    <thead>
                        <tr class="bg-slate-50/80 border-b border-[--color-line] text-slate-700 text-xs">
                            <th style="width: 48px; text-align: center; padding: 12px 10px;">#</th>
                            <th style="text-align: right; padding: 12px 16px;">جۆری سەروەت / کاڵا</th>
                            <th style="width: 160px; text-align: right; padding: 12px 16px;">ژمارە (ژمێردراو)</th>
                            <th style="width: 220px; text-align: right; padding: 12px 16px;">نرخی خەمڵاندراو (تاک)</th>
                            <th style="width: 200px; text-align: right; padding: 12px 16px;">کۆی گشتی</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @php $grandTotal = 0; @endphp
                        @foreach ($count->items as $idx => $line)
                            @if (!$line->item) @continue @endif
                            @php
                                $qtyForVal = $line->counted_qty !== null ? (float) $line->counted_qty : (float) $line->system_qty;
                                $price = (float) ($line->unit_price ?? 0);
                                $total = $qtyForVal * $price;
                                $grandTotal += $total;
                            @endphp
                            <tr data-sys="{{ (float) $line->system_qty }}" class="hover:bg-slate-50/60 transition-colors text-sm">
                                {{-- # --}}
                                <td style="text-align: center; padding: 12px 10px; color: var(--color-ink-soft); font-size: 12px;">
                                    {{ $idx + 1 }}
                                </td>
                                
                                {{-- جۆری سەروەت --}}
                                <td style="text-align: right; padding: 12px 16px; font-weight: 500; color: #1e293b;">
                                    {{ $line->item?->name }}
                                </td>

                                {{-- ژمارە --}}
                                <td style="text-align: right; padding: 12px 16px;">
                                    @if ($count->status === 'posted')
                                        <span style="font-weight: 700; color: #0f172a; display: inline-block;">{{ (float) $line->counted_qty }}</span>
                                    @else
                                        <input type="number" step="any" name="counted[{{ $line->id }}]"
                                               value="{{ $line->counted_qty !== null ? (float) $line->counted_qty : ((float) $line->system_qty > 0 ? (float) $line->system_qty : '') }}"
                                               class="field w-28 !py-1 text-right counted-input font-semibold"
                                               style="text-align: right; display: inline-block;"
                                               placeholder="{{ (float) $line->system_qty }}">
                                    @endif
                                </td>

                                {{-- نرخی خەمڵاندراو (تاک) --}}
                                <td style="text-align: right; padding: 12px 16px;">
                                    @if ($count->status === 'posted')
                                        <span style="font-weight: 600; color: #334155; display: inline-block;">{{ fmt_money($line->unit_price ?? 0) }}</span>
                                    @else
                                        <div class="flex items-center gap-1.5" style="display: flex; align-items: center;">
                                            <input type="text" inputmode="numeric" name="unit_price[{{ $line->id }}]"
                                                   value="{{ (float) ($line->unit_price ?? 0) > 0 ? number_format((float) ($line->unit_price ?? 0), 0, '.', ',') : '' }}"
                                                   class="field w-32 !py-1 text-right price-input font-medium"
                                                   style="text-align: right; display: inline-block;"
                                                   placeholder="0">
                                            <span class="text-xs text-[--color-ink-soft]">د.ع</span>
                                        </div>
                                    @endif
                                </td>

                                {{-- کۆی گشتی نرخ --}}
                                <td style="text-align: right; padding: 12px 16px; font-weight: 700; color: #0f172a;" class="row-total" data-value="{{ $total }}">
                                    {{ fmt_money($total) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="bg-slate-100 font-bold border-t-2 border-slate-300">
                            <td colspan="4" style="text-align: right; padding: 14px 16px; font-weight: 700; color: #0f172a; font-size: 14px;">
                                کۆی گشتی سەروەت
                            </td>
                            <td style="text-align: right; padding: 14px 16px; font-weight: 700; color: #047857; font-size: 16px;" id="grand-total-display">
                                {{ fmt_money($grandTotal) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- دوگمەکانی کردار لە خوارەوە --}}
        @if ($count->status !== 'posted')
            <div class="mt-4 flex items-center gap-2">
                <button type="submit" class="btn btn-primary !py-2 !px-6 text-sm font-semibold">پاشەکەوتکردن</button>
                <a href="{{ route('counts.index') }}" class="btn btn-ghost !py-2 !px-4 text-sm">گەڕانەوە</a>
                <button type="button" @click="showDeleteModal = true" class="btn btn-ghost mr-auto !text-[--color-danger] text-sm">
                    سڕینەوە
                </button>
            </div>
        @endif
    </form>

    {{-- مۆداڵی دڵنیابوونەوە لە سڕینەوە لە تەواوی ناوەڕاست --}}
    @if ($count->status !== 'posted')
        <template x-teleport="body">
            <div x-show="showDeleteModal"
                 x-cloak
                 class="fixed inset-0 z-50 flex items-center justify-center p-4"
                 style="background: rgba(15, 23, 42, 0.45); backdrop-filter: blur(2px);"
                 @keydown.escape.window="showDeleteModal = false">
                
                <div class="bg-white rounded-2xl border border-slate-200 p-5 max-w-xs w-full text-center space-y-4 shadow-xl"
                     @click.away="showDeleteModal = false">
                    
                    <h3 class="text-sm font-bold text-slate-800 pt-1">ئایا دڵنیایت لە سڕینەوە؟</h3>

                    <div class="grid grid-cols-2 gap-2 pt-1">
                        <button type="button" @click="showDeleteModal = false" class="btn btn-ghost !py-2 text-xs font-medium">
                            پاشگەزبوونەوە
                        </button>
                        <form action="{{ route('counts.destroy', $count) }}" method="POST" class="w-full">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger w-full !py-2 text-xs font-medium">
                                سڕینەوە
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
