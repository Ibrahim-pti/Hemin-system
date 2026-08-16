@extends('layouts.app')
@section('title', 'دۆخی کۆگا')

@section('actions')
    @can('manage_items')
        <a href="{{ route('items.create') }}" class="btn btn-primary shadow-sm hover:-translate-y-0.5 hover:shadow-md transition-all duration-200 relative overflow-hidden group">
            <span class="absolute inset-0 bg-white/20 translate-y-full group-hover:translate-y-0 transition-transform duration-300 ease-out"></span>
            <span class="relative flex items-center gap-1.5">
                <svg class="size-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                زیادکردنی مەواد
            </span>
        </a>
    @endcan
@endsection

@section('content')

{{-- فلتەرکردنی مەوادەکان --}}
<div class="card mb-6 border-0 ring-1 ring-[--color-line] shadow-sm bg-white rounded-2xl p-5">
    <div class="flex items-center justify-between mb-4 border-b border-gray-100 pb-3">
        <h2 class="text-sm font-bold text-gray-800 flex items-center gap-2">
            <svg class="size-4 text-[--color-brand-600]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
            </svg>
            فلتەرکردنی مەوادەکان
        </h2>
    </div>

    <form method="GET" action="{{ route('items.index') }}">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 items-end">
            {{-- ناوی مەواد --}}
            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1.5 text-right">ناوی مەواد</label>
                <input type="text" name="q" value="{{ request('q') }}" 
                       class="field px-3.5 py-2 rounded-xl border-gray-200 focus:border-[--color-brand-500] focus:ring focus:ring-[--color-brand-100] transition-all w-full text-sm bg-gray-50/60 focus:bg-white" 
                       placeholder="ناوی مەواد بنووسە...">
            </div>

            {{-- فلتەری بڕ --}}
            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1.5 text-right">فلتەری بڕ</label>
                <select name="qty_filter" 
                        class="field py-2 px-3 rounded-xl border-gray-200 focus:border-[--color-brand-500] focus:ring focus:ring-[--color-brand-100] transition-all w-full text-sm bg-gray-50/60 focus:bg-white text-gray-700 font-medium">
                    <option value="">هەموو مەوادەکان</option>
                    <option value="qty_desc" @selected(request('qty_filter') === 'qty_desc' || request('sort') === 'qty_desc')>مەوادی زۆر (زۆرترین بڕ)</option>
                    <option value="qty_asc" @selected(request('qty_filter') === 'qty_asc' || request('sort') === 'qty_asc')>مەوادی کەم (کەمترین بڕ)</option>
                </select>
            </div>

            {{-- دوگمەی فلتەرکردن و پاککردنەوە --}}
            <div class="flex items-center gap-2">
                <button type="submit" 
                        class="btn btn-primary flex-1 py-2 px-4 rounded-xl text-sm font-semibold flex items-center justify-center gap-2 shadow-sm hover:shadow transition-all">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
                    </svg>
                    فلتەرکردن
                </button>

                @if(request()->hasAny(['q', 'qty_filter', 'sort']))
                    <a href="{{ route('items.index') }}" 
                       class="btn bg-gray-100 hover:bg-gray-200 text-gray-700 border border-gray-200/80 py-2 px-3.5 rounded-xl text-sm font-medium flex items-center justify-center gap-1.5 transition-all"
                       title="پاککردنەوەی فلتەر">
                        <svg class="size-4 text-gray-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                        پاککردنەوە
                    </a>
                @endif
            </div>
        </div>
    </form>
</div>

