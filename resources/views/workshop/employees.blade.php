@extends('layouts.menu')
@section('title', 'تۆماری ئامادەبوونی کارگە')

@section('content')
<div x-data="workshopEmployeesApp()" x-init="initClock()" class="space-y-4 sm:space-y-6">

    {{-- ١. هێڵی سەرەوە: ناونیشان، بەروار و دوگمەکانی بەڕێوەبەر --}}
    <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200 shadow-xs flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div class="flex items-center gap-3.5">
            <div class="w-12 h-12 rounded-2xl bg-indigo-600 text-white flex items-center justify-center text-2xl shadow-md shrink-0">
                👷
            </div>
            <div>
                <div class="flex items-center gap-2 flex-wrap">
                    <h1 class="text-lg sm:text-xl font-black text-slate-900">وەستا و حەمەڵەکانی کارگە</h1>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                        ئامادەبوون و حەقدەست
                    </span>
                </div>
                <p class="text-xs text-slate-500 mt-1 font-medium">
                    بەڕێوەبردنی مووچە، کاتی دەوام، کاتی زیادە، و خەرجی سەرفکردنی سەر ماڵان
                </p>
            </div>
        </div>

        {{-- هەڵبژاردنی بەروار + دوگمەکانی ڕێکخستن و زیادکردن --}}
        <div class="flex items-center gap-2.5 flex-wrap">
            {{-- دوگمەی زیادکردنی وەستای نوێ --}}
            <button type="button" @click="showNewEmployeeModal = true"
                    class="px-4 py-2.5 rounded-xl text-xs font-black bg-emerald-600 hover:bg-emerald-700 text-white shadow-md shadow-emerald-600/25 flex items-center gap-1.5 transition-all cursor-pointer border border-emerald-500">
                <span class="text-base font-black leading-none">+</span>
                <span>زیادکردنی وەستا / کارمەند</span>
            </button>

            {{-- بەرواری دیاریکراو --}}
            <div class="flex items-center gap-1.5 bg-slate-50 border border-slate-200 px-3 py-1.5 rounded-xl text-xs font-bold">
                <span class="text-slate-400">📅 بەروار:</span>
                <input type="date" x-model="selectedDate" @change="changeDate()"
                       class="font-mono font-bold text-slate-800 bg-transparent focus:outline-hidden cursor-pointer">
            </div>

            {{-- دوگمەی ڕێکخستنی دەوام و پشوو --}}
            <button type="button" @click="showSettingsModal = true"
                    class="px-3.5 py-2 rounded-xl text-xs font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-300 shadow-2xs flex items-center gap-1.5 transition-all cursor-pointer">
                <span>⚙️</span>
                <span>ڕێکخستنی دەوام و پشوو</span>
            </button>
        </div>
    </div>

    {{-- ٢. شریتی زانیاری کاتی کارکردن و دەوامی فەرمی --}}
    <div class="bg-indigo-50/60 border border-indigo-200/80 rounded-2xl p-3.5 sm:p-4 flex flex-col md:flex-row md:items-center justify-between gap-3 shadow-2xs">
        <div class="flex items-center gap-3 text-xs text-indigo-950 flex-wrap">
            <span class="text-lg shrink-0">⏰</span>
            <div class="flex items-center gap-3 flex-wrap">
                <span>کاتی دەوام: <b class="font-mono text-indigo-900 font-black">{{ $shiftSettings['work_start'] }} بۆ {{ $shiftSettings['work_end'] }}</b> ({{ $shiftSettings['work_hours'] }} کاتژمێر)</span>
                <span class="text-indigo-300">•</span>
                <span>پشووی هەفتانە: <b class="text-indigo-900 font-bold">{{ $shiftSettings['weekly_holiday'] === 'friday' ? 'ڕۆژی هەینی' : ($shiftSettings['weekly_holiday'] === 'saturday' ? 'ڕۆژی شەممە' : $shiftSettings['weekly_holiday']) }}</b></span>
                <span class="text-indigo-300">•</span>
                <span>کاتی زیادە: <b class="font-mono text-indigo-900 font-bold">{{ $shiftSettings['overtime_multiplier'] }}x</b></span>
            </div>
        </div>

        @if($isHoliday)
            <div class="px-3 py-1 rounded-xl bg-amber-100 text-amber-900 text-xs font-bold flex items-center gap-1.5 shrink-0 border border-amber-200">
                <span>☕</span>
                <span>ئەمڕۆ پشووی فەرمییە (دەوام و کاتی زیادە ئارەزوومەندانەیە)</span>
            </div>
        @endif
    </div>

    {{-- ٣. کارتەکانی ئامار و خولاسەی ڕۆژ --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2.5 sm:gap-3.5">
        {{-- کۆی گشتی کارمەندان --}}
        <div class="bg-white rounded-2xl p-3.5 border border-slate-200 shadow-xs">
            <div class="text-[11px] font-bold text-slate-500 mb-1">کۆی وەستاکان</div>
            <div class="text-xl font-black text-slate-900 font-mono">{{ $employees->count() }}</div>
        </div>

        {{-- ئامادەبووان --}}
        <div class="bg-emerald-50/70 rounded-2xl p-3.5 border border-emerald-200 shadow-xs">
            <div class="text-[11px] font-bold text-emerald-800 mb-1 flex items-center gap-1">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>ئامادەبوو</span>
            </div>
            <div class="text-xl font-black text-emerald-700 font-mono">{{ $presentCount }}</div>
        </div>

        {{-- ئیجازە --}}
        <div class="bg-amber-50/70 rounded-2xl p-3.5 border border-amber-200 shadow-xs">
            <div class="text-[11px] font-bold text-amber-800 mb-1 flex items-center gap-1">
                <span>🏖️</span>
                <span>ئیجازە</span>
            </div>
            <div class="text-xl font-black text-amber-700 font-mono">{{ $leaveCount }}</div>
        </div>

        {{-- نەهاتوو --}}
        <div class="bg-rose-50/70 rounded-2xl p-3.5 border border-rose-200 shadow-xs">
            <div class="text-[11px] font-bold text-rose-800 mb-1 flex items-center gap-1">
                <span>❌</span>
                <span>نەهاتوو</span>
            </div>
            <div class="text-xl font-black text-rose-700 font-mono">{{ $absentCount }}</div>
        </div>

        {{-- کۆی کاتی زیادە --}}
        <div class="bg-blue-50/70 rounded-2xl p-3.5 border border-blue-200 shadow-xs">
            <div class="text-[11px] font-bold text-blue-800 mb-1 flex items-center gap-1">
                <span>⏱️</span>
                <span>کاتی زیادە</span>
            </div>
            <div class="text-base sm:text-lg font-black text-blue-800 font-mono">
                {{ number_format($totalOvertime, 1) }} <span class="text-xs font-normal">ک/ژ</span>
            </div>
        </div>

        {{-- کۆی بەنزینی سەر ماڵان --}}
        <div class="bg-indigo-50/70 rounded-2xl p-3.5 border border-indigo-200 shadow-xs">
            <div class="text-[11px] font-bold text-indigo-800 mb-1 flex items-center gap-1">
                <span>🚗</span>
                <span>سەرفیاتی بەستن</span>
            </div>
            <div class="text-sm sm:text-base font-black text-indigo-900 font-mono">
                {{ number_format($totalFuel) }} <span class="text-[10px] font-normal">د.ع</span>
            </div>
        </div>
    </div>

    {{-- ٤. بەشی سەرەکی: فلتەرەکان، گەڕان و جەدوەل --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        {{-- هێڵی سەرەوەی جەدوەل --}}
        <div class="p-3.5 sm:p-4 border-b border-slate-100 flex flex-col lg:flex-row lg:items-center justify-between gap-3 bg-slate-50/50">
            {{-- فلتەری دۆخی ئامادەبوون --}}
            <div class="flex items-center gap-1.5 flex-wrap">
                <button type="button" @click="statusFilter = 'all'"
                        :class="statusFilter === 'all' ? 'bg-slate-900 text-white font-black shadow-xs' : 'bg-white text-slate-700 hover:bg-slate-50 border border-slate-200 font-bold'"
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
            </div>

            {{-- گەڕان و گۆڕینی شێوازی بینین --}}
            <div class="flex items-center gap-2.5">
                <div class="w-full sm:w-64">
                    <input type="text" x-model="searchQuery" placeholder="🔍 گەڕان بە ناوی وەستا یان مۆبایل..."
                           class="w-full text-xs px-3.5 py-2 rounded-xl border border-slate-200 focus:outline-hidden focus:border-indigo-500 bg-white font-medium text-right shadow-2xs">
                </div>

                <div class="flex items-center p-1 bg-slate-200/80 rounded-xl border border-slate-200 shrink-0">
                    <button type="button" @click="viewMode = 'table'"
                            :class="viewMode === 'table' ? 'bg-white text-slate-900 font-bold shadow-xs' : 'text-slate-600 hover:text-slate-900'"
                            class="px-2.5 py-1 rounded-lg text-xs transition-all cursor-pointer flex items-center gap-1">
                        <span>📊</span>
                        <span class="hidden sm:inline">جەدوەل</span>
                    </button>
                    <button type="button" @click="viewMode = 'cards'"
                            :class="viewMode === 'cards' ? 'bg-white text-slate-900 font-bold shadow-xs' : 'text-slate-600 hover:text-slate-900'"
                            class="px-2.5 py-1 rounded-lg text-xs transition-all cursor-pointer flex items-center gap-1">
                        <span>🎴</span>
                        <span class="hidden sm:inline">کارتەکان</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- خشتەی سەرەکی (TABLE VIEW) --}}
        <div x-show="viewMode === 'table'" class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead class="bg-slate-50 text-slate-600 border-b border-slate-200 font-black">
                    <tr>
                        <th class="p-3.5">وەستا / کارمەند</th>
                        <th class="p-3.5">مووچە / حەقدەست</th>
                        <th class="p-3.5">دۆخی ئامادەبوون</th>
                        <th class="p-3.5 text-center">کاتی هاتن</th>
                        <th class="p-3.5 text-center">کاتی دەرچوون</th>
                        <th class="p-3.5 text-center">کاتی زیادە</th>
                        <th class="p-3.5 text-center">سەرفیاتی بەستنی ماڵان</th>
                        <th class="p-3.5 text-center">کردارەکان</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <template x-for="emp in filteredEmployees" :key="emp.id">
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="p-3.5">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-700 font-black flex items-center justify-center text-xs shrink-0 border border-indigo-100"
                                         x-text="emp.name.substring(0, 1)"></div>
                                    <div>
                                        <div class="font-black text-slate-900 text-xs" x-text="emp.name"></div>
                                        <div class="text-[11px] text-slate-400 font-medium" x-text="emp.job_title_label"></div>
                                    </div>
                                </div>
                            </td>

                            {{-- مووچە بەپێی شێوازی پارەدان (ڕۆژانە / حەفتانە / مانگانە) لەگەڵ دوگمەی دەستکاری خێرا --}}
                            <td class="p-3.5">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <span class="font-mono font-bold text-slate-900" x-text="formatNumber(emp.daily_wage) + ' ' + emp.wage_currency"></span>
                                    <span class="text-[10px] px-1.5 py-0.5 rounded-md font-bold"
                                          :class="emp.salary_type === 'monthly' ? 'bg-purple-50 text-purple-700 border border-purple-200' : (emp.salary_type === 'weekly' ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200')"
                                          x-text="emp.salary_type_label || 'ڕۆژانە'"></span>
                                    <button type="button" @click="openEditWageModal(emp)"
                                            class="text-indigo-600 hover:text-indigo-800 p-1 rounded hover:bg-indigo-50 transition-colors"
                                            title="دەستکاری مووچە">✏️</button>
                                </div>
                            </td>

                            {{-- دۆخ --}}
                            <td class="p-3.5">
                                <template x-if="emp.attendance && emp.attendance.status === 'present'">
                                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        ئامادەیە ✔️
                                    </span>
                                </template>
                                <template x-if="emp.attendance && emp.attendance.status === 'leave'">
                                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                        ئیجازە 🏖️
                                    </span>
                                </template>
                                <template x-if="emp.attendance && emp.attendance.status === 'absent'">
                                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-rose-100 text-rose-800 border border-rose-200">
                                        نەهاتووە ❌
                                    </span>
                                </template>
                                <template x-if="!emp.attendance || !emp.attendance.status">
                                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-500 border border-slate-200">
                                        تۆمارنەکراو
                                    </span>
                                </template>
                            </td>

                            {{-- هاتن --}}
                            <td class="p-3.5 text-center font-mono font-bold text-slate-700">
                                <span x-text="emp.attendance?.check_in || '—'"></span>
                            </td>

                            {{-- دەرچوون --}}
                            <td class="p-3.5 text-center font-mono font-bold text-slate-700">
                                <span x-text="emp.attendance?.check_out || '—'"></span>
                            </td>

                            {{-- کاتی زیادە --}}
                            <td class="p-3.5 text-center">
                                <template x-if="emp.attendance && emp.attendance.overtime_hours > 0">
                                    <span class="px-2 py-0.5 rounded-md bg-blue-50 text-blue-700 font-mono font-bold text-[11px] border border-blue-200">
                                        +<span x-text="emp.attendance.overtime_hours"></span> ک/ژ
                                    </span>
                                </template>
                                <template x-if="!emp.attendance || !emp.attendance.overtime_hours">
                                    <span class="text-slate-400 font-mono">—</span>
                                </template>
                            </td>

                            {{-- سەرفیاتی سەر ماڵان --}}
                            <td class="p-3.5 text-center">
                                <template x-if="emp.attendance && emp.attendance.fuel_expense > 0">
                                    <div class="text-[11px] font-bold text-indigo-700">
                                        <span class="font-mono" x-text="formatNumber(emp.attendance.fuel_expense)"></span> د.ع
                                        <div x-show="emp.attendance.trip_destination" class="text-[10px] text-slate-400 font-normal" x-text="emp.attendance.trip_destination"></div>
                                    </div>
                                </template>
                                <template x-if="!emp.attendance || !emp.attendance.fuel_expense">
                                    <span class="text-slate-400 font-mono">—</span>
                                </template>
                            </td>

                            {{-- کردارەکان --}}
                            <td class="p-3.5 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <button type="button" @click="quickCheckIn(emp.id)"
                                            class="px-2.5 py-1 rounded-lg text-xs font-bold bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 transition-all cursor-pointer">
                                        📥 هاتن
                                    </button>
                                    <button type="button" @click="quickCheckOut(emp.id)"
                                            class="px-2.5 py-1 rounded-lg text-xs font-bold bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 transition-all cursor-pointer">
                                        📤 دەرچوون
                                    </button>
                                    <button type="button" @click="openModalFor(emp)"
                                            class="px-2.5 py-1 rounded-lg text-xs font-bold bg-white hover:bg-slate-100 text-slate-700 border border-slate-200 transition-all cursor-pointer shadow-2xs">
                                        📝 وردەکاری
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        {{-- شێوازی کارتەکان (CARD VIEW) --}}
        <div x-show="viewMode === 'cards'" class="p-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <template x-for="emp in filteredEmployees" :key="emp.id">
                <div class="bg-white rounded-2xl p-4 border border-slate-200 hover:border-indigo-300 hover:shadow-md transition-all flex flex-col justify-between shadow-2xs">
                    <div>
                        <div class="flex items-start justify-between gap-2 mb-3">
                            <div class="flex items-center gap-2.5">
                                <div class="w-10 h-10 rounded-2xl bg-indigo-600 text-white font-black flex items-center justify-center text-sm shadow-xs"
                                     x-text="emp.name.substring(0, 1)"></div>
                                <div>
                                    <h3 class="font-black text-sm text-slate-900" x-text="emp.name"></h3>
                                    <div class="text-xs text-slate-400 font-medium" x-text="emp.job_title_label"></div>
                                </div>
                            </div>
                            <button type="button" @click="openEditWageModal(emp)"
                                    class="text-xs font-bold text-indigo-600 hover:text-indigo-800 bg-indigo-50 px-2 py-1 rounded-lg border border-indigo-100">
                                ✏️ دەستکاری
                            </button>
                        </div>

                        <div class="bg-slate-50 rounded-xl p-2.5 mb-3 border border-slate-100 text-xs space-y-1.5">
                            <div class="flex items-center justify-between">
                                <span class="text-slate-500">مووچە / حەقدەست:</span>
                                <div class="flex items-center gap-1.5">
                                    <span class="font-mono font-black text-slate-800" x-text="formatNumber(emp.daily_wage) + ' ' + emp.wage_currency"></span>
                                    <span class="text-[10px] px-1.5 py-0.2 rounded-md font-bold"
                                          :class="emp.salary_type === 'monthly' ? 'bg-purple-100 text-purple-700' : (emp.salary_type === 'weekly' ? 'bg-blue-100 text-blue-700' : 'bg-emerald-100 text-emerald-700')"
                                          x-text="emp.salary_type_label || 'ڕۆژانە'"></span>
                                </div>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-slate-500">دۆخی ئامادەبوون:</span>
                                <span class="font-bold" :class="emp.attendance?.status === 'present' ? 'text-emerald-700' : 'text-slate-600'" x-text="emp.attendance?.status_label || 'تۆمارنەکراو'"></span>
                            </div>
                            <div class="flex items-center justify-between pt-1 border-t border-slate-200/60 font-mono text-[11px]">
                                <span>هاتن: <b class="text-slate-800" x-text="emp.attendance?.check_in || '—'"></b></span>
                                <span>دەرچوون: <b class="text-slate-800" x-text="emp.attendance?.check_out || '—'"></b></span>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-1.5 pt-2 border-t border-slate-100">
                        <button type="button" @click="quickCheckIn(emp.id)"
                                class="flex-1 py-2 rounded-xl text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white transition-all">
                            📥 هاتن
                        </button>
                        <button type="button" @click="quickCheckOut(emp.id)"
                                class="flex-1 py-2 rounded-xl text-xs font-bold bg-indigo-600 hover:bg-indigo-700 text-white transition-all">
                            📤 دەرچوون
                        </button>
                        <button type="button" @click="openModalFor(emp)"
                                class="py-2 px-3 rounded-xl text-xs font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200">
                            📝
                        </button>
                    </div>
                </div>
            </template>
        </div>
    </div>

    {{-- ================= موداڵەکان ================= --}}

    {{-- ١. مۆداڵی ڕێکخستنی کاتی دەوام و پشوو --}}
    <div x-show="showSettingsModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4"
         @click.self="showSettingsModal = false">
        <div class="w-full max-w-lg bg-white rounded-3xl border border-slate-200 shadow-2xl overflow-hidden animate-in fade-in zoom-in-95">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4 bg-slate-50">
                <div class="flex items-center gap-2">
                    <span class="text-lg">⚙️</span>
                    <h3 class="font-black text-sm text-slate-800">ڕێکخستنی کاتی دەوام، پشوو و کاتی زیادە</h3>
                </div>
                <button type="button" @click="showSettingsModal = false" class="w-7 h-7 rounded-lg text-slate-400 hover:bg-slate-200 text-sm font-bold">✕</button>
            </div>

            <form @submit.prevent="saveSettings()" class="p-5 space-y-4 text-xs">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">کاتی دەستپێکردنی دەوام (هاتن)</label>
                        <input type="time" x-model="settingsForm.workshop_work_start" required
                               class="w-full px-3 py-2 rounded-xl border border-slate-200 focus:outline-hidden focus:border-indigo-500 font-mono font-bold">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">کاتی تەواوبوونی دەوام (دەرچوون)</label>
                        <input type="time" x-model="settingsForm.workshop_work_end" required
                               class="w-full px-3 py-2 rounded-xl border border-slate-200 focus:outline-hidden focus:border-indigo-500 font-mono font-bold">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">کاتژمێری کارکردنی ڕۆژانە</label>
                        <input type="number" step="0.5" min="1" max="24" x-model="settingsForm.workshop_work_hours" required
                               class="w-full px-3 py-2 rounded-xl border border-slate-200 focus:outline-hidden focus:border-indigo-500 font-mono font-bold">
                        <span class="text-[10px] text-slate-400">زیاتر لەم کاتژمێرە وەک کاتی زیادە هەژمار دەکرێت</span>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">ڕێژەی لێکدانی کاتی زیادە</label>
                        <select x-model="settingsForm.workshop_overtime_multiplier" class="w-full px-3 py-2 rounded-xl border border-slate-200 focus:outline-hidden focus:border-indigo-500 font-bold">
                            <option value="1.0">1.0x (حیسابی کاتژمێری ئاسایی)</option>
                            <option value="1.25">1.25x (کاتژمێر و چارەکێک)</option>
                            <option value="1.5">1.5x (کاتژمێر و نیو)</option>
                            <option value="2.0">2.0x (دوو هێندە)</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">ڕۆژی پشووی فەرمی هەفتانە</label>
                    <select x-model="settingsForm.workshop_weekly_holiday" class="w-full px-3 py-2 rounded-xl border border-slate-200 focus:outline-hidden focus:border-indigo-500 font-bold">
                        <option value="friday">ڕۆژی هەینی (پشووی بنەڕەت)</option>
                        <option value="saturday">ڕۆژی شەممە</option>
                        <option value="friday,saturday">هەینی و شەممە</option>
                        <option value="none">هیچ پشوویەکی هەفتانە نییە</option>
                    </select>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="showSettingsModal = false" class="btn btn-ghost !py-2 !px-4 text-xs font-bold text-slate-600 bg-slate-100 hover:bg-slate-200">داخستن</button>
                    <button type="submit" class="btn btn-primary !py-2 !px-5 text-xs font-bold" style="background-color: #2563eb !important; color: #ffffff !important;">پاشەکەوتکردنی ڕێکخستنەکان</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ٢. مۆداڵی زیادکردنی وەستا یان کارمەندی نوێ --}}
    <div x-show="showNewEmployeeModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4"
         @click.self="showNewEmployeeModal = false">
        <div class="w-full max-w-md bg-white rounded-3xl border border-slate-200 shadow-2xl overflow-hidden animate-in fade-in zoom-in-95">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4 bg-slate-50">
                <div class="flex items-center gap-2">
                    <span class="text-lg">👷</span>
                    <h3 class="font-black text-sm text-slate-800">زیادکردنی وەستا / کارمەندی نوێ</h3>
                </div>
                <button type="button" @click="showNewEmployeeModal = false" class="w-7 h-7 rounded-lg text-slate-400 hover:bg-slate-200 text-sm font-bold">✕</button>
            </div>

            <form @submit.prevent="storeEmployee()" class="p-5 space-y-3.5 text-xs">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">ناوی تەواوی وەستا / کارمەند *</label>
                    <input type="text" x-model="newEmployeeForm.name" required placeholder="ناوی کارمەند بنووسە..."
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 focus:outline-hidden focus:border-indigo-500 font-bold">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">پیشە / ڕۆڵ *</label>
                        <div x-show="!newEmployeeCustomJob">
                            <select x-model="newEmployeeForm.job_title"
                                    @change="if(newEmployeeForm.job_title === '__NEW__') { newEmployeeCustomJob = true; newEmployeeForm.job_title = ''; }"
                                    required class="w-full px-3 py-2 rounded-xl border border-slate-200 focus:outline-hidden focus:border-indigo-500 font-bold">
                                <option value="master">وەستا</option>
                                <option value="porter">حەمەڵ</option>
                                <option value="helper">یاریدەدەر</option>
                                <option value="driver">شۆفێر</option>
                                <option value="بۆیاغچی">بۆیاغچی</option>
                                <option value="لەحیمچی">لەحیمچی</option>
                                <option value="other">هیتر</option>
                                <option value="__NEW__" class="font-bold text-indigo-600">+ زیادکردنی پیشەی نوێ</option>
                            </select>
                        </div>
                        <div x-show="newEmployeeCustomJob" x-cloak class="flex gap-1">
                            <input type="text" x-model="newEmployeeForm.job_title" placeholder="پیشە بە دەست بنووسە..."
                                    class="w-full px-3 py-2 rounded-xl border border-slate-200 focus:outline-hidden focus:border-indigo-500 font-bold">
                            <button type="button" @click="newEmployeeCustomJob = false; newEmployeeForm.job_title = 'master';"
                                    class="px-2 py-1 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-lg text-xs font-bold shrink-0">لیست</button>
                        </div>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">ژمارەی مۆبایل</label>
                        <input type="text" x-model="newEmployeeForm.phone" placeholder="0750xxxxxxx"
                               class="w-full px-3 py-2 rounded-xl border border-slate-200 focus:outline-hidden focus:border-indigo-500 font-mono">
                    </div>
                </div>

                {{-- شێوازی پارەدان: ڕۆژانە / حەفتانە / مانگانە --}}
                <div>
                    <label class="block font-bold text-slate-700 mb-1">شێوازی پارەدان *</label>
                    <div class="grid grid-cols-3 gap-2">
                        <label class="flex items-center justify-center gap-1.5 p-2 rounded-xl border cursor-pointer transition-all text-xs font-bold"
                               :class="newEmployeeForm.salary_type === 'daily' ? 'bg-emerald-50 border-emerald-500 text-emerald-800 shadow-2xs' : 'bg-slate-50 border-slate-200 text-slate-600 hover:bg-slate-100'">
                            <input type="radio" value="daily" x-model="newEmployeeForm.salary_type" class="sr-only">
                            <span>📅 ڕۆژانە</span>
                        </label>
                        <label class="flex items-center justify-center gap-1.5 p-2 rounded-xl border cursor-pointer transition-all text-xs font-bold"
                               :class="newEmployeeForm.salary_type === 'weekly' ? 'bg-blue-50 border-blue-500 text-blue-800 shadow-2xs' : 'bg-slate-50 border-slate-200 text-slate-600 hover:bg-slate-100'">
                            <input type="radio" value="weekly" x-model="newEmployeeForm.salary_type" class="sr-only">
                            <span>🗓️ حەفتانە</span>
                        </label>
                        <label class="flex items-center justify-center gap-1.5 p-2 rounded-xl border cursor-pointer transition-all text-xs font-bold"
                               :class="newEmployeeForm.salary_type === 'monthly' ? 'bg-purple-50 border-purple-500 text-purple-800 shadow-2xs' : 'bg-slate-50 border-slate-200 text-slate-600 hover:bg-slate-100'">
                            <input type="radio" value="monthly" x-model="newEmployeeForm.salary_type" class="sr-only">
                            <span>📆 مانگانە</span>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">
                        <span x-text="newEmployeeForm.salary_type === 'monthly' ? 'مووچەی مانگانە (دینار) *' : (newEmployeeForm.salary_type === 'weekly' ? 'مووچەی حەفتانە (دینار) *' : 'حەقدەستی ڕۆژانە (دینار) *')"></span>
                    </label>
                    <div class="flex items-center rounded-xl border border-slate-200 focus-within:border-indigo-500 focus-within:ring-1 focus-within:ring-indigo-500 bg-white overflow-hidden shadow-2xs">
                        <input type="number" step="any" min="0" x-model="newEmployeeForm.daily_wage" required placeholder="25000"
                               dir="ltr"
                               class="w-full px-3 py-2 border-0 focus:outline-hidden font-mono font-bold text-slate-900 bg-transparent text-left">
                        <span class="px-3.5 py-2 text-xs font-bold text-slate-600 bg-slate-100 border-s border-slate-200 flex items-center font-mono select-none shrink-0">
                            د.ع
                        </span>
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">تێبینی زیادە</label>
                    <input type="text" x-model="newEmployeeForm.note" placeholder="تێبینی یان ناونیشان..."
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 focus:outline-hidden focus:border-indigo-500">
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="showNewEmployeeModal = false" class="btn btn-ghost !py-2 !px-4 text-xs font-bold text-slate-600 bg-slate-100 hover:bg-slate-200">داخستن</button>
                    <button type="submit" class="btn btn-primary !py-2 !px-5 text-xs font-bold" style="background-color: #059669 !important; color: #ffffff !important;">💾 پاشەکەوتکردن و زیادکردن</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ٣. مۆداڵی دەستکاری مووچە و زانیاری کارمەند --}}
    <div x-show="showEditWageModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4"
         @click.self="showEditWageModal = false">
        <div class="w-full max-w-md bg-white rounded-3xl border border-slate-200 shadow-2xl overflow-hidden animate-in fade-in zoom-in-95">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4 bg-slate-50">
                <div class="flex items-center gap-2">
                    <span class="text-lg">✏️</span>
                    <h3 class="font-black text-sm text-slate-800">دەستکاری مووچە و شێوازی پارەدان</h3>
                </div>
                <button type="button" @click="showEditWageModal = false" class="w-7 h-7 rounded-lg text-slate-400 hover:bg-slate-200 text-sm font-bold">✕</button>
            </div>

            <form @submit.prevent="updateWage()" class="p-5 space-y-3.5 text-xs">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">ناو</label>
                    <input type="text" x-model="editWageForm.name" required class="w-full px-3 py-2 rounded-xl border border-slate-200 font-bold">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">پیشە</label>
                        <div x-show="!editWageCustomJob">
                            <select x-model="editWageForm.job_title"
                                    @change="if(editWageForm.job_title === '__NEW__') { editWageCustomJob = true; editWageForm.job_title = ''; }"
                                    class="w-full px-3 py-2 rounded-xl border border-slate-200 font-bold">
                                <option value="master">وەستا</option>
                                <option value="porter">حەمەڵ</option>
                                <option value="helper">یاریدەدەر</option>
                                <option value="driver">شۆفێر</option>
                                <option value="بۆیاغچی">بۆیاغچی</option>
                                <option value="لەحیمچی">لەحیمچی</option>
                                <option value="other">هیتر</option>
                                <option value="__NEW__" class="font-bold text-indigo-600">+ زیادکردنی پیشەی نوێ</option>
                            </select>
                        </div>
                        <div x-show="editWageCustomJob" x-cloak class="flex gap-1">
                            <input type="text" x-model="editWageForm.job_title" placeholder="پیشە بە دەست بنووسە..."
                                    class="w-full px-3 py-2 rounded-xl border border-slate-200 font-bold">
                            <button type="button" @click="editWageCustomJob = false; editWageForm.job_title = 'master';"
                                    class="px-2 py-1 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-lg text-xs font-bold shrink-0">لیست</button>
                        </div>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">ژمارەی مۆبایل</label>
                        <input type="text" x-model="editWageForm.phone" class="w-full px-3 py-2 rounded-xl border border-slate-200 font-mono">
                    </div>
                </div>

                {{-- شێوازی پارەدان لە دەستکاریدا --}}
                <div>
                    <label class="block font-bold text-slate-700 mb-1">شێوازی پارەدان *</label>
                    <div class="grid grid-cols-3 gap-2">
                        <label class="flex items-center justify-center gap-1.5 p-2 rounded-xl border cursor-pointer transition-all text-xs font-bold"
                               :class="editWageForm.salary_type === 'daily' ? 'bg-emerald-50 border-emerald-500 text-emerald-800 shadow-2xs' : 'bg-slate-50 border-slate-200 text-slate-600 hover:bg-slate-100'">
                            <input type="radio" value="daily" x-model="editWageForm.salary_type" class="sr-only">
                            <span>📅 ڕۆژانە</span>
                        </label>
                        <label class="flex items-center justify-center gap-1.5 p-2 rounded-xl border cursor-pointer transition-all text-xs font-bold"
                               :class="editWageForm.salary_type === 'weekly' ? 'bg-blue-50 border-blue-500 text-blue-800 shadow-2xs' : 'bg-slate-50 border-slate-200 text-slate-600 hover:bg-slate-100'">
                            <input type="radio" value="weekly" x-model="editWageForm.salary_type" class="sr-only">
                            <span>🗓️ حەفتانە</span>
                        </label>
                        <label class="flex items-center justify-center gap-1.5 p-2 rounded-xl border cursor-pointer transition-all text-xs font-bold"
                               :class="editWageForm.salary_type === 'monthly' ? 'bg-purple-50 border-purple-500 text-purple-800 shadow-2xs' : 'bg-slate-50 border-slate-200 text-slate-600 hover:bg-slate-100'">
                            <input type="radio" value="monthly" x-model="editWageForm.salary_type" class="sr-only">
                            <span>📆 مانگانە</span>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">
                        <span x-text="editWageForm.salary_type === 'monthly' ? 'مووچەی مانگانە (دینار) *' : (editWageForm.salary_type === 'weekly' ? 'مووچەی حەفتانە (دینار) *' : 'حەقدەستی ڕۆژانە (دینار) *')"></span>
                    </label>
                    <div class="flex items-center rounded-xl border border-slate-200 focus-within:border-indigo-500 focus-within:ring-1 focus-within:ring-indigo-500 bg-white overflow-hidden shadow-2xs">
                        <input type="number" step="any" min="0" x-model="editWageForm.daily_wage" required placeholder="25000"
                               dir="ltr"
                               class="w-full px-3 py-2 border-0 focus:outline-hidden font-mono font-black text-indigo-700 bg-transparent text-left">
                        <span class="px-3.5 py-2 text-xs font-bold text-slate-600 bg-slate-100 border-s border-slate-200 flex items-center font-mono select-none shrink-0">
                            د.ع
                        </span>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="showEditWageModal = false" class="btn btn-ghost !py-2 !px-4 text-xs font-bold text-slate-600 bg-slate-100 hover:bg-slate-200">داخستن</button>
                    <button type="submit" class="btn btn-primary !py-2 !px-5 text-xs font-bold" style="background-color: #2563eb !important; color: #ffffff !important;">نوێکردنەوەی مووچە</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ٤. مۆداڵی وردەکاری ئامادەبوون، کاتی زیادە و سەرفیاتی سەر ماڵان --}}
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4"
         @click.self="showModal = false">
        <div class="w-full max-w-lg bg-white rounded-3xl border border-slate-200 shadow-2xl overflow-hidden animate-in fade-in zoom-in-95">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4 bg-slate-50">
                <div class="flex items-center gap-2">
                    <span class="text-lg">📝</span>
                    <div>
                        <h3 class="font-black text-sm text-slate-800" x-text="'تۆماری ئامادەبوونی ' + (selectedEmployee?.name || '')"></h3>
                        <p class="text-[11px] text-slate-400 font-medium">کاتی هاتن، دەرچوون، کاتی زیادە و سەرفیاتی سەر ماڵان</p>
                    </div>
                </div>
                <button type="button" @click="showModal = false" class="w-7 h-7 rounded-lg text-slate-400 hover:bg-slate-200 text-sm font-bold">✕</button>
            </div>

            <form @submit.prevent="saveAttendance()" class="p-5 space-y-3.5 text-xs">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">دۆخی ئامادەبوون</label>
                        <select x-model="form.status" class="w-full px-3 py-2 rounded-xl border border-slate-200 font-bold">
                            <option value="present">ئامادەیە ✔️</option>
                            <option value="absent">نەهاتووە ❌</option>
                            <option value="leave">ئیجازە 🏖️</option>
                            <option value="holiday">پشوو ☕</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">کاتی زیادە (کاتژمێر)</label>
                        <input type="number" step="0.5" min="0" x-model="form.overtime_hours" placeholder="0"
                               class="w-full px-3 py-2 rounded-xl border border-slate-200 font-mono font-bold text-blue-700">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">کاتی هاتن (Check-in)</label>
                        <input type="time" x-model="form.check_in"
                               class="w-full px-3 py-2 rounded-xl border border-slate-200 font-mono font-bold">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">کاتی دەرچوون (Check-out)</label>
                        <input type="time" x-model="form.check_out"
                               class="w-full px-3 py-2 rounded-xl border border-slate-200 font-mono font-bold">
                    </div>
                </div>

                {{-- بەشی خەرجی و سەرفیاتی سەر ماڵان بۆ بەستن --}}
                <div class="bg-indigo-50/50 rounded-2xl p-3.5 border border-indigo-100 space-y-2.5">
                    <div class="font-black text-indigo-900 text-xs flex items-center gap-1.5">
                        <span>🚗</span>
                        <span>خەرجی و سەرفیاتی سەر ماڵان (بۆ بەستنی دەرگا یان کەلوپەل)</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">خەرجی بەنزین / کرێ (د.ع)</label>
                            <input type="number" step="any" min="0" x-model="form.fuel_expense" placeholder="0"
                                   class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-white font-mono font-bold text-indigo-700">
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 mb-1">شوێنی بەستن / ناوی ماڵی کڕیار</label>
                            <input type="text" x-model="form.trip_destination" placeholder="گەڕەک یان ناوی کڕیار..."
                                   class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-white">
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">دەرچوونی کاتی (کاتژمێر و هۆکار)</label>
                    <div class="grid grid-cols-3 gap-2">
                        <input type="number" step="0.5" min="0" x-model="form.temporary_exit_hours" placeholder="کاتژمێر..."
                               class="px-3 py-2 rounded-xl border border-slate-200 font-mono font-bold">
                        <input type="text" x-model="form.exit_reason" placeholder="هۆکاری دەرچوون..."
                               class="col-span-2 px-3 py-2 rounded-xl border border-slate-200">
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">تێبینی</label>
                    <input type="text" x-model="form.note" placeholder="تێبینی زیادە بنووسە..."
                           class="w-full px-3 py-2 rounded-xl border border-slate-200">
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="showModal = false" class="btn btn-ghost !py-2 !px-4 text-xs font-bold text-slate-600 bg-slate-100 hover:bg-slate-200">داخستن</button>
                    <button type="submit" class="btn btn-primary !py-2 !px-5 text-xs font-bold" style="background-color: #2563eb !important; color: #ffffff !important;">پاشەکەوتکردن</button>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
