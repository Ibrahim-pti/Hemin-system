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
      class="mx-auto max-w-4xl">
    @csrf
    @if ($item->exists) @method('PUT') @endif

    <div class="card border-0 ring-1 ring-[--color-line] shadow-sm rounded-[14px] overflow-hidden bg-white">
        <div class="bg-[--color-surface-soft]/60 px-6 py-4 border-b border-[--color-line] flex items-center justify-between">
            <div class="flex items-center gap-3">
                <span class="icon-chip bg-[--color-brand-soft] text-[--color-brand-700] size-9 rounded-lg">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                        <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                        <line x1="12" y1="22.08" x2="12" y2="12"></line>
                    </svg>
                </span>
                <div>
                    <h2 class="font-bold text-[16px] text-[--color-ink]">
                        {{ $item->exists ? 'دەستکاریکردنی زانیارییەکانی مەواد' : 'تۆمارکردنی مەوادی نوێ لە کۆگا' }}
                    </h2>
                    <p class="text-xs text-[--color-ink-soft]">ناو، کۆد، یەکە و تێچووی کڕین لەم فۆرمە دیاری بکە</p>
                </div>
            </div>
            
            <a href="{{ route('items.index') }}" class="btn btn-ghost !py-1.5 !px-3 text-xs font-semibold text-[--color-ink-soft] hover:text-[--color-brand-700]">
                گەڕانەوە &larr;
            </a>
        </div>

        <div class="p-6 md:p-8 space-y-5">

            @if ($errors->any())
                <div class="p-4 rounded-xl bg-red-50 text-red-700 border border-red-200 text-xs">
                    <div class="font-bold text-sm mb-1">تکایە ئەم کێشانە چاک بکە:</div>
                    <ul class="list-disc list-inside space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- ڕیزی ١: ناو --}}
            <div>
                <label class="label text-xs font-bold text-[--color-ink-soft] mb-1.5" for="name">
                    ناوی مەواد <span class="text-[--color-danger]">*</span>
                </label>
                <input id="name" name="name" class="field py-2.5 text-sm" required
                       value="{{ old('name', $item->name) }}" placeholder="ناوی مەواد لێرە داخڵ بکە">
                @error('name') <p class="mt-1 text-xs text-[--color-danger]">{{ $message }}</p> @enderror
            </div>

            {{-- ڕیزی ٢: یەکە، نرخی بڕ، تێچووی کڕین و بەرواری کڕین --}}
            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                <div>
                    <label class="label text-xs font-bold text-[--color-ink-soft] mb-1.5" for="unit_id">
                        یەکە <span class="text-[--color-danger]">*</span>
                    </label>
                    <select id="unit_id" name="unit_id" x-model="selectedUnit" class="field py-2.5 text-sm" required>
                        <option value="">— هەڵبژێرە (دانە، پارچە...) —</option>
                        @foreach ($units as $unit)
                            <option value="{{ $unit->id }}" @selected(old('unit_id', $item->unit_id) == $unit->id)>
                                {{ $unit->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('unit_id') <p class="mt-1 text-xs text-[--color-danger]">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="label text-xs font-bold text-[--color-ink-soft] mb-1.5" for="min_qty">بڕ</label>
                    <div class="relative">
                        <input id="min_qty" name="min_qty" type="number" step="any" min="0" 
                               class="field num py-2.5 text-sm pl-20"
                               value="{{ old('min_qty', $item->min_qty ?: 0) }}" placeholder="0">
                        <div class="absolute inset-y-1 left-1 flex items-center bg-gray-100/90 text-gray-700 px-2.5 rounded-md text-xs font-bold pointer-events-none border border-gray-200/60"
                             x-show="unitText" x-text="unitText"></div>
                    </div>
                    @error('min_qty') <p class="mt-1 text-xs text-[--color-danger]">{{ $message }}</p> @enderror
                </div>

                {{-- تێچووی کڕین (بە دینار) --}}
                @can('view_reports')
                    <div x-data="{
                        formatInput(e) {
                            let clean = e.target.value.replace(/[^0-9.]/g, '');
                            let parts = clean.split('.');
                            if (parts.length > 2) parts = [parts[0], parts.slice(1).join('')];
                            let int = parts[0] ? parseInt(parts[0], 10).toLocaleString('en-US') : '';
                            let dec = parts.length > 1 ? '.' + parts[1] : '';
                            e.target.value = int ? int + dec : '';
                        }
                    }">
                        <label class="label text-xs font-bold text-[--color-ink-soft] mb-1.5" for="last_cost">تێچووی کڕین</label>
                        <div class="relative">
                            <input id="last_cost" name="last_cost" type="text" inputmode="numeric"
                                   value="{{ old('last_cost', $item->last_cost ? number_format((float)$item->last_cost, 0, '.', ',') : '') }}"
                                   @input="formatInput($event)"
                                   class="field num py-2.5 text-sm pl-14 font-semibold"
                                   placeholder="0">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-xs font-bold text-gray-400 pointer-events-none">
                                د.ع
                            </span>
                        </div>
                        @error('last_cost') <p class="mt-1 text-xs text-[--color-danger]">{{ $message }}</p> @enderror
                        <input type="hidden" name="cost_currency" value="IQD">
                    </div>
                @endcan

                {{-- بەرواری کڕین --}}
                <div>
                    <label class="label text-xs font-bold text-[--color-ink-soft] mb-1.5" for="purchase_date">
                        بەرواری کڕین <span class="text-[11px] font-normal text-gray-400">(ئارەزوومەندانە)</span>
                    </label>
                    <input id="purchase_date" name="purchase_date" type="date" class="field num py-2.5 text-sm"
                           value="{{ old('purchase_date', $item->purchase_date ? $item->purchase_date->format('Y-m-d') : '') }}">
                    @error('purchase_date') <p class="mt-1 text-xs text-[--color-danger]">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- ڕیزی ٣: تێبینی --}}
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
        <div class="bg-[--color-surface-soft]/60 px-6 md:px-8 py-4 border-t border-[--color-line] flex items-center justify-between">
            <div class="flex items-center gap-3">
                <button class="btn btn-primary shadow-sm hover:shadow-md transition-all px-7 py-2.5 text-sm font-semibold">
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
