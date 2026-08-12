@extends('layouts.app')
@section('title', 'هاتن و چوون')

@section('actions')
    <a href="{{ route('attendance.wages') }}" class="btn btn-ghost">حەقدەستەکان</a>
@endsection

@section('content')

<form method="GET" class="card mb-4">
    <div class="card-body flex flex-wrap items-end gap-3">
        <div>
            <label class="label">ڕۆژ</label>
            <input type="date" name="date" value="{{ $date }}" class="field num">
        </div>
        <button class="btn btn-primary">پیشاندان</button>

        <div class="flex items-center gap-2 text-sm">
            <span class="text-[--color-ink-soft]">{{ fmt_date($date) }}</span>
            @if ($isHoliday)
                <span class="badge badge-warn">هەینی — پشووی هەفتانە</span>
            @endif
        </div>
    </div>
</form>

<form method="POST" action="{{ route('attendance.store') }}">
    @csrf
    <input type="hidden" name="work_date" value="{{ $date }}">

    <div class="card">
        <div class="card-head flex items-center justify-between">
            <span>تۆماری ڕۆژانە</span>
            <button class="btn btn-primary !py-1">پاشەکەوتکردن</button>
        </div>

        <div class="overflow-x-auto">
            <table class="table" style="min-width: 800px">
                <thead>
                    <tr>
                        <th>کارمەند</th><th>پیشە</th><th class="w-40">دۆخ</th>
                        <th class="num w-28">هاتن</th><th class="num w-28">چوون</th>
                        <th class="num">حەقدەستی ڕۆژانە</th><th>تێبینی</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($employees as $employee)
                        @php
                            $record = $records[$employee->id] ?? null;
                            // ئەگەر هێشتا تۆمار نەکراوە: هەینی = پشوو، ئەگەر نا = ئامادە.
                            $status = $record->status ?? ($isHoliday ? 'holiday' : 'present');
                        @endphp
                        <tr>
                            <td class="font-medium">{{ $employee->name }}</td>
                            <td class="text-[--color-ink-soft]">{{ $employee->job_title_label }}</td>
                            <td>
                                <select name="rows[{{ $employee->id }}][status]" class="field !py-1">
                                    @foreach (\App\Models\Attendance::STATUSES as $value => $label)
                                        <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <input type="time" name="rows[{{ $employee->id }}][check_in]"
                                       value="{{ $record?->check_in ? substr($record->check_in, 0, 5) : '' }}"
                                       class="field num !py-1">
                            </td>
                            <td>
                                <input type="time" name="rows[{{ $employee->id }}][check_out]"
                                       value="{{ $record?->check_out ? substr($record->check_out, 0, 5) : '' }}"
                                       class="field num !py-1">
                            </td>
                            <td class="num text-[--color-ink-soft]">
                                {{ fmt_money($employee->daily_wage, $employee->wage_currency) }}
                            </td>
                            <td>
                                <input name="rows[{{ $employee->id }}][note]" value="{{ $record?->note }}"
                                       class="field !py-1">
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-sm text-[--color-ink-soft]">
                                هیچ کارمەندێکی چالاک نییە.
                                <a href="{{ route('employees.create') }}" class="text-[--color-brand-700]">کارمەند زیاد بکە</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-[--color-line] px-4 py-2 text-xs text-[--color-ink-soft]">
            کاتژمێری کار خۆکار حیساب دەکرێت. زیاتر لە {{ \App\Models\Attendance::STANDARD_HOURS }} کاتژمێر دەبێتە کاتی زیادە.
            حەقدەست تەنها بۆ ڕۆژی «ئامادە» دەژمێردرێت.
        </div>
    </div>

    @if ($employees->isNotEmpty())
        <div class="mt-4">
            <button class="btn btn-primary">پاشەکەوتکردن</button>
        </div>
    @endif
</form>

@endsection
