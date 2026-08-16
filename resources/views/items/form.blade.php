@extends('layouts.app')
@section('title', $item->exists ? 'دەستکاری بابەت' : 'بابەتی نوێ')

@section('content')

<form method="POST"
      action="{{ $item->exists ? route('items.update', $item) : route('items.store') }}"
      class="mx-auto max-w-2xl">
    @csrf
    @if ($item->exists) @method('PUT') @endif

    <div class="card">
        <div class="card-head">زانیاری بابەت</div>
        <div class="card-body grid gap-4 sm:grid-cols-2">

            <div>
                <label class="label" for="name">ناوی بابەت <span class="text-[--color-danger]">*</span></label>
                <input id="name" name="name" class="field" required
                       value="{{ old('name', $item->name) }}" placeholder="بۆ نموونە: لوولەی ٤٠×٤٠">
                @error('name') <p class="mt-1 text-xs text-[--color-danger]">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="label" for="code">کۆد <span class="text-[--color-danger]">*</span></label>
                <input id="code" name="code" class="field num" required dir="ltr"
                       value="{{ old('code', $item->code ?: 'K-'.str_pad((string) (\App\Models\Item::max('id') + 1), 4, '0', STR_PAD_LEFT)) }}">
                @error('code') <p class="mt-1 text-xs text-[--color-danger]">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="label" for="item_category_id">جۆر</label>
                <select id="item_category_id" name="item_category_id" class="field">
                    <option value="">— هیچ —</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected(old('item_category_id', $item->item_category_id) == $category->id)>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="label" for="unit_id">یەکە <span class="text-[--color-danger]">*</span></label>
                <select id="unit_id" name="unit_id" class="field" required>
                    <option value="">— هەڵبژێرە —</option>
                    @foreach ($units as $unit)
                        <option value="{{ $unit->id }}" @selected(old('unit_id', $item->unit_id) == $unit->id)>
                            {{ $unit->name }}
                        </option>
                    @endforeach
                </select>
                @error('unit_id') <p class="mt-1 text-xs text-[--color-danger]">{{ $message }}</p> @enderror
            </div>



            @can('view_reports')
                <div class="flex items-end gap-2">
                    <div class="flex-1">
                        <label class="label" for="last_cost">کڕین (لەسەر خۆمان)</label>
                        <input id="last_cost" name="last_cost" type="number" step="0.01" min="0" class="field num"
                               value="{{ old('last_cost', $item->last_cost) }}">
                    </div>
                    <div class="w-24">
                        <label class="label" for="cost_currency">دراو</label>
                        <select id="cost_currency" name="cost_currency" class="field">
                            <option value="IQD" @selected(old('cost_currency', $item->cost_currency) === 'IQD')>دینار</option>
                            <option value="USD" @selected(old('cost_currency', $item->cost_currency) === 'USD')>دۆلار</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="label" for="sale_price">نرخی فرۆشتن</label>
                    <input id="sale_price" name="sale_price" type="number" step="0.01" min="0" class="field num"
                           value="{{ old('sale_price', $item->sale_price) }}">
                </div>
            @endcan

            <div class="sm:col-span-2">
                <label class="label" for="note">تێبینی</label>
                <textarea id="note" name="note" rows="2" class="field">{{ old('note', $item->note) }}</textarea>
            </div>

            <input type="hidden" name="is_active" value="1">
        </div>
    </div>

    <div class="mt-4 flex gap-2">
        <button class="btn btn-primary">{{ $item->exists ? 'نوێکردنەوە' : 'زیادکردن' }}</button>
        <a href="{{ route('items.index') }}" class="btn btn-ghost">پاشگەزبوونەوە</a>

        {{-- سڕینەوە فۆرمێکی جیایە (لە دەرەوەی ئەمە) — دوگمەکەی لێرەوە بانگ دەکرێت. --}}
        @if ($item->exists)
            <button type="submit" form="delete-item" class="btn btn-ghost mr-auto !text-[--color-danger]"
                    onclick="return confirm('دڵنیایت لە سڕینەوەی ئەم بابەتە؟')">
                سڕینەوە
            </button>
        @endif
    </div>
</form>

@if ($item->exists)
    <form id="delete-item" method="POST" action="{{ route('items.destroy', $item) }}" class="hidden">
        @csrf @method('DELETE')
    </form>
@endif

@endsection
