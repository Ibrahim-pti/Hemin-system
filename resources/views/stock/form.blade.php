@extends('layouts.app')
@section('title', 'جوڵەی نوێی مەخزەن')

@section('content')

<form method="POST" action="{{ route('stock.store') }}" class="mx-auto max-w-2xl"
      x-data="{ type: '{{ old('type', 'in') }}' }">
    @csrf

    <div class="card">
        <div class="card-body space-y-4">

            {{-- جۆری جوڵە --}}
            <div>
                <label class="label">جۆری جوڵە</label>
                <div class="grid grid-cols-3 gap-2">
                    @foreach ([
                        'in' => 'زیادکردن',
                        'out' => 'کەمکردن',
                        'transfer' => 'گواستنەوە',
                    ] as $value => $label)
                        <label class="cursor-pointer rounded-md border px-3 py-2 text-center text-sm transition-colors"
                               :class="type === '{{ $value }}'
                                   ? 'border-[--color-brand-700] bg-[--color-brand-700] text-white'
                                   : 'border-[--color-line-strong] bg-[--color-surface] hover:bg-[--color-surface-soft]'">
                            <input type="radio" name="type" value="{{ $value }}" x-model="type" class="sr-only">
                            {{ $label }}
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label class="label" for="item_id">کاڵا <span class="text-[--color-danger]">*</span></label>
                    <select id="item_id" name="item_id" class="field" required>
                        <option value="">— هەڵبژێرە —</option>
                        @foreach ($items as $item)
                            <option value="{{ $item->id }}" @selected(old('item_id', $selectedItem) == $item->id)>
                                {{ $item->name }} ({{ $item->code }}) — {{ $item->unit?->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('item_id') <p class="mt-1 text-xs text-[--color-danger]">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="label" for="warehouse_id">
                        <span x-text="type === 'transfer' ? 'لە کۆگای' : 'کۆگا'"></span>
                        <span class="text-[--color-danger]">*</span>
                    </label>
                    <select id="warehouse_id" name="warehouse_id" class="field" required>
                        @foreach ($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}"
                                @selected(old('warehouse_id', \App\Models\Warehouse::defaultId()) == $warehouse->id)>
                                {{ $warehouse->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div x-show="type === 'transfer'" x-cloak>
                    <label class="label" for="to_warehouse_id">بۆ کۆگای <span class="text-[--color-danger]">*</span></label>
                    <select id="to_warehouse_id" name="to_warehouse_id" class="field">
                        <option value="">— هەڵبژێرە —</option>
                        @foreach ($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}" @selected(old('to_warehouse_id') == $warehouse->id)>
                                {{ $warehouse->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('to_warehouse_id') <p class="mt-1 text-xs text-[--color-danger]">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="label" for="qty">بڕ <span class="text-[--color-danger]">*</span></label>
                    <input id="qty" name="qty" type="number" step="0.001" min="0.001" required
                           class="field num" value="{{ old('qty') }}">
                    @error('qty') <p class="mt-1 text-xs text-[--color-danger]">{{ $message }}</p> @enderror
                </div>

                <div x-show="type !== 'transfer'">
                    <label class="label" for="reason">هۆکار</label>
                    {{-- لە گواستنەوەدا ناچالاک دەکرێت تا بەهاکەی نەنێردرێت --}}
                    <select id="reason" name="reason" class="field" :disabled="type === 'transfer'">
                        @foreach ([
                            'adjustment' => 'ڕاستکردنەوە',
                            'opening' => 'باڵانسی سەرەتایی',
                            'damage' => 'تێکچوون',
                            'production' => 'بەکارهێنان لە بەرهەمهێنان',
                        ] as $value => $label)
                            <option value="{{ $value }}" @selected(old('reason') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- بۆ گواستنەوە هۆکار هەمیشە «گواستنەوە»یە --}}
                <template x-if="type === 'transfer'">
                    <input type="hidden" name="reason" value="transfer">
                </template>

                <div>
                    <label class="label" for="moved_at">بەروار <span class="text-[--color-danger]">*</span></label>
                    <input id="moved_at" name="moved_at" type="date" required class="field num"
                           value="{{ old('moved_at', now()->toDateString()) }}">
                </div>

                <div class="sm:col-span-2">
                    <label class="label" for="note">تێبینی</label>
                    <input id="note" name="note" class="field" value="{{ old('note') }}">
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4 flex gap-2">
        <button class="btn btn-primary">تۆمارکردن</button>
        <a href="{{ route('stock.index') }}" class="btn btn-ghost">پاشگەزبوونەوە</a>
    </div>
</form>

@endsection
