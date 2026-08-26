<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    /**
     * تۆماری ڕۆژانە — هەموو کارمەندە چالاکەکان بۆ ڕۆژێکی دیاریکراو.
     * هەینی بە بنەڕەت وەک پشوو دادەنرێت.
     */
    public function index(Request $request): View
    {
        $date = $request->date('date')?->toDateString() ?? now()->toDateString();
        $isHoliday = Attendance::isWeeklyHoliday($date);

        $employees = Employee::active()->orderBy('name')->get();

        $records = Attendance::whereDate('work_date', $date)
            ->get()
            ->keyBy('employee_id');

        return view('attendance.index', [
            'employees' => $employees,
            'records' => $records,
            'date' => $date,
            'isHoliday' => $isHoliday,
        ]);
    }

    /** پاشەکەوتکردنی بە کۆمەڵی تۆماری کارمەندان */
    public function store(Request $request)
    {
        $data = $request->validate([
            'work_date' => ['required', 'date'],
            'rows' => ['required', 'array'],
            'rows.*.status' => ['required', 'in:present,absent,holiday,leave'],
            'rows.*.check_in' => ['nullable'],
            'rows.*.check_out' => ['nullable'],
            'rows.*.overtime_hours' => ['nullable', 'numeric', 'min:0'],
            'rows.*.temporary_exit_hours' => ['nullable', 'numeric', 'min:0'],
            'rows.*.exit_reason' => ['nullable', 'string', 'max:255'],
            'rows.*.fuel_expense' => ['nullable', 'numeric', 'min:0'],
            'rows.*.trip_destination' => ['nullable', 'string', 'max:255'],
            'rows.*.note' => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($data) {
            foreach ($data['rows'] as $employeeId => $row) {
                $employee = Employee::find($employeeId);

                if (! $employee) {
                    continue;
                }

                $hours = Attendance::calculateHours($row['check_in'] ?? null, $row['check_out'] ?? null);
                $overtime = isset($row['overtime_hours']) && $row['overtime_hours'] !== ''
                    ? (float) $row['overtime_hours']
                    : $hours['overtime'];

                Attendance::updateOrCreate(
                    ['employee_id' => $employee->id, 'work_date' => $data['work_date']],
                    [
                        'status' => $row['status'],
                        'check_in' => $row['check_in'] ?? null,
                        'check_out' => $row['check_out'] ?? null,
                        'hours' => $hours['hours'],
                        'overtime_hours' => $overtime,
                        'temporary_exit_hours' => (float) ($row['temporary_exit_hours'] ?? 0),
                        'exit_reason' => $row['exit_reason'] ?? null,
                        'fuel_expense' => (float) ($row['fuel_expense'] ?? 0),
                        'trip_destination' => $row['trip_destination'] ?? null,
                        'wage_snapshot' => $row['status'] === 'present' ? $employee->daily_wage : 0,
                        'user_id' => auth()->id(),
                        'note' => $row['note'] ?? null,
                    ],
                );
            }
        });

        return back()->with('ok', 'تۆماری ئامادەبوون بە سەرکەوتوویی پاشەکەوتکرا.');
    }

    /** چیک ئینی خێرا (Check-in Now) */
    public function quickCheckIn(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'work_date' => ['nullable', 'date'],
        ]);

        $workDate = $validated['work_date'] ?? now()->toDateString();
        $employee = Employee::findOrFail($validated['employee_id']);
        $currentTime = now()->format('H:i');

        $attendance = Attendance::firstOrNew([
            'employee_id' => $employee->id,
            'work_date' => $workDate,
        ]);

        $attendance->status = 'present';
        $attendance->check_in = $attendance->check_in ?: $currentTime;
        $attendance->wage_snapshot = $employee->daily_wage;
        $attendance->user_id = auth()->id();

        if ($attendance->check_out) {
            $calc = Attendance::calculateHours($attendance->check_in, $attendance->check_out);
            $attendance->hours = $calc['hours'];
            $attendance->overtime_hours = $calc['overtime'];
        }

        $attendance->save();

        if ($request->wantsJson()) {
            return response()->json([
                'ok' => true,
                'message' => "چیک ئین بۆ {$employee->name} لە کاتژمێر {$currentTime} تۆمارکرا.",
                'attendance' => $attendance,
            ]);
        }

        return back()->with('ok', "چیک ئین بۆ {$employee->name} تۆمارکرا ({$currentTime}).");
    }

    /** چیک ئاوتی خێرا (Check-out Now) */
    public function quickCheckOut(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'work_date' => ['nullable', 'date'],
        ]);

        $workDate = $validated['work_date'] ?? now()->toDateString();
        $employee = Employee::findOrFail($validated['employee_id']);
        $currentTime = now()->format('H:i');

        $attendance = Attendance::firstOrNew([
            'employee_id' => $employee->id,
            'work_date' => $workDate,
        ]);

        $attendance->status = 'present';
        $attendance->check_out = $currentTime;
        $attendance->wage_snapshot = $employee->daily_wage;
        $attendance->user_id = auth()->id();

        if ($attendance->check_in) {
            $calc = Attendance::calculateHours($attendance->check_in, $currentTime);
            $attendance->hours = $calc['hours'];
            $attendance->overtime_hours = $calc['overtime'];
        }

        $attendance->save();

        if ($request->wantsJson()) {
            return response()->json([
                'ok' => true,
                'message' => "چیک ئاوت بۆ {$employee->name} لە کاتژمێر {$currentTime} تۆمارکرا.",
                'attendance' => $attendance,
            ]);
        }

        return back()->with('ok', "چیک ئاوت بۆ {$employee->name} تۆمارکرا ({$currentTime}).");
    }

    /** پاشەکەوتکردنی وردەکاری ئامادەبوونی تەنها یەک کارمەند */
    public function recordSingle(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'work_date' => ['required', 'date'],
            'status' => ['required', 'in:present,absent,holiday,leave'],
            'check_in' => ['nullable'],
            'check_out' => ['nullable'],
            'overtime_hours' => ['nullable', 'numeric', 'min:0'],
            'temporary_exit_hours' => ['nullable', 'numeric', 'min:0'],
            'exit_reason' => ['nullable', 'string', 'max:255'],
            'fuel_expense' => ['nullable', 'numeric', 'min:0'],
            'trip_destination' => ['nullable', 'string', 'max:255'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $employee = Employee::findOrFail($validated['employee_id']);
        $hours = Attendance::calculateHours($validated['check_in'] ?? null, $validated['check_out'] ?? null);

        $overtime = isset($validated['overtime_hours']) && $validated['overtime_hours'] !== ''
            ? (float) $validated['overtime_hours']
            : $hours['overtime'];

        $attendance = Attendance::updateOrCreate(
            ['employee_id' => $employee->id, 'work_date' => $validated['work_date']],
            [
                'status' => $validated['status'],
                'check_in' => $validated['check_in'] ?? null,
                'check_out' => $validated['check_out'] ?? null,
                'hours' => $hours['hours'],
                'overtime_hours' => $overtime,
                'temporary_exit_hours' => (float) ($validated['temporary_exit_hours'] ?? 0),
                'exit_reason' => $validated['exit_reason'] ?? null,
                'fuel_expense' => (float) ($validated['fuel_expense'] ?? 0),
                'trip_destination' => $validated['trip_destination'] ?? null,
                'wage_snapshot' => $validated['status'] === 'present' ? $employee->daily_wage : 0,
                'user_id' => auth()->id(),
                'note' => $validated['note'] ?? null,
            ]
        );

        if ($request->wantsJson()) {
            return response()->json([
                'ok' => true,
                'message' => "تۆماری ئامادەبوونی {$employee->name} پاشەکەوتکرا.",
                'attendance' => $attendance,
            ]);
        }

        return back()->with('ok', "تۆماری ئامادەبوونی {$employee->name} نوێکرایەوە.");
    }

    /** حەقدەستی ماوەیەک — بۆ هەموو کارمەندان بە وردەکاری کاتی زیادە و خەرجی بەنزین */
    public function wages(Request $request): View
    {
        $from = $request->date('from')?->toDateString() ?? now()->startOfMonth()->toDateString();
        $to = $request->date('to')?->toDateString() ?? now()->endOfMonth()->toDateString();

        $rows = Employee::active()->orderBy('name')->get()->map(function (Employee $employee) use ($from, $to) {
            $earned = $employee->earnedBetween($from, $to);
            $paid = $employee->paidBetween($from, $to);

            $attendances = $employee->attendances()->whereBetween('work_date', [$from, $to])->get();

            $totalFuel = (float) $attendances->sum('fuel_expense');
            $totalOvertime = (float) $attendances->sum('overtime_hours');
            $totalTempExit = (float) $attendances->sum('temporary_exit_hours');

            return [
                'employee' => $employee,
                'days' => $attendances->where('status', 'present')->count(),
                'leave_days' => $attendances->where('status', 'leave')->count(),
                'overtime' => $totalOvertime,
                'temporary_exit_hours' => $totalTempExit,
                'fuel_expense' => $totalFuel,
                'earned' => $earned,
                'paid' => $paid,
                'remaining' => $earned - $paid,
            ];
        });

        return view('attendance.wages', compact('rows', 'from', 'to'));
    }
}
