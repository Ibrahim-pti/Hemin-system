@php
    $gateImgPath = public_path('images/receipt_gate_thumb.jpg');
    $canopyImgPath = public_path('images/receipt_canopy_thumb.jpg');
    $gateImg = file_exists($gateImgPath) ? 'data:image/jpeg;base64,' . base64_encode(file_get_contents($gateImgPath)) : '';
    $canopyImg = file_exists($canopyImgPath) ? 'data:image/jpeg;base64,' . base64_encode(file_get_contents($canopyImgPath)) : '';

    $filledRows = $order->items->count();
    // بۆ قەبارەی ستاندارد A4 کامل: ١٢ دێڕی خشتە بۆ ئەوەی لاپەڕەکە بە کامڵی پڕ بکاتەوە
    $a4TotalRows = max($filledRows + 1, 12);
    $a4EmptyRows = max(0, $a4TotalRows - $filledRows);

    // بۆ قەبارەی A5 یان دوو دانە لە A4: ٥ دێڕ
    $compactTotalRows = max($filledRows + 1, 5);

    $remaining = $order->remaining();
    $paid = $order->paidAmount();
@endphp
<!DOCTYPE html>
<html lang="ckb" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>وەسڵی ژمارە {{ $order->invoice_no }} — {{ $settings['company_name'] ?? 'کارگەی ئاسنگەری هێمن' }}</title>
    @vite(['resources/css/app.css'])

    {{-- ستایلی دینامیکی بۆ قەبارەی لاپەڕەی چاپ --}}
    <style id="dynamic-page-style">
        @page {
            size: A4 portrait;
            margin: 8mm 10mm 8mm 10mm;
        }
    </style>

    <style>
        body {
            background-color: #f1f5f9;
            font-family: var(--font-sans, system-ui, -apple-system, sans-serif);
            color: #0f172a;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            transition: all 0.2s ease;
        }

        /* قەبارەی ستاندارد A4 (کامل) بە پێوانەی ٢١٠ ملم */
        .receipt-sheet {
            width: 210mm;
            max-width: 100%;
            min-height: 272mm;
            margin: 0 auto;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            padding: 8mm 10mm;
            position: relative;
            box-shadow: 0 4px 25px rgba(0,0,0,0.07);
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        /* قەبارەی A5 */
        body.mode-a5 .receipt-sheet {
            width: 148mm !important;
            min-height: auto !important;
            padding: 5mm 6mm !important;
        }

        /* دوو دانە لە یەک لاپەڕەی A4 */
        body.mode-a4_dual .receipt-sheet {
            width: 210mm !important;
            min-height: auto !important;
            padding: 4mm 8mm !important;
            box-shadow: none !important;
        }

        .inv-table {
            width: 100%;
            table-layout: fixed;
            border-collapse: collapse;
            border: 1.5px solid #1e3a5f;
        }

        .inv-table th, .inv-table td {
            border: 1px solid #93c5fd;
            padding: 4px 6px;
            font-size: 12px;
        }

        .inv-table th {
            background-color: #fde8d7 !important;
            font-weight: 800;
            color: #1e3a5f;
            text-align: center;
            padding: 5px 4px;
            border: 1px solid #1e3a5f;
            font-size: 12.5px;
        }

        /* دێڕەکانی خشتە لە قەبارەی A4 گەورەتر و مەزبووتن */
        .inv-row {
            height: 30px;
        }

        /* لە دۆخی A5 یان دوو دانە کەم دەکرێتەوە */
        body.mode-a5 .inv-row,
        body.mode-a4_dual .inv-row {
            height: 20px !important;
        }

        body.mode-a5 .extra-a4-row,
        body.mode-a4_dual .extra-a4-row {
            display: none !important;
        }

        .dual-copy-block {
            display: none;
        }
        body.mode-a4_dual .dual-copy-block {
            display: block;
        }

        @media print {
            body {
                background: #ffffff !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            .no-print {
                display: none !important;
            }
            .receipt-sheet {
                width: 100% !important;
                max-width: 100% !important;
                min-height: auto !important;
                box-shadow: none !important;
                border: none !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            body.mode-a4_dual .receipt-sheet {
                padding: 2mm 0 !important;
            }
            .cut-line {
                border-bottom: 2px dashed #94a3b8 !important;
                margin: 4mm 0 !important;
            }
        }
    </style>
</head>
<body class="p-3 sm:p-5 mode-a4">

    {{-- باڕی ئامرازەکانی سەرەوە (تەنها لە شاشە، چاپ نابێت) --}}
    <div class="no-print mx-auto mb-4 max-w-[210mm] flex flex-col sm:flex-row items-center justify-between gap-3 bg-white p-3 rounded-2xl border border-slate-200/90 shadow-2xs">
        {{-- لای ڕاست: دوگمەی چاپ و کردارەکان --}}
        <div class="flex items-center gap-2 flex-wrap">
            <button onclick="window.print()" class="btn btn-primary !py-2 !px-5 text-xs font-black shadow-sm cursor-pointer flex items-center gap-2">
                <span>🖨️</span>
                <span>چاپکردنی وەسڵ</span>
            </button>
            @if (! in_array($order->status, ['delivered', 'cancelled'], true))
                <a href="{{ route('orders.edit', $order) }}" class="btn btn-ghost !py-2 !px-3 text-xs bg-slate-50 border border-slate-200 hover:bg-slate-100 font-bold cursor-pointer">
                    ✏️ دەستکاری
                </a>
            @endif
            <a href="{{ route('orders.create') }}" class="btn btn-ghost !py-2 !px-3 text-xs bg-slate-50 border border-slate-200 hover:bg-slate-100 font-bold cursor-pointer">
                + وەسڵی نوێ
            </a>
            <a href="{{ route('orders.index') }}" class="btn btn-ghost !py-2 !px-3 text-xs bg-slate-50 border border-slate-200 hover:bg-slate-100 font-bold cursor-pointer">
                گەڕانەوە
            </a>
        </div>

        {{-- لای چەپ: هەڵبژاردنی قەبارەی ستاندارد --}}
        <div class="flex items-center gap-1 bg-slate-100/90 p-1 rounded-xl border border-slate-200 text-xs font-bold">
            <button type="button" onclick="setPrintMode('a4')" data-mode="a4"
                    class="mode-btn !py-1.5 !px-3 rounded-lg transition-all cursor-pointer bg-blue-600 text-white shadow-xs"
                    title="پڕکردنەوەی تەواوی لاپەڕەی A4">
                📄 ستاندارد A4 (کامل)
            </button>
            <button type="button" onclick="setPrintMode('a4_dual')" data-mode="a4_dual"
                    class="mode-btn !py-1.5 !px-3 rounded-lg transition-all cursor-pointer text-slate-700 hover:bg-slate-200"
                    title="دوو دانە لە یەک پەڕەی A4دا">
                📑 ٢ دانە لە A4 (کڕیار + کارگە)
            </button>
            <button type="button" onclick="setPrintMode('a5')" data-mode="a5"
                    class="mode-btn !py-1.5 !px-3 rounded-lg transition-all cursor-pointer text-slate-700 hover:bg-slate-200"
                    title="بۆ کاغەزی A5">
                📝 قەبارەی A5
            </button>
        </div>
    </div>

    @if (session('ok'))
        <div class="no-print mx-auto mb-3 max-w-[210mm] bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold rounded-xl px-4 py-2.5 flex items-center justify-between">
            <span>✓ {{ session('ok') }}</span>
        </div>
    @endif

    {{-- ══════════════════════════════════════════════════════════════════ --}}
    {{-- وەسڵی سەرەکی (دانەی یەکەم / کامل A4)                             --}}
    {{-- ══════════════════════════════════════════════════════════════════ --}}
    <div class="receipt-sheet">
        <div>
            {{-- بەشی سەرەوە / سەردێڕ ڕێک بەپێی وێنەی دەفتەری وەسڵەکە --}}
            <div>
                {{-- ناوی کارگە لە سەرەوەی هەمووی بە سووری تۆخ --}}
                <div class="flex items-center justify-between gap-2 mb-1">
                    <div class="w-16 hidden sm:block"></div>
                    <h1 class="text-2xl sm:text-[26px] font-black text-[#b91c1c] tracking-tight leading-none text-center flex-1">
                        {{ $settings['company_name'] ?? 'کارگەی ئاسنگەری هێمن' }}
                    </h1>
                    <div class="text-[11px] font-bold text-slate-500 w-16 text-left">
                        <span class="copy-badge hidden px-2 py-0.5 rounded bg-blue-50 text-blue-700 border border-blue-200">دانەی کڕیار</span>
                    </div>
                </div>

                {{-- وێنەی لای چەپ، دەقەکانی ناوەڕاست، و وێنەی لای ڕاست --}}
                <div style="display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 0 4px;">
                    {{-- وێنەی لای چەپ: دەرگا و مەحەجەرە --}}
                    <div style="width: 65px; height: 55px; border-radius: 4px; overflow: hidden; border: 1px solid #cbd5e1; flex-shrink: 0; background-color: #f1f5f9; display: flex; align-items: center; justify-content: center;">
                        @if ($gateImg)
                            <img src="{{ $gateImg }}" alt="دەرگا و مەحەجەرە" style="width: 100%; height: 100%; object-fit: cover; display: block;">
                        @else
                            <span style="font-size: 10px; font-weight: 700; color: #94a3b8;">دەرگا</span>
                        @endif
                    </div>

                    {{-- دەقەکانی ناوەڕاست --}}
                    <div style="flex: 1; text-align: center; min-width: 0;">
                        <p style="font-size: 11.5px; font-weight: 800; color: #0f172a; line-height: 1.25; margin: 0;">
                            بۆ دروست کردنی دەرگا و مەحەجەرە و
                        </p>
                        <p style="font-size: 11.5px; font-weight: 800; color: #0f172a; line-height: 1.25; margin: 0;">
                            کەپر و مەسعەد
                        </p>
                        <p style="font-size: 10px; font-weight: 700; color: #334155; line-height: 1.2; margin-top: 2px;">
                            بە شێوازێکی هەندەسی
                        </p>
                        <p style="font-size: 11.5px; font-weight: 800; color: #0f172a; margin-top: 2px;" dir="rtl">
                            هێمن :
                            <span class="num" style="font-weight: 800;" dir="ltr">{{ $settings['company_phone2'] ?? '٠٧٥٠١٢٠١١١٠' }}</span>
                            -
                            <span class="num" style="font-weight: 800;" dir="ltr">{{ $settings['company_phone'] ?? '٠٧٥٠٤٥٦٨٥٥٦' }}</span>
                        </p>
                    </div>

                    {{-- وێنەی لای ڕاست: کەپر و مەسعەد --}}
                    <div style="width: 65px; height: 55px; border-radius: 4px; overflow: hidden; border: 1px solid #cbd5e1; flex-shrink: 0; background-color: #f1f5f9; display: flex; align-items: center; justify-content: center;">
                        @if ($canopyImg)
                            <img src="{{ $canopyImg }}" alt="کەپر و مەسعەد" style="width: 100%; height: 100%; object-fit: cover; display: block;">
                        @else
                            <span style="font-size: 10px; font-weight: 700; color: #94a3b8;">کەپر</span>
                        @endif
                    </div>
                </div>

                {{-- باڕی ناونیشان و ژمارەی وەسڵ لە دەستەچەپ --}}
                <div style="margin-top: 6px; display: flex; align-items: center; gap: 8px;">
                    <div style="border: 1.5px solid #b91c1c; background-color: #ffffff; color: #b91c1c; font-weight: 900; font-size: 13px; padding: 2px 12px; border-radius: 3px; flex-shrink: 0;">
                        No. <span class="num">{{ $order->invoice_no }}</span>
                    </div>
                    <div style="flex: 1; background-color: #edf2f7; border-top: 1px solid #1e3a5f; border-bottom: 1px solid #1e3a5f; padding: 3px 14px; text-align: center; border-radius: 3px;">
                        <div style="font-weight: 800; font-size: 12.5px; color: #1e3a5f;">
                            {{ $settings['company_address'] ?? 'هەولێر — ١٠٠م بەرامبەر گۆڕستانی شێخ ئەحمەد' }}
                        </div>
                    </div>
                </div>
            </div>

            {{-- زانیاری کڕیار، ناونیشان، بەروار بە دۆتی تەواوی ڕەش --}}
            <div style="margin-top: 10px; margin-bottom: 8px; display: flex; align-items: baseline; justify-content: space-between; font-size: 12px; font-weight: 700; color: #0f172a; gap: 10px;">
                {{-- بەڕێز --}}
                <div style="display: flex; align-items: baseline; gap: 4px; flex: 1; min-width: 0;">
                    <span style="color: #0f172a; flex-shrink: 0; user-select: none; font-weight: 800;">بەڕێز :</span>
                    <div style="flex: 1; border-bottom: 1.5px dotted #000000; padding: 0 4px; min-width: 60px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                        <span style="color: #0f172a; font-weight: 800;">{{ $order->customer?->name }}</span>
                    </div>
                </div>

                {{-- ناونیشان --}}
                <div style="display: flex; align-items: baseline; gap: 4px; flex: 1; min-width: 0;">
                    <span style="color: #0f172a; flex-shrink: 0; user-select: none; font-weight: 800;">ناونیشان :</span>
                    <div style="flex: 1; border-bottom: 1.5px dotted #000000; padding: 0 4px; min-width: 60px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                        <span style="color: #1e293b; font-weight: 700;">
                            {{ $order->address_snapshot ?: ($order->customer?->address ?: '') }}
                        </span>
                    </div>
                </div>

                {{-- بەروار --}}
                <div style="display: flex; align-items: baseline; gap: 4px; flex-shrink: 0; width: 150px;">
                    <span style="color: #0f172a; flex-shrink: 0; user-select: none; font-weight: 800;">بەروار :</span>
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
                    <col style="width: 13%;">
                    <col style="width: 17%;">
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
                        <th>ژمارە / ڕێژە</th>
                        <th class="leading-tight">
                            نرخ<br>
                            <span class="text-[10px] font-bold text-slate-700">
                                {{ $order->currency === 'USD' ? 'دۆلار' : 'دینار' }}
                            </span>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    {{-- ١. کاڵاکانی داواکاری --}}
                    @foreach ($order->items as $line)
                        <tr class="inv-row">
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
                            <td dir="rtl" class="text-center font-semibold text-slate-800">
                                @if ($line->has_meter)
                                    <span dir="rtl">&#8207;{{ fmt_qty($line->meter) }} مەتر</span>
                                @else
                                    <span class="num">{{ fmt_qty($line->qty) }}</span>
                                @endif
                            </td>

                            {{-- نرخ --}}
                            <td class="num text-center font-semibold text-slate-800">
                                {{ $line->has_meter ? fmt_num($line->meter_price) : fmt_num($line->unit_price) }}
                            </td>
                        </tr>
                    @endforeach

                    {{-- ٢. دێڕە بەتاڵەکان: ٥ دێڕی یەکەم لە هەموو دۆخەکان دەردەکەون، ٦ بۆ ١٢ تەنها لە A4 کامل --}}
                    @for ($i = 0; $i < $a4EmptyRows; $i++)
                        @php $isExtraRow = ($filledRows + $i) >= $compactTotalRows; @endphp
                        <tr class="inv-row {{ $isExtraRow ? 'extra-a4-row' : '' }}">
                            <td>&nbsp;</td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                    @endfor

                    {{-- بەشی خوارەوە: کۆی گشتی، داشکاندن، پێشەکی و ماوە لەناو یەک چوارچێوە بە مەسافەی گونجاو --}}
                    <tr>
                        <td colspan="4" style="padding: 10px 14px; border: 1.5px solid #1e3a5f; background-color: #ffffff;">
                            <div style="display: flex; flex-direction: column; gap: 8px;">
                                {{-- کۆی گشتی --}}
                                <div style="display: flex; align-items: center; justify-content: space-between; font-size: 13px; line-height: 1;">
                                    <span style="font-weight: 900; shrink: 0; color: #0f172a;">کۆی گشتی</span>
                                    <div style="flex: 1; margin: 0 10px; border-bottom: 1.5px dotted #000000; text-align: center; line-height: 1;">
                                        <span class="num" style="font-weight: 900; font-size: 15px; color: #000000; display: inline-block;">
                                            {{ fmt_num($order->total) }}
                                        </span>
                                    </div>
                                    <span style="font-weight: 900; font-size: 12px; shrink: 0; color: #0f172a;">
                                        {{ $order->currency === 'USD' ? 'دۆلار' : 'دینار' }}
                                    </span>
                                </div>

                                {{-- داشکاندن ئەگەر هەبێت --}}
                                @if ($order->discount_amount > 0)
                                    <div style="display: flex; align-items: center; justify-content: space-between; font-size: 13px; line-height: 1;">
                                        <span style="font-weight: 900; shrink: 0; color: #b91c1c;">داشکاندن</span>
                                        <div style="flex: 1; margin: 0 10px; border-bottom: 1.5px dotted #000000; text-align: center; line-height: 1;">
                                            <span class="num" style="font-weight: 900; font-size: 13px; color: #b91c1c; display: inline-block;">
                                                - {{ fmt_num($order->discount_amount) }}
                                            </span>
                                        </div>
                                        <span style="font-weight: 900; font-size: 12px; shrink: 0; color: #0f172a;">
                                            {{ $order->currency === 'USD' ? 'دۆلار' : 'دینار' }}
                                        </span>
                                    </div>
                                @endif

                                {{-- پێشەکی و ماوە تەنها کاتێک قەرز هەبێت دێتە دەرەوە --}}
                                @if ($remaining > 0)
                                    <div style="display: flex; align-items: center; justify-content: space-between; font-size: 13px; line-height: 1;">
                                        <span style="font-weight: 900; shrink: 0; color: #047857;">پێشەکی / پارەی دراو</span>
                                        <div style="flex: 1; margin: 0 10px; border-bottom: 1.5px dotted #000000; text-align: center; line-height: 1;">
                                            <span class="num" style="font-weight: 900; font-size: 13px; color: #047857; display: inline-block;">
                                                {{ fmt_num($paid) }}
                                            </span>
                                        </div>
                                        <span style="font-weight: 900; font-size: 12px; shrink: 0; color: #0f172a;">
                                            {{ $order->currency === 'USD' ? 'دۆلار' : 'دینار' }}
                                        </span>
                                    </div>

                                    <div style="display: flex; align-items: center; justify-content: space-between; font-size: 13px; line-height: 1;">
                                        <span style="font-weight: 900; shrink: 0; color: #b91c1c;">ماوە (قەرز)</span>
                                        <div style="flex: 1; margin: 0 10px; border-bottom: 1.5px dotted #000000; text-align: center; line-height: 1;">
                                            <span class="num" style="font-weight: 900; font-size: 15px; color: #b91c1c; display: inline-block;">
                                                {{ fmt_num($remaining) }}
                                            </span>
                                        </div>
                                        <span style="font-weight: 900; font-size: 12px; shrink: 0; color: #0f172a;">
                                            {{ $order->currency === 'USD' ? 'دۆلار' : 'دینار' }}
                                        </span>
                                    </div>
                                @endif
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- بەشی خوارەوە: تێبینی لە ڕاست، مۆری فەرمی لە ناوەڕاست، ئیمزا لە لای چەپ --}}
        <div style="margin-top: 16px; margin-bottom: 4px; display: flex; align-items: flex-end; justify-content: space-between; padding: 0 8px; font-size: 12px;">
            <div style="flex: 1; color: #0f172a; font-weight: 800;">
                <div>{{ $settings['invoice_footer'] ?? 'هەڵە دەگەڕێتەوە بۆ هەردوو لا' }}</div>
                @if ($order->delivery_date)
                    <div style="margin-top: 5px; font-size: 11px; color: #475569; font-weight: 700;">
                        بەرواری گەیاندن: <span class="num font-bold">{{ fmt_date($order->delivery_date) }}</span>
                    </div>
                @endif
            </div>

            <div style="width: 130px; text-align: center; color: #64748b; font-size: 11px; font-weight: 700;">
                <div style="height: 44px; border: 1.5px dashed #cbd5e1; border-radius: 6px; display: flex; align-items: center; justify-content: center; margin-bottom: 3px;">
                    مۆری فەرمی
                </div>
            </div>

            <div style="width: 140px; text-align: center; font-weight: 800; color: #0f172a;">
                <div style="border-bottom: 1.5px dotted #000000; height: 35px; margin-bottom: 4px;"></div>
                ئیمزای وەرگر
            </div>
        </div>
    </div>


    {{-- ══════════════════════════════════════════════════════════════════ --}}
    {{-- هێڵی بڕین و دانەی دووەم (تەنها لە دۆخی دوو دانە لە A4)              --}}
    {{-- ══════════════════════════════════════════════════════════════════ --}}
    <div class="dual-copy-block">
        <div class="cut-line my-3 flex items-center justify-center gap-3 text-xs text-slate-500 font-bold border-b-2 border-dashed border-slate-300 py-1 select-none">
            <span>✂️</span>
            <span>هێڵی بڕین — نیوەی لاپەڕە</span>
            <span>✂️</span>
        </div>

        <div class="receipt-sheet !min-h-0">
            <div>
                {{-- سەردێڕی دانەی دووەم (دانەی کارگە / دەفتەر) --}}
                <div class="flex items-center justify-between gap-2 mb-1">
                    <div class="w-16 hidden sm:block"></div>
                    <h2 class="text-xl sm:text-[22px] font-black text-[#b91c1c] tracking-tight leading-none text-center flex-1">
                        {{ $settings['company_name'] ?? 'کارگەی ئاسنگەری هێمن' }}
                    </h2>
                    <div class="text-[11px] font-bold text-slate-500 w-24 text-left">
                        <span class="px-2 py-0.5 rounded bg-purple-50 text-purple-700 border border-purple-200">دانەی کارگە</span>
                    </div>
                </div>

                <div style="margin-top: 4px; display: flex; align-items: center; gap: 8px;">
                    <div style="border: 1.5px solid #b91c1c; background-color: #ffffff; color: #b91c1c; font-weight: 900; font-size: 12px; padding: 1px 10px; border-radius: 3px; flex-shrink: 0;">
                        No. <span class="num">{{ $order->invoice_no }}</span>
                    </div>
                    <div style="flex: 1; background-color: #edf2f7; border-top: 1px solid #1e3a5f; border-bottom: 1px solid #1e3a5f; padding: 2px 10px; text-align: center; border-radius: 3px; font-size: 11px; font-weight: 800; color: #1e3a5f;">
                        {{ $settings['company_address'] ?? 'هەولێر — ١٠٠م بەرامبەر گۆڕستانی شێخ ئەحمەد' }}
                    </div>
                </div>

                {{-- زانیاری کڕیار --}}
                <div style="margin-top: 6px; margin-bottom: 6px; display: flex; align-items: baseline; justify-content: space-between; font-size: 11px; font-weight: 700; color: #0f172a; gap: 8px;">
                    <div style="display: flex; align-items: baseline; gap: 4px; flex: 1;">
                        <span style="font-weight: 800;">بەڕێز:</span>
                        <span style="font-weight: 800; border-bottom: 1px dotted #000; flex: 1; padding: 0 4px;">{{ $order->customer?->name }}</span>
                    </div>
                    <div style="display: flex; align-items: baseline; gap: 4px; flex-shrink: 0; width: 130px;">
                        <span style="font-weight: 800;">بەروار:</span>
                        <span class="num font-bold" dir="ltr">{{ $order->order_date?->format('Y/m/d') }}</span>
                    </div>
                </div>

                {{-- خشتەی پوختەی دانەی دووەم --}}
                <table class="inv-table">
                    <thead>
                        <tr>
                            <th style="width: 22%;">بڕی پارە ({{ $order->currency === 'USD' ? 'دۆلار' : 'دینار' }})</th>
                            <th style="width: 48%;">ناوەڕۆک</th>
                            <th style="width: 13%;">ژمارە</th>
                            <th style="width: 17%;">نرخ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($order->items as $line)
                            <tr class="inv-row">
                                <td class="num text-center font-bold text-xs">{{ fmt_num($line->line_total) }}</td>
                                <td style="text-align: center; padding: 2px 4px;">{{ $line->description }}</td>
                                <td class="text-center font-semibold text-xs">{{ $line->has_meter ? fmt_qty($line->meter).' م' : fmt_qty($line->qty) }}</td>
                                <td class="num text-center font-semibold text-xs">{{ fmt_num($line->has_meter ? $line->meter_price : $line->unit_price) }}</td>
                            </tr>
                        @endforeach
                        @for ($i = 0; $i < max(1, 4 - $filledRows); $i++)
                            <tr class="inv-row"><td>&nbsp;</td><td></td><td></td><td></td></tr>
                        @endfor
                        <tr>
                            <td colspan="4" style="padding: 6px 10px; border: 1.5px solid #1e3a5f; background-color: #ffffff;">
                                <div style="display: flex; align-items: center; justify-content: space-between; font-size: 12px; font-weight: 900;">
                                    <span>کۆی گشتی: <span class="num text-sm">{{ fmt_num($order->total) }}</span> {{ $order->currency === 'USD' ? 'دۆلار' : 'دینار' }}</span>
                                    @if ($remaining > 0)
                                        <span class="text-emerald-700">دراو: <span class="num">{{ fmt_num($paid) }}</span></span>
                                        <span class="text-rose-700">ماوە: <span class="num text-sm">{{ fmt_num($remaining) }}</span></span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div style="margin-top: 8px; display: flex; align-items: center; justify-content: space-between; font-size: 11px; font-weight: 800;">
                    <div>{{ $settings['invoice_footer'] ?? 'هەڵە دەگەڕێتەوە بۆ هەردوو لا' }}</div>
                    <div style="border-bottom: 1px dotted #000; width: 110px; text-align: center;">ئیمزای کڕیار</div>
                </div>
            </div>
        </div>
    </div>


    {{-- وێنەکانی دیزاین و داواکاری لە خوارەوەی وەسڵ (بۆ پیشاندان و گەورەکردن) --}}
    @php
        $allItemsWithImages = $order->items->filter(fn ($it) => count($it->allImageUrls()) > 0);
    @endphp
    @if ($allItemsWithImages->isNotEmpty())
        <div class="no-print mx-auto mt-6 max-w-[210mm] bg-white rounded-2xl p-4 border border-slate-200 shadow-xs">
            <div class="flex items-center justify-between gap-2 mb-3 pb-2 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <span class="text-base">🖼️</span>
                    <span class="font-black text-xs text-slate-800">وێنەکانی دیزاین و داواکاری</span>
                </div>
                <span class="text-[11px] font-bold text-blue-700 bg-blue-50 px-2 py-0.5 rounded-full font-mono">
                    {{ $allItemsWithImages->sum(fn ($it) => count($it->allImageUrls())) }} وێنە
                </span>
            </div>

            <div class="space-y-4">
                @foreach ($allItemsWithImages as $item)
                    <div>
                        <div class="text-xs font-bold text-slate-700 mb-2 flex items-center gap-1.5">
                            <span class="size-1.5 rounded-full bg-blue-600"></span>
                            <span>{{ $item->description ?: 'کاڵا' }}</span>
                            @if ($item->has_meter)
                                <span class="text-slate-400 font-normal">({{ fmt_qty($item->meter) }} مەتر)</span>
                            @endif
                        </div>
                        <div class="grid grid-cols-3 sm:grid-cols-4 gap-2.5">
                            @foreach ($item->allImageUrls() as $imgUrl)
                                <a href="{{ $imgUrl }}" target="_blank"
                                   class="group block aspect-4/3 rounded-xl overflow-hidden border border-slate-200 bg-slate-50 hover:border-blue-500 shadow-2xs transition-all relative">
                                    <img src="{{ $imgUrl }}" class="size-full object-cover group-hover:scale-105 transition-transform" alt="{{ $item->description }}">
                                    <div class="absolute inset-0 bg-black/30 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center text-white text-xs font-bold">
                                        🔍 گەورەکردن
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- سکریپتی گۆڕینی دۆخی چاپ و قەبارەکان --}}
    <script>
        function setPrintMode(mode) {
            document.body.className = document.body.className
                .replace(/\bmode-[a-z0-9_-]+\b/g, '')
                .trim() + ' mode-' + mode;

            try {
                localStorage.setItem('hemin_print_mode', mode);
            } catch (e) {}

            const styleEl = document.getElementById('dynamic-page-style');
            if (styleEl) {
                if (mode === 'a5') {
                    styleEl.innerHTML = '@page { size: A5 portrait; margin: 5mm; }';
                } else if (mode === 'a4_dual') {
                    styleEl.innerHTML = '@page { size: A4 portrait; margin: 5mm 8mm; }';
                } else {
                    styleEl.innerHTML = '@page { size: A4 portrait; margin: 8mm 10mm 8mm 10mm; }';
                }
            }

            document.querySelectorAll('.mode-btn').forEach(btn => {
                if (btn.dataset.mode === mode) {
                    btn.className = 'mode-btn !py-1.5 !px-3 rounded-lg transition-all cursor-pointer bg-blue-600 text-white shadow-xs font-bold';
                } else {
                    btn.className = 'mode-btn !py-1.5 !px-3 rounded-lg transition-all cursor-pointer text-slate-700 hover:bg-slate-200 font-bold';
                }
            });
        }

        // دەستنیشانکردنی قەبارەی بنەڕەت (کە بە پێشوەختە ستاندارد A4ـە)
        document.addEventListener('DOMContentLoaded', function () {
            let initialMode = 'a4';
            try {
                const saved = localStorage.getItem('hemin_print_mode');
                if (saved && ['a4', 'a4_dual', 'a5'].includes(saved)) {
                    initialMode = saved;
                }
            } catch (e) {}
            setPrintMode(initialMode);
        });
    </script>
</body>
</html>
