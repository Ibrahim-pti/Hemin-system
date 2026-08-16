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

{{-- پاڵاوتن --}}
<form method="GET" class="card mb-6 border-0 ring-1 ring-[--color-line] shadow-sm bg-white overflow-hidden rounded-[14px]">
    <div class="bg-[--color-surface-soft]/50 px-4 py-3 border-b border-[--color-line] flex items-center gap-2.5">
        <div class="icon-chip bg-[--color-brand-soft] text-[--color-brand-700] size-7 rounded-md">
            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
            </svg>
        </div>
        <h2 class="font-semibold text-sm text-[--color-ink]">گەڕان و پاڵاوتن</h2>
    </div>
    
    <div class="p-5 grid gap-5 sm:grid-cols-2 lg:grid-cols-12 items-end">
        <div class="lg:col-span-6 relative group">
            <label class="label text-[11px] font-bold text-[--color-ink-soft] uppercase tracking-wider mb-2">ناوی مەواد یان کۆد</label>
            <div class="relative">
                <input type="search" name="q" value="{{ request('q') }}" 
                       class="field pl-3 pr-10 py-2.5 rounded-lg border-gray-200 focus:border-[--color-brand-500] focus:ring focus:ring-[--color-brand-100] transition-all w-full text-sm group-hover:border-gray-300" 
                       placeholder="بگەڕێ...">
                <div class="absolute inset-y-0 right-0 flex items-center pr-3.5 pointer-events-none text-gray-400 group-focus-within:text-[--color-brand-600] transition-colors">
                    <svg class="size-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                </div>
            </div>
        </div>

        <div class="lg:col-span-3 group">
            <label class="label text-[11px] font-bold text-[--color-ink-soft] uppercase tracking-wider mb-2">کۆگا</label>
            <select name="warehouse" class="field py-2.5 rounded-lg border-gray-200 focus:border-[--color-brand-500] focus:ring focus:ring-[--color-brand-100] transition-all text-sm group-hover:border-gray-300 appearance-none bg-[url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20width%3D%2220%22%20height%3D%2220%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%3E%3Cpath%20d%3D%22M5%208l5%205%205-5%22%20stroke%3D%22%236b7280%22%20stroke-width%3D%222%22%20fill%3D%22none%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%2F%3E%3C%2Fsvg%3E')] bg-no-repeat bg-[position:left_0.75rem_center] pl-10 pr-3">
                <option value="">هەموو کۆگاکان</option>
                @foreach ($warehouses as $warehouse)
                    <option value="{{ $warehouse->id }}" @selected(request('warehouse') == $warehouse->id)>
                        {{ $warehouse->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="lg:col-span-3 flex items-center gap-4 justify-end h-[42px]">
            <label class="flex items-center gap-2.5 text-sm font-medium cursor-pointer group select-none">
                <div class="relative flex items-center">
                    <input type="checkbox" name="low" value="1" @checked(request('low'))
                           class="peer sr-only">
                    <div class="h-[22px] w-[38px] rounded-full bg-gray-200 transition-colors duration-300 ease-in-out peer-checked:bg-orange-500 shadow-inner"></div>
                    <div class="absolute left-[3px] top-[3px] h-4 w-4 rounded-full bg-white transition-transform duration-300 ease-out peer-checked:translate-x-[16px] shadow-sm"></div>
                </div>
                <span class="text-[--color-ink-soft] group-hover:text-[--color-ink] transition-colors">کەمەکان</span>
            </label>
            
            <button class="btn bg-gray-50 text-gray-700 border border-gray-200 hover:bg-white hover:border-gray-300 hover:shadow-sm transition-all !px-3 !py-2 rounded-lg" title="جێبەجێکردن">
                <span class="sr-only">گەڕان</span>
                <svg class="size-5 text-gray-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M5 12h14M12 5l7 7-7 7"/>
                </svg>
            </button>
        </div>
    </div>
</form>

{{-- خشتە --}}
<div class="card border-0 ring-1 ring-[--color-line] shadow-sm rounded-[14px] overflow-hidden bg-white">
    <div class="overflow-x-auto">
        <table class="table w-full text-sm text-right border-collapse">
            <thead class="bg-[--color-surface-soft]/80 text-[--color-ink-soft] text-[13px] border-b border-[--color-line]">
                <tr>
                    <th class="px-5 py-4 whitespace-nowrap font-semibold">ناوی مەواد</th>
                    <th class="px-5 py-4 whitespace-nowrap font-semibold num">باڵانس</th>
                    <th class="px-5 py-4 whitespace-nowrap font-semibold num">نرخی بڕ</th>
                    @can('view_reports')
                        <th class="px-5 py-4 whitespace-nowrap font-semibold num">تێچووی کڕین</th>
                    @endcan
                    <th class="px-5 py-4 whitespace-nowrap font-semibold text-center">کردار</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[--color-line]/60">
                @forelse ($items as $item)
                    <tr class="hover:bg-blue-50/30 transition-colors duration-150 group">
                        <td class="px-5 py-3.5 align-middle">
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
                        <td class="px-5 py-3.5 align-middle num">
                            <div class="flex flex-col items-start">
                                <span class="font-bold text-[15px] leading-none mb-1 {{ $item->is_low ? 'text-orange-600' : 'text-[--color-ink]' }}">
                                    {{ fmt_qty($item->stock_qty) }}
                                </span>
                                <span class="text-[11px] text-[--color-ink-soft] font-medium">{{ $item->unit?->name }}</span>
                            </div>
                        </td>
                        <td class="px-5 py-3.5 align-middle num">
                            <div class="flex flex-col items-start">
                                <span class="font-bold text-[14px] text-gray-700">
                                    {{ fmt_qty($item->min_qty) }}
                                </span>
                                <span class="text-[11px] text-[--color-ink-soft] font-medium">{{ $item->unit?->name }}</span>
                            </div>
                        </td>
                        @can('view_reports')
                            <td class="px-5 py-3.5 align-middle num font-medium text-gray-600 text-[13px]">
                                {{ $item->last_cost ? fmt_money($item->last_cost, $item->cost_currency) : '—' }}
                            </td>
                        @endcan
                        <td class="px-5 py-3.5 align-middle text-center">
                            @can('manage_items')
                                <a href="{{ route('items.edit', $item) }}" 
                                   class="inline-flex items-center justify-center size-8 rounded-lg text-gray-400 hover:text-[--color-brand-600] hover:bg-[--color-brand-50] transition-colors bg-white border border-gray-200 shadow-sm hover:border-[--color-brand-200]" 
                                   title="دەستکاری">
                                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path>
                                    </svg>
                                </a>
                            @endcan
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

<div class="mt-6">{{ $items->links() }}</div>

@endsection
