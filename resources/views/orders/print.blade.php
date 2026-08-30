<!DOCTYPE html>
<html lang="ckb" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>وەسڵی ژمارە {{ $order->invoice_no }} — {{ $settings['company_name'] ?? 'کارگەی ئاسنگەری هێمن' }}</title>
    @vite(['resources/css/app.css'])
    <style>
        @page {
            size: A4 portrait;
            margin: 10mm;
        }

        body {
            background-color: #f1f5f9;
            font-family: var(--font-sans, system-ui, -apple-system, sans-serif);
            color: #0f172a;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .receipt-sheet {
            width: 190mm;
            min-height: 260mm;
            margin: 0 auto;
            background: #ffffff;
            border: 2px solid #1e3a5f;
            border-radius: 4px;
            padding: 16px 20px;
            position: relative;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            box-sizing: border-box;
        }

        .inv-table {
            width: 100%;
            table-layout: fixed;
            border-collapse: collapse;
            border: 1.5px solid #1e3a5f;
        }

        .inv-table th, .inv-table td {
            border: 1px solid #1e3a5f;
            padding: 6px 8px;
            font-size: 13px;
        }

        .inv-table th {
            background-color: #f7ede2 !important;
            font-weight: 700;
            color: #1e3a5f;
            text-align: center;
        }

        .dotted-line {
            border-bottom: 1.5px dotted #64748b;
            display: inline-block;
            padding-bottom: 1px;
        }

        .dots-fill {
            flex: 1;
            height: 12px;
            background-image: radial-gradient(circle, #64748b 1.5px, transparent 1.5px);
            background-size: 8px 100%;
            background-repeat: repeat-x;
            background-position: center bottom 2px;
            min-width: 20px;
        }

        @media print {
            body {
                background: none !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            .no-print {
                display: none !important;
            }
            .receipt-sheet {
                width: 100% !important;
                min-height: 260mm !important;
                box-shadow: none !important;
                border: 2px solid #1e3a5f !important;
                margin: 0 !important;
                padding: 12px 16px !important;
            }
        }
    </style>
</head>
<body class="p-4 sm:p-6">

    {{-- دوگمەکانی سەرەوە بۆ چاپ --}}
    <div class="no-print mx-auto mb-4 flex max-w-[190mm] items-center justify-between gap-3">
        <div class="flex items-center gap-2">
            <button onclick="window.print()" class="btn btn-primary !py-2 !px-5 text-sm shadow-sm cursor-pointer">
                🖨️ چاپکردنی وەسڵ
            </button>
            <a href="{{ route('orders.show', $order) }}" class="btn btn-ghost !py-2 text-sm bg-white border border-slate-200">
                گەڕانەوە بۆ وەسڵ
            </a>
        </div>
    </div>

    <div class="receipt-sheet">
        <div>
            {{-- بەشی سەرەوە / سەردێڕ --}}
            <div class="border-b-2 border-[#b91c1c] pb-3">
                <div class="flex items-center justify-between">
                    {{-- ژمارەی مۆبایل و خاوەن کارگە لە دەستە ڕاست --}}
                    <div class="text-right text-xs leading-5">
                        <div class="font-bold text-slate-800">
                            هێمن:
                            <span class="num font-bold text-slate-900" dir="ltr">{{ $settings['company_phone'] ?? '٠٧٥٠٤٥٦٨٥٥٦' }}</span>
                            -
                            <span class="num font-bold text-slate-900" dir="ltr">{{ $settings['company_phone2'] ?? '٠٧٥٠١٢٠١١١٠' }}</span>
                        </div>
                        <div class="text-[11px] text-slate-600 mt-0.5">
                            {{ $settings['company_address'] ?? 'هەولێر — ١٠٠م بەرامبەر گۆڕستانی شێخ ئەحمەد' }}
                        </div>
                    </div>

                    {{-- ناوی سەرەکی کارگە لە ناوەڕاست --}}
                    <div class="text-center flex-1 px-4">
                        <h1 class="text-2xl sm:text-3xl font-black text-[#b91c1c] tracking-tight">
                            {{ $settings['company_name'] ?? 'کارگەی ئاسنگەری هێمن' }}
                        </h1>
                        <p class="text-[11px] sm:text-xs font-semibold text-slate-800 mt-0.5">
                            {{ $settings['company_tagline'] ?? 'بۆ دروست کردنی دەرگا و مەحەجەرە و کەپر و مەسعەد بە شێوازێکی ئەندەسی' }}
                        </p>
                    </div>

                    {{-- ژمارەی پسوولە لە چەپ --}}
                    <div class="text-left">
                        <div class="inline-block rounded border-2 border-[#b91c1c] bg-red-50/50 px-3 py-1 text-sm font-bold text-[#b91c1c]">
                            No. <span class="num">{{ $order->invoice_no }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- زانیاری کڕیار و بەروار بە شێوازی خەتی خاڵخاڵ --}}
            <div class="mt-3.5 mb-2 grid grid-cols-12 gap-2 text-[13px]">
                <div class="col-span-5 flex items-baseline gap-1.5">
                    <span class="font-bold text-slate-800 shrink-0">بەڕێز:</span>
                    <span class="dotted-line font-bold text-slate-900 flex-1 px-1">{{ $order->customer?->name }}</span>
                </div>

                <div class="col-span-4 flex items-baseline gap-1.5">
                    <span class="font-bold text-slate-800 shrink-0">ناونیشان:</span>
                    <span class="dotted-line text-slate-800 flex-1 px-1 truncate">
                        {{ $order->address_snapshot ?: ($order->customer?->address ?: '—') }}
                    </span>
                </div>

                <div class="col-span-3 flex items-baseline gap-1.5">
                    <span class="font-bold text-slate-800 shrink-0">بەروار:</span>
                    <span class="dotted-line num font-bold text-slate-900 flex-1 px-1 text-center" dir="ltr">
                        {{ $order->order_date?->format('Y / m / d') }}
                    </span>
                </div>
            </div>

            {{-- خشتەی سەرەکی وەسڵ (ڕێک وەک دەفتەرە چاپکراوەکە) --}}
            <table class="inv-table mt-3">
                <colgroup>
                    <col style="width: 20%;">
                    <col style="width: 52%;">
                    <col style="width: 10%;">
                    <col style="width: 18%;">
                </colgroup>
                <thead>
                    <tr>
                        <th class="leading-snug">
                            بڕی پارە<br>
                            <span class="text-[11px] font-normal text-slate-600">
                                {{ $order->currency === 'USD' ? 'دۆلار' : 'دینار' }}
                            </span>
                        </th>
                        <th style="text-align: right; padding-right: 12px;">ناوەڕۆک</th>
                        <th>ژمارە</th>
                        <th class="leading-snug">
                            نرخ<br>
                            <span class="text-[11px] font-normal text-slate-600">
                                {{ $order->currency === 'USD' ? 'دۆلار' : 'دینار' }}
                            </span>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $filledRows = $order->items->count();
                        $minRows = 11;
                    @endphp

                    @foreach ($order->items as $line)
                        <tr style="height: 38px;">
                            {{-- بڕی پارە --}}
                            <td class="num text-center font-bold text-slate-900 text-sm">
                                {{ fmt_num($line->line_total) }}
                            </td>

                            {{-- ناوەڕۆک + وێنە ئەگەر هەبێت --}}
                            <td>
                                <div class="flex items-center gap-2">
                                    @if ($line->imageUrl())
                                        <img src="{{ $line->imageUrl() }}"
                                             class="size-7 rounded object-cover border border-slate-300 shrink-0"
                                             alt="دیزاین">
                                    @endif
                                    <span class="font-bold text-slate-900">{{ $line->description }}</span>
                                    @if ($line->pricing_mode !== 'count' && $line->measurement_label)
                                        <span class="num text-xs text-slate-600">
                                            ({{ $line->measurement_label }} = {{ fmt_qty($line->computed_qty) }} {{ $line->mode_unit }})
                                        </span>
                                    @endif
                                    @if ($line->note)
                                        <span class="text-xs text-slate-500">— {{ $line->note }}</span>
                                    @endif
                                </div>
                            </td>

                            {{-- ژمارە --}}
                            <td class="num text-center font-semibold text-slate-800">
                                {{ fmt_qty($line->qty) }}
                            </td>

                            {{-- نرخ --}}
                            <td class="num text-center font-semibold text-slate-800">
                                {{ fmt_num($line->unit_price) }}
                            </td>
                        </tr>
                    @endforeach

                    {{-- دێڕی بەتاڵ بۆ ئەوەی وەسڵەکە وەک دەفتەرە ئەسڵییەکە پڕ بێت --}}
                    @for ($i = $filledRows; $i < $minRows; $i++)
                        <tr style="height: 34px;">
                            <td>&nbsp;</td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                    @endfor

                    {{-- کۆی گشتی --}}
                    <tr class="bg-[#f7ede2]/50 border-t-2 border-[#1e3a5f]">
                        <td colspan="4" class="py-2.5 px-3">
                            <div class="flex items-center justify-between gap-2 text-slate-900">
                                <span class="text-base font-black text-[#1e3a5f] shrink-0">کۆی گشتی:</span>
                                <div class="dots-fill mx-2"></div>
                                <span class="num text-base font-black text-slate-900 px-3 shrink-0">
                                    {{ fmt_num($order->total) }}
                                </span>
                                <div class="dots-fill mx-2"></div>
                                <span class="text-xs font-bold text-slate-700 shrink-0">{{ $order->currency === 'USD' ? 'دۆلار' : 'دینار' }}</span>
                            </div>
                        </td>
                    </tr>

                    {{-- داشکاندن ئەگەر هەبێت --}}
                    @if ($order->discount_amount > 0)
                        <tr class="border-t border-[#1e3a5f]/40 bg-rose-50/30">
                            <td colspan="4" class="py-1.5 px-3">
                                <div class="flex items-center justify-between gap-2 text-rose-700">
                                    <span class="text-xs font-bold shrink-0">
                                        داشکاندن {{ $order->discount_percent > 0 ? '('.fmt_num($order->discount_percent, 2).'٪)' : '' }}:
                                    </span>
                                    <div class="dots-fill mx-2 opacity-60"></div>
                                    <span class="num text-sm font-bold shrink-0 px-3">
                                        - {{ fmt_num($order->discount_amount) }}
                                    </span>
                                    <div class="dots-fill mx-2 opacity-60"></div>
                                    <span class="text-xs font-bold shrink-0">{{ $order->currency === 'USD' ? 'دۆلار' : 'دینار' }}</span>
                                </div>
                            </td>
                        </tr>
                    @endif

                    {{-- پێشەکی و ماوە --}}
                    @php $paid = $order->paidAmount(); @endphp
                    @if ($paid > 0 || $order->remaining() > 0)
                        <tr class="border-t border-[#1e3a5f]/40 bg-emerald-50/20">
                            <td colspan="4" class="py-2 px-3">
                                <div class="flex items-center justify-between gap-2 text-emerald-800">
                                    <span class="text-xs font-bold shrink-0">پێشەکی / پارەی دراو:</span>
                                    <div class="dots-fill mx-2 opacity-70"></div>
                                    <span class="num text-sm font-black text-emerald-800 px-3 shrink-0">
                                        {{ fmt_num($paid) }}
                                    </span>
                                    <div class="dots-fill mx-2 opacity-70"></div>
                                    <span class="text-xs font-bold text-slate-700 shrink-0">{{ $order->currency === 'USD' ? 'دۆلار' : 'دینار' }}</span>
                                </div>
                            </td>
                        </tr>
                        <tr class="border-t border-[#1e3a5f]/40 {{ $order->remaining() > 0 ? 'bg-red-50/30' : 'bg-emerald-50/10' }}">
                            <td colspan="4" class="py-2 px-3">
                                <div class="flex items-center justify-between gap-2 {{ $order->remaining() > 0 ? 'text-[#b91c1c]' : 'text-emerald-800' }}">
                                    <span class="text-xs font-bold shrink-0">ماوە (قەرز):</span>
                                    <div class="dots-fill mx-2 opacity-70"></div>
                                    <span class="num text-sm font-black px-3 shrink-0">
                                        {{ fmt_num($order->remaining()) }}
                                    </span>
                                    <div class="dots-fill mx-2 opacity-70"></div>
                                    <span class="text-xs font-bold text-slate-700 shrink-0">{{ $order->currency === 'USD' ? 'دۆلار' : 'دینار' }}</span>
                                </div>
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>

            @if ($order->delivery_date)
                <div class="mt-2.5 flex items-center justify-end text-xs">
                    <div class="flex items-baseline gap-1.5 bg-slate-50 border border-slate-200 px-2.5 py-1 rounded">
                        <span class="text-slate-600 font-medium">بەرواری گەیاندن:</span>
                        <span class="num font-bold text-slate-800">{{ fmt_date($order->delivery_date) }}</span>
                    </div>
                </div>
            @endif
        </div>

        {{-- ژێرەوەی وەسڵ: هەڵە دەگەڕێتەوە بۆ هەردوو لا و ئیمزا --}}
        <div class="mt-6 pt-3 border-t border-slate-200 flex items-end justify-between text-sm">
            <div class="font-black text-[#1e3a5f] text-sm">
                {{ $settings['invoice_footer'] ?? 'هەڵە دەگەڕێتەوە بۆ هەردوو لا' }}
            </div>

            <div class="flex items-baseline gap-2">
                <span class="font-bold text-slate-800">ئیمزا:</span>
                <span class="dotted-line w-32">&nbsp;</span>
            </div>
        </div>

    </div>

</body>
</html>
