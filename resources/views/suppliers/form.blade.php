@extends('layouts.app')
@section('title', $supplier->exists ? 'دەستکاری فرۆشیار' : 'فرۆشیاری نوێ')

@section('content')

<form method="POST"
      action="{{ $supplier->exists ? route('suppliers.update', $supplier) : route('suppliers.store') }}"
      class="mx-auto max-w-2xl">
    @csrf
    @if ($supplier->exists) @method('PUT') @endif

    <div class="card">
        <div class="card-head">زانیاری فرۆشیار</div>
        <div class="card-body grid gap-4 sm:grid-cols-2">

            <div class="sm:col-span-2">
                <label class="label" for="name">ناو <span class="text-[--color-danger]">*</span></label>
                <input id="name" name="name" class="field" required value="{{ old('name', $supplier->name) }}">
                @error('name') <p class="mt-1 text-xs text-[--color-danger]">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="label" for="phone">تەلەفۆن</label>
                <input id="phone" name="phone" class="field num" dir="ltr" value="{{ old('phone', $supplier->phone) }}">
            </div>

            <div>
                <label class="label" for="phone2">تەلەفۆنی دووەم</label>
                <input id="phone2" name="phone2" class="field num" dir="ltr" value="{{ old('phone2', $supplier->phone2) }}">
            </div>

            <div class="sm:col-span-2">
                <label class="label" for="address">شوێن</label>
                <input id="address" name="address" class="field" value="{{ old('address', $supplier->address) }}">
            </div>

            <div>
                <label class="label" for="opening_balance">باڵانسی سەرەتایی</label>
                <input id="opening_balance" name="opening_balance" type="number" step="0.01" class="field num"
                       value="{{ old('opening_balance', $supplier->opening_balance) }}">
                <p class="mt-1 text-xs text-[--color-ink-soft]">ئەرێنی = کارگە قەرزاری ئەمە.</p>
            </div>

            <div>
                <label class="label" for="opening_currency">دراو</label>
                <select id="opening_currency" name="opening_currency" class="field">
                    <option value="IQD" @selected(old('opening_currency', $supplier->opening_currency) === 'IQD')>دینار</option>
                    <option value="USD" @selected(old('opening_currency', $supplier->opening_currency) === 'USD')>دۆلار</option>
                </select>
            </div>

            <div class="sm:col-span-2">
                <label class="label" for="note">تێبینی</label>
                <textarea id="note" name="note" rows="2" class="field">{{ old('note', $supplier->note) }}</textarea>
            </div>

            <label class="flex items-center gap-2 text-sm sm:col-span-2">
                <input type="checkbox" name="is_active" value="1"
                       @checked(old('is_active', $supplier->exists ? $supplier->is_active : true))
                       class="size-4 rounded border-[--color-line-strong]">
                چالاکە
            </label>
        </div>
    </div>

    <div class="mt-4 flex gap-2">
        <button class="btn btn-primary">{{ $supplier->exists ? 'نوێکردنەوە' : 'زیادکردن' }}</button>
        <a href="{{ route('suppliers.index') }}" class="btn btn-ghost">پاشگەزبوونەوە</a>

        @if ($supplier->exists)
            <button type="submit" form="delete-supplier" class="btn btn-ghost mr-auto !text-[--color-danger]"
                    onclick="return confirm('دڵنیایت؟')">سڕینەوە</button>
        @endif
    </div>
</form>

@if ($supplier->exists)
    <form id="delete-supplier" method="POST" action="{{ route('suppliers.destroy', $supplier) }}" class="hidden">
        @csrf @method('DELETE')
    </form>
@endif

@endsection
