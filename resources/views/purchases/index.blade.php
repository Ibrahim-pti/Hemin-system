@extends('layouts.app')
@section('title', 'پسوولەکانی کڕین')

@section('content')
<div class="space-y-4 sm:space-y-6">

    {{-- ١. هێڵی سەرەوە: ناونیشان و دوگمەی دروستکردنی پسوولەی نوێ --}}
    <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3.5">
            <div class="size-12 rounded-2xl bg-linear-to-br from-teal-500 to-emerald-600 text-white flex items-center justify-center text-2xl shadow-md shadow-emerald-500/20 shrink-0">
                🛒
            </div>
            <div>
                <div class="flex items-center gap-2 flex-wrap">
                    <h1 class="text-lg sm:text-xl font-black text-slate-900">پسوولەکانی کڕین</h1>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-teal-50 text-teal-800 border border-teal-200/80">
                        مەواد و کەلوپەلی هاتوو
                    </span>
                </div>
                <p class="text-xs text-slate-500 mt-1 font-medium">
                    تۆمارکردنی کڕینی کەلوپەل لە فرۆشیاران، پسوولەکان و هاتنی مەواد بۆ کۆگا
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2 self-start sm:self-auto">
            <a href="{{ route('purchases.create') }}"
               class="px-4 py-2.5 rounded-xl text-xs font-black bg-blue-600 hover:bg-blue-700 text-white shadow-md shadow-blue-500/20 inline-flex items-center gap-1.5 transition-all cursor-pointer">
                <span>➕</span>
                <span>پسوولەی کڕینی نوێ</span>
            </a>
        </div>
    </div>

    {{-- ٢. کارتە ئامارییەکان --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs">
            <div class="text-xs font-bold text-slate-500 mb-1 flex items-center gap-1.5">
                <span>📋</span>
                <span>کۆی پسوولەکانی کڕین</span>
            </div>
            <div class="text-2xl sm:text-3xl font-black text-slate-900 font-mono">{{ fmt_num($totalPurchasesCount) }}</div>
        </div>

        <div class="bg-emerald-50/70 rounded-2xl p-4 border border-emerald-200/80 shadow-xs">
            <div class="text-xs font-bold text-emerald-800 mb-1 flex items-center gap-1.5">
                <span>💰</span>
                <span>کۆی پارەی کڕین</span>
            </div>
            <div class="text-xl sm:text-2xl font-black text-emerald-900 font-mono">{{ fmt_money($totalPurchasesAmount) }}</div>
        </div>

        <div class="bg-rose-50/70 rounded-2xl p-4 border border-rose-200/80 shadow-xs">
            <div class="text-xs font-bold text-rose-800 mb-1 flex items-center gap-1.5">
                <span>⏳</span>
                <span>قەرز / پارەی ماوە</span>
            </div>
            <div class="text-xl sm:text-2xl font-black text-rose-800 font-mono">{{ fmt_money($totalRemainingDebt) }}</div>
        </div>

        <div class="bg-amber-50/70 rounded-2xl p-4 border border-amber-200/80 shadow-xs">
            <div class="text-xs font-bold text-amber-800 mb-1 flex items-center gap-1.5">
                <span>📝</span>
                <span>ڕەشنووس / چاوەڕوان</span>
            </div>
            <div class="text-2xl sm:text-3xl font-black text-amber-900 font-mono">{{ fmt_num($draftCount) }}</div>
        </div>
    </div>

    {{-- ٣. فلتەر و گەڕان --}}
    <form method="GET" class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-xs">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 sm:gap-4 items-end">
            <div class="lg:col-span-2">
                <label class="block font-bold text-xs text-slate-600 mb-1.5">🔍 گەڕان</label>
                <input type="search" name="q" value="{{ request('q') }}"
                       class="text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white focus:outline-hidden focus:border-blue-500 w-full font-medium"
                       placeholder="ژمارەی پسوولە یان ناوی فرۆشیار...">
            </div>

            <div>
                <label class="block font-bold text-xs text-slate-600 mb-1.5">دۆخی پسوولە</label>
                <select name="status" class="text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white focus:outline-hidden focus:border-blue-500 w-full font-bold text-slate-700">
                    <option value="">هەموو دۆخەکان</option>
                    @foreach (\App\Models\Purchase::STATUSES as $key => $label)
                        <option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block font-bold text-xs text-slate-600 mb-1.5">لە بەرواری</label>
                <input type="date" name="from" value="{{ request('from') }}"
                       class="text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white focus:outline-hidden focus:border-blue-500 w-full font-mono font-bold text-slate-800">
            </div>

            <div>
                <label class="block font-bold text-xs text-slate-600 mb-1.5">تا بەرواری</label>
                <div class="flex items-center gap-2">
                    <input type="date" name="to" value="{{ request('to') }}"
                           class="text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white focus:outline-hidden focus:border-blue-500 flex-1 font-mono font-bold text-slate-800">
                    <button type="submit"
                            class="px-4 py-2.5 rounded-xl text-xs font-black bg-blue-600 hover:bg-blue-700 text-white shadow-xs transition-all cursor-pointer shrink-0">
                        پاڵاوتن
                    </button>
                    @if(request()->anyFilled(['q', 'status', 'from', 'to']))
                        <a href="{{ route('purchases.index') }}"
                           class="p-2.5 rounded-xl text-xs font-bold bg-slate-100 hover:bg-slate-200 text-slate-600 border border-slate-200 transition-all shrink-0"
                           title="سڕینەوەی فلتەر">
                            ✕
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </form>

    {{-- ٤. خشتەی پسوولەکانی کڕین --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-4 border-b border-slate-100 bg-slate-50/60 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="text-base">📋</span>
                <h3 class="font-black text-sm text-slate-800">لیستی پسوولەکانی کڕین</h3>
            </div>
            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-200/80 text-slate-700 font-mono">
                {{ fmt_num($purchases->total()) }} پسوولە
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead class="bg-slate-50 text-slate-600 border-b border-slate-200 font-black">
                    <tr>
                        <th class="p-3.5 text-center w-28">ژمارەی پسوولە</th>
                        <th class="p-3.5 w-32">بەروار</th>
                        <th class="p-3.5">فرۆشیار / کۆمپانیا</th>
                        <th class="p-3.5">کۆگای وەرگر</th>
                        <th class="p-3.5 text-left w-36">کۆی گشتی</th>
                        <th class="p-3.5 text-left w-36">بڕی ماوە (قەرز)</th>
                        <th class="p-3.5 text-center w-28">دۆخ</th>
                        <th class="p-3.5 text-center w-20">کردار</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($purchases as $purchase)
                        @php
                            $remaining = $purchase->remaining();
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            {{-- ژمارەی پسوولە --}}
                            <td class="p-3.5 text-center font-mono font-black">
                                <a href="{{ route('purchases.show', $purchase) }}"
                                   class="px-2.5 py-1 rounded-lg bg-teal-50 hover:bg-teal-100 text-teal-800 border border-teal-200 inline-block transition-colors shadow-2xs font-bold">
                                    {{ $purchase->invoice_no }}
                                </a>
                            </td>

                            {{-- بەروار --}}
                            <td class="p-3.5 font-mono text-slate-600 whitespace-nowrap">
                                {{ fmt_date($purchase->purchase_date) }}
                            </td>

                            {{-- فرۆشیار --}}
                            <td class="p-3.5">
                                <div class="font-black text-slate-900">
                                    {{ $purchase->supplier?->name ?: 'فرۆشیاری نەناسراو' }}
                                </div>
                                @if($purchase->supplier?->phone)
                                    <div class="text-[11px] text-slate-500 font-mono mt-0.5">
                                        {{ $purchase->supplier->phone }}
                                    </div>
                                @endif
                            </td>

                            {{-- کۆگا --}}
                            <td class="p-3.5 text-slate-600 font-medium">
                                <span class="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 font-bold inline-flex items-center gap-1">
                                    <span>🏬</span>
                                    <span>{{ $purchase->warehouse?->name ?: 'کۆگای سەرەکی' }}</span>
                                </span>
                            </td>

                            {{-- کۆی گشتی --}}
                            <td class="p-3.5 text-left font-mono font-black text-slate-900">
                                {{ fmt_money($purchase->total, $purchase->currency) }}
                            </td>

                            {{-- ماوە --}}
                            <td class="p-3.5 text-left font-mono font-black">
                                @if($remaining > 0)
                                    <span class="text-rose-600 bg-rose-50 px-2 py-0.5 rounded-md border border-rose-100">
                                        {{ fmt_money($remaining, $purchase->currency) }}
                                    </span>
                                @else
                                    <span class="text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-100">
                                        تەواو دراوە ✔️
                                    </span>
                                @endif
                            </td>

                            {{-- دۆخ --}}
                            <td class="p-3.5 text-center">
                                @if($purchase->status === 'confirmed')
                                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        پەسەندکراو ✔️
                                    </span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                        ڕەشنووس ⏳
                                    </span>
                                @endif
                            </td>

                            {{-- کردار --}}
                            <td class="p-3.5 text-center">
                                <a href="{{ route('purchases.show', $purchase) }}"
                                   class="px-2.5 py-1 rounded-lg text-xs font-bold bg-slate-100 hover:bg-blue-50 hover:text-blue-700 text-slate-700 border border-slate-200 transition-all inline-block">
                                    بینین 👁️
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-xs text-slate-400 font-medium">
                                <div class="text-3xl mb-2">🛒</div>
                                <div>هیچ پسوولەیەکی کڕین نەدۆزرایەوە.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- بەشی پەڕەبەندی (Pagination) --}}
        @if ($purchases->hasPages())
            <div class="p-4 border-t border-slate-100 bg-slate-50/60 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
                <div class="font-bold text-slate-600">
                    پێشاندانی <span class="font-mono text-slate-900 font-black">{{ $purchases->firstItem() ?? 0 }}</span> تا <span class="font-mono text-slate-900 font-black">{{ $purchases->lastItem() ?? 0 }}</span> لە کۆی <span class="font-mono text-teal-700 font-black">{{ $purchases->total() }}</span> پسوولە
                </div>

                <div class="flex items-center gap-1.5 self-center sm:self-auto">
                    {{-- پەڕەی پێشوو --}}
                    @if ($purchases->onFirstPage())
                        <span class="px-3 py-1.5 rounded-xl text-xs font-bold bg-slate-100 text-slate-400 cursor-not-allowed">
                            → پێشوو
                        </span>
                    @else
                        <a href="{{ $purchases->previousPageUrl() }}"
                           class="px-3 py-1.5 rounded-xl text-xs font-bold bg-white hover:bg-slate-100 text-slate-700 border border-slate-200 transition-all shadow-2xs">
                            → پێشوو
                        </a>
                    @endif

                    {{-- ژمارەی پەڕەکان --}}
                    @foreach ($purchases->getUrlRange(1, $purchases->lastPage()) as $page => $url)
                        @if ($page == $purchases->currentPage())
                            <span style="min-width: 34px; height: 34px;"
                                  class="px-2.5 py-1 rounded-xl bg-blue-600 text-white font-mono font-black text-xs inline-flex items-center justify-center shadow-xs">
                                {{ $page }}
                            </span>
                        @else
                            <a href="{{ $url }}"
                               style="min-width: 34px; height: 34px;"
                               class="px-2.5 py-1 rounded-xl bg-white hover:bg-slate-100 text-slate-800 font-mono font-bold text-xs border border-slate-300 inline-flex items-center justify-center transition-all">
                                {{ $page }}
                            </a>
                        @endif
                    @endforeach

                    {{-- پەڕەی دواتر --}}
                    @if ($purchases->hasMorePages())
                        <a href="{{ $purchases->nextPageUrl() }}"
                           class="px-3 py-1.5 rounded-xl text-xs font-bold bg-white hover:bg-slate-100 text-slate-700 border border-slate-200 transition-all shadow-2xs">
                            دواتر ←
                        </a>
                    @else
                        <span class="px-3 py-1.5 rounded-xl text-xs font-bold bg-slate-100 text-slate-400 cursor-not-allowed">
                            دواتر ←
                        </span>
                    @endif
                </div>
            </div>
        @endif
    </div>

</div>
@endsection
