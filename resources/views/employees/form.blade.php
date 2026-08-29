@extends('layouts.app')
@section('title', $employee->exists ? 'دەستکاری کارمەند' : 'کارمەندی نوێ')

@section('content')

<div class="max-w-2xl mx-auto space-y-4">
    {{-- هێڵی سەرەوە --}}
    <div class="flex items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <a href="{{ route('employees.index') }}"
               class="size-10 rounded-xl bg-white border border-slate-200 hover:bg-slate-50 flex items-center justify-center text-slate-600 transition-colors shadow-2xs">
                ←
            </a>
            <div>
                <h1 class="text-lg sm:text-xl font-black text-slate-900">
                    {{ $employee->exists ? 'دەستکاری زانیاری: ' . $employee->name : 'تۆمارکردنی کارمەندی نوێ' }}
                </h1>
                <p class="text-xs text-slate-500 font-medium">زانیاری کەسی، پیشە و مووچەی ڕۆژانەی کارمەند</p>
            </div>
        </div>

        @if ($employee->exists)
            <button type="submit" form="delete-employee"
                    class="px-3.5 py-1.5 rounded-xl text-xs font-bold bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 transition-all cursor-pointer"
                    onclick="return confirm('ئایا دڵنیایت لە سڕینەوەی ئەم کارمەندە؟')">
                🗑️ سڕینەوە
            </button>
        @endif
    </div>

    {{-- فۆڕمی سەرەکی --}}
    <form method="POST"
          action="{{ $employee->exists ? route('employees.update', $employee) : route('employees.store') }}"
          class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        @csrf
        @if ($employee->exists) @method('PUT') @endif

        <input type="hidden" name="wage_currency" value="IQD">

        <div class="p-5 sm:p-6 space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                {{-- ناو --}}
                <div>
                    <label class="block font-bold text-xs text-slate-700 mb-1.5" for="name">
                        ناوی تەواو <span class="text-rose-500">*</span>
                    </label>
                    <input id="name" name="name" type="text" required
                           value="{{ old('name', $employee->name) }}"
                           placeholder="ناوی وەستا یان کرێکار بنووسە..."
                           class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-hidden focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 bg-white font-bold text-slate-900">
                    @error('name') <p class="mt-1 text-[11px] text-rose-600 font-bold">{{ $message }}</p> @enderror
                </div>

                {{-- پیشە --}}
                @php
                    $currentJob = old('job_title', $employee->job_title);
                    $isStandardJob = array_key_exists($currentJob, \App\Models\Employee::JOB_TITLES);
                @endphp
                <div x-data="{ isCustomJob: {{ (!$isStandardJob && !empty($currentJob)) || old('job_title') === '__NEW__' ? 'true' : 'false' }} }">
                    <label class="block font-bold text-xs text-slate-700 mb-1.5" for="job_title">
                        پیشە / ڕۆڵ <span class="text-rose-500">*</span>
                    </label>
                    <div x-show="!isCustomJob">
                        <select id="job_title" name="job_title"
                                @change="if($event.target.value === '__NEW__') { isCustomJob = true; $nextTick(() => $refs.customJobInput.focus()); }"
                                class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-hidden focus:border-indigo-500 bg-white font-bold text-slate-800">
                            @foreach (\App\Models\Employee::JOB_TITLES as $value => $label)
                                <option value="{{ $value }}" @selected($currentJob === $value)>
                                    {{ $label }}
                                </option>
                            @endforeach
                            <option value="بۆیاغچی" @selected($currentJob === 'بۆیاغچی')>بۆیاغچی</option>
                            <option value="لەحیمچی" @selected($currentJob === 'لەحیمچی')>لەحیمچی</option>
                            <option value="__NEW__" class="font-bold text-indigo-600">+ زیادکردنی پیشەی نوێ</option>
                        </select>
                    </div>
                    <div x-show="isCustomJob" x-cloak class="flex gap-1.5">
                        <input x-ref="customJobInput" type="text" name="custom_job_title"
                               placeholder="ناوی پیشەی نوێ بنووسە..."
                               value="{{ !$isStandardJob ? $currentJob : old('custom_job_title') }}"
                               class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-hidden focus:border-indigo-500 bg-white font-bold text-slate-900 flex-1">
                        <button type="button" @click="isCustomJob = false; document.getElementById('job_title').value = 'master';"
                                class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold shrink-0">
                            لیست
                        </button>
                    </div>
                    @error('job_title') <p class="mt-1 text-[11px] text-rose-600 font-bold">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                {{-- تەلەفۆن --}}
                <div>
                    <label class="block font-bold text-xs text-slate-700 mb-1.5" for="phone">
                        ژمارەی مۆبایل
                    </label>
                    <input id="phone" name="phone" type="text" dir="ltr"
                           value="{{ old('phone', $employee->phone) }}"
                           placeholder="0750xxxxxxx"
                           class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-hidden focus:border-indigo-500 font-mono text-left text-slate-800">
                </div>

                {{-- حەقدەستی ڕۆژانە بە دینار --}}
                <div>
                    <label class="block font-bold text-xs text-slate-700 mb-1.5" for="daily_wage">
                        حەقدەستی ڕۆژانە (دیناری عێراقی)
                    </label>
                    <div class="relative">
                        <input id="daily_wage" name="daily_wage" type="number" step="any" min="0"
                               value="{{ old('daily_wage', $employee->daily_wage) }}"
                               placeholder="25000"
                               class="w-full text-xs px-3.5 py-2.5 pl-14 rounded-xl border border-slate-200 focus:outline-hidden focus:border-indigo-500 font-mono font-bold text-slate-900">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-400 font-mono">
                            د.ع
                        </span>
                    </div>
                </div>
            </div>

            {{-- تێبینی --}}
            <div>
                <label class="block font-bold text-xs text-slate-700 mb-1.5" for="note">
                    تێبینی
                </label>
                <textarea id="note" name="note" rows="2"
                          placeholder="تێبینی زیادە بنووسە..."
                          class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-hidden focus:border-indigo-500 text-slate-800">{{ old('note', $employee->note) }}</textarea>
            </div>

            {{-- دۆخی کارمەند --}}
            <div class="pt-2">
                <label class="inline-flex items-center gap-2.5 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1"
                           @checked(old('is_active', $employee->exists ? $employee->is_active : true))
                           class="size-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer">
                    <span class="text-xs font-bold text-slate-800">ئەم کارمەندە چالاکە (لە کارگە دەوام دەکات)</span>
                </label>
            </div>
        </div>

        {{-- بەتەنەکانی پاشەکەوتکردن --}}
        <div class="p-4 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-2.5">
            <a href="{{ route('employees.index') }}"
               class="px-4 py-2 rounded-xl text-xs font-bold bg-white hover:bg-slate-100 text-slate-700 border border-slate-200 transition-all">
                پاشگەزبوونەوە
            </a>
            <button type="submit"
                    class="px-5 py-2 rounded-xl text-xs font-bold bg-indigo-600 hover:bg-indigo-700 text-white shadow-xs transition-all cursor-pointer">
                {{ $employee->exists ? 'نوێکردنەوەی زانیاری' : 'پاشەکەوتکردن' }}
            </button>
        </div>
    </form>
</div>

@if ($employee->exists)
    <form id="delete-employee" method="POST" action="{{ route('employees.destroy', $employee) }}" class="hidden">
        @csrf @method('DELETE')
    </form>
@endif

@endsection
