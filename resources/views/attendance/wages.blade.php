@extends('layouts.app')
@section('title', 'حەقدەستەکان')

@section('actions')
    <button onclick="window.print()" class="btn btn-ghost no-print">چاپ</button>
@endsection

@section('content')

<form method="GET" class="card mb-4 no-print">
    <div class="card-body flex flex-wrap items-end gap-3">
        <div>
            <label class="label">لە بەرواری</label>
            <input type="date" name="from" value="{{ $from }}" class="field num">
        </div>
        <div>
            <label class="label">تا بەرواری</label>
            <input type="date" name="to" value="{{ $to }}" class="field num">
        </div>
        <button class="btn btn-primary">پیشاندان</button>
    </div>
</form>

<div class="card">
    <div class="card-head">
        حەقدەست — <span class="num">{{ fmt_date($from) }} تا {{ fmt_date($to) }}</span>
    </div>
    <div class="overflow-x-auto">
        <table class="table">
            <thead>
                <tr>
                    <th>کارمەند</th><th>پیشە</th>
                    <th class="num">ڕۆژی ئامادە</th><th class="num">کاتی زیادە</th>
                    <th class="num">کۆی حەقدەست</th><th class="num">دراوە</th><th class="num">ماوە</th><th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $row)
                    @php $employee = $row['employee']; @endphp
                    <tr>
                        <td>
                            <a href="{{ route('employees.show', $employee) }}" class="font-medium text-[--color-brand-700]">
                                {{ $employee->name }}
                            </a>
                        </td>
                        <td class="text-[--color-ink-soft]">{{ $employee->job_title_label }}</td>
                        <td class="num">{{ fmt_num($row['days']) }}</td>
                        <td class="num">{{ fmt_num($row['overtime'], 2) }}</td>
                        <td class="num">{{ fmt_money($row['earned']) }}</td>
                        <td class="num text-[--color-ok]">{{ fmt_money($row['paid']) }}</td>
                        <td class="num font-medium {{ $row['remaining'] > 0 ? 'text-[--color-warn]' : '' }}">
                            {{ fmt_money($row['remaining']) }}
                        </td>
                        <td class="text-left no-print">
                            <a href="{{ route('payments.create', ['type' => 'out']) }}"
                               class="text-sm text-[--color-brand-700]">حەقدی</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="py-8 text-center text-sm text-[--color-ink-soft]">هیچ کارمەندێک نییە.</td></tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="bg-[--color-surface-soft] font-semibold">
                    <td colspan="4">کۆی گشتی</td>
                    <td class="num">{{ fmt_money($rows->sum('earned')) }}</td>
                    <td class="num">{{ fmt_money($rows->sum('paid')) }}</td>
                    <td class="num">{{ fmt_money($rows->sum('remaining')) }}</td>
                    <td class="no-print"></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

@endsection
