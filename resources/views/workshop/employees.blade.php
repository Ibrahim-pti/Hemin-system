@extends('layouts.menu')
@section('title', 'دەفتەری سەحی وەستاکان')

@section('content')
<div x-data="workshopEmployeesApp()" x-init="init()" class="space-y-3.5 select-none" dir="rtl">

    {{-- ١. هێڵی سەرەوە: ناونیشان و کۆنتڕۆڵی هەفتە و دوگمە سەرەکییەکان بە شێوازێکی خاوێن و پوخت --}}
    <div class="bg-white rounded-2xl p-3.5 sm:p-4 border border-slate-200 shadow-2xs flex flex-col md:flex-row md:items-center justify-between gap-3">
        
        {{-- ناونیشان و ماوەی بەروار --}}
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-teal-700 text-white flex items-center justify-center text-xl shrink-0">
                📋
            </div>
            <div>
                <h1 class="text-base sm:text-lg font-black text-slate-900 leading-tight">جەدوەلی ئامادەبوونی ڕۆژانە (دەفتەری وەستاکان)</h1>
                <p class="text-[11px] text-slate-500 font-medium mt-0.5 flex items-center gap-1.5 font-mono">
                    <span>ماوە:</span>
                    <span class="font-bold text-slate-800 bg-slate-100 px-2 py-0.5 rounded border border-slate-200">
                        {{ str_replace('-', '/', $from) }} تا {{ str_replace('-', '/', $to) }}
                    </span>
                </p>
            </div>
        </div>

        {{-- کۆنتڕۆڵی هەفتە و دوگمەکان --}}
        <div class="flex items-center gap-2 flex-wrap">
            
            {{-- گۆڕینی هەفتە بە دوگمەی مۆدێرن --}}
            <div class="flex items-center bg-slate-100 p-1 rounded-xl border border-slate-200 text-xs">
                <button type="button" @click="changeWeekOffset(-1)"
                        title="هەفتەی پێشوو"
                        class="px-2.5 py-1 rounded-lg font-bold text-slate-600 hover:text-slate-900 hover:bg-white transition-all cursor-pointer">
                    ← پێشوو
                </button>
                <button type="button" @click="setRange('this_week')"
                        :class="rangeType === 'this_week' ? 'bg-teal-700 text-white font-black shadow-2xs' : 'text-slate-700 hover:text-slate-900 font-bold'"
                        class="px-3 py-1 rounded-lg transition-all cursor-pointer">
                    ئەم هەفتەیە
                </button>
                <button type="button" @click="changeWeekOffset(1)"
                        title="هەفتەی دواتر"
                        class="px-2.5 py-1 rounded-lg font-bold text-slate-600 hover:text-slate-900 hover:bg-white transition-all cursor-pointer">
                    دواتر →
                </button>
                <button type="button" @click="setRange('this_month')"
                        :class="rangeType === 'this_month' ? 'bg-teal-700 text-white font-black shadow-2xs' : 'text-slate-700 hover:text-slate-900 font-bold'"
                        class="px-2.5 py-1 rounded-lg transition-all cursor-pointer">
                    مانگانە
                </button>
            </div>

            {{-- سەحی هەمووان بۆ ئەمڕۆ --}}
            <button type="button" @click="batchMarkToday()"
                    class="px-3 py-1.5 rounded-xl text-xs font-black bg-emerald-600 hover:bg-emerald-700 text-white shadow-2xs flex items-center gap-1.5 transition-all cursor-pointer">
                <span>✔️</span>
                <span>سەحی ئەمڕۆ</span>
            </button>

            {{-- زیادکردنی وەستا --}}
            <button type="button" @click="openNewEmployeeModal()"
                    class="px-3 py-1.5 rounded-xl text-xs font-black bg-teal-800 hover:bg-teal-900 text-white shadow-2xs flex items-center gap-1 transition-all cursor-pointer">
                <span>+</span>
                <span>وەستای نوێ</span>
            </button>

            {{-- سێتینگ --}}
            <button type="button" @click="showSettingsModal = true"
                    class="p-1.5 rounded-xl text-slate-600 hover:bg-slate-100 bg-slate-50 border border-slate-200 transition-all cursor-pointer text-sm"
                    title="سێتینگی تاخیربوون و دەوام">
                ⚙️
            </button>
        </div>
    </div>

    {{-- جەدوەلی سەحی ڕۆژانە --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs overflow-hidden">
        
        {{-- بارێکی باریک بۆ گەڕان و هێماکان --}}
        <div class="px-3.5 py-2.5 border-b border-slate-200 flex items-center justify-between gap-3 bg-slate-50/60">
            <div class="flex items-center gap-2 flex-1 max-w-xs">
                <input type="text" x-model="searchQuery" placeholder="🔍 گەڕان بە ناوی وەستا..."
                       class="w-full text-xs px-3 py-1.5 rounded-lg border border-slate-200 focus:outline-hidden focus:border-teal-600 bg-white font-medium shadow-2xs">
            </div>

            <div class="flex items-center gap-3 text-[11px] font-bold text-slate-500">
                <span class="flex items-center gap-1 text-emerald-700"><span class="w-2 h-2 rounded-full bg-emerald-500"></span> سەح</span>
                <span class="flex items-center gap-1 text-rose-700"><span class="w-2 h-2 rounded-full bg-rose-500"></span> نەهاتوو</span>
                <span class="flex items-center gap-1 text-amber-700"><span class="w-2 h-2 rounded-full bg-amber-500"></span> نیوەڕۆژ</span>
                <span class="text-slate-400 font-normal">| کلیک لەسەر خانەکان بکە</span>
            </div>
        </div>

        {{-- خشتەی سەرەکی بە شێوازێکی زۆر ڕێک و هاوسەنگ --}}
        <div class="overflow-x-auto">
            <table class="w-full text-right border-collapse text-xs table-fixed">
                <thead>
                    <tr class="bg-slate-100/90 text-slate-700 font-black border-b border-slate-200 text-center">
                        {{-- وەستا --}}
                        <th class="py-3 px-4 text-right sticky right-0 bg-slate-100 z-10 w-56 sm:w-64 border-l border-slate-200">
                            وەستا / کارمەند
                        </th>

                        {{-- ڕۆژەکان (شەممە تا هەینی) بە قەبارەی یەکسان و ڕێک --}}
                        @foreach($days as $d)
                            <th class="py-2.5 px-1 border-l border-slate-200 {{ $d['is_today'] ? 'bg-amber-100/80 text-amber-950 font-black ring-1 ring-amber-300 ring-inset' : ($d['is_holiday'] ? 'bg-slate-200/50 text-slate-500' : '') }}">
                                <div class="flex flex-col items-center leading-tight">
                                    <span class="text-xs font-black">{{ $d['day_name'] }}</span>
                                    <span class="text-[10px] font-mono font-bold opacity-75 mt-0.5">{{ $d['day_short'] }}</span>
                                    @if($d['is_today'])
                                        <span class="text-[9px] font-black px-1.5 py-0.2 mt-0.5 rounded bg-amber-500 text-white leading-none">ئەمڕۆ</span>
                                    @endif
                                </div>
                            </th>
                        @endforeach

                        {{-- کردارەکان --}}
                        <th class="py-3 px-3 w-40 sm:w-44 text-center print:hidden">کردارەکان</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    <template x-for="row in filteredEmployees" :key="row.id">
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            
                            {{-- زانیاری وەستا --}}
                            <td class="py-3 px-3.5 sticky right-0 bg-white hover:bg-slate-50 z-10 border-l border-slate-200">
                                <div class="flex items-center justify-between gap-2">
                                    <div class="flex items-center gap-2.5 cursor-pointer overflow-hidden" @click="openEmployeeDrawer(row)">
                                        <div class="w-8 h-8 rounded-xl bg-teal-100 text-teal-800 flex items-center justify-center font-bold text-xs shrink-0">
                                            <span x-text="row.name.charAt(0)"></span>
                                        </div>
                                        <div class="overflow-hidden">
                                            <div class="font-black text-slate-900 hover:text-teal-700 leading-tight text-xs sm:text-sm truncate" x-text="row.name"></div>
                                            <div class="text-[11px] text-slate-500 font-mono flex items-center gap-1.5 mt-0.5">
                                                <span class="px-1.5 py-0.2 rounded bg-slate-100 text-slate-700 font-bold text-[10px]" x-text="row.job_title_label"></span>
                                                <span>•</span>
                                                <span class="text-teal-800 font-bold" x-text="formatNumber(row.daily_wage) + ' د.ع'"></span>
                                            </div>
                                        </div>
                                    </div>
                                    <button type="button" @click="openEditWageModal(row)"
                                            class="p-1.5 rounded-lg text-slate-400 hover:text-teal-700 hover:bg-slate-100 print:hidden transition-colors shrink-0"
                                            title="دەستکاری مووچە">
                                        ✏️
                                    </button>
                                </div>
                            </td>

                            {{-- خانەکانی ڕۆژەکان بە شێوازێکی مۆدێرن و یەکسان --}}
                            <template x-for="day in days" :key="day.date">
                                <td class="p-1.5 text-center border-l border-slate-100 relative group"
                                    :class="day.is_today ? 'bg-amber-50/50' : (day.is_holiday ? 'bg-slate-100/40' : '')">
                                    
                                    <div @click="toggleCell(row.id, day.date)"
                                         @contextmenu.prevent="openCellDetailModal(row, day)"
                                         class="w-full h-10 rounded-xl flex items-center justify-center cursor-pointer transition-all text-xs font-black select-none border shadow-2xs"
                                         :class="getCellStyle(row.cells[day.date])">
                                        <span x-text="getCellDisplay(row.cells[day.date])"></span>
                                    </div>

                                    {{-- ئایکۆنی دەستکاری ورد لەسەر هۆڤەر --}}
                                    <button type="button" @click.stop="openCellDetailModal(row, day)"
                                            class="absolute top-1 left-1 opacity-0 group-hover:opacity-100 p-1 bg-white rounded-md text-[10px] text-slate-500 hover:text-teal-700 shadow-xs border border-slate-200 print:hidden transition-all"
                                            title="دەستکاری کاتژمێری هاتن و زیادە">
                                        ⚙️
                                    </button>
                                </td>
                            </template>

                            {{-- کردارەکان: وردەکاری و پێشەکی --}}
                            <td class="py-2.5 px-3 text-center print:hidden">
                                <div class="flex items-center justify-center gap-1.5">
                                    <button type="button" @click="openEmployeeDrawer(row)"
                                            class="px-3 py-1.5 rounded-xl text-xs font-bold bg-teal-50 hover:bg-teal-100 text-teal-800 border border-teal-200 transition-all cursor-pointer flex items-center gap-1"
                                            title="بینینی وردەکاری تەواو، حیسابات و ڕۆژەکانی مانگ">
                                        <span>👁️</span>
                                        <span>وردەکاری</span>
                                    </button>
                                    <button type="button" @click="openPaymentModal(row)"
                                            class="px-2.5 py-1.5 rounded-xl text-xs font-bold bg-purple-50 hover:bg-purple-100 text-purple-800 border border-purple-200 transition-all cursor-pointer flex items-center gap-1"
                                            title="دانی پێشەکی لە قاصە">
                                        <span>💸</span>
                                        <span>پێشەکی</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    {{-- ٤. بەشی وردەکاری تەواوی وەستا (Worker Details Drawer/Modal) --}}
    <div x-show="showEmployeeDrawer" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4">
        <div @click.away="showEmployeeDrawer = false" class="bg-white rounded-3xl w-full max-w-3xl max-h-[90vh] flex flex-col shadow-2xl border border-slate-200 overflow-hidden">
            
            {{-- سەرپەڕە --}}
            <div class="p-4 sm:p-5 bg-teal-800 text-white flex items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-teal-700 text-white flex items-center justify-center font-black text-lg shrink-0">
                        <span x-text="selectedEmployee?.name?.charAt(0)"></span>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="text-base font-black text-white" x-text="selectedEmployee?.name"></h2>
                            <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-teal-700 text-teal-200" x-text="selectedEmployee?.job_title_label"></span>
                        </div>
                        <div class="text-xs text-teal-200 font-mono mt-0.5">
                            مووچە: <b class="text-white" x-text="formatNumber(selectedEmployee?.daily_wage) + ' د.ع'"></b>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <input type="month" x-model="selectedMonth" @change="loadEmployeeMonthDetails()"
                           class="bg-teal-900 text-white text-xs font-mono px-2.5 py-1 rounded-lg border border-teal-700 cursor-pointer">
                    <button type="button" @click="showEmployeeDrawer = false" class="text-teal-200 hover:text-white text-lg font-bold p-1">✕</button>
                </div>
            </div>

            {{-- کارتی کورتی ئامارەکانی مانگ --}}
            <div class="p-3.5 bg-slate-50 border-b border-slate-200 grid grid-cols-2 sm:grid-cols-4 gap-2 text-center text-xs">
                <div class="bg-white p-2.5 rounded-xl border border-slate-200">
                    <div class="text-[10px] text-slate-500 font-bold">ڕۆژانی هاتوو لە مانگدا</div>
                    <div class="text-base font-black text-emerald-800 font-mono mt-0.5" x-text="(drawerData?.stats?.present_count ?? selectedEmployee?.month_summary?.present_count ?? 0) + ' ڕۆژ'"></div>
                </div>
                <div class="bg-white p-2.5 rounded-xl border border-slate-200">
                    <div class="text-[10px] text-teal-800 font-bold">پارەی هەیە (شایستە)</div>
                    <div class="text-base font-black text-teal-950 font-mono mt-0.5" x-text="formatNumber(drawerData?.stats?.total_earned ?? selectedEmployee?.month_summary?.total_earned ?? 0) + ' د.ع'"></div>
                </div>
                <div class="bg-white p-2.5 rounded-xl border border-slate-200">
                    <div class="text-[10px] text-purple-800 font-bold">چەند براوە (پێشەکی)</div>
                    <div class="text-base font-black text-purple-950 font-mono mt-0.5" x-text="formatNumber(drawerData?.stats?.total_paid ?? selectedEmployee?.month_summary?.total_paid ?? 0) + ' د.ع'"></div>
                </div>
                <div class="bg-white p-2.5 rounded-xl border border-slate-200">
                    <div class="text-[10px] text-amber-800 font-bold">باڵانسی ماوە</div>
                    <div class="text-base font-black text-amber-950 font-mono mt-0.5" x-text="formatNumber(drawerData?.stats?.remaining_balance ?? selectedEmployee?.month_summary?.remaining ?? 0) + ' د.ع'"></div>
                </div>
            </div>

            {{-- خشتەی ڕۆژەکان و پارەدانەکان --}}
            <div class="p-4 overflow-y-auto flex-1 space-y-4 text-xs">
                
                {{-- ڕۆژەکانی مانگ --}}
                <div>
                    <h3 class="font-black text-slate-800 mb-2 flex items-center gap-1.5">
                        <span>📅</span>
                        <span>تۆماری دەوامی ڕۆژانەی مانگ</span>
                    </h3>
                    <div class="border border-slate-200 rounded-xl overflow-hidden">
                        <table class="w-full text-right">
                            <thead class="bg-slate-100 text-slate-700 font-bold border-b border-slate-200 text-center">
                                <tr>
                                    <th class="p-2 text-right">بەروار</th>
                                    <th class="p-2">دۆخ</th>
                                    <th class="p-2">هاتن / چوون</th>
                                    <th class="p-2">کاتی زیادە</th>
                                    <th class="p-2">تاخیربوون</th>
                                    <th class="p-2 text-right">تێبینی</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 font-medium">
                                <template x-for="item in (drawerData?.attendances || [])" :key="item.id">
                                    <tr class="hover:bg-slate-50 text-center">
                                        <td class="p-2 font-mono font-bold text-slate-800 text-right" x-text="item.work_date"></td>
                                        <td class="p-2">
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold"
                                                  :class="item.status === 'present' ? 'bg-emerald-100 text-emerald-800' : (item.status === 'half_day' ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800')"
                                                  x-text="item.status_label"></span>
                                        </td>
                                        <td class="p-2 font-mono text-slate-600" x-text="(item.check_in || '—') + ' - ' + (item.check_out || '—')"></td>
                                        <td class="p-2 font-mono text-blue-700" x-text="item.overtime_hours > 0 ? item.overtime_hours + ' ک' : '—'"></td>
                                        <td class="p-2 font-mono text-rose-700" x-text="item.late_minutes > 0 ? item.late_minutes + ' خ' : '—'"></td>
                                        <td class="p-2 text-slate-600 text-right" x-text="item.note || '—'"></td>
                                    </tr>
                                </template>
                                <template x-if="!drawerData?.attendances || drawerData.attendances.length === 0">
                                    <tr>
                                        <td colspan="6" class="p-3 text-center text-slate-400 font-bold">هیچ تۆمارێک نییە.</td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- وەسڵەکانی پێشەکی --}}
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="font-black text-slate-800 flex items-center gap-1.5">
                            <span>💸</span>
                            <span>پێشەکی و پارەدان لە قاصە</span>
                        </h3>
                        <button type="button" @click="openPaymentModal(selectedEmployee)"
                                class="px-2.5 py-1 rounded-lg text-xs font-bold bg-teal-700 text-white hover:bg-teal-800">
                            + دانی پێشەکی
                        </button>
                    </div>
                    <div class="border border-slate-200 rounded-xl overflow-hidden">
                        <table class="w-full text-right">
                            <thead class="bg-slate-100 text-slate-700 font-bold border-b border-slate-200 text-center">
                                <tr>
                                    <th class="p-2 text-right">ژ.وەسڵ</th>
                                    <th class="p-2">بەروار</th>
                                    <th class="p-2">بڕی پارە</th>
                                    <th class="p-2 text-right">تێبینی</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 font-medium">
                                <template x-for="p in (drawerData?.payments || [])" :key="p.id">
                                    <tr class="hover:bg-slate-50 text-center">
                                        <td class="p-2 font-mono font-bold text-slate-800 text-right" x-text="'#' + p.voucher_no"></td>
                                        <td class="p-2 font-mono text-slate-600" x-text="p.paid_at"></td>
                                        <td class="p-2 font-mono font-black text-purple-900" x-text="formatNumber(p.amount) + ' ' + p.currency"></td>
                                        <td class="p-2 text-slate-600 text-right" x-text="p.note || 'پێشەکی'"></td>
                                    </tr>
                                </template>
                                <template x-if="!drawerData?.payments || drawerData.payments.length === 0">
                                    <tr>
                                        <td colspan="4" class="p-3 text-center text-slate-400 font-bold">هیچ پارەدانێک نییە.</td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

            {{-- ژێرپەڕە --}}
            <div class="p-3.5 bg-slate-50 border-t border-slate-200 flex items-center justify-between">
                <button type="button" @click="showEmployeeDrawer = false" class="px-4 py-1.5 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-200">داخستن</button>
                <button type="button" @click="openPaymentModal(selectedEmployee)" class="px-4 py-1.5 rounded-xl text-xs font-black bg-purple-700 text-white shadow-2xs">
                    💸 دانی پێشەکی
                </button>
            </div>

        </div>
    </div>

    {{-- ٥. مۆداڵی سێتینگی بەڕێوەبەر (Settings Modal) --}}
    <div x-show="showSettingsModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-3">
        <div @click.away="showSettingsModal = false" class="bg-white rounded-3xl w-full max-w-lg shadow-2xl border border-slate-200 overflow-hidden text-xs">
            
            <form @submit.prevent="saveSettings()">
                <div class="p-4 bg-teal-800 text-white flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-black text-white">سێتینگی دەوام و یاسای تاخیربوون</h2>
                        <p class="text-[11px] text-teal-200">ڕێکخستنی کاتژمێری دەوام و بڕینی تاخیربوون</p>
                    </div>
                    <button type="button" @click="showSettingsModal = false" class="text-teal-200 hover:text-white text-lg font-bold">✕</button>
                </div>

                <div class="p-4 space-y-3.5 font-bold text-slate-700">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block mb-1 text-slate-600">دەستپێکی دەوام</label>
                            <input type="time" x-model="settingsForm.workshop_work_start" required
                                   class="w-full px-3 py-2 rounded-xl border border-slate-200 font-mono font-bold">
                        </div>
                        <div>
                            <label class="block mb-1 text-slate-600">کۆتایی دەوام</label>
                            <input type="time" x-model="settingsForm.workshop_work_end" required
                                   class="w-full px-3 py-2 rounded-xl border border-slate-200 font-mono font-bold">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block mb-1 text-slate-600">کاتژمێری فەرمی ڕۆژانە</label>
                            <input type="number" step="0.5" min="1" max="24" x-model="settingsForm.workshop_work_hours" required
                                   class="w-full px-3 py-2 rounded-xl border border-slate-200 font-mono font-bold">
                        </div>
                        <div>
                            <label class="block mb-1 text-slate-600">پشووی هەفتانە</label>
                            <select x-model="settingsForm.workshop_weekly_holiday" class="w-full px-3 py-2 rounded-xl border border-slate-200 font-bold bg-white">
                                <option value="friday">هەینی</option>
                                <option value="saturday">شەممە</option>
                                <option value="thursday">پێنجشەممە</option>
                                <option value="none">بێ پشوو</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block mb-1 text-slate-600">نرخی کاتی زیادە</label>
                            <select x-model="settingsForm.workshop_overtime_multiplier" class="w-full px-3 py-2 rounded-xl border border-slate-200 font-bold bg-white">
                                <option value="1.0">١.٠ (ئاسایی)</option>
                                <option value="1.25">١.٢٥</option>
                                <option value="1.5">١.٥</option>
                            </select>
                        </div>
                        <div>
                            <label class="block mb-1 text-slate-600">لێخۆشبوونی تاخیربوون (خولەک)</label>
                            <input type="number" min="0" max="120" x-model="settingsForm.workshop_late_grace_minutes"
                                   class="w-full px-3 py-2 rounded-xl border border-slate-200 font-mono font-bold">
                        </div>
                    </div>

                    {{-- یاسای تاخیربوون --}}
                    <div class="p-3 bg-rose-50 border border-rose-200 rounded-xl space-y-2">
                        <div class="text-rose-900 font-black flex items-center gap-1">
                            <span>⚠️</span>
                            <span>یاسای بڕینی تاخیربوون:</span>
                        </div>
                        <div>
                            <label class="block mb-1 text-slate-600">شێوازی بڕین</label>
                            <select x-model="settingsForm.workshop_late_deduction_type" class="w-full px-3 py-2 rounded-xl border border-slate-200 font-bold bg-white">
                                <option value="weekly_threshold">سزای هەفتانە (ئەگەر لە هەفتەیەک چەند ڕۆژ تاخیر بێت)</option>
                                <option value="fixed_amount">بڕی دیاریکراو بۆ هەر ڕۆژێکی تاخیر</option>
                            </select>
                        </div>
                        <template x-if="settingsForm.workshop_late_deduction_type === 'weekly_threshold'">
                            <div class="grid grid-cols-2 gap-2 pt-1">
                                <div>
                                    <label class="block mb-0.5 text-slate-600">ئەگەر تاخیر بوو زیاتر لە:</label>
                                    <input type="number" min="1" max="7" x-model="settingsForm.workshop_late_weekly_threshold_days"
                                           class="w-full px-3 py-1.5 rounded-lg border border-slate-200 font-mono font-bold bg-white">
                                </div>
                                <div>
                                    <label class="block mb-0.5 text-slate-600">بڕی سزای هەفتانە (د.ع):</label>
                                    <input type="number" min="0" step="1000" x-model="settingsForm.workshop_late_weekly_penalty_amount"
                                           placeholder="٠ = مووچەی یەک ڕۆژ"
                                           class="w-full px-3 py-1.5 rounded-lg border border-slate-200 font-mono font-bold bg-white">
                                </div>
                            </div>
                        </template>
                        <template x-if="settingsForm.workshop_late_deduction_type === 'fixed_amount'">
                            <div>
                                <label class="block mb-0.5 text-slate-600">بڕی بڕین بۆ هەر ڕۆژێکی تاخیر (د.ع):</label>
                                <input type="number" min="0" step="500" x-model="settingsForm.workshop_late_deduction_rate"
                                       class="w-full px-3 py-1.5 rounded-lg border border-slate-200 font-mono font-bold bg-white">
                            </div>
                        </template>
                    </div>
                </div>

                <div class="p-3 bg-slate-50 border-t border-slate-200 flex items-center justify-end gap-2">
                    <button type="button" @click="showSettingsModal = false" class="px-4 py-1.5 rounded-xl text-slate-600 hover:bg-slate-200">داخستن</button>
                    <button type="submit" class="px-4 py-1.5 rounded-xl font-black bg-teal-700 text-white hover:bg-teal-800 shadow-2xs">
                        پاشەکەوتکردن
                    </button>
                </div>
            </form>

        </div>
    </div>

    {{-- ٦. مۆداڵی پێشەکی (Payment Modal) --}}
    <div x-show="showPaymentModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-3">
        <div @click.away="showPaymentModal = false" class="bg-white rounded-3xl w-full max-w-sm shadow-2xl border border-slate-200 overflow-hidden text-xs">
            <form @submit.prevent="savePayment()">
                <div class="p-4 bg-purple-900 text-white flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-black text-white" x-text="'پێشەکی: ' + (paymentEmployee?.name || '')"></h2>
                        <p class="text-[11px] text-purple-200">دەرچوونی پارە لە قاصەوە</p>
                    </div>
                    <button type="button" @click="showPaymentModal = false" class="text-purple-200 hover:text-white text-lg font-bold">✕</button>
                </div>

                <div class="p-4 space-y-3 font-bold text-slate-700">
                    <div>
                        <label class="block mb-1 text-slate-600">بڕی پارە (د.ع) *</label>
                        <input type="number" min="1" step="500" x-model="paymentForm.amount" required
                               class="w-full px-3 py-2 rounded-xl border border-slate-200 font-mono font-black text-base text-purple-950">
                    </div>
                    <div>
                        <label class="block mb-1 text-slate-600">قاسە</label>
                        <select x-model="paymentForm.cash_box_id" class="w-full px-3 py-2 rounded-xl border border-slate-200 font-bold bg-white">
                            @foreach($cashBoxes as $box)
                                <option value="{{ $box->id }}">{{ $box->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block mb-1 text-slate-600">بەروار</label>
                        <input type="date" x-model="paymentForm.paid_at" required
                               class="w-full px-3 py-2 rounded-xl border border-slate-200 font-mono font-bold bg-white">
                    </div>
                    <div>
                        <label class="block mb-1 text-slate-600">تێبینی</label>
                        <input type="text" x-model="paymentForm.note"
                               class="w-full px-3 py-2 rounded-xl border border-slate-200 font-medium">
                    </div>
                </div>

                <div class="p-3 bg-slate-50 border-t border-slate-200 flex items-center justify-end gap-2">
                    <button type="button" @click="showPaymentModal = false" class="px-4 py-1.5 rounded-xl text-slate-600 hover:bg-slate-200">داخستن</button>
                    <button type="submit" class="px-4 py-1.5 rounded-xl font-black bg-purple-700 text-white hover:bg-purple-800 shadow-2xs">
                        تۆمارکردن
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ٧. مۆداڵی وەستای نوێ (New Employee Modal) --}}
    <div x-show="showNewEmployeeModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-3">
        <div @click.away="showNewEmployeeModal = false" class="bg-white rounded-3xl w-full max-w-sm shadow-2xl border border-slate-200 overflow-hidden text-xs">
            <form @submit.prevent="saveNewEmployee()">
                <div class="p-4 bg-teal-800 text-white flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-black text-white">وەستای نوێ</h2>
                        <p class="text-[11px] text-teal-200">تۆمارکردنی وەستا یان کرێکار</p>
                    </div>
                    <button type="button" @click="showNewEmployeeModal = false" class="text-teal-200 hover:text-white text-lg font-bold">✕</button>
                </div>

                <div class="p-4 space-y-3 font-bold text-slate-700">
                    <div>
                        <label class="block mb-1 text-slate-600">ناو *</label>
                        <input type="text" x-model="newEmpForm.name" required
                               class="w-full px-3 py-2 rounded-xl border border-slate-200 font-bold">
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block mb-1 text-slate-600">پیشە</label>
                            <select x-model="newEmpForm.job_title" class="w-full px-3 py-2 rounded-xl border border-slate-200 font-bold bg-white">
                                <option value="master">وەستا</option>
                                <option value="porter">حەمەڵ</option>
                                <option value="helper">یاریدەدەر</option>
                                <option value="driver">شۆفێر</option>
                                <option value="other">پیشەی تر</option>
                            </select>
                        </div>
                        <div>
                            <label class="block mb-1 text-slate-600">مووچەی ڕۆژانە *</label>
                            <input type="number" min="0" step="500" x-model="newEmpForm.daily_wage" required
                                   class="w-full px-3 py-2 rounded-xl border border-slate-200 font-mono font-bold">
                        </div>
                    </div>
                    <div>
                        <label class="block mb-1 text-slate-600">مۆبایل</label>
                        <input type="text" x-model="newEmpForm.phone"
                               class="w-full px-3 py-2 rounded-xl border border-slate-200 font-mono font-bold">
                    </div>
                </div>

                <div class="p-3 bg-slate-50 border-t border-slate-200 flex items-center justify-end gap-2">
                    <button type="button" @click="showNewEmployeeModal = false" class="px-4 py-1.5 rounded-xl text-slate-600 hover:bg-slate-200">داخستن</button>
                    <button type="submit" class="px-4 py-1.5 rounded-xl font-black bg-teal-700 text-white hover:bg-teal-800 shadow-2xs">
                        زیادکردن
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ٨. مۆداڵی دەستکاری مووچە (Edit Wage Modal) --}}
    <div x-show="showEditWageModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-3">
        <div @click.away="showEditWageModal = false" class="bg-white rounded-3xl w-full max-w-sm shadow-2xl border border-slate-200 overflow-hidden text-xs">
            <form @submit.prevent="saveEditWage()">
                <div class="p-4 bg-teal-800 text-white flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-black text-white" x-text="'دەستکاری: ' + (editWageForm.name || '')"></h2>
                        <p class="text-[11px] text-teal-200">گۆڕینی مووچە و پیشە</p>
                    </div>
                    <button type="button" @click="showEditWageModal = false" class="text-teal-200 hover:text-white text-lg font-bold">✕</button>
                </div>

                <div class="p-4 space-y-3 font-bold text-slate-700">
                    <div>
                        <label class="block mb-1 text-slate-600">ناو *</label>
                        <input type="text" x-model="editWageForm.name" required
                               class="w-full px-3 py-2 rounded-xl border border-slate-200 font-bold">
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block mb-1 text-slate-600">پیشە</label>
                            <select x-model="editWageForm.job_title" class="w-full px-3 py-2 rounded-xl border border-slate-200 font-bold bg-white">
                                <option value="master">وەستا</option>
                                <option value="porter">حەمەڵ</option>
                                <option value="helper">یاریدەدەر</option>
                                <option value="driver">شۆفێر</option>
                                <option value="other">پیشەی تر</option>
                            </select>
                        </div>
                        <div>
                            <label class="block mb-1 text-slate-600">مووچەی ڕۆژانە *</label>
                            <input type="number" min="0" step="500" x-model="editWageForm.daily_wage" required
                                   class="w-full px-3 py-2 rounded-xl border border-slate-200 font-mono font-bold">
                        </div>
                    </div>
                    <div>
                        <label class="block mb-1 text-slate-600">مۆبایل</label>
                        <input type="text" x-model="editWageForm.phone"
                               class="w-full px-3 py-2 rounded-xl border border-slate-200 font-mono font-bold">
                    </div>
                </div>

                <div class="p-3 bg-slate-50 border-t border-slate-200 flex items-center justify-end gap-2">
                    <button type="button" @click="showEditWageModal = false" class="px-4 py-1.5 rounded-xl text-slate-600 hover:bg-slate-200">داخستن</button>
                    <button type="submit" class="px-4 py-1.5 rounded-xl font-black bg-teal-700 text-white hover:bg-teal-800 shadow-2xs">
                        نوێکردنەوە
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ٩. مۆداڵی دەستکاری وردی خانەی سەح (Cell Detail Modal) --}}
    <div x-show="showCellModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-3">
        <div @click.away="showCellModal = false" class="bg-white rounded-3xl w-full max-w-sm shadow-2xl border border-slate-200 overflow-hidden text-xs">
            <form @submit.prevent="saveCellDetail()">
                <div class="p-3.5 bg-teal-800 text-white flex items-center justify-between">
                    <div>
                        <h2 class="text-sm font-black text-white" x-text="(activeCellEmployee?.name || '')"></h2>
                        <p class="text-[11px] text-teal-200 font-mono" x-text="activeCellDay?.date + ' (' + activeCellDay?.day_name + ')'"></p>
                    </div>
                    <button type="button" @click="showCellModal = false" class="text-teal-200 hover:text-white text-lg font-bold">✕</button>
                </div>

                <div class="p-3.5 space-y-3 font-bold text-slate-700">
                    <div>
                        <label class="block mb-1 text-slate-600">دۆخی ئامادەبوون</label>
                        <select x-model="cellForm.status" class="w-full px-3 py-2 rounded-xl border border-slate-200 font-bold bg-white">
                            <option value="present">سەح (ئامادە) ✔️</option>
                            <option value="half_day">نیوەڕۆژ ⏳</option>
                            <option value="absent">غائیب (نەهاتوو) ❌</option>
                            <option value="leave">مۆڵەت 🏖️</option>
                            <option value="delete">سڕینەوە (خاڵی) 🗑️</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block mb-0.5 text-slate-600">کاتی هاتن</label>
                            <input type="time" x-model="cellForm.check_in"
                                   class="w-full px-2.5 py-1.5 rounded-lg border border-slate-200 font-mono font-bold">
                        </div>
                        <div>
                            <label class="block mb-0.5 text-slate-600">کاتی چوون</label>
                            <input type="time" x-model="cellForm.check_out"
                                   class="w-full px-2.5 py-1.5 rounded-lg border border-slate-200 font-mono font-bold">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block mb-0.5 text-slate-600">کاتی زیادە (ک/ژ)</label>
                            <input type="number" step="0.5" min="0" x-model="cellForm.overtime_hours"
                                   class="w-full px-2.5 py-1.5 rounded-lg border border-slate-200 font-mono font-bold">
                        </div>
                        <div>
                            <label class="block mb-0.5 text-slate-600">سەرفیات (د.ع)</label>
                            <input type="number" step="500" min="0" x-model="cellForm.fuel_expense"
                                   class="w-full px-2.5 py-1.5 rounded-lg border border-slate-200 font-mono font-bold">
                        </div>
                    </div>

                    <div>
                        <label class="block mb-0.5 text-slate-600">تێبینی</label>
                        <input type="text" x-model="cellForm.note"
                               class="w-full px-2.5 py-1.5 rounded-lg border border-slate-200 font-medium">
                    </div>
                </div>

                <div class="p-3 bg-slate-50 border-t border-slate-200 flex items-center justify-end gap-2">
                    <button type="button" @click="showCellModal = false" class="px-3 py-1.5 rounded-lg text-slate-600 hover:bg-slate-200">داخستن</button>
                    <button type="submit" class="px-4 py-1.5 rounded-lg font-black bg-teal-700 text-white hover:bg-teal-800 shadow-2xs">
                        پاشەکەوتکردن
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
function workshopEmployeesApp() {
    return {
        rangeType: '{{ $rangeType }}',
        weekOffset: {{ $weekOffset ?? 0 }},
        from: '{{ $from }}',
        to: '{{ $to }}',
        searchQuery: '',
        days: {!! json_encode($days, JSON_UNESCAPED_UNICODE) !!},
        matrix: {!! json_encode($employeesMatrix, JSON_UNESCAPED_UNICODE) !!},
        
        showEmployeeDrawer: false,
        selectedEmployee: null,
        selectedMonth: '{{ now()->format("Y-m") }}',
        drawerData: null,

        showSettingsModal: false,
        settingsForm: {
            workshop_work_start: '{{ $shiftSettings['work_start'] }}',
            workshop_work_end: '{{ $shiftSettings['work_end'] }}',
            workshop_work_hours: {{ $shiftSettings['work_hours'] }},
            workshop_weekly_holiday: '{{ $shiftSettings['weekly_holiday'] }}',
            workshop_overtime_multiplier: {{ $shiftSettings['overtime_multiplier'] }},
            workshop_late_grace_minutes: {{ $shiftSettings['late_grace_minutes'] }},
            workshop_late_deduction_type: '{{ $shiftSettings['late_deduction_type'] }}',
            workshop_late_deduction_rate: {{ $shiftSettings['late_deduction_rate'] }},
            workshop_late_weekly_threshold_days: {{ $shiftSettings['late_weekly_threshold_days'] }},
            workshop_late_weekly_penalty_amount: {{ $shiftSettings['late_weekly_penalty_amount'] }}
        },

        showCellModal: false,
        activeCellEmployee: null,
        activeCellDay: null,
        cellForm: {
            employee_id: null,
            work_date: null,
            status: 'present',
            check_in: '',
            check_out: '',
            hours: 0,
            overtime_hours: 0,
            late_minutes: 0,
            fuel_expense: 0,
            deduction_amount: 0,
            note: ''
        },

        showPaymentModal: false,
        paymentEmployee: null,
        paymentForm: {
            employee_id: null,
            amount: '',
            currency: 'IQD',
            cash_box_id: '{{ $cashBoxes->first()?->id }}',
            paid_at: '{{ now()->toDateString() }}',
            note: ''
        },

        showNewEmployeeModal: false,
        newEmpForm: {
            name: '',
            phone: '',
            job_title: 'master',
            salary_type: 'daily',
            daily_wage: '',
            wage_currency: 'IQD',
            note: ''
        },

        showEditWageModal: false,
        editWageForm: {
            id: null,
            name: '',
            phone: '',
            job_title: 'master',
            salary_type: 'daily',
            daily_wage: ''
        },

        init() {},

        get filteredEmployees() {
            if (!this.searchQuery.trim()) return this.matrix;
            const q = this.searchQuery.toLowerCase().trim();
            return this.matrix.filter(e => {
                return (e.name && e.name.toLowerCase().includes(q)) ||
                       (e.job_title_label && e.job_title_label.toLowerCase().includes(q)) ||
                       (e.phone && e.phone.includes(q));
            });
        },

        setRange(type) {
            window.location.href = `{{ route('workshop.employees') }}?range_type=${type}`;
        },

        changeWeekOffset(diff) {
            const newOffset = this.weekOffset + diff;
            window.location.href = `{{ route('workshop.employees') }}?week_offset=${newOffset}`;
        },

        getCellStyle(cell) {
            if (!cell || !cell.status) return 'text-slate-300 border-dashed border-slate-200 hover:border-slate-300 hover:text-slate-500 hover:bg-slate-50';
            if (cell.status === 'present') return 'bg-emerald-600 border-emerald-600 text-white font-black shadow-2xs hover:bg-emerald-700';
            if (cell.status === 'half_day') return 'bg-amber-500 border-amber-500 text-white font-black shadow-2xs hover:bg-amber-600';
            if (cell.status === 'absent') return 'bg-rose-500 border-rose-500 text-white font-black shadow-2xs hover:bg-rose-600';
            if (cell.status === 'leave') return 'bg-sky-500 border-sky-500 text-white font-black shadow-2xs hover:bg-sky-600';
            if (cell.status === 'holiday') return 'bg-slate-200 border-slate-300 text-slate-600';
            return 'text-slate-300 border-slate-200';
        },

        getCellDisplay(cell) {
            if (!cell || !cell.status) return '—';
            if (cell.status === 'present') return 'سەح ✔️';
            if (cell.status === 'half_day') return 'نیوە ⏳';
            if (cell.status === 'absent') return 'غائیب ❌';
            if (cell.status === 'leave') return 'مۆڵەت 🏖️';
            if (cell.status === 'holiday') return 'پشوو 🌴';
            return '—';
        },

        async toggleCell(empId, date) {
            try {
                const res = await fetch('{{ route('workshop.employees.toggle-cell') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        employee_id: empId,
                        work_date: date
                    })
                });

                const data = await res.json();
                if (data.ok) {
                    const row = this.matrix.find(r => r.id === empId);
                    if (row) {
                        row.cells[date] = data.attendance;
                        this.recalculateRow(row);
                    }
                }
            } catch (e) {
                console.error(e);
            }
        },

        openCellDetailModal(row, day) {
            this.activeCellEmployee = row;
            this.activeCellDay = day;
            const existing = row.cells[day.date];

            this.cellForm = {
                employee_id: row.id,
                work_date: day.date,
                status: existing?.status || 'present',
                check_in: existing?.check_in || '{{ $shiftSettings['work_start'] }}',
                check_out: existing?.check_out || '{{ $shiftSettings['work_end'] }}',
                hours: existing?.hours || {{ $shiftSettings['work_hours'] }},
                overtime_hours: existing?.overtime_hours || 0,
                late_minutes: existing?.late_minutes || 0,
                fuel_expense: existing?.fuel_expense || 0,
                deduction_amount: existing?.deduction_amount || 0,
                note: existing?.note || ''
            };

            this.showCellModal = true;
        },

        async saveCellDetail() {
            try {
                const res = await fetch('{{ route('workshop.employees.save-cell-detail') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(this.cellForm)
                });

                const data = await res.json();
                if (data.ok) {
                    const row = this.matrix.find(r => r.id === this.cellForm.employee_id);
                    if (row) {
                        row.cells[this.cellForm.work_date] = data.attendance;
                        this.recalculateRow(row);
                    }
                    this.showCellModal = false;
                }
            } catch (e) {
                alert('هەڵە لە پاشەکەوتکردندا');
            }
        },

        async batchMarkToday() {
            const todayStr = '{{ now()->toDateString() }}';
            if (!confirm(`ئایا دڵنیایت دەتەوێت سەح بۆ هەموو وەستاکان بۆ ئەمڕۆ (${todayStr}) لێبدەیت؟`)) return;

            try {
                const res = await fetch('{{ route('workshop.employees.batch-mark-day') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        work_date: todayStr,
                        status: 'present'
                    })
                });

                const data = await res.json();
                if (data.ok) {
                    window.location.reload();
                }
            } catch (e) {
                alert('هەڵە لە تۆمارکردنی بەکۆمەڵ');
            }
        },

        async openEmployeeDrawer(row) {
            this.selectedEmployee = row;
            this.showEmployeeDrawer = true;
            await this.loadEmployeeMonthDetails();
        },

        async loadEmployeeMonthDetails() {
            if (!this.selectedEmployee) return;
            try {
                const res = await fetch(`/workshop/employees/${this.selectedEmployee.id}/month-details?month=${this.selectedMonth}`, {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await res.json();
                if (data.ok) {
                    this.drawerData = data;
                }
            } catch (e) {
                console.error(e);
            }
        },

        openPaymentModal(row) {
            this.paymentEmployee = row;
            this.paymentForm = {
                employee_id: row.id,
                amount: '',
                currency: row.wage_currency || 'IQD',
                cash_box_id: '{{ $cashBoxes->first()?->id }}',
                paid_at: '{{ now()->toDateString() }}',
                note: `پێشەکی ${row.name}`
            };
            this.showPaymentModal = true;
        },

        async savePayment() {
            try {
                const res = await fetch('{{ route('workshop.employees.record-payment') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(this.paymentForm)
                });

                const data = await res.json();
                if (data.ok) {
                    alert(data.message);
                    const row = this.matrix.find(r => r.id === this.paymentForm.employee_id);
                    if (row) {
                        row.total_paid += parseFloat(this.paymentForm.amount);
                        row.remaining_balance = row.total_earned - row.total_paid;
                    }
                    this.showPaymentModal = false;
                    if (this.showEmployeeDrawer) {
                        this.loadEmployeeMonthDetails();
                    }
                }
            } catch (e) {
                alert('هەڵە لە تۆمارکردنی پێشەکی');
            }
        },

        openNewEmployeeModal() {
            this.newEmpForm = {
                name: '',
                phone: '',
                job_title: 'master',
                salary_type: 'daily',
                daily_wage: '',
                wage_currency: 'IQD',
                note: ''
            };
            this.showNewEmployeeModal = true;
        },

        async saveNewEmployee() {
            try {
                const res = await fetch('{{ route('workshop.employees.quick-store') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(this.newEmpForm)
                });

                const data = await res.json();
                if (data.ok) {
                    window.location.reload();
                }
            } catch (e) {
                alert('هەڵە لە زیادکردنی وەستا');
            }
        },

        openEditWageModal(row) {
            this.editWageForm = {
                id: row.id,
                name: row.name,
                phone: row.phone,
                job_title: row.job_title,
                salary_type: row.salary_type || 'daily',
                daily_wage: row.daily_wage
            };
            this.showEditWageModal = true;
        },

        async saveEditWage() {
            try {
                const res = await fetch(`/workshop/employees/${this.editWageForm.id}/update-wage`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(this.editWageForm)
                });

                const data = await res.json();
                if (data.ok) {
                    const row = this.matrix.find(r => r.id === this.editWageForm.id);
                    if (row) {
                        row.name = this.editWageForm.name;
                        row.phone = this.editWageForm.phone;
                        row.job_title = this.editWageForm.job_title;
                        row.job_title_label = data.job_title_label;
                        row.daily_wage = parseFloat(this.editWageForm.daily_wage);
                        row.effective_daily_wage = row.daily_wage;
                        this.recalculateRow(row);
                    }
                    this.showEditWageModal = false;
                }
            } catch (e) {
                alert('هەڵە لە نوێکردنەوەی مووچە');
            }
        },

        async saveSettings() {
            try {
                const res = await fetch('{{ route('workshop.settings') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(this.settingsForm)
                });

                const data = await res.json();
                if (data.ok) {
                    alert(data.message);
                    window.location.reload();
                }
            } catch (e) {
                alert('هەڵە لە پاشەکەوتکردنی سێتینگ');
            }
        },

        recalculateRow(row) {
            let present = 0;
            let halfDay = 0;
            let ot = 0;
            let fuel = 0;
            let lateMins = 0;
            let lateDays = 0;
            let manualDeduction = 0;

            Object.values(row.cells).forEach(cell => {
                if (cell) {
                    if (cell.status === 'present') present++;
                    else if (cell.status === 'half_day') halfDay++;
                    ot += parseFloat(cell.overtime_hours || 0);
                    fuel += parseFloat(cell.fuel_expense || 0);
                    manualDeduction += parseFloat(cell.deduction_amount || 0);
                    if (cell.late_minutes > 0) {
                        lateDays++;
                        lateMins += parseInt(cell.late_minutes);
                    }
                }
            });

            row.present_count = present;
            row.half_day_count = halfDay;
            row.total_overtime = ot;
            row.total_fuel = fuel;
            row.total_late_minutes = lateMins;
            row.late_days_count = lateDays;

            const wage = row.effective_daily_wage || row.daily_wage;
            const baseEarned = (present * wage) + (halfDay * wage * 0.5);
            const hourly = wage / {{ $shiftSettings['work_hours'] }};
            const otEarned = ot * hourly * {{ $shiftSettings['overtime_multiplier'] }};

            let policyDeduction = 0;
            if (this.settingsForm.workshop_late_deduction_type === 'weekly_threshold' && lateDays >= this.settingsForm.workshop_late_weekly_threshold_days) {
                policyDeduction = this.settingsForm.workshop_late_weekly_penalty_amount > 0 ? this.settingsForm.workshop_late_weekly_penalty_amount : wage;
            } else if (this.settingsForm.workshop_late_deduction_type === 'fixed_amount') {
                policyDeduction = lateDays * this.settingsForm.workshop_late_deduction_rate;
            }

            row.total_deductions = manualDeduction + policyDeduction;
            row.total_earned = Math.round(baseEarned + otEarned + fuel - row.total_deductions);
            row.remaining_balance = Math.round(row.total_earned - row.total_paid);
        },

        formatNumber(num) {
            if (num === null || num === undefined || isNaN(num)) return '0';
            return Number(num).toLocaleString('en-US');
        }
    };
}
</script>
@endsection
