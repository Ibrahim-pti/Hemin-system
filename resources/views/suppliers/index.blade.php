@extends('layouts.app')
@section('title', 'فرۆشیارەکان')

@section('actions')
    <a href="{{ route('suppliers.create') }}" class="btn btn-primary">فرۆشیاری نوێ</a>
@endsection

@section('content')

<div x-data="{ showDeleteModal: false, deleteUrl: '' }">

    {{-- شریتی گۆڕینی دراو و نرخی ئاڵوگۆڕ --}}
    <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
        <div class="flex items-center gap-2">
            <span class="text-xs font-bold text-slate-500">پیشاندان بە دراو:</span>
            <div class="inline-flex items-center bg-white p-1 rounded-xl border border-slate-200 shadow-2xs">
                <a href="{{ request()->fullUrlWithQuery(['currency' => 'all']) }}"
                   class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all flex items-center gap-1.5 {{ $currency === 'all' ? 'bg-blue-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                    <span>🌐</span>
                    <span>هەمووی (دۆلار و دینار)</span>
                </a>
                <a href="{{ request()->fullUrlWithQuery(['currency' => 'USD']) }}"
                   class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all flex items-center gap-1.5 {{ $currency === 'USD' ? 'bg-emerald-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                    <span>💵</span>
                    <span>تەنها دۆلار ($)</span>
                </a>
                <a href="{{ request()->fullUrlWithQuery(['currency' => 'IQD']) }}"
                   class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all flex items-center gap-1.5 {{ $currency === 'IQD' ? 'bg-indigo-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                    <span>🇮🇶</span>
                    <span>تەنها دینار (د.ع)</span>
                </a>
            </div>
        </div>

        @if ($currentRate > 0)
            <div class="text-xs font-mono font-bold text-slate-600 bg-white px-3.5 py-1.5 rounded-xl border border-slate-200 shadow-2xs flex items-center gap-2">
                <span class="size-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>نرخی ١٠٠$:</span>
                <span class="text-emerald-700 font-black">{{ number_format($currentRate * 100, 0) }} د.ع</span>
            </div>
        @endif
    </div>

    {{-- ١. کارتەکانی کورتە-ئامار بە دیزاینی هاوشێوەی فرۆشتن --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
        {{-- فرۆشیارەکان --}}
        <div class="bg-white rounded-2xl p-5 shadow-xs border border-slate-100 border-r-4 border-r-blue-500 relative flex items-center justify-between overflow-hidden">
            <div>
                <div class="text-3xl font-black text-slate-800 num tracking-tight">{{ fmt_num($suppliers->total()) }}</div>
                <div class="text-xs font-bold text-slate-500 mt-1">کۆی فرۆشیارەکان</div>
            </div>
            <div class="size-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl shrink-0">
                👥
            </div>
        </div>

        {{-- کۆی کڕینەکان --}}
        <div class="bg-white rounded-2xl p-5 shadow-xs border border-slate-100 border-r-4 border-r-emerald-500 relative flex items-center justify-between overflow-hidden">
            <div>
                @if ($currency === 'USD')
                    <div class="text-2xl font-black text-emerald-700 num tracking-tight font-mono">${{ number_format($totalPurchasesUsd, 2) }}</div>
                    <div class="text-xs font-bold text-slate-500 mt-1">کۆی کڕین بە دۆلار</div>
                @elseif ($currency === 'IQD')
                    <div class="text-2xl font-black text-slate-800 num tracking-tight font-mono">{{ fmt_money($totalPurchasesIqd) }}</div>
                    <div class="text-xs font-bold text-slate-500 mt-1">کۆی کڕین بە دینار</div>
                @else
                    <div class="text-2xl font-black text-emerald-700 num tracking-tight font-mono">${{ number_format($totalPurchasesAllInUsd, 2) }}</div>
                    <div class="text-xs font-bold text-slate-500 mt-1 flex items-center gap-1.5">
                        <span>کۆی گشتی کڕینەکان</span>
                        <span class="text-slate-400 font-mono text-2xs">({{ fmt_money($totalPurchasesIqd) }})</span>
                    </div>
                @endif
            </div>
            <div class="size-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl shrink-0">
                🛒
            </div>
        </div>

        {{-- کۆی پارەی دراو --}}
        <div class="bg-white rounded-2xl p-5 shadow-xs border border-slate-100 border-r-4 border-r-teal-500 relative flex items-center justify-between overflow-hidden">
            <div>
                @if ($currency === 'USD')
                    <div class="text-2xl font-black text-teal-700 num tracking-tight font-mono">${{ number_format($totalPaidUsd, 2) }}</div>
                    <div class="text-xs font-bold text-slate-500 mt-1">کۆی دراو بە دۆلار</div>
                @elseif ($currency === 'IQD')
                    <div class="text-2xl font-black text-teal-700 num tracking-tight font-mono">{{ fmt_money($totalPaidIqd) }}</div>
                    <div class="text-xs font-bold text-slate-500 mt-1">کۆی دراو بە دینار</div>
                @else
                    <div class="text-2xl font-black text-teal-700 num tracking-tight font-mono">${{ number_format($totalPaidAllInUsd, 2) }}</div>
                    <div class="text-xs font-bold text-slate-500 mt-1 flex items-center gap-1.5">
                        <span>کۆی پارەی دراو</span>
                        <span class="text-slate-400 font-mono text-2xs">({{ fmt_money($totalPaidIqd) }})</span>
                    </div>
                @endif
            </div>
            <div class="size-12 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center text-xl shrink-0">
                💳
            </div>
        </div>

        {{-- قەرزی ماوەی سەر کارگە --}}
        <div class="bg-white rounded-2xl p-5 shadow-xs border border-slate-100 border-r-4 border-r-rose-500 relative flex items-center justify-between overflow-hidden">
            <div>
                @if ($currency === 'USD')
                    <div class="text-2xl font-black text-rose-600 num tracking-tight font-mono">${{ number_format($totalDebtUsd, 2) }}</div>
                    <div class="text-xs font-bold text-slate-500 mt-1">قەرزی سەر کارگە بە دۆلار</div>
                @elseif ($currency === 'IQD')
                    <div class="text-2xl font-black text-rose-600 num tracking-tight font-mono">{{ fmt_money($totalDebtIqd) }}</div>
                    <div class="text-xs font-bold text-slate-500 mt-1">قەرزی سەر کارگە بە دینار</div>
                @else
                    <div class="text-2xl font-black text-rose-600 num tracking-tight font-mono">${{ number_format($totalDebtUsd, 2) }}</div>
                    <div class="text-xs font-bold text-slate-500 mt-1 flex items-center gap-1.5">
                        <span>قەرزی سەر کارگە</span>
                        <span class="text-slate-400 font-mono text-2xs">({{ fmt_money($totalDebtIqd) }})</span>
                    </div>
                @endif
            </div>
            <div class="size-12 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-xl shrink-0">
                ⚠️
            </div>
        </div>
    </div>

    {{-- ٢. فۆرمی گەڕان بە دیزاینی ستاندارد --}}
    <form method="GET" class="card mb-4">
        <div class="card-body flex gap-3">
            <input type="search" name="q" value="{{ request('q') }}" class="field" placeholder="ناو، مۆبایل یان شوێن...">
            <button class="btn btn-primary">گەڕان</button>
            @if(request('q'))
                <a href="{{ route('suppliers.index') }}" class="btn btn-ghost">پاککردنەوە</a>
            @endif
        </div>
    </form>

    {{-- ٣. خشتەی فرۆشیارەکان --}}
    <div class="card">
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 40px;" class="text-center">#</th>
                        <th>ناوی فرۆشیار</th>
                        <th>مۆبایل</th>
                        <th>شوێن</th>
                        <th class="num">کۆی کڕین</th>
                        <th class="num">کۆی دراو</th>
                        <th class="num">قەرزی ماوە</th>
                        <th style="width: 120px;" class="text-center">کردار</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($suppliers as $idx => $supplier)
                        @php
                            $purchasesTotal = $supplier->totalPurchases();
                            $paidTotal = $supplier->totalPaid();
                            $balance = $supplier->balance();
                        @endphp
                        <tr>
                            {{-- # --}}
                            <td class="text-center text-xs text-[--color-ink-soft]">
                                {{ $suppliers->firstItem() + $idx }}
                            </td>

                            {{-- ناوی فرۆشیار --}}
                            <td>
                                <a href="{{ route('suppliers.show', $supplier) }}" class="font-medium text-[--color-brand-700] hover:underline">
                                    {{ $supplier->name }}
                                </a>
                            </td>

                            {{-- مۆبایل --}}
                            <td class="num" dir="ltr">{{ $supplier->phone ?? '—' }}</td>

                            {{-- شوێن --}}
                            <td class="text-[--color-ink-soft]">{{ $supplier->address ?? '—' }}</td>

                            @php
                                $purchasesTotalUsd = $currentRate > 0 ? round($purchasesTotal / $currentRate, 2) : 0;
                                $paidTotalUsd = $currentRate > 0 ? round($paidTotal / $currentRate, 2) : 0;
                                $balanceUsd = $currentRate > 0 ? round($balance / $currentRate, 2) : 0;
                            @endphp

                            {{-- کۆی کڕینەکان --}}
                            <td class="num font-mono text-center">
                                @if ($currency === 'USD')
                                    <span class="font-bold text-slate-800">${{ number_format($purchasesTotalUsd, 2) }}</span>
                                @elseif ($currency === 'IQD')
                                    <span class="font-bold text-slate-800">{{ fmt_money($purchasesTotal) }}</span>
                                @else
                                    <div class="font-bold text-slate-800">${{ number_format($purchasesTotalUsd, 2) }}</div>
                                    <div class="text-[10px] text-slate-400">({{ fmt_money($purchasesTotal) }})</div>
                                @endif
                            </td>

                            {{-- کۆی پارەی دراو --}}
                            <td class="num font-mono text-center">
                                @if ($currency === 'USD')
                                    <span class="font-bold text-emerald-600">${{ number_format($paidTotalUsd, 2) }}</span>
                                @elseif ($currency === 'IQD')
                                    <span class="font-bold text-emerald-600">{{ fmt_money($paidTotal) }}</span>
                                @else
                                    <div class="font-bold text-emerald-600">${{ number_format($paidTotalUsd, 2) }}</div>
                                    <div class="text-[10px] text-slate-400">({{ fmt_money($paidTotal) }})</div>
                                @endif
                            </td>

                            {{-- قەرزی ماوە --}}
                            <td class="num font-mono text-center">
                                @if ($balance > 0)
                                    @if ($currency === 'USD')
                                        <span class="px-2.5 py-0.5 rounded-md font-mono font-black text-xs bg-rose-50 text-rose-700 border border-rose-200">
                                            ${{ number_format($balanceUsd, 2) }}
                                        </span>
                                    @elseif ($currency === 'IQD')
                                        <span class="px-2.5 py-0.5 rounded-md font-mono font-black text-xs bg-rose-50 text-rose-700 border border-rose-200">
                                            {{ fmt_money($balance) }}
                                        </span>
                                    @else
                                        <div class="inline-flex flex-col items-center">
                                            <span class="px-2.5 py-0.5 rounded-md font-mono font-black text-xs bg-rose-50 text-rose-700 border border-rose-200">
                                                ${{ number_format($balanceUsd, 2) }}
                                            </span>
                                            <span class="text-[10px] text-slate-400 font-mono mt-0.5">({{ fmt_money($balance) }})</span>
                                        </div>
                                    @endif
                                @else
                                    <span class="text-xs text-slate-400 font-medium">پاکتاوە</span>
                                @endif
                            </td>

                            {{-- کردارەکان --}}
                            <td class="text-center">
                                <div class="inline-flex items-center justify-center gap-1.5">
                                    {{-- بینینی کەشف حیساب --}}
                                    <a href="{{ route('suppliers.show', $supplier) }}"
                                       class="inline-flex items-center justify-center size-8 rounded-lg text-blue-600 hover:bg-blue-50 border border-slate-200 transition-colors"
                                       title="کەشف حیساب">
                                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                            <circle cx="12" cy="12" r="3"></circle>
                                        </svg>
                                    </a>

                                    {{-- دەستکاری --}}
                                    <a href="{{ route('suppliers.edit', $supplier) }}"
                                       class="inline-flex items-center justify-center size-8 rounded-lg text-slate-600 hover:bg-slate-100 border border-slate-200 transition-colors"
                                       title="دەستکاری">
                                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                        </svg>
                                    </a>

                                    {{-- سڕینەوە --}}
                                    <button type="button"
                                            @click="deleteUrl = '{{ route('suppliers.destroy', $supplier) }}'; showDeleteModal = true"
                                            class="inline-flex items-center justify-center size-8 rounded-lg text-rose-500 hover:text-rose-700 hover:bg-rose-50 border border-slate-200 transition-colors"
                                            title="سڕینەوە">
                                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <polyline points="3 6 5 6 21 6"></polyline>
                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-sm text-[--color-ink-soft]">هیچ فرۆشیارێک نییە.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($suppliers->hasPages())
        <div class="mt-4">{{ $suppliers->links() }}</div>
    @endif

    {{-- مۆداڵی سڕینەوە لە تەواوی ناوەڕاست --}}
    <template x-teleport="body">
        <div x-show="showDeleteModal"
             x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4"
             style="background: rgba(15, 23, 42, 0.45); backdrop-filter: blur(2px);"
             @keydown.escape.window="showDeleteModal = false">
            
            <div class="bg-white rounded-2xl border border-slate-200 p-5 max-w-xs w-full text-center space-y-4 shadow-xl"
                 @click.away="showDeleteModal = false">
                
                <h3 class="text-sm font-bold text-slate-800 pt-1">ئایا دڵنیایت لە سڕینەوە؟</h3>

                <div class="grid grid-cols-2 gap-2 pt-1">
                    <button type="button" @click="showDeleteModal = false" class="btn btn-ghost !py-2 text-xs font-medium">
                        پاشگەزبوونەوە
                    </button>
                    <form :action="deleteUrl" method="POST" class="w-full">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger w-full !py-2 text-xs font-medium">
                            سڕینەوە
                        </button>
                    </form>
                </div>

            </div>
        </div>
    </template>

</div>

@endsection