function workshopEmployeesApp() {
    return {
        selectedDate: '{{ $date }}',
        currentTime: '',
        searchQuery: '',
        statusFilter: 'all',
        viewMode: 'table',
        employeesList: @json($employeesData),

        showSettingsModal: false,
        showNewEmployeeModal: false,
        showEditWageModal: false,
        showModal: false,
        selectedEmployee: null,

        newEmployeeCustomJob: false,
        editWageCustomJob: false,

        settingsForm: {
            workshop_work_start: '{{ $shiftSettings['work_start'] }}',
            workshop_work_end: '{{ $shiftSettings['work_end'] }}',
            workshop_work_hours: {{ $shiftSettings['work_hours'] }},
            workshop_weekly_holiday: '{{ $shiftSettings['weekly_holiday'] }}',
            workshop_overtime_multiplier: {{ $shiftSettings['overtime_multiplier'] }}
        },

        newEmployeeForm: {
            name: '',
            phone: '',
            job_title: 'master',
            salary_type: 'daily',
            daily_wage: '',
            wage_currency: 'IQD',
            note: ''
        },

        editWageForm: {
            id: null,
            name: '',
            phone: '',
            job_title: 'master',
            salary_type: 'daily',
            daily_wage: '',
            wage_currency: 'IQD'
        },

        form: {
            employee_id: null,
            work_date: '{{ $date }}',
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
            this.updateTime();
            setInterval(() => this.updateTime(), 1000);
        },

        updateTime() {
            const now = new Date();
            this.currentTime = now.toLocaleTimeString('en-US', { hour12: true, hour: '2-digit', minute: '2-digit' });
        },

        changeDate() {
            window.location.href = `{{ route('workshop.employees') }}?date=${this.selectedDate}`;
        },

        get filteredEmployees() {
            return this.employeesList.filter(emp => {
                const matchStatus = this.statusFilter === 'all' || (emp.attendance && emp.attendance.status === this.statusFilter);
                const q = this.searchQuery.toLowerCase();
                const matchQuery = !q || emp.name.toLowerCase().includes(q) || (emp.phone && emp.phone.includes(q));
                return matchStatus && matchQuery;
            });
        },

        countByStatus(status) {
            return this.employeesList.filter(e => e.attendance && e.attendance.status === status).length;
        },

        formatNumber(num) {
            return Number(num || 0).toLocaleString();
        },

        openModalFor(emp) {
            this.selectedEmployee = emp;
            const att = emp.attendance;
            this.form = {
                employee_id: emp.id,
                work_date: this.selectedDate,
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

        openEditWageModal(emp) {
            const standardJobs = ['master', 'porter', 'helper', 'driver', 'other', 'بۆیاغچی', 'لەحیمچی'];
            const isStandard = standardJobs.includes(emp.job_title);
            this.editWageCustomJob = !isStandard;
            this.editWageForm = {
                id: emp.id,
                name: emp.name,
                phone: emp.phone || '',
                job_title: emp.job_title,
                salary_type: emp.salary_type || 'daily',
                daily_wage: emp.daily_wage,
                wage_currency: emp.wage_currency
            };
            this.showEditWageModal = true;
        },

        async quickCheckIn(employeeId) {
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
                        work_date: this.selectedDate
                    })
                });
                const data = await res.json();
                if (data.ok) {
                    const emp = this.employeesList.find(e => e.id === employeeId);
                    if (emp) {
                        if (!emp.attendance) {
                            emp.attendance = { hours: 0, overtime_hours: 0, temporary_exit_hours: 0, fuel_expense: 0 };
                        }
                        emp.attendance.status = 'present';
                        emp.attendance.status_label = 'ئامادەیە';
                        emp.attendance.check_in = data.attendance.check_in.substring(0, 5);
                    }
                }
            } catch (e) {
                alert('هەڵەی پەیوەندی.');
            }
        },

        async quickCheckOut(employeeId) {
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
                        work_date: this.selectedDate
                    })
                });
                const data = await res.json();
                if (data.ok) {
                    const emp = this.employeesList.find(e => e.id === employeeId);
                    if (emp) {
                        if (!emp.attendance) {
                            emp.attendance = { hours: 0, overtime_hours: 0, temporary_exit_hours: 0, fuel_expense: 0 };
                        }
                        emp.attendance.status = 'present';
                        emp.attendance.status_label = 'ئامادەیە';
                        emp.attendance.check_out = data.attendance.check_out.substring(0, 5);
                        emp.attendance.overtime_hours = data.attendance.overtime_hours;
                    }
                }
            } catch (e) {
                alert('هەڵەی پەیوەندی.');
            }
        },

        async saveAttendance() {
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
                }
            } catch (e) {
                alert('هەڵەی پەیوەندی.');
            }
        },

        async saveSettings() {
            try {
                const res = await fetch('{{ route('workshop.settings') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(this.settingsForm)
                });
                const data = await res.json();
                if (data.ok) {
                    this.showSettingsModal = false;
                    window.location.reload();
                }
            } catch (e) {
                alert('هەڵەی پەیوەندی.');
            }
        },

        async storeEmployee() {
            try {
                const res = await fetch('{{ route('workshop.employees.quick-store') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(this.newEmployeeForm)
                });
                const data = await res.json();
                if (data.ok) {
                    this.showNewEmployeeModal = false;
                    window.location.reload();
                }
            } catch (e) {
                alert('هەڵەی پەیوەندی.');
            }
        },

        async updateWage() {
            try {
                const res = await fetch(`/workshop/employees/${this.editWageForm.id}/update-wage`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(this.editWageForm)
                });
                const data = await res.json();
                if (data.ok) {
                    const emp = this.employeesList.find(e => e.id === this.editWageForm.id);
                    if (emp) {
                        emp.name = this.editWageForm.name;
                        emp.phone = this.editWageForm.phone;
                        emp.job_title = data.job_title || this.editWageForm.job_title;
                        emp.job_title_label = data.job_title_label || this.editWageForm.job_title;
                        emp.salary_type = data.salary_type || this.editWageForm.salary_type;
                        emp.salary_type_label = data.salary_type_label || (emp.salary_type === 'monthly' ? 'مانگانە' : (emp.salary_type === 'weekly' ? 'حەفتانە' : 'ڕۆژانە'));
                        emp.daily_wage = parseFloat(this.editWageForm.daily_wage);
                        emp.wage_currency = this.editWageForm.wage_currency;
                    }
                    this.showEditWageModal = false;
                }
            } catch (e) {
                alert('هەڵەی پەیوەندی.');
            }
        }
    };
}
</script>
@endsection
