<!DOCTYPE html>
<html lang="ckb" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>پسوولەی کڕین {{ $purchase->invoice_no }} — {{ $settings['company_name'] ?? 'کارگەی ئاسنگەری هێمن' }}</title>
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
        }

        .inv-table {
            width: 100%;
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

        @media print {
            body {
                background: none !important;
                padding: 0 !important;
            }
            .no-print {
                display: none !important;
            }
            .receipt-sheet {
                width: 100% !important;
                min-height: auto !important;
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
                🖨️ چاپکردنی پسوولە
            </button>
            <a href="{{ route('purchases.show', $purchase) }}" class="btn btn-ghost !py-2 text-sm bg-white border border-slate-200">
                گەڕانەوە بۆ پسوولە
            </a>
        </div>
    </div>

    <div class="receipt-sheet">

        {{-- بەشی سەرەوە / سەردێڕ --}}
        <div class="border-b-2 border-blue-900 pb-3">
            <div class="flex items-center justify-between">
                {{-- ژمارەی مۆبایل و شوێن لە دەستە ڕاست --}}
                <div class="text-right text-xs leading-5">
                    <div class="font-bold text-slate-800">
                        مۆبایل:
                        <span class="num font-bold text-slate-900" dir="ltr">{{ $settings['company_phone'] ?? '٠٧٥٠٤٥٦٨٥٥٦' }}</span>
                        -
                        <span class="num font-bold text-slate-900" dir="ltr">{{ $settings['company_phone2'] ?? '٠٧٥٠١٢٠١١١٠' }}</span>
                    </div>
                    <div class="text-[11px] text-slate-600 mt-0.5">
                        {{ $settings['company_address'] ?? 'هەولێر — کارگەی ئاسنگەری هێمن' }}
                    </div>
                </div>

                {{-- ناوی سەرەکی لە ناوەڕاست --}}
                <div class="text-center flex-1 px-4">
                    <h1 class="text-2xl sm:text-3xl font-black text-blue-900 tracking-tight">
                        {{ $settings['company_name'] ?? 'کارگەی ئاسنگەری هێمن' }}
                    </h1>
                    <div class="inline-block mt-1 px-3 py-0.5 rounded-md bg-blue-50 border border-blue-200 text-xs font-bold text-blue-900">
                        پسوولەی کڕینی مەواد و کاڵا
                    </div>
                </div>

                {{-- ژمارەی پسوولە لە چەپ --}}
                <div class="text-left">
                    <div class="inline-block rounded border-2 border-blue-900 bg-blue-50/50 px-3 py-1 text-sm font-bold text-blue-900">
                        No. <span class="num">{{ $purchase->invoice_no }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- زانیاری فرۆشیار و بەروار --}}
        <div class="mt-3.5 mb-2 grid grid-cols-12 gap-2 text-[13px]">
            <div class="col-span-5 flex items-baseline gap-1.5">
                <span class="font-bold text-slate-800 shrink-0">فرۆشیار / کۆمپانیا:</span>
                <span class="dotted-line font-bold text-slate-900 flex-1 px-1">{{ $purchase->supplier?->name }}</span>
            </div>

            <div class="col-span-4 flex items-baseline gap-1.5">
                <span class="font-bold text-slate-800 shrink-0">کۆگا:</span>
                <span class="dotted-line text-slate-800 flex-1 px-1 truncate">
                    {{ $purchase->warehouse?->name ?? 'کۆگای سەرەکی' }}
                </span>
            </div>

            <div class="col-span-3 flex items-baseline gap-1.5">
                <span class="font-bold text-slate-800 shrink-0">بەروار:</span>
                <span class="dotted-line num font-bold text-slate-900 flex-1 px-1 text-center" dir="ltr">
                    {{ fmt_date($purchase->purchase_date) }}
                </span>
            </div>
        </div>

        {{-- خشتەی سەرەکی کاڵاکان --}}
        <table class="inv-table mt-3">
            <thead>
                <tr>
                    <th style="width: 22%;" class="leading-snug">
                        کۆی گشتی<br>
                        <span class="text-[11px] font-normal text-slate-600">
                            {{ $purchase->currency === 'USD' ? 'دۆلار' : 'دینار' }}
                        </span>
                    </th>
                    <th style="width: 18%;" class="leading-snug">
                        نرخی تاک<br>
                        <span class="text-[11px] font-normal text-slate-600">
                            {{ $purchase->currency === 'USD' ? 'دۆلار' : 'دینار' }}
                        </span>
                    </th>
                    <th style="width: 12%;">بڕ / یەکە</th>
                    <th style="text-align: right; padding-right: 12px;">ناوی کاڵا / مەواد</th>
                    <th style="width: 6%;">#</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $filledRows = $purchase->items->count();
                    $minRows = 10;
                @endphp

                @foreach ($purchase->items as $index => $line)
                    <tr style="height: 38px;">
                        {{-- کۆی گشتی دێڕ --}}
                        <td class="num text-center font-bold text-slate-900 text-sm">
                            {{ fmt_num($line->line_total) }}
                        </td>

                        {{-- نرخی تاک --}}
                        <td class="num text-center font-semibold text-slate-800">
                            {{ fmt_num($line->unit_price) }}
                        </td>

                        {{-- بڕ --}}
                        <td class="num text-center font-semibold text-slate-800">
                            {{ fmt_qty($line->qty) }} {{ $line->item?->unit?->name }}
                        </td>

                        {{-- ناوی کاڵا --}}
                        <td>
                            <div class="flex items-center gap-2">
                                @if ($line->imageUrl())
                                    <img src="{{ $line->imageUrl() }}"
                                         class="size-7 rounded object-cover border border-slate-300 shrink-0">
                                @endif
                                <span class="font-bold text-slate-900">{{ $line->item?->name }}</span>
                            </div>
                        </td>

                        {{-- # --}}
                        <td class="num text-center text-slate-400 font-medium">
                            {{ $index + 1 }}
                        </td>
                    </tr>
                @endforeach

                {{-- دێڕی بەتاڵ بۆ پڕکردنەوەی وەسڵەکە --}}
                @for ($i = $filledRows; $i < $minRows; $i++)
                    <tr style="height: 34px;">
                        <td>&nbsp;</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td class="text-center text-slate-300 num text-xs">{{ $i + 1 }}</td>
                    </tr>
                @endfor

                {{-- کۆی گشتی --}}
                <tr class="bg-slate-50/50 font-bold border-t-2 border-[#1e3a5f]">
                    <td class="num text-center text-base font-black text-slate-900 bg-[#f7ede2]">
                        {{ fmt_num($purchase->total) }}
                    </td>
                    <td colspan="4" class="text-left px-3 text-sm font-bold text-slate-900">
                        کۆی گشتی پسوولە:
                    </td>
                </tr>

                {{-- پارەی دراو --}}
                @php
                    $paid = $purchase->paidTotal();
                    $remaining = $purchase->remaining();
                @endphp
                <tr>
                    <td class="num text-center font-bold text-emerald-700 bg-emerald-50/30">
                        {{ fmt_num($paid) }}
                    </td>
                    <td colspan="4" class="text-left px-3 text-xs font-bold text-emerald-800">
                        پارەی دراو (واصلکراو):
                    </td>
                </tr>

                {{-- ماوە / قەرز --}}
                <tr>
                    <td class="num text-center font-black text-base {{ $remaining > 0 ? 'text-rose-700 bg-rose-50/30' : 'text-slate-700' }}">
                        {{ fmt_num($remaining) }}
                    </td>
                    <td colspan="4" class="text-left px-3 text-xs font-bold text-slate-900">
                        ماوە (قەرز):
                    </td>
                </tr>
            </tbody>
        </table>

        @if($purchase->note)
            <div class="mt-3 p-2.5 rounded border border-slate-200 bg-slate-50 text-xs">
                <span class="font-bold text-slate-700">تێبینی:</span>
                <span class="text-slate-850">{{ $purchase->note }}</span>
            </div>
        @endif

        {{-- ئیمزا و پەسەندکردن لە خوارەوە --}}
        <div class="mt-8 pt-4 border-t border-slate-300 grid grid-cols-2 text-center text-xs font-bold text-slate-800">
            <div>
                <span>ئیمزای وەرگر / ژمێریاری:</span>
                <div class="mt-8 text-slate-400 font-normal">................................</div>
            </div>
            <div>
                <span>ئیمزای فرۆشیار / کۆمپانیا:</span>
                <div class="mt-8 text-slate-400 font-normal">................................</div>
            </div>
        </div>

    </div>

</body>
</html>
