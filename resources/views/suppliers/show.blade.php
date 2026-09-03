@extends('layouts.app')
@section('title', 'کەشف حیسابی فرۆشیار: ' . $supplier->name)

@section('actions')
    <div class="flex items-center gap-2 no-print">
        <a href="{{ route('purchases.index') }}" class="btn btn-ghost !py-1.5 !px-3 text-xs gap-1 border border-slate-200 hover:bg-slate-100 font-bold text-slate-700">
            <span>&larr;</span>
            <span>گەڕانەوە بۆ کڕینەکان</span>
        </a>
        <a href="{{ route('purchases.create', ['supplier' => $supplier->id]) }}" class="btn !py-1.5 !px-3 text-xs font-bold gap-1 bg-blue-600 hover:bg-blue-700 text-white shadow-xs">
            <span>+</span>
            <span>پسوولەی کڕینی نوێ</span>
        </a>
        @if($currentBalance > 0)
            <a href="{{ route('payments.create', ['type' => 'out', 'supplier' => $supplier->id]) }}" class="btn !py-1.5 !px-3 text-xs font-bold gap-1 bg-emerald-600 hover:bg-emerald-700 text-white shadow-xs">
                <span>+</span>
                <span>پارەدانی قەرز</span>
            </a>
        @endif
        <button type="button" onclick="window.print()" class="btn btn-ghost !py-1.5 !px-3 text-xs font-bold gap-1 border border-slate-200 hover:bg-slate-100 text-slate-700">
            <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="6 9 6 2 18 2 18 9"></polyline>
                <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                <rect x="6" y="14" width="12" height="8"></rect>
            </svg>
            <span>چاپکردن</span>
        </button>
        <a href="{{ route('suppliers.edit', $supplier) }}" class="btn btn-ghost !py-1.5 !px-3 text-xs font-bold border border-slate-200 hover:bg-slate-100 text-slate-700">
            <span>✏️</span>
            <span>دەستکاری</span>
        </a>
    </div>
@endsection

@section('content')

