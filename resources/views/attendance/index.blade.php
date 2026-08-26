@extends('layouts.app')
@section('title', 'تۆماری ئامادەبوونی کارمەندان')

@section('actions')
    <a href="{{ route('attendance.wages') }}" class="btn btn-ghost">حەقدەست و ئامارەکان</a>
@endsection

@section('content')

{{-- فلتەری بەروار --}}
<form method="GET" class="card mb-4">
    <div class="card-body flex flex-wrap items-end justify-between gap-3">
        <div class="flex items-center gap-3">
            <div>
                <label class="label">ڕۆژ</label>
                <input type="date" name="date" value="{{ $date }}" class="field num">
            </div>
            <button class="btn btn-primary self-end">پیشاندان</button>

            <div class="flex items-center gap-2 text-sm self-end pb-2">
                <span class="text-[--color-ink-soft]">{{ fmt_date($date) }}</span>
                @if ($isHoliday)
                    <span class="badge badge-warn">هەینی — پشووی هەفتانە</span>
                @endif
            </div>
        </div>
    </div>
</form>

<form method="POST" action="{{ route('attendance.store') }}">
    @csrf
    <input type="hidden" name="work_date" value="{{ $date }}">

    <div class="card">
        <div class="card-head flex items-center justify-between">
            <span>تۆماری ڕۆژانەی کارمەندان و وەستاکان</span>
            <button class="btn btn-primary !py-1.5 !px-4">پاشەکەوتکردنی هەمووان</button>
        </div>

        <div class="overflow-x-auto">
            <table class="table" style="min-width: 1100px">
                <thead>
                    <tr>
                        <th>کارمەند</th>
                        <th>پیشە</th>
                        <th class="w-36">دۆخ</th>
                        <th class="num w-28">کاتی هاتن</th>
                        <th class="num w-28">کاتی چوون</th>
                        <th class="num w-24">کاتی زیادە (کاتژمێر)</th>
                        <th class="w-48">دەرچوونی کاتی (کاتژمێر / هۆکار)</th>
                        <th class="w-56">سەردانی ماڵان و بەنزین</th>
                        <th>تێبینی</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($employees as $employee)
                        @php
                            $record = $records[$employee->id] ?? null;
                            $status = $record->status ?? ($isHoliday ? 'holiday' : 'present');
                        @endphp
                        <tr>
                            <td>
                                <div class="font-bold text-slate-800">{{ $employee->name }}</div>
                                <div class="text-xs text-slate-400 font-mono">{{ fmt_money($employee->daily_wage, $employee->wage_currency) }} / ڕۆژ</div>
                            </td>
                            <td class="text-[--color-ink-soft]">{{ $employee->job_title_label }}</td>
                            <td>
                                <select name="rows[{{ $employee->id }}][status]" class="field !py-1 text-xs font-bold">
                                    @foreach (\App\Models\Attendance::STATUSES as $value => $label)
                                        <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <input type="time" name="rows[{{ $employee->id }}][check_in]"
                                       value="{{ $record?->check_in ? substr($record->check_in, 0, 5) : '' }}"
                                       class="field num !py-1 text-xs">
                            </td>
                            <td>
                                <input type="time" name="rows[{{ $employee->id }}][check_out]"
                                       value="{{ $record?->check_out ? substr($record->check_out, 0, 5) : '' }}"
                                       class="field num !py-1 text-xs">
                            </td>
                            <td>
                                <input type="number" step="0.5" min="0" name="rows[{{ $employee->id }}][overtime_hours]"
                                       value="{{ $record?->overtime_hours > 0 ? $record->overtime_hours : '' }}"
                                       placeholder="زیادە..."
                                       class="field num !py-1 text-xs">
                            </td>
                            <td>
                                <div class="flex items-center gap-1">
                                    <input type="number" step="0.5" min="0" name="rows[{{ $employee->id }}][temporary_exit_hours]"
                                           value="{{ $record?->temporary_exit_hours > 0 ? $record->temporary_exit_hours : '' }}"
                                           placeholder="کاتژمێر"
                                           class="field num !py-1 !w-16 text-xs" title="کاتژمێری ڕۆیشتن لە ناو ئیش">
                                    <input type="text" name="rows[{{ $employee->id }}][exit_reason]"
                                           value="{{ $record?->exit_reason }}"
                                           placeholder="هۆکار..."
                                           class="field !py-1 text-xs flex-1">
                                </div>
                            </td>
                            <td>
                                <div class="flex items-center gap-1">
                                    <input type="text" name="rows[{{ $employee->id }}][trip_destination]"
                                           value="{{ $record?->trip_destination }}"
                                           placeholder="ماڵی کڕیار / شوێن"
                                           class="field !py-1 text-xs flex-1">
                                    <input type="number" step="500" min="0" name="rows[{{ $employee->id }}][fuel_expense]"
                                           value="{{ $record?->fuel_expense > 0 ? $record->fuel_expense : '' }}"
                                           placeholder="بەنزین (د.ع)"
                                           class="field num !py-1 !w-24 text-xs">
                                </div>
                            </td>
                            <td>
                                <input name="rows[{{ $employee->id }}][note]" value="{{ $record?->note }}"
                                       placeholder="تێبینی..."
                                       class="field !py-1 text-xs">
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-8 text-center text-sm text-[--color-ink-soft]">
                                هیچ کارمەندێکی چالاک نییە.
                                <a href="{{ route('employees.create') }}" class="text-[--color-brand-700]">کارمەند زیاد بکە</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-[--color-line] px-4 py-2 text-xs text-[--color-ink-soft] flex flex-wrap items-center justify-between gap-2">
            <div>
                کاتژمێری کار خۆکار حیساب دەکرێت. زیاتر لە {{ \App\Models\Attendance::STANDARD_HOURS }} کاتژمێر دەبێتە کاتی زیادە.
                حەقدەست تەنها بۆ ڕۆژی «ئامادە» دەژمێردرێت.
            </div>
            <div>
                سەردانی ماڵان بە سەیارەی کارمەند بە خەرجی بەنزین هەژمار دەکرێت.
            </div>
        </div>
    </div>

    @if ($employees->isNotEmpty())
        <div class="mt-4 flex items-center justify-end">
            <button class="btn btn-primary !py-2 !px-6 text-sm font-bold">پاشەکەوتکردنی هەموو تۆمارەکان</button>
        </div>
    @endif
</form>

@endsection
