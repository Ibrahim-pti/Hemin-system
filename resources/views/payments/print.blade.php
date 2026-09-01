<!DOCTYPE html>
<html lang="ckb" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>حەقدی {{ $payment->voucher_no }}</title>
    @vite(['resources/css/app.css'])
    <style>
        .sheet { width: 190mm; margin: 0 auto; background: #fff; }
        .dotted { border-bottom: 1px dotted #64748b; display: inline-block; min-width: 140px; }

        @media print {
            .no-print { display: none !important; }
            .sheet { width: auto; }
            @page { size: A5 landscape; margin: 8mm; }
        }
    </style>
</head>
<body class="bg-[--color-canvas] p-4">

    <div class="no-print mx-auto mb-4 flex max-w-[190mm] gap-2">
        <button onclick="window.print()" class="btn btn-primary">چاپکردن</button>
        <a href="{{ route('payments.index') }}" class="btn btn-ghost">لیستی حەقدی</a>
    </div>

    <div class="sheet border-2 border-[#1d4ed8] p-6">

        {{-- سەردێڕ --}}
        <div class="flex items-start justify-between border-b border-[--color-line] pb-3">
            <div class="text-xs leading-6">
                <div class="num" dir="ltr">{{ $settings['company_phone'] ?? '' }}</div>
                <div class="num" dir="ltr">{{ $settings['company_phone2'] ?? '' }}</div>
            </div>

            <div class="text-center">
                <h1 class="text-xl font-bold text-[#b91c1c]">{{ $settings['company_name'] ?? 'کارگەی ئاسنگەری هێمن' }}</h1>
                <p class="mt-1 inline-block rounded border border-[#1d4ed8] px-3 py-1 text-sm font-semibold text-[#1d4ed8]">
                    سەنەدی حەقدی وەرگیراو لە موشتەری
                </p>
            </div>

            <div class="text-left text-xs leading-6">
                <div>ژمارە: <span class="num font-bold">{{ $payment->voucher_no }}</span></div>
                <div>بەروار: <span class="num">{{ fmt_date($payment->paid_at) }}</span></div>
            </div>
        </div>

        {{-- ناوەڕۆک --}}
        <div class="mt-6 space-y-4 text-base">
            <div>
                وەرگیرا لە بەڕێز:
                <span class="dotted font-semibold">{{ $payment->party_label }}</span>
            </div>

            <div class="flex items-center gap-3">
                <span>بڕی پارە:</span>
                <span class="num rounded border border-[#1d4ed8] bg-[#eff4ff] px-4 py-2 text-xl font-bold text-emerald-800">
                    {{ fmt_money($payment->amount, $payment->currency) }}
                </span>
            </div>

            <div>
                بە نووسین: <span class="font-medium">{{ $payment->amount_in_words }}</span>
            </div>

            @if ($payment->currency === 'USD')
                <div class="text-sm text-[--color-ink-soft]">
                    بە نرخی <span class="num">{{ fmt_num($payment->exchange_rate) }}</span> =
                    <span class="num font-bold text-slate-800">{{ fmt_money($payment->amount_iqd) }}</span>
                </div>
            @endif

            @if ($payment->order)
                <div>
                    لەسەر حسابی وەسڵی ژمارە:
                    <span class="num font-semibold text-blue-700">#{{ $payment->order->invoice_no }}</span>
                </div>
            @endif

            @if ($payment->note)
                <div>تێبینی: <span class="text-[--color-ink-soft]">{{ $payment->note }}</span></div>
            @endif

            @if ($balance !== null)
                <div class="rounded border border-[--color-line] bg-[--color-canvas] px-4 py-2">
                    باڵانسی ماوەی کڕیار دوای ئەم حەقدییە:
                    <span class="num font-bold {{ $balance > 0 ? 'text-[#dc2626]' : 'text-[#16a34a]' }}">
                        {{ fmt_money($balance) }}
                    </span>
                </div>
            @endif
        </div>

        {{-- ئیمزا --}}
        <div class="mt-10 flex items-end justify-between text-sm">
            <div>ئیمزای موشتەری (پارەدەر): <span class="dotted">&nbsp;</span></div>
            <div>ئیمزای کارگە (پارەوەرگر): <span class="dotted">&nbsp;</span></div>
            <div class="text-[--color-ink-soft]">
                تۆمارکەر: {{ $payment->user?->name ?? '—' }}
            </div>
        </div>

        <p class="mt-6 text-center text-xs text-[--color-ink-soft]">
            {{ $settings['company_address'] ?? '' }}
        </p>
    </div>

</body>
</html>
