@extends('layouts.app')
@section('title', 'جەردی کۆگا: ' . $count->count_no)

@section('content')

<div x-data="stockCountApp(@js($count->items), {{ $count->status === 'posted' ? 'true' : 'false' }})" class="space-y-4">

    {{-- ── ١. زانیاری سەرەکی جەرد و دوگمەکانی سەرەوە ── --}}
    <div class="card bg-white border border-slate-200 p-4">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            
            {{-- لای ڕاست: زانیاری سەرەکی --}}
            <div class="flex flex-wrap items-center gap-3 sm:gap-6 text-sm">
                <div class="flex items-center gap-2">
                    <span class="flex size-9 items-center justify-center rounded-xl {{ $count->status === 'posted' ? 'bg-emerald-100 text-emerald-700' : 'bg-blue-100 text-blue-700' }}">
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/>
                            <rect x="9" y="3" width="6" height="4" rx="2"/>
                            <path d="M9 14l2 2 4-4"/>
                        </svg>
                    </span>
                    <div>
                        <div class="text-xs text-slate-500 font-medium">ژمارەی جەرد</div>
                        <div class="text-base font-bold text-slate-900 num">{{ $count->count_no }}</div>
                    </div>
                </div>

                <div class="h-8 w-px bg-slate-200 hidden sm:block"></div>

                <div>
                    <div class="text-xs text-slate-500 font-medium">کۆگا</div>
                    <div class="font-bold text-slate-800 flex items-center gap-1 mt-0.5">
                        <svg class="size-3.5 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                        </svg>
                        {{ $count->warehouse?->name }}
                    </div>
                </div>

                <div class="h-8 w-px bg-slate-200 hidden sm:block"></div>

                <div>
                    <div class="text-xs text-slate-500 font-medium">بەرواری جەرد</div>
                    <div class="font-semibold text-slate-800 num mt-0.5">{{ fmt_date($count->count_date) }}</div>
                </div>

                <div class="h-8 w-px bg-slate-200 hidden sm:block"></div>

                <div>
                    <div class="text-xs text-slate-500 font-medium">دۆخ</div>
                    <div class="mt-0.5">
                        @if ($count->status === 'posted')
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                <svg class="size-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <polyline points="20 6 9 17 4 12"/>
                                </svg>
                                پەسەندکراو (جێبەجێکراوە)
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                <span class="size-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                کراوەیە (تۆمارنەکراو لە کۆگا)
                            </span>
                        @endif
                    </div>
                </div>

                @if($count->note)
                    <div class="h-8 w-px bg-slate-200 hidden sm:block"></div>
                    <div>
                        <div class="text-xs text-slate-500 font-medium">تێبینی</div>
                        <div class="text-xs text-slate-700 font-medium mt-0.5">{{ $count->note }}</div>
                    </div>
                @endif
            </div>

            {{-- لای چەپ: دوگمەکانی کردار --}}
            <div class="no-print flex items-center gap-2 shrink-0">
                <button type="button" @click="window.print()" class="btn btn-ghost !py-1.5 !px-3 text-xs gap-1.5 text-slate-700 hover:bg-slate-100" title="چاپکردنی فۆرمی جەرد">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="6 9 6 2 18 2 18 9"/>
                        <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/>
                        <rect x="6" y="14" width="12" height="8"/>
                    </svg>
                    <span>چاپکردن</span>
                </button>

                <a href="{{ route('counts.index') }}" class="btn btn-ghost !py-1.5 !px-3 text-xs gap-1 text-slate-700 hover:bg-slate-100">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M19 12H5M12 19l-7-7 7-7"/>
                    </svg>
                    <span>گەڕانەوە</span>
                </a>

                @if ($count->status !== 'posted')
                    <button type="button" @click="confirmPostModal = true" class="btn btn-primary !py-1.5 !px-3.5 text-xs gap-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold">
                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <polyline points="20 6 9 17 4 12"/>
                        </svg>
                        <span>پەسەندکردن و چاککردنی مەخزەن</span>
                    </button>
                @endif
            </div>

        </div>
    </div>

    {{-- ── ٢. کارتی پێشکەوتن و کورتەی ئامارەکان بە شێوەی Live ── --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
        {{-- کۆی کاڵاکان --}}
        <div class="card p-3.5 bg-white border border-slate-200">
            <div class="text-xs text-slate-500 font-medium">کۆی کاڵاکان</div>
            <div class="text-lg font-bold text-slate-800 num mt-1" x-text="items.length"></div>
        </div>

        {{-- ژمێردراو --}}
        <div class="card p-3.5 bg-white border border-slate-200">
            <div class="flex items-center justify-between text-xs text-slate-500 font-medium">
                <span>ژمێردراو</span>
                <span class="num text-blue-600 font-bold" x-text="progressPercentage + '%'"></span>
            </div>
            <div class="text-lg font-bold text-blue-600 num mt-1" x-text="countedCount"></div>
            {{-- هێڵی پێشکەوتن --}}
            <div class="w-full bg-slate-100 rounded-full h-1.5 mt-2 overflow-hidden">
                <div class="bg-blue-600 h-1.5 rounded-full transition-all duration-300" :style="`width: ${progressPercentage}%`"></div>
            </div>
        </div>

        {{-- ماوە --}}
        <div class="card p-3.5 bg-white border border-slate-200">
            <div class="text-xs text-slate-500 font-medium">ماوە بژمێردرێت</div>
            <div class="text-lg font-bold text-amber-600 num mt-1" x-text="uncountedCount"></div>
        </div>

        {{-- کاڵای یەکسان --}}
        <div class="card p-3.5 bg-white border border-slate-200">
            <div class="text-xs text-slate-500 font-medium">یەکسان لەگەڵ سیستەم</div>
            <div class="text-lg font-bold text-slate-700 num mt-1" x-text="matchedCount"></div>
        </div>

        {{-- جیاوازی (زیادە یان کەم) --}}
        <div class="card p-3.5 bg-white border border-slate-200 col-span-2 sm:col-span-1">
            <div class="text-xs text-slate-500 font-medium">جیاوازی هەبوو (زیادە / کەم)</div>
            <div class="text-lg font-bold num mt-1 flex items-center gap-2">
                <span class="text-emerald-600 text-sm" x-text="`+${surplusCount} زیادە`"></span>
                <span class="text-slate-300">|</span>
                <span class="text-rose-600 text-sm" x-text="`-${deficitCount} کەم`"></span>
            </div>
        </div>
    </div>

    {{-- ── ٣. فۆرمی پاشەکەوتکردن و خشتەی سەرەکی کاڵاکان ── --}}
    <form method="POST" action="{{ route('counts.update', $count) }}" id="count-form">
        @csrf
        @method('PUT')

        <div class="card bg-white border border-slate-200 overflow-hidden">
            
            {{-- هێدەری بەشی خشتە + فلتەرە خێراکان و گەڕان --}}
            <div class="no-print p-4 border-b border-slate-200 bg-slate-50/80 flex flex-col md:flex-row md:items-center justify-between gap-3">
                
                {{-- گەڕانی ناوخۆیی و فلتەری دۆخەکان --}}
                <div class="flex flex-wrap items-center gap-2 flex-1">
                    {{-- خانەی گەڕان بەپێی ناو یان کۆد --}}
                    <div class="relative w-full sm:w-64">
                        <input type="search"
                               x-model="searchQuery"
                               placeholder="گەڕان بەپێی ناوی کاڵا یان کۆد..."
                               class="field bg-white !py-1.5 !pr-8 text-xs">
                        <svg class="size-4 text-slate-400 absolute right-2.5 top-1/2 -translate-y-1/2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8"/>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                        </svg>
                    </div>

                    {{-- دوگمەکانی فلتەر --}}
                    <div class="inline-flex rounded-lg border border-slate-200 bg-white p-0.5 text-xs">
                        <button type="button"
                                @click="filterTab = 'all'"
                                :class="filterTab === 'all' ? 'bg-blue-600 text-white font-bold' : 'text-slate-600 hover:text-slate-900'"
                                class="px-2.5 py-1 rounded-md transition-colors">
                            هەموو (<span x-text="items.length"></span>)
                        </button>
                        <button type="button"
                                @click="filterTab = 'uncounted'"
                                :class="filterTab === 'uncounted' ? 'bg-amber-500 text-white font-bold' : 'text-slate-600 hover:text-slate-900'"
                                class="px-2.5 py-1 rounded-md transition-colors">
                            ماوە (<span x-text="uncountedCount"></span>)
                        </button>
                        <button type="button"
                                @click="filterTab = 'discrepancy'"
                                :class="filterTab === 'discrepancy' ? 'bg-rose-600 text-white font-bold' : 'text-slate-600 hover:text-slate-900'"
                                class="px-2.5 py-1 rounded-md transition-colors">
                            جیاواز (<span x-text="surplusCount + deficitCount"></span>)
                        </button>
                        <button type="button"
                                @click="filterTab = 'matched'"
                                :class="filterTab === 'matched' ? 'bg-emerald-600 text-white font-bold' : 'text-slate-600 hover:text-slate-900'"
                                class="px-2.5 py-1 rounded-md transition-colors">
                            یەکسان (<span x-text="matchedCount"></span>)
                        </button>
                    </div>
                </div>

                {{-- کردارە خێراکان (Quick Tools) ئەگەر جەردەکە تەواونەکرابێت --}}
                @if ($count->status !== 'posted')
                    <div class="flex flex-wrap items-center gap-2">
                        <button type="button"
                                @click="fillAllWithSystem()"
                                class="btn btn-ghost !py-1.5 !px-2.5 text-xs text-blue-700 hover:bg-blue-50 border-blue-200"
                                title="هەموو کاڵاکان بە ژمارەی سیستەم پڕ دەکاتەوە">
                            <span>پڕکردنەوەی هەمووی وەک سیستەم ⚡</span>
                        </button>

                        <button type="button"
                                @click="fillUncountedWithZero()"
                                class="btn btn-ghost !py-1.5 !px-2.5 text-xs text-slate-700 hover:bg-slate-100"
                                title="ئەوانەی بەتاڵن دەیکات بە 0">
                            <span>سفرکردنەوەی ماوەکان (0)</span>
                        </button>

                        <button type="submit" class="btn btn-primary !py-1.5 !px-4 text-xs font-bold gap-1.5">
                            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                                <polyline points="17 21 17 13 7 13 7 21"/>
                                <polyline points="7 3 7 8 15 8"/>
                            </svg>
                            <span>پاشەکەوتکردن</span>
                        </button>
                    </div>
                @endif

            </div>

            {{-- خشتەی کاڵاکان --}}
            <div class="overflow-x-auto">
                <table class="table w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50 text-slate-600 border-b border-slate-200 text-xs">
                            <th class="py-3 px-3 text-center w-12">#</th>
                            <th class="py-3 px-3 text-right">ناوی کاڵا</th>
                            <th class="py-3 px-3 text-right">کۆد / جۆر</th>
                            <th class="py-3 px-3 text-center">یەکە</th>
                            <th class="py-3 px-3 text-center bg-slate-100/70">ژمارەی سیستەم</th>
                            <th class="py-3 px-3 text-center w-44 {{ $count->status !== 'posted' ? 'bg-blue-50/50' : '' }}">
                                ژمێردراو (واقعی)
                            </th>
                            <th class="py-3 px-3 text-center w-36">جیاوازی</th>
                            <th class="py-3 px-3 text-right">تێبینی بۆ ئەم کاڵایە</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <template x-for="(item, index) in filteredItems" :key="item.id">
                            <tr class="hover:bg-slate-50/80 transition-colors"
                                :class="{
                                    'bg-rose-50/30': item.difference !== null && item.difference < -0.0005,
                                    'bg-emerald-50/30': item.difference !== null && item.difference > 0.0005,
                                    'bg-amber-50/20': item.counted_qty === null || item.counted_qty === ''
                                }">
                                
                                {{-- ژمارەی ڕیزبەند --}}
                                <td class="py-2.5 px-3 text-center text-xs text-slate-400 num" x-text="index + 1"></td>

                                {{-- ناوی کاڵا --}}
                                <td class="py-2.5 px-3 font-semibold text-slate-800">
                                    <span x-text="item.item?.name || '—'"></span>
                                </td>

                                {{-- کۆد و کاتێگۆری --}}
                                <td class="py-2.5 px-3 text-xs">
                                    <span class="num font-medium text-slate-600 bg-slate-100 px-2 py-0.5 rounded border border-slate-200" x-text="item.item?.code || '—'"></span>
                                </td>

                                {{-- یەکە --}}
                                <td class="py-2.5 px-3 text-center text-xs text-slate-500 font-medium">
                                    <span x-text="item.item?.unit?.name || 'دانە'"></span>
                                </td>

                                {{-- ژمارەی سیستەم --}}
                                <td class="py-2.5 px-3 text-center num font-bold text-slate-700 bg-slate-50/60">
                                    <span x-text="formatQty(item.system_qty)"></span>
                                </td>

                                {{-- خانەی ژمێردراو --}}
                                <td class="py-2 px-3 text-center {{ $count->status !== 'posted' ? 'bg-blue-50/20' : '' }}">
                                    @if ($count->status === 'posted')
                                        <span class="num font-bold text-slate-900 text-base" x-text="formatQty(item.counted_qty)"></span>
                                    @else
                                        <div class="flex items-center justify-center gap-1">
                                            <input type="number"
                                                   step="any"
                                                   :name="'counted[' + item.id + ']'"
                                                   x-model="item.counted_qty"
                                                   @input="updateItemDiff(item)"
                                                   @keydown.enter.prevent="focusNextInput($event)"
                                                   @keydown.down.prevent="focusNextInput($event)"
                                                   @keydown.up.prevent="focusPrevInput($event)"
                                                   class="count-input field num w-28 !py-1 !px-2 text-center font-bold text-slate-900 text-sm border-slate-300 focus:border-blue-500 focus:bg-white"
                                                   :class="{
                                                       '!border-amber-400 bg-amber-50/40': item.counted_qty === null || item.counted_qty === '',
                                                       '!border-emerald-500 bg-emerald-50/30': item.difference !== null && Math.abs(item.difference) < 0.0005 && item.counted_qty !== null && item.counted_qty !== '',
                                                       '!border-rose-400 bg-rose-50/30': item.difference !== null && item.difference < -0.0005,
                                                       '!border-blue-400 bg-blue-50/30': item.difference !== null && item.difference > 0.0005
                                                   }"
                                                   placeholder="—">
                                            
                                            {{-- دوگمەی خێرای یەکسانکردن بە سیستەم بۆ ئەم ڕیزە --}}
                                            <button type="button"
                                                    @click="matchSystem(item)"
                                                    class="size-7 flex items-center justify-center rounded border border-slate-200 bg-slate-100 hover:bg-blue-100 hover:text-blue-700 text-slate-600 text-xs font-bold transition-colors"
                                                    title="دانانی ژمارەی سیستەم">
                                                =
                                            </button>
                                        </div>
                                    @endif
                                </td>

                                {{-- جیاوازی --}}
                                <td class="py-2.5 px-3 text-center whitespace-nowrap">
                                    <template x-if="item.counted_qty === null || item.counted_qty === ''">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs text-amber-700 bg-amber-50 border border-amber-200">
                                            نەژمێردراوە
                                        </span>
                                    </template>

                                    <template x-if="item.counted_qty !== null && item.counted_qty !== '' && Math.abs(item.difference) < 0.0005">
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-semibold text-emerald-700 bg-emerald-50 border border-emerald-200">
                                            <svg class="size-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                                <polyline points="20 6 9 17 4 12"/>
                                            </svg>
                                            یەکسانە (0)
                                        </span>
                                    </template>

                                    <template x-if="item.counted_qty !== null && item.counted_qty !== '' && item.difference > 0.0005">
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-bold text-emerald-700 bg-emerald-100/70 border border-emerald-300 num">
                                            +<span x-text="formatQty(item.difference)"></span> زیادە
                                        </span>
                                    </template>

                                    <template x-if="item.counted_qty !== null && item.counted_qty !== '' && item.difference < -0.0005">
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-bold text-rose-700 bg-rose-100/70 border border-rose-300 num">
                                            <span x-text="formatQty(item.difference)"></span> کەم
                                        </span>
                                    </template>
                                </td>

                                {{-- تێبینی کاڵا --}}
                                <td class="py-2 px-3">
                                    @if ($count->status === 'posted')
                                        <span class="text-xs text-slate-500" x-text="item.note || '—'"></span>
                                    @else
                                        <input type="text"
                                               :name="'notes[' + item.id + ']'"
                                               x-model="item.note"
                                               class="field bg-white !py-1 !px-2 text-xs border-slate-200"
                                               placeholder="هۆکاری جیاوازی یان تێبینی...">
                                    @endif
                                </td>

                            </tr>
                        </template>

                        <template x-if="filteredItems.length === 0">
                            <tr>
                                <td colspan="8" class="py-8 text-center text-slate-400 text-xs">
                                    هیچ کاڵایەک نەدۆزرایەوە بەپێی ئەم فلتەرە.
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            {{-- ژێرەوەی کارت: دوگمەکانی پاشەکەوتکردن --}}
            @if ($count->status !== 'posted')
                <div class="no-print p-4 border-t border-slate-200 bg-slate-50/80 flex flex-wrap items-center justify-between gap-3">
                    <div class="text-xs text-slate-500 flex items-center gap-1.5">
                        <svg class="size-4 text-blue-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/>
                            <line x1="12" y1="16" x2="12" y2="12"/>
                            <line x1="12" y1="8" x2="12.01" y2="8"/>
                        </svg>
                        <span>دەتوانیت پاشەکەوتی بکەیت و دواتر تەواوی بکەیت، تا کاتێک دوگمەی پەسەندکردن لێدەدەیت هیچ جوڵەیەک لە مەخزەن دروست نابێت.</span>
                    </div>

                    <div class="flex items-center gap-2">
                        <a href="{{ route('counts.index') }}" class="btn btn-ghost !py-2 !px-4 text-xs text-slate-700">پاشگەزبوونەوە</a>
                        <button type="submit" class="btn btn-primary !py-2 !px-6 text-sm font-bold gap-2">
                            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                                <polyline points="17 21 17 13 7 13 7 21"/>
                                <polyline points="7 3 7 8 15 8"/>
                            </svg>
                            <span>پاشەکەوتکردنی ژمارەکان</span>
                        </button>
                    </div>
                </div>
            @endif

        </div>
    </form>

    {{-- ── ٤. مۆداڵی دڵنیابوونەوە لە پەسەندکردن ── --}}
    @if ($count->status !== 'posted')
        <div x-show="confirmPostModal"
             x-cloak
             style="position: fixed; inset: 0; z-index: 50; display: flex; align-items: center; justify-content: center; background: rgba(15,23,42,0.6); backdrop-filter: blur(2px);">
            
            <div class="bg-white rounded-2xl border border-slate-200 p-6 max-w-md w-full mx-4 space-y-4 shadow-xl" @click.away="confirmPostModal = false">
                <div class="flex items-center gap-3">
                    <div class="flex size-12 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700 shrink-0">
                        <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <polyline points="20 6 9 17 4 12"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900">پەسەندکردنی کۆتایی جەرد</h3>
                        <p class="text-xs text-slate-500 mt-0.5">ئەم کردارە ڕاستەوخۆ مەخزەن نوێ دەکاتەوە</p>
                    </div>
                </div>

                {{-- ئاگاداریی ماوەکان ئەگەر هەبێت --}}
                <template x-if="uncountedCount > 0">
                    <div class="rounded-lg bg-rose-50 border border-rose-200 p-3 text-xs text-rose-800 flex items-start gap-2">
                        <svg class="size-4 text-rose-600 shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/>
                            <line x1="12" y1="8" x2="12" y2="12"/>
                            <line x1="12" y1="16" x2="12.01" y2="16"/>
                        </svg>
                        <div>
                            <strong>ئاگاداری:</strong> هێشتا <span class="font-bold text-rose-900" x-text="uncountedCount"></span> کاڵات ژمێردراو نەکردووە. تکایە سەرەتا هەموویان پڕبکەرەوە و پاشەکەوتی بکە.
                        </div>
                    </div>
                </template>

                <div class="text-xs text-slate-600 space-y-2 bg-slate-50 p-3.5 rounded-xl border border-slate-200">
                    <div class="flex justify-between">
                        <span>کۆی کاڵاکان:</span>
                        <strong class="num" x-text="items.length"></strong>
                    </div>
                    <div class="flex justify-between text-emerald-700">
                        <span>کاڵای زیادە (+):</span>
                        <strong class="num" x-text="surplusCount"></strong>
                    </div>
                    <div class="flex justify-between text-rose-700">
                        <span>کاڵای کەم (-):</span>
                        <strong class="num" x-text="deficitCount"></strong>
                    </div>
                    <div class="flex justify-between text-slate-700">
                        <span>کاڵای ڕێک و بێ جیاوازی:</span>
                        <strong class="num" x-text="matchedCount"></strong>
                    </div>
                </div>

                <div class="text-xs text-slate-500 leading-relaxed">
                    دوای پەسەندکردن، جوڵەی نوێی «ڕاستکردنەوە» لە کۆگای <strong>{{ $count->warehouse?->name }}</strong> بەپێی جیاوازییەکان دروست دەبێت و ناتوانرێت دەستکاری بکرێتەوە.
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                    <button type="button" @click="confirmPostModal = false" class="btn btn-ghost !py-2 !px-4 text-xs">
                        پاشگەزبوونەوە
                    </button>

                    <form method="POST" action="{{ route('counts.post', $count) }}" class="inline">
                        @csrf
                        <button type="submit"
                                :disabled="uncountedCount > 0"
                                :class="uncountedCount > 0 ? 'opacity-50 cursor-not-allowed bg-slate-400' : 'bg-emerald-600 hover:bg-emerald-700 text-white'"
                                class="btn !py-2 !px-5 text-xs font-bold">
                            پەسەندکردن و جێبەجێکردن
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @endif

