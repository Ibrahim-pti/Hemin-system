@extends('layouts.app')
@section('title', 'کەشف حساب — ' . $customer->name)

@section('actions')
    <button onclick="window.print()" class="btn btn-ghost no-print">چاپ</button>
@endsection

@section('content')

{{-- ماوەی کات --}}
<form method="GET" class="card mb-4 no-print">
    <div class="card-body grid gap-3 sm:grid-cols-3">
        <div>
            <label class="label">لە بەرواری</label>
            <input type="date" name="from" value="{{ $from->toDateString() }}" class="field num">
        </div>
        <div>
            <label class="label">تا بەرواری</label>
            <input type="date" name="to" value="{{ $to->toDateString() }}" class="field num">
        </div>
        <div class="flex items-end">
            <button class="btn btn-primary w-full">پیشاندان</button>
        </div>
    </div>
</form>

{{-- سەردێڕی چاپ --}}
<div class="card">
    <div class="card-body border-b border-[--color-line]">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h2 class="text-lg font-semibold">{{ \App\Models\Setting::get('company_name') }}</h2>
                <p class="text-sm text-[--color-ink-soft]">کەشف حساب</p>
            </div>
            <div class="text-sm">
                <div><span class="text-[--color-ink-soft]">بەڕێز:</span> <span class="font-medium">{{ $customer->name }}</span></div>
                <div class="num" dir="ltr">{{ $customer->phone }}</div>
                <div class="num text-[--color-ink-soft]">{{ fmt_date($from) }} — {{ fmt_date($to) }}</div>
            </div>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="table">
            <thead>
                <tr>
                    <th>بەروار</th>
                    <th>بەڵگەنامە</th>
                    <th>ڕوونکردنەوە</th>
                    <th class="num">لەسەری</th>
                    <th class="num">بۆی</th>
                    <th class="num">باڵانس</th>
                </tr>
            </thead>
            <tbody>
                {{-- باڵانسی پێشوو --}}
                <tr class="bg-[--color-surface-soft]">
                    <td colspan="5" class="font-medium">باڵانسی پێشوو</td>
                    <td class="num font-semibold">{{ fmt_money($openingBalance) }}</td>
                </tr>

                @foreach ($rows as $row)
                    <tr>
                        <td class="num whitespace-nowrap">{{ fmt_date($row['date']) }}</td>
                        <td class="num">
                            <a href="{{ $row['link'] }}" class="text-[--color-brand-700]">{{ $row['ref'] }}</a>
                        </td>
                        <td class="text-[--color-ink-soft]">{{ $row['description'] }}</td>
                        <td class="num">{{ $row['debit'] > 0 ? fmt_money($row['debit']) : '—' }}</td>
                        <td class="num text-[--color-ok]">{{ $row['credit'] > 0 ? fmt_money($row['credit']) : '—' }}</td>
                        <td class="num font-medium">{{ fmt_money($row['balance']) }}</td>
                    </tr>
                @endforeach

                @if ($rows->isEmpty())
                    <tr>
                        <td colspan="6" class="py-8 text-center text-sm text-[--color-ink-soft]">
                            لەم ماوەیەدا هیچ مامەڵەیەک نییە.
                        </td>
                    </tr>
                @endif
            </tbody>
            <tfoot>
                <tr class="bg-[--color-surface-soft]">
                    <td colspan="3" class="font-semibold">کۆتا باڵانس</td>
                    <td class="num">{{ fmt_money($rows->sum('debit')) }}</td>
                    <td class="num">{{ fmt_money($rows->sum('credit')) }}</td>
                    <td class="num text-base font-bold {{ $closingBalance > 0 ? 'text-[--color-danger]' : 'text-[--color-ok]' }}">
                        {{ fmt_money($closingBalance) }}
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<p class="mt-3 text-xs text-[--color-ink-soft]">
    «لەسەری» = ئەوەی کڕیار قەرزاری بووە &nbsp;·&nbsp; «بۆی» = ئەوەی داویەتی. باڵانسی ئەرێنی واتە قەرزارە.
</p>

@endsection
