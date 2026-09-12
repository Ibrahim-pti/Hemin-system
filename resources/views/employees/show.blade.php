@extends('layouts.app')
@section('title', $employee->name)

@section('actions')
    <a href="{{ route('payments.create', ['type' => 'out']) }}" class="btn btn-primary">دانی حەقدەست</a>
    <a href="{{ route('employees.edit', $employee) }}" class="btn btn-ghost">دەستکاری</a>
@endsection

@section('content')

<form method="GET" class="card mb-4">
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

<div class="grid grid-cols-2 gap-3 lg:grid-cols-5">
    @include('partials.stat-tile', ['label' => 'پیشە', 'value' => $employee->job_title_label, 'tone' => null])
    @include('partials.stat-tile', ['label' => 'مووچەی جێگیر (' . $employee->salary_type_label . ')', 'value' => fmt_money($employee->daily_wage, $employee->wage_currency), 'tone' => 'primary'])
    @include('partials.stat-tile', ['label' => 'حەقدەستی کۆکراوە', 'value' => fmt_money($earned), 'tone' => null])
    @include('partials.stat-tile', ['label' => 'دراوە', 'value' => fmt_money($paid), 'tone' => 'ok'])
    @include('partials.stat-tile', [
        'label' => 'ماوە',
        'value' => fmt_money($earned - $paid),
        'tone' => $earned - $paid > 0 ? 'warn' : null,
    ])
</div>

<div class="card mt-4">
    <div class="card-head">تۆماری هاتن و چوون</div>
    <div class="overflow-x-auto">
        <table class="table">
            <thead>
                <tr>
                    <th>بەروار</th><th>دۆخ</th><th class="num">هاتن</th><th class="num">چوون</th>
                    <th class="num">کاتژمێر</th><th class="num">زیادە</th><th class="num">حەقدەست</th><th>تێبینی</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($attendances as $record)
                    <tr>
                        <td class="num whitespace-nowrap">{{ fmt_date($record->work_date) }}</td>
                        <td>
                            <span class="badge {{ match ($record->status) {
                                'present' => 'badge-ok',
                                'absent' => 'badge-danger',
                                default => 'badge-warn',
                            } }}">{{ $record->status_label }}</span>
                        </td>
                        <td class="num">{{ $record->check_in ?? '—' }}</td>
                        <td class="num">{{ $record->check_out ?? '—' }}</td>
                        <td class="num">{{ fmt_num($record->hours, 2) }}</td>
                        <td class="num {{ $record->overtime_hours > 0 ? 'text-[--color-warn]' : '' }}">
                            {{ fmt_num($record->overtime_hours, 2) }}
                        </td>
                        <td class="num">{{ fmt_money($record->wage_snapshot) }}</td>
                        <td class="text-[--color-ink-soft]">{{ $record->note ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="py-8 text-center text-sm text-[--color-ink-soft]">لەم ماوەیەدا تۆمار نییە.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="card mt-4">
    <div class="card-head flex items-center justify-between">
        <div class="flex items-center gap-2">
            <span>💸</span>
            <span>مێژووی پارەدان و وەسڵەکانی قاصە</span>
            <span class="badge">{{ $payments->count() }}</span>
        </div>
        <a href="{{ route('payments.create', ['type' => 'out', 'employee_id' => $employee->id]) }}" class="btn btn-sm btn-outline">
            + وەسڵی نوێ
        </a>
    </div>
    <div class="overflow-x-auto">
        <table class="table">
            <thead>
                <tr>
                    <th>ژ. وەسڵ</th>
                    <th>بەروار</th>
                    <th>قاسە</th>
                    <th>جۆری جوڵە</th>
                    <th class="num">بڕی پارە</th>
                    <th>تێبینی</th>
                    <th class="text-center">چاپکردن</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($payments as $payment)
                    <tr>
                        <td class="font-mono font-bold">#{{ $payment->voucher_no }}</td>
                        <td class="num whitespace-nowrap">{{ fmt_date($payment->paid_at) }}</td>
                        <td>{{ $payment->cashBox?->name ?? 'قاسە' }}</td>
                        <td>
                            <span class="badge {{ $payment->direction === 'in' ? 'badge-ok' : 'badge-primary' }}">
                                {{ $payment->payment_type_label ?? ($payment->isAdvance() ? 'پێدانی قەرز' : ($payment->isDebtRepayment() ? 'دانەوەی قەرز' : 'مووچە')) }}
                            </span>
                        </td>
                        <td class="num font-black {{ $payment->direction === 'in' ? 'text-[--color-ok]' : '' }}">
                            {{ $payment->direction === 'in' ? '+' : '-' }} {{ fmt_money($payment->amount, $payment->currency) }}
                        </td>
                        <td class="text-[--color-ink-soft]">{{ $payment->note ?? '—' }}</td>
                        <td class="text-center">
                            <a href="{{ route('payments.print', $payment) }}" target="_blank" class="btn btn-xs btn-ghost" title="چاپکردنی وەسڵ">
                                🖨️ چاپ
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="py-8 text-center text-sm text-[--color-ink-soft]">هیچ وەسڵێکی پارەدان بۆ ئەم کارمەندە تۆمار نەکراوە.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
