@extends('layouts.app')
@section('title', 'کۆگا و بەشەکانی کارگە')

@section('content')
<div class="space-y-4 sm:space-y-6">

    {{-- ١. هێڵی سەرەوە: ناونیشان و دوگمەکانی کردار --}}
    <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-xs flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div class="flex items-center gap-3.5">
            <div class="size-12 rounded-2xl bg-linear-to-br from-amber-500 to-orange-600 text-white flex items-center justify-center text-2xl shadow-md shadow-orange-500/20 shrink-0">
                🏬
            </div>
            <div>
                <div class="flex items-center gap-2 flex-wrap">
                    <h1 class="text-lg sm:text-xl font-black text-slate-900">کۆگا و بەشەکانی کارگە</h1>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-orange-50 text-orange-700 border border-orange-200/80">
                        مەعمەل و فرۆشتن
                    </span>
                </div>
                <p class="text-xs text-slate-500 mt-1 font-medium">
                    بەڕێوەبردنی مەعمەلی دروستکردن (مەوادی خاو و وەستاکان) و کۆگای سەرەکی (کاڵای ئامادە و فرۆشتن)
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('counts.index') }}"
               class="px-4 py-2 rounded-xl text-xs font-bold bg-slate-100 hover:bg-slate-200 text-slate-800 border border-slate-300 inline-flex items-center gap-1.5 transition-all">
                <span>📋</span>
                <span>جەردی کۆگا</span>
            </a>

            <a href="{{ route('warehouses.create') }}"
               class="px-4 py-2 rounded-xl text-xs font-bold bg-orange-600 hover:bg-orange-700 text-white inline-flex items-center gap-1.5 transition-all shadow-sm">
                <span>+</span>
                <span>زیادکردنی شوێنی نوێ</span>
            </a>
        </div>
    </div>

    {{-- ٢. کارتەکانی ئاماری سەرەکی --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4">
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs">
            <div class="text-xs font-bold text-slate-500 mb-1">کۆی شوێنەکانی کارگە</div>
            <div class="text-2xl font-black text-slate-900 font-mono">{{ count($warehouses) }} شوێن</div>
        </div>

        <div class="bg-blue-50/70 rounded-2xl p-4 border border-blue-200/80 shadow-xs">
            <div class="text-xs font-bold text-blue-800 mb-1 flex items-center gap-1.5">
                <span>📦</span>
                <span>کۆی جوڵەکانی مەخزەن</span>
            </div>
            <div class="text-2xl font-black text-blue-800 font-mono">{{ fmt_num($totalMovements) }}</div>
        </div>

        <div class="bg-emerald-50/70 rounded-2xl p-4 border border-emerald-200/80 shadow-xs">
            <div class="text-xs font-bold text-emerald-800 mb-1 flex items-center gap-1.5">
                <span class="size-2 rounded-full bg-emerald-500"></span>
                <span>باری کارکردن</span>
            </div>
            <div class="text-lg font-black text-emerald-700">سەرجەم بەشەکان چالاکن ✔️</div>
        </div>
    </div>

    {{-- ٣. دوو کارتی سەرەکی تایبەت (مەعمەل + کۆگای فرۆشتن) --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
        @foreach($warehouses as $wh)
            @php
                $isProduction = str_contains($wh->name, 'دروستکردن') || str_contains($wh->name, 'مەعمەل') || str_contains($wh->name, 'کارگە');
            @endphp

            @if($isProduction)
                {{-- کارتی مەعمەلی دروستکردن --}}
                <div class="bg-linear-to-br from-white via-indigo-50/30 to-blue-50/40 rounded-3xl p-5 sm:p-6 border-2 border-indigo-200 shadow-sm relative overflow-hidden flex flex-col justify-between">
                    <div class="space-y-4">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <div class="size-14 rounded-2xl bg-indigo-600 text-white flex items-center justify-center text-3xl shadow-md shadow-indigo-600/30 shrink-0">
                                    🏭
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h2 class="text-base sm:text-lg font-black text-slate-900">{{ $wh->name }}</h2>
                                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-indigo-100 text-indigo-800 border border-indigo-200">
                                            مەعمەلی پیشەسازی
                                        </span>
                                    </div>
                                    <p class="text-xs text-slate-500 font-medium mt-0.5">شوێنی کارکردن، بڕین، لەحیم و مەوادی خاو</p>
                                </div>
                            </div>

                            <a href="{{ route('warehouses.edit', $wh) }}"
                               class="size-8 rounded-xl bg-white border border-slate-200 hover:bg-slate-100 flex items-center justify-center text-xs font-bold text-slate-600 transition-colors shadow-2xs"
                               title="دەستکاری ناونیشان و زانیاری">
                                ✏️
                            </a>
                        </div>

                        <div class="bg-white/80 backdrop-blur-xs rounded-2xl p-4 border border-indigo-100/80 space-y-2">
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-bold text-slate-500">ناونیشان / شوێن:</span>
                                <span class="font-bold text-slate-800 text-left">{{ $wh->location ?: 'شەقامی کارگە' }}</span>
                            </div>
                            <div class="flex items-center justify-between text-xs pt-2 border-t border-slate-100">
                                <span class="font-bold text-slate-500">جوڵەی مەوادەکان:</span>
                                <span class="font-mono font-black text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded-md border border-indigo-200/60">
                                    {{ fmt_num($wh->movements_count) }} جوڵە
                                </span>
                            </div>
                            <div class="flex items-center justify-between text-xs pt-2 border-t border-slate-100">
                                <span class="font-bold text-slate-500">دۆخی کارگە:</span>
                                <span class="text-emerald-700 font-bold flex items-center gap-1">
                                    <span class="size-2 rounded-full bg-emerald-500"></span>
                                    چالاکە بۆ دروستکردن و مەواد
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- بەستەرە ڕاستەوخۆکان بۆ بینینی هەموو داتاکانی مەعمەل و وەستاکان --}}
                    <div class="pt-5 border-t border-indigo-100/60 mt-4 space-y-2">
                        <div class="text-xs font-black text-slate-700 flex items-center gap-1.5 mb-2">
                            <span>⚡</span>
                            <span>داتاکان و بەشەکانی مەعمەل:</span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            <a href="{{ route('workshop.employees') }}"
                               class="px-3.5 py-2.5 rounded-xl text-xs font-black bg-indigo-600 hover:bg-indigo-700 text-white shadow-xs inline-flex items-center justify-center gap-1.5 transition-all">
                                <span>👷</span>
                                <span>وەستا و حەمەڵەکان</span>
                            </a>

                            @if(Route::has('reports.workshop-production'))
                                <a href="{{ route('reports.workshop-production') }}"
                                   class="px-3.5 py-2.5 rounded-xl text-xs font-bold bg-white hover:bg-indigo-50 text-indigo-700 border border-indigo-200 inline-flex items-center justify-center gap-1.5 transition-all">
                                    <span>⚙️</span>
                                    <span>ڕاپۆرتی دروستکردن</span>
                                </a>
                            @endif

                            @if(Route::has('reports.workshop-materials'))
                                <a href="{{ route('reports.workshop-materials') }}"
                                   class="px-3.5 py-2.5 rounded-xl text-xs font-bold bg-white hover:bg-indigo-50 text-indigo-700 border border-indigo-200 inline-flex items-center justify-center gap-1.5 transition-all">
                                    <span>🧱</span>
                                    <span>مەوادی خاو و کۆگا</span>
                                </a>
                            @endif

                            <a href="{{ route('workshop.index') }}"
                               class="px-3.5 py-2.5 rounded-xl text-xs font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 inline-flex items-center justify-center gap-1.5 transition-all">
                                <span>📊</span>
                                <span>داشبۆردی مەعمەل</span>
                            </a>
                        </div>
                    </div>
                </div>

            @else
                {{-- کارتی کۆگای سەرەکی و فرۆشتن --}}
                <div class="bg-linear-to-br from-white via-emerald-50/30 to-teal-50/40 rounded-3xl p-5 sm:p-6 border-2 border-emerald-200 shadow-sm relative overflow-hidden flex flex-col justify-between">
                    <div class="space-y-4">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <div class="size-14 rounded-2xl bg-emerald-600 text-white flex items-center justify-center text-3xl shadow-md shadow-emerald-600/30 shrink-0">
                                    🏬
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h2 class="text-base sm:text-lg font-black text-slate-900">{{ $wh->name }}</h2>
                                        @if($wh->is_default)
                                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                                کۆگای بنەڕەت ⭐
                                            </span>
                                        @endif
                                    </div>
                                    <p class="text-xs text-slate-500 font-medium mt-0.5">شوێنی هەڵگرتنی کاڵای ئامادە، کەلوپەل و فرۆشتن</p>
                                </div>
                            </div>

                            <a href="{{ route('warehouses.edit', $wh) }}"
                               class="size-8 rounded-xl bg-white border border-slate-200 hover:bg-slate-100 flex items-center justify-center text-xs font-bold text-slate-600 transition-colors shadow-2xs"
                               title="دەستکاری">
                                ✏️
                            </a>
                        </div>

                        <div class="bg-white/80 backdrop-blur-xs rounded-2xl p-4 border border-emerald-100/80 space-y-2">
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-bold text-slate-500">ناونیشان / شوێن:</span>
                                <span class="font-bold text-slate-800 text-left">{{ $wh->location ?: 'پێشانگای سەرەکی' }}</span>
                            </div>
                            <div class="flex items-center justify-between text-xs pt-2 border-t border-slate-100">
                                <span class="font-bold text-slate-500">جوڵەی فرۆشتن و کڕین:</span>
                                <span class="font-mono font-black text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-200/60">
                                    {{ fmt_num($wh->movements_count) }} جوڵە
                                </span>
                            </div>
                            <div class="flex items-center justify-between text-xs pt-2 border-t border-slate-100">
                                <span class="font-bold text-slate-500">دۆخ:</span>
                                <span class="text-emerald-700 font-bold flex items-center gap-1">
                                    <span class="size-2 rounded-full bg-emerald-500"></span>
                                    چالاکە بۆ وەسڵەکانی فرۆشتن
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- بەستەرە ڕاستەوخۆکان بۆ بینینی کەلوپەل و فرۆشتن --}}
                    <div class="pt-5 border-t border-emerald-100/60 mt-4 space-y-2">
                        <div class="text-xs font-black text-slate-700 flex items-center gap-1.5 mb-2">
                            <span>⚡</span>
                            <span>داتاکان و بەشەکانی فرۆشتن:</span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            <a href="{{ route('counts.index') }}"
                               class="px-3.5 py-2.5 rounded-xl text-xs font-black bg-emerald-600 hover:bg-emerald-700 text-white shadow-xs inline-flex items-center justify-center gap-1.5 transition-all">
                                <span>📊</span>
                                <span>جەردی کەلوپەلەکان</span>
                            </a>

                            <a href="{{ route('orders.index') }}"
                               class="px-3.5 py-2.5 rounded-xl text-xs font-bold bg-white hover:bg-emerald-50 text-emerald-700 border border-emerald-200 inline-flex items-center justify-center gap-1.5 transition-all">
                                <span>🛒</span>
                                <span>وەسڵەکانی فرۆشتن</span>
                            </a>

                            <a href="{{ route('items.index') }}"
                               class="px-3.5 py-2.5 rounded-xl text-xs font-bold bg-white hover:bg-emerald-50 text-emerald-700 border border-emerald-200 inline-flex items-center justify-center gap-1.5 transition-all">
                                <span>📦</span>
                                <span>فەرهەنگی کەلوپەل</span>
                            </a>

                            <a href="{{ route('customers.index') }}"
                               class="px-3.5 py-2.5 rounded-xl text-xs font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 inline-flex items-center justify-center gap-1.5 transition-all">
                                <span>👥</span>
                                <span>کڕیارانی کارگە</span>
                            </a>
                        </div>
                    </div>
                </div>
            @endif
        @endforeach
    </div>

    {{-- ٤. خشتەی گشتی هەموو کۆگاکان --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between gap-3 bg-slate-50/60">
            <div class="flex items-center gap-2">
                <span class="text-base">📋</span>
                <h3 class="font-black text-sm text-slate-800">تۆماری سەرجەم کۆگا و شوێنەکان</h3>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead class="bg-slate-50 text-slate-600 border-b border-slate-200 font-black">
                    <tr>
                        <th class="p-3.5 w-12 text-center">#</th>
                        <th class="p-3.5">ناوی کۆگا / شوێن</th>
                        <th class="p-3.5">ناونیشان</th>
                        <th class="p-3.5 text-center">کۆی جوڵەکان</th>
                        <th class="p-3.5 text-center">دۆخ</th>
                        <th class="p-3.5 text-center w-28">کردار</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($warehouses as $index => $warehouse)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="p-3.5 text-center font-mono font-bold text-slate-400">
                                {{ $index + 1 }}
                            </td>
                            <td class="p-3.5 font-bold text-slate-900">
                                <div class="flex items-center gap-2">
                                    <span>{{ $warehouse->name }}</span>
                                    @if ($warehouse->is_default)
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                            بنەڕەت
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="p-3.5 text-slate-600 font-medium">
                                {{ $warehouse->location ?: '—' }}
                            </td>
                            <td class="p-3.5 text-center font-mono font-bold text-slate-700">
                                {{ fmt_num($warehouse->movements_count) }}
                            </td>
                            <td class="p-3.5 text-center">
                                @if($warehouse->is_active)
                                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        چالاک ✔️
                                    </span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-rose-100 text-rose-800 border border-rose-200">
                                        ناچالاک ❌
                                    </span>
                                @endif
                            </td>
                            <td class="p-3.5 text-center">
                                <a href="{{ route('warehouses.edit', $warehouse) }}"
                                   class="px-2.5 py-1 rounded-lg text-xs font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 transition-all">
                                    ✏️ دەستکاری
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-10 text-center text-slate-400 font-medium">هیچ کۆگایەک نییە.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
