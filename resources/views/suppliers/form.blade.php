@extends('layouts.app')
@section('title', $supplier->exists ? 'دەستکاری فرۆشیار' : 'فرۆشیاری نوێ')

@section('content')

<form method="POST"
      action="{{ $supplier->exists ? route('suppliers.update', $supplier) : route('suppliers.store') }}"
      class="w-full"
      x-data="{
          lines: [
              { name: '', qty: '', unit_price: '', unit_id: '{{ $units->first()?->id ?? 1 }}' }
          ],
          paymentType: 'debt',
          paidAmount: '',
          addLine() {
              this.lines.push({ name: '', qty: '', unit_price: '', unit_id: '{{ $units->first()?->id ?? 1 }}' });
          },
          removeLine(index) {
              if (this.lines.length > 1) {
                  this.lines.splice(index, 1);
              } else {
                  this.lines[0] = { name: '', qty: '', unit_price: '', unit_id: '{{ $units->first()?->id ?? 1 }}' };
              }
          },
          lineTotal(line) {
              let q = parseFloat(line.qty) || 0;
              let p = parseFloat(line.unit_price.toString().replace(/,/g, '')) || 0;
              return q * p;
          },
          get grandTotal() {
              return this.lines.reduce((sum, line) => sum + this.lineTotal(line), 0);
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

    {{-- لیستی پێشنیارکراوی کاڵاکان بۆ خۆتەواوکردن لە کاتی نووسیندا --}}
    <datalist id="existing-items">
        @foreach ($items as $item)
            <option value="{{ $item->name }}">
        @endforeach
    </datalist>

    <div class="card">
        {{-- سەردێڕی فۆرم --}}
        <div class="card-head flex items-center justify-between">
            <span>{{ $supplier->exists ? 'دەستکاریکردنی زانیارییەکانی فرۆشیار' : 'تۆمارکردنی فرۆشیاری نوێ' }}</span>
            <a href="{{ route('suppliers.index') }}" class="btn btn-ghost !py-1 text-xs">گەڕانەوە &larr;</a>
        </div>

        <div class="card-body space-y-5">

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

            {{-- بەشی زانیاری فرۆشیار --}}
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
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
            </div>

            <input type="hidden" name="opening_currency" value="IQD">
            <input type="hidden" name="is_active" value="1">

            {{-- بەشی هێنانی مەواد و شێوازی پارەدان لە سەرەوەی خشتە --}}
            @if (!$supplier->exists)
                <div class="space-y-4 pt-2">
                    {{-- بەروار و شێوازی پارەدان لە سەرەوە --}}
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 items-end">
                        {{-- بەرواری هێنان --}}
                        <div>
                            <label class="label" for="purchase_date">بەرواری هێنانی مەواد</label>
                            <input id="purchase_date" name="purchase_date" type="date" class="field num"
                                   value="{{ old('purchase_date', now()->toDateString()) }}">
                        </div>

                        {{-- شێوازی پارەدان --}}
                        <div>
                            <label class="label" for="payment_type">شێوازی پارەدان</label>
                            <select id="payment_type" name="payment_type" x-model="paymentType" class="field font-medium">
                                <option value="debt">قەرز (هەمووی دەچێتە سەر قەرزی کارگە)</option>
                                <option value="full">پارەدانی کامل (کاش و واصل بە تەواوی)</option>
                                <option value="partial">بەشێکی دراو و بەشێکی قەرز</option>
                            </select>
                        </div>

                        {{-- بڕی پارەی دراو ئەگەر بەشێکی دراو بێت --}}
                        <div x-show="paymentType === 'partial'">
                            <label class="label" for="paid_amount">بڕی پارەی دراو (کاش)</label>
                            <div class="relative">
                                <input id="paid_amount" name="paid_amount" type="text" inputmode="numeric"
                                       @input="formatInput($event)"
                                       class="field num pl-14 font-semibold text-emerald-700" placeholder="0">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-xs font-bold text-slate-400 pointer-events-none">
                                    د.ع
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- خشتەی مەوادەکان بە دیزاینی خاوێن --}}
                    <div class="overflow-x-auto rounded-lg border border-[--color-line]">
                        <table class="table w-full" style="direction: rtl;">
                            <thead>
                                <tr class="bg-slate-50 border-b border-[--color-line] text-xs text-slate-700">
                                    <th style="width: 44px; text-align: center; padding: 10px 8px;">#</th>
                                    <th style="text-align: right; padding: 10px 12px;">ناوی مەواد</th>
                                    <th style="width: 140px; text-align: right; padding: 10px 12px;">یەکە</th>
                                    <th style="width: 140px; text-align: right; padding: 10px 12px;">بڕ</th>
                                    <th style="width: 180px; text-align: right; padding: 10px 12px;">نرخی تاک (تێچوو)</th>
                                    <th style="width: 160px; text-align: right; padding: 10px 12px;">کۆی گشتی</th>
                                    <th style="width: 44px; text-align: center; padding: 10px 8px;"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-sm">
                                <template x-for="(line, index) in lines" :key="index">
                                    <tr>
                                        {{-- # --}}
                                        <td style="text-align: center; padding: 8px;" class="text-xs text-slate-400 font-bold" x-text="index + 1"></td>

                                        {{-- ناوی مەواد --}}
                                        <td style="padding: 8px 12px;">
                                            <input type="text"
                                                   :name="'purchase_lines[' + index + '][name]'"
                                                   x-model="line.name"
                                                   list="existing-items"
                                                   class="field w-full !py-1.5 text-sm"
                                                   placeholder="ناوی مەواد بنووسە...">
                                        </td>

                                        {{-- یەکە --}}
                                        <td style="padding: 8px 12px;">
                                            <select :name="'purchase_lines[' + index + '][unit_id]'"
                                                    x-model="line.unit_id"
                                                    class="field w-full !py-1.5 text-sm">
                                                @foreach ($units as $unit)
                                                    <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                                                @endforeach
                                            </select>
                                        </td>

                                        {{-- بڕ --}}
                                        <td style="padding: 8px 12px;">
                                            <input type="number" step="any" min="0"
                                                   :name="'purchase_lines[' + index + '][qty]'"
                                                   x-model="line.qty"
                                                   class="field num w-full !py-1.5 text-sm text-right font-medium"
                                                   placeholder="0">
                                        </td>

                                        {{-- نرخی تاک --}}
                                        <td style="padding: 8px 12px;">
                                            <div class="relative">
                                                <input type="text" inputmode="numeric"
                                                       :name="'purchase_lines[' + index + '][unit_price]'"
                                                       x-model="line.unit_price"
                                                       @input="formatInput($event)"
                                                       class="field num w-full !py-1.5 text-sm pl-12 font-semibold text-right"
                                                       placeholder="0">
                                                <span class="absolute inset-y-0 left-0 flex items-center pl-2.5 text-xs font-bold text-slate-400 pointer-events-none">
                                                    د.ع
                                                </span>
                                            </div>
                                        </td>

                                        {{-- کۆی گشتی دێڕ --}}
                                        <td class="num font-bold text-slate-900" style="padding: 8px 12px; text-align: right;">
                                            <span x-text="lineTotal(line).toLocaleString('en-US') + ' د.ع'">0 د.ع</span>
                                        </td>

                                        {{-- سڕینەوە --}}
                                        <td style="text-align: center; padding: 8px;">
                                            <button type="button" @click="removeLine(index)"
                                                    class="inline-flex items-center justify-center size-8 rounded-lg text-rose-500 hover:text-rose-700 hover:bg-rose-50 transition-colors"
                                                    title="سڕینەوە">
                                                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <polyline points="3 6 5 6 21 6"></polyline>
                                                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    {{-- دوگمەی زیادکردنی مەوادی تر و کۆی گشتی لە خوار خشتە --}}
                    <div class="flex items-center justify-between pt-1">
                        <button type="button" @click="addLine()" class="btn btn-ghost !py-1.5 !px-3 text-xs font-semibold text-[--color-brand-700] hover:bg-blue-50 border border-dashed border-blue-300">
                            + زیادکردنی مەوادی تر
                        </button>

                        <div class="bg-slate-50 px-4 py-2 rounded-lg border border-slate-200 flex items-center gap-3">
                            <span class="text-xs text-[--color-ink-soft]">کۆی گشتی کڕین:</span>
                            <span class="text-base font-bold text-slate-900 num" x-text="grandTotal.toLocaleString('en-US') + ' د.ع'">0 د.ع</span>
                        </div>
                    </div>
                </div>
            @endif

        </div>
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
