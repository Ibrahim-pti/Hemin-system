@extends('layouts.app')
@section('title', $supplier->exists ? 'دەستکاری فرۆشیار' : 'فرۆشیاری نوێ')

@section('content')

<form method="POST"
      action="{{ $supplier->exists ? route('suppliers.update', $supplier) : route('suppliers.store') }}"
      class="w-full"
      x-data="{
          hasMaterial: false,
          paymentType: 'debt',
          qty: '',
          price: '',
          paidAmount: '',
          get total() {
              let q = parseFloat(this.qty) || 0;
              let p = parseFloat(this.price.toString().replace(/,/g, '')) || 0;
              return q * p;
          },
          formatInput(e) {
              let clean = e.target.value.replace(/[^0-9.]/g, '');
              let parts = clean.split('.');
              if (parts.length > 2) parts = [parts[0], parts.slice(1).join('')];
              let int = parts[0] ? parseInt(parts[0], 10).toLocaleString('en-US') : '';
              let dec = parts.length > 1 ? '.' + parts[1] : '';
              e.target.value = int ? int + dec : '';
          }
      }">
    @csrf
    @if ($supplier->exists) @method('PUT') @endif

    <div class="space-y-4">
        {{-- ١. کارتی زانیاری فرۆشیار --}}
        <div class="card">
            <div class="card-head flex items-center justify-between">
                <span>{{ $supplier->exists ? 'دەستکاریکردنی زانیارییەکانی فرۆشیار' : 'تۆمارکردنی فرۆشیاری نوێ' }}</span>
                <a href="{{ route('suppliers.index') }}" class="btn btn-ghost !py-1 text-xs">گەڕانەوە &larr;</a>
            </div>

            <div class="card-body grid gap-4 sm:grid-cols-2 lg:grid-cols-3">

                @if ($errors->any())
                    <div class="sm:col-span-2 lg:col-span-3 p-4 rounded-xl bg-red-50 text-red-700 border border-red-200 text-xs">
                        <div class="font-bold text-sm mb-1">تکایە ئەم کێشانە چاک بکە:</div>
                        <ul class="list-disc list-inside space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- ناوی فرۆشیار --}}
                <div class="sm:col-span-2 lg:col-span-1">
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

                {{-- تێبینی --}}
                <div class="sm:col-span-2 lg:col-span-3">
                    <label class="label" for="note">تێبینی</label>
                    <input id="note" name="note" class="field"
                           value="{{ old('note', $supplier->note) }}" placeholder="تێبینی تایبەت لەسەر فرۆشیار...">
                </div>

                <input type="hidden" name="opening_currency" value="IQD">
                <input type="hidden" name="is_active" value="1">
            </div>
        </div>

        {{-- ٢. کارتی هێنانی مەواد (کڕینی سەرەتایی) --}}
        @if (!$supplier->exists)
            <div class="card">
                <div class="card-head flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span>هێنانی مەواد / کڕین</span>
                        <span class="text-xs font-normal text-[--color-ink-soft]">(ئارەزوومەندانە - دەتوانیت لەگەڵ تۆمارکردن مەوادی کڕدراو بنووسیت)</span>
                    </div>
                </div>

                <div class="card-body space-y-4">
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        {{-- بەرواری هێنانی مەواد --}}
                        <div>
                            <label class="label" for="purchase_date">بەرواری هێنانی مەواد</label>
                            <input id="purchase_date" name="purchase_date" type="date" class="field num"
                                   value="{{ old('purchase_date', now()->toDateString()) }}">
                        </div>

                        {{-- ناوی مەواد --}}
                        <div>
                            <label class="label" for="item_id">ناوی مەواد</label>
                            <select id="item_id" name="item_id" class="field">
                                <option value="">— هیچ مەوادێک دیاری نەکراوە —</option>
                                @foreach ($items as $item)
                                    <option value="{{ $item->id }}" @selected(old('item_id') == $item->id)>
                                        {{ $item->name }} ({{ $item->unit?->name ?? 'دانە' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- بڕ --}}
                        <div>
                            <label class="label" for="purchase_qty">بڕ</label>
                            <input id="purchase_qty" name="purchase_qty" type="number" step="any" min="0"
                                   x-model="qty"
                                   class="field num" placeholder="0">
                        </div>

                        {{-- نرخی تاک --}}
                        <div>
                            <label class="label" for="purchase_unit_price">نرخی تاک (تێچووی کڕین)</label>
                            <div class="relative">
                                <input id="purchase_unit_price" name="purchase_unit_price" type="text" inputmode="numeric"
                                       x-model="price"
                                       @input="formatInput($event)"
                                       class="field num pl-14 font-semibold" placeholder="0">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-xs font-bold text-slate-400 pointer-events-none">
                                    د.ع
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- شێوازی پارەدان و کۆی گشتی --}}
                    <div class="pt-3 border-t border-[--color-line] grid gap-4 sm:grid-cols-2 lg:grid-cols-3 items-end">
                        {{-- شێوازی پارەدان --}}
                        <div>
                            <label class="label" for="payment_type">شێوازی پارەدان</label>
                            <select id="payment_type" name="payment_type" x-model="paymentType" class="field font-medium">
                                <option value="debt">قەرز (هەمووی دەچێتە سەر قەرز)</option>
                                <option value="full">پارەدانی کامل (کاش و واصلکراو)</option>
                                <option value="partial">بەشێکی دراو و بەشێکی قەرز</option>
                            </select>
                        </div>

                        {{-- بڕی پارەی دراو ئەگەر partial بێت --}}
                        <div x-show="paymentType === 'partial'">
                            <label class="label" for="paid_amount">بڕی پارەی دراو (کاش)</label>
                            <div class="relative">
                                <input id="paid_amount" name="paid_amount" type="text" inputmode="numeric"
                                       @input="formatInput($event)"
                                       class="field num pl-14 font-semibold text-emerald-700" placeholder="0">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-xs font-bold text-slate-400 pointer-events-none">
                                    د.ع
                                </span>
                            </div>
                        </div>

                        {{-- کۆی گشتی تێچووی کڕین --}}
                        <div class="bg-slate-50 p-2.5 rounded-lg border border-slate-200">
                            <span class="text-xs text-[--color-ink-soft] block">کۆی تێچووی کڕین:</span>
                            <span class="text-base font-bold text-slate-900 num" x-text="(total).toLocaleString('en-US') + ' د.ع'">0 د.ع</span>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- دوگمەکانی خوارەوە --}}
    <div class="mt-4 flex gap-2">
        <button class="btn btn-primary">{{ $supplier->exists ? 'نوێکردنەوە' : 'زیادکردنی فرۆشیار' }}</button>
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
