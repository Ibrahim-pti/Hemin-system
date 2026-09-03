@extends('layouts.app')
@section('title', 'حیسابی فرۆشیار: ' . $supplier->name)

@section('actions')
    <div class="flex items-center gap-2 no-print">
        <a href="{{ route('suppliers.index') }}" class="btn btn-ghost !py-1.5 !px-3 text-xs gap-1 border border-slate-200 hover:bg-slate-100 font-bold text-slate-700">
            <span>&larr;</span>
            <span>گەڕانەوە</span>
        </a>
        <a href="{{ route('purchases.create', ['supplier' => $supplier->id]) }}" class="btn !py-1.5 !px-3 text-xs font-bold gap-1 bg-blue-600 hover:bg-blue-700 text-white shadow-xs">
            <span>+</span>
            <span>پسوولەی کڕین</span>
        </a>
        <a href="{{ route('payments.create', ['type' => 'out', 'supplier' => $supplier->id]) }}" class="btn !py-1.5 !px-3 text-xs font-bold gap-1 bg-emerald-600 hover:bg-emerald-700 text-white shadow-xs">
            <span>+</span>
            <span>پارەدان (حەقدی)</span>
        </a>
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

    {{-- ١. هێرۆی سەرەوەی پڕۆفایل (Profile Hero Card) --}}
    <div class="bg-white rounded-2xl shadow-xs border border-slate-100 p-6">
        <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6">
            {{-- لایەن و ناوی فرۆشیار --}}
            <div class="flex items-center gap-4">
                <div class="size-16 rounded-2xl bg-gradient-to-br from-blue-500/10 via-indigo-500/10 to-slate-200 border border-blue-200/60 flex items-center justify-center text-3xl font-black text-blue-700 shadow-inner shrink-0">
                    🏢
                </div>
                <div>
                    <div class="flex items-center gap-2.5 flex-wrap">
                        <h2 class="text-xl font-black text-slate-900">{{ $supplier->name }}</h2>
                        <span class="px-2.5 py-0.5 rounded-md text-xs font-mono font-bold text-blue-700 bg-blue-50 border border-blue-200/80">
                            S-{{ str_pad($supplier->id, 5, '0', STR_PAD_LEFT) }}
                        </span>
                        @if($supplier->is_active)
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/60">
                                <span class="size-1.5 rounded-full bg-emerald-500"></span>
                                چالاک
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-bold bg-slate-100 text-slate-600 border border-slate-200">
                                ناچالاک
                            </span>
                        @endif
                    </div>
                    <div class="flex flex-wrap items-center gap-4 mt-2 text-xs font-medium text-slate-600">
                        <span class="flex items-center gap-1.5">
                            <span class="text-slate-400">📱 مۆبایل:</span>
                            <span class="num font-bold text-slate-800" dir="ltr">{{ $supplier->phone ?: '—' }}</span>
                        </span>
                        <span class="text-slate-300">|</span>
                        <span class="flex items-center gap-1.5">
                            <span class="text-slate-400">📍 ناونیشان:</span>
                            <span class="font-bold text-slate-800">{{ $supplier->address ?: '—' }}</span>
                        </span>
                        @if ($supplier->note)
                            <span class="text-slate-300">|</span>
                            <span class="flex items-center gap-1.5">
                                <span class="text-slate-400">📝 تێبینی:</span>
                                <span class="text-slate-700">{{ $supplier->note }}</span>
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- دوگمەی دەستکاری خێرا --}}
            <div class="no-print">
                <a href="{{ route('suppliers.edit', $supplier) }}" class="btn btn-ghost !py-1.5 !px-3.5 text-xs font-bold border border-slate-200 hover:bg-slate-100 text-slate-700 inline-flex items-center gap-1.5">
                    <span>✏️</span>
                    <span>دەستکاری زانیاری</span>
                </a>
            </div>
        </div>

        {{-- کارتەکانی ئاماری تایبەت بەم فرۆشیارە --}}
        <div class="grid gap-4 grid-cols-1 sm:grid-cols-3 mt-6 pt-6 border-t border-slate-100">
            {{-- کۆی کڕینەکان --}}
            <div class="bg-slate-50/70 rounded-xl p-4 border border-slate-100 border-r-4 border-r-indigo-500 text-center">
                <div class="text-2xl font-black text-slate-900 num">{{ fmt_money($totalPurchases) }}</div>
                <div class="text-xs font-bold text-slate-500 mt-1">کۆی گشتی کڕینەکان</div>
            </div>

            {{-- کۆی پارەی دراو --}}
            <div class="bg-slate-50/70 rounded-xl p-4 border border-slate-100 border-r-4 border-r-emerald-500 text-center">
                <div class="text-2xl font-black text-emerald-700 num">{{ fmt_money($totalPaid) }}</div>
                <div class="text-xs font-bold text-slate-500 mt-1">کۆی پارەی دراو ({{ fmt_num($payments->count()) }} جار)</div>
            </div>

            {{-- قەرزی ماوە --}}
            <div class="bg-slate-50/70 rounded-xl p-4 border border-slate-100 border-r-4 {{ $currentBalance > 0 ? 'border-r-rose-500 bg-rose-50/20' : ($currentBalance < 0 ? 'border-r-blue-500' : 'border-r-emerald-500') }} text-center">
                <div class="text-2xl font-black num {{ $currentBalance > 0 ? 'text-rose-600' : ($currentBalance < 0 ? 'text-blue-600' : 'text-emerald-600') }}">
                    {{ fmt_money(abs($currentBalance)) }}
                </div>
                <div class="text-xs font-bold mt-1 {{ $currentBalance > 0 ? 'text-rose-600' : ($currentBalance < 0 ? 'text-blue-600' : 'text-emerald-600') }}">
                    {{ $currentBalance > 0 ? 'قەرزی ماوە لەسەر کارگە' : ($currentBalance < 0 ? 'فرۆشیار قەرزاری کارگەیە' : 'حساب پاکە') }}
                </div>
            </div>
        </div>
    </div>

    {{-- ٢. بەشی تابەکان (Interactive Tabs) --}}
    <div x-data="{ activeTab: 'statement' }" class="space-y-4">

        {{-- تابەکانی سەرەوە --}}
        <div class="bg-white rounded-2xl p-1.5 border border-slate-100 shadow-xs flex items-center gap-1.5 overflow-x-auto no-print">
            <button type="button" @click="activeTab = 'statement'"
                    :class="activeTab === 'statement' ? 'bg-blue-600 text-white shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50 font-medium'"
                    class="px-4 py-2 text-xs rounded-xl transition-all flex items-center gap-2 whitespace-nowrap cursor-pointer">
                <span>📄</span>
                <span>دەفتەری حیسابات (کەشف حیساب)</span>
            </button>

            <button type="button" @click="activeTab = 'purchases'"
                    :class="activeTab === 'purchases' ? 'bg-blue-600 text-white shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50 font-medium'"
                    class="px-4 py-2 text-xs rounded-xl transition-all flex items-center gap-2 whitespace-nowrap cursor-pointer">
                <span>🛒</span>
                <span>پسوولەکانی کڕین و کاڵاکان</span>
                <span :class="activeTab === 'purchases' ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600'" class="px-2 py-0.5 rounded-full text-[10px] font-bold num">
                    {{ $purchases->count() }}
                </span>
            </button>

            <button type="button" @click="activeTab = 'payments'"
                    :class="activeTab === 'payments' ? 'bg-blue-600 text-white shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50 font-medium'"
                    class="px-4 py-2 text-xs rounded-xl transition-all flex items-center gap-2 whitespace-nowrap cursor-pointer">
                <span>💳</span>
                <span>پارەدانەکان (حەقدی)</span>
                <span :class="activeTab === 'payments' ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600'" class="px-2 py-0.5 rounded-full text-[10px] font-bold num">
                    {{ $payments->count() }}
                </span>
            </button>

            @if($jobs->isNotEmpty())
                <button type="button" @click="activeTab = 'jobs'"
                        :class="activeTab === 'jobs' ? 'bg-blue-600 text-white shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50 font-medium'"
                        class="px-4 py-2 text-xs rounded-xl transition-all flex items-center gap-2 whitespace-nowrap cursor-pointer">
                    <span>🔧</span>
                    <span>ئیشی دەرەکی</span>
                    <span :class="activeTab === 'jobs' ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600'" class="px-2 py-0.5 rounded-full text-[10px] font-bold num">
                        {{ $jobs->count() }}
                    </span>
                </button>
            @endif
        </div>

        {{-- ── ١. دەفتەری حیسابات (کەشف حیساب) ── --}}
        <div x-show="activeTab === 'statement'" class="bg-white rounded-2xl shadow-xs border border-slate-100 overflow-hidden">
            <div class="p-4 border-b border-slate-100 flex items-center justify-between flex-wrap gap-2">
                <div class="font-bold text-slate-800 text-sm flex items-center gap-2">
                    <span class="text-blue-600">📄</span>
                    <span>کەشف حیسابی تەواوی مامەڵەکان</span>
                </div>
                <span class="text-xs text-slate-400 font-medium">ڕیزبەندی بەپێی بەروار لەگەڵ باڵانسی ماوە</span>
            </div>

            <div class="overflow-x-auto">
                <table class="table w-full text-right">
                    <thead>
                        <tr class="text-xs text-slate-500 border-b border-slate-100 bg-slate-50/40">
                            <th class="py-3 px-4 text-center w-12">#</th>
                            <th class="py-3 px-4 text-center">بەروار</th>
                            <th class="py-3 px-4">جۆری مامەڵە</th>
                            <th class="py-3 px-4">وردەکاری و کاڵاکان</th>
                            <th class="py-3 px-4 text-center font-bold text-slate-900">کڕین / قەرز (+)</th>
                            <th class="py-3 px-4 text-center font-bold text-emerald-700">پارەی دراو (-)</th>
                            <th class="py-3 px-4 text-center font-bold">باڵانسی ماوە</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        @forelse ($ledger as $index => $item)
                            <tr class="hover:bg-blue-50/40 transition-colors {{ $item->reference ? 'cursor-pointer' : '' }}"
                                @if($item->reference) onclick="if (!event.target.closest('a')) window.open('{{ $item->reference }}', '_blank')" @endif>
                                <td class="py-3.5 px-4 text-center num text-slate-400 font-medium">
                                    {{ $index + 1 }}
                                </td>
                                <td class="py-3.5 px-4 text-center num text-xs text-slate-600 whitespace-nowrap">
                                    {{ fmt_date($item->date) }}
                                </td>
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    @if($item->type === 'purchase')
                                        <div class="inline-flex items-center gap-2">
                                            <a href="{{ $item->reference }}" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold text-blue-700 bg-blue-50 border border-blue-100 hover:bg-blue-100 transition-colors">
                                                <span>🛒</span>
                                                <span>{{ $item->title }}</span>
                                            </a>
                                            @if(!empty($item->image))
                                                <img src="{{ $item->image }}"
                                                     class="size-8 rounded-lg object-cover border border-slate-200 shadow-2xs hover:scale-125 transition-transform cursor-pointer"
                                                     onclick="event.stopPropagation(); window.open('{{ $item->image }}', '_blank')"
                                                     title="کرتە بکە بۆ بینینی وێنەی وەسڵ">
                                            @endif
                                        </div>
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
                                <td class="py-3.5 px-4 text-xs text-slate-600 max-w-md">
                                    {{ $item->details }}
                                </td>
                                <td class="py-3.5 px-4 text-center num font-bold text-slate-900">
                                    @if($item->amount_due > 0)
                                        {{ fmt_money($item->amount_due) }}
                                    @else
                                        <span class="text-slate-300 font-normal">—</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-center num font-bold text-emerald-700">
                                    @if($item->amount_paid > 0)
                                        {{ fmt_money($item->amount_paid) }}
                                    @else
                                        <span class="text-slate-300 font-normal">—</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-center num font-black {{ $item->running_balance > 0 ? 'text-rose-600' : ($item->running_balance < 0 ? 'text-blue-600' : 'text-emerald-600') }}">
                                    {{ fmt_money(abs($item->running_balance)) }}
                                    @if($item->running_balance > 0)
                                        <span class="text-[10px] font-normal text-rose-500 block">لەسەر کارگە</span>
                                    @elseif($item->running_balance < 0)
                                        <span class="text-[10px] font-normal text-blue-500 block">لای فرۆشیار</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-10 text-center text-slate-400 text-sm font-medium">
                                    هیچ مامەڵەیەک بۆ ئەم فرۆشیارە تۆمار نەکراوە.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($ledger->isNotEmpty())
                        <tfoot>
                            <tr class="bg-slate-50 border-t-2 border-slate-200 text-sm">
                                <td colspan="4" class="py-4 px-4 font-black text-slate-800">
                                    کۆی گشتی باڵانس
                                </td>
                                <td class="py-4 px-4 text-center num font-black text-slate-900">
                                    {{ fmt_money($totalPurchases) }}
                                </td>
                                <td class="py-4 px-4 text-center num font-black text-emerald-700">
                                    {{ fmt_money($totalPaid) }}
                                </td>
                                <td class="py-4 px-4 text-center num font-black text-base {{ $currentBalance > 0 ? 'text-rose-600' : ($currentBalance < 0 ? 'text-blue-600' : 'text-emerald-600') }}">
                                    {{ fmt_money(abs($currentBalance)) }}
                                </td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>

        {{-- ── ٢. پسوولەکانی کڕین لەگەڵ کاڵاکان ── --}}
        <div x-show="activeTab === 'purchases'" class="bg-white rounded-2xl shadow-xs border border-slate-100 overflow-hidden">
            <div class="p-4 border-b border-slate-100 flex items-center justify-between flex-wrap gap-2">
                <div class="font-bold text-slate-800 text-sm flex items-center gap-2">
                    <span class="text-blue-600">🛒</span>
                    <span>پسوولەکانی کڕین لەم فرۆشیارە</span>
                </div>
                <a href="{{ route('purchases.create', ['supplier' => $supplier->id]) }}" class="btn !py-1.5 !px-3 text-xs font-bold bg-blue-600 hover:bg-blue-700 text-white rounded-xl shadow-xs no-print">
                    + پسوولەی نوێ
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="table w-full text-right">
                    <thead>
                        <tr class="text-xs text-slate-500 border-b border-slate-100 bg-slate-50/40">
                            <th class="py-3 px-4 text-center w-12">#</th>
                            <th class="py-3 px-4 text-center">بەروار</th>
                            <th class="py-3 px-4">کۆگا</th>
                            <th class="py-3 px-4">کاڵا کڕدراوەکان</th>
                            <th class="py-3 px-4 text-center">کۆی گشتی</th>
                            <th class="py-3 px-4 text-center">پارەی دراو</th>
                            <th class="py-3 px-4 text-center">ماوە (قەرز)</th>
                            <th class="py-3 px-4 text-center w-24 no-print">کردار</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        @forelse ($purchases as $index => $purchase)
                            @php
                                $paid = $purchase->paidTotal();
                                $remaining = $purchase->remaining();
                            @endphp
                            <tr class="hover:bg-blue-50/40 transition-colors">
                                <td class="py-3.5 px-4 text-center num text-slate-400 font-medium">
                                    {{ $index + 1 }}
                                </td>
                                <td class="py-3.5 px-4 text-center num text-xs text-slate-600 whitespace-nowrap">
                                    {{ fmt_date($purchase->purchase_date) }}
                                </td>
                                <td class="py-3.5 px-4 text-xs font-medium text-slate-700">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md bg-slate-100 text-slate-700 border border-slate-200">
                                        <span>🏬</span>
                                        <span>{{ $purchase->warehouse?->name ?? 'کۆگای سەرەکی' }}</span>
                                    </span>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="flex flex-wrap gap-2 items-center">
                                        @if($purchase->imageUrl())
                                            <div class="inline-flex items-center gap-1.5 bg-teal-50 px-2 py-1 rounded-lg border border-teal-200">
                                                <img src="{{ $purchase->imageUrl() }}"
                                                     class="size-7 rounded-md object-cover border border-teal-400 cursor-pointer hover:scale-125 transition-transform"
                                                     onclick="event.stopPropagation(); window.open(this.src, '_blank')"
                                                     title="کرتە بکە بۆ بینینی وێنەی پسوولە">
                                                <span class="text-[11px] font-bold text-teal-800">وێنەی وەسڵ</span>
                                            </div>
                                        @endif
                                        @foreach($purchase->items as $pItem)
                                            <span class="inline-flex items-center gap-1.5 bg-slate-50 px-2 py-1 rounded-lg text-xs border border-slate-200">
                                                @if($pItem->imageUrl())
                                                    <img src="{{ $pItem->imageUrl() }}"
                                                         class="size-6 rounded-md object-cover border border-slate-300 cursor-pointer hover:scale-125 transition-transform"
                                                         onclick="window.open(this.src, '_blank')"
                                                         title="کرتە بکە بۆ بینینی تەواوی وێنە">
                                                @endif
                                                <strong class="text-slate-800">{{ $pItem->item?->name }}</strong>:
                                                <span class="num text-slate-600 font-bold">{{ fmt_qty($pItem->qty) }} {{ $pItem->item?->unit?->name }}</span>
                                                <span class="text-slate-400">×</span>
                                                <span class="num text-slate-700 font-bold">{{ fmt_money($pItem->unit_price, $purchase->currency) }}</span>
                                            </span>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 text-center num font-black text-slate-900">
                                    {{ fmt_money($purchase->total, $purchase->currency) }}
                                </td>
                                <td class="py-3.5 px-4 text-center num font-bold text-emerald-700">
                                    {{ fmt_money($paid) }}
                                </td>
                                <td class="py-3.5 px-4 text-center num font-bold {{ $remaining > 0 ? 'text-rose-600' : 'text-slate-400' }}">
                                    {{ $remaining > 0 ? fmt_money($remaining) : '-' }}
                                </td>
                                <td class="py-3.5 px-4 text-center no-print">
                                    <a href="{{ route('purchases.show', $purchase) }}" class="btn btn-ghost !py-1 !px-2.5 text-xs font-bold text-blue-600 hover:bg-blue-50">
                                        بینین
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-10 text-center text-slate-400 text-sm font-medium">
                                    هیچ پسوولەیەکی کڕین لەم فرۆشیارە تۆمار نەکراوە.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ── ٣. پارەدانەکان (حەقدی) ── --}}
        <div x-show="activeTab === 'payments'" class="bg-white rounded-2xl shadow-xs border border-slate-100 overflow-hidden">
            <div class="p-4 border-b border-slate-100 flex items-center justify-between flex-wrap gap-2">
                <div class="font-bold text-slate-800 text-sm flex items-center gap-2">
                    <span class="text-emerald-600">💳</span>
                    <span>پارەدانەکان بۆ ئەم فرۆشیارە</span>
                </div>
                <a href="{{ route('payments.create', ['type' => 'out', 'supplier' => $supplier->id]) }}" class="btn !py-1.5 !px-3 text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl shadow-xs no-print">
                    + پارەدانی نوێ
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="table w-full text-right">
                    <thead>
                        <tr class="text-xs text-slate-500 border-b border-slate-100 bg-slate-50/40">
                            <th class="py-3 px-4 text-center w-12">#</th>
                            <th class="py-3 px-4 text-center">بەروار</th>
                            <th class="py-3 px-4 text-center">بڕی پارە</th>
                            <th class="py-3 px-4">تێبینی</th>
                            <th class="py-3 px-4 text-center">تۆمارکەر</th>
                            <th class="py-3 px-4 text-center w-24 no-print">کردار</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        @forelse ($payments as $index => $payment)
                            <tr class="hover:bg-emerald-50/30 transition-colors">
                                <td class="py-3.5 px-4 text-center num text-slate-400 font-medium">
                                    {{ $index + 1 }}
                                </td>
                                <td class="py-3.5 px-4 text-center num text-xs text-slate-600 whitespace-nowrap">
                                    {{ fmt_date($payment->paid_at) }}
                                </td>
                                <td class="py-3.5 px-4 text-center num font-black text-emerald-700">
                                    {{ fmt_money($payment->amount, $payment->currency) }}
                                </td>
                                <td class="py-3.5 px-4 text-xs text-slate-600">
                                    {{ $payment->note ?? '—' }}
                                </td>
                                <td class="py-3.5 px-4 text-center text-xs font-medium text-slate-600">
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-slate-100 text-slate-700">
                                        <span>👤</span>
                                        <span>{{ $payment->user?->name ?? 'سیستەم' }}</span>
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-center no-print">
                                    <a href="{{ route('payments.print', $payment) }}" class="btn btn-ghost !py-1 !px-2.5 text-xs font-bold text-emerald-700 hover:bg-emerald-50" target="_blank">
                                        چاپ
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-10 text-center text-slate-400 text-sm font-medium">
                                    هیچ پارەدانێک بۆ ئەم فرۆشیارە تۆمار نەکراوە.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ── ٤. ئیشی دەرەکی (External Jobs) ── --}}
        @if ($jobs->isNotEmpty())
            <div x-show="activeTab === 'jobs'" class="bg-white rounded-2xl shadow-xs border border-slate-100 overflow-hidden">
                <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                    <div class="font-bold text-slate-800 text-sm flex items-center gap-2">
                        <span class="text-purple-600">🔧</span>
                        <span>ئیشی خاریجی تایبەت بەم فرۆشیارە</span>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="table w-full text-right">
                        <thead>
                            <tr class="text-xs text-slate-500 border-b border-slate-100 bg-slate-50/40">
                                <th class="py-3 px-4 text-center">ژمارە</th>
                                <th class="py-3 px-4">ناونیشان</th>
                                <th class="py-3 px-4 text-center">تێچوو</th>
                                <th class="py-3 px-4 text-center">دۆخ</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            @foreach ($jobs as $job)
                                <tr class="hover:bg-purple-50/30 transition-colors">
                                    <td class="py-3.5 px-4 text-center num font-bold text-purple-700">{{ $job->job_no }}</td>
                                    <td class="py-3.5 px-4 font-medium text-slate-800">{{ $job->title }}</td>
                                    <td class="py-3.5 px-4 text-center num font-black text-slate-900">{{ fmt_money($job->cost, $job->currency) }}</td>
                                    <td class="py-3.5 px-4 text-center text-xs">
                                        <span class="px-2.5 py-1 rounded-md font-bold bg-slate-100 text-slate-700">
                                            {{ $job->status_label }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

    </div>

</div>

@endsection

