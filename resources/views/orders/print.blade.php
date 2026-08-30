<!DOCTYPE html>
<html lang="ckb" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>وەسڵی ژمارە {{ $order->invoice_no }} — {{ $settings['company_name'] ?? 'کارگەی ئاسنگەری هێمن' }}</title>
    @vite(['resources/css/app.css'])
    <style>
        @page {
            size: A5 portrait;
            margin: 5mm;
        }

        body {
            background-color: #f1f5f9;
            font-family: var(--font-sans, system-ui, -apple-system, sans-serif);
            color: #0f172a;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .receipt-sheet {
            width: 148mm;
            max-width: 100%;
            min-height: 180mm;
            margin: 0 auto;
            background: #ffffff;
            border: 2px solid #1e3a5f;
            border-radius: 4px;
            padding: 12px 14px;
            position: relative;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .inv-table {
            width: 100%;
            table-layout: fixed;
            border-collapse: collapse;
            border: 1.5px solid #1e3a5f;
        }

        .inv-table th, .inv-table td {
            border: 1px solid #1e3a5f;
            padding: 4px 6px;
            font-size: 12px;
        }

        .inv-table th {
            background-color: #f7ede2 !important;
            font-weight: 700;
            color: #1e3a5f;
            text-align: center;
            padding: 6px 4px;
        }

        .dotted-line {
            border-bottom: 1.5px dotted #64748b;
            display: inline-block;
            padding-bottom: 1px;
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
                min-height: 180mm !important;
                box-shadow: none !important;
                border: 2px solid #1e3a5f !important;
                margin: 0 !important;
                padding: 8px 12px !important;
            }
        }
    </style>
