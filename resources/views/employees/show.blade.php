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

<div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
    @include('partials.stat-tile', ['label' => 'پیشە', 'value' => $employee->job_title_label, 'tone' => null])
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

@endsection
