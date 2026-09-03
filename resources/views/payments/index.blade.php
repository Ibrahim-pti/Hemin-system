@extends('layouts.app')
@section('title', 'حەقدی موشتەرییەکان')

@section('actions')
    <a href="{{ route('payments.create') }}"
       class="btn btn-primary !py-2 !px-4 text-xs font-bold gap-1.5 shadow-sm bg-emerald-600 hover:bg-emerald-700 text-white cursor-pointer">
        <span>+</span>
        <span>وەرگرتنی حەقدی نوێ</span>
    </a>
@endsection

@section('content')

<div x-data="{ showDeleteModal: false, deleteUrl: '' }" class="flex flex-col gap-5 w-full">

    {{-- ١. کارتەکانی ئاماری سەرەوە --}}
    <div class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
        {{-- ١. کۆی حەقدی وەرگیراو --}}
        <div class="bg-white rounded-2xl p-5 shadow-xs border border-slate-100 border-r-4 border-r-emerald-500 relative flex items-center justify-between overflow-hidden">
            <div>
                <div class="text-2xl sm:text-3xl font-black text-emerald-700 num tracking-tight">
                    {{ fmt_money($totalIn) }}
                </div>
                <div class="text-xs font-bold text-slate-500 mt-1">کۆی حەقدی وەرگیراو لە موشتەری (د.ع)</div>
            </div>
            <div class="size-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl shrink-0">
                📥
            </div>
        </div>

        {{-- ٢. ژمارەی حەقدییەکان --}}
        <div class="bg-white rounded-2xl p-5 shadow-xs border border-slate-100 border-r-4 border-r-blue-500 relative flex items-center justify-between overflow-hidden">
            <div>
                <div class="text-2xl sm:text-3xl font-black text-slate-800 num tracking-tight">
                    {{ fmt_num($totalCount) }}
                </div>
                <div class="text-xs font-bold text-slate-500 mt-1">کۆی حەقدییە تۆمارکراوەکان</div>
            </div>
            <div class="size-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl shrink-0">
                🧾
            </div>
        </div>

        {{-- ٣. قەرزی ماوە لای موشتەرییەکان --}}
        <div class="bg-white rounded-2xl p-5 shadow-xs border border-slate-100 border-r-4 border-r-rose-500 relative flex items-center justify-between overflow-hidden sm:col-span-2 lg:col-span-1">
            <div>
                <div class="text-2xl sm:text-3xl font-black text-rose-600 num tracking-tight">
                    {{ fmt_money($totalDebt) }}
                </div>
                <div class="text-xs font-bold text-slate-500 mt-1">کۆی قەرزی ماوە لای موشتەرییەکان (د.ع)</div>
            </div>
            <div class="size-12 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-xl shrink-0">
                ⚠️
            </div>
        </div>
    </div>

    {{-- ٢. فۆرمی فلتەر و گەڕان --}}
    <form method="GET" class="bg-white rounded-2xl p-4 border border-slate-100 shadow-xs">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-3 items-end">
            {{-- گەڕان بە ناو و وەسڵ --}}
            <div class="sm:col-span-2">
                <label class="block text-xs font-bold text-slate-600 mb-1">گەڕان لە حەقدی</label>
                <div class="relative">
                    <input type="search" name="q" value="{{ request('q') }}"
                           class="w-full pl-8 pr-3 py-2 text-xs rounded-xl border border-slate-200 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100 bg-slate-50/50"
                           placeholder="ناوی موشتەری، وەسڵ یان تێبینی...">
                    <span class="absolute left-2.5 top-2.5 text-slate-400 text-xs">🔍</span>
                </div>
            </div>

            {{-- لە بەرواری --}}
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">لە بەرواری</label>
                <input type="date" name="from" value="{{ request('from') }}"
                       class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100 bg-slate-50/50 num">
            </div>

            {{-- تا بەرواری --}}
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">تا بەرواری</label>
                <input type="date" name="to" value="{{ request('to') }}"
                       class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100 bg-slate-50/50 num">
            </div>

            {{-- دوگمەی کردار --}}
            <div class="flex items-center gap-2">
                <button type="submit"
                        class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white py-2 px-4 rounded-xl font-bold text-xs shadow-xs transition-colors cursor-pointer text-center">
                    پاڵاوتن
                </button>
                @if(request()->hasAny(['q', 'from', 'to', 'customer_id']))
                    <a href="{{ route('payments.index') }}"
                       class="bg-slate-100 hover:bg-slate-200 text-slate-600 py-2 px-3 rounded-xl font-bold text-xs transition-colors"
                       title="پاککردنەوەی فلتەر">
                        ✕
                    </a>
                @endif
            </div>
        </div>
    </form>

    {{-- ٣. خشتەی سەرەکی حەقدی موشتەرییەکان --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-xs overflow-hidden">
        <div class="p-4 bg-slate-50/50 border-b border-slate-100 flex items-center justify-between">
            <div class="flex items-center gap-2 font-bold text-sm text-slate-800">
                <span class="text-emerald-600">📥</span>
                <span>تۆماری حەقدی وەرگیراو لە موشتەرییەکان</span>
            </div>
            <span class="text-xs text-slate-500 font-semibold num">
                کۆی گشتی: {{ fmt_num($payments->total()) }} حەقدی
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-right text-sm">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50/80 text-xs text-slate-500 font-bold">
                        <th class="py-3 px-4 w-12 text-center">#</th>
                        <th class="py-3 px-4">موشتەری (کڕیار)</th>
                        <th class="py-3 px-4 text-center">وەسڵی پەیوەندیدار</th>
                        <th class="py-3 px-4 text-center">بەروار</th>
                        <th class="py-3 px-4 text-center">بڕی حەقدی</th>
                        <th class="py-3 px-4">تێبینی</th>
                        <th class="py-3 px-4 text-center w-28">کردار</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($payments as $index => $payment)
                        <tr class="hover:bg-emerald-50/30 transition-colors">
                            {{-- # --}}
                            <td class="py-3.5 px-4 text-center num text-slate-400 font-medium text-xs">
                                {{ $payments->firstItem() + $index }}
                            </td>

                            {{-- موشتەری --}}
                            <td class="py-3.5 px-4">
                                @if ($payment->party instanceof \App\Models\Customer)
                                    <a href="{{ route('customers.show', $payment->party) }}" class="flex items-center gap-2 group">
                                        <span class="size-7 rounded-full bg-emerald-100 text-emerald-700 font-bold text-xs flex items-center justify-center shrink-0 group-hover:bg-emerald-600 group-hover:text-white transition-colors">
                                            {{ mb_substr($payment->party->name, 0, 1) }}
                                        </span>
                                        <div>
                                            <span class="font-bold text-slate-800 group-hover:text-emerald-700 transition-colors block">
                                                {{ $payment->party->name }}
                                            </span>
                                            @if ($payment->party->phone)
                                                <span class="num text-[11px] text-slate-400 block" dir="ltr">{{ $payment->party->phone }}</span>
                                            @endif
                                        </div>
                                    </a>
                                @else
                                    <span class="font-bold text-slate-800">{{ $payment->party_label }}</span>
                                @endif
                            </td>

                            {{-- وەسڵی پەیوەندیدار --}}
                            <td class="py-3.5 px-4 text-center">
                                @if ($payment->order)
                                    <a href="{{ route('orders.print', $payment->order) }}"
                                       class="inline-flex items-center gap-1 font-mono text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200 px-2.5 py-0.5 rounded-md hover:bg-blue-100 transition-colors">
                                        <span>📄</span>
                                        <span>وەسڵی {{ $payment->order->invoice_no }}</span>
                                    </a>
                                @else
                                    <span class="text-xs text-slate-400">حسابی گشتی کڕیار</span>
                                @endif
                            </td>

                            {{-- بەروار --}}
                            <td class="py-3.5 px-4 text-center num text-xs text-slate-600 whitespace-nowrap">
                                {{ fmt_date($payment->paid_at) }}
                            </td>

                            {{-- بڕی پارە --}}
                            <td class="py-3.5 px-4 text-center num font-black text-emerald-700 text-base whitespace-nowrap">
                                +{{ fmt_money($payment->amount, $payment->currency) }}
                            </td>

                            {{-- تێبینی --}}
                            <td class="py-3.5 px-4 text-xs text-slate-500 max-w-[200px] truncate">
                                {{ $payment->note ?: '—' }}
                            </td>

                            {{-- کردار --}}
                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                <div class="inline-flex items-center gap-1.5">
                                    <a href="{{ route('payments.print', $payment) }}"
                                       class="inline-flex items-center gap-1 bg-white hover:bg-emerald-50 text-emerald-700 border border-emerald-200 px-2.5 py-1 rounded-lg text-xs font-bold shadow-2xs transition-colors"
                                       title="چاپ">
                                        <span>🖨️</span>
                                        <span>چاپ</span>
                                    </a>

                                    <button type="button"
                                            @click="showDeleteModal = true; deleteUrl = '{{ route('payments.destroy', $payment) }}'"
                                            class="size-7 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 inline-flex items-center justify-center text-xs transition-colors cursor-pointer"
                                            title="سڕینەوە">
                                        🗑️
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400 text-sm font-medium">
                                هیچ حەقدییەک نەدۆزرایەوە.
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
    <div x-show="showDeleteModal" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs"
         x-transition.opacity>
        <div class="bg-white rounded-2xl max-w-sm w-full p-6 text-center shadow-xl border border-slate-100"
             @click.away="showDeleteModal = false"
             x-transition.scale>
            <div class="size-14 rounded-full bg-rose-50 text-rose-600 flex items-center justify-center mx-auto mb-3 text-2xl">
                ⚠️
            </div>
            <h3 class="text-base font-extrabold text-slate-900 mb-1">دڵنیایت لە سڕینەوە؟</h3>
            <p class="text-xs text-slate-500 mb-5 leading-relaxed">
                ئەم حەقدییە و جوڵەی ناو قاسەکەی بە تەواوی دەسڕدرێنەوە و باڵانسی کڕیارەکە نوێ دەبێتەوە.
            </p>
            <div class="flex items-center justify-center gap-2">
                <form :action="deleteUrl" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="bg-rose-600 hover:bg-rose-700 text-white px-4 py-2 rounded-xl text-xs font-bold cursor-pointer transition-colors">
                        بەڵێ، بسڕەوە
                    </button>
                </form>
                <button type="button" @click="showDeleteModal = false" class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-4 py-2 rounded-xl text-xs font-bold cursor-pointer transition-colors">
                    پاشگەزبوونەوە
                </button>
            </div>
        </div>
    </div>

</div>

@endsection