</head>
<body class="p-3 sm:p-5">

    {{-- دوگمەکانی سەرەوە بۆ چاپ --}}
    <div class="no-print mx-auto mb-3 flex max-w-[148mm] items-center justify-between gap-2">
        <div class="flex items-center gap-2">
            <button onclick="window.print()" class="btn btn-primary !py-1.5 !px-4 text-xs shadow-sm cursor-pointer">
                🖨️ چاپکردنی وەسڵ
            </button>
            <a href="{{ route('orders.show', $order) }}" class="btn btn-ghost !py-1.5 text-xs bg-white border border-slate-200">
                گەڕانەوە بۆ وەسڵ
            </a>
        </div>
    </div>

    <div class="receipt-sheet">
        <div>
            {{-- بەشی سەرەوە / سەردێڕ بە شێوازی دەفتەری وەسڵ --}}
            <div class="text-center">
                <h1 class="text-2xl font-black text-[#b91c1c] tracking-tight leading-none">
                    {{ $settings['company_name'] ?? 'کارگەی ئاسنگەری هێمن' }}
                </h1>
                <p class="text-[11px] font-bold text-slate-800 mt-1">
                    {{ $settings['company_tagline'] ?? 'بۆ دروست کردنی دەرگا و مەحەجەرە و کەپر و مەسعەد' }}
                </p>
                <p class="text-[10px] font-semibold text-slate-700">
                    بە شێوازێکی ئەندەسی
                </p>
                <p class="text-[11px] font-bold text-slate-900 mt-0.5" dir="rtl">
                    هێمن :
                    <span class="num" dir="ltr">{{ $settings['company_phone'] ?? '٠٧٥٠٤٥٦٨٥٥٦' }}</span>
                    -
                    <span class="num" dir="ltr">{{ $settings['company_phone2'] ?? '٠٧٥٠١٢٠١١١٠' }}</span>
                </p>

                {{-- باڕی ناونیشان لە ناوەڕاست بە دیزاینێکی جوان و No لە دەستە چەپ --}}
                <div class="mt-1.5 relative flex items-center justify-center rounded-sm bg-[#1e3a5f]/10 border-y border-[#1e3a5f] py-1 px-3">
                    <div class="text-center font-bold text-xs text-[#1e3a5f]">
                        {{ $settings['company_address'] ?? 'هەولێر — ١٠٠م بەرامبەر گۆڕستانی شێخ ئەحمەد' }}
                    </div>
                    <div class="absolute left-2 top-1/2 -translate-y-1/2 border border-[#b91c1c] bg-white px-2 py-0.5 rounded text-[11px] font-black text-[#b91c1c] shadow-2xs">
                        No. <span class="num">{{ $order->invoice_no }}</span>
                    </div>
                </div>
            </div>

            {{-- زانیاری کڕیار، ناونیشان، بەروار --}}
            <div class="mt-2.5 mb-1.5 flex items-baseline justify-between text-[11px] gap-2">
                <div class="flex items-baseline gap-1 flex-1">
                    <span class="font-bold text-slate-800 shrink-0">بەڕێز:</span>
                    <span class="dotted-line font-bold text-slate-900 flex-1 px-1 truncate">{{ $order->customer?->name }}</span>
                </div>

                <div class="flex items-baseline gap-1 flex-1">
                    <span class="font-bold text-slate-800 shrink-0">ناونیشان:</span>
                    <span class="dotted-line text-slate-800 flex-1 px-1 truncate">
                        {{ $order->address_snapshot ?: ($order->customer?->address ?: '—') }}
                    </span>
                </div>

                <div class="flex items-baseline gap-1 shrink-0 w-28">
                    <span class="font-bold text-slate-800 shrink-0">بەروار:</span>
                    <span class="dotted-line num font-bold text-slate-900 flex-1 px-1 text-center" dir="ltr">
                        {{ $order->order_date?->format('Y / m / d') }}
                    </span>
                </div>
            </div>

            {{-- خشتەی سەرەکی وەسڵ --}}
            <table class="inv-table mt-1.5">
                <colgroup>
                    <col style="width: 22%;">
                    <col style="width: 48%;">
                    <col style="width: 12%;">
                    <col style="width: 18%;">
                </colgroup>
                <thead>
                    <tr>
                        <th class="leading-tight">
                            بڕی پارە<br>
                            <span class="text-[10px] font-normal text-slate-600">
                                {{ $order->currency === 'USD' ? 'دۆلار' : 'دینار' }}
                            </span>
                        </th>
                        <th style="text-align: right; padding-right: 8px;">ناوەڕۆک</th>
                        <th>ژمارە</th>
                        <th class="leading-tight">
                            نرخ<br>
                            <span class="text-[10px] font-normal text-slate-600">
                                {{ $order->currency === 'USD' ? 'دۆلار' : 'دینار' }}
                            </span>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $filledRows = $order->items->count();
                        $minRows = 12;
                        $remaining = $order->remaining();
                        $paid = $order->paidAmount();
                    @endphp

                    @foreach ($order->items as $line)
                        <tr style="height: 29px;">
                            {{-- بڕی پارە --}}
                            <td class="num text-center font-bold text-slate-900 text-xs">
                                {{ fmt_num($line->line_total) }}
                            </td>

                            {{-- ناوەڕۆک (تەنها نووسین، بەبێ وێنە بەپێی داواکاری) --}}
                            <td>
                                <span class="font-bold text-slate-900">{{ $line->description }}</span>
                                @if ($line->pricing_mode !== 'count' && $line->measurement_label)
                                    <span class="num text-[10px] text-slate-600">
                                        ({{ $line->measurement_label }})
                                    </span>
                                @endif
                                @if ($line->note)
                                    <span class="text-[10px] text-slate-500">— {{ $line->note }}</span>
                                @endif
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

                    {{-- دێڕی بەتاڵ بۆ پڕکردنەوەی وەسڵەکە وەک دەفتەری وەسڵ --}}
                    @for ($i = $filledRows; $i < $minRows; $i++)
                        <tr style="height: 28px;">
                            <td>&nbsp;</td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                    @endfor

                    {{-- کۆی گشتی بە شێوازی نووسین لەسەر خەتی خاڵخاڵ --}}
                    <tr class="bg-[#f7ede2]/40 border-t-2 border-[#1e3a5f]">
                        <td colspan="4" class="py-2 px-2.5">
                            <div class="flex items-baseline justify-between gap-1.5 text-slate-900">
                                <span class="text-sm font-black text-[#1e3a5f] shrink-0">کۆی گشتی:</span>
                                <span class="dotted-line flex-1 text-center num text-sm font-black text-slate-900 px-2">
                                    {{ fmt_num($order->total) }}
                                </span>
                                <span class="text-[11px] font-bold text-slate-700 shrink-0">{{ $order->currency === 'USD' ? 'دۆلار' : 'دینار' }}</span>
                            </div>
                        </td>
                    </tr>

                    {{-- داشکاندن ئەگەر هەبێت --}}
                    @if ($order->discount_amount > 0)
                        <tr class="border-t border-[#1e3a5f]/40 bg-rose-50/30">
                            <td colspan="4" class="py-1 px-2.5">
                                <div class="flex items-baseline justify-between gap-1.5 text-rose-700">
                                    <span class="text-[11px] font-bold shrink-0">
                                        داشکاندن {{ $order->discount_percent > 0 ? '('.fmt_num($order->discount_percent, 2).'٪)' : '' }}:
                                    </span>
                                    <span class="dotted-line flex-1 text-center num text-xs font-bold text-rose-700 px-2 !border-rose-300">
                                        - {{ fmt_num($order->discount_amount) }}
                                    </span>
                                    <span class="text-[10px] font-bold shrink-0">{{ $order->currency === 'USD' ? 'دۆلار' : 'دینار' }}</span>
                                </div>
                            </td>
                        </tr>
                    @endif

                    {{-- پێشەکی و ماوە تەنها کاتێک قەرز هەبێت دێتە دەرەوە --}}
                    @if ($remaining > 0)
                        <tr class="border-t border-[#1e3a5f]/40 bg-emerald-50/20">
                            <td colspan="4" class="py-1.5 px-2.5">
                                <div class="flex items-baseline justify-between gap-1.5 text-emerald-800">
                                    <span class="text-[11px] font-bold shrink-0">پێشەکی / پارەی دراو:</span>
                                    <span class="dotted-line flex-1 text-center num text-xs font-black text-emerald-800 px-2 !border-emerald-300">
                                        {{ fmt_num($paid) }}
                                    </span>
                                    <span class="text-[10px] font-bold text-slate-700 shrink-0">{{ $order->currency === 'USD' ? 'دۆلار' : 'دینار' }}</span>
                                </div>
                            </td>
                        </tr>
                        <tr class="border-t border-[#1e3a5f]/40 bg-red-50/20">
                            <td colspan="4" class="py-1.5 px-2.5">
                                <div class="flex items-baseline justify-between gap-1.5 text-[#b91c1c]">
                                    <span class="text-[11px] font-bold shrink-0">ماوە (قەرز):</span>
                                    <span class="dotted-line flex-1 text-center num text-xs font-black text-[#b91c1c] px-2 !border-red-300">
                                        {{ fmt_num($remaining) }}
                                    </span>
                                    <span class="text-[10px] font-bold text-slate-700 shrink-0">{{ $order->currency === 'USD' ? 'دۆلار' : 'دینار' }}</span>
                                </div>
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>

            @if ($order->delivery_date)
                <div class="mt-1.5 flex items-center justify-end text-[11px]">
                    <div class="flex items-baseline gap-1 bg-slate-50 border border-slate-200 px-2 py-0.5 rounded">
                        <span class="text-slate-600 font-medium">بەرواری گەیاندن:</span>
                        <span class="num font-bold text-slate-800">{{ fmt_date($order->delivery_date) }}</span>
                    </div>
                </div>
            @endif
        </div>

        {{-- ژێرەوەی وەسڵ: ئیمزا بەبێ دۆت و هەڵە دەگەڕێتەوە لە ناوەڕاست/ڕاست --}}
        <div class="mt-3 pt-2 border-t border-slate-200 flex items-center justify-between text-xs">
            <div class="font-black text-[#1e3a5f] text-xs">
                {{ $settings['invoice_footer'] ?? 'هەڵە دەگەڕێتەوە بۆ هەردوو لا' }}
            </div>

            <div class="font-bold text-slate-800 text-xs pl-4">
                ئیمزا
            </div>
        </div>
    </div>

</body>
</html>
