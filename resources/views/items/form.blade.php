@extends('layouts.app')
@section('title', $item->exists ? 'دەستکاری مەواد' : 'مەوادی نوێ')

@section('content')

<form method="POST"
      action="{{ $item->exists ? route('items.update', $item) : route('items.store') }}"
      x-data="{
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
                        {{ $item->exists ? 'دەستکاریکردنی زانیارییەکانی مەواد' : 'تۆمارکردنی مەوادی نوێ لە کۆگا' }}
                    </h2>
                    <p class="text-xs text-[--color-ink-soft]">ناو، کۆد، یەکە و تێچووی کڕین لەم فۆرمە دیاری بکە</p>
                </div>
            </div>
            
            <a href="{{ route('items.index') }}" class="text-xs font-semibold text-[--color-ink-soft] hover:text-[--color-brand-700] transition-colors">
                گەڕانەوە &larr;
            </a>
        </div>

        <div class="p-6 space-y-4">

            {{-- ناو و کۆد --}}
            <div class="grid gap-4 sm:grid-cols-3">
                <div class="sm:col-span-2">
                    <label class="label text-xs font-bold text-[--color-ink-soft] mb-1.5" for="name">
                        ناوی مەواد <span class="text-[--color-danger]">*</span>
                    </label>
                    <input id="name" name="name" class="field py-2.5 text-sm" required
                           value="{{ old('name', $item->name) }}" placeholder="بۆ نموونە: لوولەی ٤٠×٤٠، وایەری لەحیم، بۆیاخی ڕەش...">
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
                    <label class="label text-xs font-bold text-[--color-ink-soft] mb-1.5" for="min_qty">نرخی بڕ</label>
                    <div class="relative">
                        <input id="min_qty" name="min_qty" type="number" step="any" min="0" 
                               class="field num py-2.5 text-sm pl-20"
                               value="{{ old('min_qty', $item->min_qty ?: 0) }}" placeholder="0">
                        <div class="absolute inset-y-1 left-1 flex items-center bg-gray-100/90 text-gray-700 px-2.5 rounded-md text-xs font-bold pointer-events-none border border-gray-200/60"
                             x-show="unitText" x-text="unitText"></div>
                    </div>
                </div>
            </div>

            {{-- تێچووی کڕین (بە دینار) --}}
            @can('view_reports')
                <div x-data="{
                    rawCost: '{{ old('last_cost', $item->last_cost ? (float)$item->last_cost : '') }}',
                    displayCost: '{{ old('last_cost', $item->last_cost ? number_format((float)$item->last_cost, 0, '.', ',') : '') }}',
                    formatInput(val) {
                        let clean = val.replace(/[^0-9.]/g, '');
                        let parts = clean.split('.');
                        if (parts.length > 2) parts = [parts[0], parts.slice(1).join('')];
                        
                        let integerPart = parts[0];
                        let decimalPart = parts.length > 1 ? '.' + parts[1] : '';
                        
                        if (integerPart) {
                            integerPart = parseInt(integerPart, 10).toLocaleString('en-US');
                        }
                        
                        this.rawCost = clean;
                        this.displayCost = integerPart ? integerPart + decimalPart : '';
                    }
                }">
                    <label class="label text-xs font-bold text-[--color-ink-soft] mb-1.5" for="display_cost">تێچووی کڕین</label>
                    <div class="relative">
                        <input id="display_cost" type="text" inputmode="numeric"
                               x-model="displayCost"
                               @input="formatInput($event.target.value)"
                               class="field num py-2.5 text-sm pl-14 font-semibold"
                               placeholder="0">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-xs font-bold text-gray-400 pointer-events-none">
                            د.ع
                        </span>
                        <input type="hidden" name="last_cost" :value="rawCost">
                    </div>
                    <input type="hidden" name="cost_currency" value="IQD">
                </div>
            @endcan

            {{-- تێبینی --}}
            <div>
                <label class="label text-xs font-bold text-[--color-ink-soft] mb-1.5" for="note">
                    تێبینی <span class="text-[11px] font-normal text-gray-400">(ئارەزوومەندانە)</span>
                </label>
                <textarea id="note" name="note" rows="2" class="field text-sm" placeholder="ڕوونکردنەوە، قیاس، یان تێبینی تایبەت...">{{ old('note', $item->note) }}</textarea>
            </div>

            <input type="hidden" name="is_active" value="1">
            <input type="hidden" name="is_for_sale" value="0">
        </div>

        {{-- دوگمەکانی خوارەوە --}}
        <div class="bg-[--color-surface-soft]/60 px-6 py-4 border-t border-[--color-line] flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <button class="btn btn-primary shadow-sm hover:shadow-md transition-all px-6 py-2.5 text-sm font-semibold">
                    {{ $item->exists ? 'نوێکردنەوەی مەواد' : 'زیادکردنی مەواد' }}
                </button>
                <a href="{{ route('items.index') }}" class="btn btn-ghost px-4 py-2.5 text-sm">پاشگەزبوونەوە</a>
            </div>

            @if ($item->exists)
                <button type="submit" form="delete-item" class="btn btn-ghost !text-[--color-danger] hover:!bg-red-50 text-xs font-semibold"
                        onclick="return confirm('دڵنیایت لە سڕینەوەی ئەم مەوادە؟')">
                    سڕینەوەی مەواد
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
