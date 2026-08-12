@extends('layouts.app')
@section('title', 'کارمەندان')

@section('actions')
    <a href="{{ route('employees.create') }}" class="btn btn-primary">کارمەندی نوێ</a>
    <a href="{{ route('attendance.index') }}" class="btn btn-ghost">هاتن و چوون</a>
@endsection

@section('content')

<div class="card">
    <div class="overflow-x-auto">
        <table class="table">
            <thead>
                <tr>
                    <th>ناو</th><th>پیشە</th><th>تەلەفۆن</th>
                    <th class="num">حەقدەستی ڕۆژانە</th><th class="num">ڕۆژی تۆمارکراو</th><th>دۆخ</th><th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($employees as $employee)
                    <tr>
                        <td>
                            <a href="{{ route('employees.show', $employee) }}" class="font-medium text-[--color-brand-700]">
                                {{ $employee->name }}
                            </a>
                        </td>
                        <td>{{ $employee->job_title_label }}</td>
                        <td class="num" dir="ltr">{{ $employee->phone ?? '—' }}</td>
                        <td class="num">{{ fmt_money($employee->daily_wage, $employee->wage_currency) }}</td>
                        <td class="num">{{ fmt_num($employee->attendances_count) }}</td>
                        <td>
                            <span class="badge {{ $employee->is_active ? 'badge-ok' : 'badge-warn' }}">
                                {{ $employee->is_active ? 'چالاک' : 'ناچالاک' }}
                            </span>
                        </td>
                        <td class="text-left">
                            <a href="{{ route('employees.edit', $employee) }}" class="text-sm text-[--color-brand-700]">دەستکاری</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="py-8 text-center text-sm text-[--color-ink-soft]">هیچ کارمەندێک نییە.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-4">{{ $employees->links() }}</div>

@endsection
