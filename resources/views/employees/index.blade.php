@extends('layouts.app')
@section('title', 'کارمەندان و وەستاکان')

@section('content')
<div class="space-y-4 sm:space-y-6">

    {{-- ١. هێڵی سەرەوە: ناونیشان و دوگمەکانی کردار --}}
    <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-xs flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div class="flex items-center gap-3.5">
            <div class="size-12 rounded-2xl bg-linear-to-br from-indigo-500 to-blue-600 text-white flex items-center justify-center text-2xl shadow-md shadow-indigo-500/20 shrink-0">
                👥
            </div>
            <div>
                <div class="flex items-center gap-2 flex-wrap">
                    <h1 class="text-lg sm:text-xl font-black text-slate-900">لیستی کارمەندان و وەستاکان</h1>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200/80">
                        کارگێڕی و حەقدەست
                    </span>
                </div>
                <p class="text-xs text-slate-500 mt-1 font-medium">
                    بەڕێوەبردنی زانیاری کارمەندان، پیشە، مووچەی ڕۆژانە و تۆماری کارکردن
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2.5 flex-wrap">
            <a href="{{ route('attendance.index') }}"
               class="px-3.5 py-2 rounded-xl text-xs font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-300 shadow-2xs flex items-center gap-1.5 transition-all">
                <span>📅</span>
                <span>تۆماری ئامادەبوون</span>
            </a>

            <a href="{{ route('attendance.wages') }}"
               class="px-3.5 py-2 rounded-xl text-xs font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-300 shadow-2xs flex items-center gap-1.5 transition-all">
                <span>💰</span>
                <span>حەقدەستەکان</span>
            </a>

            <a href="{{ route('employees.create') }}"
               class="px-4 py-2 rounded-xl text-xs font-bold bg-indigo-600 hover:bg-indigo-700 text-white shadow-xs flex items-center gap-1.5 transition-all">
                <span>+</span>
                <span>کارمەندی نوێ</span>
            </a>
        </div>
    </div>

    {{-- ٢. کارتەکانی ئامار --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs">
            <div class="text-xs font-bold text-slate-500 mb-1">کۆی کارمەندان</div>
            <div class="text-2xl font-black text-slate-900 font-mono">{{ $totalCount }}</div>
        </div>

        <div class="bg-emerald-50/70 rounded-2xl p-4 border border-emerald-200/80 shadow-xs">
            <div class="text-xs font-bold text-emerald-800 mb-1 flex items-center gap-1.5">
                <span class="size-2 rounded-full bg-emerald-500"></span>
                <span>کارمەندانی چالاک</span>
            </div>
            <div class="text-2xl font-black text-emerald-700 font-mono">{{ $activeCount }}</div>
        </div>

        <div class="bg-blue-50/70 rounded-2xl p-4 border border-blue-200/80 shadow-xs">
            <div class="text-xs font-bold text-blue-800 mb-1 flex items-center gap-1.5">
                <span>👷</span>
                <span>وەستاکان</span>
            </div>
            <div class="text-2xl font-black text-blue-800 font-mono">{{ $mastersCount }}</div>
        </div>

        <div class="bg-indigo-50/70 rounded-2xl p-4 border border-indigo-200/80 shadow-xs">
            <div class="text-xs font-bold text-indigo-800 mb-1 flex items-center gap-1.5">
                <span>📦</span>
                <span>حەمەڵ و یاریدەدەر</span>
            </div>
            <div class="text-2xl font-black text-indigo-800 font-mono">{{ $portersCount }}</div>
        </div>
    </div>

    {{-- ٣. بەشی سەرەکی: فلتەر و خشتەی کارمەندان --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        {{-- شریتی گەڕان و فلتەر --}}
        <form method="GET" class="p-4 border-b border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-3 bg-slate-50/50">
            <div class="flex items-center gap-2 flex-wrap">
                {{-- فلتەری دۆخ --}}
                <div class="flex items-center gap-1 bg-white p-1 rounded-xl border border-slate-200">
                    <a href="{{ route('employees.index', array_merge(request()->except('status', 'page'), ['status' => 'all'])) }}"
                       class="px-3 py-1 rounded-lg text-xs font-bold transition-all {{ ($status ?? 'all') === 'all' ? 'bg-slate-900 text-white' : 'text-slate-600 hover:text-slate-900' }}">
                        هەموو
                    </a>
                    <a href="{{ route('employees.index', array_merge(request()->except('status', 'page'), ['status' => 'active'])) }}"
                       class="px-3 py-1 rounded-lg text-xs font-bold transition-all {{ ($status ?? '') === 'active' ? 'bg-emerald-600 text-white' : 'text-slate-600 hover:text-slate-900' }}">
                        چالاک
                    </a>
                    <a href="{{ route('employees.index', array_merge(request()->except('status', 'page'), ['status' => 'inactive'])) }}"
                       class="px-3 py-1 rounded-lg text-xs font-bold transition-all {{ ($status ?? '') === 'inactive' ? 'bg-rose-600 text-white' : 'text-slate-600 hover:text-slate-900' }}">
                        ناچالاک
                    </a>
                </div>

                {{-- فلتەری پیشە --}}
                @if(count($jobTitles) > 1)
                    <select name="job_title" onchange="this.form.submit()"
                            class="text-xs px-3 py-1.5 rounded-xl border border-slate-200 bg-white font-bold text-slate-700 focus:outline-hidden focus:border-indigo-500">
                        <option value="all">هەموو پیشەکان</option>
                        @foreach($jobTitles as $jt)
                            @php
                                $label = \App\Models\Employee::JOB_TITLES[$jt] ?? $jt;
                            @endphp
                            <option value="{{ $jt }}" @selected(($jobTitle ?? '') === $jt)>{{ $label }}</option>
                        @endforeach
                    </select>
                @endif
            </div>

            {{-- گەڕان --}}
            <div class="flex items-center gap-2">
                <input type="text" name="search" value="{{ $search }}" placeholder="🔍 گەڕان بە ناو، مۆبایل، پیشە..."
                       class="text-xs px-3.5 py-2 rounded-xl border border-slate-200 bg-white focus:outline-hidden focus:border-indigo-500 font-medium text-right w-full sm:w-64">
                @if($search || ($status ?? 'all') !== 'all' || ($jobTitle ?? 'all') !== 'all')
                    <a href="{{ route('employees.index') }}" class="px-2.5 py-2 text-xs font-bold text-slate-400 hover:text-slate-600 rounded-xl bg-slate-100">✕</a>
                @endif
            </div>
        </form>

        {{-- خشتەی سەرەکی --}}
        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead class="bg-slate-50 text-slate-600 border-b border-slate-200 font-black">
                    <tr>
                        <th class="p-3.5">ناوی کارمەند / وەستا</th>
                        <th class="p-3.5">پیشە</th>
                        <th class="p-3.5">ژمارەی مۆبایل</th>
                        <th class="p-3.5">حەقدەستی ڕۆژانە</th>
                        <th class="p-3.5 text-center">ڕۆژانی ئامادەبوون</th>
                        <th class="p-3.5 text-center">دۆخ</th>
                        <th class="p-3.5 text-center">کردارەکان</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($employees as $employee)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="p-3.5">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-700 font-black flex items-center justify-center text-xs shrink-0 border border-indigo-100">
                                        {{ mb_substr($employee->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <a href="{{ route('employees.show', $employee) }}" class="font-black text-slate-900 text-xs hover:text-indigo-600 transition-colors">
                                            {{ $employee->name }}
                                        </a>
                                        @if($employee->hire_date)
                                            <div class="text-[10px] text-slate-400 font-mono mt-0.5">دەستپێک: {{ fmt_date($employee->hire_date) }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <td class="p-3.5">
                                <span class="px-2.5 py-0.5 rounded-lg text-xs font-bold bg-slate-100 text-slate-800 border border-slate-200/60">
                                    {{ $employee->job_title_label }}
                                </span>
                            </td>

                            <td class="p-3.5 font-mono text-slate-600 font-bold" dir="ltr">
                                {{ $employee->phone ?: '—' }}
                            </td>

                            <td class="p-3.5">
                                <span class="font-mono font-black text-indigo-700 text-xs">
                                    {{ fmt_money($employee->daily_wage, $employee->wage_currency) }}
                                </span>
                            </td>

                            <td class="p-3.5 text-center">
                                <span class="px-2 py-0.5 rounded-md bg-blue-50 text-blue-700 font-mono font-bold text-[11px] border border-blue-200/60">
                                    {{ fmt_num($employee->attendances_count) }} ڕۆژ
                                </span>
                            </td>

                            <td class="p-3.5 text-center">
                                @if($employee->is_active)
                                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        چالاک ✔️
                                    </span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-rose-100 text-rose-800 border border-rose-200">
                                        ناچالاک ❌
                                    </span>
                                @endif
                            </td>

                            <td class="p-3.5 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <a href="{{ route('employees.show', $employee) }}"
                                       class="px-2.5 py-1 rounded-lg text-xs font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 transition-all"
                                       title="پرۆفایل و تۆماری کارکردن">
                                        👁️ پرۆفایل
                                    </a>
                                    <a href="{{ route('employees.edit', $employee) }}"
                                       class="px-2.5 py-1 rounded-lg text-xs font-bold bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 transition-all"
                                       title="دەستکاری">
                                        ✏️ دەستکاری
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-sm text-slate-400">
                                <div class="text-3xl mb-2">👥</div>
                                <div>هیچ کارمەندێک نەدۆزرایەوە.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($employees->hasPages())
            <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                {{ $employees->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
