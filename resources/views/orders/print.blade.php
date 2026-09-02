@php
    $gateImgPath = public_path('images/receipt_gate_thumb.jpg');
    $canopyImgPath = public_path('images/receipt_canopy_thumb.jpg');
    $gateImg = file_exists($gateImgPath) ? 'data:image/jpeg;base64,' . base64_encode(file_get_contents($gateImgPath)) : '';
    $canopyImg = file_exists($canopyImgPath) ? 'data:image/jpeg;base64,' . base64_encode(file_get_contents($canopyImgPath)) : '';
@endphp
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
            <a href="{{ route('orders.index') }}" onclick="if (window.opener) { window.close(); return false; } if (history.length > 1 && document.referrer) { history.back(); return false; }" class="btn btn-ghost !py-1.5 text-xs bg-white border border-slate-200 cursor-pointer">
                گەڕانەوە
            </a>
        </div>
    </div>

    <div class="receipt-sheet">
        <div>
            {{-- بەشی سەرەوە / سەردێڕ ڕێک بەپێی وێنەی دەفتەری وەسڵەکە --}}
            <div>
                {{-- ناوی کارگە لە سەرەوەی هەمووی بە سووری تۆخ --}}
                <h1 class="text-2xl font-black text-[#b91c1c] tracking-tight leading-none text-center mb-1">
                    {{ $settings['company_name'] ?? 'کارگەی ئاسنگەری هێمن' }}
                </h1>

                {{-- وێنەی لای چەپ، دەقەکانی ناوەڕاست، و وێنەی لای ڕاست --}}
                <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; padding: 0 4px;">
                    {{-- وێنەی لای چەپ: دەرگا و مەحەجەرە --}}
                    <div style="width: 58px; height: 50px; border-radius: 3px; overflow: hidden; border: 1px solid #cbd5e1; flex-shrink: 0; background-color: #f1f5f9; display: flex; align-items: center; justify-content: center;">
                        @if ($gateImg)
                            <img src="{{ $gateImg }}" alt="دەرگا و مەحەجەرە" style="width: 100%; height: 100%; object-fit: cover; display: block;">
                        @else
                            <span style="font-size: 9px; font-weight: 700; color: #94a3b8;">دەرگا</span>
                        @endif
                    </div>

                    {{-- دەقەکانی ناوەڕاست --}}
                    <div style="flex: 1; text-align: center; min-width: 0;">
                        <p style="font-size: 10.5px; font-weight: 800; color: #0f172a; line-height: 1.2; margin: 0;">
                            بۆ دروست کردنی دەرگا و مەحەجەرە و
                        </p>
                        <p style="font-size: 10.5px; font-weight: 800; color: #0f172a; line-height: 1.2; margin: 0;">
                            کەپر و مەسعەد
                        </p>
                        <p style="font-size: 9.5px; font-weight: 700; color: #334155; line-height: 1.2; margin-top: 2px;">
                            بە شێوازێکی هەندەسی
                        </p>
                        <p style="font-size: 11px; font-weight: 800; color: #0f172a; margin-top: 2px;" dir="rtl">
                            هێمن :
                            <span class="num" style="font-weight: 800;" dir="ltr">{{ $settings['company_phone2'] ?? '٠٧٥٠١٢٠١١١٠' }}</span>
                            -
                            <span class="num" style="font-weight: 800;" dir="ltr">{{ $settings['company_phone'] ?? '٠٧٥٠٤٥٦٨٥٥٦' }}</span>
                        </p>
                    </div>

                    {{-- وێنەی لای ڕاست: کەپر و مەسعەد --}}
                    <div style="width: 58px; height: 50px; border-radius: 3px; overflow: hidden; border: 1px solid #cbd5e1; flex-shrink: 0; background-color: #f1f5f9; display: flex; align-items: center; justify-content: center;">
                        @if ($canopyImg)
                            <img src="{{ $canopyImg }}" alt="کەپر و مەسعەد" style="width: 100%; height: 100%; object-fit: cover; display: block;">
                        @else
                            <span style="font-size: 9px; font-weight: 700; color: #94a3b8;">کەپر</span>
                        @endif
                    </div>
                </div>

                {{-- باڕی ناونیشان و ژمارەی وەسڵ لە دەستەچەپ ڕێک وەک دەفتەرەکە --}}
                <div style="margin-top: 6px; display: flex; align-items: center; gap: 6px;">
                    <div style="border: 1px solid #b91c1c; background-color: #ffffff; color: #b91c1c; font-weight: 900; font-size: 12px; padding: 2px 10px; border-radius: 2px; flex-shrink: 0;">
                        No. <span class="num">{{ $order->invoice_no }}</span>
                    </div>
                    <div style="flex: 1; background-color: #edf2f7; border-top: 1px solid #1e3a5f; border-bottom: 1px solid #1e3a5f; padding: 2px 12px; text-align: center; border-radius: 2px;">
                        <div style="font-weight: 800; font-size: 12px; color: #1e3a5f;">
                            {{ $settings['company_address'] ?? 'هەولێر — ١٠٠م بەرامبەر گۆڕستانی شێخ ئەحمەد' }}
                        </div>
                    </div>
                </div>
            </div>

            {{-- زانیاری کڕیار، ناونیشان، بەروار بە دۆتی تەواوی ڕەش --}}
            <div style="margin-top: 8px; margin-bottom: 6px; display: flex; align-items: baseline; justify-content: space-between; font-size: 11px; font-weight: 700; color: #0f172a; gap: 8px;">
                {{-- بەڕێز --}}
                <div style="display: flex; align-items: baseline; gap: 4px; flex: 1; min-width: 0;">
                    <span style="color: #0f172a; flex-shrink: 0; user-select: none;">بەڕێز :</span>
                    <div style="flex: 1; border-bottom: 1.5px dotted #000000; padding: 0 4px; min-width: 60px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                        <span style="color: #0f172a; font-weight: 800;">{{ $order->customer?->name }}</span>
                    </div>
                </div>

                {{-- ناونیشان --}}
                <div style="display: flex; align-items: baseline; gap: 4px; flex: 1; min-width: 0;">
                    <span style="color: #0f172a; flex-shrink: 0; user-select: none;">ناونیشان :</span>
                    <div style="flex: 1; border-bottom: 1.5px dotted #000000; padding: 0 4px; min-width: 60px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                        <span style="color: #1e293b; font-weight: 700;">
                            {{ $order->address_snapshot ?: ($order->customer?->address ?: '') }}
                        </span>
                    </div>
                </div>

                {{-- بەروار --}}
                <div style="display: flex; align-items: baseline; gap: 4px; flex-shrink: 0; width: 140px;">
                    <span style="color: #0f172a; flex-shrink: 0; user-select: none;">بەروار :</span>
                    <div style="flex: 1; border-bottom: 1.5px dotted #000000; text-align: center; padding: 0 4px;">
                        <span class="num" style="color: #0f172a; font-weight: 800;" dir="ltr">
                            {{ $order->order_date?->format('Y / m / d') }}
                        </span>
                    </div>
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
                                @if (!$line->has_meter && $line->pricing_mode !== 'count' && $line->measurement_label)
                                    <span class="num text-[10px] text-slate-600">
                                        ({{ $line->measurement_label }})
                                    </span>
                                @endif
                                @if ($line->note)
                                    <span class="text-[10px] text-slate-500">— {{ $line->note }}</span>
                                @endif
                            </td>

                            {{-- ژمارە / مەتر --}}
                            <td class="num text-center font-semibold text-slate-800">
                                {{ $line->has_meter ? (fmt_qty($line->meter) . ' مەتر') : fmt_qty($line->qty) }}
                            </td>

                            {{-- نرخ --}}
                            <td class="num text-center font-semibold text-slate-800">
                                {{ $line->has_meter ? fmt_num($line->meter_price) : fmt_num($line->unit_price) }}
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

                    {{-- بەشی خوارەوە: کۆی گشتی، داشکاندن، پێشەکی و ماوە لەناو یەک چوارچێوە بە مەسافەی گونجاو --}}
                    <tr>
                        <td colspan="4" style="padding: 8px 12px; border: 1.5px solid #1e3a5f; background-color: #ffffff;">
                            <div style="display: flex; flex-direction: column; gap: 8px;">
                                {{-- کۆی گشتی --}}
                                <div style="display: flex; align-items: center; justify-content: space-between; font-size: 12px; line-height: 1;">
                                    <span style="font-weight: 900; shrink: 0; color: #0f172a; transform: translateY(2px);">کۆی گشتی</span>
                                    <div style="flex: 1; margin: 0 8px; border-bottom: 1.5px dotted #000000; text-align: center; line-height: 1;">
                                        <span class="num" style="font-weight: 900; font-size: 13px; color: #000000; display: inline-block; transform: translateY(2px);">
                                            {{ fmt_num($order->total) }}
                                        </span>
                                    </div>
                                    <span style="font-weight: 900; font-size: 11px; shrink: 0; color: #0f172a; transform: translateY(2px);">
                                        {{ $order->currency === 'USD' ? 'دۆلار' : 'دینار' }}
                                    </span>
                                </div>

                                {{-- داشکاندن ئەگەر هەبێت --}}
                                @if ($order->discount_amount > 0)
                                    <div style="display: flex; align-items: center; justify-content: space-between; font-size: 12px; line-height: 1;">
                                        <span style="font-weight: 900; shrink: 0; color: #b91c1c; transform: translateY(2px);">داشکاندن</span>
                                        <div style="flex: 1; margin: 0 8px; border-bottom: 1.5px dotted #000000; text-align: center; line-height: 1;">
                                            <span class="num" style="font-weight: 900; font-size: 12px; color: #b91c1c; display: inline-block; transform: translateY(2px);">
                                                - {{ fmt_num($order->discount_amount) }}
                                            </span>
                                        </div>
                                        <span style="font-weight: 900; font-size: 11px; shrink: 0; color: #0f172a; transform: translateY(2px);">
                                            {{ $order->currency === 'USD' ? 'دۆلار' : 'دینار' }}
                                        </span>
                                    </div>
                                @endif

                                {{-- پێشەکی و ماوە تەنها کاتێک قەرز هەبێت دێتە دەرەوە --}}
                                @if ($remaining > 0)
                                    <div style="display: flex; align-items: center; justify-content: space-between; font-size: 12px; line-height: 1;">
                                        <span style="font-weight: 900; shrink: 0; color: #047857; transform: translateY(2px);">پێشەکی / پارەی دراو</span>
                                        <div style="flex: 1; margin: 0 8px; border-bottom: 1.5px dotted #000000; text-align: center; line-height: 1;">
                                            <span class="num" style="font-weight: 900; font-size: 12px; color: #047857; display: inline-block; transform: translateY(2px);">
                                                {{ fmt_num($paid) }}
                                            </span>
                                        </div>
                                        <span style="font-weight: 900; font-size: 11px; shrink: 0; color: #0f172a; transform: translateY(2px);">
                                            {{ $order->currency === 'USD' ? 'دۆلار' : 'دینار' }}
                                        </span>
                                    </div>

                                    <div style="display: flex; align-items: center; justify-content: space-between; font-size: 12px; line-height: 1;">
                                        <span style="font-weight: 900; shrink: 0; color: #b91c1c; transform: translateY(2px);">ماوە (قەرز)</span>
                                        <div style="flex: 1; margin: 0 8px; border-bottom: 1.5px dotted #000000; text-align: center; line-height: 1;">
                                            <span class="num" style="font-weight: 900; font-size: 13px; color: #b91c1c; display: inline-block; transform: translateY(2px);">
                                                {{ fmt_num($remaining) }}
                                            </span>
                                        </div>
                                        <span style="font-weight: 900; font-size: 11px; shrink: 0; color: #0f172a; transform: translateY(2px);">
                                            {{ $order->currency === 'USD' ? 'دۆلار' : 'دینار' }}
                                        </span>
                                    </div>
                                @endif
                            </div>
                        </td>
                    </tr>
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