</div>

{{-- ── ٥. سکریپتی کارلێکی Alpine.js بۆ ژماردنی خێرا و ڕاستەوخۆ ── --}}
<script>
function stockCountApp(initialItems, isPosted) {
    return {
        isPosted: isPosted,
        searchQuery: '',
        filterTab: 'all',
        confirmPostModal: false,
        items: initialItems.map(item => {
            let sys = parseFloat(item.system_qty) || 0;
            let cnt = item.counted_qty !== null && item.counted_qty !== undefined ? parseFloat(item.counted_qty) : null;
            let diff = cnt !== null ? (cnt - sys) : null;
            return {
                ...item,
                system_qty: sys,
                counted_qty: item.counted_qty !== null ? item.counted_qty : null,
                difference: diff,
                note: item.note || ''
            };
        }),

        get filteredItems() {
            return this.items.filter(item => {
                // گەڕان بەپێی دەق
                if (this.searchQuery.trim() !== '') {
                    let q = this.searchQuery.toLowerCase();
                    let name = (item.item?.name || '').toLowerCase();
                    let code = (item.item?.code || '').toLowerCase();
                    if (!name.includes(q) && !code.includes(q)) {
                        return false;
                    }
                }

                // فلتەری تابی چالاک
                if (this.filterTab === 'uncounted') {
                    return item.counted_qty === null || item.counted_qty === '';
                }
                if (this.filterTab === 'discrepancy') {
                    return item.counted_qty !== null && item.counted_qty !== '' && Math.abs(item.difference) >= 0.0005;
                }
                if (this.filterTab === 'matched') {
                    return item.counted_qty !== null && item.counted_qty !== '' && Math.abs(item.difference) < 0.0005;
                }

                return true;
            });
        },

        get countedCount() {
            return this.items.filter(i => i.counted_qty !== null && i.counted_qty !== '').length;
        },

        get uncountedCount() {
            return this.items.length - this.countedCount;
        },

        get progressPercentage() {
            if (this.items.length === 0) return 0;
            return Math.round((this.countedCount / this.items.length) * 100);
        },

        get matchedCount() {
            return this.items.filter(i => i.counted_qty !== null && i.counted_qty !== '' && Math.abs(i.difference) < 0.0005).length;
        },

        get surplusCount() {
            return this.items.filter(i => i.counted_qty !== null && i.counted_qty !== '' && i.difference > 0.0005).length;
        },

        get deficitCount() {
            return this.items.filter(i => i.counted_qty !== null && i.counted_qty !== '' && i.difference < -0.0005).length;
        },

        updateItemDiff(item) {
            if (item.counted_qty === null || item.counted_qty === '') {
                item.difference = null;
            } else {
                let cnt = parseFloat(item.counted_qty) || 0;
                item.difference = cnt - item.system_qty;
            }
        },

        matchSystem(item) {
            item.counted_qty = item.system_qty;
            this.updateItemDiff(item);
        },

        fillAllWithSystem() {
            if (confirm('دڵنیایت؟ ژمارەی ژمێردراوی هەموو کاڵاکان هاوتای ژمارەی سیستەم دەکرێت.')) {
                this.items.forEach(item => {
                    item.counted_qty = item.system_qty;
                    this.updateItemDiff(item);
                });
            }
        },

        fillUncountedWithZero() {
            this.items.forEach(item => {
                if (item.counted_qty === null || item.counted_qty === '') {
                    item.counted_qty = 0;
                    this.updateItemDiff(item);
                }
            });
        },

        focusNextInput(event) {
            const inputs = Array.from(document.querySelectorAll('.count-input'));
            const index = inputs.indexOf(event.target);
            if (index > -1 && index < inputs.length - 1) {
                inputs[index + 1].focus();
                inputs[index + 1].select();
            }
        },

        focusPrevInput(event) {
            const inputs = Array.from(document.querySelectorAll('.count-input'));
            const index = inputs.indexOf(event.target);
            if (index > 0) {
                inputs[index - 1].focus();
                inputs[index - 1].select();
            }
        },

        formatQty(val) {
            if (val === null || val === undefined || isNaN(val)) return '—';
            let num = parseFloat(val);
            if (Math.abs(num - Math.round(num)) < 0.0001) {
                return Math.round(num).toString();
            }
            return num.toFixed(3).replace(/\.?0+$/, '');
        }
    };
}
</script>

{{-- ستایلی تایبەت بۆ چاپکردن --}}
<style>
@media print {
    body {
        background: #fff !important;
        font-size: 11pt !important;
    }
    .no-print, header, aside, .btn {
        display: none !important;
    }
    .card {
        border: none !important;
        box-shadow: none !important;
    }
    table {
        width: 100% !important;
        border-collapse: collapse !important;
    }
    th, td {
        border: 1px solid #cbd5e1 !important;
        padding: 6px 8px !important;
    }
    .count-input {
        border: 1px solid #94a3b8 !important;
        width: 100% !important;
        height: 24px !important;
    }
}
</style>

@endsection
