@extends('layouts.menu')
@section('title', 'تۆماری ئامادەبوونی ئەمڕۆ')

@section('content')
<div x-data="workshopEmployeesApp()" x-init="initClock()" class="space-y-4 sm:space-y-6">

    {{-- ١. هێڵی سەرەوە: ناونیشان و بەرواری ئەمڕۆ بە شێوەی خۆکار و قفڵکراو --}}
    <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-xs flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div class="flex items-center gap-3.5">
            <div class="size-12 rounded-2xl bg-linear-to-br from-indigo-500 to-blue-600 text-white flex items-center justify-center text-2xl shadow-md shadow-indigo-500/20 shrink-0">
                👷
            </div>
            <div>
                <div class="flex items-center gap-2 flex-wrap">
                    <h1 class="text-lg sm:text-xl font-black text-slate-900">تۆماری ئامادەبوونی وەستا و کارمەندان</h1>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200/80">
                        کارگەی ئاسنگەری
                    </span>
                </div>
                <p class="text-xs text-slate-500 mt-1 font-medium">
                    تۆمارکردنی هاتن، دەرچوون، کاتی زیادە، دەرچوونی کاتی و خەرجی بەنزینی سەر ماڵان
                </p>
            </div>
        </div>

        {{-- بەرواری ئەمڕۆ و کاتژمێری ڕاستەوخۆ --}}
        <div class="flex items-center gap-3 bg-slate-50 border border-slate-200/80 px-4 py-2.5 rounded-2xl shrink-0">
            <div class="size-10 rounded-xl bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-lg shadow-2xs">
                📅
            </div>
            <div>
                <div class="text-xs font-black text-slate-800 font-mono">{{ now()->format('Y/m/d') }}</div>
                <div class="text-[11px] text-slate-500 font-medium flex items-center gap-1.5 mt-0.5">
                    <span>کاتی ئێستا:</span>
                    <span class="font-mono font-bold text-slate-800" x-text="currentTime"></span>
                </div>
            </div>
        </div>
    </div>

    {{-- ٢. کارتەکانی ئامار و خولاسەی ئەمڕۆ --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2.5 sm:gap-3.5">
        {{-- کۆی گشتی کارمەندان --}}
        <div class="bg-white rounded-2xl p-3.5 border border-slate-200/80 shadow-xs">
            <div class="text-[11px] font-bold text-slate-500 mb-1">کۆی کارمەندان</div>
            <div class="text-xl font-black text-slate-900 font-mono">{{ $employees->count() }}</div>
        </div>

        {{-- ئامادەبووان --}}
        <div class="bg-emerald-50/70 rounded-2xl p-3.5 border border-emerald-200/80 shadow-xs">
            <div class="text-[11px] font-bold text-emerald-800 mb-1 flex items-center gap-1">
                <span class="size-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>ئامادەبوو</span>
            </div>
            <div class="text-xl font-black text-emerald-700 font-mono">{{ $presentCount }}</div>
        </div>

        {{-- ئیجازە --}}
        <div class="bg-amber-50/70 rounded-2xl p-3.5 border border-amber-200/80 shadow-xs">
            <div class="text-[11px] font-bold text-amber-800 mb-1 flex items-center gap-1">
                <span>🏖️</span>
                <span>ئیجازە</span>
            </div>
            <div class="text-xl font-black text-amber-700 font-mono">{{ $leaveCount }}</div>
        </div>

        {{-- نەهاتوو --}}
        <div class="bg-rose-50/70 rounded-2xl p-3.5 border border-rose-200/80 shadow-xs">
            <div class="text-[11px] font-bold text-rose-800 mb-1 flex items-center gap-1">
                <span>❌</span>
                <span>نەهاتوو</span>
            </div>
            <div class="text-xl font-black text-rose-700 font-mono">{{ $absentCount }}</div>
        </div>

        {{-- کۆی کاتی زیادە --}}
        <div class="bg-blue-50/70 rounded-2xl p-3.5 border border-blue-200/80 shadow-xs">
            <div class="text-[11px] font-bold text-blue-800 mb-1 flex items-center gap-1">
                <span>⏱️</span>
                <span>کاتی زیادە</span>
            </div>
            <div class="text-base sm:text-lg font-black text-blue-800 font-mono">
                {{ number_format($totalOvertime, 1) }} <span class="text-xs font-normal">ک/ژ</span>
            </div>
        </div>

        {{-- کۆی بەنزینی سەر ماڵان --}}
        <div class="bg-indigo-50/70 rounded-2xl p-3.5 border border-indigo-200/80 shadow-xs">
            <div class="text-[11px] font-bold text-indigo-800 mb-1 flex items-center gap-1">
                <span>🚗</span>
                <span>بەنزینی سەردان</span>
            </div>
            <div class="text-sm sm:text-base font-black text-indigo-900 font-mono">
                {{ number_format($totalFuel) }} <span class="text-[10px] font-normal">د.ع</span>
            </div>
        </div>
    </div>

    {{-- ٣. بەشی سەرەکی: فلتەرەکان، گەڕان و جەدوەل --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        {{-- هێڵی سەرەوەی جەدوەل: گەڕان و فلتەر --}}
        <div class="p-3.5 sm:p-4 border-b border-slate-100 flex flex-col lg:flex-row lg:items-center justify-between gap-3 bg-slate-50/50">
            {{-- فلتەرەکان بەپێی دۆخ --}}
            <div class="flex items-center gap-1.5 flex-wrap">
                <button type="button" @click="statusFilter = 'all'"
                        :class="statusFilter === 'all' ? 'bg-slate-900 text-white font-black shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200 font-bold'"
                        class="px-3 py-1.5 rounded-xl text-xs transition-all cursor-pointer">
                    هەموو (<span x-text="employeesList.length"></span>)
                </button>
                <button type="button" @click="statusFilter = 'present'"
                        :class="statusFilter === 'present' ? 'bg-emerald-600 text-white font-black shadow-xs' : 'bg-white text-emerald-700 hover:bg-emerald-50 border border-emerald-200 font-bold'"
                        class="px-3 py-1.5 rounded-xl text-xs transition-all cursor-pointer">
                    ئامادەبووان (<span x-text="countByStatus('present')"></span>)
                </button>
                <button type="button" @click="statusFilter = 'absent'"
                        :class="statusFilter === 'absent' ? 'bg-rose-600 text-white font-black shadow-xs' : 'bg-white text-rose-700 hover:bg-rose-50 border border-rose-200 font-bold'"
                        class="px-3 py-1.5 rounded-xl text-xs transition-all cursor-pointer">
                    نەهاتووان (<span x-text="countByStatus('absent')"></span>)
                </button>
                <button type="button" @click="statusFilter = 'leave'"
                        :class="statusFilter === 'leave' ? 'bg-amber-500 text-white font-black shadow-xs' : 'bg-white text-amber-800 hover:bg-amber-50 border border-amber-200 font-bold'"
                        class="px-3 py-1.5 rounded-xl text-xs transition-all cursor-pointer">
                    ئیجازە (<span x-text="countByStatus('leave')"></span>)
                </button>
                <button type="button" @click="statusFilter = 'not_recorded'"
                        :class="statusFilter === 'not_recorded' ? 'bg-slate-600 text-white font-black shadow-xs' : 'bg-white text-slate-500 hover:bg-slate-100 border border-slate-200 font-bold'"
                        class="px-3 py-1.5 rounded-xl text-xs transition-all cursor-pointer">
                    تۆمارنەکراو (<span x-text="countByStatus('not_recorded')"></span>)
                </button>
            </div>

            {{-- گەڕان و گۆڕینی شێوازی بینین (خشتە / کارت) --}}
            <div class="flex items-center gap-2">
                <div class="relative flex-1 sm:w-64">
                    <input type="text" x-model="searchQuery" placeholder="گەڕان بە ناوی وەستا یان ژمارە..."
                           class="w-full text-xs pr-8 pl-3 py-2 rounded-xl border border-slate-200 focus:outline-hidden focus:border-indigo-500 bg-white font-medium">
                    <span class="absolute right-2.5 top-2.5 text-slate-400 text-xs">🔍</span>
                </div>

                {{-- گۆڕینی شێوازی بینین --}}
                <div class="flex items-center p-1 bg-slate-200/80 rounded-xl border border-slate-200 shrink-0">
                    <button type="button" @click="viewMode = 'table'"
                            :class="viewMode === 'table' ? 'bg-white text-slate-900 font-bold shadow-xs' : 'text-slate-600 hover:text-slate-900'"
                            class="px-2.5 py-1 rounded-lg text-xs transition-all cursor-pointer flex items-center gap-1"
                            title="شێوازی خشتە">
                        <span>📊</span>
                        <span class="hidden sm:inline">جەدوەل</span>
                    </button>
                    <button type="button" @click="viewMode = 'cards'"
                            :class="viewMode === 'cards' ? 'bg-white text-slate-900 font-bold shadow-xs' : 'text-slate-600 hover:text-slate-900'"
                            class="px-2.5 py-1 rounded-lg text-xs transition-all cursor-pointer flex items-center gap-1"
                            title="شێوازی کارتەکان">
                        <span>🎴</span>
                        <span class="hidden sm:inline">کارتەکان</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- ئەگەر کارمەند نەبوو --}}
        @if ($employees->isEmpty())
            <div class="p-12 text-center">
                <div class="text-4xl mb-3">👷‍♂️</div>
                <div class="font-bold text-slate-700 text-base">هیچ وەستا یان کارمەندێک تۆمار نەکراوە</div>
                <div class="text-xs text-slate-400 mt-1">لە بەشی سەرەکی کارمەندان دەتوانیت وەستا و کرێکاران زیاد بکەیت.</div>
            </div>
        @else

            {{-- ٤.١ خشتەی سەرەکی ڕۆژانە (TABLE VIEW) --}}
            <div x-show="viewMode === 'table'" class="overflow-x-auto scrollbar-none">
                <table class="w-full text-right text-xs">
                    <thead class="bg-slate-50 text-slate-600 border-b border-slate-200 font-black">
                        <tr>
                            <th class="p-3.5 w-12 text-center">#</th>
                            <th class="p-3.5 min-w-[180px]">وەستا / کارمەند</th>
                            <th class="p-3.5 min-w-[120px]">حەقدەستی ڕۆژانە</th>
                            <th class="p-3.5 min-w-[130px]">دۆخی ئەمڕۆ</th>
                            <th class="p-3.5 min-w-[110px] text-center bg-emerald-50/40 border-x border-emerald-100/60">
                                <span class="text-emerald-800 flex items-center justify-center gap-1">
                                    <span>📥</span>
                                    <span>کاتی هاتن</span>
                                </span>
                            </th>
                            <th class="p-3.5 min-w-[110px] text-center bg-indigo-50/40 border-e border-indigo-100/60">
                                <span class="text-indigo-800 flex items-center justify-center gap-1">
                                    <span>📤</span>
                                    <span>کاتی دەرچوون</span>
                                </span>
                            </th>
                            <th class="p-3.5 min-w-[110px] text-center">کاتی زیادە</th>
                            <th class="p-3.5 min-w-[140px]">دەرچوونی کاتی</th>
                            <th class="p-3.5 min-w-[140px]">سەردانی ماڵان / بەنزین</th>
                            <th class="p-3.5 min-w-[200px] text-center">کردارە خێراکان</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <template x-for="(emp, index) in filteredEmployees" :key="emp.id">
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                {{-- # --}}
                                <td class="p-3.5 text-center text-slate-400 font-mono font-bold" x-text="index + 1"></td>

                                {{-- وەستا / کارمەند --}}
                                <td class="p-3.5">
                                    <div class="flex items-center gap-2.5">
                                        <div class="size-9 rounded-xl flex items-center justify-center font-black text-sm shrink-0 border"
                                             :class="emp.job_title === 'master' ? 'bg-amber-50 text-amber-700 border-amber-200' : 'bg-slate-100 text-slate-700 border-slate-200'">
                                            <span x-text="emp.name.charAt(0)"></span>
                                        </div>
                                        <div class="min-w-0">
                                            <div class="font-black text-slate-900 text-xs sm:text-sm truncate flex items-center gap-1.5">
                                                <span x-text="emp.name"></span>
                                            </div>
                                            <div class="flex items-center gap-2 mt-0.5 flex-wrap">
                                                <span class="px-2 py-0.2 rounded-md text-[10px] font-bold"
                                                      :class="emp.job_title === 'master' ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-700'"
                                                      x-text="emp.job_title_label"></span>
                                                <span x-show="emp.phone" class="text-[11px] text-slate-500 font-mono" dir="ltr" x-text="emp.phone"></span>
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                {{-- حەقدەست --}}
                                <td class="p-3.5">
                                    <div class="font-black text-slate-800 font-mono text-xs" x-text="emp.daily_wage.toLocaleString() + ' ' + emp.wage_currency"></div>
                                    <div class="text-[10px] text-slate-400">حەقدەستی ڕۆژ</div>
                                </td>

                                {{-- دۆخی ئامادەبوون --}}
                                <td class="p-3.5">
                                    <span x-show="emp.attendance && emp.attendance.status === 'present'"
                                          class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-black bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        <span class="size-2 rounded-full bg-emerald-500"></span>
                                        <span>ئامادەیە</span>
                                    </span>
                                    <span x-show="emp.attendance && emp.attendance.status === 'leave'"
                                          class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-black bg-amber-100 text-amber-800 border border-amber-200">
                                        <span>🏖️</span>
                                        <span>ئیجازە</span>
                                    </span>
                                    <span x-show="emp.attendance && emp.attendance.status === 'absent'"
                                          class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-black bg-rose-100 text-rose-800 border border-rose-200">
                                        <span>❌</span>
                                        <span>نەهاتووە</span>
                                    </span>
                                    <span x-show="emp.attendance && emp.attendance.status === 'holiday'"
                                          class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-black bg-slate-100 text-slate-700 border border-slate-200">
                                        <span>☕</span>
                                        <span>پشوو</span>
                                    </span>
                                    <span x-show="!emp.attendance"
                                          class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-[11px] font-semibold bg-slate-100 text-slate-500 border border-slate-200">
                                        تۆمار نەکراوە
                                    </span>
                                </td>

                                {{-- کاتی هاتن --}}
                                <td class="p-3.5 text-center bg-emerald-50/20 border-x border-emerald-100/50">
                                    <div x-show="emp.attendance && emp.attendance.check_in"
                                         class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl bg-emerald-100/70 border border-emerald-200 text-emerald-900 font-mono font-black text-xs">
                                        <span>🕒</span>
                                        <span x-text="emp.attendance.check_in"></span>
                                    </div>
                                    <div x-show="!emp.attendance || !emp.attendance.check_in" class="text-slate-300 text-xs font-mono font-bold">
                                        — : —
                                    </div>
                                </td>

                                {{-- کاتی دەرچوون --}}
                                <td class="p-3.5 text-center bg-indigo-50/20 border-e border-indigo-100/50">
                                    <div x-show="emp.attendance && emp.attendance.check_out"
                                         class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl bg-indigo-100/70 border border-indigo-200 text-indigo-900 font-mono font-black text-xs">
                                        <span>🕒</span>
                                        <span x-text="emp.attendance.check_out"></span>
                                    </div>
                                    <div x-show="!emp.attendance || !emp.attendance.check_out" class="text-slate-300 text-xs font-mono font-bold">
                                        — : —
                                    </div>
                                </td>

                                {{-- کاتی زیادە --}}
                                <td class="p-3.5 text-center">
                                    <span x-show="emp.attendance && emp.attendance.overtime_hours > 0"
                                          class="inline-flex items-center gap-1 px-2 py-1 rounded-lg bg-blue-100 text-blue-800 font-black text-xs border border-blue-200">
                                        <span>+</span>
                                        <span x-text="emp.attendance.overtime_hours"></span>
                                        <span class="text-[10px] font-normal">ک/ژ</span>
                                    </span>
                                    <span x-show="!emp.attendance || !emp.attendance.overtime_hours" class="text-slate-300 text-xs font-mono">
                                        0
                                    </span>
                                </td>

                                {{-- دەرچوونی کاتی لە ناو ئیش --}}
                                <td class="p-3.5">
                                    <div x-show="emp.attendance && emp.attendance.temporary_exit_hours > 0" class="text-xs space-y-0.5">
                                        <span class="px-2 py-0.5 rounded-md bg-amber-100 text-amber-900 font-bold border border-amber-200 inline-block">
                                            🚪 <span x-text="emp.attendance.temporary_exit_hours + ' کاتژمێر'"></span>
                                        </span>
                                        <div class="text-[11px] text-slate-500 truncate max-w-[130px]" x-show="emp.attendance.exit_reason" x-text="emp.attendance.exit_reason"></div>
                                    </div>
                                    <div x-show="!emp.attendance || !emp.attendance.temporary_exit_hours" class="text-slate-300 text-xs">
                                        نییە
                                    </div>
                                </td>

                                {{-- سەردانی ماڵان و بەنزین --}}
                                <td class="p-3.5">
                                    <div x-show="emp.attendance && (emp.attendance.fuel_expense > 0 || emp.attendance.trip_destination)" class="text-xs space-y-0.5">
                                        <div x-show="emp.attendance.fuel_expense > 0" class="font-black text-emerald-700 font-mono">
                                            ⛽ <span x-text="emp.attendance.fuel_expense.toLocaleString() + ' د.ع'"></span>
                                        </div>
                                        <div class="text-[11px] text-slate-500 truncate max-w-[140px]" x-show="emp.attendance.trip_destination" x-text="emp.attendance.trip_destination"></div>
                                    </div>
                                    <div x-show="!emp.attendance || (!emp.attendance.fuel_expense && !emp.attendance.trip_destination)" class="text-slate-300 text-xs">
                                        نییە
                                    </div>
                                </td>

                                {{-- دوگمەکانی کردار --}}
                                <td class="p-3.5">
                                    <div class="flex items-center justify-center gap-1.5">
                                        {{-- دوگمەی خێرای تۆماری هاتن --}}
                                        <button type="button"
                                                @click="quickCheckIn(emp.id)"
                                                :disabled="loadingId === emp.id"
                                                title="تۆمارکردنی هاتن بە کاتی ئێستا"
                                                class="px-2.5 py-1.5 rounded-xl text-xs font-black bg-emerald-600 hover:bg-emerald-700 text-white shadow-2xs cursor-pointer active:scale-95 transition-all flex items-center gap-1">
                                            <span>📥</span>
                                            <span>هاتن</span>
                                        </button>

                                        {{-- دوگمەی خێرای تۆماری دەرچوون --}}
                                        <button type="button"
                                                @click="quickCheckOut(emp.id)"
                                                :disabled="loadingId === emp.id"
                                                title="تۆمارکردنی دەرچوون بە کاتی ئێستا"
                                                class="px-2.5 py-1.5 rounded-xl text-xs font-black bg-slate-800 hover:bg-slate-900 text-white shadow-2xs cursor-pointer active:scale-95 transition-all flex items-center gap-1">
                                            <span>📤</span>
                                            <span>دەرچوون</span>
                                        </button>

                                        {{-- دوگمەی وردەکاری و دەستکاری --}}
                                        <button type="button"
                                                @click="openModalFor(emp)"
                                                title="دەستکاری هەموو وردەکارییەکان"
                                                class="px-2 py-1.5 rounded-xl text-xs font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 cursor-pointer transition-all flex items-center gap-1">
                                            <span>✏️</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            {{-- ٤.٢ شێوازی کارتەکان (CARD VIEW) --}}
            <div x-show="viewMode === 'cards'" class="p-4 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3.5 sm:gap-4">
                <template x-for="emp in filteredEmployees" :key="emp.id">
                    <div class="bg-slate-50/70 rounded-2xl p-4 sm:p-4.5 border border-slate-200 shadow-2xs flex flex-col justify-between hover:shadow-md transition-all">
                        <div>
                            {{-- بەشی سەرەوەی کارت --}}
                            <div class="flex items-start justify-between gap-2 border-b border-slate-200/80 pb-3 mb-3">
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <h3 class="font-black text-slate-900 text-sm sm:text-base truncate" x-text="emp.name"></h3>
                                        <span class="px-2 py-0.5 rounded-lg text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200"
                                              x-text="emp.job_title_label"></span>
                                    </div>
                                    <div class="text-xs text-slate-500 font-medium mt-1">
                                        <span x-show="emp.phone" class="font-mono text-slate-600" dir="ltr" x-text="emp.phone"></span>
                                        <span x-show="!emp.phone" class="text-slate-400">بێ ژمارە</span>
                                        <span class="text-slate-300 mx-1.5">•</span>
                                        <span>حەقدەست: <span class="font-black text-slate-800" x-text="emp.daily_wage.toLocaleString() + ' ' + emp.wage_currency"></span></span>
                                    </div>
                                </div>

                                {{-- دۆخی ئەمڕۆ --}}
                                <div class="shrink-0">
                                    <span x-show="emp.attendance && emp.attendance.status === 'present'"
                                          class="px-2.5 py-1 rounded-full text-xs font-black bg-emerald-100 text-emerald-800 border border-emerald-200 flex items-center gap-1">
                                        <span class="size-2 rounded-full bg-emerald-500"></span>
                                        <span>ئامادەیە</span>
                                    </span>
                                    <span x-show="emp.attendance && emp.attendance.status === 'leave'"
                                          class="px-2.5 py-1 rounded-full text-xs font-black bg-amber-100 text-amber-800 border border-amber-200 flex items-center gap-1">
                                        <span>🏖️</span>
                                        <span>ئیجازە</span>
                                    </span>
                                    <span x-show="emp.attendance && emp.attendance.status === 'absent'"
                                          class="px-2.5 py-1 rounded-full text-xs font-black bg-rose-100 text-rose-800 border border-rose-200 flex items-center gap-1">
                                        <span>❌</span>
                                        <span>نەهاتووە</span>
                                    </span>
                                    <span x-show="emp.attendance && emp.attendance.status === 'holiday'"
                                          class="px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                        پشوو
                                    </span>
                                    <span x-show="!emp.attendance"
                                          class="px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-200 text-slate-600 border border-slate-300">
                                        تۆمار نەکراوە
                                    </span>
                                </div>
                            </div>

                            {{-- کاتی هاتن و دەرچوون --}}
                            <div class="grid grid-cols-2 gap-2 mb-3">
                                <div class="bg-emerald-50/60 p-2.5 rounded-xl border border-emerald-100">
                                    <div class="text-[10px] font-bold text-emerald-800 mb-0.5">📥 کاتی هاتن:</div>
                                    <div class="text-xs sm:text-sm font-black text-emerald-950 font-mono" x-text="(emp.attendance && emp.attendance.check_in) ? emp.attendance.check_in : '— : —'"></div>
                                </div>
                                <div class="bg-indigo-50/60 p-2.5 rounded-xl border border-indigo-100">
                                    <div class="text-[10px] font-bold text-indigo-800 mb-0.5">📤 کاتی دەرچوون:</div>
                                    <div class="text-xs sm:text-sm font-black text-indigo-950 font-mono" x-text="(emp.attendance && emp.attendance.check_out) ? emp.attendance.check_out : '— : —'"></div>
                                </div>
                            </div>

                            {{-- زانیارییە تایبەتەکان --}}
                            <div class="space-y-1.5 mb-3 text-xs">
                                <div x-show="emp.attendance && emp.attendance.overtime_hours > 0"
                                     class="flex items-center justify-between p-2 rounded-xl bg-blue-50/80 border border-blue-100 text-blue-900 font-medium">
                                    <span class="flex items-center gap-1 font-bold">
                                        <span>⏱️</span>
                                        <span>کاتی زیادە:</span>
                                    </span>
                                    <span class="font-black" x-text="emp.attendance.overtime_hours + ' کاتژمێر'"></span>
                                </div>

                                <div x-show="emp.attendance && emp.attendance.temporary_exit_hours > 0"
                                     class="flex items-center justify-between p-2 rounded-xl bg-amber-50/80 border border-amber-100 text-amber-900 font-medium">
                                    <span class="flex items-center gap-1 font-bold">
                                        <span>🚪</span>
                                        <span>دەرچوونی کاتی:</span>
                                        <span class="text-[11px] text-amber-700" x-show="emp.attendance.exit_reason" x-text="'(' + emp.attendance.exit_reason + ')'"></span>
                                    </span>
                                    <span class="font-black" x-text="emp.attendance.temporary_exit_hours + ' کاتژمێر'"></span>
                                </div>

                                <div x-show="emp.attendance && (emp.attendance.fuel_expense > 0 || emp.attendance.trip_destination)"
                                     class="flex items-center justify-between p-2 rounded-xl bg-emerald-50/80 border border-emerald-100 text-emerald-900 font-medium">
                                    <span class="flex items-center gap-1 font-bold">
                                        <span>🚗</span>
                                        <span>سەردانی کڕیار:</span>
                                        <span class="text-[11px] text-emerald-700" x-show="emp.attendance.trip_destination" x-text="'(' + emp.attendance.trip_destination + ')'"></span>
                                    </span>
                                    <span class="font-black" x-show="emp.attendance.fuel_expense > 0" x-text="'بەنزین: ' + emp.attendance.fuel_expense.toLocaleString() + ' د.ع'"></span>
                                </div>

                                <div x-show="emp.attendance && emp.attendance.note"
                                     class="text-[11px] text-slate-500 bg-white p-2 rounded-xl border border-slate-200">
                                    <span class="font-bold text-slate-700">تێبینی:</span> <span x-text="emp.attendance.note"></span>
                                </div>
                            </div>
                        </div>

                        {{-- دوگمەکانی کردار لە کارتدا --}}
                        <div class="border-t border-slate-200/80 pt-3 flex items-center justify-between gap-1.5 flex-wrap">
                            <div class="flex items-center gap-1.5 flex-1">
                                <button type="button" @click="quickCheckIn(emp.id)"
                                        :disabled="loadingId === emp.id"
                                        class="flex-1 px-3 py-1.5 rounded-xl text-xs font-black bg-emerald-600 hover:bg-emerald-700 text-white flex items-center justify-center gap-1 shadow-2xs cursor-pointer active:scale-95 transition-all">
                                    <span>📥</span>
                                    <span>هاتن</span>
                                </button>

                                <button type="button" @click="quickCheckOut(emp.id)"
                                        :disabled="loadingId === emp.id"
                                        class="flex-1 px-3 py-1.5 rounded-xl text-xs font-black bg-slate-800 hover:bg-slate-900 text-white flex items-center justify-center gap-1 shadow-2xs cursor-pointer active:scale-95 transition-all">
                                    <span>📤</span>
                                    <span>دەرچوون</span>
                                </button>
                            </div>

                            <button type="button" @click="openModalFor(emp)"
                                    class="px-2.5 py-1.5 rounded-xl text-xs font-bold bg-white hover:bg-slate-100 text-slate-700 border border-slate-200 flex items-center justify-center gap-1 cursor-pointer transition-all">
                                <span>✏️</span>
                                <span>دەستکاری</span>
                            </button>
                        </div>
                    </div>
                </template>
            </div>

            {{-- فلتەری بەتاڵ --}}
            <div x-show="filteredEmployees.length === 0" class="p-10 text-center text-slate-400 text-xs font-bold">
                هیچ ئەنجامێک بەپێی ئەم فلتەرە نەدۆزرایەوە
            </div>
        @endif
    </div>

    {{-- ============================================================ --}}
    {{-- ٥. مۆداڵی وردەکاری ئامادەبوون و دەستکاری (MODAL) --}}
    {{-- ============================================================ --}}
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/70 backdrop-blur-xs p-3.5 sm:p-4">
        <div class="relative w-full max-w-lg bg-white rounded-2xl sm:rounded-3xl p-5 sm:p-6 shadow-2xl border border-slate-100 max-h-[90vh] overflow-y-auto scrollbar-none" @click.outside="showModal = false">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3.5 sm:pb-4 mb-4">
                <div class="flex items-center gap-2.5">
                    <div class="size-10 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-lg border border-indigo-100 shrink-0">
                        👷
                    </div>
                    <div class="min-w-0">
                        <h3 class="font-black text-slate-800 text-sm sm:text-base truncate" x-text="'تۆماری ئامادەبوون: ' + (selectedEmployee ? selectedEmployee.name : '')"></h3>
                        <p class="text-xs text-slate-400 font-medium font-mono" x-text="'بەروار: {{ now()->format('Y/m/d') }} (ئەمڕۆ)'"></p>
                    </div>
                </div>
                <button type="button" @click="showModal = false" class="size-8 rounded-full hover:bg-slate-100 text-slate-400 hover:text-slate-600 text-lg flex items-center justify-center cursor-pointer">✕</button>
            </div>

            <form @submit.prevent="saveAttendance" class="space-y-4 text-xs">
                {{-- دۆخی ئامادەبوون --}}
                <div>
                    <label class="block font-bold text-slate-700 mb-1.5">دۆخی ئامادەبوونی ئەمڕۆ *</label>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                        <button type="button" @click="form.status = 'present'"
                                :class="form.status === 'present' ? 'bg-emerald-600 text-white font-black shadow-xs ring-2 ring-emerald-600/30' : 'bg-slate-50 text-slate-700 hover:bg-slate-100 border border-slate-200 font-bold'"
                                class="py-2.5 rounded-xl text-center cursor-pointer transition-all">
                            ئامادە ✔️
                        </button>
                        <button type="button" @click="form.status = 'leave'"
                                :class="form.status === 'leave' ? 'bg-amber-500 text-white font-black shadow-xs ring-2 ring-amber-500/30' : 'bg-slate-50 text-slate-700 hover:bg-slate-100 border border-slate-200 font-bold'"
                                class="py-2.5 rounded-xl text-center cursor-pointer transition-all">
                            ئیجازە 🏖️
                        </button>
                        <button type="button" @click="form.status = 'absent'"
                                :class="form.status === 'absent' ? 'bg-rose-600 text-white font-black shadow-xs ring-2 ring-rose-600/30' : 'bg-slate-50 text-slate-700 hover:bg-slate-100 border border-slate-200 font-bold'"
                                class="py-2.5 rounded-xl text-center cursor-pointer transition-all">
                            نەهاتوو ❌
                        </button>
                        <button type="button" @click="form.status = 'holiday'"
                                :class="form.status === 'holiday' ? 'bg-slate-700 text-white font-black shadow-xs ring-2 ring-slate-700/30' : 'bg-slate-50 text-slate-700 hover:bg-slate-100 border border-slate-200 font-bold'"
                                class="py-2.5 rounded-xl text-center cursor-pointer transition-all">
                            پشوو ☕
                        </button>
                    </div>
                </div>

                {{-- کاتی هاتن و دەرچوون --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 bg-slate-50/80 p-3 sm:p-3.5 rounded-2xl border border-slate-200/80">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1 flex items-center gap-1 text-emerald-800">
                            <span>📥</span>
                            <span>کاتی هاتن</span>
                        </label>
                        <input type="time" x-model="form.check_in"
                               class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:outline-hidden focus:border-emerald-500 bg-white font-mono font-bold">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1 flex items-center gap-1 text-indigo-800">
                            <span>📤</span>
                            <span>کاتی دەرچوون</span>
                        </label>
                        <input type="time" x-model="form.check_out"
                               class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:outline-hidden focus:border-indigo-500 bg-white font-mono font-bold">
                    </div>
                </div>

                {{-- کاتی زیادە --}}
                <div>
                    <label class="block font-bold text-slate-700 mb-1">
                        ⏱️ کاتژمێری زیادە
                        <span class="text-slate-400 font-normal text-[11px]">(لە کاتی دیاریکراو زیاتر لە کارگە ماوەتەوە)</span>
                    </label>
                    <input type="number" step="0.5" min="0" x-model="form.overtime_hours" placeholder="وەک: 1.5 کاتژمێر..."
                           class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:outline-hidden focus:border-blue-500 bg-white">
                </div>

                {{-- ڕۆیشتنی کاتی لە ناو کاردا --}}
                <div class="bg-amber-50/50 p-3 sm:p-3.5 rounded-2xl border border-amber-200/60 space-y-2.5">
                    <label class="block font-bold text-amber-900">
                        🚪 دەرچوونی کاتی لە کاتی ئیشدا (ڕۆیشتن بۆ کار و گەڕانەوە)
                    </label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                        <div>
                            <span class="text-[11px] font-bold text-slate-600 block mb-1">ماوەی دەرچوون (کاتژمێر):</span>
                            <input type="number" step="0.5" min="0" x-model="form.temporary_exit_hours" placeholder="وەک: 2..."
                                   class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:outline-hidden focus:border-amber-500 bg-white">
                        </div>
                        <div>
                            <span class="text-[11px] font-bold text-slate-600 block mb-1">هۆکاری دەرچوون:</span>
                            <input type="text" x-model="form.exit_reason" placeholder="وەک: کڕینی پەڕەسەندن، کاری دەرەوە..."
                                   class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:outline-hidden focus:border-amber-500 bg-white">
                        </div>
                    </div>
                </div>

                {{-- چوون بۆ سەر ماڵان و خەرجی بەنزین --}}
                <div class="bg-emerald-50/50 p-3 sm:p-3.5 rounded-2xl border border-emerald-200/60 space-y-2.5">
                    <label class="block font-bold text-emerald-900">
                        🚗 چوون بۆ سەردانی ماڵی کڕیاران و خەرجی بەنزین
                    </label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                        <div>
                            <span class="text-[11px] font-bold text-slate-600 block mb-1">شوێن / ماڵی کڕیار:</span>
                            <input type="text" x-model="form.trip_destination" placeholder="وەک: ماڵی کاک کاروان، بەستنی دەرگا..."
                                   class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:outline-hidden focus:border-emerald-500 bg-white">
                        </div>
                        <div>
                            <span class="text-[11px] font-bold text-slate-600 block mb-1">بڕی خەرجی بەنزین (د.ع):</span>
                            <input type="number" step="500" min="0" x-model="form.fuel_expense" placeholder="وەک: 10000"
                                   class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:outline-hidden focus:border-emerald-500 bg-white font-mono font-bold">
                        </div>
                    </div>
                </div>

                {{-- تێبینی --}}
                <div>
                    <label class="block font-bold text-slate-700 mb-1">تێبینی و ڕوونکردنەوەی تر</label>
                    <input type="text" x-model="form.note" placeholder="هەر تێبینییەکی تایبەت..."
                           class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:outline-hidden focus:border-blue-500 bg-white">
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="showModal = false" class="px-4 py-2 rounded-xl text-xs font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 cursor-pointer transition-all">
                        داخستن
                    </button>
                    <button type="submit"
                            :disabled="isSaving"
                            class="px-5 py-2 rounded-xl text-xs font-bold bg-indigo-600 hover:bg-indigo-700 text-white shadow-xs cursor-pointer transition-all flex items-center gap-1.5">
                        <span x-show="isSaving" class="animate-spin text-xs">⏳</span>
                        <span>پاشەکەوتکردنی هەموو وردەکارییەکان</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- نۆتیفیکەیشنی سەرکەوتن (Toast) --}}
    <div x-show="toast.show" x-cloak
         x-transition:enter="transition ease-out duration-300 transform"
         x-transition:enter-start="opacity-0 translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200 transform"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-4"
         class="fixed bottom-6 left-6 z-50 bg-slate-900 text-white px-4 py-3 rounded-2xl shadow-xl border border-slate-700 flex items-center gap-2.5 text-xs font-bold">
        <span class="text-base" x-text="toast.icon"></span>
        <span x-text="toast.message"></span>
    </div>

</div>

<script>
function workshopEmployeesApp() {
    return {
        employeesList: @json($employeesData),
        showModal: false,
        selectedEmployee: null,
        statusFilter: 'all',
        searchQuery: '',
        viewMode: 'table',
        loadingId: null,
        isSaving: false,
        currentTime: '',
        toast: {
            show: false,
            message: '',
            icon: '✅',
            timer: null
        },
        form: {
            employee_id: '',
            work_date: '{{ now()->toDateString() }}',
            status: 'present',
            check_in: '',
            check_out: '',
            overtime_hours: 0,
            temporary_exit_hours: 0,
            exit_reason: '',
            fuel_expense: 0,
            trip_destination: '',
            note: ''
        },

        initClock() {
            this.updateClock();
            setInterval(() => this.updateClock(), 1000);
        },

        updateClock() {
            const now = new Date();
            this.currentTime = now.toLocaleTimeString('en-US', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: true
            });
        },

        showToast(msg, icon = '✅') {
            this.toast.message = msg;
            this.toast.icon = icon;
            this.toast.show = true;
            if (this.toast.timer) clearTimeout(this.toast.timer);
            this.toast.timer = setTimeout(() => {
                this.toast.show = false;
            }, 3500);
        },

        get filteredEmployees() {
            let list = this.employeesList;

            // ١. فلتەری دۆخ
            if (this.statusFilter === 'present') {
                list = list.filter(e => e.attendance && e.attendance.status === 'present');
            } else if (this.statusFilter === 'absent') {
                list = list.filter(e => e.attendance && e.attendance.status === 'absent');
            } else if (this.statusFilter === 'leave') {
                list = list.filter(e => e.attendance && e.attendance.status === 'leave');
            } else if (this.statusFilter === 'not_recorded') {
                list = list.filter(e => !e.attendance || !e.attendance.status);
            }

            // ٢. فلتەری گەڕان
            if (this.searchQuery.trim() !== '') {
                const q = this.searchQuery.toLowerCase().trim();
                list = list.filter(e => 
                    (e.name && e.name.toLowerCase().includes(q)) ||
                    (e.phone && e.phone.includes(q)) ||
                    (e.job_title_label && e.job_title_label.includes(q))
                );
            }

            return list;
        },

        countByStatus(status) {
            if (status === 'not_recorded') {
                return this.employeesList.filter(e => !e.attendance || !e.attendance.status).length;
            }
            return this.employeesList.filter(e => e.attendance && e.attendance.status === status).length;
        },

        openModalFor(emp) {
            this.selectedEmployee = emp;
            const att = emp.attendance;
            this.form = {
                employee_id: emp.id,
                work_date: '{{ now()->toDateString() }}',
                status: att ? att.status : 'present',
                check_in: att ? att.check_in : '',
                check_out: att ? att.check_out : '',
                overtime_hours: att ? att.overtime_hours : 0,
                temporary_exit_hours: att ? att.temporary_exit_hours : 0,
                exit_reason: att ? att.exit_reason : '',
                fuel_expense: att ? att.fuel_expense : 0,
                trip_destination: att ? att.trip_destination : '',
                note: att ? att.note : ''
            };
            this.showModal = true;
        },

        async quickCheckIn(employeeId) {
            this.loadingId = employeeId;
            try {
                const res = await fetch('{{ route('attendance.quick-check-in') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        employee_id: employeeId,
                        work_date: '{{ now()->toDateString() }}'
                    })
                });
                const data = await res.json();
                if (data.ok) {
                    const emp = this.employeesList.find(e => e.id === employeeId);
                    if (emp) {
                        if (!emp.attendance) {
                            emp.attendance = {
                                hours: 0,
                                overtime_hours: 0,
                                temporary_exit_hours: 0,
                                fuel_expense: 0
                            };
                        }
                        emp.attendance.status = 'present';
                        emp.attendance.status_label = 'ئامادەیە';
                        emp.attendance.check_in = data.attendance.check_in.substring(0, 5);
                    }
                    this.showToast(data.message || 'هاتن سەرکەوتووانە تۆمارکرا', '📥');
                }
            } catch (e) {
                this.showToast('هەڵەیەک ڕوویدا لە تۆمارکردنی هاتن', '⚠️');
            } finally {
                this.loadingId = null;
            }
        },

        async quickCheckOut(employeeId) {
            this.loadingId = employeeId;
            try {
                const res = await fetch('{{ route('attendance.quick-check-out') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        employee_id: employeeId,
                        work_date: '{{ now()->toDateString() }}'
                    })
                });
                const data = await res.json();
                if (data.ok) {
                    const emp = this.employeesList.find(e => e.id === employeeId);
                    if (emp) {
                        if (!emp.attendance) {
                            emp.attendance = {
                                hours: 0,
                                overtime_hours: 0,
                                temporary_exit_hours: 0,
                                fuel_expense: 0
                            };
                        }
                        emp.attendance.status = 'present';
                        emp.attendance.status_label = 'ئامادەیە';
                        emp.attendance.check_out = data.attendance.check_out.substring(0, 5);
                        emp.attendance.overtime_hours = data.attendance.overtime_hours;
                    }
                    this.showToast(data.message || 'دەرچوون سەرکەوتووانە تۆمارکرا', '📤');
                }
            } catch (e) {
                this.showToast('هەڵەیەک ڕوویدا لە تۆمارکردنی دەرچوون', '⚠️');
            } finally {
                this.loadingId = null;
            }
        },

        async saveAttendance() {
            this.isSaving = true;
            try {
                const res = await fetch('{{ route('attendance.record-single') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(this.form)
                });
                const data = await res.json();
                if (data.ok) {
                    const emp = this.employeesList.find(e => e.id === this.form.employee_id);
                    if (emp) {
                        emp.attendance = {
                            id: data.attendance.id,
                            status: data.attendance.status,
                            status_label: data.attendance.status,
                            check_in: data.attendance.check_in ? data.attendance.check_in.substring(0, 5) : '',
                            check_out: data.attendance.check_out ? data.attendance.check_out.substring(0, 5) : '',
                            hours: parseFloat(data.attendance.hours || 0),
                            overtime_hours: parseFloat(data.attendance.overtime_hours || 0),
                            temporary_exit_hours: parseFloat(data.attendance.temporary_exit_hours || 0),
                            exit_reason: data.attendance.exit_reason || '',
                            fuel_expense: parseFloat(data.attendance.fuel_expense || 0),
                            trip_destination: data.attendance.trip_destination || '',
                            note: data.attendance.note || ''
                        };
                    }
                    this.showModal = false;
                    this.showToast(data.message || 'وردەکارییەکان پاشەکەوتکران', '✅');
                } else {
                    this.showToast('هەڵەیەک ڕوویدا لە پاشەکەوتکردن', '⚠️');
                }
            } catch (e) {
                this.showToast('هەڵەی پەیوەندی لەگەڵ سێرڤەر', '⚠️');
            } finally {
                this.isSaving = false;
            }
        }
    };
}
</script>
@endsection
