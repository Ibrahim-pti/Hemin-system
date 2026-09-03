<!DOCTYPE html>
<html lang="ckb" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>سەنەدی حەقدی {{ $payment->voucher_no }} — {{ $settings['company_name'] ?? 'کارگەی ئاسنگەری هێمن' }}</title>
    @vite(['resources/css/app.css'])
    <style>
        @page {
            size: A5 landscape;
            margin: 8mm;
        }

        body {
            background-color: #f8fafc;
            font-family: var(--font-sans, system-ui, -apple-system, sans-serif);
            color: #0f172a;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .receipt-card {
            width: 195mm;
            max-width: 100%;
            margin: 0 auto;
            background: #ffffff;
            border: 2px solid #1e3a5f;
            border-radius: 12px;
            padding: 20px 26px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.06);
            box-sizing: border-box;
            position: relative;
        }

        .dotted-line {
            border-bottom: 1.5px dotted #94a3b8;
            display: inline-block;
            min-width: 160px;
        }

        @media print {
            .no-print { display: none !important; }
            body { background: #fff; padding: 0; }
            .receipt-card {
                width: 100%;
                border: 2px solid #1e3a5f;
                box-shadow: none;
                border-radius: 0;
                padding: 16px 20px;
            }
        }
    </style>
</head>
<body class="p-4 sm:p-6">

    {{-- دوگمەکانی سەرەوە (تەنها لە شاشە نیشان دەدرێن) --}}
    <div class="no-print mx-auto mb-5 flex max-w-[195mm] items-center justify-between flex-wrap gap-2">
        <div class="flex items-center gap-2">
            <a href="{{ route('payments.index') }}"
               class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold shadow-2xs transition-colors">
                <span>←</span>
                <span>گەڕانەوە بۆ لیستی حەقدی</span>
            </a>
            <a href="{{ route('payments.create') }}"
               class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg border border-emerald-300 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 text-xs font-bold shadow-2xs transition-colors">
                <span>+</span>
                <span>وەرگرتنی حەقدی نوێ</span>
            </a>
        </div>

        <button onclick="window.print()"
                class="inline-flex items-center gap-2 px-5 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-extrabold shadow-sm cursor-pointer transition-colors">
            <span>🖨️</span>
            <span>چاپکردنی سەنەد</span>
        </button>
    </div>

    {{-- پسوولەی فەرمیی حەقدی --}}
    <div class="receipt-card">

        {{-- سەردێڕ و لۆگۆ --}}
        <div class="flex items-start justify-between border-b-2 border-[#1e3a5f] pb-4">
            {{-- تەلەفۆنەکان --}}
            <div class="text-right text-xs leading-5">
                <div class="font-bold text-slate-500 mb-0.5">پەیوەندی:</div>
                <div class="num font-bold text-slate-800" dir="ltr">{{ $settings['company_phone'] ?? '0750 148 4020' }}</div>
                @if(!empty($settings['company_phone2']))
                    <div class="num font-bold text-slate-800" dir="ltr">{{ $settings['company_phone2'] }}</div>
                @endif
            </div>

            {{-- ناوی کارگە و ناونیشانی سەنەد --}}
            <div class="text-center">
                <h1 class="text-2xl font-black text-[#1e3a5f] tracking-tight">
                    {{ $settings['company_name'] ?? 'کارگەی ئاسنگەری هێمن' }}
                </h1>
                <div class="mt-1.5 inline-flex items-center gap-1.5 bg-[#1e3a5f] text-white px-4 py-1 rounded-full text-xs font-bold shadow-2xs">
                    <span>🧾</span>
                    <span>سەنەدی وەرگرتنی پارە (حەقدی موشتەری)</span>
                </div>
            </div>

            {{-- ژمارە و بەروار --}}
            <div class="text-left text-xs leading-5">
                <div>
                    <span class="text-slate-500 font-semibold">ژمارەی سەنەد:</span>
                    <span class="num font-black text-rose-600 text-sm mr-1">{{ $payment->voucher_no }}</span>
                </div>
                <div class="mt-0.5">
                    <span class="text-slate-500 font-semibold">بەروار:</span>
                    <span class="num font-bold text-slate-800 mr-1">{{ fmt_date($payment->paid_at) }}</span>
                </div>
            </div>
        </div>

        {{-- ناوەڕۆکی سەرەکی سەنەدەکە --}}
        <div class="mt-5 space-y-4 text-sm">

            {{-- کڕیار --}}
            <div class="flex items-center gap-2">
                <span class="font-bold text-slate-700 min-w-[130px]">وەرگیرا لە بەڕێز:</span>
                <span class="font-extrabold text-slate-900 text-base bg-slate-50 border border-slate-200 px-3 py-1 rounded-lg">
                    {{ $payment->party_label }}
                </span>
            </div>

            {{-- بڕی پارە و نووسین --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 items-center bg-emerald-50/50 border border-emerald-200 p-3.5 rounded-xl">
                <div class="flex items-center gap-3">
                    <span class="font-bold text-emerald-900 text-sm">بڕی پارەی وەرگیراو:</span>
                    <span class="num px-3.5 py-1.5 rounded-lg bg-white border border-emerald-300 text-xl font-black text-emerald-700 shadow-2xs">
                        {{ fmt_money($payment->amount, $payment->currency) }}
                    </span>
                </div>

                <div class="text-xs font-bold text-slate-700">
                    <span class="text-slate-500">بە نووسین:</span>
                    <span class="text-slate-900 font-extrabold mr-1">{{ $payment->amount_in_words }}</span>
                </div>
            </div>

            {{-- ئەگەر بە دۆلار بێت --}}
            @if ($payment->currency === 'USD')
                <div class="text-xs bg-amber-50 border border-amber-200 text-amber-900 px-3 py-1.5 rounded-lg flex items-center justify-between">
                    <span>نرخی ئاڵوگۆڕ: <strong class="num">{{ fmt_num($payment->exchange_rate) }}</strong></span>
                    <span>هاوتای بە دینار: <strong class="num text-emerald-700">{{ fmt_money($payment->amount_iqd) }}</strong></span>
                </div>
            @endif

            {{-- ئەگەر پەیوەست بێت بە وەسڵێکی دیاریکراو --}}
            @if ($payment->order)
                <div class="flex items-center gap-2 text-xs">
                    <span class="font-bold text-slate-600 min-w-[130px]">لەسەر حسابی وەسڵی:</span>
                    <span class="font-bold text-blue-800 bg-blue-50 border border-blue-200 px-2.5 py-0.5 rounded-md">
                        وەسڵی ژمارە #{{ $payment->order->invoice_no }}
                    </span>
                </div>
            @endif

            {{-- تێبینی --}}
            @if ($payment->note)
                <div class="flex items-start gap-2 text-xs">
                    <span class="font-bold text-slate-600 min-w-[130px]">تێبینی:</span>
                    <span class="text-slate-700 font-medium">{{ $payment->note }}</span>
                </div>
            @endif

            {{-- باڵانسی ماوە دوای ئەم حەقدییە --}}
            @if ($balance !== null)
                <div class="flex items-center justify-between bg-slate-50 border border-slate-200 px-3.5 py-2 rounded-lg text-xs font-bold">
                    <span class="text-slate-600">باڵانسی ماوەی کڕیار دوای ئەم حەقدییە:</span>
                    <span class="num text-sm font-black {{ $balance > 0 ? 'text-rose-600' : 'text-emerald-700' }}">
                        {{ fmt_money($balance) }}
                    </span>
                </div>
            @endif

        </div>

        {{-- بەشی ئیمزاکان --}}
        <div class="mt-8 pt-4 border-t border-slate-200 grid grid-cols-3 gap-4 items-end text-xs">
            <div>
                <div class="text-slate-600 font-bold mb-5">ئیمزای موشتەری (پارەدەر):</div>
                <div class="dotted-line w-full"></div>
            </div>

            <div class="text-center">
                <div class="text-slate-400 font-semibold text-[11px]">
                    تۆمارکەر: <strong class="text-slate-700">{{ $payment->user?->name ?? '—' }}</strong>
                </div>
            </div>

            <div class="text-left">
                <div class="text-slate-600 font-bold mb-5">ئیمزای کارگە (پارەوەرگر):</div>
                <div class="dotted-line w-full"></div>
            </div>
        </div>

        {{-- ناونیشانی کارگە لە خوارەوە --}}
        <div class="mt-4 text-center text-[10px] text-slate-400 font-medium">
            {{ $settings['company_address'] ?? 'هەولێر — کارگەی ئاسنگەری هێمن' }}
        </div>

    </div>

</body>
</html>
