@extends('layouts.app')
@section('title', $warehouse->exists ? 'دەستکاری کۆگا' : 'کۆگای نوێ')

@section('content')

<div class="w-full space-y-4">
    {{-- هێڵی سەرەوە --}}
    <div class="flex items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <a href="{{ route('warehouses.index') }}"
               class="size-10 rounded-xl bg-white border border-slate-200 hover:bg-slate-50 flex items-center justify-center text-slate-600 transition-colors shadow-2xs">
                ←
            </a>
            <div>
                <h1 class="text-lg sm:text-xl font-black text-slate-900">
                    {{ $warehouse->exists ? 'دەستکاری شوێن: ' . $warehouse->name : 'تۆمارکردنی کۆگا / شوێنی نوێ' }}
                </h1>
                <p class="text-xs text-slate-500 font-medium">زانیاری ناونیشان و ڕێکخستنی شوێنی کۆگا</p>
            </div>
        </div>

        @if ($warehouse->exists && !$warehouse->is_default)
            <button type="submit" form="delete-warehouse"
                    class="px-3.5 py-1.5 rounded-xl text-xs font-bold bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 transition-all cursor-pointer"
                    onclick="return confirm('ئایا دڵنیایت لە سڕینەوەی ئەم کۆگایە؟')">
                🗑️ سڕینەوە
            </button>
        @endif
    </div>

    {{-- فۆڕمی سەرەکی --}}
    <form method="POST"
          action="{{ $warehouse->exists ? route('warehouses.update', $warehouse) : route('warehouses.store') }}"
          class="w-full bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        @csrf
        @if ($warehouse->exists) @method('PUT') @endif

        <div class="p-5 sm:p-6 space-y-5">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                {{-- ناوی کۆگا --}}
                <div>
                    <label class="block font-bold text-xs text-slate-700 mb-1.5" for="name">
                        ناوی کۆگا / مەعمەل <span class="text-rose-500">*</span>
                    </label>
                    <input id="name" name="name" type="text" required
                           value="{{ old('name', $warehouse->name) }}"
                           placeholder="ناوی کۆگا بنووسە..."
                           class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-hidden focus:border-orange-500 focus:ring-1 focus:ring-orange-500 bg-white font-bold text-slate-900">
                    @error('name') <p class="mt-1 text-[11px] text-rose-600 font-bold">{{ $message }}</p> @enderror
                </div>

                {{-- شوێن --}}
                <div>
                    <label class="block font-bold text-xs text-slate-700 mb-1.5" for="location">
                        ناونیشان / شوێن
                    </label>
                    <input id="location" name="location" type="text"
                           value="{{ old('location', $warehouse->location) }}"
                           placeholder="شەقام، شوێن، یان ناونیشانی ورد..."
                           class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-hidden focus:border-orange-500 bg-white font-medium text-slate-800">
                </div>
            </div>

            {{-- هەڵبژاردەکان --}}
            <div class="pt-3 border-t border-slate-100 flex flex-col sm:flex-row gap-4">
                <label class="inline-flex items-center gap-2.5 cursor-pointer">
                    <input type="checkbox" name="is_default" value="1"
                           @checked(old('is_default', $warehouse->is_default))
                           class="size-4 rounded border-slate-300 text-orange-600 focus:ring-orange-500 cursor-pointer">
                    <span class="text-xs font-bold text-slate-800">کۆگای بنەڕەت بۆ فرۆشتن (Default Warehouse)</span>
                </label>

                <label class="inline-flex items-center gap-2.5 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1"
                           @checked(old('is_active', $warehouse->exists ? $warehouse->is_active : true))
                           class="size-4 rounded border-slate-300 text-orange-600 focus:ring-orange-500 cursor-pointer">
                    <span class="text-xs font-bold text-slate-800">چالاکە</span>
                </label>
            </div>
        </div>

        {{-- بەتەنەکانی پاشەکەوتکردن --}}
        <div class="p-4 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-2.5">
            <a href="{{ route('warehouses.index') }}"
               class="px-4 py-2 rounded-xl text-xs font-bold bg-white hover:bg-slate-100 text-slate-700 border border-slate-200 transition-all">
                پاشگەزبوونەوە
            </a>
            <button type="submit"
                    class="px-6 py-2.5 rounded-xl text-xs font-black bg-orange-600 hover:bg-orange-700 text-white shadow-md shadow-orange-500/20 transition-all cursor-pointer">
                {{ $warehouse->exists ? 'نوێکردنەوەی زانیاری' : 'پاشەکەوتکردن' }}
            </button>
        </div>
    </form>
</div>

@if ($warehouse->exists)
    <form id="delete-warehouse" method="POST" action="{{ route('warehouses.destroy', $warehouse) }}" class="hidden">
        @csrf @method('DELETE')
    </form>
@endif

@endsection
