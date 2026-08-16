@extends('layouts.app')
@section('title', $item->exists ? 'دەستکاری بابەت' : 'بابەتی نوێ')

@section('content')

<form method="POST"
      action="{{ $item->exists ? route('items.update', $item) : route('items.store') }}"
      x-data="{
          isForSale: {{ old('is_for_sale', $item->is_for_sale ? '1' : '0') }},
          selectedUnit: '{{ old('unit_id', $item->unit_id) }}',
          unitNames: {
              @foreach ($units as $unit)
                  '{{ $unit->id }}': '{{ $unit->name }}',
              @endforeach
          },
          get unitText() {
              return this.unitNames[this.selectedUnit] || '';
          }
      }"
      class="mx-auto max-w-2xl">
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
                        {{ $item->exists ? 'دەستکاریکردنی زانیارییەکانی بابەت' : 'تۆمارکردنی بابەتی نوێ' }}
                    </h2>
                    <p class="text-xs text-[--color-ink-soft]">ناو، کۆد، جۆری بەکارهێنان و تێچوو لەم فۆرمە دیاری بکە</p>
                </div>
            </div>
            
            <a href="{{ route('items.index') }}" class="text-xs font-semibold text-[--color-ink-soft] hover:text-[--color-brand-700] transition-colors">
                گەڕانەوە &larr;
            </a>
        </div>

        <div class="p-6 space-y-4">

            {{-- هەڵبژاردنی جۆری بابەت: مەوادی کارگە یان بۆ فرۆشتن --}}
            <div class="p-3 bg-gray-50 rounded-xl border border-gray-200/80 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <span class="text-xs font-bold text-[--color-ink] block">ئەم بابەتە بۆ چی بەکاردێت؟</span>
                    <span class="text-[11px] text-[--color-ink-soft]">دیاری بکە ئایا مەوادی خاوی کارگەیە یان کاڵای فرۆشتنە</span>
                </div>
                <div class="flex items-center gap-2">
                    <label class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg border text-xs font-semibold cursor-pointer transition-all select-none"
                           :class="!Boolean(Number(isForSale)) ? 'bg-[--color-brand-600] text-white border-[--color-brand-600] shadow-sm' : 'bg-white text-gray-600 border-gray-200 hover:border-gray-300'">
                        <input type="radio" name="is_for_sale" value="0" x-model="isForSale" class="sr-only">
                        <span>📦 مەوادی کارگە (مەخزەن)</span>
                    </label>
                    <label class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg border text-xs font-semibold cursor-pointer transition-all select-none"
                           :class="Boolean(Number(isForSale)) ? 'bg-[--color-brand-600] text-white border-[--color-brand-600] shadow-sm' : 'bg-white text-gray-600 border-gray-200 hover:border-gray-300'">
                        <input type="radio" name="is_for_sale" value="1" x-model="isForSale" class="sr-only">
                        <span>🛒 کاڵا بۆ فرۆشتن</span>
                    </label>
                </div>
            </div>

            {{-- ناو و کۆد --}}
            <div class="grid gap-4 sm:grid-cols-3">
                <div class="sm:col-span-2">
                    <label class="label text-xs font-bold text-[--color-ink-soft] mb-1.5" for="name">
                        ناوی بابەت <span class="text-[--color-danger]">*</span>
                    </label>
                    <input id="name" name="name" class="field py-2.5 text-sm" required
                           value="{{ old('name', $item->name) }}" placeholder="بۆ نموونە: لوولەی ٤٠×٤٠، وایەری لەحیم، قوفڵ...">
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

            {{-- یەکە و حەدەد --}}
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="label text-xs font-bold text-[--color-ink-soft] mb-1.5" for="unit_id">
                        یەکە <span class="text-[--color-danger]">*</span>
                    </label>
                    <select id="unit_id" name="unit_id" x-model="selectedUnit" class="field py-2.5 text-sm" required>
                        <option value="">— هەڵبژێرە (دانە، پارچە، کارتۆن...) —</option>
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
                        حەدەد (کەمترین بڕ)
                    </label>
                    <div class="relative">
                        <input id="min_qty" name="min_qty" type="number" step="any" min="0" class="field num py-2.5 text-sm pl-16"
                               value="{{ old('min_qty', $item->min_qty ?: 0) }}" placeholder="0">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-xs font-bold text-[--color-brand-700] pointer-events-none"
                              x-text="unitText"></span>
                    </div>
                </div>
            </div>

            {{-- نرخەکان --}}
            @can('view_reports')
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

                    <div x-show="Boolean(Number(isForSale))" x-transition>
                        <label class="label text-xs font-bold text-[--color-ink-soft] mb-1.5" for="sale_price">نرخی فرۆشتن</label>
                        <input id="sale_price" name="sale_price" type="number" step="0.01" min="0" class="field num py-2.5 text-sm"
                               value="{{ old('sale_price', $item->sale_price) }}" placeholder="0">
                    </div>

                    <div x-show="!Boolean(Number(isForSale))" class="flex flex-col justify-center text-xs text-[--color-ink-soft] bg-gray-50/70 p-2.5 rounded-lg border border-dashed border-gray-200">
                        <span class="font-medium text-gray-500">🔒 تەنها مەوادی کارگەیە</span>
                        <span class="text-[11px] text-gray-400">نرخی فرۆشتنی پێویست نییە</span>
                    </div>
                </div>
            @endcan

            {{-- تێبینی --}}
            <div>
                <label class="label text-xs font-bold text-[--color-ink-soft] mb-1.5" for="note">تێبینی</label>
                <textarea id="note" name="note" rows="2" class="field text-sm" placeholder="ڕوونکردنەوە یان تێبینی تایبەت...">{{ old('note', $item->note) }}</textarea>
            </div>

            <input type="hidden" name="is_active" value="1">
        </div>

        {{-- دوگمەکانی خوارەوە --}}
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
