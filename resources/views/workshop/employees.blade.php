@extends('layouts.menu')
@section('title', 'جەدوەلی سەحی ڕۆژانە و حیساباتی وەستاکان')

@section('content')
<div x-data="workshopEmployeesMatrixApp()" x-init="init()" class="space-y-4 sm:space-y-6">

    {{-- ١. هێڵی سەرەوە: ناونیشان، فلتەری ماوەی بەروار و دوگمەکانی کردار --}}
    <div class="bg-white rounded-3xl p-4 sm:p-5 border border-slate-200 shadow-xs flex flex-col xl:flex-row xl:items-center justify-between gap-4">
        <div class="flex items-center gap-3.5">
            <div class="w-12 h-12 rounded-2xl bg-indigo-600 text-white flex items-center justify-center text-2xl shadow-md shadow-indigo-600/20 shrink-0">
                📋
            </div>
            <div>
                <div class="flex items-center gap-2 flex-wrap">
                    <h1 class="text-lg sm:text-xl font-black text-slate-900">جەدوەلی ئامادەبوونی ڕۆژانە و حیساباتی وەستاکان</h1>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        شێوازی دەفتەری سەح
                    </span>
                </div>
                <p class="text-xs text-slate-500 mt-0.5 font-medium flex items-center gap-2 flex-wrap">
                    <span>ماوەی دیاریکراو:</span>
                    <b class="font-mono text-slate-800 font-bold bg-slate-100 px-2 py-0.5 rounded-md text-[11px] border border-slate-200">
                        {{ str_replace('-', '/', $from) }} تا {{ str_replace('-', '/', $to) }}
                    </b>
                    <span class="text-slate-400 font-normal">({{ count($days) }} ڕۆژ)</span>
                </p>
            </div>
        </div>

        {{-- فلتەری ماوەکانی بەروار (هەفتانە / مانگانە / دیاریکراو) --}}
        <div class="flex items-center gap-2 flex-wrap">
            <div class="flex items-center p-1 bg-slate-100 rounded-2xl border border-slate-200">
                <button type="button" @click="setRange('this_week')"
                        :class="rangeType === 'this_week' ? 'bg-white text-indigo-700 font-black shadow-xs' : 'text-slate-600 hover:text-slate-900 font-bold'"
                        class="px-3 py-1.5 rounded-xl text-xs transition-all cursor-pointer">
                    ئەم هەفتەیە (٧ ڕۆژ)
                </button>
                <button type="button" @click="setRange('last_week')"
                        :class="rangeType === 'last_week' ? 'bg-white text-indigo-700 font-black shadow-xs' : 'text-slate-600 hover:text-slate-900 font-bold'"
                        class="px-3 py-1.5 rounded-xl text-xs transition-all cursor-pointer">
                    هەفتەی پێشوو
                </button>
                <button type="button" @click="setRange('this_month')"
                        :class="rangeType === 'this_month' ? 'bg-white text-indigo-700 font-black shadow-xs' : 'text-slate-600 hover:text-slate-900 font-bold'"
                        class="px-3 py-1.5 rounded-xl text-xs transition-all cursor-pointer">
                    ئەم مانگە
                </button>
            </div>

            {{-- فۆڕمی بەرواری دەستی --}}
            <div class="flex items-center gap-1.5 bg-slate-50 border border-slate-200 px-2.5 py-1 rounded-2xl text-xs">
                <span class="text-slate-400 font-bold">لە:</span>
                <input type="date" x-model="customFrom" @change="applyCustomDates()"
                       class="font-mono font-bold text-slate-800 bg-transparent focus:outline-hidden cursor-pointer text-xs">
                <span class="text-slate-400 font-bold">بۆ:</span>
                <input type="date" x-model="customTo" @change="applyCustomDates()"
                       class="font-mono font-bold text-slate-800 bg-transparent focus:outline-hidden cursor-pointer text-xs">
            </div>

            {{-- دوگمەی زیادکردنی وەستا --}}
            <button type="button" @click="openNewEmployeeModal()"
                    class="px-3.5 py-2 rounded-2xl text-xs font-black bg-emerald-600 hover:bg-emerald-700 text-white shadow-md shadow-emerald-600/25 flex items-center gap-1.5 transition-all cursor-pointer border border-emerald-500">
                <span class="text-base font-black leading-none">+</span>
                <span>زیادکردنی وەستا</span>
            </button>

            {{-- دوگمەی چاپ --}}
            <button type="button" @click="printLedger()"
                    class="px-3 py-2 rounded-2xl text-xs font-bold bg-white hover:bg-slate-100 text-slate-700 border border-slate-200 shadow-2xs flex items-center gap-1.5 transition-all cursor-pointer">
                <span>🖨️</span>
                <span>چاپکردن</span>
            </button>

            {{-- ڕێکخستن --}}
            <button type="button" @click="showSettingsModal = true"
                    class="p-2 rounded-2xl text-slate-500 hover:bg-slate-100 border border-slate-200 transition-all cursor-pointer"
                    title="ڕێکخستنی کاتی دەوام و پشوو">
                ⚙️
            </button>
        </div>
    </div>

    {{-- ٢. کارتەکانی پوختەی دارایی و ئاماری ماوەکە --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2.5 sm:gap-3.5">
        {{-- کۆی وەستاکان --}}
        <div class="bg-white rounded-2xl p-3.5 border border-slate-200 shadow-xs">
            <div class="text-[11px] font-bold text-slate-500 mb-1">کۆی وەستاکان</div>
            <div class="text-xl font-black text-slate-900 font-mono">{{ $totalEmployeesCount }} <span class="text-xs font-normal text-slate-400">کەس</span></div>
        </div>

        {{-- کۆی ڕۆژانی ئامادەبوو (سەح) --}}
        <div class="bg-emerald-50/70 rounded-2xl p-3.5 border border-emerald-200 shadow-xs">
            <div class="text-[11px] font-bold text-emerald-800 mb-1 flex items-center gap-1">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                <span>کۆی سەحی ڕۆژانە</span>
            </div>
            <div class="text-xl font-black text-emerald-700 font-mono">{{ $totalPresentManDays }} <span class="text-xs font-normal">ڕۆژ</span></div>
        </div>

        {{-- کاتی زیادە --}}
        <div class="bg-blue-50/70 rounded-2xl p-3.5 border border-blue-200 shadow-xs">
            <div class="text-[11px] font-bold text-blue-800 mb-1 flex items-center gap-1">
                <span>⏱️</span>
                <span>کۆی کاتی زیادە</span>
            </div>
            <div class="text-xl font-black text-blue-800 font-mono">{{ number_format($totalOvertimeHours, 1) }} <span class="text-xs font-normal">ک/ژ</span></div>
        </div>

        {{-- حەقدەستی شایستە --}}
        <div class="bg-indigo-50/70 rounded-2xl p-3.5 border border-indigo-200 shadow-xs">
            <div class="text-[11px] font-bold text-indigo-800 mb-1 flex items-center gap-1">
                <span>💰</span>
                <span>کۆی حەقدەست (شایستە)</span>
            </div>
            <div class="text-base sm:text-lg font-black text-indigo-950 font-mono">{{ number_format($totalEarnedAll) }} <span class="text-[10px] font-normal">د.ع</span></div>
        </div>

        {{-- پارەی دراو / پێشەکی --}}
        <div class="bg-purple-50/70 rounded-2xl p-3.5 border border-purple-200 shadow-xs">
            <div class="text-[11px] font-bold text-purple-800 mb-1 flex items-center gap-1">
                <span>💸</span>
                <span>دراوە / پێشەکی</span>
            </div>
            <div class="text-base sm:text-lg font-black text-purple-900 font-mono">{{ number_format($totalPaidAll) }} <span class="text-[10px] font-normal">د.ع</span></div>
        </div>

        {{-- باڵانسی ماوە --}}
        <div class="bg-amber-50/70 rounded-2xl p-3.5 border border-amber-200 shadow-xs">
            <div class="text-[11px] font-bold text-amber-800 mb-1 flex items-center gap-1">
                <span>⚖️</span>
                <span>باڵانسی ماوە (لای ئێمە)</span>
            </div>
            <div class="text-base sm:text-lg font-black text-amber-900 font-mono">{{ number_format($totalRemainingAll) }} <span class="text-[10px] font-normal">د.ع</span></div>
        </div>
    </div>

    {{-- ٣. جەدوەلی سەرەکی: دەفتەری سەحی ڕۆژانە بە شێوازی دەستی ماتریکس --}}
    <div class="bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden">
        
        {{-- هێڵی فلتەر و گەڕان و ڕێبەری نیشانەکان --}}
        <div class="p-3.5 sm:p-4 border-b border-slate-200 flex flex-col md:flex-row md:items-center justify-between gap-3 bg-slate-50/60">
            {{-- گەڕان بەپێی ناو --}}
            <div class="flex items-center gap-2.5 flex-1 max-w-md">
                <input type="text" x-model="searchQuery" placeholder="🔍 گەڕان بە ناوی وەستا یان پیشە..."
                       class="w-full text-xs px-3.5 py-2 rounded-xl border border-slate-200 focus:outline-hidden focus:border-indigo-500 bg-white font-medium text-right shadow-2xs">
            </div>

            {{-- ڕێبەری نیشانەکان و ڕوونکردنەوە --}}
            <div class="flex items-center gap-3 text-xs flex-wrap">
                <div class="flex items-center gap-1 text-slate-600">
                    <span class="w-5 h-5 rounded-lg bg-emerald-500 text-white flex items-center justify-center font-bold text-xs shadow-2xs">✓</span>
                    <span class="font-bold text-[11px]">ئامادە (سەح)</span>
                </div>
                <div class="flex items-center gap-1 text-slate-600">
                    <span class="w-5 h-5 rounded-lg bg-rose-500 text-white flex items-center justify-center font-bold text-xs shadow-2xs">✕</span>
                    <span class="font-bold text-[11px]">نەهاتوو (غائیب)</span>
                </div>
                <div class="flex items-center gap-1 text-slate-600">
                    <span class="w-5 h-5 rounded-lg bg-amber-400 text-white flex items-center justify-center font-bold text-xs shadow-2xs">🏖️</span>
                    <span class="font-bold text-[11px]">ئیجازە</span>
                </div>
                <div class="flex items-center gap-1 text-slate-600">
                    <span class="w-5 h-5 rounded-lg bg-slate-200 text-slate-400 flex items-center justify-center font-bold text-xs">—</span>
                    <span class="font-bold text-[11px]">تۆمارنەکراو</span>
                </div>
                <span class="text-slate-300">|</span>
                <span class="text-[11px] text-slate-400 font-medium">💡 کلیک لەسەر خانەکە بکە بۆ سەحی خێرا</span>
            </div>
        </div>

        {{-- خشتەی دەفتەری ئامادەبوون (Matrix Attendance Ledger) --}}
        <div class="overflow-x-auto print-container">
            <table class="w-full text-right text-xs border-collapse attendance-matrix-table">
                <thead>
                    <tr class="bg-slate-100/80 text-slate-700 border-b-2 border-slate-200 font-black select-none">
                        {{-- ستوونی ڕیزبەندی --}}
                        <th class="p-3 text-center w-10 border-e border-slate-200">#</th>
                        
                        {{-- ستوونی ناوی وەستا و حەمەلەکان --}}
                        <th class="p-3.5 min-w-[200px] border-e-2 border-slate-300 sticky right-0 bg-slate-100 z-10">
                            ناو و پیشەی وەستا / حەمەڵە
                        </th>

                        {{-- ستوونی ڕۆژەکانی هەفتە / مانگ بەپێی بەروار --}}
                        @foreach($days as $day)
                            <th class="p-2.5 text-center min-w-[85px] border-e border-slate-200 {{ $day['is_today'] ? 'bg-indigo-50/80 text-indigo-900 font-black' : ($day['is_holiday'] ? 'bg-amber-50/60 text-amber-900' : '') }}">
                                <div class="flex flex-col items-center justify-center gap-0.5">
                                    <span class="text-xs font-black">{{ $day['day_name'] }}</span>
                                    <span class="font-mono text-[11px] font-bold text-slate-500 dir-ltr">{{ $day['day_short'] }}</span>
                                    @if($day['is_holiday'])
                                        <span class="text-[9px] px-1 rounded bg-amber-100 text-amber-800 font-medium">پشوو</span>
                                    @endif
                                    {{-- دوگمەی سەحی هەمووان بۆ ئەم ڕۆژە --}}
                                    <button type="button" @click="batchMarkDay('{{ $day['date'] }}')"
                                            class="mt-1 px-1.5 py-0.5 rounded-md text-[10px] font-bold bg-emerald-100 hover:bg-emerald-200 text-emerald-800 transition-colors shadow-2xs"
                                            title="سەح لێدانی هەمووان بۆ ڕۆژی {{ $day['day_name'] }}">
                                        سەحی هەموو
                                    </button>
                                </div>
                            </th>
                        @endforeach

                        {{-- ستوونەکانی کۆکراوە و حیسابات --}}
                        <th class="p-3 text-center border-s-2 border-slate-300 bg-slate-100">ئامادە</th>
                        <th class="p-3 text-center border-s border-slate-200 bg-slate-100">کاتی زیادە</th>
                        <th class="p-3 text-center border-s border-slate-200 bg-slate-100">سەرفیاتی بەستن</th>
                        <th class="p-3 text-center border-s border-slate-200 bg-slate-100">حەقدەست</th>
                        <th class="p-3 text-center border-s border-slate-200 bg-emerald-50 text-emerald-950 font-black">کۆی شایستە</th>
                        <th class="p-3 text-center border-s border-slate-200 bg-purple-50 text-purple-950 font-black">دراوە</th>
                        <th class="p-3 text-center border-s border-slate-200 bg-amber-50 text-amber-950 font-black">ماوە</th>
                        <th class="p-3 text-center border-s border-slate-200 bg-slate-100">دێتەل و حیسابات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 font-medium">
                    <template x-for="(emp, idx) in filteredEmployees" :key="emp.id">
                        <tr class="hover:bg-slate-50 transition-colors group">
                            {{-- ژمارەی ڕیز --}}
                            <td class="p-3 text-center font-mono font-bold text-slate-400 border-e border-slate-200 text-xs" x-text="idx + 1"></td>

                            {{-- ناوی کارمەند + زانیاری --}}
                            <td class="p-3 border-e-2 border-slate-300 sticky right-0 bg-white group-hover:bg-slate-50 z-10 transition-colors">
                                <div class="flex items-center justify-between gap-2">
                                    <div class="flex items-center gap-2">
                                        <div class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-700 font-black flex items-center justify-center text-xs shrink-0 border border-indigo-100 shadow-2xs"
                                             x-text="emp.name.substring(0, 1)"></div>
                                        <div>
                                            <div class="font-black text-slate-900 text-xs hover:text-indigo-600 cursor-pointer"
                                                 @click="openEmployeeDetails(emp)"
                                                 x-text="emp.name"></div>
                                            <div class="text-[10px] text-slate-400 font-medium flex items-center gap-1">
                                                <span x-text="emp.job_title_label"></span>
                                                <span x-show="emp.phone" class="font-mono text-slate-400" x-text="'• ' + emp.phone"></span>
                                            </div>
                                        </div>
                                    </div>
                                    <button type="button" @click="openEditWageModal(emp)"
                                            class="opacity-0 group-hover:opacity-100 p-1 rounded hover:bg-slate-200 text-slate-500 transition-opacity text-xs"
                                            title="دەستکاری مووچە">✏️</button>
                                </div>
                            </td>

                            {{-- خانەکانی ڕۆژانی ئامادەبوون (دەفتەری سەح) --}}
                            <template x-for="day in daysList" :key="day.date">
                                <td class="p-1.5 text-center border-e border-slate-200 align-middle"
                                    :class="day.is_today ? 'bg-indigo-50/30' : (day.is_holiday ? 'bg-amber-50/20' : '')">
                                    
                                    {{-- کارتی خانە --}}
                                    <div class="flex flex-col items-center justify-center min-h-[46px] p-1 rounded-xl transition-all cursor-pointer relative"
                                         @click="toggleCell(emp.id, day.date)"
                                         :class="{
                                             'bg-emerald-500 text-white font-black shadow-xs hover:bg-emerald-600': getCell(emp, day.date)?.status === 'present',
                                             'bg-rose-500 text-white font-black shadow-xs hover:bg-rose-600': getCell(emp, day.date)?.status === 'absent',
                                             'bg-amber-400 text-white font-black shadow-xs hover:bg-amber-500': getCell(emp, day.date)?.status === 'leave',
                                             'bg-slate-100 text-slate-600 hover:bg-slate-200': getCell(emp, day.date)?.status === 'holiday',
                                             'bg-slate-50 border border-dashed border-slate-200 text-slate-300 hover:border-emerald-400 hover:text-emerald-500': !getCell(emp, day.date) || !getCell(emp, day.date)?.status
                                         }">
                                        
                                        {{-- نیشانەی دۆخ --}}
                                        <template x-if="getCell(emp, day.date)?.status === 'present'">
                                            <div class="flex flex-col items-center">
                                                <span class="text-base leading-none font-black">✓</span>
                                                {{-- کاتی زیادە یان سەرفیات ئەگەر هەبێت --}}
                                                <div class="flex items-center gap-0.5 mt-0.5 text-[9px] font-mono opacity-90">
                                                    <span x-show="getCell(emp, day.date)?.overtime_hours > 0" x-text="'+' + getCell(emp, day.date)?.overtime_hours + 'ک'"></span>
                                                    <span x-show="getCell(emp, day.date)?.fuel_expense > 0" class="text-amber-200">🚗</span>
                                                </div>
                                            </div>
                                        </template>

                                        <template x-if="getCell(emp, day.date)?.status === 'absent'">
                                            <span class="text-sm leading-none font-black">✕</span>
                                        </template>

                                        <template x-if="getCell(emp, day.date)?.status === 'leave'">
                                            <span class="text-xs leading-none">🏖️</span>
                                        </template>

                                        <template x-if="getCell(emp, day.date)?.status === 'holiday'">
                                            <span class="text-xs leading-none">☕</span>
                                        </template>

                                        <template x-if="!getCell(emp, day.date) || !getCell(emp, day.date)?.status">
                                            <span class="text-sm font-bold leading-none">—</span>
                                        </template>

                                        {{-- دوگمەی وردەکاری زیاتری ئەم خانەیە بە بچووکی --}}
                                        <button type="button" 
                                                @click.stop="openCellDetailsModal(emp, day.date)"
                                                class="absolute -top-1 -right-1 w-4 h-4 rounded-full bg-white text-slate-700 shadow-xs border border-slate-200 flex items-center justify-center text-[9px] opacity-0 group-hover:opacity-100 hover:scale-110 transition-all z-10"
                                                title="وردەکاری هاتن، دەرچوون، کاتی زیادە و بەنزین">
                                            ⚙️
                                        </button>
                                    </div>
                                </td>
                            </template>

                            {{-- کۆی ئامادەبوو (سەح) لەم ماوەیەدا --}}
                            <td class="p-3 text-center font-mono font-black text-emerald-700 border-s-2 border-slate-300 bg-slate-50/50">
                                <span class="text-sm" x-text="emp.present_count"></span> <span class="text-[10px] font-normal text-slate-400">ڕۆژ</span>
                            </td>

                            {{-- کاتی زیادە --}}
                            <td class="p-3 text-center font-mono font-bold text-blue-700 border-s border-slate-200">
                                <span x-show="emp.total_overtime > 0" x-text="emp.total_overtime + ' ک'"></span>
                                <span x-show="!emp.total_overtime" class="text-slate-300">—</span>
                            </td>

                            {{-- سەرفیاتی بەستنی سەر ماڵان --}}
                            <td class="p-3 text-center font-mono font-bold text-slate-700 border-s border-slate-200">
                                <span x-show="emp.total_fuel > 0" x-text="formatNumber(emp.total_fuel)"></span>
                                <span x-show="!emp.total_fuel" class="text-slate-300">—</span>
                            </td>

                            {{-- حەقدەستی ڕۆژانە / شێوازی پارەدان --}}
                            <td class="p-3 text-center border-s border-slate-200">
                                <div class="font-mono font-bold text-slate-900 text-xs" x-text="formatNumber(emp.daily_wage)"></div>
                                <span class="text-[10px] px-1.5 py-0.2 rounded-md font-bold"
                                      :class="emp.salary_type === 'monthly' ? 'bg-purple-50 text-purple-700' : (emp.salary_type === 'weekly' ? 'bg-blue-50 text-blue-700' : 'bg-emerald-50 text-emerald-700')"
                                      x-text="emp.salary_type_label || 'ڕۆژانە'"></span>
                            </td>

                            {{-- کۆی حەقدەستی شایستە لەم ماوەیەدا --}}
                            <td class="p-3 text-center font-mono font-black text-emerald-950 border-s border-slate-200 bg-emerald-50/40 text-xs">
                                <span x-text="formatNumber(emp.total_earned)"></span>
                            </td>

                            {{-- پارەی دراو / پێشەکی --}}
                            <td class="p-3 text-center font-mono font-bold text-purple-900 border-s border-slate-200 bg-purple-50/40 text-xs">
                                <span x-text="formatNumber(emp.total_paid)"></span>
                            </td>

                            {{-- باڵانس / ماوە --}}
                            <td class="p-3 text-center font-mono font-black border-s border-slate-200 bg-amber-50/40 text-xs"
                                :class="emp.remaining_balance > 0 ? 'text-amber-900 font-black' : (emp.remaining_balance < 0 ? 'text-rose-700 font-black' : 'text-slate-400')">
                                <span x-text="formatNumber(emp.remaining_balance)"></span>
                            </td>

                            {{-- دوگمەی دێتەل و حیسابات --}}
                            <td class="p-3 text-center border-s border-slate-200 bg-slate-50/50">
                                <button type="button" @click="openEmployeeDetails(emp)"
                                        class="px-3 py-1.5 rounded-xl text-xs font-bold bg-indigo-600 hover:bg-indigo-700 text-white shadow-xs transition-all cursor-pointer flex items-center gap-1 mx-auto">
                                    <span>📁</span>
                                    <span>دێتەل و حیسابات</span>
                                </button>
                            </td>
                        </tr>
                    </template>
                </tbody>

                {{-- هێڵی خوارەوەی جەدوەل: کۆی کارمەندانی ئامادەبوو بۆ هەر ڕۆژێک (وەک دەفتەرە دەستییەکە) --}}
                <tfoot>
                    <tr class="bg-slate-900 text-white font-black border-t-2 border-slate-800 text-xs select-none">
                        <td class="p-3 text-center border-e border-slate-800">Σ</td>
                        <td class="p-3.5 border-e-2 border-slate-800 sticky right-0 bg-slate-900 z-10">
                            کۆی کارمەندانی ئامادەبوو
                        </td>

                        {{-- ژماردنی ئامادەبووانی هەر ڕۆژێک بە جیا --}}
                        @foreach($days as $day)
                            <td class="p-2.5 text-center border-e border-slate-800">
                                <div class="flex flex-col items-center justify-center">
                                    <span class="font-mono text-sm font-black text-emerald-400"
                                          x-text="getDayPresentCount('{{ $day['date'] }}')"></span>
                                    <span class="text-[9px] text-slate-300 font-normal">کارمەند</span>
                                </div>
                            </td>
                        @endforeach

                        {{-- کۆی گشتی هەموو ئامادەبوونەکان --}}
                        <td class="p-3 text-center font-mono font-black text-emerald-400 border-s-2 border-slate-800">
                            <span x-text="getTotalPresentAll()"></span> <span class="text-[10px] font-normal text-slate-400">ڕۆژ</span>
                        </td>
                        <td class="p-3 text-center font-mono font-bold text-blue-300 border-s border-slate-800">
                            <span x-text="getTotalOvertimeAll() + ' ک'"></span>
                        </td>
                        <td class="p-3 text-center font-mono font-bold text-slate-300 border-s border-slate-800">
                            <span x-text="formatNumber(getTotalFuelAll())"></span>
                        </td>
                        <td class="p-3 text-center text-slate-400 border-s border-slate-800">—</td>
                        <td class="p-3 text-center font-mono font-black text-emerald-400 border-s border-slate-800 text-xs">
                            <span x-text="formatNumber(getTotalEarnedAll())"></span>
                        </td>
                        <td class="p-3 text-center font-mono font-black text-purple-300 border-s border-slate-800 text-xs">
                            <span x-text="formatNumber(getTotalPaidAll())"></span>
                        </td>
                        <td class="p-3 text-center font-mono font-black text-amber-300 border-s border-slate-800 text-xs">
                            <span x-text="formatNumber(getTotalRemainingAll())"></span>
                        </td>
                        <td class="p-3 text-center text-slate-400 border-s border-slate-800">—</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- ================= موداڵ و دراوەری دێتەلی وەستا و حیساباتی دارایی ================= --}}

    {{-- ١. مۆداڵی تەواوی دێتەلی وەستا، حیساباتی هەفتانە/مانگانە، و پێشەکی --}}
    <div x-show="showEmployeeDrawer" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-3 sm:p-5 overflow-y-auto"
         @click.self="showEmployeeDrawer = false">
        <div class="w-full max-w-4xl bg-white rounded-3xl border border-slate-200 shadow-2xl overflow-hidden animate-in fade-in zoom-in-95 my-auto max-h-[90vh] flex flex-col">
            
            {{-- سەرپەڕەی مۆداڵ --}}
            <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4 bg-slate-50 shrink-0">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-indigo-600 text-white font-black flex items-center justify-center text-base shadow-xs"
                         x-text="activeEmployee?.name?.substring(0, 1)"></div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="font-black text-base text-slate-900" x-text="activeEmployee?.name"></h3>
                            <span class="px-2 py-0.5 rounded-md text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200"
                                  x-text="activeEmployee?.job_title_label"></span>
                        </div>
                        <p class="text-xs text-slate-400 font-medium">
                            کشف حساب و حیساباتی وردی ماوەی <b class="font-mono text-slate-700" x-text="'{{ $from }} تا {{ $to }}'"></b>
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <button type="button" @click="printEmployeeStatement()"
                            class="px-3 py-1.5 rounded-xl text-xs font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 flex items-center gap-1 cursor-pointer">
                        <span>🖨️</span>
                        <span>چاپکردنی کشف حساب</span>
                    </button>
                    <button type="button" @click="showEmployeeDrawer = false" class="w-8 h-8 rounded-xl text-slate-400 hover:bg-slate-200 text-base font-bold flex items-center justify-center">✕</button>
                </div>
            </div>

            {{-- ناوەڕۆکی دێتەل بە سکرۆڵ --}}
            <div class="p-6 overflow-y-auto space-y-6 text-xs flex-1">
                
                {{-- کارتی خولاسەی دارایی کارمەند لەم ماوەیەدا --}}
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    <div class="bg-slate-50 rounded-2xl p-3.5 border border-slate-200">
                        <div class="text-[11px] font-bold text-slate-500 mb-1">ڕۆژانی ئامادەبوو</div>
                        <div class="text-lg font-black text-slate-900 font-mono">
                            <span x-text="activeEmployee?.present_count || 0"></span> <span class="text-xs font-normal text-slate-400">ڕۆژ</span>
                        </div>
                        <div class="text-[10px] text-slate-400 mt-1" x-text="'حەقدەست: ' + formatNumber(activeEmployee?.daily_wage) + ' د.ع'"></div>
                    </div>

                    <div class="bg-emerald-50 rounded-2xl p-3.5 border border-emerald-200">
                        <div class="text-[11px] font-bold text-emerald-800 mb-1">کۆی شایستەی ماوەکە</div>
                        <div class="text-lg font-black text-emerald-950 font-mono">
                            <span x-text="formatNumber(activeEmployee?.total_earned)"></span> <span class="text-xs font-normal">د.ع</span>
                        </div>
                        <div class="text-[10px] text-emerald-700 mt-1" x-text="'کاتی زیادە: ' + (activeEmployee?.total_overtime || 0) + ' ک/ژ'"></div>
                    </div>

                    <div class="bg-purple-50 rounded-2xl p-3.5 border border-purple-200">
                        <div class="text-[11px] font-bold text-purple-800 mb-1">کۆی پێشەکی و دراو</div>
                        <div class="text-lg font-black text-purple-900 font-mono">
                            <span x-text="formatNumber(activeEmployee?.total_paid)"></span> <span class="text-xs font-normal">د.ع</span>
                        </div>
                        <div class="text-[10px] text-purple-700 mt-1" x-text="(activeEmployee?.payments?.length || 0) + ' جار پارەی وەرگرتووە'"></div>
                    </div>

                    <div class="bg-amber-50 rounded-2xl p-3.5 border border-amber-200">
                        <div class="text-[11px] font-bold text-amber-800 mb-1">باڵانسی ماوە (شایستە)</div>
                        <div class="text-lg font-black text-amber-950 font-mono">
                            <span x-text="formatNumber(activeEmployee?.remaining_balance)"></span> <span class="text-xs font-normal">د.ع</span>
                        </div>
                        <div class="text-[10px] text-amber-800 mt-1">ئەوەی لای کارگە ماوەتەوە</div>
                    </div>
                </div>

                {{-- بەشی پێدانی پێشەکی و دانەوەی مووچە (Quick Payment Form) --}}
                <div class="bg-indigo-50/50 rounded-3xl p-4.5 border border-indigo-100 space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="font-black text-indigo-950 text-xs flex items-center gap-1.5">
                            <span>💸</span>
                            <span>تۆمارکردنی پێشەکی یان دانی حەقدەست (سەرفکردنی پارە)</span>
                        </div>
                        <span class="text-[11px] text-indigo-600 font-bold">ڕاستەوخۆ لە قاصە دەبڕدرێت</span>
                    </div>

                    <form @submit.prevent="submitEmployeePayment()" class="grid grid-cols-1 sm:grid-cols-4 gap-2.5 items-end">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">بڕی پارە (دینار) *</label>
                            <input type="number" step="any" min="1" x-model="paymentForm.amount" required placeholder="50000"
                                   class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-white font-mono font-black text-slate-900 focus:outline-hidden focus:border-indigo-500 text-left" dir="ltr">
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 mb-1">بەروار *</label>
                            <input type="date" x-model="paymentForm.paid_at" required
                                   class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-white font-mono font-bold text-slate-800 focus:outline-hidden focus:border-indigo-500">
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 mb-1">تێبینی / هۆکار</label>
                            <input type="text" x-model="paymentForm.note" placeholder="پێشەکی، پاکتاوی هەفتانە..."
                                   class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-white focus:outline-hidden focus:border-indigo-500">
                        </div>

                        <div>
                            <button type="submit" 
                                    :disabled="isSubmittingPayment"
                                    class="w-full py-2.5 rounded-xl font-black bg-emerald-600 hover:bg-emerald-700 text-white shadow-xs transition-all disabled:opacity-50 flex items-center justify-center gap-1 cursor-pointer">
                                <span x-show="isSubmittingPayment" class="animate-spin">⏳</span>
                                <span x-text="isSubmittingPayment ? 'تۆمار دەکرێت...' : '💾 پێدانی پارە'"></span>
                            </button>
                        </div>
                    </form>
                </div>

                {{-- جەدوەلی ڕۆژانەی کارمەند لەم ماوەیەدا --}}
                <div>
                    <h4 class="font-black text-slate-900 text-xs mb-2.5 flex items-center gap-1.5">
                        <span>📅</span>
                        <span>تۆماری ئامادەبوونی ڕۆژ بە ڕۆژ</span>
                    </h4>

                    <div class="border border-slate-200 rounded-2xl overflow-hidden">
                        <table class="w-full text-right text-xs">
                            <thead class="bg-slate-50 text-slate-600 font-black border-b border-slate-200">
                                <tr>
                                    <th class="p-2.5">بەروار و ڕۆژ</th>
                                    <th class="p-2.5 text-center">دۆخی سەح</th>
                                    <th class="p-2.5 text-center">هاتن</th>
                                    <th class="p-2.5 text-center">دەرچوون</th>
                                    <th class="p-2.5 text-center">کاتی زیادە</th>
                                    <th class="p-2.5 text-center">سەرفیاتی بەستن</th>
                                    <th class="p-2.5">تێبینی / شوێن</th>
                                    <th class="p-2.5 text-center">دەستکاری</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <template x-for="day in daysList" :key="day.date">
                                    <tr class="hover:bg-slate-50 transition-colors"
                                        :class="day.is_today ? 'bg-indigo-50/20' : ''">
                                        <td class="p-2.5 font-bold">
                                            <span x-text="day.day_name"></span>
                                            <span class="text-slate-400 font-mono text-[11px]" x-text="' (' + day.date + ')'"></span>
                                        </td>
                                        <td class="p-2.5 text-center">
                                            <template x-if="getCell(activeEmployee, day.date)?.status === 'present'">
                                                <span class="px-2 py-0.5 rounded-md font-bold bg-emerald-100 text-emerald-800 text-[11px]">ئامادە ✔️</span>
                                            </template>
                                            <template x-if="getCell(activeEmployee, day.date)?.status === 'absent'">
                                                <span class="px-2 py-0.5 rounded-md font-bold bg-rose-100 text-rose-800 text-[11px]">نەهاتوو ❌</span>
                                            </template>
                                            <template x-if="getCell(activeEmployee, day.date)?.status === 'leave'">
                                                <span class="px-2 py-0.5 rounded-md font-bold bg-amber-100 text-amber-800 text-[11px]">ئیجازە 🏖️</span>
                                            </template>
                                            <template x-if="getCell(activeEmployee, day.date)?.status === 'holiday'">
                                                <span class="px-2 py-0.5 rounded-md font-bold bg-slate-100 text-slate-600 text-[11px]">پشوو ☕</span>
                                            </template>
                                            <template x-if="!getCell(activeEmployee, day.date) || !getCell(activeEmployee, day.date)?.status">
                                                <span class="text-slate-300 font-mono">—</span>
                                            </template>
                                        </td>
                                        <td class="p-2.5 text-center font-mono text-slate-700" x-text="getCell(activeEmployee, day.date)?.check_in || '—'"></td>
                                        <td class="p-2.5 text-center font-mono text-slate-700" x-text="getCell(activeEmployee, day.date)?.check_out || '—'"></td>
                                        <td class="p-2.5 text-center font-mono font-bold text-blue-700" x-text="getCell(activeEmployee, day.date)?.overtime_hours ? '+' + getCell(activeEmployee, day.date)?.overtime_hours + ' ک' : '—'"></td>
                                        <td class="p-2.5 text-center font-mono font-bold text-slate-700" x-text="getCell(activeEmployee, day.date)?.fuel_expense ? formatNumber(getCell(activeEmployee, day.date)?.fuel_expense) + ' د.ع' : '—'"></td>
                                        <td class="p-2.5 text-slate-500" x-text="getCell(activeEmployee, day.date)?.trip_destination || getCell(activeEmployee, day.date)?.note || '—'"></td>
                                        <td class="p-2.5 text-center">
                                            <button type="button" @click="openCellDetailsModal(activeEmployee, day.date)"
                                                    class="p-1 rounded hover:bg-slate-100 text-indigo-600 font-bold" title="دەستکاری وردەکاری">
                                                ✏️
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- لیستی پێشەکی و پارە وەرگیراوەکانی پێشوو --}}
                <div x-show="activeEmployee?.payments?.length > 0">
                    <h4 class="font-black text-slate-900 text-xs mb-2.5 flex items-center gap-1.5">
                        <span>🧾</span>
                        <span>مێژووی پارەدان و پێشەکییەکان لەم ماوەیەدا</span>
                    </h4>

                    <div class="border border-slate-200 rounded-2xl overflow-hidden">
                        <table class="w-full text-right text-xs">
                            <thead class="bg-slate-50 text-slate-600 font-black border-b border-slate-200">
                                <tr>
                                    <th class="p-2.5">ژمارەی وەصڵ</th>
                                    <th class="p-2.5">بەروار</th>
                                    <th class="p-2.5 text-center">بڕی پارە</th>
                                    <th class="p-2.5">تێبینی</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 font-mono">
                                <template x-for="p in activeEmployee?.payments" :key="p.id">
                                    <tr class="hover:bg-slate-50">
                                        <td class="p-2.5 font-bold text-indigo-700" x-text="p.voucher_no"></td>
                                        <td class="p-2.5 text-slate-600" x-text="p.paid_at"></td>
                                        <td class="p-2.5 text-center font-black text-purple-900" x-text="formatNumber(p.amount) + ' ' + p.currency"></td>
                                        <td class="p-2.5 font-sans text-slate-500" x-text="p.note || '—'"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- ٢. مۆداڵی وردەکاری ئامادەبوونی خانەیەک (هاتن، دەرچوون، کاتی زیادە، بەنزین) --}}
    <div x-show="showCellModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4"
         @click.self="showCellModal = false">
        <div class="w-full max-w-lg bg-white rounded-3xl border border-slate-200 shadow-2xl overflow-hidden animate-in fade-in zoom-in-95">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4 bg-slate-50">
                <div class="flex items-center gap-2">
                    <span class="text-lg">📝</span>
                    <div>
                        <h3 class="font-black text-sm text-slate-800" x-text="'تۆماری ئامادەبوونی ' + (selectedEmployee?.name || '')"></h3>
                        <p class="text-[11px] text-slate-400 font-medium" x-text="'ڕۆژی بەرواری: ' + cellForm.work_date"></p>
                    </div>
                </div>
                <button type="button" @click="showCellModal = false" class="w-7 h-7 rounded-lg text-slate-400 hover:bg-slate-200 text-sm font-bold">✕</button>
            </div>

            <form @submit.prevent="saveCellDetails()" class="p-5 space-y-3.5 text-xs">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">دۆخی ئامادەبوون</label>
                        <select x-model="cellForm.status" class="w-full px-3 py-2 rounded-xl border border-slate-200 font-bold">
                            <option value="present">ئامادەیە ✔️</option>
                            <option value="absent">نەهاتووە ❌</option>
                            <option value="leave">ئیجازە 🏖️</option>
                            <option value="holiday">پشوو ☕</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">کاتی زیادە (کاتژمێر)</label>
                        <input type="number" step="0.5" min="0" x-model="cellForm.overtime_hours" placeholder="0"
                               class="w-full px-3 py-2 rounded-xl border border-slate-200 font-mono font-bold text-blue-700">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">کاتی هاتن (Check-in)</label>
                        <input type="time" x-model="cellForm.check_in"
                               class="w-full px-3 py-2 rounded-xl border border-slate-200 font-mono font-bold">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">کاتی دەرچوون (Check-out)</label>
                        <input type="time" x-model="cellForm.check_out"
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
                            <input type="number" step="any" min="0" x-model="cellForm.fuel_expense" placeholder="0"
                                   class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-white font-mono font-bold text-indigo-700">
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 mb-1">شوێنی بەستن / ناوی ماڵی کڕیار</label>
                            <input type="text" x-model="cellForm.trip_destination" placeholder="گەڕەک یان ناوی کڕیار..."
                                   class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-white">
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">دەرچوونی کاتی (کاتژمێر و هۆکار)</label>
                    <div class="grid grid-cols-3 gap-2">
                        <input type="number" step="0.5" min="0" x-model="cellForm.temporary_exit_hours" placeholder="کاتژمێر..."
                               class="px-3 py-2 rounded-xl border border-slate-200 font-mono font-bold">
                        <input type="text" x-model="cellForm.exit_reason" placeholder="هۆکاری دەرچوون..."
                               class="col-span-2 px-3 py-2 rounded-xl border border-slate-200">
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">تێبینی</label>
                    <input type="text" x-model="cellForm.note" placeholder="تێبینی زیادە بنووسە..."
                           class="w-full px-3 py-2 rounded-xl border border-slate-200">
                </div>

                <div class="flex items-center justify-between pt-3 border-t border-slate-100">
                    <button type="button" @click="deleteAttendance(cellForm.employee_id, cellForm.work_date)"
                            class="px-3 py-2 rounded-xl text-xs font-bold text-rose-600 hover:bg-rose-50 border border-rose-200 transition-colors">
                        سڕینەوەی ئەم تۆمارە
                    </button>

                    <div class="flex items-center gap-2">
                        <button type="button" @click="showCellModal = false" class="btn btn-ghost !py-2 !px-4 text-xs font-bold text-slate-600 bg-slate-100 hover:bg-slate-200">داخستن</button>
                        <button type="submit" 
                                :disabled="isSavingCell"
                                class="btn btn-primary !py-2 !px-5 text-xs font-bold transition-all disabled:opacity-50 flex items-center gap-1.5 cursor-pointer" 
                                style="background-color: #2563eb !important; color: #ffffff !important;">
                            <span x-show="isSavingCell" class="inline-block animate-spin">⏳</span>
                            <span x-text="isSavingCell ? 'پاشەکەوت دەکرێت...' : 'پاشەکەوتکردن'"></span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- ٣. مۆداڵی زیادکردنی وەستا یان کارمەندی نوێ --}}
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
                    <button type="submit" 
                            :disabled="isSavingEmployee"
                            class="btn btn-primary !py-2 !px-5 text-xs font-bold transition-all disabled:opacity-50 flex items-center gap-1.5 cursor-pointer" 
                            style="background-color: #059669 !important; color: #ffffff !important;">
                        <span x-show="isSavingEmployee" class="inline-block animate-spin">⏳</span>
                        <span x-text="isSavingEmployee ? 'پاشەکەوت دەکرێت...' : '💾 پاشەکەوتکردن و زیادکردن'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ٤. مۆداڵی دەستکاری مووچە و زانیاری کارمەند --}}
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
                    <button type="submit" 
                            :disabled="isUpdatingWage"
                            class="btn btn-primary !py-2 !px-5 text-xs font-bold transition-all disabled:opacity-50 flex items-center gap-1.5 cursor-pointer" 
                            style="background-color: #2563eb !important; color: #ffffff !important;">
                        <span x-show="isUpdatingWage" class="inline-block animate-spin">⏳</span>
                        <span x-text="isUpdatingWage ? 'نوێ دەکرێتەوە...' : 'نوێکردنەوەی مووچە'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ٥. مۆداڵی ڕێکخستنی کاتی دەوام و پشوو --}}
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

