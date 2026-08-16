@extends('layouts.app')
@section('title', $supplier->exists ? 'دەستکاری فرۆشیار' : 'فرۆشیاری نوێ')

@section('content')

<form method="POST"
      action="{{ $supplier->exists ? route('suppliers.update', $supplier) : route('suppliers.store') }}"
      class="mx-auto max-w-4xl">
    @csrf
    @if ($supplier->exists) @method('PUT') @endif

    <div class="card border-0 ring-1 ring-[--color-line] shadow-sm rounded-[14px] overflow-hidden bg-white">
        {{-- سەرەوەی کارتی فۆرم --}}
        <div class="bg-[--color-surface-soft]/60 px-6 py-4 border-b border-[--color-line] flex items-center justify-between">
            <div class="flex items-center gap-3">
                <span class="icon-chip bg-[--color-brand-soft] text-[--color-brand-700] size-9 rounded-lg">
                    @include('partials.icon', ['name' => 'suppliers', 'class' => 'size-5'])
                </span>
                <div>
                    <h2 class="font-bold text-[16px] text-[--color-ink]">
                        {{ $supplier->exists ? 'دەستکاریکردنی زانیارییەکانی فرۆشیار' : 'تۆمارکردنی فرۆشیاری نوێ' }}
                    </h2>
                    <p class="text-xs text-[--color-ink-soft]">ناو، مۆبایل، ناونیشان و قەرزی پێشووی فرۆشیار لێرە دیاری بکە</p>
                </div>
            </div>
            
            <a href="{{ route('suppliers.index') }}" class="btn btn-ghost !py-1.5 !px-3 text-xs font-semibold text-[--color-ink-soft] hover:text-[--color-brand-700]">
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

            {{-- ڕیزی ١: ناوی فرۆشیار --}}
            <div>
                <label class="label text-xs font-bold text-[--color-ink-soft] mb-1.5" for="name">
                    ناوی فرۆشیار / کۆمپانیا <span class="text-[--color-danger]">*</span>
                </label>
                <input id="name" name="name" class="field py-2.5 text-sm" required
                       value="{{ old('name', $supplier->name) }}" placeholder="ناوی فرۆشیار بنووسە">
                @error('name') <p class="mt-1 text-xs text-[--color-danger]">{{ $message }}</p> @enderror
            </div>

            {{-- ڕیزی ٢: مۆبایل، شوێن، قەرزی ماوەی سەرەتایی، و دراو --}}
            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                {{-- مۆبایل --}}
                <div>
                    <label class="label text-xs font-bold text-[--color-ink-soft] mb-1.5" for="phone">
                        ژمارەی مۆبایل <span class="text-[11px] font-normal text-gray-400">(ئارەزوومەندانە)</span>
                    </label>
                    <input id="phone" name="phone" class="field num py-2.5 text-sm" dir="ltr"
                           value="{{ old('phone', $supplier->phone) }}" placeholder="0750xxxxxxx">
                    @error('phone') <p class="mt-1 text-xs text-[--color-danger]">{{ $message }}</p> @enderror
                </div>

                {{-- شوێن --}}
                <div>
                    <label class="label text-xs font-bold text-[--color-ink-soft] mb-1.5" for="address">
                        شوێن / ناونیشان <span class="text-[11px] font-normal text-gray-400">(ئارەزوومەندانە)</span>
                    </label>
                    <input id="address" name="address" class="field py-2.5 text-sm"
                           value="{{ old('address', $supplier->address) }}" placeholder="شار، ناوچە، ناونیشان...">
                    @error('address') <p class="mt-1 text-xs text-[--color-danger]">{{ $message }}</p> @enderror
                </div>

                {{-- قەرزی پێشوو / باڵانسی سەرەتایی --}}
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
                    <label class="label text-xs font-bold text-[--color-ink-soft] mb-1.5" for="opening_balance">
                        قەرزی ماوەی پێشوو <span class="text-[11px] font-normal text-gray-400">(ئەگەر هەبێت)</span>
                    </label>
                    <div class="relative">
                        <input id="opening_balance" name="opening_balance" type="text" inputmode="numeric"
                               value="{{ old('opening_balance', ($supplier->exists && (float)$supplier->opening_balance > 0) ? number_format((float)$supplier->opening_balance, 0, '.', ',') : '') }}"
                               @input="formatInput($event)"
                               class="field num py-2.5 text-sm pl-14 font-semibold"
                               placeholder="0">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-xs font-bold text-gray-400 pointer-events-none">
                            د.ع
                        </span>
                    </div>
                    @error('opening_balance') <p class="mt-1 text-xs text-[--color-danger]">{{ $message }}</p> @enderror
                    <input type="hidden" name="opening_currency" value="IQD">
                </div>
            </div>

            {{-- ڕیزی ٣: تێبینی --}}
            <div>
                <label class="label text-xs font-bold text-[--color-ink-soft] mb-1.5" for="note">
                    تێبینی <span class="text-[11px] font-normal text-gray-400">(ئارەزوومەندانە)</span>
                </label>
                <textarea id="note" name="note" rows="2" class="field text-sm" placeholder="تێبینی، کورتە زانیاری لەسەر فرۆشیار...">{{ old('note', $supplier->note) }}</textarea>
            </div>

            <input type="hidden" name="is_active" value="1">
        </div>

        {{-- دوگمەکانی خوارەوە --}}
        <div class="bg-[--color-surface-soft]/60 px-6 md:px-8 py-4 border-t border-[--color-line] flex items-center justify-between">
            <div class="flex items-center gap-3">
                <button class="btn btn-primary shadow-sm hover:shadow-md transition-all px-7 py-2.5 text-sm font-semibold">
                    {{ $supplier->exists ? 'نوێکردنەوەی فرۆشیار' : 'زیادکردنی فرۆشیار' }}
                </button>
                <a href="{{ route('suppliers.index') }}" class="btn btn-ghost px-4 py-2.5 text-sm">پاشگەزبوونەوە</a>
            </div>

            @if ($supplier->exists)
                <button type="submit" form="delete-supplier" class="btn btn-ghost !text-[--color-danger] hover:!bg-red-50 text-xs font-semibold"
                        onclick="return confirm('دڵنیایت لە سڕینەوەی ئەم فرۆشیارە؟')">
                    سڕینەوەی فرۆشیار
                </button>
            @endif
        </div>
    </div>
</form>

@if ($supplier->exists)
    <form id="delete-supplier" method="POST" action="{{ route('suppliers.destroy', $supplier) }}" class="hidden">
        @csrf @method('DELETE')
    </form>
@endif

@endsection
