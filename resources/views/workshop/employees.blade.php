@extends('layouts.menu')
@section('title', 'دەفتەری سەحی وەستاکان')

@section('content')
<div x-data="workshopEmployeesApp()" x-init="init()" class="space-y-3.5 select-none" dir="rtl">

    {{-- ١. هێڵی سەرەوە: پەرتکراو لەسەر یەک دێڕ (ڕاست: ناونیشان | ناوەڕاست: فلتەر | چەپ: دوگمەکان) --}}
    <div class="bg-white rounded-2xl p-3 sm:p-3.5 border border-slate-200 shadow-2xs flex flex-wrap lg:flex-nowrap items-center justify-between gap-3">
        
        {{-- لای ڕاست: ناونیشان --}}
        <div class="flex items-center gap-2.5 shrink-0">
            <div class="w-9 h-9 rounded-xl bg-teal-700 text-white flex items-center justify-center text-lg shrink-0 shadow-xs">
                📋
            </div>
            <div>
                <h1 class="text-sm sm:text-base font-black text-slate-900 leading-tight">جەدوەلی ئامادەبوونی ڕۆژانە (دەفتەری وەستاکان)</h1>
                <p class="text-[11px] text-slate-500 font-medium mt-0.5">بەڕێوەبردنی دەوام و حیساباتی وەستاکان</p>
            </div>
        </div>

        {{-- ناوەڕاست: فلتەری ماوە و بەروار --}}
        <div class="flex items-center justify-center shrink-0">
            <div class="flex items-center gap-1.5 bg-slate-50 p-1 rounded-2xl border border-slate-200 shadow-2xs">
                {{-- گەڕانەوە بۆ هەفتەی پێشوو --}}
                <button type="button" @click="changeWeekOffset(-1)"
                        title="هەفتەی پێشوو"
                        class="w-7 h-7 rounded-lg bg-white hover:bg-slate-100 text-slate-700 border border-slate-200 flex items-center justify-center font-bold text-xs shadow-2xs transition-all cursor-pointer">
                    ←
                </button>

                {{-- سێلێکتی فلتەری کات --}}
                <div class="relative flex items-center">
                    <span class="absolute right-2 text-slate-400 pointer-events-none text-xs">📅</span>
                    <select x-model="rangeType" @change="setRange(rangeType)"
                            class="bg-white text-slate-800 font-black text-xs pr-7 pl-5 py-1.5 rounded-xl border border-slate-200 hover:border-teal-600 focus:outline-hidden focus:ring-2 focus:ring-teal-500/20 cursor-pointer shadow-2xs transition-all appearance-none">
                        <option value="this_month">ئەم مانگە (سەرەمانگ بۆ سەرەمانگ)</option>
                        <option value="this_week">ئەم هەفتەیە</option>
                        <option value="last_week">هەفتەی پێشوو</option>
                        <option value="last_month">مانگی پێشوو</option>
                    </select>
                    <span class="absolute left-2 text-slate-400 pointer-events-none text-[9px]">▼</span>
                </div>

                {{-- ڕۆیشتن بۆ هەفتەی دواتر --}}
                <button type="button" @click="changeWeekOffset(1)"
                        title="هەفتەی دواتر"
                        class="w-7 h-7 rounded-lg bg-white hover:bg-slate-100 text-slate-700 border border-slate-200 flex items-center justify-center font-bold text-xs shadow-2xs transition-all cursor-pointer">
                    →
                </button>

                {{-- بەرواری ئەم ماوەیە --}}
                <div class="flex items-center whitespace-nowrap font-mono text-[11px] font-bold text-teal-950 bg-white px-2.5 py-1 rounded-xl border border-slate-200 shadow-2xs shrink-0 select-text" dir="ltr">
                    <span>{{ str_replace('-', '/', $from) }}</span>
                    <span class="text-teal-600 font-bold mx-1 text-[10px]">تا</span>
                    <span>{{ str_replace('-', '/', $to) }}</span>
                </div>
            </div>
        </div>

        {{-- لای چەپ: دوگمەکانی کردار (وەستای نوێ، سێتینگ) --}}
        <div class="flex items-center gap-2 shrink-0">

            {{-- زیادکردنی وەستا --}}
            <button type="button" @click="openNewEmployeeModal()"
                    class="px-3 py-2 rounded-xl text-xs font-black bg-teal-800 hover:bg-teal-900 text-white shadow-2xs flex items-center gap-1 transition-all cursor-pointer">
                <span>+</span>
                <span>وەستای نوێ</span>
            </button>

            {{-- سێتینگ --}}
            <button type="button" @click="showSettingsModal = true"
                    class="p-2 rounded-xl text-slate-600 hover:bg-slate-100 bg-slate-50 border border-slate-200 transition-all cursor-pointer text-sm shadow-2xs"
                    title="سێتینگی تاخیربوون و دەوام">
                ⚙️
            </button>
        </div>
    </div>

    {{-- جەدوەلی سەحی ڕۆژانە --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs overflow-hidden">
        
        {{-- بارێکی باریک بۆ گەڕان و هێماکان --}}
        <div class="px-3.5 py-2.5 border-b border-slate-200 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-2.5 bg-slate-50/60">
            <div class="flex items-center gap-2 flex-1 max-w-xs">
                <input type="text" x-model="searchQuery" placeholder="🔍 گەڕان بە ناوی وەستا..."
                       class="w-full text-xs px-3 py-1.5 rounded-lg border border-slate-200 focus:outline-hidden focus:border-teal-600 bg-white font-medium shadow-2xs">
            </div>

            <div class="flex items-center gap-3 text-[11px] font-bold text-slate-500 flex-wrap">
                <span class="flex items-center gap-1 text-emerald-700"><span class="w-2 h-2 rounded-full bg-emerald-500"></span> هاتووە</span>
                <span class="flex items-center gap-1 text-amber-700"><span class="w-2 h-2 rounded-full bg-amber-500"></span> نیو ڕۆژ</span>
                <span class="flex items-center gap-1 text-rose-700"><span class="w-2 h-2 rounded-full bg-rose-500"></span> نەهاتووە</span>
                <span class="text-slate-400 font-normal hidden sm:inline">| کلیک لەسەر خانە بکە بۆ گۆڕینی دۆخ</span>
            </div>
        </div>

        {{-- خشتەی سەرەکی بە شێوازێکی زۆر ڕێک و ڕیسپۆنسیڤ --}}
        <div class="overflow-x-auto">
            <table class="w-full min-w-[760px] text-right border-collapse text-xs table-fixed">
                <thead>
                    <tr class="bg-slate-100/90 text-slate-700 font-black border-b border-slate-200 text-center">
                        {{-- ژمارەی ڕیزبەند (#) --}}
                        <th class="py-3 px-2 text-center sticky right-0 bg-slate-100 z-20 w-12 min-w-[48px] border-l border-slate-200 font-mono text-slate-600 font-black">
                            #
                        </th>

                        {{-- ناو (لە ناوەڕاست) --}}
                        <th class="py-3 px-3 text-center sticky bg-slate-100 z-20 w-36 sm:w-44 min-w-[130px] border-l border-slate-200 font-black text-slate-700" style="right: 48px;">
                            ناو
                        </th>

                        {{-- ڕۆژەکان (شەممە تا هەینی) بە قەبارەی یەکسان، فراوان و بێ بەریەککەوتن --}}
                        @foreach($days as $d)
                            <th class="py-2.5 px-1 w-20 min-w-[70px] border-l border-slate-200 {{ $d['is_today'] ? 'bg-amber-100/80 text-amber-950 font-black ring-1 ring-amber-300 ring-inset' : ($d['is_holiday'] ? 'bg-slate-200/50 text-slate-500' : '') }}">
                                <div class="flex flex-col items-center leading-tight whitespace-nowrap">
                                    <span class="text-[11px] sm:text-xs font-black">{{ $d['day_name'] }}</span>
                                    <span class="text-[10px] font-mono font-bold opacity-75 mt-0.5">{{ $d['day_short'] }}</span>
                                    @if($d['is_today'])
                                        <span class="text-[9px] font-black px-1.5 py-0.2 mt-0.5 rounded bg-amber-500 text-white leading-none">ئەمڕۆ</span>
                                    @endif
                                </div>
                            </th>
                        @endforeach

                        {{-- کردارەکان بە قەبارەی کەم و ئایکۆن --}}
                        <th class="py-3 px-2 w-28 sm:w-32 min-w-[100px] text-center print:hidden">کردارەکان</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    <template x-for="(row, index) in filteredEmployees" :key="row.id">
                        <tr class="hover:bg-slate-50/80 transition-colors group">
                            
                            {{-- ژمارەی ڕیزبەند --}}
                            <td class="py-2.5 px-2 text-center sticky right-0 bg-white group-hover:bg-slate-50 z-10 border-l border-slate-200 font-mono font-bold text-slate-500 text-xs">
                                <span x-text="index + 1"></span>
                            </td>

                            {{-- ناوی وەستا (لە ناوەڕاست) --}}
                            <td class="py-2.5 px-3 text-center sticky bg-white group-hover:bg-slate-50 z-10 border-l border-slate-200" style="right: 48px;">
                                <div class="cursor-pointer overflow-hidden text-center" @click="openEmployeeDrawer(row)">
                                    <span class="font-black text-slate-900 hover:text-teal-700 text-xs sm:text-sm truncate block text-center" x-text="row.name"></span>
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

                            {{-- کردارەکان: بەس ئایکۆن بۆ ئەوەی جێ نەگرێت --}}
                            <td class="py-2 px-2 text-center print:hidden">
                                <div class="flex items-center justify-center gap-1">
                                    <button type="button" @click="openEmployeeDrawer(row)"
                                            class="w-7 h-7 rounded-lg text-xs font-bold bg-teal-50 hover:bg-teal-100 text-teal-800 border border-teal-200 transition-all cursor-pointer flex items-center justify-center shadow-2xs"
                                            title="بینینی وردەکاری و حیساباتی خۆکار">
                                        👁️
                                    </button>
                                    <button type="button" @click="openEditWageModal(row)"
                                            class="w-7 h-7 rounded-lg text-xs font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 transition-all cursor-pointer flex items-center justify-center shadow-2xs"
                                            title="دەستکاری مووچە و پیشەی وەستا">
                                        ✏️
                                    </button>
                                    <button type="button" @click="confirmDeleteEmployee(row)"
                                            class="w-7 h-7 rounded-lg text-xs font-bold bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 transition-all cursor-pointer flex items-center justify-center shadow-2xs"
                                            title="سڕینەوەی ئەم وەستایە">
                                        🗑️
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    {{-- ٤. بەشی وردەکاری تەواوی وەستا و حیساباتی خۆکار (Worker Details Drawer/Modal) --}}
    <div x-show="showEmployeeDrawer" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4">
        <div @click.away="if (!showPaymentModal && !showEditWageModal && !showCellModal) showEmployeeDrawer = false" class="bg-white rounded-3xl w-full max-w-3xl max-h-[90vh] flex flex-col shadow-2xl border border-slate-200 overflow-hidden">
            
            {{-- سەرپەڕە --}}
            <div class="p-4 sm:p-5 bg-teal-800 text-white flex items-center justify-between gap-3 shrink-0">
                <div class="flex items-center gap-2">
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="text-base sm:text-lg font-black text-white" x-text="selectedEmployee?.name"></h2>
                            <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-teal-700 text-teal-200" x-text="selectedEmployee?.job_title_label"></span>
                        </div>
                        <div class="text-xs text-teal-200 font-mono mt-0.5 flex items-center gap-1.5 flex-wrap">
                            <span x-text="selectedEmployee?.salary_type === 'monthly' ? 'مووچەی مانگانە:' : (selectedEmployee?.salary_type === 'weekly' ? 'مووچەی حەفتانە:' : 'مووچەی ڕۆژانە:')"></span>
                            <b class="text-white font-bold text-sm" x-text="formatNumber(selectedEmployee?.daily_wage) + ' د.ع'"></b>
                            <span class="text-[10px] bg-teal-900/80 text-teal-200 px-1.5 py-0.5 rounded font-bold" x-text="selectedEmployee?.salary_type_label || (selectedEmployee?.salary_type === 'monthly' ? 'مانگانە' : (selectedEmployee?.salary_type === 'weekly' ? 'حەفتانە' : 'ڕۆژانە'))"></span>
                            <template x-if="selectedEmployee?.salary_type === 'monthly' || selectedEmployee?.salary_type === 'weekly'">
                                <span class="text-[11px] text-teal-300 font-normal">
                                    (ڕۆژانەی هاوتا: <span class="font-bold text-white" x-text="formatNumber(selectedEmployee?.effective_daily_wage)"></span> د.ع)
                                </span>
                            </template>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <button type="button" @click.stop="openPaymentModal(selectedEmployee)"
                            class="px-3.5 py-1.5 rounded-xl text-xs font-black bg-emerald-500 hover:bg-emerald-600 text-white shadow-md cursor-pointer flex items-center gap-1.5 transition-all">
                        <span>💸</span>
                        <span>پێدانی پارە</span>
                    </button>

                    <div class="flex items-center gap-1.5 bg-teal-900/90 px-2.5 py-1 rounded-xl border border-teal-700">
                        <span class="text-[11px] text-teal-200 font-bold">مانگ:</span>
                        <input type="month" x-model="selectedMonth" @change="loadEmployeeMonthDetails()"
                               class="bg-transparent text-white text-xs font-mono font-bold cursor-pointer focus:outline-hidden">
                    </div>
                    <button type="button" @click="showEmployeeDrawer = false" class="text-teal-200 hover:text-white text-xl font-bold p-1 leading-none cursor-pointer">✕</button>
                </div>
            </div>

            {{-- چوار کارتی حیساباتی خۆکار --}}
            <div class="p-3.5 bg-slate-50 border-b border-slate-200 shrink-0">
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-center text-xs">
                    {{-- ١. ڕۆژانی دەوام --}}
                    <div class="bg-white p-2.5 rounded-xl border border-slate-200 shadow-2xs">
                        <div class="text-[10px] text-slate-500 font-bold">ڕۆژانی هاتوو لە مانگدا</div>
                        <div class="text-base font-black text-emerald-800 font-mono mt-0.5" x-text="(drawerData?.stats?.present_count ?? 0) + ' ڕۆژ'"></div>
                        <template x-if="(drawerData?.stats?.half_day_count ?? 0) > 0">
                            <div class="text-[10px] text-amber-700 font-bold mt-0.5" x-text="'+ ' + drawerData.stats.half_day_count + ' نیوە'"></div>
                        </template>
                    </div>

                    {{-- ٢. پارەی هەیە (شایستە) --}}
                    <div class="bg-white p-2.5 rounded-xl border border-teal-200 shadow-2xs">
                        <div class="text-[10px] text-teal-800 font-bold">پارەی هەیە (شایستە)</div>
                        <div class="text-base font-black text-teal-950 font-mono mt-0.5" x-text="formatNumber(drawerData?.stats?.total_earned ?? 0) + ' د.ع'"></div>
                        <div class="text-[9px] text-slate-400 mt-0.5">حەقدەست + زیادە - سزا</div>
                    </div>

                    {{-- ٣. چەند براوە (پێشەکی) --}}
                    <div class="bg-white p-2.5 rounded-xl border border-purple-200 shadow-2xs">
                        <div class="text-[10px] text-purple-800 font-bold">چەند براوە (پێشەکی)</div>
                        <div class="text-base font-black text-purple-950 font-mono mt-0.5" x-text="formatNumber(drawerData?.stats?.total_paid ?? 0) + ' د.ع'"></div>
                        <div class="text-[9px] text-slate-400 mt-0.5">لە قاصەوە دراوە</div>
                    </div>

                    {{-- ٤. باڵانسی ماوە --}}
                    <div class="bg-white p-2.5 rounded-xl border border-amber-300 shadow-2xs">
                        <div class="text-[10px] text-amber-800 font-bold">باڵانسی ماوە لای کارگە</div>
                        <div class="text-base font-black font-mono mt-0.5"
                             :class="(drawerData?.stats?.remaining_balance ?? 0) > 0 ? 'text-amber-900' : 'text-slate-600'"
                             x-text="formatNumber(drawerData?.stats?.remaining_balance ?? 0) + ' د.ع'"></div>
                        <div class="text-[9px] text-slate-400 mt-0.5">ماوە بۆی بدرێت</div>
                    </div>
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
                    <div class="border border-slate-200 rounded-2xl overflow-hidden shadow-2xs bg-white">
                        <table class="w-full text-right">
                            <thead class="bg-slate-100/80 text-slate-700 font-black border-b border-slate-200 text-xs">
                                <tr>
                                    <th class="py-2.5 px-3 text-right">ڕۆژ و بەروار</th>
                                    <th class="py-2.5 px-3 text-center">دۆخی دەوام</th>
                                    <th class="py-2.5 px-3 text-center">کاتی زیادە</th>
                                    <th class="py-2.5 px-3 text-right">تێبینی / خەرجی</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 font-medium">
                                <template x-for="item in (drawerData?.attendances || [])" :key="item.id">
                                    <tr class="hover:bg-slate-50/80 transition-colors">
                                        <td class="py-2.5 px-3 text-right">
                                            <span class="font-bold text-slate-800 text-xs" x-text="item.day_name || ''"></span>
                                            <span class="text-[11px] font-mono text-slate-400 mr-1.5" x-text="item.work_date"></span>
                                        </td>
                                        <td class="py-2.5 px-3 text-center">
                                            <span class="px-2.5 py-0.5 rounded-md text-[11px] font-black inline-block shadow-2xs"
                                                  :class="item.status === 'present' ? 'bg-emerald-600 text-white' : (item.status === 'half_day' ? 'bg-amber-500 text-white' : 'bg-rose-500 text-white')"
                                                  x-text="item.status_label"></span>
                                        </td>
                                        <td class="py-2.5 px-3 text-center font-mono text-xs">
                                            <template x-if="item.overtime_hours > 0">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-md font-black bg-blue-50 text-blue-700 border border-blue-200"
                                                      x-text="'+ ' + item.overtime_hours + ' کاتژمێر'"></span>
                                            </template>
                                            <template x-if="!item.overtime_hours || item.overtime_hours <= 0">
                                                <span class="text-slate-300 font-bold">—</span>
                                            </template>
                                        </td>
                                        <td class="py-2.5 px-3 text-right text-xs">
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <template x-if="item.fuel_expense > 0">
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-bold bg-purple-50 text-purple-700 border border-purple-200"
                                                          x-text="'بەنزین: ' + formatNumber(item.fuel_expense) + ' د.ع'"></span>
                                                </template>
                                                <template x-if="item.note">
                                                    <span class="text-slate-600 font-medium" x-text="item.note"></span>
                                                </template>
                                                <template x-if="(!item.fuel_expense || item.fuel_expense <= 0) && !item.note">
                                                    <span class="text-slate-300 font-bold">—</span>
                                                </template>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                                <template x-if="!drawerData?.attendances || drawerData.attendances.length === 0">
                                    <tr>
                                        <td colspan="4" class="p-4 text-center text-slate-400 font-bold">هیچ تۆمارێکی دەوام بۆ ئەم مانگە نییە.</td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- وەسڵەکانی پێشەکی --}}
                <div>
                    <div class="mb-2">
                        <h3 class="font-black text-slate-800 flex items-center gap-1.5">
                            <span>💸</span>
                            <span>وەسڵەکانی پارەدان و پێشەکی لە قاصە</span>
                        </h3>
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
            <div class="p-3.5 bg-slate-50 border-t border-slate-200 flex items-center justify-end shrink-0">
                <button type="button" @click="showEmployeeDrawer = false" class="px-5 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-200 cursor-pointer transition-all">داخستن</button>
            </div>

        </div>
    </div>

    {{-- ٥. مۆداڵی سێتینگی کارگە (Settings Modal) --}}
    <div x-show="showSettingsModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-3">
        <div @click.away="showSettingsModal = false" class="bg-white rounded-3xl w-full max-w-md shadow-2xl border border-slate-200 overflow-hidden text-xs">
            
            <form @submit.prevent="saveSettings()">
                <div class="p-4 bg-teal-800 text-white flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-black text-white">سێتینگی دەوام و کاتی زیادە</h2>
                        <p class="text-[11px] text-teal-200">دیاریکردنی کاتژمێری فەرمی و نرخی کاتی زیادە</p>
                    </div>
                    <button type="button" @click="showSettingsModal = false" class="text-teal-200 hover:text-white text-lg font-bold cursor-pointer">✕</button>
                </div>

                <div class="p-5 space-y-4 font-bold text-slate-700">
                    {{-- کاتژمێری فەرمی --}}
                    <div>
                        <label class="block mb-1.5 text-slate-700 font-black">کاتژمێری فەرمی ڕۆژانە *</label>
                        <div class="flex items-stretch rounded-xl border border-slate-200 overflow-hidden focus-within:border-teal-600 focus-within:ring-2 focus-within:ring-teal-500/20 bg-white transition-all shadow-2xs">
                            <input type="number" step="0.5" min="1" max="24" x-model="settingsForm.workshop_work_hours" required
                                   class="w-full px-3.5 py-2.5 font-mono font-bold text-slate-800 focus:outline-hidden text-xs bg-transparent">
                            <span class="bg-slate-100 px-3.5 flex items-center text-xs font-bold text-slate-600 border-r border-slate-200 shrink-0">
                                کاتژمێر
                            </span>
                        </div>
                        <p class="text-[11px] text-slate-400 font-medium mt-1.5 flex items-center gap-1">
                            <span>ℹ️</span>
                            <span>کاتی فەرمی ڕۆژانەی کارکردنە</span>
                        </p>
                    </div>

                    {{-- نرخی کاتی زیادە --}}
                    <div>
                        <label class="block mb-1.5 text-slate-700 font-black">نرخی هەر کاتژمێرێکی کاتی زیادە (د.ع) *</label>
                        <div class="flex items-stretch rounded-xl border border-slate-200 overflow-hidden focus-within:border-teal-600 focus-within:ring-2 focus-within:ring-teal-500/20 bg-white transition-all shadow-2xs">
                            <input type="text" inputmode="numeric" x-model="settingsForm.workshop_overtime_hourly_rate"
                                   @input="settingsForm.workshop_overtime_hourly_rate = formatMoneyInput($event.target.value)"
                                   placeholder="0"
                                   class="w-full px-3.5 py-2.5 font-mono font-black text-emerald-700 focus:outline-hidden text-sm bg-transparent">
                            <span class="bg-slate-100 px-3.5 flex items-center text-xs font-black text-slate-600 border-r border-slate-200 shrink-0">
                                د.ع
                            </span>
                        </div>
                        <p class="text-[11px] text-slate-500 font-medium mt-1.5 flex items-center gap-1 leading-relaxed">
                            <span>💡</span>
                            <span>ئەگەر لە کاتی فەرمی زیاتر بمێنێتەوە، بۆ هەر کاتژمێرێک ئەم بڕەی بۆ هەژمار دەکرێت</span>
                        </p>
                    </div>

                    {{-- پشووی هەفتانە --}}
                    <div>
                        <label class="block mb-1.5 text-slate-700 font-black">ڕۆژی پشووی هەفتانە</label>
                        <select x-model="settingsForm.workshop_weekly_holiday" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 font-bold bg-white focus:outline-hidden focus:border-teal-600 focus:ring-2 focus:ring-teal-500/20 shadow-2xs">
                            <option value="friday">هەینی</option>
                            <option value="saturday">شەممە</option>
                            <option value="thursday">پێنجشەممە</option>
                            <option value="none">بێ پشوو</option>
                        </select>
                    </div>
                </div>

                <div class="p-3.5 bg-slate-50 border-t border-slate-200 flex items-center justify-end gap-2">
                    <button type="button" @click="showSettingsModal = false" class="px-4 py-2 rounded-xl text-slate-600 hover:bg-slate-200 font-bold cursor-pointer transition-all">داخستن</button>
                    <button type="submit" class="px-5 py-2 rounded-xl font-black bg-teal-700 text-white hover:bg-teal-800 shadow-2xs cursor-pointer transition-all">
                        پاشەکەوتکردن
                    </button>
                </div>
            </form>

        </div>
    </div>

    {{-- ٦. مۆداڵی پێدانی پارە و شایستە (Payment Modal) --}}
    <div x-show="showPaymentModal" x-cloak class="fixed inset-0 z-60 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-3">
        <div @click.away="showPaymentModal = false" class="bg-white rounded-3xl w-full max-w-sm shadow-2xl border border-slate-200 overflow-hidden text-xs">
            <form @submit.prevent="savePayment()">
                <div class="p-4 bg-teal-800 text-white flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-black text-white" x-text="'پێدانی پارە بە: ' + (paymentEmployee?.name || '')"></h2>
                        <p class="text-[11px] text-teal-200">دانی پارەی شایستە (حەقی خۆی) یان پێشەکی لە قاصەوە</p>
                    </div>
                    <button type="button" @click="showPaymentModal = false" class="text-teal-200 hover:text-white text-lg font-bold">✕</button>
                </div>

                <div class="p-4 space-y-3 font-bold text-slate-700">
                    {{-- کورتەی حەقی شایستە و باڵانسی ماوە --}}
                    <div class="bg-slate-50 p-2.5 rounded-2xl border border-slate-200 space-y-1.5">
                        <div class="flex items-center justify-between text-[11px]">
                            <span class="text-slate-500">کۆی شایستە (حەقی کارکردن):</span>
                            <span class="font-mono font-black text-slate-800" x-text="formatNumber(drawerData?.stats?.total_earned ?? paymentEmployee?.total_earned ?? 0) + ' د.ع'"></span>
                        </div>
                        <div class="flex items-center justify-between text-[11px]">
                            <span class="text-amber-700">باڵانسی ماوە لای کارگە:</span>
                            <span class="font-mono font-black text-amber-900" x-text="formatNumber(drawerData?.stats?.remaining_balance ?? paymentEmployee?.remaining_balance ?? 0) + ' د.ع'"></span>
                        </div>
                        <template x-if="(drawerData?.stats?.remaining_balance ?? paymentEmployee?.remaining_balance ?? 0) > 0">
                            <button type="button" @click="setFullDuePayment()"
                                    class="w-full mt-1 py-1.5 px-2.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-[11px] flex items-center justify-between shadow-2xs transition-colors cursor-pointer">
                                <span>💰 دانانی تەواوی حەقی شایستەی ماوە:</span>
                                <span class="font-mono font-black" x-text="formatNumber(drawerData?.stats?.remaining_balance ?? paymentEmployee?.remaining_balance ?? 0) + ' د.ع'"></span>
                            </button>
                        </template>
                    </div>

                    <div>
                        <label class="block mb-1 text-slate-600">جۆری پارەدان</label>
                        <select x-model="paymentForm.payment_type" @change="onPaymentTypeChange()"
                                class="w-full px-3 py-2 rounded-xl border border-slate-200 font-bold bg-white focus:outline-hidden focus:border-teal-600">
                            <option value="wage">دانی حەقی خۆی (شایستە / مووچە)</option>
                            <option value="advance">پێشەکی</option>
                        </select>
                    </div>

                    <div>
                        <label class="block mb-1 text-slate-600">بڕی پارە (د.ع) *</label>
                        <input type="text" inputmode="numeric" x-model="paymentForm.amount"
                               @input="paymentForm.amount = formatMoneyInput($event.target.value)" required
                               placeholder="بڕی پارە بە دینار"
                               class="w-full px-3 py-2 rounded-xl border border-slate-200 font-mono font-black text-base text-slate-900 focus:outline-hidden focus:border-teal-600">
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

                <div class="p-3.5 bg-slate-50 border-t border-slate-200 flex items-center justify-end gap-2">
                    <button type="button" @click="showPaymentModal = false" class="px-4 py-1.5 rounded-xl text-slate-600 hover:bg-slate-200 font-bold">داخستن</button>
                    <button type="submit" class="px-5 py-1.5 rounded-xl font-black bg-teal-700 text-white hover:bg-teal-800 shadow-2xs">
                        تۆمارکردنی پارەدان
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
                               placeholder="ناوی سیانی وەستا یان کرێکار"
                               class="w-full px-3 py-2 rounded-xl border border-slate-200 font-bold focus:outline-hidden focus:border-teal-600">
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block mb-1 text-slate-600">پیشە</label>
                            <select x-model="newEmpForm.job_title" class="w-full px-3 py-2 rounded-xl border border-slate-200 font-bold bg-white focus:outline-hidden focus:border-teal-600">
                                <option value="master">وەستا</option>
                                <option value="porter">حەمەڵ</option>
                                <option value="helper">یاریدەدەر</option>
                                <option value="driver">شۆفێر</option>
                                <option value="other">پیشەی تر</option>
                            </select>
                        </div>
                        <div>
                            <label class="block mb-1 text-slate-600">شێوازی مووچە</label>
                            <select x-model="newEmpForm.salary_type" class="w-full px-3 py-2 rounded-xl border border-slate-200 font-bold bg-white focus:outline-hidden focus:border-teal-600">
                                <option value="daily">ڕۆژانە</option>
                                <option value="weekly">حەفتانە</option>
                                <option value="monthly">مانگانە</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block mb-1 text-slate-600"
                                   x-text="newEmpForm.salary_type === 'monthly' ? 'مووچەی مانگانە *' : (newEmpForm.salary_type === 'weekly' ? 'مووچەی حەفتانە *' : 'مووچەی ڕۆژانە *')"></label>
                            <input type="text" inputmode="numeric" x-model="newEmpForm.daily_wage"
                                   @input="newEmpForm.daily_wage = formatMoneyInput($event.target.value)" required
                                   :placeholder="newEmpForm.salary_type === 'monthly' ? 'بڕی مانگانە' : (newEmpForm.salary_type === 'weekly' ? 'بڕی حەفتانە' : 'بڕی ڕۆژانە')"
                                   class="w-full px-3 py-2 rounded-xl border border-slate-200 font-mono font-bold focus:outline-hidden focus:border-teal-600">
                        </div>
                        <div>
                            <label class="block mb-1 text-slate-600">مۆبایل</label>
                            <input type="text" x-model="newEmpForm.phone" placeholder="0750xxxxxxx"
                                   class="w-full px-3 py-2 rounded-xl border border-slate-200 font-mono font-bold focus:outline-hidden focus:border-teal-600">
                        </div>
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
                        <p class="text-[11px] text-teal-200">گۆڕینی مووچە و شێوازی پارەدان</p>
                    </div>
                    <button type="button" @click="showEditWageModal = false" class="text-teal-200 hover:text-white text-lg font-bold">✕</button>
                </div>

                <div class="p-4 space-y-3 font-bold text-slate-700">
                    <div>
                        <label class="block mb-1 text-slate-600">ناو *</label>
                        <input type="text" x-model="editWageForm.name" required
                               class="w-full px-3 py-2 rounded-xl border border-slate-200 font-bold focus:outline-hidden focus:border-teal-600">
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block mb-1 text-slate-600">پیشە</label>
                            <select x-model="editWageForm.job_title" class="w-full px-3 py-2 rounded-xl border border-slate-200 font-bold bg-white focus:outline-hidden focus:border-teal-600">
                                <option value="master">وەستا</option>
                                <option value="porter">حەمەڵ</option>
                                <option value="helper">یاریدەدەر</option>
                                <option value="driver">شۆفێر</option>
                                <option value="other">پیشەی تر</option>
                            </select>
                        </div>
                        <div>
                            <label class="block mb-1 text-slate-600">شێوازی مووچە</label>
                            <select x-model="editWageForm.salary_type" class="w-full px-3 py-2 rounded-xl border border-slate-200 font-bold bg-white focus:outline-hidden focus:border-teal-600">
                                <option value="daily">ڕۆژانە</option>
                                <option value="weekly">حەفتانە</option>
                                <option value="monthly">مانگانە</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block mb-1 text-slate-600"
                                   x-text="editWageForm.salary_type === 'monthly' ? 'مووچەی مانگانە *' : (editWageForm.salary_type === 'weekly' ? 'مووچەی حەفتانە *' : 'مووچەی ڕۆژانە *')"></label>
                            <input type="text" inputmode="numeric" x-model="editWageForm.daily_wage"
                                   @input="editWageForm.daily_wage = formatMoneyInput($event.target.value)" required
                                   class="w-full px-3 py-2 rounded-xl border border-slate-200 font-mono font-bold focus:outline-hidden focus:border-teal-600">
                        </div>
                        <div>
                            <label class="block mb-1 text-slate-600">مۆبایل</label>
                            <input type="text" x-model="editWageForm.phone"
                                   class="w-full px-3 py-2 rounded-xl border border-slate-200 font-mono font-bold focus:outline-hidden focus:border-teal-600">
                        </div>
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
                        <select x-model="cellForm.status" class="w-full px-3 py-2 rounded-xl border border-slate-200 font-bold bg-white focus:outline-hidden focus:border-teal-600">
                            <option value="present">هاتووە</option>
                            <option value="half_day">نیو ڕۆژ</option>
                            <option value="absent">نەهاتووە</option>
                            <option value="delete">سڕینەوە (خاڵی)</option>
                        </select>
                    </div>

                    {{-- کاتی زیادە و سەرفیات (بەنزین) --}}
                    <div class="grid grid-cols-2 gap-2.5">
                        <div>
                            <label class="block mb-1 text-slate-600 font-bold">کاتی زیادە</label>
                            <div class="flex items-stretch rounded-xl border border-slate-200 overflow-hidden focus-within:border-teal-600 bg-white">
                                <input type="number" step="0.5" min="0" x-model="cellForm.overtime_hours"
                                       placeholder="0"
                                       class="w-full px-2.5 py-2 font-mono font-black text-blue-700 text-xs focus:outline-hidden">
                                <span class="bg-slate-100 px-2 flex items-center text-[11px] font-bold text-slate-500 border-r border-slate-200 shrink-0">ک</span>
                            </div>
                        </div>
                        <div>
                            <label class="block mb-1 text-slate-600 font-bold">سەرفیات (بەنزین)</label>
                            <div class="flex items-stretch rounded-xl border border-slate-200 overflow-hidden focus-within:border-teal-600 bg-white">
                                <input type="text" inputmode="numeric" x-model="cellForm.fuel_expense"
                                       @input="cellForm.fuel_expense = formatMoneyInput($event.target.value)"
                                       placeholder="0"
                                       class="w-full px-2.5 py-2 font-mono font-black text-purple-900 text-xs focus:outline-hidden">
                                <span class="bg-slate-100 px-2 flex items-center text-[11px] font-bold text-slate-500 border-r border-slate-200 shrink-0">د.ع</span>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block mb-1 text-slate-600 font-bold">تێبینی</label>
                        <input type="text" x-model="cellForm.note" placeholder="تێبینی بۆ ئەم ڕۆژە..."
                               class="w-full px-3 py-2 rounded-xl border border-slate-200 font-medium text-xs focus:outline-hidden focus:border-teal-600">
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
            workshop_work_hours: {{ $shiftSettings['work_hours'] }},
            workshop_overtime_hourly_rate: {!! json_encode(($shiftSettings['overtime_hourly_rate'] ?? 0) > 0 ? $shiftSettings['overtime_hourly_rate'] : '') !!},
            workshop_weekly_holiday: '{{ $shiftSettings['weekly_holiday'] }}'
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
            return 'text-slate-300 border-slate-200';
        },

        getCellDisplay(cell) {
            if (!cell || !cell.status) return '—';
            if (cell.status === 'present') return 'هاتووە';
            if (cell.status === 'half_day') return 'نیو ڕۆژ';
            if (cell.status === 'absent') return 'نەهاتووە';
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
                overtime_hours: existing?.overtime_hours > 0 ? existing.overtime_hours : '',
                fuel_expense: existing?.fuel_expense > 0 ? this.formatMoneyInput(existing.fuel_expense) : '',
                note: existing?.note || ''
            };

            this.showCellModal = true;
        },

        async saveCellDetail() {
            try {
                const payload = {
                    employee_id: this.cellForm.employee_id,
                    work_date: this.cellForm.work_date,
                    status: this.cellForm.status,
                    overtime_hours: parseFloat(this.cellForm.overtime_hours) || 0,
                    fuel_expense: this.cleanMoney(this.cellForm.fuel_expense),
                    note: this.cellForm.note || ''
                };

                const res = await fetch('{{ route('workshop.employees.save-cell-detail') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });

                const data = await res.json();
                if (data.ok) {
                    const row = this.matrix.find(r => r.id === this.cellForm.employee_id);
                    if (row) {
                        row.cells[this.cellForm.work_date] = data.attendance;
                        this.recalculateRow(row);
                    }
                    if (this.showEmployeeDrawer && this.selectedEmployee?.id === this.cellForm.employee_id) {
                        this.loadEmployeeMonthDetails();
                    }
                    this.showCellModal = false;
                } else if (data.message) {
                    alert(data.message);
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
            this.drawerLoading = true;
            try {
                const res = await fetch(`/workshop/employees/${this.selectedEmployee.id}/month-details?month=${this.selectedMonth}`, {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await res.json();
                if (data.ok) {
                    this.drawerData = data;
                    if (data.employee) {
                        this.selectedEmployee = { ...this.selectedEmployee, ...data.employee };
                    }
                }
            } catch (e) {
                console.error(e);
            } finally {
                this.drawerLoading = false;
            }
        },

        async confirmDeleteEmployee(row) {
            if (!confirm(`ئایا دڵنیایت دەتەوێت وەستا (${row.name}) بە تەواوی بسڕیتەوە لە سیستەم؟`)) return;
            try {
                const res = await fetch(`/workshop/employees/${row.id}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });

                const data = await res.json();
                if (data.ok) {
                    this.matrix = this.matrix.filter(e => e.id !== row.id);
                    alert(data.message);
                } else {
                    alert(data.message || 'هەڵە لە سڕینەوەدا');
                }
            } catch (e) {
                alert('هەڵە لە سڕینەوەدا');
            }
        },

        openPaymentModal(row) {
            this.paymentEmployee = row;
            const remaining = this.drawerData?.stats?.remaining_balance ?? row?.remaining_balance ?? 0;
            const hasRemaining = remaining > 0;

            this.paymentForm = {
                employee_id: row.id,
                payment_type: hasRemaining ? 'wage' : 'advance',
                amount: hasRemaining ? this.formatMoneyInput(remaining) : '',
                currency: row.wage_currency || 'IQD',
                cash_box_id: '{{ $cashBoxes->first()?->id }}',
                paid_at: '{{ now()->toDateString() }}',
                note: hasRemaining ? `حەقی شایستەی ${row.name}` : `پێشەکی ${row.name}`
            };
            this.showPaymentModal = true;
        },

        setFullDuePayment() {
            const remaining = this.drawerData?.stats?.remaining_balance ?? this.paymentEmployee?.remaining_balance ?? 0;
            if (remaining > 0) {
                this.paymentForm.amount = this.formatMoneyInput(remaining);
                this.paymentForm.payment_type = 'wage';
                this.paymentForm.note = `حەقی شایستەی ${this.paymentEmployee?.name || ''}`;
            }
        },

        onPaymentTypeChange() {
            if (this.paymentForm.payment_type === 'wage') {
                this.paymentForm.note = `حەقی شایستەی ${this.paymentEmployee?.name || ''}`;
            } else {
                this.paymentForm.note = `پێشەکی ${this.paymentEmployee?.name || ''}`;
            }
        },

        async savePayment() {
            try {
                const payload = {
                    ...this.paymentForm,
                    amount: this.cleanMoney(this.paymentForm.amount)
                };
                const res = await fetch('{{ route('workshop.employees.record-payment') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });

                const data = await res.json();
                if (data.ok) {
                    alert(data.message);
                    const row = this.matrix.find(r => r.id === this.paymentForm.employee_id);
                    if (row) {
                        row.total_paid += parseFloat(payload.amount);
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
                const payload = {
                    ...this.newEmpForm,
                    daily_wage: this.cleanMoney(this.newEmpForm.daily_wage)
                };
                const res = await fetch('{{ route('workshop.employees.quick-store') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
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
                daily_wage: this.formatMoneyInput(row.daily_wage)
            };
            this.showEditWageModal = true;
        },

        async saveEditWage() {
            try {
                const payload = {
                    ...this.editWageForm,
                    daily_wage: this.cleanMoney(this.editWageForm.daily_wage)
                };
                const res = await fetch(`/workshop/employees/${this.editWageForm.id}/update-wage`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });

                const data = await res.json();
                if (data.ok) {
                    const row = this.matrix.find(r => r.id === this.editWageForm.id);
                    if (row) {
                        row.name = this.editWageForm.name;
                        row.phone = this.editWageForm.phone;
                        row.job_title = this.editWageForm.job_title;
                        row.job_title_label = data.job_title_label;
                        row.salary_type = this.editWageForm.salary_type;
                        row.salary_type_label = data.salary_type_label || (this.editWageForm.salary_type === 'monthly' ? 'مانگانە' : (this.editWageForm.salary_type === 'weekly' ? 'حەفتانە' : 'ڕۆژانە'));
                        row.daily_wage = payload.daily_wage;
                        row.effective_daily_wage = data.effective_daily_wage ?? payload.daily_wage;
                        this.recalculateRow(row);
                    }
                    if (this.showEmployeeDrawer && this.selectedEmployee?.id === this.editWageForm.id) {
                        this.loadEmployeeMonthDetails();
                    }
                    this.showEditWageModal = false;
                }
            } catch (e) {
                alert('هەڵە لە نوێکردنەوەی مووچە');
            }
        },

        async saveSettings() {
            try {
                const payload = { ...this.settingsForm };
                payload.workshop_overtime_hourly_rate = this.cleanMoney(payload.workshop_overtime_hourly_rate);
                const res = await fetch('{{ route('workshop.settings') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
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
            const otHourlyRate = (this.settingsForm.workshop_overtime_hourly_rate > 0)
                ? this.settingsForm.workshop_overtime_hourly_rate
                : (wage / (this.settingsForm.workshop_work_hours || 8));
            const otEarned = ot * otHourlyRate;

            row.total_deductions = manualDeduction;
            row.total_earned = Math.round(baseEarned + otEarned + fuel - manualDeduction);
            row.remaining_balance = Math.round(row.total_earned - row.total_paid);
        },

        formatNumber(num) {
            if (num === null || num === undefined || isNaN(num)) return '0';
            return Number(Math.round(num)).toLocaleString('en-US');
        },

        formatMoneyInput(val) {
            if (val === null || val === undefined || val === '') return '';
            let clean = String(val).replace(/[^0-9]/g, '');
            if (!clean) return '';
            return parseInt(clean, 10).toLocaleString('en-US');
        },

        cleanMoney(val) {
            if (!val) return 0;
            return parseFloat(String(val).replace(/,/g, '')) || 0;
        }
    };
}
</script>
@endsection