{{-- خشتە --}}
<div class="card border-0 ring-1 ring-[--color-line] shadow-sm rounded-[14px] overflow-hidden bg-white">
    <div class="overflow-x-auto">
        <table class="table w-full text-sm text-right border-collapse">
            <thead class="bg-[--color-surface-soft]/80 text-[--color-ink-soft] text-[13px] border-b border-[--color-line]">
                <tr>
                    <th class="px-5 py-4 whitespace-nowrap font-semibold text-right">ناوی مەواد</th>
                    <th class="px-5 py-4 whitespace-nowrap font-semibold text-right">بڕ</th>
                    @can('view_reports')
                        <th class="px-5 py-4 whitespace-nowrap font-semibold text-right">تێچووی کڕین</th>
                    @endcan
                    <th class="px-5 py-4 whitespace-nowrap font-semibold text-right">بەرواری کڕین</th>
                    <th class="px-5 py-4 whitespace-nowrap font-semibold text-center">کردار</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[--color-line]/60">
                @forelse ($items as $item)
                    <tr class="hover:bg-blue-50/30 transition-colors duration-150 group">
                        <td class="px-5 py-3.5 align-middle text-right">
                            <div class="flex items-center gap-3.5">
                                <div class="size-10 rounded-[10px] bg-gradient-to-br from-[--color-brand-50] to-blue-100/50 text-[--color-brand-600] flex items-center justify-center border border-[--color-brand-100] shrink-0 group-hover:scale-105 group-hover:shadow-sm transition-all duration-300">
                                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                                        <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                                        <line x1="12" y1="22.08" x2="12" y2="12"></line>
                                    </svg>
                                </div>
                                <div class="flex flex-col justify-center">
                                    <a href="{{ route('items.show', $item) }}" class="font-bold text-[--color-ink] hover:text-[--color-brand-600] transition-colors text-[15px] leading-snug">
                                        {{ $item->name }}
                                    </a>
                                    @unless ($item->is_active)
                                        <span class="inline-flex items-center gap-1.5 text-[10px] font-bold px-1.5 py-0.5 rounded text-red-700 bg-red-50 border border-red-100 mt-1 w-max">
                                            <span class="size-1.5 rounded-full bg-red-500 animate-pulse"></span> ناچالاک
                                        </span>
                                    @endunless
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-3.5 align-middle text-right">
                            <div class="flex flex-col items-start">
                                <span class="font-bold text-[15px] text-gray-800 num">
                                    {{ fmt_qty($item->min_qty) }}
                                </span>
                                <span class="text-[11px] text-[--color-ink-soft] font-medium">{{ $item->unit?->name }}</span>
                            </div>
                        </td>
                        @can('view_reports')
                            <td class="px-5 py-3.5 align-middle text-right font-medium text-gray-600 text-[13px]">
                                <span class="num font-bold text-[14px] text-gray-800">{{ $item->last_cost ? number_format((float)$item->last_cost, 0, '.', ',') : '—' }}</span>
                                @if($item->last_cost)
                                    <span class="text-xs text-gray-400 font-normal">د.ع</span>
                                @endif
                            </td>
                        @endcan
                        <td class="px-5 py-3.5 align-middle text-right text-[13px] text-gray-600 font-medium">
                            @if ($item->purchase_date)
                                <span class="num bg-gray-50 border border-gray-200/80 px-2 py-1 rounded-md text-xs text-gray-700 font-medium">
                                    {{ $item->purchase_date->format('Y/m/d') }}
                                </span>
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 align-middle text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                @can('manage_items')
                                    <a href="{{ route('items.edit', $item) }}" 
                                       class="inline-flex items-center justify-center size-8 rounded-lg text-blue-600 hover:bg-blue-50 transition-colors bg-white border border-gray-200 shadow-sm hover:border-blue-200" 
                                       title="دەستکاری">
                                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path>
                                        </svg>
                                    </a>

                                    <form method="POST" action="{{ route('items.destroy', $item) }}" 
                                          onsubmit="return confirm('دڵنیایت لە سڕینەوەی ئەم مەوادە؟')"
                                          class="inline-block">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="inline-flex items-center justify-center size-8 rounded-lg text-red-500 hover:text-red-700 hover:bg-red-50 transition-colors bg-white border border-gray-200 shadow-sm hover:border-red-200" 
                                                title="سڕینەوە">
                                            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <polyline points="3 6 5 6 21 6"></polyline>
                                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                            </svg>
                                        </button>
                                    </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-5 py-24 text-center bg-gray-50/30">
                            <div class="inline-flex flex-col items-center justify-center text-gray-400 max-w-sm mx-auto">
                                <div class="size-20 rounded-full bg-white shadow-sm flex items-center justify-center mb-5 border border-gray-100">
                                    <svg class="size-10 text-gray-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                                        <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                                        <line x1="12" y1="22.08" x2="12" y2="12"></line>
                                    </svg>
                                </div>
                                <h3 class="text-lg font-bold text-gray-700 mb-2">هیچ بابەتێک نەدۆزرایەوە</h3>
                                <p class="text-[13px] text-gray-500 leading-relaxed mb-6">داتایەک بۆ پیشاندان نییە. گەڕانەکەت بگۆڕە یان کلیک لە دوگمەی خوارەوە بکە بۆ زیادکردنی بابەت.</p>
                                @can('manage_items')
                                    <a href="{{ route('items.create') }}" class="btn bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 hover:border-gray-300 shadow-sm transition-all rounded-lg text-sm px-5 py-2.5">
                                        زیادکردنی بابەت
                                    </a>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if ($items->hasPages())
    <div class="mt-5 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-gray-500">
        <div class="font-medium">
            پیشاندانی <span class="font-bold text-gray-800 num">{{ $items->firstItem() ?? 0 }}</span> تا <span class="font-bold text-gray-800 num">{{ $items->lastItem() ?? 0 }}</span> لە کۆی <span class="font-bold text-gray-800 num">{{ $items->total() }}</span> مەواد
        </div>
        <div>
            {{ $items->links() }}
        </div>
    </div>
@endif

@endsection
