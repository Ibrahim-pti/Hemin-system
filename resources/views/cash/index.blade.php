@extends('layouts.app')
@section('title', 'قاسە — حیسابی ڕۆژانە')

@section('content')

{{-- هەڵبژاردنی ڕۆژ --}}
<form method="GET" class="card mb-4">
    <div class="card-body flex flex-wrap items-end gap-3">
        <div>
            <label class="label">ڕۆژ</label>
            <input type="date" name="date" value="{{ $date }}" class="field num">
        </div>
        <button class="btn btn-primary">پیشاندان</button>
        <span class="text-sm text-[--color-ink-soft]">{{ fmt_date($date) }}</span>
    </div>
</form>

{{-- کورتەی هەر قاسەیەک --}}
<div class="grid gap-4 lg:grid-cols-2">
    @foreach ($summary as $row)
        @php $box = $row['box']; @endphp
        <div class="card">
            <div class="card-head flex items-center justify-between">
                <span>{{ $box->name }}</span>
                @if ($row['closing'])
                    <span class="badge {{ $row['closing']->is_balanced ? 'badge-ok' : 'badge-danger' }}">
                        داخراوە
                    </span>
                @endif
            </div>

            <div class="card-body space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-[--color-ink-soft]">باڵانسی سەرەتای ڕۆژ</span>
                    <span class="num">{{ fmt_money($row['opening'], $box->currency) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-[--color-ink-soft]">داهات</span>
                    <span class="num text-[--color-ok]">+{{ fmt_money($row['in'], $box->currency) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-[--color-ink-soft]">خەرجی</span>
                    <span class="num text-[--color-danger]">−{{ fmt_money($row['out'], $box->currency) }}</span>
                </div>
                <div class="flex justify-between border-t border-[--color-line] pt-2 text-base font-semibold">
                    <span>پێویستە هەبێت</span>
                    <span class="num">{{ fmt_money($row['expected'], $box->currency) }}</span>
                </div>

                @if ($row['closing'])
                    <div class="flex justify-between">
                        <span class="text-[--color-ink-soft]">ژمێردراو</span>
                        <span class="num">{{ fmt_money($row['closing']->counted_balance, $box->currency) }}</span>
                    </div>
                    <div class="flex justify-between font-medium">
                        <span>جیاوازی</span>
                        <span class="num {{ $row['closing']->is_balanced ? 'text-[--color-ok]' : 'text-[--color-danger]' }}">
                            {{ fmt_money($row['closing']->difference, $box->currency) }}
                        </span>
                    </div>
                @else
                    {{-- داخستنی ڕۆژ --}}
                    <form method="POST" action="{{ route('cash.close') }}" class="border-t border-[--color-line] pt-3">
                        @csrf
                        <input type="hidden" name="cash_box_id" value="{{ $box->id }}">
                        <input type="hidden" name="closing_date" value="{{ $date }}">
                        <label class="label">پارەی ژمێردراو لە قاسەدا</label>
                        <div class="flex gap-2">
                            <input type="number" step="0.01" name="counted_balance" class="field num" required
                                   placeholder="{{ fmt_num($row['expected']) }}">
                            <button class="btn btn-primary whitespace-nowrap">داخستنی ڕۆژ</button>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    @endforeach
</div>

{{-- تۆمارکردنی خەرجی / داهات --}}
<div class="card mt-4">
    <div class="card-head">تۆمارکردنی خەرجی یان داهات</div>
    <form method="POST" action="{{ route('cash.transaction') }}">
        @csrf
        <div class="card-body grid gap-3 sm:grid-cols-2 lg:grid-cols-6">
            <div>
                <label class="label">قاسە</label>
                <select name="cash_box_id" class="field" required>
                    @foreach ($boxes as $box)
                        <option value="{{ $box->id }}">{{ $box->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="label">جۆر</label>
                <select name="direction" class="field">
                    <option value="out">خەرجی</option>
                    <option value="in">داهات</option>
                </select>
            </div>
            <div>
                <label class="label">بڕ</label>
                <input type="number" step="0.01" min="0.01" name="amount" class="field num" required>
            </div>
            <div>
                <label class="label">بابەت</label>
                <select name="category" class="field">
                    @foreach (\App\Models\CashTransaction::CATEGORIES as $key => $label)
                        <option value="{{ $key }}" @selected($key === 'expense')>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="label">بەروار</label>
                <input type="date" name="occurred_at" class="field num" value="{{ $date }}" required>
            </div>
            <div class="flex items-end">
                <button class="btn btn-primary w-full">تۆمارکردن</button>
            </div>
            <div class="sm:col-span-2 lg:col-span-6">
                <label class="label">تێبینی</label>
                <input name="note" class="field" placeholder="بۆ نموونە: کرێی گواستنەوە">
            </div>
        </div>
    </form>
</div>

{{-- جوڵەکانی ڕۆژ --}}
<div class="card mt-4">
    <div class="card-head">جوڵەکانی ئەم ڕۆژە</div>
    <div class="overflow-x-auto">
        <table class="table">
            <thead>
                <tr><th>قاسە</th><th>جۆر</th><th>بابەت</th><th class="num">بڕ</th><th>تێبینی</th><th>بەکارهێنەر</th></tr>
            </thead>
            <tbody>
                @forelse ($transactions as $transaction)
                    <tr>
                        <td>{{ $transaction->cashBox->name }}</td>
                        <td>
                            <span class="badge {{ $transaction->direction === 'in' ? 'badge-ok' : 'badge-warn' }}">
                                {{ $transaction->direction === 'in' ? 'داهات' : 'خەرجی' }}
                            </span>
                        </td>
                        <td>{{ $transaction->category_label }}</td>
                        <td class="num font-medium {{ $transaction->direction === 'in' ? 'text-[--color-ok]' : 'text-[--color-danger]' }}">
                            {{ $transaction->direction === 'in' ? '+' : '−' }}{{ fmt_money($transaction->amount, $transaction->cashBox->currency) }}
                        </td>
                        <td class="text-[--color-ink-soft]">{{ $transaction->note ?? '—' }}</td>
                        <td class="text-[--color-ink-soft]">{{ $transaction->user?->name ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="py-8 text-center text-sm text-[--color-ink-soft]">لەم ڕۆژەدا هیچ جوڵەیەک نییە.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
