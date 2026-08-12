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

    /** پاشەکەوتکردنی تۆماری هەموو کارمەندان بۆ ئەو ڕۆژە. */
    public function store(Request $request)
    {
        $data = $request->validate([
            'work_date' => ['required', 'date'],
            'rows' => ['required', 'array'],
            'rows.*.status' => ['required', 'in:present,absent,holiday,leave'],
            'rows.*.check_in' => ['nullable', 'date_format:H:i'],
            'rows.*.check_out' => ['nullable', 'date_format:H:i'],
            'rows.*.note' => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($data) {
            foreach ($data['rows'] as $employeeId => $row) {
                $employee = Employee::find($employeeId);

                if (! $employee) {
                    continue;
                }

                $hours = Attendance::calculateHours($row['check_in'] ?? null, $row['check_out'] ?? null);

                Attendance::updateOrCreate(
                    ['employee_id' => $employee->id, 'work_date' => $data['work_date']],
                    [
                        'status' => $row['status'],
                        'check_in' => $row['check_in'] ?? null,
                        'check_out' => $row['check_out'] ?? null,
                        'hours' => $hours['hours'],
                        'overtime_hours' => $hours['overtime'],
                        // حەقدەست تەنها بۆ ڕۆژی ئامادەبوون دەژمێردرێت.
                        'wage_snapshot' => $row['status'] === 'present' ? $employee->daily_wage : 0,
                        'user_id' => auth()->id(),
                        'note' => $row['note'] ?? null,
                    ],
                );
            }
        });

        return back()->with('ok', 'تۆماری ئامادەبوون پاشەکەوتکرا.');
    }

    /** حەقدەستی ماوەیەک — بۆ هەموو کارمەندان. */
    public function wages(Request $request): View
    {
        $from = $request->date('from')?->toDateString() ?? now()->startOfMonth()->toDateString();
        $to = $request->date('to')?->toDateString() ?? now()->endOfMonth()->toDateString();

        $rows = Employee::active()->orderBy('name')->get()->map(function (Employee $employee) use ($from, $to) {
            $earned = $employee->earnedBetween($from, $to);
            $paid = $employee->paidBetween($from, $to);

            return [
                'employee' => $employee,
                'days' => $employee->attendances()
                    ->whereBetween('work_date', [$from, $to])
                    ->where('status', 'present')
                    ->count(),
                'overtime' => (float) $employee->attendances()
                    ->whereBetween('work_date', [$from, $to])
                    ->sum('overtime_hours'),
                'earned' => $earned,
                'paid' => $paid,
                'remaining' => $earned - $paid,
            ];
        });

        return view('attendance.wages', compact('rows', 'from', 'to'));
    }
}
