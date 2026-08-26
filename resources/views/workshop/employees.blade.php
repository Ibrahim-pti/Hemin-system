@extends('layouts.menu')
@section('title', 'وەستا و حەمەڵەکان')

@section('content')
<div x-data="workshopEmployeesApp()" class="space-y-4 sm:space-y-6">

    {{-- ١. هێڵی سەرەوە: ناونیشان و فلتەری بەروار --}}
    <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <div class="size-11 sm:size-12 rounded-2xl bg-indigo-50 border border-indigo-100 text-indigo-600 flex items-center justify-center text-xl sm:text-2xl shadow-xs shrink-0">
                👷
            </div>
            <div>
                <h1 class="text-lg sm:text-xl font-black text-slate-800">تۆماری ئامادەبوون و وەستاکانی کارگە</h1>
                <p class="text-xs text-slate-500 mt-0.5 font-medium">
                    چیک ئین و چیک ئاوت، کاتی زیادە، دەرچوونی کاتی لە ناو ئیش و مەسروفی بەنزینی سەر ماڵان
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2.5 sm:gap-3 flex-wrap">
            <form method="GET" action="{{ route('workshop.employees') }}" class="flex items-center gap-2">
                <label class="text-xs font-bold text-slate-600">بەروار:</label>
                <input type="date" name="date" value="{{ $date }}" onchange="this.form.submit()"
                       class="text-xs px-2.5 sm:px-3 py-1.5 rounded-xl border border-slate-200 font-mono font-bold focus:outline-hidden focus:border-blue-500 bg-slate-50">
            </form>

            <div class="flex items-center gap-1.5 text-xs font-bold flex-wrap">
                <span class="px-2 sm:px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200 text-[11px] sm:text-xs">
                    ئامادە: {{ $presentCount }}
                </span>
                <span class="px-2 sm:px-2.5 py-1 rounded-lg bg-amber-50 text-amber-700 border border-amber-200 text-[11px] sm:text-xs">
                    ئیجازە: {{ $leaveCount }}
                </span>
                <span class="px-2 sm:px-2.5 py-1 rounded-lg bg-rose-50 text-rose-700 border border-rose-200 text-[11px] sm:text-xs">
                    نەهاتوو: {{ $absentCount }}
                </span>
            </div>
        </div>
    </div>

    {{-- ٢. لیستی کارتەکانی کارمەندان --}}
    @if ($employees->isEmpty())
        <div class="bg-white rounded-2xl p-8 sm:p-12 text-center border border-slate-200 shadow-xs">
            <div class="text-4xl mb-2.5">👷‍♂️</div>
            <div class="font-bold text-slate-700 text-base">هیچ کارمەند یان وەستایەک تۆمار نەکراوە</div>
            <div class="text-xs text-slate-400 mt-1">بەڕێوەبەر دەتوانێت لە بەشی کارمەندان کارمەندی نوێ زیاد بکات.</div>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3.5 sm:gap-4">
            <template x-for="emp in employeesList" :key="emp.id">
                <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200 shadow-xs flex flex-col justify-between hover:shadow-md transition-all">
                    <div>
                        {{-- بەشی سەرەوەی کارت --}}
                        <div class="flex items-start justify-between gap-2 border-b border-slate-100 pb-3 mb-3">
                            <div class="min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h3 class="font-black text-slate-900 text-sm sm:text-base truncate" x-text="emp.name"></h3>
                                    <span class="px-2 py-0.5 rounded-lg text-[10px] sm:text-[11px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200"
                                          x-text="emp.job_title_label"></span>
                                </div>
                                <div class="text-xs text-slate-500 font-medium mt-1">
                                    <span x-show="emp.phone" class="font-mono text-slate-600" dir="ltr" x-text="emp.phone"></span>
                                    <span x-show="!emp.phone" class="text-slate-400">بێ ژمارە</span>
                                    <span class="text-slate-300 mx-1.5">•</span>
                                    <span>حەقدەست: <span class="font-bold text-slate-800" x-text="emp.daily_wage.toLocaleString() + ' ' + emp.wage_currency"></span></span>
                                </div>
                            </div>

                            {{-- باجی دۆخی ئەمڕۆ --}}
                            <div class="shrink-0">
                                <span x-show="emp.attendance && emp.attendance.status === 'present'"
                                      class="px-2.5 py-1 rounded-full text-[11px] sm:text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200 flex items-center gap-1">
                                    <span class="size-2 rounded-full bg-emerald-500"></span>
                                    <span>ئامادەیە</span>
                                </span>
                                <span x-show="emp.attendance && emp.attendance.status === 'leave'"
                                      class="px-2.5 py-1 rounded-full text-[11px] sm:text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200 flex items-center gap-1">
                                    <span>🏖️</span>
                                    <span>ئیجازە</span>
                                </span>
                                <span x-show="emp.attendance && emp.attendance.status === 'absent'"
                                      class="px-2.5 py-1 rounded-full text-[11px] sm:text-xs font-bold bg-rose-100 text-rose-800 border border-rose-200 flex items-center gap-1">
                                    <span>❌</span>
                                    <span>نەهاتووە</span>
                                </span>
                                <span x-show="emp.attendance && emp.attendance.status === 'holiday'"
                                      class="px-2.5 py-1 rounded-full text-[11px] sm:text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                    پشوو
                                </span>
                                <span x-show="!emp.attendance"
                                      class="px-2.5 py-1 rounded-full text-[11px] sm:text-xs font-semibold bg-slate-100 text-slate-500 border border-slate-200">
                                    تۆمار نەکراوە
                                </span>
                            </div>
                        </div>

                        {{-- کاتی هاتن و چوون (Check-in / Check-out) --}}
                        <div class="grid grid-cols-2 gap-2.5 mb-3 bg-slate-50/80 p-3 rounded-xl border border-slate-100">
                            <div>
                                <div class="text-[10px] sm:text-[11px] font-bold text-slate-400 mb-0.5">کاتی هاتن (Check-in):</div>
                                <div class="text-xs sm:text-sm font-black text-slate-800 font-mono" x-text="(emp.attendance && emp.attendance.check_in) ? emp.attendance.check_in : '—'"></div>
                            </div>
                            <div>
                                <div class="text-[10px] sm:text-[11px] font-bold text-slate-400 mb-0.5">کاتی چوون (Check-out):</div>
                                <div class="text-xs sm:text-sm font-black text-slate-800 font-mono" x-text="(emp.attendance && emp.attendance.check_out) ? emp.attendance.check_out : '—'"></div>
                            </div>
                        </div>

                        {{-- زانیارییە تایبەتەکان (کاتی زیادە، دەرچوون، بەنزین) --}}
                        <div class="space-y-1.5 mb-3 text-xs">
                            {{-- کاتی زیادە / Overtime --}}
                            <div x-show="emp.attendance && emp.attendance.overtime_hours > 0"
                                 class="flex items-center justify-between p-2 rounded-lg bg-blue-50/70 border border-blue-100 text-blue-800 font-medium">
                                <span class="flex items-center gap-1">
                                    <span>⏱️</span>
                                    <span>کاتی زیادە (Overtime):</span>
                                </span>
                                <span class="font-black" x-text="emp.attendance.overtime_hours + ' کاتژمێر'"></span>
                            </div>

                            {{-- دەرچوون لە کاتی کاردا --}}
                            <div x-show="emp.attendance && emp.attendance.temporary_exit_hours > 0"
                                 class="flex items-center justify-between p-2 rounded-lg bg-amber-50/70 border border-amber-100 text-amber-900 font-medium">
                                <span class="flex items-center gap-1">
                                    <span>🚪</span>
                                    <span>دەرچوونی کاتی:</span>
                                    <span class="text-[11px] text-amber-700" x-show="emp.attendance.exit_reason" x-text="'(' + emp.attendance.exit_reason + ')'"></span>
                                </span>
                                <span class="font-black" x-text="emp.attendance.temporary_exit_hours + ' کاتژمێر'"></span>
                            </div>

                            {{-- بەنزین و چوونە سەر ماڵان --}}
                            <div x-show="emp.attendance && (emp.attendance.fuel_expense > 0 || emp.attendance.trip_destination)"
                                 class="flex items-center justify-between p-2 rounded-lg bg-emerald-50/70 border border-emerald-100 text-emerald-900 font-medium">
                                <span class="flex items-center gap-1">
                                    <span>🚗</span>
                                    <span>سەردانی سەر ماڵان:</span>
                                    <span class="text-[11px] text-emerald-700" x-show="emp.attendance.trip_destination" x-text="'(' + emp.attendance.trip_destination + ')'"></span>
                                </span>
                                <span class="font-black" x-show="emp.attendance.fuel_expense > 0" x-text="'بەنزین: ' + emp.attendance.fuel_expense.toLocaleString() + ' د.ع'"></span>
                            </div>

                            {{-- تێبینی --}}
                            <div x-show="emp.attendance && emp.attendance.note"
                                 class="text-[11px] text-slate-500 bg-slate-50 p-2 rounded-lg border border-slate-100">
                                <span class="font-bold text-slate-700">تێبینی:</span> <span x-text="emp.attendance.note"></span>
                            </div>
                        </div>
                    </div>

                    {{-- دوگمەکانی کردار: چیک ئینی خێرا، چیک ئاوت، وردەکاری --}}
                    <div class="border-t border-slate-100 pt-3 flex items-center justify-between gap-1.5 flex-wrap">
                        <div class="flex items-center gap-1.5 flex-1 sm:flex-initial">
                            {{-- دوگمەی چیک ئینی خێرا --}}
                            <button type="button" @click="quickCheckIn(emp.id)"
                                    class="flex-1 sm:flex-initial px-2.5 py-1.5 rounded-xl text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white flex items-center justify-center gap-1 shadow-2xs cursor-pointer active:scale-95 transition-all">
                                <span>📥</span>
                                <span>چیک ئین</span>
                            </button>

                            {{-- دوگمەی چیک ئاوتی خێرا --}}
                            <button type="button" @click="quickCheckOut(emp.id)"
                                    class="flex-1 sm:flex-initial px-2.5 py-1.5 rounded-xl text-xs font-bold bg-slate-800 hover:bg-slate-900 text-white flex items-center justify-center gap-1 shadow-2xs cursor-pointer active:scale-95 transition-all">
                                <span>📤</span>
                                <span>چیک ئاوت</span>
                            </button>
                        </div>

                        {{-- دوگمەی تۆمارکردنی هەموو وردەکارییەکان --}}
                        <button type="button" @click="openModalFor(emp)"
                                class="w-full sm:w-auto px-2.5 py-1.5 rounded-xl text-xs font-bold bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 flex items-center justify-center gap-1 cursor-pointer transition-all">
                            <span>✏️</span>
                            <span>وردەکاری و دەستکاری</span>
                        </button>
                    </div>
                </div>
            </template>
        </div>
    @endif

    {{-- ============================================================ --}}
    {{-- مۆداڵی وردەکاری ئامادەبوون و کارمەند (MODAL) --}}
    {{-- ============================================================ --}}
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/70 backdrop-blur-xs p-3.5 sm:p-4">
        <div class="relative w-full max-w-lg bg-white rounded-2xl sm:rounded-3xl p-5 sm:p-6 shadow-2xl border border-slate-100 max-h-[90vh] overflow-y-auto scrollbar-none" @click.outside="showModal = false">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3.5 sm:pb-4 mb-4">
                <div class="flex items-center gap-2.5">
                    <div class="size-9 sm:size-10 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-base sm:text-lg border border-blue-100 shrink-0">
                        👷
                    </div>
                    <div class="min-w-0">
                        <h3 class="font-black text-slate-800 text-sm sm:text-base truncate" x-text="'تۆماری کارمەند: ' + (selectedEmployee ? selectedEmployee.name : '')"></h3>
                        <p class="text-xs text-slate-400 font-medium" x-text="'بەروار: {{ $date }}'"></p>
                    </div>
                </div>
                <button type="button" @click="showModal = false" class="text-slate-400 hover:text-slate-600 text-lg cursor-pointer">✕</button>
            </div>

            <form @submit.prevent="saveAttendance" class="space-y-4 text-xs">
                {{-- دۆخی ئامادەبوون --}}
                <div>
                    <label class="block font-bold text-slate-700 mb-1.5">دۆخی ئامادەبوونی ڕۆژانە *</label>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                        <button type="button" @click="form.status = 'present'"
                                :class="form.status === 'present' ? 'bg-emerald-600 text-white font-black shadow-xs' : 'bg-slate-50 text-slate-700 hover:bg-slate-100 border border-slate-200'"
                                class="py-2 rounded-xl text-center cursor-pointer transition-all">
                            ئامادە ✔️
                        </button>
                        <button type="button" @click="form.status = 'leave'"
                                :class="form.status === 'leave' ? 'bg-amber-500 text-white font-black shadow-xs' : 'bg-slate-50 text-slate-700 hover:bg-slate-100 border border-slate-200'"
                                class="py-2 rounded-xl text-center cursor-pointer transition-all">
                            ئیجازە 🏖️
                        </button>
                        <button type="button" @click="form.status = 'absent'"
                                :class="form.status === 'absent' ? 'bg-rose-600 text-white font-black shadow-xs' : 'bg-slate-50 text-slate-700 hover:bg-slate-100 border border-slate-200'"
                                class="py-2 rounded-xl text-center cursor-pointer transition-all">
                            نەهاتوو ❌
                        </button>
                        <button type="button" @click="form.status = 'holiday'"
                                :class="form.status === 'holiday' ? 'bg-slate-700 text-white font-black shadow-xs' : 'bg-slate-50 text-slate-700 hover:bg-slate-100 border border-slate-200'"
                                class="py-2 rounded-xl text-center cursor-pointer transition-all">
                            پشوو
                        </button>
                    </div>
                </div>

                {{-- کاتی هاتن و چوون --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 bg-slate-50/80 p-3 sm:p-3.5 rounded-2xl border border-slate-100">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">کاتی هاتن (Check-in)</label>
                        <input type="time" x-model="form.check_in"
                               class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:outline-hidden focus:border-blue-500 bg-white font-mono">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">کاتی چوون (Check-out)</label>
                        <input type="time" x-model="form.check_out"
                               class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:outline-hidden focus:border-blue-500 bg-white font-mono">
                    </div>
                </div>

                {{-- کاتی زیادە (Overtime) --}}
                <div>
                    <label class="block font-bold text-slate-700 mb-1">
                        ⏱️ کاتژمێری زیادە (Overtime)
                        <span class="text-slate-400 font-normal text-[11px]">(لە کاتی ئاسایی زیاتر ماوەتەوە)</span>
                    </label>
                    <input type="number" step="0.5" min="0" x-model="form.overtime_hours" placeholder="وەک: 1.5 کاتژمێر..."
                           class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:outline-hidden focus:border-blue-500">
                </div>

                {{-- ڕۆیشتنی کاتی لە ناو کاردا (Temporary Exit) --}}
                <div class="bg-amber-50/50 p-3 sm:p-3.5 rounded-2xl border border-amber-200/60 space-y-2.5">
                    <label class="block font-bold text-amber-900">
                        🚪 دەرچوونی کاتی لە کاتی ئیشدا (ڕۆیشتن و گەڕانەوە)
                    </label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                        <div>
                            <span class="text-[11px] font-bold text-slate-600 block mb-1">چەند کاتژمێر ڕۆیشت:</span>
                            <input type="number" step="0.5" min="0" x-model="form.temporary_exit_hours" placeholder="وەک: 2 کاتژمێر..."
                                   class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:outline-hidden focus:border-blue-500 bg-white">
                        </div>
                        <div>
                            <span class="text-[11px] font-bold text-slate-600 block mb-1">هۆکاری دەرچوون:</span>
                            <input type="text" x-model="form.exit_reason" placeholder="وەک: کڕینی کەرەستە، کاری کاتی..."
                                   class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:outline-hidden focus:border-blue-500 bg-white">
                        </div>
                    </div>
                </div>

                {{-- چوونە سەر ماڵان بە سەیارەی خۆی و بەنزین (Fuel Expense) --}}
                <div class="bg-emerald-50/50 p-3 sm:p-3.5 rounded-2xl border border-emerald-200/60 space-y-2.5">
                    <label class="block font-bold text-emerald-900">
                        🚗 چوون بۆ سەر ماڵان بە سەیارەی خۆی و خەرجی بەنزین
                    </label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                        <div>
                            <span class="text-[11px] font-bold text-slate-600 block mb-1">شوێن / ماڵی کڕیار:</span>
                            <input type="text" x-model="form.trip_destination" placeholder="وەک: ماڵی کاک ڕێبوار، بەستنی کار..."
                                   class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:outline-hidden focus:border-blue-500 bg-white">
                        </div>
                        <div>
                            <span class="text-[11px] font-bold text-slate-600 block mb-1">پارەی بەنزین (د.ع):</span>
                            <input type="number" step="500" min="0" x-model="form.fuel_expense" placeholder="وەک: 10000"
                                   class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:outline-hidden focus:border-blue-500 bg-white font-mono">
                        </div>
                    </div>
                </div>

                {{-- تێبینی --}}
                <div>
                    <label class="block font-bold text-slate-700 mb-1">تێبینی تر</label>
                    <input type="text" x-model="form.note" placeholder="هەر تێبینییەکی تر..."
                           class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:outline-hidden focus:border-blue-500">
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="showModal = false" class="btn btn-ghost !py-2 !px-3.5 text-xs font-bold cursor-pointer">
                        داخستن
                    </button>
                    <button type="submit" class="btn btn-primary !py-2 !px-5 text-xs font-bold bg-blue-600 hover:bg-blue-700 cursor-pointer shadow-xs">
                        پاشەکەوتکردنی هەموو وردەکارییەکان
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
function workshopEmployeesApp() {
    return {
        employeesList: @json($employeesData),
        showModal: false,
        selectedEmployee: null,
        form: {
            employee_id: '',
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

        openModalFor(emp) {
            this.selectedEmployee = emp;
            const att = emp.attendance;
            this.form = {
                employee_id: emp.id,
                work_date: '{{ $date }}',
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
                        work_date: '{{ $date }}'
                    })
                });
                const data = await res.json();
                if (data.ok) {
                    const emp = this.employeesList.find(e => e.id === employeeId);
                    if (emp) {
                        if (!emp.attendance) emp.attendance = {};
                        emp.attendance.status = 'present';
                        emp.attendance.check_in = data.attendance.check_in.substring(0, 5);
                    }
                }
            } catch (e) {
                alert('هەڵەیەک ڕوویدا لە چیک ئین.');
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
                        work_date: '{{ $date }}'
                    })
                });
                const data = await res.json();
                if (data.ok) {
                    const emp = this.employeesList.find(e => e.id === employeeId);
                    if (emp) {
                        if (!emp.attendance) emp.attendance = {};
                        emp.attendance.status = 'present';
                        emp.attendance.check_out = data.attendance.check_out.substring(0, 5);
                        emp.attendance.overtime_hours = data.attendance.overtime_hours;
                    }
                }
            } catch (e) {
                alert('هەڵەیەک ڕوویدا لە چیک ئاوت.');
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
                } else {
                    alert('هەڵەیەک ڕوویدا لە پاشەکەوتکردن.');
                }
            } catch (e) {
                alert('هەڵەی پەیوەندی.');
            }
        }
    };
}
</script>
@endsection
