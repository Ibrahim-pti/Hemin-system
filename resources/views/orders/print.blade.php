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
            margin: 4mm;
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
            margin: 0 auto;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            padding: 8px 10px;
            position: relative;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            box-sizing: border-box;
        }

        .inv-table {
            width: 100%;
            table-layout: fixed;
            border-collapse: collapse;
            border: 1.5px solid #1e3a5f;
        }

        .inv-table th, .inv-table td {
            border: 1px solid #93c5fd;
            padding: 3px 5px;
            font-size: 11.5px;
        }

        .inv-table th {
            background-color: #fde8d7 !important;
            font-weight: 800;
            color: #1e3a5f;
            text-align: center;
            padding: 4px 4px;
            border: 1px solid #1e3a5f;
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
                box-shadow: none !important;
                border: none !important;
                margin: 0 !important;
                padding: 4mm !important;
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
            <div class="text-center relative">
                {{-- ژمارەی وەسڵ لە سەرەوە دەستە چەپ --}}
                <div class="absolute left-0 top-0 border border-[#b91c1c] bg-white px-2.5 py-0.5 rounded text-xs font-black text-[#b91c1c] shadow-2xs">
                    No. <span class="num">{{ $order->invoice_no }}</span>
                </div>

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

                {{-- باڕی ناونیشان تەواو لە ناوەڕاست وەک وەسڵە ئەسڵیەکە --}}
                <div class="mt-1.5 rounded-sm bg-[#1e3a5f]/10 border-y border-[#1e3a5f] py-0.5 px-3 text-center">
                    <div class="font-bold text-xs text-[#1e3a5f]">
                        {{ $settings['company_address'] ?? 'هەولێر — ١٠٠م بەرامبەر گۆڕستانی شێخ ئەحمەد' }}
                    </div>
                </div>
            </div>

            {{-- زانیاری کڕیار، ناونیشان، بەروار بەبێ هیچ خەت و هێڵێک لە ژێریاندا --}}
            <div class="mt-2.5 mb-1.5 flex items-center justify-between text-[11px] gap-3 font-bold text-slate-900">
                <div class="flex items-center gap-1.5 flex-1 min-w-0">
                    <span class="text-slate-800 shrink-0">بەڕێز:</span>
                    <span class="text-slate-900 truncate leading-none">{{ $order->customer?->name }}</span>
                </div>

                <div class="flex items-center gap-1.5 flex-1 min-w-0">
                    <span class="text-slate-800 shrink-0">ناونیشان:</span>
                    <span class="text-slate-800 truncate leading-none">
                        {{ $order->address_snapshot ?: ($order->customer?->address ?: '—') }}
                    </span>
                </div>

                <div class="flex items-center gap-1.5 shrink-0">
                    <span class="text-slate-800 shrink-0">بەروار:</span>
                    <span class="num text-slate-900 leading-none" dir="ltr">
                        {{ $order->order_date?->format('Y / m / d') }}
                    </span>
                </div>
            </div>

            {{-- خشتەی سەرەکی وەسڵ ڕێک هاوشێوەی دەفتەری وەسڵ --}}
            <table class="inv-table mt-1">
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
                            <span class="text-[10px] font-bold text-slate-700">
                                {{ $order->currency === 'USD' ? 'دۆلار' : 'دینار' }}
                            </span>
                        </th>
                        <th style="text-align: center;">ناوەڕۆک</th>
                        <th>ژمارە</th>
                        <th class="leading-tight">
                            نرخ<br>
                            <span class="text-[10px] font-bold text-slate-700">
                                {{ $order->currency === 'USD' ? 'دۆلار' : 'دینار' }}
                            </span>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $filledRows = $order->items->count();
                        $minRows = 14;
                        $remaining = $order->remaining();
                        $paid = $order->paidAmount();
                    @endphp

                    @foreach ($order->items as $line)
                        <tr style="height: 24px;">
                            {{-- بڕی پارە --}}
                            <td class="num text-center font-bold text-slate-900 text-xs">
                                {{ fmt_num($line->line_total) }}
                            </td>

                            {{-- ناوەڕۆک --}}
                            <td style="text-align: center; padding: 2px 6px;">
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
                        <tr style="height: 23px;">
                            <td>&nbsp;</td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                    @endfor

                    {{-- بەشی خوارەوە: کۆی گشتی بە دۆتی تەواو و هاوتەریب لەگەڵ دەقەکە --}}
                    <tr>
                        <td colspan="4" style="padding: 5px 12px; border: 1.5px solid #1e3a5f; background-color: #ffffff;">
                            <div style="display: flex; align-items: baseline; justify-content: space-between; font-size: 12px; line-height: 1.2;">
                                <span style="font-weight: 900; shrink: 0; color: #0f172a;">کۆی گشتی</span>
                                <div style="flex: 1; margin: 0 8px; border-bottom: 1.5px dotted #000000; display: flex; justify-content: center; align-items: baseline;">
                                    <span class="num" style="font-weight: 900; font-size: 13px; color: #000000; line-height: 1; transform: translateY(2px);">
                                        {{ fmt_num($order->total) }}
                                    </span>
                                </div>
                                <span style="font-weight: 900; font-size: 11px; shrink: 0; color: #0f172a;">
                                    {{ $order->currency === 'USD' ? 'دۆلار' : 'دینار' }}
                                </span>
                            </div>
                        </td>
                    </tr>

                    {{-- داشکاندن ئەگەر هەبێت --}}
                    @if ($order->discount_amount > 0)
                        <tr>
                            <td colspan="4" style="padding: 4px 12px; border: 1.5px solid #1e3a5f; border-top: none; background-color: #ffffff;">
                                <div style="display: flex; align-items: baseline; justify-content: space-between; font-size: 12px; line-height: 1.2;">
                                    <span style="font-weight: 900; shrink: 0; color: #b91c1c;">داشکاندن</span>
                                    <div style="flex: 1; margin: 0 8px; border-bottom: 1.5px dotted #000000; display: flex; justify-content: center; align-items: baseline;">
                                        <span class="num" style="font-weight: 900; font-size: 12px; color: #b91c1c; line-height: 1; transform: translateY(2px);">
                                            - {{ fmt_num($order->discount_amount) }}
                                        </span>
                                    </div>
                                    <span style="font-weight: 900; font-size: 11px; shrink: 0; color: #0f172a;">
                                        {{ $order->currency === 'USD' ? 'دۆلار' : 'دینار' }}
                                    </span>
                                </div>
                            </td>
                        </tr>
                    @endif

                    {{-- پێشەکی و ماوە تەنها کاتێک قەرز هەبێت دێتە دەرەوە --}}
                    @if ($remaining > 0)
                        <tr>
                            <td colspan="4" style="padding: 4px 12px; border: 1.5px solid #1e3a5f; border-top: none; background-color: #ffffff;">
                                <div style="display: flex; align-items: baseline; justify-content: space-between; font-size: 12px; line-height: 1.2;">
                                    <span style="font-weight: 900; shrink: 0; color: #047857;">پێشەکی / پارەی دراو</span>
                                    <div style="flex: 1; margin: 0 8px; border-bottom: 1.5px dotted #000000; display: flex; justify-content: center; align-items: baseline;">
                                        <span class="num" style="font-weight: 900; font-size: 12px; color: #047857; line-height: 1; transform: translateY(2px);">
                                            {{ fmt_num($paid) }}
                                        </span>
                                    </div>
                                    <span style="font-weight: 900; font-size: 11px; shrink: 0; color: #0f172a;">
                                        {{ $order->currency === 'USD' ? 'دۆلار' : 'دینار' }}
                                    </span>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="4" style="padding: 4px 12px; border: 1.5px solid #1e3a5f; border-top: none; background-color: #ffffff;">
                                <div style="display: flex; align-items: baseline; justify-content: space-between; font-size: 12px; line-height: 1.2;">
                                    <span style="font-weight: 900; shrink: 0; color: #b91c1c;">ماوە (قەرز)</span>
                                    <div style="flex: 1; margin: 0 8px; border-bottom: 1.5px dotted #000000; display: flex; justify-content: center; align-items: baseline;">
                                        <span class="num" style="font-weight: 900; font-size: 13px; color: #b91c1c; line-height: 1; transform: translateY(2px);">
                                            {{ fmt_num($remaining) }}
                                        </span>
                                    </div>
                                    <span style="font-weight: 900; font-size: 11px; shrink: 0; color: #0f172a;">
                                        {{ $order->currency === 'USD' ? 'دۆلار' : 'دینار' }}
                                    </span>
                                </div>
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>

            @if ($order->delivery_date)
                <div class="mt-1 flex items-center justify-end text-[10px]">
                    <div class="flex items-baseline gap-1 bg-slate-50 border border-slate-200 px-2 py-0.5 rounded">
                        <span class="text-slate-600 font-medium">بەرواری گەیاندن:</span>
                        <span class="num font-bold text-slate-800">{{ fmt_date($order->delivery_date) }}</span>
                    </div>
                </div>
            @endif

            {{-- ژێرەوەی وەسڵ: هەڵە دەگەڕێتەوە لە دەستەڕاست و ئیمزا لە لای چەپ --}}
            <div class="mt-3 mb-1 flex items-center justify-between text-xs px-2">
                <div class="font-bold text-slate-900 text-xs">
                    {{ $settings['invoice_footer'] ?? 'هەڵە دەگەڕێتەوە بۆ هەردوو لا' }}
                </div>

                <div class="font-bold text-slate-900 text-xs pl-8">
                    ئیمزا
                </div>
            </div>
        </div>
    </div>

</body>
</html>
