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

{{-- گەڕان و فلتەر --}}
<form id="filterForm" method="GET" action="{{ route('items.index') }}" class="card mb-6 border-0 ring-1 ring-[--color-line] shadow-sm bg-white rounded-[14px]">
    <input type="hidden" name="sort" id="sortInput" value="{{ request('sort') }}">
    
    <div class="p-3 flex flex-col sm:flex-row items-center gap-3">
        {{-- سێرچ --}}
        <div class="relative flex-1 w-full">
            <input type="search" name="q" value="{{ request('q') }}" 
                   class="field pl-3 pr-10 py-2.5 rounded-xl border-gray-200 focus:border-[--color-brand-500] focus:ring focus:ring-[--color-brand-100] transition-all w-full text-sm bg-gray-50/50 focus:bg-white" 
                   placeholder="گەڕان بەپێی ناوی مەواد...">
            <div class="absolute inset-y-0 right-0 flex items-center pr-3.5 pointer-events-none text-gray-400">
                <svg class="size-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
            </div>
        </div>

        {{-- درۆپ داونی فلتەری جوان --}}
        <div class="relative shrink-0 w-full sm:w-auto" x-data="{ open: false }" @click.outside="open = false">
            <button type="button" @click="open = !open" 
                    class="w-full sm:w-auto inline-flex items-center justify-between gap-2.5 px-4 py-2.5 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 text-gray-700 text-sm font-medium transition-all shadow-sm focus:outline-none focus:ring-2 focus:ring-[--color-brand-500]/20 {{ request('sort') ? 'border-[--color-brand-500] bg-[--color-brand-50]/30 text-[--color-brand-600]' : '' }}">
                <span class="flex items-center gap-2">
                    <svg class="size-4 text-gray-500 {{ request('sort') ? 'text-[--color-brand-600]' : '' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
                    </svg>
                    <span>
                        @if(request('sort') === 'qty_desc')
                            مەوادی زۆر (زۆرترین بڕ)
                        @elseif(request('sort') === 'qty_asc')
                            مەوادی کەم (کەمترین بڕ)
                        @elseif(request('sort') === 'cost_desc')
                            تێچووی کڕین (بەرزترین)
                        @elseif(request('sort') === 'date_desc')
                            بەرواری کڕین (نوێترین)
                        @else
                            فلتەری مەواد
                        @endif
                    </span>
                </span>
                
                <svg class="size-4 text-gray-400 transition-transform duration-200" :class="{ 'rotate-180': open }" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="6 9 12 15 18 9"></polyline>
                </svg>
            </button>

            {{-- لیستی هەڵبژاردنەکان --}}
            <div x-show="open" 
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 scale-95 -translate-y-2"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-100"
                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                 x-transition:leave-end="opacity-0 scale-95 -translate-y-2"
                 style="display: none;"
                 class="absolute left-0 mt-2 w-56 rounded-2xl bg-white p-1.5 shadow-xl ring-1 ring-black/10 z-50 divide-y divide-gray-100">
                
                <div class="py-1">
                    <button type="button" @click="document.getElementById('sortInput').value = ''; document.getElementById('filterForm').submit();"
                            class="w-full flex items-center justify-between px-3 py-2 text-xs font-medium rounded-xl transition-colors {{ !request('sort') ? 'bg-[--color-brand-50] text-[--color-brand-600] font-bold' : 'text-gray-700 hover:bg-gray-50' }}">
                        <span class="flex items-center gap-2">
                            <span class="size-2 rounded-full {{ !request('sort') ? 'bg-[--color-brand-500]' : 'bg-gray-300' }}"></span>
                            هەموو مەوادەکان
                        </span>
                        @if(!request('sort'))
                            <svg class="size-4 text-[--color-brand-600]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        @endif
                    </button>
                </div>

                <div class="py-1 space-y-0.5">
                    <div class="px-3 py-1 text-[10px] font-bold text-gray-400 uppercase tracking-wider text-right">فلتەری بڕی کۆگا</div>
                    <button type="button" @click="document.getElementById('sortInput').value = 'qty_desc'; document.getElementById('filterForm').submit();"
                            class="w-full flex items-center justify-between px-3 py-2 text-xs font-medium rounded-xl transition-colors {{ request('sort') === 'qty_desc' ? 'bg-emerald-50 text-emerald-700 font-bold' : 'text-gray-700 hover:bg-gray-50' }}">
                        <span class="flex items-center gap-2">
                            <svg class="size-4 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 19V5M5 12l7-7 7 7"/></svg>
                            مەوادی زۆر (زۆرترین بڕ)
                        </span>
                        @if(request('sort') === 'qty_desc')
                            <svg class="size-4 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        @endif
                    </button>

                    <button type="button" @click="document.getElementById('sortInput').value = 'qty_asc'; document.getElementById('filterForm').submit();"
                            class="w-full flex items-center justify-between px-3 py-2 text-xs font-medium rounded-xl transition-colors {{ request('sort') === 'qty_asc' ? 'bg-amber-50 text-amber-700 font-bold' : 'text-gray-700 hover:bg-gray-50' }}">
                        <span class="flex items-center gap-2">
                            <svg class="size-4 text-amber-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12l7 7 7-7"/></svg>
                            مەوادی کەم (کەمترین بڕ)
                        </span>
                        @if(request('sort') === 'qty_asc')
                            <svg class="size-4 text-amber-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        @endif
                    </button>
                </div>

                <div class="py-1 space-y-0.5">
                    <div class="px-3 py-1 text-[10px] font-bold text-gray-400 uppercase tracking-wider text-right">هەڵبژاردەی تر</div>
                    @can('view_reports')
                        <button type="button" @click="document.getElementById('sortInput').value = 'cost_desc'; document.getElementById('filterForm').submit();"
                                class="w-full flex items-center justify-between px-3 py-2 text-xs font-medium rounded-xl transition-colors {{ request('sort') === 'cost_desc' ? 'bg-blue-50 text-blue-700 font-bold' : 'text-gray-700 hover:bg-gray-50' }}">
                            <span class="flex items-center gap-2">
                                <svg class="size-4 text-blue-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                                بەرزترین تێچوو
                            </span>
                            @if(request('sort') === 'cost_desc')
                                <svg class="size-4 text-blue-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            @endif
                        </button>
                    @endcan

                    <button type="button" @click="document.getElementById('sortInput').value = 'date_desc'; document.getElementById('filterForm').submit();"
                            class="w-full flex items-center justify-between px-3 py-2 text-xs font-medium rounded-xl transition-colors {{ request('sort') === 'date_desc' ? 'bg-purple-50 text-purple-700 font-bold' : 'text-gray-700 hover:bg-gray-50' }}">
                        <span class="flex items-center gap-2">
                            <svg class="size-4 text-purple-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                            نوێترین بەرواری کڕین
                        </span>
                        @if(request('sort') === 'date_desc')
                            <svg class="size-4 text-purple-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        @endif
                    </button>
                </div>
            </div>
        </div>

        @if(request('q') || request('sort'))
            <a href="{{ route('items.index') }}" class="btn btn-ghost text-xs text-gray-500 !py-2 shrink-0 flex items-center gap-1.5">
                <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                پاککردنەوە
            </a>
        @endif
    </div>
</form>

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
