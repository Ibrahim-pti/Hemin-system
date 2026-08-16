@extends('layouts.app')
@section('title', $item->exists ? 'دەستکاری بابەت' : 'بابەتی نوێ')

@section('content')

<form method="POST"
      action="{{ $item->exists ? route('items.update', $item) : route('items.store') }}"
      class="mx-auto max-w-3xl">
    @csrf
    @if ($item->exists) @method('PUT') @endif

    <div class="card border-0 ring-1 ring-[--color-line] shadow-sm rounded-[14px] overflow-hidden bg-white">
        <div class="bg-[--color-surface-soft]/60 px-5 py-4 border-b border-[--color-line] flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <span class="icon-chip bg-[--color-brand-soft] text-[--color-brand-700] size-8 rounded-lg">
                    <svg class="size-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                        <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                        <line x1="12" y1="22.08" x2="12" y2="12"></line>
                    </svg>
                </span>
                <div>
                    <h2 class="font-bold text-[15px] text-[--color-ink]">
                        {{ $item->exists ? 'دەستکاریکردنی زانیارییەکانی بابەت' : 'تۆمارکردنی بابەتی نوێ لە کۆگا' }}
                    </h2>
                    <p class="text-xs text-[--color-ink-soft]">ناو، کۆد، نرخ و تایبەتمەندییەکانی ماددە لەم فۆرمە دیاری بکە</p>
                </div>
            </div>
            
            <a href="{{ route('items.index') }}" class="text-xs font-semibold text-[--color-ink-soft] hover:text-[--color-brand-700] transition-colors">
                گەڕانەوە &larr;
            </a>
        </div>

        <div class="p-6 space-y-6">

            {{-- بەشی بنەڕەتی --}}
            <div class="grid gap-4 sm:grid-cols-3">
                <div class="sm:col-span-2">
                    <label class="label text-xs font-bold text-[--color-ink-soft] mb-1.5" for="name">
                        ناوی بابەت <span class="text-[--color-danger]">*</span>
                    </label>
                    <input id="name" name="name" class="field py-2.5 text-sm" required
                           value="{{ old('name', $item->name) }}" placeholder="بۆ نموونە: لوولەی ٤٠×٤٠، قوفڵی دەرگا...">
                    @error('name') <p class="mt-1 text-xs text-[--color-danger]">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="label text-xs font-bold text-[--color-ink-soft] mb-1.5" for="code">
                        کۆد <span class="text-[--color-danger]">*</span>
                    </label>
                    <input id="code" name="code" class="field num py-2.5 text-sm" required dir="ltr"
                           value="{{ old('code', $item->code ?: 'K-'.str_pad((string) (\App\Models\Item::max('id') + 1), 4, '0', STR_PAD_LEFT)) }}">
                    @error('code') <p class="mt-1 text-xs text-[--color-danger]">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- پۆلێنکردن و یەکە و کەمترین بڕ --}}
            <div class="grid gap-4 sm:grid-cols-3 border-t border-[--color-line] pt-5">
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="label !mb-0 text-xs font-bold text-[--color-ink-soft]" for="item_category_id">جۆر (Category)</label>
                        <a href="{{ route('categories.index') }}" target="_blank" class="text-[11px] font-semibold text-[--color-brand-700] hover:underline">+ بەڕێوەبردن</a>
                    </div>
                    <select id="item_category_id" name="item_category_id" class="field py-2.5 text-sm">
                        <option value="">— هیچ —</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected(old('item_category_id', $item->item_category_id) == $category->id)>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="label text-xs font-bold text-[--color-ink-soft] mb-1.5" for="unit_id">
                        یەکە <span class="text-[--color-danger]">*</span>
                    </label>
                    <select id="unit_id" name="unit_id" class="field py-2.5 text-sm" required>
                        <option value="">— هەڵبژێرە —</option>
                        @foreach ($units as $unit)
                            <option value="{{ $unit->id }}" @selected(old('unit_id', $item->unit_id) == $unit->id)>
                                {{ $unit->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('unit_id') <p class="mt-1 text-xs text-[--color-danger]">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="label text-xs font-bold text-[--color-ink-soft] mb-1.5" for="min_qty">
                        سنووری کەمی مەخزەن
                    </label>
                    <input id="min_qty" name="min_qty" type="number" step="any" min="0" class="field num py-2.5 text-sm"
                           value="{{ old('min_qty', $item->min_qty ?: 0) }}" placeholder="0">
                    <p class="mt-1 text-[11px] text-[--color-ink-soft]">کاتی لەم بڕە کەمتر بێت ئاگادارت دەکاتەوە</p>
                </div>
            </div>

            {{-- نرخەکان --}}
            @can('view_reports')
                <div class="border-t border-[--color-line] pt-5">
                    <div class="text-xs font-bold text-[--color-ink] mb-3 flex items-center gap-1.5">
                        <svg class="size-4 text-[--color-brand-600]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                        </svg>
                        زانیاری نرخ و تێچوو
                    </div>

                    <div class="grid gap-4 sm:grid-cols-3">
                        <div>
                            <label class="label text-xs font-bold text-[--color-ink-soft] mb-1.5" for="last_cost">کڕین (تێچووی سەر خۆمان)</label>
                            <input id="last_cost" name="last_cost" type="number" step="0.01" min="0" class="field num py-2.5 text-sm"
                                   value="{{ old('last_cost', $item->last_cost) }}" placeholder="0">
                        </div>

                        <div>
                            <label class="label text-xs font-bold text-[--color-ink-soft] mb-1.5" for="cost_currency">دراوی تێچوو</label>
                            <select id="cost_currency" name="cost_currency" class="field py-2.5 text-sm">
                                <option value="IQD" @selected(old('cost_currency', $item->cost_currency) === 'IQD')>دینار (IQD)</option>
                                <option value="USD" @selected(old('cost_currency', $item->cost_currency) === 'USD')>دۆلار ($)</option>
                            </select>
                        </div>

                        <div>
                            <label class="label text-xs font-bold text-[--color-ink-soft] mb-1.5" for="sale_price">نرخی فرۆشتن</label>
                            <input id="sale_price" name="sale_price" type="number" step="0.01" min="0" class="field num py-2.5 text-sm"
                                   value="{{ old('sale_price', $item->sale_price) }}" placeholder="0">
                        </div>
                    </div>
                </div>
            @endcan

            {{-- تێبینی --}}
            <div class="border-t border-[--color-line] pt-5">
                <label class="label text-xs font-bold text-[--color-ink-soft] mb-1.5" for="note">تێبینی زیادە</label>
                <textarea id="note" name="note" rows="2" class="field text-sm" placeholder="ڕوونکردنەوەی قیاس، شوێن لە کۆگا، یان تێبینی تایبەت...">{{ old('note', $item->note) }}</textarea>
            </div>

            <input type="hidden" name="is_active" value="1">
        </div>

        {{-- دوگمەکانی فۆرم --}}
        <div class="bg-[--color-surface-soft]/60 px-6 py-4 border-t border-[--color-line] flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <button class="btn btn-primary shadow-sm hover:shadow-md transition-all px-6 py-2.5 text-sm font-semibold">
                    {{ $item->exists ? 'نوێکردنەوەی بابەت' : 'زیادکردنی بابەت' }}
                </button>
                <a href="{{ route('items.index') }}" class="btn btn-ghost px-4 py-2.5 text-sm">پاشگەزبوونەوە</a>
            </div>

            @if ($item->exists)
                <button type="submit" form="delete-item" class="btn btn-ghost !text-[--color-danger] hover:!bg-red-50 text-xs font-semibold"
                        onclick="return confirm('دڵنیایت لە سڕینەوەی ئەم بابەتە؟')">
                    سڕینەوەی بابەت
                </button>
            @endif
        </div>
    </div>
</form>

@if ($item->exists)
    <form id="delete-item" method="POST" action="{{ route('items.destroy', $item) }}" class="hidden">
        @csrf @method('DELETE')
    </form>
@endif

@endsection
