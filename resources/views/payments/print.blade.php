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
    <title>سەنەدی حەقدی {{ $payment->voucher_no }} — {{ $settings['company_name'] ?? 'کارگەی ئاسنگەری هێمن' }}</title>
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
            padding: 4px 6px;
            font-size: 11.5px;
        }

        .inv-table th {
            background-color: #fde8d7 !important;
            font-weight: 800;
            color: #1e3a5f;
            text-align: center;
            padding: 5px 4px;
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

    {{-- دوگمەکانی سەرەوە بۆ چاپ و بەڕێوەبردن --}}
    <div class="no-print mx-auto mb-3 flex max-w-[148mm] items-center justify-between gap-2">
        <div class="flex items-center gap-2">
            <button onclick="window.print()" class="btn btn-primary !py-1.5 !px-4 text-xs shadow-sm cursor-pointer">
                🖨️ چاپکردنی سەنەد
            </button>
            <a href="{{ route('payments.create') }}" class="btn btn-ghost !py-1.5 !px-3 text-xs bg-white border border-slate-200 hover:bg-slate-50 cursor-pointer">
                + حەقدی نوێ
            </a>
            <a href="{{ route('payments.index') }}" class="btn btn-ghost !py-1.5 !px-3 text-xs bg-white border border-slate-200 hover:bg-slate-50 cursor-pointer">
                گەڕانەوە
            </a>
        </div>
    </div>

    @if (session('ok'))
        <div class="no-print mx-auto mb-3 max-w-[148mm] bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold rounded-xl px-4 py-2 flex items-center justify-between">
            <span>✓ {{ session('ok') }}</span>
        </div>
    @endif

    <div class="receipt-sheet">
        <div>
            {{-- بەشی سەرەوە / سەردێڕ ڕێک وەک وەسڵی فرۆشتن --}}
            <div>
                {{-- ناوی کارگە بە سووری تۆخ --}}
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

                {{-- باڕی ناونیشان و ژمارەی سەنەد --}}
                <div style="margin-top: 6px; display: flex; align-items: center; gap: 6px;">
                    <div style="border: 1px solid #b91c1c; background-color: #ffffff; color: #b91c1c; font-weight: 900; font-size: 11.5px; padding: 2px 8px; border-radius: 2px; flex-shrink: 0;">
                        سەنەدی حەقدی: <span class="num">{{ $payment->voucher_no }}</span>
                    </div>
                    <div style="flex: 1; background-color: #edf2f7; border-top: 1px solid #1e3a5f; border-bottom: 1px solid #1e3a5f; padding: 2px 10px; text-align: center; border-radius: 2px;">
                        <div style="font-weight: 800; font-size: 11px; color: #1e3a5f;">
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
                        <span style="color: #0f172a; font-weight: 800;">{{ $payment->party_label }}</span>
                    </div>
                </div>

                {{-- تەلەفۆن --}}
                @if ($payment->party?->phone)
                    <div style="display: flex; align-items: baseline; gap: 4px; flex-shrink: 0;">
                        <span style="color: #0f172a; flex-shrink: 0; user-select: none;">تەلەفۆن :</span>
                        <div style="border-bottom: 1.5px dotted #000000; padding: 0 4px;">
                            <span class="num" style="color: #1e293b; font-weight: 700;" dir="ltr">{{ $payment->party->phone }}</span>
                        </div>
                    </div>
                @endif

                {{-- بەروار --}}
                <div style="display: flex; align-items: baseline; gap: 4px; flex-shrink: 0; width: 130px;">
                    <span style="color: #0f172a; flex-shrink: 0; user-select: none;">بەروار :</span>
                    <div style="flex: 1; border-bottom: 1.5px dotted #000000; text-align: center; padding: 0 4px;">
                        <span class="num" style="color: #0f172a; font-weight: 800;" dir="ltr">
                            {{ fmt_date($payment->paid_at) }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- خشتەی سەرەکی سەنەد --}}
            <table class="inv-table mt-1">
                <colgroup>
                    <col style="width: 25%;">
                    <col style="width: 35%;">
                    <col style="width: 20%;">
                    <col style="width: 20%;">
                </colgroup>
                <thead>
                    <tr>
                        <th class="leading-tight">
                            بڕی پارەی وەرگیراو<br>
                            <span class="text-[10px] font-bold text-slate-700">
                                {{ $payment->currency === 'USD' ? 'دۆلار' : 'دینار' }}
                            </span>
                        </th>
                        <th style="text-align: center;">ناوەڕۆک و مەبەست</th>
                        <th>وەسڵی پەیوەندیدار</th>
                        <th class="leading-tight">تێبینی</th>
                    </tr>
                </thead>
                <tbody>
                    <tr style="height: 38px;">
                        {{-- بڕی پارە --}}
                        <td class="num text-center font-black text-emerald-800 text-sm bg-emerald-50/40">
                            {{ fmt_num($payment->amount) }}
                            <span style="font-size: 10px; font-weight: 700;">{{ $payment->currency === 'USD' ? '$' : 'د.ع' }}</span>
                        </td>

                        {{-- ناوەڕۆک --}}
                        <td style="text-align: center; padding: 4px 6px;">
                            <span class="font-extrabold text-slate-900">وەرگرتنی حەقدی موشتەری</span>
                        </td>

                        {{-- وەسڵ --}}
                        <td class="text-center font-bold text-blue-800">
                            @if ($payment->order)
                                وەسڵی #{{ $payment->order->invoice_no }}
                            @else
                                حسابی گشتی کڕیار
                            @endif
                        </td>

                        {{-- تێبینی --}}
                        <td class="text-center text-xs text-slate-600">
                            {{ $payment->note ?: '—' }}
                        </td>
                    </tr>

                    {{-- ئەگەر بە دۆلار بێت، دێڕی نرخی دۆلار --}}
                    @if ($payment->currency === 'USD')
                        <tr style="height: 28px; background-color: #fffbeb;">
                            <td class="num text-center font-bold text-amber-900 text-xs">
                                {{ fmt_money($payment->amount_iqd) }}
                            </td>
                            <td colspan="3" style="padding: 2px 8px; font-size: 10.5px; font-weight: 700; color: #78350f;">
                                بە نرخی ١٠٠$ = {{ fmt_num($payment->exchange_rate) }} د.ع (کۆی گشتی بە دینار)
                            </td>
                        </tr>
                    @endif

                    {{-- دێڕی بەتاڵ بۆ شێوازی دەفتەر --}}
                    @for ($i = 0; $i < ($payment->currency === 'USD' ? 6 : 7); $i++)
                        <tr style="height: 24px;">
                            <td>&nbsp;</td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                    @endfor

                    {{-- بەشی خوارەوە: کۆی گشتی و باڵانسی ماوە --}}
                    <tr>
                        <td colspan="4" style="padding: 8px 12px; border: 1.5px solid #1e3a5f; background-color: #ffffff;">
                            <div style="display: flex; flex-direction: column; gap: 8px;">
                                {{-- کۆی پارەی وەرگیراو --}}
                                <div style="display: flex; align-items: center; justify-content: space-between; font-size: 12px; line-height: 1;">
                                    <span style="font-weight: 900; shrink: 0; color: #047857; transform: translateY(2px);">کۆی پارەی وەرگیراو (دراو)</span>
                                    <div style="flex: 1; margin: 0 8px; border-bottom: 1.5px dotted #000000; text-align: center; line-height: 1;">
                                        <span class="num" style="font-weight: 900; font-size: 13px; color: #047857; display: inline-block; transform: translateY(2px);">
                                            {{ fmt_num($payment->amount) }}
                                        </span>
                                    </div>
                                    <span style="font-weight: 900; font-size: 11px; shrink: 0; color: #047857; transform: translateY(2px);">
                                        {{ $payment->currency === 'USD' ? 'دۆلار' : 'دینار' }}
                                    </span>
                                </div>

                                {{-- باڵانسی ماوەی کڕیار دوای ئەم حەقدییە --}}
                                @if ($balance !== null)
                                    <div style="display: flex; align-items: center; justify-content: space-between; font-size: 12px; line-height: 1;">
                                        <span style="font-weight: 900; shrink: 0; color: {{ $balance > 0 ? '#b91c1c' : '#047857' }}; transform: translateY(2px);">
                                            باڵانسی ماوەی کڕیار دوای ئەم حەقدییە
                                        </span>
                                        <div style="flex: 1; margin: 0 8px; border-bottom: 1.5px dotted #000000; text-align: center; line-height: 1;">
                                            <span class="num" style="font-weight: 900; font-size: 13px; color: {{ $balance > 0 ? '#b91c1c' : '#047857' }}; display: inline-block; transform: translateY(2px);">
                                                {{ fmt_num($balance) }}
                                            </span>
                                        </div>
                                        <span style="font-weight: 900; font-size: 11px; shrink: 0; color: #0f172a; transform: translateY(2px);">
                                            دینار {{ $balance > 0 ? '(قەرزە)' : '(پاکتاو)' }}
                                        </span>
                                    </div>
                                @endif
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>

            {{-- ژێرەوەی سەنەد: هەڵە دەگەڕێتەوە لە دەستەڕاست و ئیمزا لە لای چەپ --}}
            <div class="mt-4 mb-2 flex items-center justify-between text-xs px-2">
                <div class="font-bold text-slate-900 text-xs">
                    {{ $settings['invoice_footer'] ?? 'هەڵە دەگەڕێتەوە بۆ هەردوو لا' }}
                </div>

                <div class="flex items-center gap-12 font-bold text-slate-900 text-xs">
                    <div>ئیمزای موشتەری (پارەدەر)</div>
                    <div>ئیمزای کارگە (پارەوەرگر)</div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
