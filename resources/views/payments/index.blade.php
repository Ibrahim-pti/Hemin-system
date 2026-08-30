@extends('layouts.app')
@section('title', 'حەقدی و پارەدانەکان')

@section('actions')
    <div class="flex items-center gap-2">
        <a href="{{ route('payments.create', ['type' => 'in']) }}" class="btn !py-1.5 !px-3 text-xs font-bold gap-1 bg-emerald-600 hover:bg-emerald-700 text-white shadow-xs">
            <span>+</span>
            <span>وەرگرتنی پارە (حەقدی)</span>
        </a>
        <a href="{{ route('payments.create', ['type' => 'out']) }}" class="btn btn-ghost !py-1.5 !px-3 text-xs font-bold gap-1 border border-slate-200 hover:bg-slate-100 text-slate-700">
            <span>-</span>
            <span>دانی پارە</span>
        </a>
    </div>
@endsection

@section('content')

<div x-data="{ showDeleteModal: false, deleteUrl: '' }" class="space-y-4">

    {{-- ١. کارتەکانی کورتە-ئاماری دارایی --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
        {{-- کۆی وەرگیراو --}}
        <div class="bg-white rounded-2xl p-4 border border-slate-100 shadow-xs border-r-4 border-r-emerald-500 flex items-center justify-between">
            <div>
                <div class="text-xs font-bold text-slate-500">کۆی گشتی وەرگیراو (داهات)</div>
                <div class="text-2xl font-black text-emerald-700 num mt-1">{{ fmt_money($totalIn) }}</div>
            </div>
            <div class="size-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl shrink-0">
                📥
            </div>
        </div>

        {{-- کۆی دراو --}}
        <div class="bg-white rounded-2xl p-4 border border-slate-100 shadow-xs border-r-4 border-r-amber-500 flex items-center justify-between">
            <div>
                <div class="text-xs font-bold text-slate-500">کۆی پارەی دراو (خەرجی)</div>
                <div class="text-2xl font-black text-amber-700 num mt-1">{{ fmt_money($totalOut) }}</div>
            </div>
            <div class="size-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl shrink-0">
                📤
            </div>
        </div>

        {{-- پوختەی جیاوازی --}}
        @php
            $netDiff = $totalIn - $totalOut;
        @endphp
        <div class="bg-white rounded-2xl p-4 border border-slate-100 shadow-xs border-r-4 {{ $netDiff >= 0 ? 'border-r-blue-500' : 'border-r-rose-500' }} flex items-center justify-between">
            <div>
                <div class="text-xs font-bold text-slate-500">پوختەی داهات (جیاوازی)</div>
                <div class="text-2xl font-black num mt-1 {{ $netDiff >= 0 ? 'text-blue-700' : 'text-rose-600' }}">
                    {{ fmt_money($netDiff) }}
                </div>
            </div>
            <div class="size-11 rounded-xl {{ $netDiff >= 0 ? 'bg-blue-50 text-blue-600' : 'bg-rose-50 text-rose-600' }} flex items-center justify-center text-xl shrink-0">
                ⚖️
            </div>
        </div>
    </div>

    {{-- ٢. فۆرمی فلتەر و گەڕان --}}
    <form method="GET" class="bg-white rounded-2xl p-4 border border-slate-100 shadow-xs">
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-5 items-end">
            {{-- خانەی گەڕان --}}
            <div class="lg:col-span-2">
                <label class="block text-xs font-bold text-slate-600 mb-1.5">گەڕان</label>
                <div class="relative">
                    <input type="search" name="q" value="{{ request('q') }}" class="w-full pl-8 pr-3 py-2 text-xs rounded-xl border border-slate-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 bg-slate-50/50" placeholder="ژمارەی سەنەد، ناوی لایەن یان تێبینی...">
                    <span class="absolute left-2.5 top-2.5 text-slate-400 text-xs">🔍</span>
                </div>
            </div>

            {{-- جۆری جوڵە --}}
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1.5">جۆری جوڵە</label>
                <select name="direction" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 bg-slate-50/50">
                    <option value="">هەموو جۆرەکان</option>
                    <option value="in" @selected(request('direction') === 'in')>📥 وەرگرتنی پارە</option>
                    <option value="out" @selected(request('direction') === 'out')>📤 دانی پارە</option>
                </select>
            </div>

            {{-- لە بەرواری --}}
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1.5">لە بەرواری</label>
                <input type="date" name="from" value="{{ request('from') }}" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 bg-slate-50/50 num">
            </div>

            {{-- تا بەرواری و دوگمەی پاڵاوتن --}}
            <div class="flex items-center gap-2">
                <div class="flex-1">
                    <label class="block text-xs font-bold text-slate-600 mb-1.5">تا بەرواری</label>
                    <input type="date" name="to" value="{{ request('to') }}" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 bg-slate-50/50 num">
                </div>
                <button type="submit" class="btn !py-2 !px-4 text-xs font-bold bg-blue-600 hover:bg-blue-700 text-white rounded-xl shadow-xs shrink-0 cursor-pointer">
                    پاڵاوتن
                </button>
                @if(request()->hasAny(['q', 'direction', 'from', 'to']))
                    <a href="{{ route('payments.index') }}" class="btn btn-ghost !py-2 !px-2.5 text-xs text-slate-500 hover:bg-slate-100 rounded-xl" title="پاککردنەوەی فلتەر">
                        ✕
                    </a>
                @endif
            </div>
        </div>
    </form>

    {{-- ٣. خشتەی سەرەکی حەقدییەکان --}}
    <div class="bg-white rounded-2xl shadow-xs border border-slate-100 overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between flex-wrap gap-2">
            <div class="font-bold text-slate-800 text-sm flex items-center gap-2">
                <span class="text-blue-600">💳</span>
                <span>تۆماری حەقدی و پارەدانەکان</span>
            </div>
            <span class="text-xs text-slate-400 font-medium num">{{ fmt_num($payments->total()) }} جوڵە دۆزرایەوە</span>
        </div>

        <div class="overflow-x-auto">
            <table class="table w-full text-right">
                <thead>
                    <tr class="text-xs text-slate-500 border-b border-slate-100 bg-slate-50/40">
                        <th class="py-3 px-4 text-center w-12">#</th>
                        <th class="py-3 px-4 text-center">ژمارەی سەنەد</th>
                        <th class="py-3 px-4 text-center">بەروار</th>
                        <th class="py-3 px-4 text-center">جۆر</th>
                        <th class="py-3 px-4">لایەن / کەس</th>
                        <th class="py-3 px-4">وەسڵ / پسوولە</th>
                        <th class="py-3 px-4 text-center">بڕی پارە</th>
                        <th class="py-3 px-4">قاسە</th>
                        <th class="py-3 px-4">تێبینی</th>
                        <th class="py-3 px-4 text-center w-28">کردار</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse ($payments as $index => $payment)
                        <tr class="hover:bg-blue-50/30 transition-colors">
                            {{-- # --}}
                            <td class="py-3.5 px-4 text-center num text-slate-400 font-medium">
                                {{ $payments->firstItem() + $index }}
                            </td>

                            {{-- ژمارەی سەنەد --}}
                            <td class="py-3.5 px-4 text-center">
                                <a href="{{ route('payments.print', $payment) }}" target="_blank"
                                   class="inline-flex items-center justify-center px-2.5 py-0.5 rounded-md text-xs font-mono font-bold text-slate-700 bg-slate-100 hover:bg-blue-600 hover:text-white border border-slate-200 transition-colors"
                                   title="کرتە بکە بۆ چاپی سەنەد">
                                    {{ $payment->voucher_no }}
                                </a>
                            </td>

                            {{-- بەروار --}}
                            <td class="py-3.5 px-4 text-center num text-xs text-slate-600 whitespace-nowrap">
                                {{ fmt_date($payment->paid_at) }}
                            </td>

                            {{-- جۆری وەرگرتن یان دان --}}
                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                @if ($payment->direction === 'in')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold text-emerald-700 bg-emerald-50 border border-emerald-200">
                                        <span>📥</span>
                                        <span>وەرگرتن</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold text-amber-700 bg-amber-50 border border-amber-200">
                                        <span>📤</span>
                                        <span>دان</span>
                                    </span>
                                @endif
                            </td>

                            {{-- لایەن --}}
                            <td class="py-3.5 px-4">
                                @if ($payment->party instanceof \App\Models\Customer)
                                    <a href="{{ route('customers.show', $payment->party) }}" class="font-bold text-slate-800 hover:text-blue-600 transition-colors inline-flex items-center gap-1.5">
                                        <span class="size-5 rounded-full bg-blue-50 text-blue-600 text-[10px] font-bold flex items-center justify-center">کڕ</span>
                                        <span>{{ $payment->party_label }}</span>
                                    </a>
                                @elseif ($payment->party instanceof \App\Models\Supplier)
                                    <a href="{{ route('suppliers.show', $payment->party) }}" class="font-bold text-slate-800 hover:text-blue-600 transition-colors inline-flex items-center gap-1.5">
                                        <span class="size-5 rounded-full bg-indigo-50 text-indigo-600 text-[10px] font-bold flex items-center justify-center">فر</span>
                                        <span>{{ $payment->party_label }}</span>
                                    </a>
                                @else
                                    <span class="font-bold text-slate-800">{{ $payment->party_label }}</span>
                                @endif
                            </td>

                            {{-- بەستراوە بە وەسڵ یان پسوولە --}}
                            <td class="py-3.5 px-4 text-xs">
                                @if ($payment->order)
                                    <a href="{{ route('orders.print', $payment->order) }}" target="_blank"
                                       class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-xs font-mono font-bold text-blue-700 bg-blue-50 border border-blue-200 hover:bg-blue-100">
                                        <span>📄</span>
                                        <span>وەسڵ: {{ $payment->order->invoice_no }}</span>
                                    </a>
                                @elseif ($payment->purchase)
                                    <a href="{{ route('purchases.show', $payment->purchase) }}"
                                       class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-xs font-mono font-bold text-indigo-700 bg-indigo-50 border border-indigo-200 hover:bg-indigo-100">
                                        <span>🛒</span>
                                        <span>پسوولە: {{ $payment->purchase->invoice_no }}</span>
                                    </a>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>

                            {{-- بڕی پارە --}}
                            <td class="py-3.5 px-4 text-center num font-black {{ $payment->direction === 'in' ? 'text-emerald-700' : 'text-amber-700' }}">
                                {{ fmt_money($payment->amount, $payment->currency) }}
                            </td>

                            {{-- قاسە --}}
                            <td class="py-3.5 px-4 text-xs font-medium text-slate-600">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-slate-100 text-slate-700">
                                    <span>💰</span>
                                    <span>{{ $payment->cashBox?->name ?? 'قاسەی سەرەکی' }}</span>
                                </span>
                            </td>

                            {{-- تێبینی --}}
                            <td class="py-3.5 px-4 text-xs text-slate-600 max-w-xs truncate">
                                {{ $payment->note ?? '—' }}
                            </td>

                            {{-- کردار --}}
                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                <div class="flex items-center justify-center gap-1">
                                    <a href="{{ route('payments.print', $payment) }}" target="_blank"
                                       class="btn btn-ghost !py-1 !px-2 text-xs font-bold text-slate-700 hover:bg-slate-100"
                                       title="چاپکردنی سەنەد">
                                        🖨️ چاپ
                                    </a>
                                    <button type="button"
                                            @click="showDeleteModal = true; deleteUrl = '{{ route('payments.destroy', $payment) }}'"
                                            class="btn btn-ghost !py-1 !px-2 text-xs font-bold text-rose-600 hover:bg-rose-50"
                                            title="سڕینەوەی سەنەد">
                                        🗑️
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="py-12 text-center text-slate-400 text-sm font-medium">
                                هیچ حەقدی و پارەدانێک نەدۆزرایەوە.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($payments->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $payments->links() }}
            </div>
        @endif
    </div>

    {{-- مۆداڵی دڵنیابوونەوە لە سڕینەوە --}}
    <div x-show="showDeleteModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-xs p-4"
         x-transition.opacity>
        <div class="bg-white rounded-2xl shadow-xl max-w-sm w-full p-6 text-center border border-slate-100"
             @click.away="showDeleteModal = false"
             x-transition.scale>
            <div class="size-14 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center mx-auto mb-3 text-2xl">
                ⚠️
            </div>
            <h3 class="text-base font-bold text-slate-900 mb-1">دڵنیایت لە سڕینەوە؟</h3>
            <p class="text-xs text-slate-500 mb-5 leading-relaxed">
                ئەم حەقدییە و جوڵەی ناو قاسەکەی بە تەواوی دەسڕدرێنەوە.
            </p>
            <div class="flex items-center justify-center gap-2">
                <form :action="deleteUrl" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn !py-2 !px-4 text-xs font-bold bg-rose-600 hover:bg-rose-700 text-white rounded-xl">
                        بەڵێ، بسڕەوە
                    </button>
                </form>
                <button type="button" @click="showDeleteModal = false" class="btn btn-ghost !py-2 !px-4 text-xs font-bold text-slate-600">
                    پاشگەزبوونەوە
                </button>
            </div>
        </div>
    </div>

</div>

@endsection
