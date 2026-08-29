@extends('layouts.app')
@section('title', 'پسوولەی کڕین ' . $purchase->invoice_no)

@section('content')
<div class="space-y-4 sm:space-y-6">

    {{-- ١. هێڵی سەرەوە: ناونیشان و دوگمەکان --}}
    <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-xs flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div class="flex items-center gap-3.5">
            <div class="size-12 rounded-2xl bg-linear-to-br from-teal-500 to-emerald-600 text-white flex items-center justify-center text-2xl shadow-md shadow-emerald-500/20 shrink-0">
                🛒
            </div>
            <div>
                <div class="flex items-center gap-2 flex-wrap">
                    <h1 class="text-lg sm:text-xl font-black text-slate-900">
                        پسوولەی کڕینی <span class="font-mono text-teal-700">#{{ $purchase->invoice_no }}</span>
                    </h1>
                    @if ($purchase->status === 'confirmed')
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                            پەسەندکراو ✔️
                        </span>
                    @else
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200">
                            ڕەشنووس ⏳
                        </span>
                    @endif
                </div>
                <p class="text-xs text-slate-500 mt-1 font-medium">
                    فرۆشیار: <b class="text-slate-800">{{ $purchase->supplier?->name ?: 'نەناسراو' }}</b>
                    • بەروار: <span class="font-mono font-bold text-slate-700">{{ fmt_date($purchase->purchase_date) }}</span>
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            @if ($purchase->status === 'draft')
                <button type="submit" form="confirm-purchase"
                        class="px-4 py-2 rounded-xl text-xs font-black bg-emerald-600 hover:bg-emerald-700 text-white shadow-xs inline-flex items-center gap-1.5 transition-all cursor-pointer">
                    <span>✔️</span>
                    <span>پەسەندکردنی پسوولە</span>
                </button>
                <a href="{{ route('purchases.edit', $purchase) }}"
                   class="px-4 py-2 rounded-xl text-xs font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-300 inline-flex items-center gap-1.5 transition-all">
                    <span>✏️</span>
                    <span>دەستکاری</span>
                </a>
            @else
                <a href="{{ route('payments.create', ['type' => 'out', 'supplier' => $purchase->supplier_id, 'purchase' => $purchase->id]) }}"
                   class="px-4 py-2 rounded-xl text-xs font-black bg-emerald-600 hover:bg-emerald-700 text-white shadow-xs inline-flex items-center gap-1.5 transition-all cursor-pointer">
                    <span>💳</span>
                    <span>تۆمارکردنی حەقدی</span>
                </a>
            @endif

            <a href="{{ route('purchases.index') }}"
               class="px-4 py-2 rounded-xl text-xs font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 inline-flex items-center gap-1.5 transition-all">
                <span>←</span>
                <span>لیستی کڕینەکان</span>
            </a>
        </div>
    </div>

    {{-- ٢. ناوەڕۆک: خشتەی کاڵاکان و کارتی حیسابات --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">
        {{-- خشتەی کاڵاکان --}}
        <div class="lg:col-span-2 space-y-4">
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
                <div class="p-4 border-b border-slate-100 bg-slate-50/60 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="text-base">📦</span>
                        <h3 class="font-black text-sm text-slate-800">کاڵا و مەوادە کڕدراوەکان</h3>
                    </div>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-200/80 text-slate-700 font-mono">
                        {{ $purchase->items->count() }} بابەت
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-right text-xs">
                        <thead class="bg-slate-50 text-slate-600 border-b border-slate-200 font-black">
                            <tr>
                                <th class="p-3.5">ناوی کاڵا / مەواد</th>
                                <th class="p-3.5 text-center w-28">بڕ / یەکە</th>
                                <th class="p-3.5 text-left w-36">نرخی یەکە</th>
                                <th class="p-3.5 text-left w-36">کۆی گشتی</th>
                                <th class="p-3.5">تێبینی</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($purchase->items as $line)
                                <tr class="hover:bg-slate-50/80 transition-colors">
                                    <td class="p-3.5">
                                        <div class="flex items-center gap-2.5">
                                            @if ($line->imageUrl())
                                                <img src="{{ $line->imageUrl() }}"
                                                     class="size-9 rounded-lg object-cover border border-slate-200 shrink-0 cursor-pointer hover:scale-110 transition-transform"
                                                     onclick="window.open(this.src, '_blank')">
                                            @endif
                                            <span class="font-black text-slate-900">{{ $line->item?->name }}</span>
                                        </div>
                                    </td>
                                    <td class="p-3.5 text-center font-mono font-bold text-slate-800">
                                        {{ fmt_qty($line->qty) }} {{ $line->item?->unit?->name ?: 'دانە' }}
                                    </td>
                                    <td class="p-3.5 text-left font-mono font-bold text-slate-700">
                                        {{ fmt_money($line->unit_price, $purchase->currency) }}
                                    </td>
                                    <td class="p-3.5 text-left font-mono font-black text-slate-900">
                                        {{ fmt_money($line->line_total, $purchase->currency) }}
                                    </td>
                                    <td class="p-3.5 text-slate-500 font-medium">
                                        {{ $line->note ?: '—' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- خشتەی حەقدییەکان --}}
            @if ($purchase->payments->isNotEmpty())
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
                    <div class="p-4 border-b border-slate-100 bg-slate-50/60 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="text-base">💳</span>
                            <h3 class="font-black text-sm text-slate-800">تۆماری حەقدی و پارەدانەکان</h3>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-right text-xs">
                            <thead class="bg-slate-50 text-slate-600 border-b border-slate-200 font-black">
                                <tr>
                                    <th class="p-3.5">ژمارەی وەسڵ</th>
                                    <th class="p-3.5">بەروار</th>
                                    <th class="p-3.5 text-left">بڕی پارە</th>
                                    <th class="p-3.5 text-center w-20">چاپ</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($purchase->payments as $payment)
                                    <tr class="hover:bg-slate-50/80 transition-colors">
                                        <td class="p-3.5 font-mono font-bold text-slate-800">{{ $payment->voucher_no }}</td>
                                        <td class="p-3.5 font-mono text-slate-600">{{ fmt_date($payment->paid_at) }}</td>
                                        <td class="p-3.5 text-left font-mono font-black text-emerald-700">{{ fmt_money($payment->amount, $payment->currency) }}</td>
                                        <td class="p-3.5 text-center">
                                            <a href="{{ route('payments.print', $payment) }}" class="text-xs font-bold text-blue-600 hover:underline">
                                                🖨️ چاپ
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>

        {{-- کارتی زانیاری فرۆشیار و پوختەی حیسابات --}}
        <div class="space-y-4">
            {{-- زانیاری فرۆشیار و کۆگا --}}
            <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-xs space-y-3 text-xs">
                <h3 class="font-black text-slate-800 text-sm border-b border-slate-100 pb-2">زانیاری لایەنەکان</h3>
                
                <div class="flex items-center justify-between">
                    <span class="text-slate-500 font-medium">فرۆشیار:</span>
                    <span class="font-black text-slate-900">{{ $purchase->supplier?->name ?: 'نەناسراو' }}</span>
                </div>

                <div class="flex items-center justify-between">
                    <span class="text-slate-500 font-medium">کۆگا:</span>
                    <span class="font-bold text-slate-800">{{ $purchase->warehouse?->name ?: 'کۆگای سەرەکی' }}</span>
                </div>

                <div class="flex items-center justify-between">
                    <span class="text-slate-500 font-medium">بەرواری کڕین:</span>
                    <span class="font-mono font-bold text-slate-800">{{ fmt_date($purchase->purchase_date) }}</span>
                </div>

                @if($purchase->note)
                    <div class="pt-2 border-t border-slate-100">
                        <span class="text-slate-400 block mb-1">تێبینی:</span>
                        <span class="text-slate-700 italic">{{ $purchase->note }}</span>
                    </div>
                @endif
            </div>

            {{-- پوختەی دارایی --}}
            <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-xs space-y-3 text-xs">
                <h3 class="font-black text-slate-800 text-sm border-b border-slate-100 pb-2">پوختەی دارایی پسوولە</h3>

                <div class="flex items-center justify-between text-slate-600">
                    <span>کۆی دێڕەکان:</span>
                    <span class="font-mono font-bold text-slate-800">{{ fmt_money($purchase->subtotal, $purchase->currency) }}</span>
                </div>

                @if($purchase->discount_amount > 0)
                    <div class="flex items-center justify-between text-slate-600">
                        <span>داشکاندن:</span>
                        <span class="font-mono font-bold text-amber-600">-{{ fmt_money($purchase->discount_amount, $purchase->currency) }}</span>
                    </div>
                @endif

                <div class="flex items-center justify-between font-black text-sm border-t border-slate-100 pt-2 text-slate-900">
                    <span>کۆی گشتی:</span>
                    <span class="font-mono text-base text-slate-900">{{ fmt_money($purchase->total, $purchase->currency) }}</span>
                </div>

                <div class="flex items-center justify-between text-slate-600">
                    <span>پارەی دراو:</span>
                    <span class="font-mono font-bold text-emerald-700">{{ fmt_money($purchase->paidTotal(), $purchase->currency) }}</span>
                </div>

                @php $rem = $purchase->remaining(); @endphp
                <div class="flex items-center justify-between font-black text-xs border-t border-slate-100 pt-2">
                    <span class="{{ $rem > 0 ? 'text-rose-700' : 'text-slate-600' }}">ماوە (قەرز):</span>
                    <span class="font-mono font-black text-sm {{ $rem > 0 ? 'text-rose-600 bg-rose-50 px-2 py-0.5 rounded-md border border-rose-100' : 'text-emerald-700' }}">
                        {{ fmt_money($rem, $purchase->currency) }}
                    </span>
                </div>
            </div>

            @if ($purchase->status === 'confirmed')
                <form method="POST" action="{{ route('purchases.unconfirm', $purchase) }}"
                      onsubmit="return confirm('جوڵەکانی مەخزەن دەسڕدرێنەوە. بەردەوام بم؟')">
                    @csrf
                    <button class="w-full py-2.5 rounded-xl text-xs font-bold bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 transition-all cursor-pointer">
                        هەڵوەشاندنەوەی پەسەندکردن
                    </button>
                </form>
            @endif
        </div>
    </div>

</div>

@if ($purchase->status === 'draft')
    <form id="confirm-purchase" method="POST" action="{{ route('purchases.confirm', $purchase) }}" class="hidden">
        @csrf
    </form>
@endif

@endsection
