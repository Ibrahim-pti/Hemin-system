@extends('layouts.app')
@section('title', $customer->exists ? 'دەستکاری کڕیار' : 'کڕیاری نوێ')

@section('content')

<form method="POST"
      action="{{ $customer->exists ? route('customers.update', $customer) : route('customers.store') }}"
      class="mx-auto max-w-xl">
    @csrf
    @if ($customer->exists) @method('PUT') @endif

    <div class="card">
        <div class="card-head">زانیاری کڕیار</div>
        <div class="card-body space-y-4">

            <div>
                <label class="label" for="name">ناو <span class="text-[--color-danger]">*</span></label>
                <input id="name" name="name" class="field" required value="{{ old('name', $customer->name) }}" placeholder="ناوی کڕیار بنووسە...">
                @error('name') <p class="mt-1 text-xs text-[--color-danger]">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="label" for="phone">تەلەفۆن</label>
                <input id="phone" name="phone" class="field num" dir="ltr" value="{{ old('phone', $customer->phone) }}" placeholder="07XXXXXXXXX">
                @error('phone') <p class="mt-1 text-xs text-[--color-danger]">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="label" for="note">تێبینی</label>
                <textarea id="note" name="note" rows="3" class="field" placeholder="تێبینی سەبارەت بەم کڕیارە (ئارەزوومەندانە)...">{{ old('note', $customer->note) }}</textarea>
            </div>

            <label class="flex items-center gap-2 text-sm pt-1">
                <input type="checkbox" name="is_active" value="1"
                       @checked(old('is_active', $customer->exists ? $customer->is_active : true))
                       class="size-4 rounded border-[--color-line-strong]">
                <span>کڕیاری چالاکە</span>
            </label>
        </div>
    </div>

    <div class="mt-4 flex gap-2">
        <button class="btn btn-primary">{{ $customer->exists ? 'نوێکردنەوە' : 'زیادکردن' }}</button>
        <a href="{{ route('customers.index') }}" class="btn btn-ghost">پاشگەزبوونەوە</a>

        @if ($customer->exists)
            <button type="submit" form="delete-customer" class="btn btn-ghost mr-auto !text-[--color-danger]"
                    onclick="return confirm('دڵنیایت لە سڕینەوەی ئەم کڕیارە؟')">سڕینەوە</button>
        @endif
    </div>
</form>

@if ($customer->exists)
    <form id="delete-customer" method="POST" action="{{ route('customers.destroy', $customer) }}" class="hidden">
        @csrf @method('DELETE')
    </form>
@endif

@endsection
