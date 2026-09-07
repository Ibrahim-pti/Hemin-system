@extends('layouts.app')
@section('title', 'پسوولەی کڕین ' . $purchase->invoice_no)

@section('content')
<div class="space-y-4 sm:space-y-6"
     x-data="{
         payModal: false,
         payForm: {
             amount: '{{ number_format((float)$purchase->remaining()) }}',
             cash_box_id: '{{ $cashBoxes->first()?->id ?? '' }}',
             paid_at: '{{ now()->toDateString() }}',
             note: 'پارەدانی قەرزی پسوولەی #{{ $purchase->invoice_no }}'
         },
         formatAmount(e) {
             let clean = e.target.value.replace(/[^0-9.]/g, '');
             let parts = clean.split('.');
             if (parts.length > 2) parts = [parts[0], parts.slice(1).join('')];
             let int = parts[0] ? parseInt(parts[0], 10).toLocaleString('en-US') : '';
             let dec = parts.length > 1 ? '.' + parts[1] : '';
             e.target.value = int ? int + dec : '';
             this.payForm.amount = e.target.value;
         }
     }">

    {{-- ١. هێڵی سەرەوە: ناونیشان و دوگمەکان --}}
    <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-xs flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div class="flex items-center gap-3.5">
            @if ($purchase->imageUrl())
                <div class="relative size-14 rounded-2xl overflow-hidden border-2 border-teal-500 shadow-md shrink-0 cursor-pointer group"
                     onclick="window.open('{{ $purchase->imageUrl() }}', '_blank')" title="کرتە بکە بۆ بینینی تەواوی وێنەکە">
                    <img src="{{ $purchase->imageUrl() }}" class="size-full object-cover group-hover:scale-110 transition-transform">
                    <span class="absolute bottom-0 inset-x-0 bg-black/60 text-white text-[9px] text-center font-bold py-0.5">وێنە</span>
                </div>
            @else
                <div class="size-12 rounded-2xl bg-linear-to-br from-teal-500 to-emerald-600 text-white flex items-center justify-center text-2xl shadow-md shadow-emerald-500/20 shrink-0">
                    🛒
                </div>
            @endif
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
            @php $rem = $purchase->remaining(); @endphp
            @if ($rem > 0)
                <button type="button" @click="payModal = true"
                        class="px-4 py-2 rounded-xl text-xs font-black bg-emerald-600 hover:bg-emerald-700 text-white shadow-xs inline-flex items-center gap-1.5 transition-all cursor-pointer">
                    <span>💳</span>
                    <span>پارەدانی قەرز</span>
                </button>
            @endif

            <a href="{{ route('purchases.print', $purchase) }}" target="_blank"
               class="px-4 py-2 rounded-xl text-xs font-black bg-blue-600 hover:bg-blue-700 text-white shadow-xs inline-flex items-center gap-1.5 transition-all cursor-pointer">
                <span>🖨️</span>
                <span>چاپکردنی پسوولە</span>
            </a>

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
            @endif

            <form method="POST" action="{{ route('purchases.destroy', $purchase) }}"
                  onsubmit="return confirm('دڵنیایت لە سڕینەوەی ئەم پسوولەی کڕینە؟ سەرجەم جوڵەی کۆگا و داراییەکانی پاک دەکرێنەوە.')"
                  class="inline">
                @csrf
                @method('DELETE')
                <button type="submit"
                        class="px-4 py-2 rounded-xl text-xs font-bold bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 inline-flex items-center gap-1.5 transition-all cursor-pointer">
                    <span>🗑️</span>
                    <span>سڕینەوە</span>
                </button>
            </form>

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
                                            @php $rowImg = $line->imageUrl() ?? $purchase->imageUrl(); @endphp
                                            @if ($rowImg)
                                                <img src="{{ $rowImg }}"
                                                      class="size-9 rounded-lg object-cover border border-slate-200 shrink-0 cursor-pointer hover:scale-110 transition-transform"
                                                      onclick="window.open('{{ $rowImg }}', '_blank')"
                                                      title="کرتە بکە بۆ بینینی وێنەی وەسڵ">
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
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($purchase->payments as $payment)
                                    <tr class="hover:bg-slate-50/80 transition-colors">
                                        <td class="p-3.5 font-mono font-bold text-slate-800">{{ $payment->voucher_no }}</td>
                                        <td class="p-3.5 font-mono text-slate-600">{{ fmt_date($payment->paid_at) }}</td>
                                        <td class="p-3.5 text-left font-mono font-black text-emerald-700">{{ fmt_money($payment->amount, $payment->currency) }}</td>
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

                <div class="flex items-center justify-between">
                    <span class="text-slate-500 font-medium">دراوی پسوولە:</span>
                    <span class="font-bold {{ $purchase->currency === 'USD' ? 'text-amber-700' : 'text-slate-800' }}">
                        {{ $purchase->currency === 'USD' ? 'دۆلاری ئەمریکی ($ USD)' : 'دیناری عێراقی (IQD)' }}
                    </span>
                </div>

                @if($purchase->note)
                    <div class="pt-2 border-t border-slate-100">
                        <span class="text-slate-400 block mb-1">تێبینی:</span>
                        <span class="text-slate-700 italic">{{ $purchase->note }}</span>
                    </div>
                @endif
            </div>

            {{-- وێنەی پسوولەی کڕین --}}
            @if ($purchase->imageUrl())
                <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs space-y-2">
                    <h3 class="font-black text-slate-800 text-xs flex items-center gap-1.5 border-b border-slate-100 pb-2">
                        <span>📷</span>
                        <span>وێنەی وەسڵی کڕین</span>
                    </h3>
                    <div class="rounded-xl overflow-hidden border border-slate-200 bg-slate-50 cursor-pointer group"
                         onclick="window.open('{{ $purchase->imageUrl() }}', '_blank')"
                         title="کلیک بکە بۆ بینینی تەواوی وێنەکە">
                        <img src="{{ $purchase->imageUrl() }}" class="w-full max-h-64 object-contain mx-auto group-hover:scale-105 transition-transform">
                    </div>
                    <a href="{{ $purchase->imageUrl() }}" target="_blank"
                       class="block text-center text-[11px] font-bold text-teal-700 hover:underline pt-1">
                        🔍 بینینی تەواوی وێنەکە
                    </a>
                </div>
            @endif

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

                @if ($rem > 0)
                    <div class="pt-2">
                        <button type="button" @click="payModal = true"
                                class="w-full py-2.5 rounded-xl text-xs font-black bg-emerald-600 hover:bg-emerald-700 text-white shadow-xs flex items-center justify-center gap-1.5 transition-all cursor-pointer">
                            <span>💳</span>
                            <span>پارەدانی ماوەی پسوولە</span>
                        </button>
                    </div>
                @endif
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

    {{-- مۆداڵی پارەدانی قەرزی پسوولە --}}
    <div x-show="payModal"
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs transition-opacity"
         @keydown.escape.window="payModal = false">
        
        <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl border border-slate-100 overflow-hidden transform transition-all text-right"
             @click.outside="payModal = false"
             dir="rtl">
            
            {{-- سەردێڕی مۆداڵ --}}
            <div class="bg-gradient-to-l from-emerald-600 to-teal-700 p-4 sm:p-5 text-white flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="size-10 rounded-xl bg-white/20 flex items-center justify-center text-xl shrink-0">
                        💳
                    </div>
                    <div>
                        <h3 class="font-black text-sm sm:text-base">تۆمارکردنی پارەدان بە فرۆشیار</h3>
                        <p class="text-xs text-emerald-100 mt-0.5">
                            پسوولەی <span class="font-mono font-bold text-white">#{{ $purchase->invoice_no }}</span>
                            • فرۆشیار: <span class="font-bold text-white">{{ $purchase->supplier?->name ?: 'نەناسراو' }}</span>
                        </p>
                    </div>
                </div>
                <button type="button" @click="payModal = false" class="size-8 rounded-lg bg-white/10 hover:bg-white/20 text-white flex items-center justify-center text-lg transition-colors cursor-pointer">
                    &times;
                </button>
            </div>

            {{-- کارتی زانیاری قەرز --}}
            <div class="p-4 sm:p-5 bg-slate-50/80 border-b border-slate-100">
                <div class="grid grid-cols-3 gap-2 text-center text-xs">
                    <div class="bg-white p-2.5 rounded-xl border border-slate-200">
                        <span class="text-slate-400 block text-[11px] font-medium mb-0.5">کۆی پسوولە</span>
                        <span class="font-mono font-bold text-slate-800">{{ fmt_money($purchase->total, $purchase->currency) }}</span>
                    </div>
                    <div class="bg-white p-2.5 rounded-xl border border-slate-200">
                        <span class="text-slate-400 block text-[11px] font-medium mb-0.5">دراوە</span>
                        <span class="font-mono font-bold text-emerald-600">{{ fmt_money($purchase->paidTotal(), $purchase->currency) }}</span>
                    </div>
                    <div class="bg-rose-50 p-2.5 rounded-xl border border-rose-200">
                        <span class="text-rose-600 block text-[11px] font-bold mb-0.5">ماوە (قەرز)</span>
                        <span class="font-mono font-black text-rose-700">{{ fmt_money($rem, $purchase->currency) }}</span>
                    </div>
                </div>
            </div>

            {{-- فۆڕمی پارەدان --}}
            <form method="POST" action="{{ route('purchases.payments.store', $purchase) }}" class="p-4 sm:p-5 space-y-4">
                @csrf

                {{-- بڕی پارەی دراو --}}
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="label !mb-0 font-bold" for="show_modal_pay_amount">
                            بڕی پارەی دراو <span class="text-rose-500">*</span>
                        </label>
                        <button type="button" @click="payForm.amount = '{{ number_format((float)$rem) }}'" class="text-xs text-teal-700 hover:text-teal-800 font-bold underline cursor-pointer">
                            دانەوەی هەمووی ({{ number_format((float)$rem) }})
                        </button>
                    </div>
                    <div class="relative">
                        <input id="show_modal_pay_amount"
                               name="amount"
                               type="text"
                               inputmode="numeric"
                               required
                               x-model="payForm.amount"
                               @input="formatAmount($event)"
                               class="field num font-black text-emerald-700 text-base !py-2.5 pl-14 w-full"
                               placeholder="0">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-xs font-bold text-slate-400 pointer-events-none">
                            {{ $purchase->currency }}
                        </span>
                    </div>
                </div>

                {{-- هەڵبژاردنی قاسە --}}
                <div>
                    <label class="label font-bold" for="show_modal_pay_box">دەرهێنان لە قاسەی <span class="text-rose-500">*</span></label>
                    <select id="show_modal_pay_box" name="cash_box_id" x-model="payForm.cash_box_id" class="field w-full cursor-pointer font-medium" required>
                        @foreach ($cashBoxes as $box)
                            <option value="{{ $box->id }}">{{ $box->name }} ({{ $box->currency }}) — باڵانس: {{ fmt_num($box->balance()) }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- بەرواری پارەدان --}}
                <div>
                    <label class="label font-bold" for="show_modal_pay_date">بەرواری پارەدان <span class="text-rose-500">*</span></label>
                    <input id="show_modal_pay_date" name="paid_at" type="date" x-model="payForm.paid_at" class="field num w-full" required>
                </div>

                {{-- تێبینی --}}
                <div>
                    <label class="label" for="show_modal_pay_note">تێبینی (ئارەزوومەندانە)</label>
                    <input id="show_modal_pay_note" name="note" type="text" x-model="payForm.note" class="field w-full text-xs" placeholder="تێبینی بنووسە...">
                </div>

                {{-- دوگمەکان --}}
                <div class="flex items-center justify-end gap-2.5 pt-2">
                    <button type="button" @click="payModal = false" class="btn btn-ghost !py-2 !px-4 text-xs font-bold text-slate-600">
                        پاشگەزبوونەوە
                    </button>
                    <button type="submit" class="btn !py-2 !px-5 text-xs font-black text-white bg-emerald-600 hover:bg-emerald-700 shadow-sm cursor-pointer">
                        تۆمارکردنی پارەدان ✔️
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

@if ($purchase->status === 'draft')
    <form id="confirm-purchase" method="POST" action="{{ route('purchases.confirm', $purchase) }}" class="hidden">
        @csrf
    </form>
@endif

@endsection
