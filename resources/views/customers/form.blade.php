@extends('layouts.app')
@section('title', $customer->exists ? 'دەستکاری کڕیار' : 'زیادکردنی کڕیاری نوێ')

@section('actions')
    <a href="{{ $customer->exists ? route('customers.show', $customer) : route('customers.index') }}"
       class="btn btn-ghost !py-1.5 !px-3 text-xs gap-1 border border-slate-200 hover:bg-slate-100 font-bold text-slate-700">
        <span>&larr;</span>
        <span>گەڕانەوە</span>
    </a>
@endsection

@section('content')

<form method="POST"
      action="{{ $customer->exists ? route('customers.update', $customer) : route('customers.store') }}"
      class="w-full">
    @csrf
    @if ($customer->exists) @method('PUT') @endif

    <div class="bg-white rounded-2xl shadow-xs border border-slate-100 p-6">
        <div class="border-b border-slate-100 pb-4 mb-6 flex items-center justify-between">
            <div class="font-bold text-base text-slate-800 flex items-center gap-2">
                <span>👤</span>
                <span>{{ $customer->exists ? 'دەستکاری زانیاری کڕیار' : 'زانیاری کڕیاری نوێ' }}</span>
            </div>
            @if ($customer->exists)
                <span class="px-2.5 py-0.5 rounded-md text-xs font-mono font-bold text-rose-600 bg-rose-50 border border-rose-100">
                    C-{{ str_pad($customer->id, 5, '0', STR_PAD_LEFT) }}
                </span>
            @endif
        </div>

        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            {{-- ناوی کڕیار --}}
            <div class="sm:col-span-2 lg:col-span-1">
                <label class="block text-xs font-bold text-slate-700 mb-1.5" for="name">
                    ناوی کڕیار <span class="text-rose-500">*</span>
                </label>
                <input id="name" name="name" class="field !py-2.5 !px-3.5 w-full font-bold text-slate-800 rounded-xl" required
                       value="{{ old('name', $customer->name) }}" placeholder="ناوی تەواوی کڕیار بنووسە...">
                @error('name') <p class="mt-1 text-xs text-rose-500 font-medium">{{ $message }}</p> @enderror
            </div>

            {{-- ژمارەی مۆبایل --}}
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5" for="phone">ژمارەی تەلەفۆن / مۆبایل</label>
                <input id="phone" name="phone" class="field num !py-2.5 !px-3.5 w-full rounded-xl" dir="ltr"
                       value="{{ old('phone', $customer->phone) }}" placeholder="07XXXXXXXXX">
                @error('phone') <p class="mt-1 text-xs text-rose-500 font-medium">{{ $message }}</p> @enderror
            </div>

            {{-- ناونیشان --}}
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5" for="address">ناونیشان (شوێن)</label>
                <input id="address" name="address" class="field !py-2.5 !px-3.5 w-full rounded-xl"
                       value="{{ old('address', $customer->address) }}" placeholder="شار، گەڕەک یان ناونیشان...">
                @error('address') <p class="mt-1 text-xs text-rose-500 font-medium">{{ $message }}</p> @enderror
            </div>

            {{-- حیساباتی پێشتر / قەرزی کۆن --}}
            <div class="sm:col-span-2 lg:col-span-3 bg-gradient-to-l from-amber-50/70 to-orange-50/50 border border-amber-200/90 rounded-2xl p-4.5 sm:p-5 shadow-xs">
                <div class="flex flex-wrap items-center justify-between gap-2 mb-2">
                    <div class="flex items-center gap-2">
                        <span class="text-base">📜</span>
                        <label class="text-xs font-black text-amber-950" for="opening_balance">
                            حیساباتی پێشتر / قەرزی کۆن (باڵانسی سەرەتایی)
                        </label>
                    </div>
                    <span class="text-[11px] font-black px-2.5 py-0.5 rounded-full bg-amber-100 text-amber-800 border border-amber-300/80">
                        🚫 ناچێتە کارگە و وەسڵی دروستکردن
                    </span>
                </div>
                <p class="text-[11px] text-slate-600 mb-3.5 font-medium leading-relaxed">
                    ئەگەر لە پێشتردا یان لە دەفتەر ئەم کڕیارە حساباتی کۆنی هەبووە دەتوانی بڕەکەی لێرە بنووسیت. <strong class="text-amber-900 font-bold">ئەم بڕە بە هیچ جۆرێک ناچێتە بەشی کارگە بۆ دروستکردن</strong> و کرێکاران وەک داواکاری نوێ نایبینن.
                </p>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div class="sm:col-span-2">
                        <label class="block text-[11px] font-bold text-slate-700 mb-1" for="opening_balance">بڕی قەرز / حیسابی پێشتر</label>
                        <input id="opening_balance" type="number" step="any" min="0" name="opening_balance"
                               class="field num !py-2.5 !px-3.5 w-full rounded-xl font-black text-rose-600 text-base"
                               value="{{ old('opening_balance', (float) $customer->opening_balance > 0 ? (float) $customer->opening_balance : '') }}"
                               placeholder="0">
                        @error('opening_balance') <p class="mt-1 text-xs text-rose-500 font-medium">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1" for="opening_currency">جۆری دراو</label>
                        <select id="opening_currency" name="opening_currency" class="field !py-2.5 !px-3.5 w-full rounded-xl font-bold text-slate-800">
                            <option value="IQD" @selected(old('opening_currency', $customer->opening_currency ?? 'IQD') === 'IQD')>دیناری عێراقی (د.ع)</option>
                            <option value="USD" @selected(old('opening_currency', $customer->opening_currency ?? 'IQD') === 'USD')>دۆلاری ئەمریکی ($)</option>
                        </select>
                        @error('opening_currency') <p class="mt-1 text-xs text-rose-500 font-medium">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- تێبینی --}}
            <div class="sm:col-span-2 lg:col-span-3">
                <label class="block text-xs font-bold text-slate-700 mb-1.5" for="note">تێبینی</label>
                <textarea id="note" name="note" rows="3" class="field !py-2.5 !px-3.5 w-full rounded-xl"
                          placeholder="تێبینی سەبارەت بەم کڕیارە (ئارەزوومەندانە)...">{{ old('note', $customer->note) }}</textarea>
            </div>

            {{-- دۆخی چالاکبوون --}}
            <div class="sm:col-span-2 lg:col-span-3 pt-2">
                <label class="inline-flex items-center gap-2.5 text-xs font-bold text-slate-700 cursor-pointer select-none">
                    <input type="checkbox" name="is_active" value="1"
                           @checked(old('is_active', $customer->exists ? $customer->is_active : true))
                           class="size-4.5 rounded text-blue-600 focus:ring-blue-500 border-slate-300">
                    <span>کڕیاری چالاکە</span>
                </label>
            </div>
        </div>

        {{-- دوگمەکانی کردار --}}
        <div class="mt-8 pt-6 border-t border-slate-100 flex items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <button type="submit" class="btn btn-primary !py-2.5 !px-6 text-xs font-bold bg-blue-600 hover:bg-blue-700 shadow-sm">
                    {{ $customer->exists ? 'نوێکردنەوەی زانیاری' : 'تۆمارکردنی کڕیار' }}
                </button>
                <a href="{{ $customer->exists ? route('customers.show', $customer) : route('customers.index') }}"
                   class="btn btn-ghost !py-2.5 !px-4 text-xs font-bold border border-slate-200 hover:bg-slate-100 text-slate-700">
                    پاشگەزبوونەوە
                </a>
            </div>

            @if ($customer->exists)
                <button type="submit" form="delete-customer"
                        class="btn btn-ghost !py-2.5 !px-4 text-xs font-bold !text-rose-600 hover:bg-rose-50 border border-rose-200"
                        onclick="return confirm('دڵنیایت لە سڕینەوەی ئەم کڕیارە؟')">
                    🗑️ سڕینەوەی کڕیار
                </button>
            @endif
        </div>
    </div>
</form>

@if ($customer->exists)
    <form id="delete-customer" method="POST" action="{{ route('customers.destroy', $customer) }}" class="hidden">
        @csrf @method('DELETE')
    </form>
@endif

@endsection
