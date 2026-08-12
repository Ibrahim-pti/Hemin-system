@extends('layouts.app')
@section('title', $customer->exists ? 'دەستکاری کڕیار' : 'کڕیاری نوێ')

@section('content')

<form method="POST"
      action="{{ $customer->exists ? route('customers.update', $customer) : route('customers.store') }}"
      class="mx-auto max-w-2xl">
    @csrf
    @if ($customer->exists) @method('PUT') @endif

    <div class="card">
        <div class="card-head">زانیاری کڕیار</div>
        <div class="card-body grid gap-4 sm:grid-cols-2">

            <div class="sm:col-span-2">
                <label class="label" for="name">ناو <span class="text-[--color-danger]">*</span></label>
                <input id="name" name="name" class="field" required value="{{ old('name', $customer->name) }}">
                @error('name') <p class="mt-1 text-xs text-[--color-danger]">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="label" for="phone">تەلەفۆن</label>
                <input id="phone" name="phone" class="field num" dir="ltr" value="{{ old('phone', $customer->phone) }}">
            </div>

            <div>
                <label class="label" for="phone2">تەلەفۆنی دووەم</label>
                <input id="phone2" name="phone2" class="field num" dir="ltr" value="{{ old('phone2', $customer->phone2) }}">
            </div>

            <div class="sm:col-span-2">
                <label class="label" for="address">شوێن</label>
                <input id="address" name="address" class="field" value="{{ old('address', $customer->address) }}"
                       placeholder="گەڕەک / شەقام / نیشانەی نزیک">
            </div>

            <div>
                <label class="label" for="discount_percent">داشکاندنی هەمیشەیی (٪)</label>
                <input id="discount_percent" name="discount_percent" type="number" step="0.01" min="0" max="100"
                       class="field num" value="{{ old('discount_percent', $customer->discount_percent) }}">
                <p class="mt-1 text-xs text-[--color-ink-soft]">لە وەسڵی نوێدا خۆکار دادەنرێت.</p>
            </div>

            <div>
                <label class="label" for="opening_balance">باڵانسی سەرەتایی</label>
                <input id="opening_balance" name="opening_balance" type="number" step="0.01" class="field num"
                       value="{{ old('opening_balance', $customer->opening_balance) }}">
                <p class="mt-1 text-xs text-[--color-ink-soft]">ئەرێنی = قەرزاری کارگەیە.</p>
            </div>

            <div>
                <label class="label" for="opening_currency">دراو</label>
                <select id="opening_currency" name="opening_currency" class="field">
                    <option value="IQD" @selected(old('opening_currency', $customer->opening_currency) === 'IQD')>دینار</option>
                    <option value="USD" @selected(old('opening_currency', $customer->opening_currency) === 'USD')>دۆلار</option>
                </select>
            </div>

            <div class="sm:col-span-2">
                <label class="label" for="note">تێبینی</label>
                <textarea id="note" name="note" rows="2" class="field">{{ old('note', $customer->note) }}</textarea>
            </div>

            <label class="flex items-center gap-2 text-sm sm:col-span-2">
                <input type="checkbox" name="is_active" value="1"
                       @checked(old('is_active', $customer->exists ? $customer->is_active : true))
                       class="size-4 rounded border-[--color-line-strong]">
                چالاکە
            </label>
        </div>
    </div>

    <div class="mt-4 flex gap-2">
        <button class="btn btn-primary">{{ $customer->exists ? 'نوێکردنەوە' : 'زیادکردن' }}</button>
        <a href="{{ route('customers.index') }}" class="btn btn-ghost">پاشگەزبوونەوە</a>

        @if ($customer->exists)
            <button type="submit" form="delete-customer" class="btn btn-ghost mr-auto !text-[--color-danger]"
                    onclick="return confirm('دڵنیایت؟')">سڕینەوە</button>
        @endif
    </div>
</form>

@if ($customer->exists)
    <form id="delete-customer" method="POST" action="{{ route('customers.destroy', $customer) }}" class="hidden">
        @csrf @method('DELETE')
    </form>
@endif

@endsection
