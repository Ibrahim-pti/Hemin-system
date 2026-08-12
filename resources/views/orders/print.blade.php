<!DOCTYPE html>
<html lang="ckb" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>وەسڵی ژمارە {{ $order->invoice_no }}</title>
    @vite(['resources/css/app.css'])
    <style>
        /* وەسڵ — وێنەی دەفتەرە چاپکراوەکەی کارگە. */
        .sheet { width: 190mm; margin: 0 auto; background: #fff; }
        .inv-table { width: 100%; border-collapse: collapse; }
        .inv-table th, .inv-table td { border: 1px solid #1e3a5f; padding: 6px 8px; }
        .inv-table th { background: #f6ece4; font-weight: 600; text-align: center; }
        .dotted { border-bottom: 1px dotted #64748b; display: inline-block; min-width: 120px; }

        @media print {
            .no-print { display: none !important; }
            .sheet { width: auto; }
            @page { size: A4; margin: 8mm; }
        }
    </style>
</head>
<body class="bg-[--color-canvas] p-4">

    {{-- تووڵامرازی چاپ --}}
    <div class="no-print mx-auto mb-4 flex max-w-[190mm] gap-2">
        <button onclick="window.print()" class="btn btn-primary">چاپکردن</button>
        <a href="{{ route('orders.show', $order) }}" class="btn btn-ghost">گەڕانەوە</a>
    </div>

    <div class="sheet border border-[--color-line] p-6">

        {{-- سەردێڕ --}}
        <div class="flex items-start justify-between border-b-2 border-[#b91c1c] pb-3">
            <div class="text-xs leading-6">
                <div>ژ. تەلەفۆن: <span class="num" dir="ltr">{{ $settings['company_phone'] ?? '' }}</span></div>
                <div><span class="num" dir="ltr">{{ $settings['company_phone2'] ?? '' }}</span></div>
            </div>

            <div class="text-center">
                <h1 class="text-2xl font-bold text-[#b91c1c]">{{ $settings['company_name'] ?? 'کارگەی ئاسنگەری هێمن' }}</h1>
                <p class="mt-1 text-xs">{{ $settings['company_tagline'] ?? '' }}</p>
            </div>

            <div class="text-left text-xs leading-6">
                <div class="inline-block rounded border border-[#b91c1c] px-2 py-1 font-bold text-[#b91c1c]">
                    ژمارە: <span class="num">{{ $order->invoice_no }}</span>
                </div>
            </div>
        </div>

        <p class="mt-1 text-center text-xs">{{ $settings['company_address'] ?? '' }}</p>

        {{-- زانیاری کڕیار --}}
        <div class="mt-4 grid grid-cols-3 gap-3 text-sm">
            <div>بەڕێز: <span class="dotted font-medium">{{ $order->customer->name }}</span></div>
            <div>ناونیشان: <span class="dotted">{{ $order->address_snapshot ?? $order->customer->address }}</span></div>
            <div>بەروار: <span class="dotted num">{{ fmt_date($order->order_date) }}</span></div>
        </div>

        {{-- خشتەی ناوەڕۆک — هەمان ڕیزبەندی دەفتەرەکە --}}
        <table class="inv-table mt-3 text-sm">
            <thead>
                <tr>
                    <th style="width: 18%">بڕی پارە<br><span class="text-xs font-normal">دینار</span></th>
                    <th>ناوەڕۆک</th>
                    <th style="width: 12%">ژمارە</th>
                    <th style="width: 18%">نرخ<br><span class="text-xs font-normal">دینار</span></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($order->items as $line)
                    <tr>
                        <td class="num text-center font-medium">{{ fmt_num($line->line_total) }}</td>
                        <td>
                            {{ $line->description }}
                            @if ($line->pricing_mode !== 'count')
                                <span class="num text-xs text-[--color-ink-soft]">
                                    ({{ $line->measurement_label }} = {{ fmt_qty($line->computed_qty) }} {{ $line->mode_unit }})
                                </span>
                            @endif
                            @if ($line->note)
                                <span class="text-xs text-[--color-ink-soft]">— {{ $line->note }}</span>
                            @endif
                        </td>
                        <td class="num text-center">{{ fmt_qty($line->qty) }}</td>
                        <td class="num text-center">{{ fmt_num($line->unit_price) }}</td>
                    </tr>
                @endforeach

                {{-- دێڕی بەتاڵ تا خشتەکە وەک دەفتەرەکە پڕ بێت --}}
                @for ($i = $order->items->count(); $i < 10; $i++)
                    <tr><td>&nbsp;</td><td></td><td></td><td></td></tr>
                @endfor
            </tbody>
        </table>

        {{-- کۆکان --}}
        <table class="inv-table mt-0 text-sm">
            <tbody>
                <tr>
                    <td class="num text-center font-bold" style="width: 18%">{{ fmt_num($order->subtotal) }}</td>
                    <td class="text-left font-bold">کۆی گشتی</td>
                </tr>

                @if ($order->discount_amount > 0)
                    <tr>
                        <td class="num text-center">{{ fmt_num($order->discount_amount) }}</td>
                        <td class="text-left">داشکاندن {{ $order->discount_percent > 0 ? '('.fmt_num($order->discount_percent, 2).'٪)' : '' }}</td>
                    </tr>
                    <tr>
                        <td class="num text-center font-bold">{{ fmt_num($order->total) }}</td>
                        <td class="text-left font-bold">دوای داشکاندن</td>
                    </tr>
                @endif

                @php $paid = $order->paidAmount(); @endphp
                @if ($paid > 0)
                    <tr>
                        <td class="num text-center">{{ fmt_num($paid) }}</td>
                        <td class="text-left">پێشەکی / دراوە</td>
                    </tr>
                    <tr>
                        <td class="num text-center font-bold">{{ fmt_num($order->remaining()) }}</td>
                        <td class="text-left font-bold">ماوە</td>
                    </tr>
                @endif
            </tbody>
        </table>

        {{-- بڕی پارە بە نووسین --}}
        <p class="mt-3 text-sm">
            بە نووسین:
            <span class="font-medium">{{ \App\Support\KurdishNumber::spell((int) round((float) $order->total)) }}
            {{ $order->currency === 'USD' ? 'دۆلار' : 'دینار' }}</span>
        </p>

        {{-- ژێرەوە --}}
        <div class="mt-6 flex items-end justify-between text-sm">
            <div class="font-medium text-[#b91c1c]">{{ $settings['invoice_footer'] ?? 'هەڵە دەگەڕێتەوە بۆ هەردوو لا' }}</div>
            <div>ئیمزا: <span class="dotted">&nbsp;</span></div>
        </div>
    </div>

</body>
</html>
