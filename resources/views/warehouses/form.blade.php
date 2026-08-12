@extends('layouts.app')
@section('title', $warehouse->exists ? 'دەستکاری کۆگا' : 'کۆگای نوێ')

@section('content')

<form method="POST"
      action="{{ $warehouse->exists ? route('warehouses.update', $warehouse) : route('warehouses.store') }}"
      class="mx-auto max-w-lg">
    @csrf
    @if ($warehouse->exists) @method('PUT') @endif

    <div class="card">
        <div class="card-body space-y-4">
            <div>
                <label class="label" for="name">ناوی کۆگا <span class="text-[--color-danger]">*</span></label>
                <input id="name" name="name" class="field" required value="{{ old('name', $warehouse->name) }}">
                @error('name') <p class="mt-1 text-xs text-[--color-danger]">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="label" for="location">شوێن</label>
                <input id="location" name="location" class="field" value="{{ old('location', $warehouse->location) }}">
            </div>

            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" name="is_default" value="1"
                       @checked(old('is_default', $warehouse->is_default))
                       class="size-4 rounded border-[--color-line-strong]">
                کۆگای بنەڕەت
            </label>

            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" name="is_active" value="1"
                       @checked(old('is_active', $warehouse->exists ? $warehouse->is_active : true))
                       class="size-4 rounded border-[--color-line-strong]">
                چالاکە
            </label>
        </div>
    </div>

    <div class="mt-4 flex gap-2">
        <button class="btn btn-primary">{{ $warehouse->exists ? 'نوێکردنەوە' : 'زیادکردن' }}</button>
        <a href="{{ route('warehouses.index') }}" class="btn btn-ghost">پاشگەزبوونەوە</a>

        @if ($warehouse->exists)
            <button type="submit" form="delete-warehouse" class="btn btn-ghost mr-auto !text-[--color-danger]"
                    onclick="return confirm('دڵنیایت؟')">سڕینەوە</button>
        @endif
    </div>
</form>

@if ($warehouse->exists)
    <form id="delete-warehouse" method="POST" action="{{ route('warehouses.destroy', $warehouse) }}" class="hidden">
        @csrf @method('DELETE')
    </form>
@endif

@endsection
