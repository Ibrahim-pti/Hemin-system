@extends('layouts.app')
@section('title', $supplier->exists ? 'دەستکاری فرۆشیار' : 'فرۆشیاری نوێ')

@section('content')

<form method="POST"
      action="{{ $supplier->exists ? route('suppliers.update', $supplier) : route('suppliers.store') }}"
      class="w-full">
    @csrf
    @if ($supplier->exists) @method('PUT') @endif

    <div class="card">
        <div class="card-head flex items-center justify-between">
            <span>{{ $supplier->exists ? 'دەستکاریکردنی زانیارییەکانی فرۆشیار' : 'تۆمارکردنی فرۆشیاری نوێ' }}</span>
            <a href="{{ route('suppliers.index') }}" class="btn btn-ghost !py-1 text-xs">گەڕانەوە &larr;</a>
        </div>

        <div class="card-body grid gap-4 sm:grid-cols-2">

            @if ($errors->any())
                <div class="sm:col-span-2 p-4 rounded-xl bg-red-50 text-red-700 border border-red-200 text-xs">
                    <div class="font-bold text-sm mb-1">تکایە ئەم کێشانە چاک بکە:</div>
                    <ul class="list-disc list-inside space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- ناوی فرۆشیار --}}
            <div class="sm:col-span-2">
                <label class="label" for="name">
                    ناوی فرۆشیار / کۆمپانیا <span class="text-[--color-danger]">*</span>
                </label>
                <input id="name" name="name" class="field" required
                       value="{{ old('name', $supplier->name) }}" placeholder="ناوی فرۆشیار بنووسە">
                @error('name') <p class="mt-1 text-xs text-[--color-danger]">{{ $message }}</p> @enderror
            </div>

            {{-- مۆبایل --}}
            <div>
                <label class="label" for="phone">ژمارەی مۆبایل</label>
                <input id="phone" name="phone" class="field num" dir="ltr"
                       value="{{ old('phone', $supplier->phone) }}" placeholder="0750xxxxxxx">
                @error('phone') <p class="mt-1 text-xs text-[--color-danger]">{{ $message }}</p> @enderror
            </div>

            {{-- شوێن --}}
            <div>
                <label class="label" for="address">شوێن / ناونیشان</label>
                <input id="address" name="address" class="field"
                       value="{{ old('address', $supplier->address) }}" placeholder="شار، ناونیشان...">
                @error('address') <p class="mt-1 text-xs text-[--color-danger]">{{ $message }}</p> @enderror
            </div>

            {{-- قەرزی ماوەی پێشوو --}}
            <div>
                <label class="label" for="opening_balance">قەرزی ماوەی پێشوو</label>
                <div class="relative">
                    <input id="opening_balance" name="opening_balance" type="number" step="any" min="0"
                           value="{{ old('opening_balance', $supplier->exists && (float)$supplier->opening_balance > 0 ? (float)$supplier->opening_balance : '') }}"
                           class="field num pl-14 font-semibold"
                           placeholder="0">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-xs font-bold text-slate-400 pointer-events-none">
                        د.ع
                    </span>
                </div>
                <input type="hidden" name="opening_currency" value="IQD">
                @error('opening_balance') <p class="mt-1 text-xs text-[--color-danger]">{{ $message }}</p> @enderror
            </div>

            {{-- تێبینی --}}
            <div>
                <label class="label" for="note">تێبینی</label>
                <input id="note" name="note" class="field"
                       value="{{ old('note', $supplier->note) }}" placeholder="تێبینی تایبەت...">
            </div>

            <input type="hidden" name="is_active" value="1">
        </div>
    </div>

    {{-- دوگمەکانی خوارەوە --}}
    <div class="mt-4 flex gap-2">
        <button class="btn btn-primary">{{ $supplier->exists ? 'نوێکردنەوە' : 'زیادکردن' }}</button>
        <a href="{{ route('suppliers.index') }}" class="btn btn-ghost">پاشگەزبوونەوە</a>

        @if ($supplier->exists)
            <button type="submit" form="delete-supplier" class="btn btn-ghost mr-auto !text-[--color-danger]"
                    onclick="return confirm('دڵنیایت لە سڕینەوەی ئەم فرۆشیارە؟')">
                سڕینەوە
            </button>
        @endif
    </div>
</form>

@if ($supplier->exists)
    <form id="delete-supplier" method="POST" action="{{ route('suppliers.destroy', $supplier) }}" class="hidden">
        @csrf @method('DELETE')
    </form>
@endif

@endsection
