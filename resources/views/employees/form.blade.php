@extends('layouts.app')
@section('title', $employee->exists ? 'دەستکاری کارمەند' : 'کارمەندی نوێ')

@section('content')

<form method="POST"
      action="{{ $employee->exists ? route('employees.update', $employee) : route('employees.store') }}"
      class="mx-auto max-w-2xl">
    @csrf
    @if ($employee->exists) @method('PUT') @endif

    <div class="card">
        <div class="card-head">زانیاری کارمەند</div>
        <div class="card-body grid gap-4 sm:grid-cols-2">

            <div>
                <label class="label" for="name">ناو <span class="text-[--color-danger]">*</span></label>
                <input id="name" name="name" class="field" required value="{{ old('name', $employee->name) }}">
                @error('name') <p class="mt-1 text-xs text-[--color-danger]">{{ $message }}</p> @enderror
            </div>

            @php
                $currentJob = old('job_title', $employee->job_title);
                $isStandardJob = array_key_exists($currentJob, \App\Models\Employee::JOB_TITLES);
            @endphp
            <div x-data="{ isCustomJob: {{ (!$isStandardJob && !empty($currentJob)) || old('job_title') === '__NEW__' ? 'true' : 'false' }} }">
                <label class="label" for="job_title">پیشە</label>
                <div x-show="!isCustomJob" class="space-y-1.5">
                    <select id="job_title" name="job_title" class="field"
                            @change="if($event.target.value === '__NEW__') { isCustomJob = true; $nextTick(() => $refs.customJobInput.focus()); }">
                        @foreach (\App\Models\Employee::JOB_TITLES as $value => $label)
                            <option value="{{ $value }}" @selected($currentJob === $value)>
                                {{ $label }}
                            </option>
                        @endforeach
                        <option value="__NEW__" class="font-bold text-indigo-600">+ زیادکردنی پیشەی نوێ</option>
                    </select>
                </div>
                <div x-show="isCustomJob" class="space-y-1.5" x-cloak>
                    <div class="flex gap-1.5">
                        <input x-ref="customJobInput" type="text" name="custom_job_title"
                               placeholder="ناوی پیشەی نوێ بنووسە (بۆ نموونە: بۆیاغچی)..."
                               value="{{ !$isStandardJob ? $currentJob : old('custom_job_title') }}"
                               class="field flex-1 font-bold">
                        <button type="button" @click="isCustomJob = false; document.getElementById('job_title').value = 'master';"
                                class="btn btn-ghost !text-xs !py-1 !px-2.5 text-slate-500">
                            گەڕانەوە
                        </button>
                    </div>
                    <span class="text-[11px] text-[--color-ink-soft]">دەتوانیت هەر پیشەیەک بە دەست بنووسیت.</span>
                </div>
                @error('job_title') <p class="mt-1 text-xs text-[--color-danger]">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="label" for="phone">تەلەفۆن</label>
                <input id="phone" name="phone" class="field num" dir="ltr" value="{{ old('phone', $employee->phone) }}">
            </div>

            <div>
                <label class="label" for="hire_date">بەرواری دەستپێکردن</label>
                <input id="hire_date" name="hire_date" type="date" class="field num"
                       value="{{ old('hire_date', $employee->hire_date?->toDateString()) }}">
            </div>

            <div>
                <label class="label" for="daily_wage">حەقدەستی ڕۆژانە</label>
                <input id="daily_wage" name="daily_wage" type="number" step="0.01" min="0" class="field num"
                       value="{{ old('daily_wage', $employee->daily_wage) }}">
            </div>

            <div>
                <label class="label" for="wage_currency">دراو</label>
                <select id="wage_currency" name="wage_currency" class="field">
                    <option value="IQD" @selected(old('wage_currency', $employee->wage_currency) === 'IQD')>دینار</option>
                    <option value="USD" @selected(old('wage_currency', $employee->wage_currency) === 'USD')>دۆلار</option>
                </select>
            </div>

            <div class="sm:col-span-2">
                <label class="label" for="note">تێبینی</label>
                <textarea id="note" name="note" rows="2" class="field">{{ old('note', $employee->note) }}</textarea>
            </div>

            <label class="flex items-center gap-2 text-sm sm:col-span-2">
                <input type="checkbox" name="is_active" value="1"
                       @checked(old('is_active', $employee->exists ? $employee->is_active : true))
                       class="size-4 rounded border-[--color-line-strong]">
                چالاکە
            </label>
        </div>
    </div>

    <div class="mt-4 flex gap-2">
        <button class="btn btn-primary">{{ $employee->exists ? 'نوێکردنەوە' : 'زیادکردن' }}</button>
        <a href="{{ route('employees.index') }}" class="btn btn-ghost">پاشگەزبوونەوە</a>

        @if ($employee->exists)
            <button type="submit" form="delete-employee" class="btn btn-ghost mr-auto !text-[--color-danger]"
                    onclick="return confirm('دڵنیایت؟')">سڕینەوە</button>
        @endif
    </div>
</form>

@if ($employee->exists)
    <form id="delete-employee" method="POST" action="{{ route('employees.destroy', $employee) }}" class="hidden">
        @csrf @method('DELETE')
    </form>
@endif

@endsection
