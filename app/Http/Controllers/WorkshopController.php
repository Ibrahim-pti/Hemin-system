<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\Order;
use App\Models\Setting;
use App\Models\Unit;
use App\Models\Warehouse;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class WorkshopController extends Controller
{
    public function __construct(
        private readonly StockService $stockService
    ) {}

    /** بەدەستهێنانی کۆگای دروستکردن */
    private function getWorkshopWarehouse(): ?Warehouse
    {
        return Warehouse::where('name', 'like', '%دروستکردن%')->first()
            ?? Warehouse::where('is_default', false)->first()
            ?? Warehouse::first();
    }

    /** داشبۆردی سەرەکی کارگە و وەستاکان */
    public function dashboard(Request $request): View
    {
        $workshopWarehouse = $this->getWorkshopWarehouse();
        $warehouseId = $workshopWarehouse?->id;

        // ئامارە خێراکان
        $pendingCount = Order::where('status', 'confirmed')->count();
        $inProductionCount = Order::where('status', 'in_production')->count();
        $readyCount = Order::where('status', 'ready')->count();
        $deliveredCount = Order::where('status', 'delivered')->whereDate('updated_at', now()->toDateString())->count();

        // وەسڵە کاراکانی کارگە بۆ پێشاندان لە داشبۆرد
        $activeOrders = Order::query()
            ->with(['customer', 'items'])
            ->whereIn('status', ['in_production', 'confirmed', 'ready'])
            ->orderByRaw("CASE status WHEN 'in_production' THEN 1 WHEN 'confirmed' THEN 2 WHEN 'ready' THEN 3 ELSE 4 END")
            ->latest('order_date')
            ->take(6)
            ->get();

        // مەوادی کەمبووەوە
        $rawMaterials = Item::query()
            ->active()
            ->withStock($warehouseId)
            ->with(['unit', 'category'])
            ->orderBy('name')
            ->get();

        $lowStockMaterials = $rawMaterials->filter(fn ($item) => $item->is_low);

        // کارمەندان و وەستاکان بۆ کورتەی داشبۆرد
        $employees = Employee::query()
            ->active()
            ->with(['attendances' => fn ($q) => $q->whereDate('work_date', now()->toDateString())])
            ->orderByRaw("CASE job_title WHEN 'master' THEN 1 WHEN 'porter' THEN 2 WHEN 'helper' THEN 3 WHEN 'driver' THEN 4 WHEN 'other' THEN 5 ELSE 6 END")
            ->orderBy('name')
            ->take(8)
            ->get();

        return view('workshop.dashboard', compact(
            'workshopWarehouse',
            'pendingCount',
            'inProductionCount',
            'readyCount',
            'deliveredCount',
            'activeOrders',
            'lowStockMaterials',
            'employees'
        ));
    }

    /** لاپەڕەی جیاکراوەی داواکارییەکان و وەسڵەکانی کارگە */
    public function orders(Request $request): View
    {
        $pendingCount = Order::where('status', 'confirmed')->count();
        $inProductionCount = Order::where('status', 'in_production')->count();
        $readyCount = Order::where('status', 'ready')->count();
        $deliveredCount = Order::where('status', 'delivered')->whereDate('updated_at', now()->toDateString())->count();
        $orders = Order::query()
            ->with(['customer', 'items'])
            ->whereIn('status', ['in_production', 'confirmed', 'ready'])
            ->orderByRaw("CASE status WHEN 'in_production' THEN 1 WHEN 'confirmed' THEN 2 WHEN 'ready' THEN 3 ELSE 4 END")
            ->latest('order_date')
            ->get();

        $ordersData = $orders->map(function ($o) {
            $today = now()->toDateString();
            $isUrgent = !empty($o->delivery_date) && $o->status !== 'delivered' && $o->delivery_date->toDateString() <= $today;

            return [
                'id' => $o->id,
                'invoice_no' => $o->invoice_no,
                'status' => $o->status,
                'status_label' => $o->status_label,
                'customer_name' => $o->customer?->name ?? 'نەناسراو',
                'customer_phone' => $o->customer?->phone ?? '',
                'order_date' => $o->order_date?->format('Y/m/d') ?? '',
                'delivery_date' => $o->delivery_date?->format('Y/m/d') ?? '',
                'is_urgent' => $isUrgent,
                'notes' => $o->notes ?? '',
                'print_url' => route('orders.print', $o->id),
                'items_count' => $o->items->count(),
                'items' => $o->items->map(fn ($it) => [
                    'id' => $it->id,
                    'item_name' => $it->description ?: ($it->item?->name ?? 'کەلوپەل'),
                    'qty' => (float) $it->qty,
                    'unit_name' => $it->mode_unit ?: ($it->unit_name ?? 'دانە'),
                    'width' => $it->width,
                    'height' => $it->height,
                    'measurement' => $it->measurement_label,
                    'note' => $it->note,
                    'image' => $it->imageUrl(),
                ])->values()->all(),
            ];
        })->values()->all();

        return view('workshop.orders', compact(
            'orders',
            'ordersData',
            'pendingCount',
            'inProductionCount',
            'readyCount',
            'deliveredCount'
        ));
    }

    /** لاپەڕەی جیاکراوەی مەوادی خاوی کارگە */
    public function materials(Request $request): View
    {
        $workshopWarehouse = $this->getWorkshopWarehouse();
        $warehouseId = $workshopWarehouse?->id;

        $rawMaterials = Item::query()
            ->active()
            ->withStock($warehouseId)
            ->with([
                'unit',
                'category',
                'movements' => fn ($q) => $q->where('warehouse_id', $warehouseId)->latest('moved_at')->latest('id')
            ])
            ->orderBy('name')
            ->get();

        $lowStockMaterials = $rawMaterials->filter(fn ($item) => $item->is_low);
        $categories = ItemCategory::orderBy('name')->get();
        $units = Unit::orderBy('name')->get();
        $orders = Order::query()
            ->with('customer')
            ->whereIn('status', ['confirmed', 'in_production', 'ready'])
            ->latest('id')
            ->take(50)
            ->get();

        $materialsData = $rawMaterials->map(function ($m) {
            $latestMovement = $m->movements->first();
            $date = $latestMovement?->moved_at?->format('Y/m/d') ?? $m->created_at?->format('Y/m/d') ?? '';

            return [
                'id' => $m->id,
                'code' => $m->code,
                'name' => $m->name,
                'category_name' => $m->category?->name,
                'stock_qty' => (float) $m->stock_qty,
                'min_qty' => (float) $m->min_qty,
                'unit_name' => $m->unit?->name ?? '',
                'is_low' => $m->is_low,
                'date' => $date,
            ];
        })->values()->all();

        return view('workshop.materials', compact(
            'workshopWarehouse',
            'rawMaterials',
            'materialsData',
            'lowStockMaterials',
            'categories',
            'units',
            'orders'
        ));
    }

    /** لاپەڕەی جەدوەلی سەحی ڕۆژانە و حیساباتی وەستا و حەمەڵەکان */
    public function employees(Request $request): View
    {
        $today = now();
        $monthStart = $today->copy()->startOfMonth()->toDateString();
        $monthEnd = $today->copy()->endOfMonth()->toDateString();

        $rangeType = $request->input('range_type', 'this_month');

        $weekOffset = (int) $request->input('week_offset', 0);
        if ($request->has('week_offset')) {
            $rangeType = 'week_offset';
            $targetWeek = $today->copy()->addWeeks($weekOffset);
            $wStart = $targetWeek->copy()->startOfWeek(\Carbon\Carbon::SATURDAY);
            $wEnd = $targetWeek->copy()->endOfWeek(\Carbon\Carbon::FRIDAY);
            $from = $wStart->toDateString();
            $to = $wEnd->toDateString();
        } elseif ($request->filled('from') && $request->filled('to')) {
            $from = $request->date('from')->toDateString();
            $to = $request->date('to')->toDateString();
            $rangeType = 'custom';
        } else {
            switch ($rangeType) {
                case 'this_week':
                    $wStart = $today->copy()->startOfWeek(\Carbon\Carbon::SATURDAY);
                    $wEnd = $today->copy()->endOfWeek(\Carbon\Carbon::FRIDAY);
                    $from = $wStart->toDateString();
                    $to = $wEnd->toDateString();
                    break;
                case 'last_week':
                    $lastWeek = $today->copy()->subWeek();
                    $from = $lastWeek->copy()->startOfWeek(\Carbon\Carbon::SATURDAY)->toDateString();
                    $to = $lastWeek->copy()->endOfWeek(\Carbon\Carbon::FRIDAY)->toDateString();
                    break;
                case 'last_month':
                    $lastMonth = $today->copy()->subMonth();
                    $from = $lastMonth->copy()->startOfMonth()->toDateString();
                    $to = $lastMonth->copy()->endOfMonth()->toDateString();
                    break;
                case 'this_month':
                default:
                    $rangeType = 'this_month';
                    $from = $monthStart;
                    $to = $monthEnd;
                    break;
            }
        }

        // دڵنیابوونەوە لەوەی لە ٣٥ ڕۆژ زیاتر نەبێت بۆ ڕێگری لە قورسایی جەدوەل
        $fromDate = \Carbon\Carbon::parse($from);
        $toDate = \Carbon\Carbon::parse($to);
        if ($fromDate->diffInDays($toDate) > 35) {
            $toDate = $fromDate->copy()->addDays(34);
            $to = $toDate->toDateString();
        }

        $kurdishDays = [
            0 => 'یەکشەممە',
            1 => 'دووشەممە',
            2 => 'سێشەممە',
            3 => 'چوارشەممە',
            4 => 'پێنجشەممە',
            5 => 'هەینی',
            6 => 'شەممە',
        ];

        $period = \Carbon\CarbonPeriod::create($from, $to);
        $days = [];
        foreach ($period as $dt) {
            $dateStr = $dt->toDateString();
            $days[] = [
                'date' => $dateStr,
                'day_name' => $kurdishDays[$dt->dayOfWeek],
                'day_short' => $dt->format('m/d'),
                'day_num' => $dt->format('j'),
                'month_num' => $dt->format('n'),
                'day_of_week' => $dt->dayOfWeek,
                'is_today' => $dt->isToday(),
                'is_holiday' => Attendance::isWeeklyHoliday($dateStr),
            ];
        }

        $canSeeMoney = auth()->user()?->isAdmin() ?? false;

        $shiftSettings = [
            'work_start' => Setting::get('workshop_work_start', '08:00'),
            'work_end' => Setting::get('workshop_work_end', '17:00'),
            'work_hours' => (float) Setting::get('workshop_work_hours', 8),
            'weekly_holiday' => Setting::get('workshop_weekly_holiday', 'friday'),
            'overtime_hourly_rate' => (float) Setting::get('workshop_overtime_hourly_rate', 0),
            'overtime_multiplier' => (float) Setting::get('workshop_overtime_multiplier', 1.0),
            'home_visit_hourly_rate' => (float) Setting::get('workshop_home_visit_hourly_rate', 0),
            'custom_overtime_rates' => json_decode(Setting::get('workshop_custom_overtime_rates', '[]'), true) ?: [
                ['name' => 'چوونە ماڵان / دانان', 'rate' => ((float) Setting::get('workshop_home_visit_hourly_rate', 0) ?: 7000), 'unit' => 'hourly']
            ],
            'half_day_deduction_type' => Setting::get('workshop_half_day_deduction_type', 'percentage'),
            'half_day_deduction_rate' => (float) Setting::get('workshop_half_day_deduction_rate', 0),
            'absent_deduction_type' => Setting::get('workshop_absent_deduction_type', 'none'),
            'absent_deduction_rate' => (float) Setting::get('workshop_absent_deduction_rate', 0),
            'late_grace_minutes' => (int) Setting::get('workshop_late_grace_minutes', 0),
            'late_deduction_type' => Setting::get('workshop_late_deduction_type', 'none'),
            'late_deduction_rate' => (float) Setting::get('workshop_late_deduction_rate', 0),
            'late_weekly_threshold_days' => (int) Setting::get('workshop_late_weekly_threshold_days', 2),
            'late_weekly_penalty_amount' => (float) Setting::get('workshop_late_weekly_penalty_amount', 0),
        ];

        // دەستنیشانکردنی دەستپێک و کۆتایی ئەم مانگە بۆ کورتەی مانگانەی هەر وەستایەک
        $monthStart = $today->copy()->startOfMonth()->toDateString();
        $monthEnd = $today->copy()->endOfMonth()->toDateString();

        $employees = Employee::query()
            ->active()
            ->with([
                'attendances' => fn ($q) => $q->whereBetween('work_date', [
                    min($from, $monthStart),
                    max($to, $monthEnd)
                ]),
                'payments' => fn ($q) => $q->where('direction', 'out')->latest('paid_at'),
            ])
            ->orderByRaw("CASE job_title WHEN 'master' THEN 1 WHEN 'porter' THEN 2 WHEN 'helper' THEN 3 WHEN 'driver' THEN 4 WHEN 'other' THEN 5 ELSE 6 END")
            ->orderBy('name')
            ->get();

        $employeesMatrix = $employees->map(function (Employee $emp) use ($days, $shiftSettings, $from, $to, $monthStart, $monthEnd, $canSeeMoney) {
            $allAtts = $emp->attendances->keyBy(fn ($a) => $a->work_date instanceof \DateTimeInterface ? $a->work_date->format('Y-m-d') : substr((string) $a->work_date, 0, 10));

            $cells = [];
            $presentCount = 0;
            $halfDayCount = 0;
            $leaveCount = 0;
            $absentCount = 0;
            $holidayCount = 0;
            $totalOvertime = 0.0;
            $totalFuel = 0.0;
            $totalTempExit = 0.0;
            $totalManualDeduction = 0.0;
            $totalBonus = 0.0;
            $totalLateMinutes = 0;
            $lateDaysCount = 0;

            foreach ($days as $d) {
                $dStr = $d['date'];
                $att = $allAtts->get($dStr);

                if ($att) {
                    $status = $att->status;
                    $ot = (float) $att->overtime_hours;
                    $fuel = (float) $att->fuel_expense;
                    $tempExit = (float) $att->temporary_exit_hours;
                    $deduction = (float) $att->deduction_amount;
                    $bonus = (float) $att->bonus_amount;
                    $lateMin = (int) $att->late_minutes;

                    if ($status === 'present') $presentCount++;
                    elseif ($status === 'half_day') $halfDayCount++;
                    elseif ($status === 'leave') $leaveCount++;
                    elseif ($status === 'absent') $absentCount++;
                    elseif ($status === 'holiday') $holidayCount++;

                    if ($lateMin > 0) {
                        $lateDaysCount++;
                        $totalLateMinutes += $lateMin;
                    }

                    $totalOvertime += $ot;
                    $totalFuel += $fuel;
                    $totalTempExit += $tempExit;
                    $totalManualDeduction += $deduction;
                    $totalBonus += $bonus;

                    $cells[$dStr] = [
                        'id' => $att->id,
                        'status' => $status,
                        'status_label' => $att->status_label,
                        'check_in' => $att->check_in ? substr($att->check_in, 0, 5) : '',
                        'check_out' => $att->check_out ? substr($att->check_out, 0, 5) : '',
                        'hours' => (float) $att->hours,
                        'overtime_hours' => $ot,
                        'late_minutes' => $lateMin,
                        'temporary_exit_hours' => $tempExit,
                        'exit_reason' => $canSeeMoney ? ($att->exit_reason ?? '') : '',
                        'fuel_expense' => $canSeeMoney ? $fuel : 0,
                        'deduction_amount' => $canSeeMoney ? $deduction : 0,
                        'bonus_amount' => $canSeeMoney ? $bonus : 0,
                        'trip_destination' => $att->trip_destination ?? '',
                        'custom_task_name' => $canSeeMoney ? ($att->custom_task_name ?? '') : '',
                        'custom_task_rate' => $canSeeMoney ? (float) ($att->custom_task_rate ?? 0) : 0,
                        'custom_task_unit' => $att->custom_task_unit ?? 'hourly',
                        'custom_task_hours' => (float) ($att->custom_task_hours ?? 0),
                        'custom_task_amount' => $canSeeMoney ? (float) ($att->custom_task_amount ?? 0) : 0,
                        'note' => $att->note ?? '',
                    ];
                } else {
                    $cells[$dStr] = null;
                }
            }

            $dailyWage = (float) $emp->daily_wage;
            $salaryType = $emp->salary_type ?? 'daily';
            $effectiveDailyWage = $emp->effective_daily_wage;

            // حیساباتی دارایی بۆ ماوەی دیاریکراو
            $halfDayEarned = round($effectiveDailyWage * 0.5, 2);
            if ($shiftSettings['half_day_deduction_type'] === 'fixed_amount' && $shiftSettings['half_day_deduction_rate'] > 0) {
                $halfDayEarned = max(0, round($effectiveDailyWage - $shiftSettings['half_day_deduction_rate'], 2));
            }
            $baseEarned = ($presentCount * $effectiveDailyWage) + ($halfDayCount * $halfDayEarned);
            $hourlyWage = $shiftSettings['work_hours'] > 0 ? ($effectiveDailyWage / $shiftSettings['work_hours']) : 0;
            $stdOvertimeRate = $shiftSettings['overtime_hourly_rate'] > 0
                ? $shiftSettings['overtime_hourly_rate']
                : ($hourlyWage * $shiftSettings['overtime_multiplier']);
            $homeOvertimeRate = $shiftSettings['home_visit_hourly_rate'] > 0
                ? $shiftSettings['home_visit_hourly_rate']
                : $stdOvertimeRate;

            $overtimeEarned = 0.0;
            foreach ($days as $d) {
                $dAtt = $allAtts->get($d['date']);
                if ($dAtt) {
                    $otHours = (float) $dAtt->overtime_hours;
                    if ($otHours > 0) {
                        $rate = (! empty($dAtt->trip_destination) && empty($dAtt->custom_task_name)) ? $homeOvertimeRate : $stdOvertimeRate;
                        $overtimeEarned += ($otHours * $rate);
                    }
                    if ((float) $dAtt->custom_task_amount > 0) {
                        $overtimeEarned += (float) $dAtt->custom_task_amount;
                    } elseif ((float) $dAtt->custom_task_hours > 0 && (float) $dAtt->custom_task_rate > 0) {
                        $overtimeEarned += ((float) $dAtt->custom_task_hours * (float) $dAtt->custom_task_rate);
                    }
                }
            }
            $overtimeEarned = round($overtimeEarned, 2);

            // حیساباتی سزای غیاببوونی کامل
            $calculatedAbsentPenalty = 0.0;
            if ($shiftSettings['absent_deduction_type'] === 'fixed_amount' && $shiftSettings['absent_deduction_rate'] > 0) {
                $calculatedAbsentPenalty = $absentCount * $shiftSettings['absent_deduction_rate'];
            } elseif ($shiftSettings['absent_deduction_type'] === 'one_day_wage') {
                $calculatedAbsentPenalty = $absentCount * $effectiveDailyWage;
            }

            // حیساباتی بڕینی تاخیربوون بەپێی یاسای بەڕێوەبەر
            $calculatedLateDeduction = 0.0;
            if ($shiftSettings['late_deduction_type'] === 'per_minute' && $shiftSettings['late_deduction_rate'] > 0) {
                $calculatedLateDeduction += $totalLateMinutes * $shiftSettings['late_deduction_rate'];
            } elseif ($shiftSettings['late_deduction_type'] === 'per_hour' && $shiftSettings['late_deduction_rate'] > 0) {
                $calculatedLateDeduction += ($totalLateMinutes / 60) * $hourlyWage * $shiftSettings['late_deduction_rate'];
            } elseif ($shiftSettings['late_deduction_type'] === 'weekly_threshold' && $shiftSettings['late_weekly_threshold_days'] > 0) {
                if ($lateDaysCount >= $shiftSettings['late_weekly_threshold_days']) {
                    $calculatedLateDeduction += $shiftSettings['late_weekly_penalty_amount'] > 0 ? $shiftSettings['late_weekly_penalty_amount'] : $effectiveDailyWage;
                }
            } elseif ($shiftSettings['late_deduction_type'] === 'fixed_amount' && $shiftSettings['late_deduction_rate'] > 0) {
                $calculatedLateDeduction += $lateDaysCount * $shiftSettings['late_deduction_rate'];
            }

            $totalDeductions = round($totalManualDeduction + $calculatedLateDeduction + $calculatedAbsentPenalty, 2);
            $totalEarned = round($baseEarned + $overtimeEarned + $totalFuel + $totalBonus - $totalDeductions, 2);

            $rangePayments = $emp->payments->filter(fn ($p) => $p->paid_at && $p->paid_at->toDateString() >= $from && $p->paid_at->toDateString() <= $to);
            $totalPaid = (float) $rangePayments->sum('amount_iqd');
            $remainingBalance = round($totalEarned - $totalPaid, 2);

            // هەژمارکردنی ئاماری تەواوی مانگ بۆ پڕۆفایل و دێتەلی وەستاکە
            $monthAtts = $emp->attendances->filter(fn ($a) => $a->work_date && $a->work_date->toDateString() >= $monthStart && $a->work_date->toDateString() <= $monthEnd);
            $monthPresent = $monthAtts->where('status', 'present')->count();
            $monthHalfDay = $monthAtts->where('status', 'half_day')->count();
            $monthAbsent = $monthAtts->where('status', 'absent')->count();
            $monthLeave = $monthAtts->where('status', 'leave')->count();
            $monthOvertime = (float) $monthAtts->sum('overtime_hours');
            $monthFuel = (float) $monthAtts->sum('fuel_expense');
            $monthDeductions = (float) $monthAtts->sum('deduction_amount');
            $monthBonus = (float) $monthAtts->sum('bonus_amount');
            $monthLateMinutes = (int) $monthAtts->sum('late_minutes');
            $monthLateDays = $monthAtts->where('late_minutes', '>', 0)->count();

            $monthHalfDayEarned = round($effectiveDailyWage * 0.5, 2);
            if ($shiftSettings['half_day_deduction_type'] === 'fixed_amount' && $shiftSettings['half_day_deduction_rate'] > 0) {
                $monthHalfDayEarned = max(0, round($effectiveDailyWage - $shiftSettings['half_day_deduction_rate'], 2));
            }
            $monthBaseEarned = ($monthPresent * $effectiveDailyWage) + ($monthHalfDay * $monthHalfDayEarned);

            $monthAbsentPenalty = 0.0;
            if ($shiftSettings['absent_deduction_type'] === 'fixed_amount' && $shiftSettings['absent_deduction_rate'] > 0) {
                $monthAbsentPenalty = $monthAbsent * $shiftSettings['absent_deduction_rate'];
            } elseif ($shiftSettings['absent_deduction_type'] === 'one_day_wage') {
                $monthAbsentPenalty = $monthAbsent * $effectiveDailyWage;
            }

            $monthOvertimeEarned = 0.0;
            foreach ($monthAtts as $mAtt) {
                $otHours = (float) $mAtt->overtime_hours;
                if ($otHours > 0) {
                    $rate = (! empty($mAtt->trip_destination) && empty($mAtt->custom_task_name)) ? $homeOvertimeRate : $stdOvertimeRate;
                    $monthOvertimeEarned += ($otHours * $rate);
                }
                if ((float) $mAtt->custom_task_amount > 0) {
                    $monthOvertimeEarned += (float) $mAtt->custom_task_amount;
                } elseif ((float) $mAtt->custom_task_hours > 0 && (float) $mAtt->custom_task_rate > 0) {
                    $monthOvertimeEarned += ((float) $mAtt->custom_task_hours * (float) $mAtt->custom_task_rate);
                }
            }
            $monthOvertimeEarned = round($monthOvertimeEarned, 2);
            $monthTotalEarned = round($monthBaseEarned + $monthOvertimeEarned + $monthFuel + $monthBonus - ($monthDeductions + $monthAbsentPenalty), 2);

            $monthPayments = $emp->payments->filter(fn ($p) => $p->paid_at && $p->paid_at->toDateString() >= $monthStart && $p->paid_at->toDateString() <= $monthEnd);
            $monthTotalPaid = (float) $monthPayments->sum('amount_iqd');
            $monthRemaining = round($monthTotalEarned - $monthTotalPaid, 2);

            $paymentsList = $rangePayments->map(fn ($p) => [
                'id' => $p->id,
                'voucher_no' => $p->voucher_no,
                'amount' => (float) $p->amount,
                'amount_iqd' => (float) $p->amount_iqd,
                'currency' => $p->currency,
                'paid_at' => $p->paid_at?->format('Y/m/d'),
                'note' => $p->note,
            ])->values()->all();

            return [
                'id' => $emp->id,
                'name' => $emp->name,
                'phone' => $emp->phone ?? '',
                'job_title' => $emp->job_title,
                'job_title_label' => $emp->job_title_label,
                'salary_type' => $canSeeMoney ? $salaryType : 'daily',
                'salary_type_label' => $canSeeMoney ? $emp->salary_type_label : '',
                'daily_wage' => $canSeeMoney ? $dailyWage : 0,
                'effective_daily_wage' => $canSeeMoney ? $effectiveDailyWage : 0,
                'wage_currency' => $emp->wage_currency ?? 'IQD',
                'hire_date' => $emp->hire_date?->format('Y/m/d'),
                'note' => $emp->note ?? '',
                'cells' => $cells,
                'present_count' => $presentCount,
                'half_day_count' => $halfDayCount,
                'leave_count' => $leaveCount,
                'absent_count' => $absentCount,
                'holiday_count' => $holidayCount,
                'total_overtime' => $totalOvertime,
                'total_fuel' => $canSeeMoney ? $totalFuel : 0,
                'total_temp_exit' => $totalTempExit,
                'total_late_minutes' => $totalLateMinutes,
                'late_days_count' => $lateDaysCount,
                'total_deductions' => $canSeeMoney ? $totalDeductions : 0,
                'total_bonus' => $canSeeMoney ? $totalBonus : 0,
                'base_earned' => $canSeeMoney ? $baseEarned : 0,
                'overtime_earned' => $canSeeMoney ? $overtimeEarned : 0,
                'total_earned' => $canSeeMoney ? $totalEarned : 0,
                'total_paid' => $canSeeMoney ? $totalPaid : 0,
                'remaining_balance' => $canSeeMoney ? $remainingBalance : 0,
                'payments' => $canSeeMoney ? $paymentsList : [],
                'month_summary' => [
                    'present_count' => $monthPresent,
                    'half_day_count' => $monthHalfDay,
                    'absent_count' => $monthAbsent,
                    'leave_count' => $monthLeave,
                    'overtime_hours' => $monthOvertime,
                    'fuel_expense' => $canSeeMoney ? $monthFuel : 0,
                    'late_minutes' => $monthLateMinutes,
                    'late_days' => $monthLateDays,
                    'total_earned' => $canSeeMoney ? $monthTotalEarned : 0,
                    'total_paid' => $canSeeMoney ? $monthTotalPaid : 0,
                    'remaining' => $canSeeMoney ? $monthRemaining : 0,
                ],
            ];
        })->values()->all();

        // ئاماری کۆڵۆمەکان بۆ خوارەوەی خشتەی دەفتەرەکە
        $dayTotals = [];
        foreach ($days as $d) {
            $dStr = $d['date'];
            $presentOnDay = 0;
            $halfDayOnDay = 0;
            $overtimeOnDay = 0.0;
            $fuelOnDay = 0.0;
            $lateOnDay = 0;

            foreach ($employeesMatrix as $row) {
                $cell = $row['cells'][$dStr] ?? null;
                if ($cell) {
                    if ($cell['status'] === 'present') $presentOnDay++;
                    elseif ($cell['status'] === 'half_day') $halfDayOnDay++;
                    $overtimeOnDay += $cell['overtime_hours'];
                    $fuelOnDay += $cell['fuel_expense'];
                    if ($cell['late_minutes'] > 0) $lateOnDay++;
                }
            }

            $dayTotals[$dStr] = [
                'present' => $presentOnDay,
                'half_day' => $halfDayOnDay,
                'overtime' => $overtimeOnDay,
                'fuel' => $fuelOnDay,
                'late' => $lateOnDay,
            ];
        }

        // کۆی گشتییەکانی سەرجەم ماوەکە
        $totalEmployeesCount = count($employeesMatrix);
        $totalPresentManDays = array_sum(array_column($employeesMatrix, 'present_count'));
        $totalHalfDays = array_sum(array_column($employeesMatrix, 'half_day_count'));
        $totalOvertimeHours = array_sum(array_column($employeesMatrix, 'total_overtime'));
        $totalFuelExpenses = array_sum(array_column($employeesMatrix, 'total_fuel'));
        $totalLateDeductions = array_sum(array_column($employeesMatrix, 'total_deductions'));
        $totalEarnedAll = array_sum(array_column($employeesMatrix, 'total_earned'));
        $totalPaidAll = array_sum(array_column($employeesMatrix, 'total_paid'));
        $totalRemainingAll = array_sum(array_column($employeesMatrix, 'remaining_balance'));

        $cashBoxes = \App\Models\CashBox::all()->each(function ($box) {
            $box->balance = (float) $box->balance();
        });

        return view('workshop.employees', compact(
            'canSeeMoney',
            'employees',
            'employeesMatrix',
            'days',
            'dayTotals',
            'from',
            'to',
            'rangeType',
            'weekOffset',
            'shiftSettings',
            'totalEmployeesCount',
            'totalPresentManDays',
            'totalHalfDays',
            'totalOvertimeHours',
            'totalFuelExpenses',
            'totalLateDeductions',
            'totalEarnedAll',
            'totalPaidAll',
            'totalRemainingAll',
            'cashBoxes'
        ));
    }

    /** گۆڕینی دۆخی دروستکردنی وەسڵ */
    public function updateStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:confirmed,in_production,ready,delivered'],
        ]);

        $order->update(['status' => $validated['status']]);

        $messages = [
            'confirmed' => 'وەسڵ خرایە چاوەڕوانی.',
            'in_production' => 'دەست بە دروستکردنی کارەکە کرا ⚙️',
            'ready' => 'کارەکە تەواوبوو و ئامادەیە بۆ ڕادەستکردن ✅',
            'delivered' => 'کارەکە ڕادەستی کڕیار کرا 🚚',
        ];

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'ok' => true,
                'message' => $messages[$validated['status']] ?? 'دۆخی وەسڵ گۆڕدرا.',
                'status' => $validated['status'],
                'status_label' => $order->status_label,
            ]);
        }

        return back()->with('ok', $messages[$validated['status']] ?? 'دۆخی وەسڵ گۆڕدرا.');
    }

    /** زیادکردنی خێرای مەوادی نوێ بۆ مەخزەن و شوێنی دروستکردن */
    public function storeRawMaterial(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'item_category_id' => ['nullable'],
            'new_category_name' => ['nullable', 'string', 'max:255'],
            'unit_id' => ['nullable'],
            'new_unit_name' => ['nullable', 'string', 'max:255'],
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'initial_qty' => ['nullable', 'numeric', 'min:0'],
            'min_qty' => ['nullable', 'numeric', 'min:0'],
            'date' => ['nullable', 'date'],
            'note' => ['nullable', 'string'],
        ], [], [
            'name' => 'ناوی مەواد',
            'unit_id' => 'یەکە',
            'new_unit_name' => 'یەکەی نوێ',
            'warehouse_id' => 'کۆگا',
        ]);

        // جۆر یان پۆل
        $categoryId = null;
        if (!empty($validated['new_category_name'])) {
            $cat = ItemCategory::firstOrCreate([
                'name' => trim($validated['new_category_name']),
            ]);
            $categoryId = $cat->id;
        } elseif (!empty($validated['item_category_id']) && $validated['item_category_id'] !== '__NEW__') {
            $categoryId = (int) $validated['item_category_id'];
        }

        // یەکە
        $unitId = null;
        if (!empty($validated['new_unit_name'])) {
            $unit = Unit::firstOrCreate([
                'name' => trim($validated['new_unit_name']),
            ], [
                'type' => 'count',
                'is_active' => true,
            ]);
            $unitId = $unit->id;
        } elseif (!empty($validated['unit_id']) && $validated['unit_id'] !== '__NEW__') {
            $unitId = (int) $validated['unit_id'];
        }

        if (!$unitId) {
            $defaultUnit = Unit::first();
            $unitId = $defaultUnit?->id;
        }

        DB::transaction(function () use ($validated, $categoryId, $unitId) {
            $item = Item::create([
                'code' => Item::nextCode(),
                'name' => $validated['name'],
                'item_category_id' => $categoryId,
                'unit_id' => $unitId,
                'min_qty' => !empty($validated['min_qty']) ? (float) $validated['min_qty'] : 5,
                'is_for_sale' => false,
                'is_active' => true,
                'note' => $validated['note'] ?? null,
            ]);

            $qty = (float) ($validated['initial_qty'] ?? 0);
            if ($qty > 0) {
                $this->stockService->record(
                    itemId: $item->id,
                    warehouseId: (int) $validated['warehouse_id'],
                    direction: 'in',
                    qty: $qty,
                    reason: 'opening',
                    extra: [
                        'note' => 'مەوادی سەرەتایی دروستکردن',
                        'moved_at' => $validated['date'] ?? now()->toDateString(),
                    ]
                );
            }
        });

        return back()->with('ok', 'مەوادی نوێ زیادکرا بۆ مەخزەن.');
    }

    /** زیادکردنی بڕ بۆ مەوادێکی هەبوو لە شوێنی دروستکردن (Stock In) */
    public function stockIn(Request $request)
    {
        $validated = $request->validate([
            'item_id' => ['required', 'exists:items,id'],
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'qty' => ['required', 'numeric', 'min:0.01'],
            'date' => ['nullable', 'date'],
            'note' => ['nullable', 'string', 'max:255'],
        ], [], [
            'item_id' => 'مەواد',
            'qty' => 'بڕ',
        ]);

        $this->stockService->record(
            itemId: (int) $validated['item_id'],
            warehouseId: (int) $validated['warehouse_id'],
            direction: 'in',
            qty: (float) $validated['qty'],
            reason: 'adjustment',
            extra: [
                'note' => ($validated['note'] ?? null) ?: 'زیادکردنی مەواد بۆ کارگە',
                'moved_at' => $validated['date'] ?? now()->toDateString(),
            ]
        );

        return back()->with('ok', 'بڕی مەوادەکە بە سەرکەوتوویی زیادکرا.');
    }

    /** بەکارهێنان و کەمکردنەوەی مەواد لە دروستکردندا (Stock Out) */
    public function stockOut(Request $request)
    {
        $validated = $request->validate([
            'item_id' => ['required', 'exists:items,id'],
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'qty' => ['required', 'numeric', 'min:0.01'],
            'date' => ['nullable', 'date'],
            'order_id' => ['nullable', 'exists:orders,id'],
            'note' => ['nullable', 'string', 'max:255'],
        ], [], [
            'item_id' => 'مەواد',
            'qty' => 'بڕ',
        ]);

        $item = Item::query()->withStock($validated['warehouse_id'])->findOrFail($validated['item_id']);
        if ((float) $validated['qty'] > (float) $item->stock_qty) {
            $unitName = $item->unit?->name ?? '';
            return back()->withInput()->with('error', "بڕی بەردەست تەنها ({$item->stock_qty} {$unitName}) یە، ناتوانیت زیاتر لەوە کەم بکەیتەوە.");
        }

        $note = $validated['note'] ?? '';
        $order = null;

        if (!empty($validated['order_id'])) {
            $order = Order::with('customer')->find($validated['order_id']);
            if ($order) {
                $orderLabel = "بۆ وەسڵی #" . ($order->invoice_no ?: $order->id) . ($order->customer ? " ({$order->customer->name})" : "");
                $note = $note ? "{$orderLabel} - {$note}" : $orderLabel;
            }
        }

        $this->stockService->record(
            itemId: (int) $validated['item_id'],
            warehouseId: (int) $validated['warehouse_id'],
            direction: 'out',
            qty: (float) $validated['qty'],
            reason: 'production',
            reference: $order,
            extra: [
                'note' => $note ?: 'بەکارهێنان لە دروستکردندا',
                'moved_at' => $validated['date'] ?? now()->toDateString(),
            ]
        );

        return back()->with('ok', 'بەکارهێنانی مەواد تۆمارکرا.');
    }

    /** نوێکردنەوەی ڕێکخستنەکانی دەوام و کاتی زیادە */
    public function updateSettings(Request $request)
    {
        if (! auth()->user()->isAdmin()) {
            abort(403, 'تەنها بەڕێوەبەر دەسەڵاتی دەستکاریکردنی سێتینگی هەیە.');
        }

        $validated = $request->validate([
            'workshop_work_hours' => ['required', 'numeric', 'min:1', 'max:24'],
            'workshop_overtime_hourly_rate' => ['nullable', 'numeric', 'min:0'],
            'workshop_weekly_holiday' => ['required', 'string'],
            'workshop_work_start' => ['nullable', 'string'],
            'workshop_work_end' => ['nullable', 'string'],
            'workshop_overtime_multiplier' => ['nullable', 'numeric'],
            'workshop_home_visit_hourly_rate' => ['nullable', 'numeric', 'min:0'],
            'workshop_half_day_deduction_type' => ['nullable', 'string'],
            'workshop_half_day_deduction_rate' => ['nullable', 'numeric', 'min:0'],
            'workshop_absent_deduction_type' => ['nullable', 'string'],
            'workshop_absent_deduction_rate' => ['nullable', 'numeric', 'min:0'],
            'workshop_late_grace_minutes' => ['nullable', 'numeric'],
            'workshop_late_deduction_type' => ['nullable', 'string'],
            'workshop_late_deduction_rate' => ['nullable', 'numeric'],
            'workshop_late_weekly_threshold_days' => ['nullable', 'numeric'],
            'workshop_late_weekly_penalty_amount' => ['nullable', 'numeric'],
        ]);

        foreach ($validated as $key => $value) {
            Setting::put($key, (string) $value);
        }

        if ($request->has('workshop_custom_overtime_rates')) {
            $rates = $request->input('workshop_custom_overtime_rates');
            if (is_array($rates)) {
                $cleaned = [];
                foreach ($rates as $r) {
                    $name = trim($r['name'] ?? '');
                    $rate = (float) ($r['rate'] ?? 0);
                    $unit = in_array($r['unit'] ?? '', ['hourly', 'fixed']) ? $r['unit'] : 'hourly';
                    if ($name !== '' || $rate > 0) {
                        $cleaned[] = [
                            'name' => $name,
                            'rate' => $rate,
                            'unit' => $unit,
                        ];
                    }
                }
                Setting::put('workshop_custom_overtime_rates', json_encode($cleaned, JSON_UNESCAPED_UNICODE));
            } elseif (is_string($rates)) {
                Setting::put('workshop_custom_overtime_rates', $rates);
            }
        }

        if ($request->wantsJson()) {
            return response()->json(['ok' => true, 'message' => 'ڕێکخستنەکانی دەوام و کاتی زیادە بە سەرکەوتوویی پاشەکەوتکران.']);
        }

        return back()->with('ok', 'ڕێکخستنەکانی دەوام و کاتی زیادە بە سەرکەوتوویی پاشەکەوتکران.');
    }

    /** زیادکردنی خێرای وەستا و حەمەڵ */
    public function quickStoreEmployee(Request $request)
    {
        if (! auth()->user()->isAdmin()) {
            abort(403, 'تەنها بەڕێوەبەر دەسەڵاتی زیادکردنی کارمەندی هەیە.');
        }

        $jobTitle = $request->input('job_title');
        if ($jobTitle === '__NEW__' || !empty($request->input('custom_job_title'))) {
            $jobTitle = trim($request->input('custom_job_title') ?: $jobTitle);
        }
        $request->merge(['job_title' => $jobTitle ?: 'master']);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'job_title' => ['required', 'string', 'max:100'],
            'salary_type' => ['nullable', 'in:daily,weekly,monthly'],
            'daily_wage' => ['nullable', 'numeric', 'min:0'],
            'wage_currency' => ['nullable', 'in:IQD,USD'],
            'note' => ['nullable', 'string', 'max:255'],
        ], [], ['name' => 'ناو', 'job_title' => 'پیشە', 'salary_type' => 'شێوازی پارەدان']);

        try {
            $data = [
                'name' => $validated['name'],
                'phone' => $validated['phone'] ?? null,
                'job_title' => $validated['job_title'],
                'daily_wage' => $validated['daily_wage'] ?? 0,
                'wage_currency' => $validated['wage_currency'] ?? 'IQD',
                'hire_date' => now()->toDateString(),
                'is_active' => true,
                'note' => $validated['note'] ?? null,
            ];

            if (\Illuminate\Support\Facades\Schema::hasColumn('employees', 'salary_type')) {
                $data['salary_type'] = $validated['salary_type'] ?? 'daily';
            }

            $employee = Employee::create($data);

            if ($request->wantsJson()) {
                return response()->json([
                    'ok' => true,
                    'message' => "وەستا / کارمەند {$employee->name} زیادکرا.",
                    'employee' => [
                        'id' => $employee->id,
                        'name' => $employee->name,
                        'phone' => $employee->phone,
                        'job_title' => $employee->job_title,
                        'job_title_label' => $employee->job_title_label,
                        'salary_type' => $employee->salary_type ?? 'daily',
                        'salary_type_label' => $employee->salary_type_label ?? 'ڕۆژانە',
                        'daily_wage' => (float) $employee->daily_wage,
                        'wage_currency' => $employee->wage_currency ?? 'IQD',
                        'hire_date' => $employee->hire_date?->format('Y/m/d'),
                        'is_active' => true,
                        'note' => $employee->note,
                        'attendance' => null,
                    ]
                ]);
            }

            return back()->with('ok', "وەستا / کارمەند {$employee->name} بە سەرکەوتوویی زیادکرا.");
        } catch (\Throwable $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'ok' => false,
                    'message' => 'هەڵە لە پاشەکەوتکردندا: ' . $e->getMessage(),
                ], 500);
            }
            return back()->with('err', 'هەڵە لە پاشەکەوتکردندا: ' . $e->getMessage());
        }
    }

    /** دەستکاری مووچە و زانیاری وەستا لەلایەن بەڕێوەبەرەوە */
    public function updateEmployeeWage(Request $request, Employee $employee)
    {
        if (! auth()->user()->isAdmin()) {
            abort(403, 'تەنها بەڕێوەبەر دەسەڵاتی دەستکاریکردنی مووچەی هەیە.');
        }

        $jobTitle = $request->input('job_title');
        if ($jobTitle === '__NEW__' || !empty($request->input('custom_job_title'))) {
            $jobTitle = trim($request->input('custom_job_title') ?: $jobTitle);
        }
        $request->merge(['job_title' => $jobTitle ?: $employee->job_title]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'job_title' => ['required', 'string', 'max:100'],
            'salary_type' => ['nullable', 'in:daily,weekly,monthly'],
            'daily_wage' => ['required', 'numeric', 'min:0'],
            'wage_currency' => ['nullable', 'in:IQD,USD'],
            'is_active' => ['nullable', 'boolean'],
            'note' => ['nullable', 'string', 'max:255'],
        ], [], ['name' => 'ناو', 'job_title' => 'پیشە', 'salary_type' => 'شێوازی پارەدان']);

        try {
            $data = [
                'name' => $validated['name'],
                'phone' => $validated['phone'] ?? null,
                'job_title' => $validated['job_title'],
                'daily_wage' => $validated['daily_wage'],
                'wage_currency' => $validated['wage_currency'] ?? 'IQD',
                'is_active' => $request->has('is_active') ? $request->boolean('is_active') : $employee->is_active,
                'note' => $validated['note'] ?? null,
            ];

            if (\Illuminate\Support\Facades\Schema::hasColumn('employees', 'salary_type')) {
                $data['salary_type'] = $validated['salary_type'] ?? 'daily';
            }

            $employee->update($data);

            if ($request->wantsJson()) {
                return response()->json([
                    'ok' => true,
                    'message' => "زانیاری و مووچەی {$employee->name} نوێکرایەوە.",
                    'job_title' => $employee->job_title,
                    'job_title_label' => $employee->job_title_label,
                    'salary_type' => $employee->salary_type ?? 'daily',
                    'salary_type_label' => $employee->salary_type_label ?? 'ڕۆژانە',
                    'daily_wage' => (float) $employee->daily_wage,
                    'effective_daily_wage' => (float) $employee->effective_daily_wage,
                ]);
            }

            return back()->with('ok', "زانیاری و مووچەی {$employee->name} بە سەرکەوتوویی نوێکرایەوە.");
        } catch (\Throwable $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'ok' => false,
                    'message' => 'هەڵە لە نوێکردنەوەدا: ' . $e->getMessage(),
                ], 500);
            }
            return back()->with('err', 'هەڵە لە نوێکردنەوەدا: ' . $e->getMessage());
        }
    }

    /** گۆڕینی خێرای دۆخی خانەی ئامادەبوون (سەح / نیوەڕۆژ / غائیب / ئیجازە / هیچی تر) بە یەک کلیک */
    public function toggleAttendanceCell(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'work_date' => ['required', 'date'],
            'status' => ['nullable', 'in:present,half_day,absent,leave,holiday,delete'],
        ]);

        $employee = Employee::findOrFail($validated['employee_id']);
        $date = $validated['work_date'];
        $status = $validated['status'] ?? null;

        $attendance = Attendance::where('employee_id', $employee->id)
            ->whereDate('work_date', $date)
            ->first();

        if (Attendance::isWeeklyHoliday($date)) {
            return response()->json([
                'ok' => false,
                'message' => 'ئەم ڕۆژە پشووی هەفتانەی کارگەیە و ناتوانرێت دەوامی لێ تۆمار بکرێت.',
            ], 422);
        }

        // سوڕانەوەی دۆخ بە یەک کلیک: هاتووە -> نیو ڕۆژ -> نەهاتووە -> خاڵی -> هاتووە
        if (! $status) {
            $currentStatus = $attendance?->status;
            if (! $attendance || ! in_array($currentStatus, ['present', 'half_day', 'absent'])) {
                $status = 'present';
            } elseif ($currentStatus === 'present') {
                $status = 'half_day';
            } elseif ($currentStatus === 'half_day') {
                $status = 'absent';
            } else {
                $status = 'delete';
            }
        }

        if ($status === 'delete') {
            if ($attendance) {
                $attendance->delete();
            }
            return response()->json([
                'ok' => true,
                'status' => null,
                'status_label' => 'تۆمارنەکراو',
                'attendance' => null,
                'message' => "تۆماری ئامادەبوونی {$employee->name} سڕدرایەوە.",
            ]);
        }

        if (! $attendance) {
            $attendance = new Attendance([
                'employee_id' => $employee->id,
                'work_date' => $date,
            ]);
        }

        $attendance->status = $status;
        $effectiveWage = $employee->effective_daily_wage;
        $halfDayDedType = Setting::get('workshop_half_day_deduction_type', 'percentage');
        $halfDayDedRate = (float) Setting::get('workshop_half_day_deduction_rate', 0);
        $halfDayWage = ($halfDayDedType === 'fixed_amount' && $halfDayDedRate > 0)
            ? max(0, round($effectiveWage - $halfDayDedRate, 2))
            : round($effectiveWage * 0.5, 2);

        if ($status === 'present') {
            $attendance->wage_snapshot = $effectiveWage;
        } elseif ($status === 'half_day') {
            $attendance->wage_snapshot = $halfDayWage;
        } else {
            $attendance->wage_snapshot = 0;
        }

        $attendance->user_id = auth()->id();
        $attendance->save();

        return response()->json([
            'ok' => true,
            'status' => $attendance->status,
            'status_label' => $attendance->status_label,
            'attendance' => [
                'id' => $attendance->id,
                'status' => $attendance->status,
                'status_label' => $attendance->status_label,
                'check_in' => $attendance->check_in ? substr($attendance->check_in, 0, 5) : '',
                'check_out' => $attendance->check_out ? substr($attendance->check_out, 0, 5) : '',
                'hours' => (float) $attendance->hours,
                'overtime_hours' => (float) $attendance->overtime_hours,
                'late_minutes' => (int) $attendance->late_minutes,
                'temporary_exit_hours' => (float) $attendance->temporary_exit_hours,
                'exit_reason' => $attendance->exit_reason ?? '',
                'fuel_expense' => (float) $attendance->fuel_expense,
                'deduction_amount' => (float) $attendance->deduction_amount,
                'bonus_amount' => (float) $attendance->bonus_amount,
                'trip_destination' => $attendance->trip_destination ?? '',
                'note' => $attendance->note ?? '',
            ],
            'message' => "دۆخی {$employee->name} بۆ بەرواری {$date} نوێکرایەوە.",
        ]);
    }

    /** پاشەکەوتکردنی دێتەلی تەواوی خانەی سەح و دەوامی ڕۆژێک (موداڵی دێتەل) */
    public function saveAttendanceDetail(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'work_date' => ['required', 'date'],
            'status' => ['required', 'in:present,half_day,absent,leave,holiday,delete'],
            'check_in' => ['nullable', 'string'],
            'check_out' => ['nullable', 'string'],
            'hours' => ['nullable', 'numeric', 'min:0'],
            'overtime_hours' => ['nullable', 'numeric', 'min:0'],
            'late_minutes' => ['nullable', 'integer', 'min:0'],
            'temporary_exit_hours' => ['nullable', 'numeric', 'min:0'],
            'exit_reason' => ['nullable', 'string', 'max:255'],
            'fuel_expense' => ['nullable', 'numeric', 'min:0'],
            'deduction_amount' => ['nullable', 'numeric', 'min:0'],
            'bonus_amount' => ['nullable', 'numeric', 'min:0'],
            'trip_destination' => ['nullable', 'string', 'max:255'],
            'custom_task_name' => ['nullable', 'string', 'max:255'],
            'custom_task_rate' => ['nullable', 'numeric', 'min:0'],
            'custom_task_unit' => ['nullable', 'string', 'in:hourly,fixed'],
            'custom_task_hours' => ['nullable', 'numeric', 'min:0'],
            'custom_task_amount' => ['nullable', 'numeric', 'min:0'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $employee = Employee::findOrFail($validated['employee_id']);
        $date = $validated['work_date'];

        $attendance = Attendance::where('employee_id', $employee->id)
            ->whereDate('work_date', $date)
            ->first();

        if ($validated['status'] === 'delete') {
            if ($attendance) {
                $attendance->delete();
            }
            return response()->json([
                'ok' => true,
                'status' => null,
                'status_label' => 'تۆمارنەکراو',
                'attendance' => null,
                'message' => "تۆماری ڕۆژەکە سڕدرایەوە.",
            ]);
        }

        if (! $attendance) {
            $attendance = new Attendance([
                'employee_id' => $employee->id,
                'work_date' => $date,
            ]);
        }

        $status = $validated['status'];
        $checkIn = $validated['check_in'] ?? null;
        $checkOut = $validated['check_out'] ?? null;

        // ئەگەر کاتژمێری هاتن و چوون هەبوو و hours پڕنەکرابۆوە، با خۆکار حیساب بێت
        $calculated = Attendance::calculateHours($checkIn, $checkOut);
        $hours = isset($validated['hours']) && $validated['hours'] !== '' ? (float) $validated['hours'] : $calculated['hours'];
        $overtime = isset($validated['overtime_hours']) && $validated['overtime_hours'] !== '' ? (float) $validated['overtime_hours'] : $calculated['overtime'];

        // هەژمارکردنی تاخیربوون ئەگەر دەستی دیاری نەکرابێت
        $lateMinutes = isset($validated['late_minutes']) && $validated['late_minutes'] !== '' ? (int) $validated['late_minutes'] : Attendance::calculateLateMinutes($checkIn, $date);

        $taskName = $validated['custom_task_name'] ?? null;
        $taskRate = (float) ($validated['custom_task_rate'] ?? 0);
        $taskUnit = $validated['custom_task_unit'] ?? 'hourly';
        $taskHours = (float) ($validated['custom_task_hours'] ?? 0);
        $taskAmount = 0.0;
        if (! empty($taskName) || $taskRate > 0) {
            if ($taskUnit === 'fixed') {
                $taskAmount = $taskRate;
            } else {
                $taskAmount = round($taskHours * $taskRate, 2);
            }
        }

        $attendance->status = $status;
        $attendance->check_in = $checkIn ? (strlen($checkIn) === 5 ? "{$checkIn}:00" : $checkIn) : null;
        $attendance->check_out = $checkOut ? (strlen($checkOut) === 5 ? "{$checkOut}:00" : $checkOut) : null;
        $attendance->hours = $hours;
        $attendance->overtime_hours = $overtime;
        $attendance->late_minutes = $lateMinutes;
        $attendance->temporary_exit_hours = (float) ($validated['temporary_exit_hours'] ?? 0);
        $attendance->exit_reason = $validated['exit_reason'] ?? null;
        $attendance->fuel_expense = (float) ($validated['fuel_expense'] ?? 0);
        $attendance->deduction_amount = (float) ($validated['deduction_amount'] ?? 0);
        $attendance->bonus_amount = (float) ($validated['bonus_amount'] ?? 0);
        $attendance->trip_destination = $validated['trip_destination'] ?? null;
        $attendance->custom_task_name = $taskName;
        $attendance->custom_task_rate = $taskRate;
        $attendance->custom_task_unit = $taskUnit;
        $attendance->custom_task_hours = $taskHours;
        $attendance->custom_task_amount = $taskAmount;
        $attendance->note = $validated['note'] ?? null;

        $effectiveWage = $employee->effective_daily_wage;
        $halfDayDedType = Setting::get('workshop_half_day_deduction_type', 'percentage');
        $halfDayDedRate = (float) Setting::get('workshop_half_day_deduction_rate', 0);
        $halfDayWage = ($halfDayDedType === 'fixed_amount' && $halfDayDedRate > 0)
            ? max(0, round($effectiveWage - $halfDayDedRate, 2))
            : round($effectiveWage * 0.5, 2);

        if ($status === 'present') {
            $attendance->wage_snapshot = $effectiveWage;
        } elseif ($status === 'half_day') {
            $attendance->wage_snapshot = $halfDayWage;
        } else {
            $attendance->wage_snapshot = 0;
        }

        $attendance->user_id = auth()->id();
        $attendance->save();

        return response()->json([
            'ok' => true,
            'status' => $attendance->status,
            'status_label' => $attendance->status_label,
            'attendance' => [
                'id' => $attendance->id,
                'status' => $attendance->status,
                'status_label' => $attendance->status_label,
                'check_in' => $attendance->check_in ? substr($attendance->check_in, 0, 5) : '',
                'check_out' => $attendance->check_out ? substr($attendance->check_out, 0, 5) : '',
                'hours' => (float) $attendance->hours,
                'overtime_hours' => (float) $attendance->overtime_hours,
                'late_minutes' => (int) $attendance->late_minutes,
                'temporary_exit_hours' => (float) $attendance->temporary_exit_hours,
                'exit_reason' => $attendance->exit_reason ?? '',
                'fuel_expense' => (float) $attendance->fuel_expense,
                'deduction_amount' => (float) $attendance->deduction_amount,
                'bonus_amount' => (float) $attendance->bonus_amount,
                'trip_destination' => $attendance->trip_destination ?? '',
                'custom_task_name' => $attendance->custom_task_name ?? '',
                'custom_task_rate' => (float) $attendance->custom_task_rate,
                'custom_task_unit' => $attendance->custom_task_unit ?? 'hourly',
                'custom_task_hours' => (float) $attendance->custom_task_hours,
                'custom_task_amount' => (float) $attendance->custom_task_amount,
                'note' => $attendance->note ?? '',
            ],
            'message' => "دێتەلی دەوامی {$employee->name} بە سەرکەوتوویی نوێکرایەوە.",
        ]);
    }

    /** بەدەستهێنانی دێتەلی مانگانە و ڕاپۆرتی حیساباتی تاکەکەسی بۆ وەستا */
    public function employeeMonthDetails(Employee $employee, Request $request)
    {
        if (! auth()->user()->isAdmin()) {
            abort(403, 'تەنها بەڕێوەبەر دەسەڵاتی بینینی حیساباتی دارایی هەیە.');
        }

        $yearMonth = $request->input('month', now()->format('Y-m'));
        $startDate = \Carbon\Carbon::parse("{$yearMonth}-01")->startOfMonth()->toDateString();
        $endDate = \Carbon\Carbon::parse("{$yearMonth}-01")->endOfMonth()->toDateString();

        $shiftSettings = [
            'work_hours' => (float) Setting::get('workshop_work_hours', 8),
            'weekly_holiday' => Setting::get('workshop_weekly_holiday', 'friday'),
            'overtime_hourly_rate' => (float) Setting::get('workshop_overtime_hourly_rate', 0),
            'overtime_multiplier' => (float) Setting::get('workshop_overtime_multiplier', 1.0),
            'home_visit_hourly_rate' => (float) Setting::get('workshop_home_visit_hourly_rate', 0),
            'half_day_deduction_type' => Setting::get('workshop_half_day_deduction_type', 'percentage'),
            'half_day_deduction_rate' => (float) Setting::get('workshop_half_day_deduction_rate', 0),
            'absent_deduction_type' => Setting::get('workshop_absent_deduction_type', 'none'),
            'absent_deduction_rate' => (float) Setting::get('workshop_absent_deduction_rate', 0),
        ];

        $attendances = $employee->attendances()
            ->whereBetween('work_date', [$startDate, $endDate])
            ->orderBy('work_date')
            ->get();

        $payments = $employee->payments()
            ->where('direction', 'out')
            ->whereBetween('paid_at', [$startDate, $endDate])
            ->orderByDesc('paid_at')
            ->get();

        $presentCount = $attendances->where('status', 'present')->count();
        $halfDayCount = $attendances->where('status', 'half_day')->count();
        $absentCount = $attendances->where('status', 'absent')->count();
        $leaveCount = $attendances->where('status', 'leave')->count();
        $holidayCount = $attendances->where('status', 'holiday')->count();

        $totalOvertime = (float) $attendances->sum('overtime_hours');
        $totalFuel = (float) $attendances->sum('fuel_expense');
        $totalDeductions = (float) $attendances->sum('deduction_amount');
        $totalBonus = (float) $attendances->sum('bonus_amount');
        $totalLateMinutes = (int) $attendances->sum('late_minutes');
        $lateDaysCount = $attendances->where('late_minutes', '>', 0)->count();

        $effectiveDailyWage = $employee->effective_daily_wage;
        if ($employee->salary_type === 'monthly') {
            $period = \Carbon\CarbonPeriod::create($startDate, $endDate);
            $workingDaysCount = 0;
            foreach ($period as $dt) {
                if (! Attendance::isWeeklyHoliday($dt)) {
                    $workingDaysCount++;
                }
            }
            if ($workingDaysCount > 0) {
                $effectiveDailyWage = (float) $employee->daily_wage / $workingDaysCount;
            }
        }

        $halfDayEarned = round($effectiveDailyWage * 0.5, 2);
        if ($shiftSettings['half_day_deduction_type'] === 'fixed_amount' && $shiftSettings['half_day_deduction_rate'] > 0) {
            $halfDayEarned = max(0, round($effectiveDailyWage - $shiftSettings['half_day_deduction_rate'], 2));
        }
        $baseEarned = round(($presentCount * $effectiveDailyWage) + ($halfDayCount * $halfDayEarned));
        $hourlyWage = $shiftSettings['work_hours'] > 0 ? ($effectiveDailyWage / $shiftSettings['work_hours']) : 0;
        $stdOvertimeRate = $shiftSettings['overtime_hourly_rate'] > 0
            ? $shiftSettings['overtime_hourly_rate']
            : ($hourlyWage * $shiftSettings['overtime_multiplier']);
        $homeOvertimeRate = $shiftSettings['home_visit_hourly_rate'] > 0
            ? $shiftSettings['home_visit_hourly_rate']
            : $stdOvertimeRate;

        $overtimeEarned = 0.0;
        foreach ($attendances as $att) {
            $otHours = (float) $att->overtime_hours;
            if ($otHours > 0) {
                $rate = (! empty($att->trip_destination) && empty($att->custom_task_name)) ? $homeOvertimeRate : $stdOvertimeRate;
                $overtimeEarned += ($otHours * $rate);
            }
            if ((float) $att->custom_task_amount > 0) {
                $overtimeEarned += (float) $att->custom_task_amount;
            } elseif ((float) $att->custom_task_hours > 0 && (float) $att->custom_task_rate > 0) {
                $overtimeEarned += ((float) $att->custom_task_hours * (float) $att->custom_task_rate);
            }
        }
        $overtimeEarned = round($overtimeEarned);

        // هەژمارکردنی سزای غیاببوونی کامل بۆ مانگەکە
        $calculatedAbsentPenalty = 0;
        if ($shiftSettings['absent_deduction_type'] === 'fixed_amount' && $shiftSettings['absent_deduction_rate'] > 0) {
            $calculatedAbsentPenalty = round($absentCount * $shiftSettings['absent_deduction_rate']);
        } elseif ($shiftSettings['absent_deduction_type'] === 'one_day_wage') {
            $calculatedAbsentPenalty = round($absentCount * $effectiveDailyWage);
        }

        // هەژمارکردنی یاسای تاخیربوونی کارگە بۆ مانگەکە
        $calculatedLatePenalty = 0;
        $lateDeductionType = Setting::get('workshop_late_deduction_type', 'none');
        $lateDeductionRate = (float) Setting::get('workshop_late_deduction_rate', 0);
        $weeklyThresholdDays = (int) Setting::get('workshop_late_weekly_threshold_days', 2);
        $weeklyPenaltyAmount = (float) Setting::get('workshop_late_weekly_penalty_amount', 0);

        if ($lateDeductionType === 'weekly_threshold' && $weeklyThresholdDays > 0) {
            if ($lateDaysCount >= $weeklyThresholdDays) {
                $weeksWithPenalty = ceil($lateDaysCount / $weeklyThresholdDays);
                $calculatedLatePenalty = $weeksWithPenalty * ($weeklyPenaltyAmount > 0 ? $weeklyPenaltyAmount : $effectiveDailyWage);
            }
        } elseif ($lateDeductionType === 'fixed_amount' && $lateDeductionRate > 0) {
            $calculatedLatePenalty = $lateDaysCount * $lateDeductionRate;
        }

        $allDeductions = round($totalDeductions + $calculatedLatePenalty + $calculatedAbsentPenalty);
        $totalEarned = round($baseEarned + $overtimeEarned + $totalFuel + $totalBonus - $allDeductions);

        $totalPaid = round((float) $payments->sum('amount_iqd'));
        $remainingBalance = round($totalEarned - $totalPaid);

        return response()->json([
            'ok' => true,
            'employee' => [
                'id' => $employee->id,
                'name' => $employee->name,
                'phone' => $employee->phone,
                'job_title' => $employee->job_title,
                'job_title_label' => $employee->job_title_label,
                'salary_type' => $employee->salary_type ?? 'daily',
                'salary_type_label' => $employee->salary_type_label ?? 'ڕۆژانە',
                'daily_wage' => (float) $employee->daily_wage,
                'effective_daily_wage' => $effectiveDailyWage,
                'wage_currency' => $employee->wage_currency ?? 'IQD',
            ],
            'month' => $yearMonth,
            'stats' => [
                'present_count' => $presentCount,
                'half_day_count' => $halfDayCount,
                'absent_count' => $absentCount,
                'leave_count' => $leaveCount,
                'holiday_count' => $holidayCount,
                'total_overtime' => $totalOvertime,
                'total_fuel' => $totalFuel,
                'total_late_minutes' => $totalLateMinutes,
                'late_days_count' => $lateDaysCount,
                'manual_deductions' => $totalDeductions,
                'late_penalty_deduction' => $calculatedLatePenalty,
                'absent_penalty_deduction' => $calculatedAbsentPenalty,
                'total_deductions' => $allDeductions,
                'total_bonus' => $totalBonus,
                'base_earned' => $baseEarned,
                'overtime_earned' => $overtimeEarned,
                'total_earned' => $totalEarned,
                'total_paid' => $totalPaid,
                'remaining_balance' => $remainingBalance,
            ],
            'attendances' => $attendances->map(function ($a) {
                $kurdishDays = [
                    0 => 'یەکشەممە',
                    1 => 'دووشەممە',
                    2 => 'سێشەممە',
                    3 => 'چوارشەممە',
                    4 => 'پێنجشەممە',
                    5 => 'هەینی',
                    6 => 'شەممە',
                ];
                $dayOfWeek = $a->work_date ? ($kurdishDays[$a->work_date->dayOfWeek] ?? '') : '';
                return [
                    'id' => $a->id,
                    'work_date' => $a->work_date?->format('Y/m/d'),
                    'day_name' => $dayOfWeek,
                    'status' => $a->status,
                    'status_label' => $a->status_label,
                    'overtime_hours' => (float) $a->overtime_hours,
                    'trip_destination' => $a->trip_destination,
                    'custom_task_name' => $a->custom_task_name,
                    'custom_task_rate' => (float) $a->custom_task_rate,
                    'custom_task_unit' => $a->custom_task_unit ?? 'hourly',
                    'custom_task_hours' => (float) $a->custom_task_hours,
                    'custom_task_amount' => (float) $a->custom_task_amount,
                    'exit_reason' => $a->exit_reason,
                    'fuel_expense' => (float) $a->fuel_expense,
                    'deduction_amount' => (float) $a->deduction_amount,
                    'bonus_amount' => (float) $a->bonus_amount,
                    'note' => $a->note,
                ];
            })->values()->all(),
            'payments' => $payments->map(fn ($p) => [
                'id' => $p->id,
                'voucher_no' => $p->voucher_no,
                'amount' => (float) $p->amount,
                'amount_iqd' => (float) $p->amount_iqd,
                'currency' => $p->currency,
                'paid_at' => $p->paid_at?->format('Y/m/d'),
                'note' => $p->note,
            ])->values()->all(),
        ]);
    }

    /** سەح لێدانی هەمووان بۆ ڕۆژێکی دیاریکراو (سەحی بەکۆمەڵ) */
    public function batchMarkDay(Request $request)
    {
        $validated = $request->validate([
            'work_date' => ['required', 'date'],
            'status' => ['required', 'in:present,half_day,absent,leave,holiday'],
        ]);

        $date = $validated['work_date'];
        $status = $validated['status'];

        if (Attendance::isWeeklyHoliday($date)) {
            return response()->json([
                'ok' => false,
                'message' => 'ئەمڕۆ پشووی هەفتانەی کارگەیە و ناتوانرێت دەوامی بەکۆمەڵی لێ تۆمار بکرێت.',
            ], 422);
        }
        $employees = Employee::active()->get();

        DB::transaction(function () use ($employees, $date, $status) {
            foreach ($employees as $employee) {
                $attendance = Attendance::where('employee_id', $employee->id)
                    ->whereDate('work_date', $date)
                    ->first();

                if (! $attendance) {
                    $attendance = new Attendance([
                        'employee_id' => $employee->id,
                        'work_date' => $date,
                    ]);
                }

                $attendance->status = $status;
                $effectiveWage = $employee->effective_daily_wage;
                if ($status === 'present') {
                    $attendance->wage_snapshot = $effectiveWage;
                } elseif ($status === 'half_day') {
                    $attendance->wage_snapshot = round($effectiveWage * 0.5, 2);
                } else {
                    $attendance->wage_snapshot = 0;
                }
                $attendance->user_id = auth()->id();
                $attendance->save();
            }
        });

        if ($request->wantsJson()) {
            return response()->json([
                'ok' => true,
                'message' => "ئامادەبوونی هەموو وەستاکان بۆ بەرواری {$date} بە سەرکەوتوویی تۆمارکرا.",
            ]);
        }

        return back()->with('ok', "ئامادەبوونی هەموو وەستاکان بۆ بەرواری {$date} تۆمارکرا.");
    }

    /** تۆمارکردنی پێشەکی یان دانی مووچە بۆ وەستا لە ڕێگەی قاصەوە */
    public function recordEmployeePayment(Request $request, \App\Services\PaymentService $paymentService)
    {
        if (! auth()->user()->isAdmin()) {
            abort(403, 'تەنها بەڕێوەبەر دەسەڵاتی تۆمارکردنی پارەدانی هەیە.');
        }

        $validated = $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'amount' => ['required', 'numeric', 'min:1'],
            'currency' => ['nullable', 'in:IQD,USD'],
            'cash_box_id' => ['nullable', 'exists:cash_boxes,id'],
            'paid_at' => ['required', 'date'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $employee = Employee::findOrFail($validated['employee_id']);

        $payment = $paymentService->record([
            'direction' => 'out',
            'party' => $employee,
            'party_name' => $employee->name,
            'amount' => (float) $validated['amount'],
            'currency' => $validated['currency'] ?? 'IQD',
            'cash_box_id' => $validated['cash_box_id'] ?? null,
            'paid_at' => $validated['paid_at'],
            'category' => 'wage',
            'note' => $validated['note'] ?: "پێشەکی / حەقدەستی {$employee->name}",
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'ok' => true,
                'message' => "بڕی " . number_format($payment->amount) . " {$payment->currency} بە سەرکەوتوویی درا بە {$employee->name}.",
                'payment' => [
                    'id' => $payment->id,
                    'voucher_no' => $payment->voucher_no,
                    'amount' => (float) $payment->amount,
                    'amount_iqd' => (float) $payment->amount_iqd,
                    'currency' => $payment->currency,
                    'paid_at' => $payment->paid_at?->format('Y/m/d'),
                    'note' => $payment->note,
                ],
                'cash_box' => $payment->cashBox ? [
                    'id' => $payment->cashBox->id,
                    'balance' => (float) $payment->cashBox->balance(),
                ] : null,
            ]);
        }

        return back()->with('ok', "پارەدان بۆ {$employee->name} بە سەرکەوتوویی تۆمارکرا.");
    }

    /** سڕینەوەی وەستا یان کرێکار لە سیستەم */
    public function destroyEmployee(Employee $employee)
    {
        if (! auth()->user()->isAdmin()) {
            abort(403, 'تەنها بەڕێوەبەر دەسەڵاتی سڕینەوەی کارمەندی هەیە.');
        }

        $name = $employee->name;
        $employee->attendances()->delete();
        $employee->delete();

        return response()->json([
            'ok' => true,
            'message' => "وەستا ({$name}) بە سەرکەوتوویی سڕدرایەوە.",
        ]);
    }
}

