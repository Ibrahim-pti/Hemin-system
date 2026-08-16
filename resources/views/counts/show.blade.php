@extends('layouts.app')
@section('title', 'جەرد و خەمڵاندنی سەروەت')

@section('content')

<div x-data="{ showDeleteModal: false }">

    {{-- سەرەوەی جەرد --}}
    <div class="card mb-4">
        <div class="card-body flex flex-wrap items-center justify-between gap-4 text-sm">
            {{-- زانیارییەکان --}}
            <div class="flex flex-wrap items-center gap-4 sm:gap-6">
                <div>
                    <span class="text-[--color-ink-soft] text-xs block mb-0.5">ژمارەی جەرد:</span>
                    <span class="font-bold text-slate-900 num text-base">{{ $count->count_no }}</span>
                </div>
                <div>
                    <span class="text-[--color-ink-soft] text-xs block mb-0.5">کۆگا:</span>
                    <span class="font-semibold text-slate-800">{{ $count->warehouse?->name }}</span>
                </div>
                <div>
                    <span class="text-[--color-ink-soft] text-xs block mb-0.5">بەروار:</span>
                    <span class="num font-semibold text-slate-800">{{ fmt_date($count->count_date) }}</span>
                </div>
                <div>
                    <span class="text-[--color-ink-soft] text-xs block mb-0.5">دۆخ:</span>
                    <span class="badge {{ $count->status === 'posted' ? 'badge-ok' : 'badge-warn' }}">{{ $count->status_label }}</span>
                </div>
                @if($count->note)
                    <div>
                        <span class="text-[--color-ink-soft] text-xs block mb-0.5">تێبینی:</span>
                        <span class="text-slate-700 text-xs font-medium">{{ $count->note }}</span>
                    </div>
                @endif
            </div>

            {{-- دوگمەکانی سەرەوە --}}
            <div class="flex items-center gap-2">
                <a href="{{ route('counts.index') }}" class="btn btn-ghost !py-1.5 !px-3 text-xs gap-1.5 text-slate-700 hover:bg-slate-100">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 12H5M12 19l-7-7 7-7"/>
                    </svg>
                    <span>گەڕانەوە</span>
                </a>

                @if ($count->status !== 'posted')
                    <form method="POST" action="{{ route('counts.post', $count) }}"
                          onsubmit="return confirm('دوای پەسەندکردن، جیاوازییەکان وەک جوڵەی مەخزەن تۆمار دەکرێن و ناگۆڕدرێنەوە. دڵنیایت؟')">
                        @csrf
                        <button class="btn btn-primary !py-1.5 !px-3.5 text-xs font-semibold">پەسەندکردن و ڕاستکردنەوەی مەخزەن</button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('counts.update', $count) }}" id="count-form">
        @csrf @method('PUT')

        <div class="card overflow-hidden">
            <div class="card-head flex items-center justify-between bg-slate-50 border-b border-[--color-line] px-4 py-3">
                <span class="font-bold text-slate-800 text-sm">جەرد و خەمڵاندنی سەروەت</span>
                @if ($count->status !== 'posted')
                    <button type="submit" class="btn btn-primary !py-1 !px-4 text-xs font-semibold">پاشەکەوتکردن</button>
                @endif
            </div>

            <div class="overflow-x-auto">
                <table class="table w-full" id="count-table">
                    <thead>
                        <tr class="bg-slate-50/80 border-b border-[--color-line] text-slate-700 text-xs">
                            <th class="w-12 !text-center py-3 px-3">#</th>
                            <th class="!text-right py-3 px-3">جۆری سەروەت / کاڵا</th>
                            <th class="!text-right py-3 px-3 w-36">ژمارە (ژمێردراو)</th>
                            <th class="!text-right py-3 px-3 w-44">نرخی خەمڵاندراو (تاک)</th>
                            <th class="!text-right py-3 px-3 w-44">کۆی گشتی</th>
                            <th class="!text-right py-3 px-3 w-28">جیاوازی</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @php $grandTotal = 0; @endphp
                        @foreach ($count->items as $idx => $line)
                            @php
                                $diff = (float) $line->counted_qty - (float) $line->system_qty;
                                $qtyForVal = $line->counted_qty !== null ? (float) $line->counted_qty : (float) $line->system_qty;
                                $price = (float) ($line->unit_price ?? 0);
                                $total = $qtyForVal * $price;
                                $grandTotal += $total;
                            @endphp
                            <tr data-sys="{{ (float) $line->system_qty }}" class="hover:bg-slate-50/60 transition-colors text-sm">
                                {{-- # --}}
                                <td class="!text-center text-xs text-[--color-ink-soft] num py-3 px-3">{{ $idx + 1 }}</td>
                                
                                {{-- جۆری سەروەت --}}
                                <td class="!text-right font-medium text-slate-800 py-3 px-3">
                                    {{ $line->item?->name }}
                                </td>

                                {{-- ژمارە --}}
                                <td class="!text-right py-3 px-3">
                                    @if ($count->status === 'posted')
                                        <span class="font-bold text-slate-900 num">{{ fmt_qty($line->counted_qty) }}</span>
                                    @else
                                        <input type="number" step="any" name="counted[{{ $line->id }}]"
                                               value="{{ $line->counted_qty !== null ? $line->counted_qty : $line->system_qty }}"
                                               class="field num w-28 !py-1 text-right counted-input font-semibold"
                                               placeholder="{{ fmt_qty($line->system_qty) }}">
                                    @endif
                                </td>

                                {{-- نرخی خەمڵاندراو (تاک) --}}
                                <td class="!text-right py-3 px-3">
                                    @if ($count->status === 'posted')
                                        <span class="font-medium text-slate-800 num">{{ fmt_money($line->unit_price ?? 0) }}</span>
                                    @else
                                        <div class="flex items-center gap-1.5">
                                            <input type="number" step="any" name="unit_price[{{ $line->id }}]"
                                                   value="{{ (float) ($line->unit_price ?? 0) }}"
                                                   class="field num w-32 !py-1 text-right price-input font-medium"
                                                   placeholder="0">
                                            <span class="text-xs text-[--color-ink-soft]">د.ع</span>
                                        </div>
                                    @endif
                                </td>

                                {{-- کۆی گشتی نرخ --}}
                                <td class="!text-right font-bold text-slate-900 py-3 px-3 row-total num" data-value="{{ $total }}">
                                    {{ fmt_money($total) }}
                                </td>

                                {{-- جیاوازی --}}
                                <td class="!text-right font-medium py-3 px-3 diff-cell num
                                    {{ $line->counted_qty === null ? 'text-[--color-ink-soft]'
                                       : (abs($diff) < 0.0005 ? 'text-slate-600' : ($diff > 0 ? 'text-[--color-ok]' : 'text-[--color-danger]')) }}">
                                    @if ($line->counted_qty === null)
                                        0
                                    @else
                                        {{ $diff > 0 ? '+' : '' }}{{ fmt_qty($diff) }}
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="bg-slate-100 font-bold border-t-2 border-slate-300">
                            <td colspan="4" class="!text-right py-3.5 px-4 text-slate-900 text-sm font-bold">
                                کۆی گشتی سەروەت
                            </td>
                            <td class="!text-right py-3.5 px-4 text-emerald-700 text-base font-bold num" id="grand-total-display">
                                {{ fmt_money($grandTotal) }}
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        @if ($count->status !== 'posted')
            <div class="mt-4 flex items-center gap-2">
                <button type="submit" class="btn btn-primary">پاشەکەوتکردن</button>
                <a href="{{ route('counts.index') }}" class="btn btn-ghost">گەڕانەوە</a>
                <button type="button" @click="showDeleteModal = true" class="btn btn-ghost mr-auto !text-[--color-danger]">
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

            function recalculateTotals() {
                let grandTotal = 0;
                document.querySelectorAll('#count-table tbody tr').forEach(function(tr) {
                    const sys = parseFloat(tr.dataset.sys) || 0;
                    const countInput = tr.querySelector('.counted-input');
                    const priceInput = tr.querySelector('.price-input');
                    const diffCell = tr.querySelector('.diff-cell');
                    const totalCell = tr.querySelector('.row-total');

                    if (!countInput || !priceInput) return;

                    const cntVal = countInput.value.trim();
                    const priceVal = parseFloat(priceInput.value) || 0;
                    
                    let qty = cntVal === '' ? sys : (parseFloat(cntVal) || 0);
                    let rowTotal = qty * priceVal;
                    grandTotal += rowTotal;

                    totalCell.textContent = formatMoney(rowTotal);

                    if (cntVal === '') {
                        diffCell.textContent = '0';
                        diffCell.className = '!text-right font-medium py-3 px-3 diff-cell num text-slate-600';
                    } else {
                        let diff = qty - sys;
                        if (Math.abs(diff) < 0.0005) {
                            diffCell.textContent = '0';
                            diffCell.className = '!text-right font-medium py-3 px-3 diff-cell num text-slate-600';
                        } else if (diff > 0) {
                            diffCell.textContent = '+' + (Math.round(diff * 1000) / 1000);
                            diffCell.className = '!text-right font-medium py-3 px-3 diff-cell num text-[--color-ok]';
                        } else {
                            diffCell.textContent = (Math.round(diff * 1000) / 1000).toString();
                            diffCell.className = '!text-right font-medium py-3 px-3 diff-cell num text-[--color-danger]';
                        }
                    }
                });

                const grandTotalDisplay = document.getElementById('grand-total-display');
                if (grandTotalDisplay) {
                    grandTotalDisplay.textContent = formatMoney(grandTotal);
                }
            }

            document.querySelectorAll('.counted-input, .price-input').forEach(function(input) {
                input.addEventListener('input', recalculateTotals);
            });
        </script>
    @endif

</div>

@endsection