</div>

{{-- ستایلی تایبەت بە چاپکردن (Print Stylesheet) وەک دەفتەری کارگە --}}
<style>
@media print {
    body * {
        visibility: hidden;
    }
    .print-container, .print-container * {
        visibility: visible;
    }
    .print-container {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
    }
    @page {
        size: landscape;
        margin: 10mm;
    }
}
</style>

<script>
function workshopEmployeesMatrixApp() {
    return {
        from: '{{ $from }}',
        to: '{{ $to }}',
        rangeType: '{{ $rangeType }}',
        customFrom: '{{ $from }}',
        customTo: '{{ $to }}',
        searchQuery: '',
        
        daysList: @json($days, JSON_UNESCAPED_UNICODE),
        employeesList: @json($employeesMatrix, JSON_UNESCAPED_UNICODE),
        dayTotalsMap: @json($dayTotals, JSON_UNESCAPED_UNICODE),

        showEmployeeDrawer: false,
        showCellModal: false,
        showNewEmployeeModal: false,
        showEditWageModal: false,
        showSettingsModal: false,

        activeEmployee: null,
        selectedEmployee: null,

        newEmployeeCustomJob: false,
        editWageCustomJob: false,

        isSavingEmployee: false,
        isUpdatingWage: false,
        isSavingCell: false,
        isSubmittingPayment: false,

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

        cellForm: {
            employee_id: null,
            work_date: '',
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

        paymentForm: {
            employee_id: null,
            amount: '',
            currency: 'IQD',
            paid_at: '{{ now()->toDateString() }}',
            note: ''
        },

        init() {
            // دەستپێکردن
        },

        setRange(type) {
            window.location.href = `{{ route('workshop.employees') }}?range_type=${type}`;
        },

        applyCustomDates() {
            if (this.customFrom && this.customTo) {
                window.location.href = `{{ route('workshop.employees') }}?from=${this.customFrom}&to=${this.customTo}`;
            }
        },

        get filteredEmployees() {
            const q = (this.searchQuery || '').toLowerCase().trim();
            if (!q) return this.employeesList;
            return this.employeesList.filter(emp => {
                return emp.name.toLowerCase().includes(q) ||
                       (emp.phone && emp.phone.includes(q)) ||
                       (emp.job_title_label && emp.job_title_label.toLowerCase().includes(q));
            });
        },

        getCell(emp, dateStr) {
            if (!emp || !emp.cells) return null;
            return emp.cells[dateStr] || null;
        },

        getDayPresentCount(dateStr) {
            let count = 0;
            this.employeesList.forEach(emp => {
                if (emp.cells && emp.cells[dateStr] && emp.cells[dateStr].status === 'present') {
                    count++;
                }
            });
            return count;
        },

        getTotalPresentAll() {
            return this.employeesList.reduce((sum, emp) => sum + (emp.present_count || 0), 0);
        },

        getTotalOvertimeAll() {
            return this.employeesList.reduce((sum, emp) => sum + (emp.total_overtime || 0), 0).toFixed(1);
        },

        getTotalFuelAll() {
            return this.employeesList.reduce((sum, emp) => sum + (emp.total_fuel || 0), 0);
        },

        getTotalEarnedAll() {
            return this.employeesList.reduce((sum, emp) => sum + (emp.total_earned || 0), 0);
        },

        getTotalPaidAll() {
            return this.employeesList.reduce((sum, emp) => sum + (emp.total_paid || 0), 0);
        },

        getTotalRemainingAll() {
            return this.employeesList.reduce((sum, emp) => sum + (emp.remaining_balance || 0), 0);
        },

        formatNumber(num) {
            return Number(num || 0).toLocaleString();
        },

        async toggleCell(employeeId, dateStr) {
            const emp = this.employeesList.find(e => e.id === employeeId);
            if (!emp) return;

            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            try {
                const res = await fetch('{{ route('workshop.employees.toggle-cell') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        employee_id: employeeId,
                        work_date: dateStr
                    })
                });
                const data = await res.json().catch(() => ({}));
                if (res.ok && data.ok) {
                    if (!emp.cells) emp.cells = {};
                    emp.cells[dateStr] = data.attendance;
                    this.recalculateEmployeeStats(emp);
                } else {
                    alert(data.message || 'هەڵەیەک ڕوویدا.');
                }
            } catch (e) {
                alert('هەڵەی پەیوەندی بە سێرڤەرەوە.');
            }
        },

        async batchMarkDay(dateStr) {
            if (!confirm(`ئایا دڵنیایت دەتەوێت هەموو کارمەندان بە ئامادەبوو (سەح) تۆمار بکەیت بۆ بەرواری ${dateStr}؟`)) {
                return;
            }

            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            try {
                const res = await fetch('{{ route('workshop.employees.batch-mark-day') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        work_date: dateStr,
                        status: 'present'
                    })
                });
                const data = await res.json().catch(() => ({}));
                if (res.ok && data.ok) {
                    window.location.reload();
                } else {
                    alert(data.message || 'هەڵەیەک ڕوویدا.');
                }
            } catch (e) {
                alert('هەڵەی پەیوەندی.');
            }
        },

        recalculateEmployeeStats(emp) {
            let present = 0, overtime = 0, fuel = 0;
            if (emp.cells) {
                Object.values(emp.cells).forEach(cell => {
                    if (cell && cell.status === 'present') {
                        present++;
                        overtime += parseFloat(cell.overtime_hours || 0);
                        fuel += parseFloat(cell.fuel_expense || 0);
                    }
                });
            }
            emp.present_count = present;
            emp.total_overtime = overtime;
            emp.total_fuel = fuel;

            const effectiveWage = emp.effective_daily_wage || emp.daily_wage || 0;
            const baseEarned = present * effectiveWage;
            const hourlyWage = effectiveWage / 8;
            const otEarned = overtime * hourlyWage * 1.0;
            emp.total_earned = Math.round(baseEarned + otEarned + fuel);
            emp.remaining_balance = Math.round(emp.total_earned - (emp.total_paid || 0));
        },

        openEmployeeDetails(emp) {
            this.activeEmployee = emp;
            this.paymentForm = {
                employee_id: emp.id,
                amount: '',
                currency: emp.wage_currency || 'IQD',
                paid_at: '{{ now()->toDateString() }}',
                note: ''
            };
            this.showEmployeeDrawer = true;
        },

        openCellDetailsModal(emp, dateStr) {
            this.selectedEmployee = emp;
            const cell = this.getCell(emp, dateStr);
            this.cellForm = {
                employee_id: emp.id,
                work_date: dateStr,
                status: cell ? cell.status : 'present',
                check_in: cell ? cell.check_in : '',
                check_out: cell ? cell.check_out : '',
                overtime_hours: cell ? cell.overtime_hours : 0,
                temporary_exit_hours: cell ? cell.temporary_exit_hours : 0,
                exit_reason: cell ? cell.exit_reason : '',
                fuel_expense: cell ? cell.fuel_expense : 0,
                trip_destination: cell ? cell.trip_destination : '',
                note: cell ? cell.note : ''
            };
            this.showCellModal = true;
        },

        async saveCellDetails() {
            if (this.isSavingCell) return;
            this.isSavingCell = true;
            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                const res = await fetch('{{ route('attendance.record-single') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(this.cellForm)
                });
                const data = await res.json().catch(() => ({}));
                if (res.ok && data.ok) {
                    const emp = this.employeesList.find(e => e.id === this.cellForm.employee_id);
                    if (emp) {
                        if (!emp.cells) emp.cells = {};
                        emp.cells[this.cellForm.work_date] = {
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
                        this.recalculateEmployeeStats(emp);
                    }
                    this.showCellModal = false;
                } else {
                    alert(data.message || 'هەڵەیەک ڕوویدا.');
                }
            } catch (e) {
                alert('هەڵەی پەیوەندی بە سێرڤەرەوە.');
            } finally {
                this.isSavingCell = false;
            }
        },

        async deleteAttendance(employeeId, dateStr) {
            if (!confirm('ئایا دڵنیایت دەتەوێت تۆماری ئامادەبوونی ئەم ڕۆژە بسڕیتەوە؟')) return;
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            try {
                const res = await fetch('{{ route('workshop.employees.toggle-cell') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        employee_id: employeeId,
                        work_date: dateStr,
                        status: 'delete'
                    })
                });
                const data = await res.json().catch(() => ({}));
                if (res.ok && data.ok) {
                    const emp = this.employeesList.find(e => e.id === employeeId);
                    if (emp && emp.cells) {
                        emp.cells[dateStr] = null;
                        this.recalculateEmployeeStats(emp);
                    }
                    this.showCellModal = false;
                }
            } catch (e) {
                alert('هەڵە لە سڕینەوەدا.');
            }
        },

        async submitEmployeePayment() {
            if (this.isSubmittingPayment) return;
            this.isSubmittingPayment = true;
            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                const res = await fetch('{{ route('workshop.employees.record-payment') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(this.paymentForm)
                });
                const data = await res.json().catch(() => ({}));
                if (res.ok && data.ok) {
                    const emp = this.employeesList.find(e => e.id === this.paymentForm.employee_id);
                    if (emp) {
                        if (!emp.payments) emp.payments = [];
                        emp.payments.unshift(data.payment);
                        emp.total_paid = (parseFloat(emp.total_paid || 0) + parseFloat(data.payment.amount_iqd));
                        emp.remaining_balance = Math.round(emp.total_earned - emp.total_paid);
                    }
                    this.paymentForm.amount = '';
                    this.paymentForm.note = '';
                    alert(data.message || 'پارەدان بە سەرکەوتوویی تۆمارکرا.');
                } else {
                    alert(data.message || 'هەڵە لە تۆمارکردنی پارەدان.');
                }
            } catch (e) {
                alert('هەڵەی پەیوەندی بە سێرڤەرەوە.');
            } finally {
                this.isSubmittingPayment = false;
            }
        },

        openNewEmployeeModal() {
            this.newEmployeeForm = {
                name: '',
                phone: '',
                job_title: 'master',
                salary_type: 'daily',
                daily_wage: '',
                wage_currency: 'IQD',
                note: ''
            };
            this.newEmployeeCustomJob = false;
            this.isSavingEmployee = false;
            this.showNewEmployeeModal = true;
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
                wage_currency: emp.wage_currency || 'IQD'
            };
            this.showEditWageModal = true;
        },

        async storeEmployee() {
            if (this.isSavingEmployee) return;
            this.isSavingEmployee = true;
            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                const res = await fetch('{{ route('workshop.employees.quick-store') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(this.newEmployeeForm)
                });
                const data = await res.json().catch(() => ({}));
                if (res.ok && data.ok) {
                    this.showNewEmployeeModal = false;
                    window.location.reload();
                } else {
                    alert(data.message || 'هەڵەیەک ڕوویدا لە کاتی پاشەکەوتکردندا.');
                }
            } catch (e) {
                alert('هەڵەی پەیوەندی.');
            } finally {
                this.isSavingEmployee = false;
            }
        },

        async updateWage() {
            if (this.isUpdatingWage) return;
            this.isUpdatingWage = true;
            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                const res = await fetch(`/workshop/employees/${this.editWageForm.id}/update-wage`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(this.editWageForm)
                });
                const data = await res.json().catch(() => ({}));
                if (res.ok && data.ok) {
                    const emp = this.employeesList.find(e => e.id === this.editWageForm.id);
                    if (emp) {
                        emp.name = this.editWageForm.name;
                        emp.phone = this.editWageForm.phone;
                        emp.job_title = data.job_title || this.editWageForm.job_title;
                        emp.job_title_label = data.job_title_label || this.editWageForm.job_title;
                        emp.salary_type = data.salary_type || this.editWageForm.salary_type;
                        emp.salary_type_label = data.salary_type_label || (emp.salary_type === 'monthly' ? 'مانگانە' : (emp.salary_type === 'weekly' ? 'حەفتانە' : 'ڕۆژانە'));
                        emp.daily_wage = parseFloat(this.editWageForm.daily_wage);
                        this.recalculateEmployeeStats(emp);
                    }
                    this.showEditWageModal = false;
                } else {
                    alert(data.message || 'هەڵە لە نوێکردنەوەدا.');
                }
            } catch (e) {
                alert('هەڵەی پەیوەندی.');
            } finally {
                this.isUpdatingWage = false;
            }
        },

        async saveSettings() {
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            try {
                const res = await fetch('{{ route('workshop.settings') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(this.settingsForm)
                });
                const data = await res.json().catch(() => ({}));
                if (res.ok && data.ok) {
                    this.showSettingsModal = false;
                    window.location.reload();
                } else {
                    alert(data.message || 'هەڵەیەک ڕوویدا.');
                }
            } catch (e) {
                alert('هەڵەی پەیوەندی.');
            }
        },

        printLedger() {
            window.print();
        },

        printEmployeeStatement() {
            if (!this.activeEmployee) return;
            const w = window.open('', '_blank');
            const emp = this.activeEmployee;
            const daysRows = this.daysList.map(d => {
                const cell = this.getCell(emp, d.date);
                const statusTxt = cell?.status === 'present' ? 'ئامادە ✔️' : (cell?.status === 'absent' ? 'غائیب ❌' : (cell?.status === 'leave' ? 'ئیجازە' : '—'));
                return `
                    <tr>
                        <td style="padding: 6px; border: 1px solid #e2e8f0; text-align: right;">${d.day_name} (${d.date})</td>
                        <td style="padding: 6px; border: 1px solid #e2e8f0; text-align: center; font-weight: bold;">${statusTxt}</td>
                        <td style="padding: 6px; border: 1px solid #e2e8f0; text-align: center;">${cell?.check_in || '—'} - ${cell?.check_out || '—'}</td>
                        <td style="padding: 6px; border: 1px solid #e2e8f0; text-align: center;">${cell?.overtime_hours ? '+' + cell.overtime_hours : '—'}</td>
                        <td style="padding: 6px; border: 1px solid #e2e8f0; text-align: center;">${cell?.fuel_expense ? Number(cell.fuel_expense).toLocaleString() : '—'}</td>
                        <td style="padding: 6px; border: 1px solid #e2e8f0; text-align: right;">${cell?.trip_destination || cell?.note || '—'}</td>
                    </tr>
                `;
            }).join('');

            w.document.write(`
                <!DOCTYPE html>
                <html dir="rtl" lang="ckb">
                <head>
                    <meta charset="utf-8">
                    <title>کشف حسابی ${emp.name}</title>
                    <style>
                        body { font-family: system-ui, -apple-system, sans-serif; padding: 25px; color: #0f172a; }
                        table { width: 100%; border-collapse: collapse; margin-top: 15px; font-size: 13px; }
                        th { background: #f1f5f9; padding: 8px; border: 1px solid #cbd5e1; font-weight: bold; text-align: right; }
                        .summary-box { display: flex; gap: 15px; margin: 15px 0; }
                        .stat { flex: 1; border: 1px solid #cbd5e1; border-radius: 8px; padding: 10px; text-align: center; }
                        .stat .val { font-size: 18px; font-weight: 900; margin-top: 4px; font-family: monospace; }
                        .header { display: flex; justify-content: space-between; border-bottom: 2px solid #0f172a; padding-bottom: 12px; }
                    </style>
                </head>
                <body>
                    <div class="header">
                        <div>
                            <h2 style="margin: 0;">کشف حسابی کارمەند / وەستا</h2>
                            <p style="margin: 5px 0 0 0; color: #64748b; font-size: 13px;">کارگەی ئاسنگەری هێمن</p>
                        </div>
                        <div style="text-align: left;">
                            <div style="font-size: 16px; font-weight: 900;">${emp.name}</div>
                            <div style="font-size: 12px; color: #64748b;">${emp.job_title_label} | ${emp.phone || ''}</div>
                            <div style="font-size: 11px; color: #94a3b8; margin-top: 4px;">ماوە: ${this.from} تا ${this.to}</div>
                        </div>
                    </div>

                    <div class="summary-box">
                        <div class="stat">
                            <div style="font-size: 11px; color: #64748b;">ڕۆژانی ئامادە</div>
                            <div class="val" style="color: #059669;">${emp.present_count || 0} ڕۆژ</div>
                        </div>
                        <div class="stat">
                            <div style="font-size: 11px; color: #64748b;">کۆی شایستە</div>
                            <div class="val" style="color: #1e1b4b;">${Number(emp.total_earned).toLocaleString()} د.ع</div>
                        </div>
                        <div class="stat">
                            <div style="font-size: 11px; color: #64748b;">پێشەکی و دراو</div>
                            <div class="val" style="color: #7c2d12;">${Number(emp.total_paid).toLocaleString()} د.ع</div>
                        </div>
                        <div class="stat" style="background: #fef3c7;">
                            <div style="font-size: 11px; color: #92400e;">باڵانسی ماوە</div>
                            <div class="val" style="color: #b45309;">${Number(emp.remaining_balance).toLocaleString()} د.ع</div>
                        </div>
                    </div>

                    <h4 style="margin: 20px 0 5px 0;">تۆماری ئامادەبوونی ڕۆژانە</h4>
                    <table>
                        <thead>
                            <tr>
                                <th>بەروار و ڕۆژ</th>
                                <th style="text-align: center;">دۆخ</th>
                                <th style="text-align: center;">هاتن و چوون</th>
                                <th style="text-align: center;">کاتی زیادە</th>
                                <th style="text-align: center;">سەرفیاتی بەستن</th>
                                <th>تێبینی / شوێن</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${daysRows}
                        </tbody>
                    </table>

                    <div style="margin-top: 40px; display: flex; justify-content: space-between; font-size: 13px;">
                        <div>واژۆی وەستا / کارمەند: __________________</div>
                        <div>واژۆی بەڕێوەبەر: __________________</div>
                    </div>

                    <script>window.onload = () => { window.print(); };<\/script>
                </body>
                </html>
            `);
            w.document.close();
        }
    };
}
</script>
@endsection