<div class="space-y-6">

    {{-- ١. کارتی سەرەوەی پڕۆفایل و زانیارییەکان --}}
    <div class="bg-white rounded-2xl shadow-xs border border-slate-100 p-5 sm:p-6">
        <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="size-14 rounded-2xl bg-gradient-to-br from-blue-500/15 to-indigo-500/10 border border-blue-200 flex items-center justify-center text-2xl font-black text-blue-700 shadow-inner shrink-0">
                    🏢
                </div>
                <div>
                    <div class="flex items-center gap-2.5 flex-wrap">
                        <h1 class="text-xl font-black text-slate-900">{{ $supplier->name }}</h1>
                        <span class="px-2.5 py-0.5 rounded-md text-xs font-mono font-bold text-blue-700 bg-blue-50 border border-blue-200/80">
                            S-{{ str_pad($supplier->id, 5, '0', STR_PAD_LEFT) }}
                        </span>
                        @if($supplier->is_active)
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                <span class="size-1.5 rounded-full bg-emerald-500"></span>
                                چالاک
                            </span>
                        @endif
                    </div>
                    <div class="flex flex-wrap items-center gap-3 mt-1.5 text-xs text-slate-500">
                        @if ($supplier->phone)
                            <span class="flex items-center gap-1">
                                <span>📱</span>
                                <span class="num font-bold text-slate-700" dir="ltr">{{ $supplier->phone }}</span>
                            </span>
                        @endif
                        @if ($supplier->address)
                            <span class="text-slate-300">•</span>
                            <span class="flex items-center gap-1">
                                <span>📍</span>
                                <span class="font-medium text-slate-700">{{ $supplier->address }}</span>
                            </span>
                        @endif
                        @if ($supplier->note)
                            <span class="text-slate-300">•</span>
                            <span class="text-slate-600">📝 {{ $supplier->note }}</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="no-print">
                <a href="{{ route('suppliers.edit', $supplier) }}" class="btn btn-ghost !py-1.5 !px-3 text-xs font-bold border border-slate-200 hover:bg-slate-100 text-slate-700 inline-flex items-center gap-1.5">
                    <span>✏️</span>
                    <span>دەستکاری زانیاری</span>
                </a>
            </div>
        </div>

        {{-- سێ کارتی ئاماری کۆی کڕینەکان و قەرز --}}
        <div class="grid gap-4 grid-cols-1 sm:grid-cols-3 mt-5 pt-5 border-t border-slate-100">
            {{-- کۆی کڕینەکان --}}
            <div class="bg-slate-50/80 rounded-xl p-4 border border-slate-100 border-r-4 border-r-blue-500 text-center">
                <div class="text-2xl font-black text-slate-900 num">{{ fmt_money($totalPurchases) }}</div>
                <div class="text-xs font-bold text-slate-500 mt-1">کۆی گشتی کڕینەکان</div>
            </div>

            {{-- کۆی پارەی دراو --}}
            <div class="bg-slate-50/80 rounded-xl p-4 border border-slate-100 border-r-4 border-r-emerald-500 text-center">
                <div class="text-2xl font-black text-emerald-700 num">{{ fmt_money($totalPaid) }}</div>
                <div class="text-xs font-bold text-slate-500 mt-1">کۆی پارەی دراو</div>
            </div>

            {{-- قەرزی ماوە --}}
            <div class="bg-slate-50/80 rounded-xl p-4 border border-slate-100 border-r-4 {{ $currentBalance > 0 ? 'border-r-rose-500 bg-rose-50/30' : 'border-r-emerald-500 bg-emerald-50/30' }} text-center">
                <div class="text-2xl font-black num {{ $currentBalance > 0 ? 'text-rose-600' : 'text-emerald-700' }}">
                    {{ fmt_money(abs($currentBalance)) }}
                </div>
                <div class="text-xs font-bold mt-1 {{ $currentBalance > 0 ? 'text-rose-600' : 'text-emerald-700' }}">
                    {{ $currentBalance > 0 ? 'قەرزی ماوە لەسەر کارگە' : '✓ حساب پاکە (بێ قەرز)' }}
                </div>
            </div>
        </div>
    </div>

    {{-- ٢. خشتەی کەشف حیسابی سادە و ڕێکخراو لەگەڵ وێنەی وەسڵەکان --}}
    <div class="bg-white rounded-2xl shadow-xs border border-slate-100 overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between flex-wrap gap-2">
            <div class="font-bold text-slate-800 text-sm flex items-center gap-2">
                <span class="text-blue-600">🧾</span>
                <span>کەشف حیسابی تەواوی کڕین و مامەڵەکان</span>
            </div>
            <span class="text-xs text-slate-400 font-medium">ڕیزکراوە بەپێی بەروار لەگەڵ وێنەی وەسڵ و باڵانس</span>
        </div>

        <div class="overflow-x-auto">
            <table class="table w-full text-right">
                <thead>
                    <tr class="text-xs text-slate-500 border-b border-slate-100 bg-slate-50/40">
                        <th class="py-3 px-4 text-center w-12">#</th>
                        <th class="py-3 px-4 text-center">بەروار</th>
                        <th class="py-3 px-4 text-center">وێنەی وەسڵ</th>
                        <th class="py-3 px-4">پسوولە / جۆری مامەڵە</th>
                        <th class="py-3 px-4">وردەکاری مەوادەکان</th>
                        <th class="py-3 px-4 text-center font-bold text-slate-900">کۆی گشتی</th>
                        <th class="py-3 px-4 text-center font-bold text-emerald-700">پارەی دراو</th>
                        <th class="py-3 px-4 text-center font-bold text-rose-600">ماوە (قەرز)</th>
                        <th class="py-3 px-4 text-center">دۆخ</th>
                        <th class="py-3 px-4 text-center w-24 no-print">کردار</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse ($ledger as $index => $item)
                        <tr class="hover:bg-blue-50/40 transition-colors {{ $item->reference ? 'cursor-pointer' : '' }}"
                            @if($item->reference) onclick="if (!event.target.closest('a') && !event.target.closest('img')) window.location.href='{{ $item->reference }}'" @endif>

                            {{-- # --}}
                            <td class="py-3.5 px-4 text-center num text-slate-400 font-medium">
                                {{ $index + 1 }}
                            </td>

                            {{-- بەروار --}}
                            <td class="py-3.5 px-4 text-center num text-xs text-slate-600 whitespace-nowrap">
                                {{ fmt_date($item->date) }}
                            </td>

                            {{-- وێنەی وەسڵ --}}
                            <td class="py-2.5 px-3 text-center">
                                @if(!empty($item->image))
                                    <div class="inline-flex items-center justify-center group relative">
                                        <img src="{{ $item->image }}"
                                             class="size-12 rounded-xl object-cover border-2 border-slate-200 shadow-2xs group-hover:scale-125 group-hover:border-teal-500 transition-all cursor-pointer bg-white"
                                             onclick="event.stopPropagation(); window.open('{{ $item->image }}', '_blank')"
                                             title="کرتە بکە بۆ بینینی تەواوی وێنەی وەسڵ">
                                        <span class="absolute -bottom-1 -right-1 size-4 bg-teal-600 text-white rounded-full flex items-center justify-center text-[9px] shadow-xs pointer-events-none">🔍</span>
                                    </div>
                                @else
                                    <span class="inline-flex items-center gap-1 text-[11px] text-slate-400 font-medium bg-slate-50 px-2 py-1 rounded-md border border-slate-100">
                                        <span>📷</span>
                                        <span>بێ وێنە</span>
                                    </span>
                                @endif
                            </td>

                            {{-- جۆری مامەڵە --}}
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                @if($item->type === 'purchase')
                                    <a href="{{ $item->reference }}" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold text-blue-700 bg-blue-50 border border-blue-100 hover:bg-blue-100 transition-colors">
                                        <span>🛒</span>
                                        <span>{{ $item->title }}</span>
                                    </a>
                                @elseif($item->type === 'payment')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold text-emerald-700 bg-emerald-50 border border-emerald-100">
                                        <span>💳</span>
                                        <span>{{ $item->title }}</span>
                                    </span>
                                @elseif($item->type === 'job')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold text-purple-700 bg-purple-50 border border-purple-100">
                                        <span>🔧</span>
                                        <span>{{ $item->title }}</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold text-amber-700 bg-amber-50 border border-amber-100">
                                        <span>⚖️</span>
                                        <span>{{ $item->title }}</span>
                                    </span>
                                @endif
                            </td>

                            {{-- وردەکاری مەوادەکان --}}
                            <td class="py-3.5 px-4 text-xs text-slate-700 max-w-xs font-medium">
                                {{ $item->details }}
                            </td>

                            {{-- کۆی گشتی --}}
                            <td class="py-3.5 px-4 text-center num font-bold text-slate-900">
                                @if($item->total > 0)
                                    {{ fmt_money($item->total) }}
                                @else
                                    <span class="text-slate-300 font-normal">—</span>
                                @endif
                            </td>

                            {{-- پارەی دراو --}}
                            <td class="py-3.5 px-4 text-center num font-bold text-emerald-700">
                                @if($item->paid > 0)
                                    {{ fmt_money($item->paid) }}
                                @else
                                    <span class="text-slate-300 font-normal">—</span>
                                @endif
                            </td>

                            {{-- ماوە (قەرز) --}}
                            <td class="py-3.5 px-4 text-center num font-bold {{ $item->remaining > 0 ? 'text-rose-600' : 'text-slate-400' }}">
                                @if($item->remaining > 0)
                                    {{ fmt_money($item->remaining) }}
                                @else
                                    <span class="text-slate-300 font-normal">0 د.ع</span>
                                @endif
                            </td>

                            {{-- دۆخ --}}
                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                @if($item->type === 'purchase')
                                    @if($item->status === 'cash')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <span>✓</span>
                                            <span>حازری (نەقد)</span>
                                        </span>
                                    @elseif($item->status === 'partial')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                            <span>⚖️</span>
                                            <span>بەشێکی دراوە</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                            <span>⏳</span>
                                            <span>بە قەرز</span>
                                        </span>
                                    @endif
                                @elseif($item->type === 'payment')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                        <span>💳</span>
                                        <span>پارەدان</span>
                                    </span>
                                @else
                                    <span class="text-slate-400 text-xs">—</span>
                                @endif
                            </td>

                            {{-- کردار --}}
                            <td class="py-3.5 px-4 text-center whitespace-nowrap no-print">
                                @if($item->reference)
                                    <a href="{{ $item->reference }}" class="btn btn-ghost !py-1 !px-2.5 text-xs font-bold text-slate-700 hover:bg-slate-100">
                                        بینین
                                    </a>
                                @else
                                    <span class="text-slate-300">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="py-10 text-center text-slate-400 text-sm font-medium">
                                هیچ مامەڵەیەک بۆ ئەم فرۆشیارە تۆمار نەکراوە.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if($ledger->isNotEmpty())
                    <tfoot>
                        <tr class="bg-slate-50 border-t-2 border-slate-200 text-sm">
                            <td colspan="5" class="py-4 px-4 font-black text-slate-800">
                                کۆی گشتی حسابات
                            </td>
                            <td class="py-4 px-4 text-center num font-black text-slate-900">
                                {{ fmt_money($totalPurchases) }}
                            </td>
                            <td class="py-4 px-4 text-center num font-black text-emerald-700">
                                {{ fmt_money($totalPaid) }}
                            </td>
                            <td class="py-4 px-4 text-center num font-black text-base {{ $currentBalance > 0 ? 'text-rose-600' : 'text-emerald-700' }}">
                                {{ fmt_money(abs($currentBalance)) }}
                            </td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>

</div>

@endsection
