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
        $rangeType = $request->input('range_type', 'this_week');
        $today = now();

        if ($request->filled('from') && $request->filled('to')) {
            $from = $request->date('from')->toDateString();
            $to = $request->date('to')->toDateString();
            $rangeType = 'custom';
        } else {
            switch ($rangeType) {
                case 'last_week':
                    $lastWeek = $today->copy()->subWeek();
                    $from = $lastWeek->copy()->startOfWeek(\Carbon\Carbon::SATURDAY)->toDateString();
                    $to = $lastWeek->copy()->endOfWeek(\Carbon\Carbon::FRIDAY)->toDateString();
                    break;
                case 'this_month':
                    $from = $today->copy()->startOfMonth()->toDateString();
                    $to = $today->copy()->endOfMonth()->toDateString();
                    break;
                case 'this_week':
                default:
                    $rangeType = 'this_week';
                    $from = $today->copy()->startOfWeek(\Carbon\Carbon::SATURDAY)->toDateString();
                    $to = $today->copy()->endOfWeek(\Carbon\Carbon::FRIDAY)->toDateString();
                    break;
            }
        }

        // دڵنیابوونەوە لەوەی لە ٣١ ڕۆژ زیاتر نەبێت بۆ ڕێگری لە قورسایی جەدوەل
        $fromDate = \Carbon\Carbon::parse($from);
        $toDate = \Carbon\Carbon::parse($to);
        if ($fromDate->diffInDays($toDate) > 31) {
            $toDate = $fromDate->copy()->addDays(30);
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
                'is_today' => $dt->isToday(),
                'is_holiday' => Attendance::isWeeklyHoliday($dateStr),
            ];
        }

        $shiftSettings = [
            'work_start' => Setting::get('workshop_work_start', '08:00'),
            'work_end' => Setting::get('workshop_work_end', '17:00'),
            'work_hours' => (float) Setting::get('workshop_work_hours', 8),
            'weekly_holiday' => Setting::get('workshop_weekly_holiday', 'friday'),
            'overtime_multiplier' => (float) Setting::get('workshop_overtime_multiplier', 1.0),
        ];

        $employees = Employee::query()
            ->active()
            ->with([
                'attendances' => fn ($q) => $q->whereBetween('work_date', [$from, $to]),
                'payments' => fn ($q) => $q->where('direction', 'out')->whereBetween('paid_at', [$from, $to])->latest('paid_at'),
            ])
            ->orderByRaw("CASE job_title WHEN 'master' THEN 1 WHEN 'porter' THEN 2 WHEN 'helper' THEN 3 WHEN 'driver' THEN 4 WHEN 'other' THEN 5 ELSE 6 END")
            ->orderBy('name')
            ->get();

        $employeesMatrix = $employees->map(function (Employee $emp) use ($days, $shiftSettings, $from, $to) {
            $attendancesKeyed = $emp->attendances->keyBy(fn ($a) => $a->work_date instanceof \DateTimeInterface ? $a->work_date->format('Y-m-d') : substr((string) $a->work_date, 0, 10));

            $cells = [];
            $presentCount = 0;
            $leaveCount = 0;
            $absentCount = 0;
            $holidayCount = 0;
            $totalOvertime = 0.0;
            $totalFuel = 0.0;
            $totalTempExit = 0.0;

            foreach ($days as $d) {
                $dStr = $d['date'];
                $att = $attendancesKeyed->get($dStr);

                if ($att) {
                    $status = $att->status;
                    $ot = (float) $att->overtime_hours;
                    $fuel = (float) $att->fuel_expense;
                    $tempExit = (float) $att->temporary_exit_hours;

                    if ($status === 'present') $presentCount++;
                    elseif ($status === 'leave') $leaveCount++;
                    elseif ($status === 'absent') $absentCount++;
                    elseif ($status === 'holiday') $holidayCount++;

                    $totalOvertime += $ot;
                    $totalFuel += $fuel;
                    $totalTempExit += $tempExit;

                    $cells[$dStr] = [
                        'id' => $att->id,
                        'status' => $status,
                        'status_label' => $att->status_label,
                        'check_in' => $att->check_in ? substr($att->check_in, 0, 5) : '',
                        'check_out' => $att->check_out ? substr($att->check_out, 0, 5) : '',
                        'hours' => (float) $att->hours,
                        'overtime_hours' => $ot,
                        'temporary_exit_hours' => $tempExit,
                        'exit_reason' => $att->exit_reason ?? '',
                        'fuel_expense' => $fuel,
                        'trip_destination' => $att->trip_destination ?? '',
                        'note' => $att->note ?? '',
                    ];
                } else {
                    $cells[$dStr] = null;
                }
            }

            $dailyWage = (float) $emp->daily_wage;
            $salaryType = $emp->salary_type ?? 'daily';
            $effectiveDailyWage = $emp->effective_daily_wage;

            // حیساباتی دارایی
            $baseEarned = $presentCount * $effectiveDailyWage;
            $hourlyWage = $shiftSettings['work_hours'] > 0 ? ($effectiveDailyWage / $shiftSettings['work_hours']) : 0;
            $overtimeEarned = $totalOvertime * $hourlyWage * $shiftSettings['overtime_multiplier'];
            $totalEarned = round($baseEarned + $overtimeEarned + $totalFuel, 2);

            $totalPaid = (float) $emp->payments->sum('amount_iqd');
            $remainingBalance = round($totalEarned - $totalPaid, 2);

            $paymentsList = $emp->payments->map(fn ($p) => [
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
                'salary_type' => $salaryType,
                'salary_type_label' => $emp->salary_type_label,
                'daily_wage' => $dailyWage,
                'effective_daily_wage' => $effectiveDailyWage,
                'wage_currency' => $emp->wage_currency ?? 'IQD',
                'hire_date' => $emp->hire_date?->format('Y/m/d'),
                'note' => $emp->note ?? '',
                'cells' => $cells,
                'present_count' => $presentCount,
                'leave_count' => $leaveCount,
                'absent_count' => $absentCount,
                'holiday_count' => $holidayCount,
                'total_overtime' => $totalOvertime,
                'total_fuel' => $totalFuel,
                'total_temp_exit' => $totalTempExit,
                'base_earned' => $baseEarned,
                'overtime_earned' => $overtimeEarned,
                'total_earned' => $totalEarned,
                'total_paid' => $totalPaid,
                'remaining_balance' => $remainingBalance,
                'payments' => $paymentsList,
            ];
        })->values()->all();

        // ژماردنی ئاماری هەر ڕۆژێک بە جیا بۆ خوارەوەی جەدوەل (وەک دەفتەرەکە)
        $dayTotals = [];
        foreach ($days as $d) {
            $dStr = $d['date'];
            $presentOnDay = 0;
            $overtimeOnDay = 0.0;
            $fuelOnDay = 0.0;

            foreach ($employeesMatrix as $row) {
                $cell = $row['cells'][$dStr] ?? null;
                if ($cell && $cell['status'] === 'present') {
                    $presentOnDay++;
                    $overtimeOnDay += $cell['overtime_hours'];
                    $fuelOnDay += $cell['fuel_expense'];
                }
            }

            $dayTotals[$dStr] = [
                'present' => $presentOnDay,
                'overtime' => $overtimeOnDay,
                'fuel' => $fuelOnDay,
            ];
        }

        // کۆی گشتییەکانی سەرجەم ماوەکە
        $totalEmployeesCount = count($employeesMatrix);
        $totalPresentManDays = array_sum(array_column($employeesMatrix, 'present_count'));
        $totalOvertimeHours = array_sum(array_column($employeesMatrix, 'total_overtime'));
        $totalFuelExpenses = array_sum(array_column($employeesMatrix, 'total_fuel'));
        $totalEarnedAll = array_sum(array_column($employeesMatrix, 'total_earned'));
        $totalPaidAll = array_sum(array_column($employeesMatrix, 'total_paid'));
        $totalRemainingAll = array_sum(array_column($employeesMatrix, 'remaining_balance'));

        $cashBoxes = \App\Models\CashBox::all();

        return view('workshop.employees', compact(
            'employees',
            'employeesMatrix',
            'days',
            'dayTotals',
            'from',
            'to',
            'rangeType',
            'shiftSettings',
            'totalEmployeesCount',
            'totalPresentManDays',
            'totalOvertimeHours',
            'totalFuelExpenses',
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

    /** نوێکردنەوەی ڕێکخستنەکانی دەوام و پشوو و کاتی زیادە */
    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'workshop_work_start' => ['required', 'string'],
            'workshop_work_end' => ['required', 'string'],
            'workshop_work_hours' => ['required', 'numeric', 'min:1', 'max:24'],
            'workshop_weekly_holiday' => ['required', 'string'],
            'workshop_overtime_multiplier' => ['required', 'numeric', 'min:0.5', 'max:5'],
        ]);

        foreach ($validated as $key => $value) {
            Setting::put($key, (string) $value);
        }

        if ($request->wantsJson()) {
            return response()->json(['ok' => true, 'message' => 'ڕێکخستنەکانی دەوام بە سەرکەوتوویی پاشەکەوتکران.']);
        }

        return back()->with('ok', 'ڕێکخستنەکانی دەوام بە سەرکەوتوویی پاشەکەوتکران.');
    }

    /** زیادکردنی خێرای وەستا و حەمەڵ */
    public function quickStoreEmployee(Request $request)
    {
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

    /** گۆڕینی خێرای دۆخی خانەی ئامادەبوون (سەح / غائیب / ئیجازە / هیچی تر) بە یەک کلیک */
    public function toggleAttendanceCell(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'work_date' => ['required', 'date'],
            'status' => ['nullable', 'in:present,absent,leave,holiday,delete'],
        ]);

        $employee = Employee::findOrFail($validated['employee_id']);
        $date = $validated['work_date'];
        $status = $validated['status'] ?? null;

        $attendance = Attendance::where('employee_id', $employee->id)
            ->whereDate('work_date', $date)
            ->first();

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

        // ئەگەر دۆخ دیاری نەکرابێت، بە یەک کلیک بەدوای یەکدا دەسوڕێت: سەح -> غائیب -> ئیجازە -> خاڵی
        if (! $status) {
            if (! $attendance || ! $attendance->status) {
                $status = 'present';
            } elseif ($attendance->status === 'present') {
                $status = 'absent';
            } elseif ($attendance->status === 'absent') {
                $status = 'leave';
            } else {
                $attendance->delete();
                return response()->json([
                    'ok' => true,
                    'status' => null,
                    'status_label' => 'تۆمارنەکراو',
                    'attendance' => null,
                    'message' => "تۆماری ئامادەبوونی {$employee->name} لابرا.",
                ]);
            }
        }

        if (! $attendance) {
            $attendance = new Attendance([
                'employee_id' => $employee->id,
                'work_date' => $date,
            ]);
        }

        $attendance->status = $status;
        $attendance->wage_snapshot = $status === 'present' ? $employee->effective_daily_wage : 0;
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
                'temporary_exit_hours' => (float) $attendance->temporary_exit_hours,
                'exit_reason' => $attendance->exit_reason ?? '',
                'fuel_expense' => (float) $attendance->fuel_expense,
                'trip_destination' => $attendance->trip_destination ?? '',
                'note' => $attendance->note ?? '',
            ],
            'message' => "دۆخی {$employee->name} بۆ بەرواری {$date} نوێکرایەوە.",
        ]);
    }

    /** سەح لێدانی هەمووان بۆ ڕۆژێکی دیاریکراو (سەحی بەکۆمەڵ) */
    public function batchMarkDay(Request $request)
    {
        $validated = $request->validate([
            'work_date' => ['required', 'date'],
            'status' => ['required', 'in:present,absent,leave,holiday'],
        ]);

        $date = $validated['work_date'];
        $status = $validated['status'];
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
                $attendance->wage_snapshot = $status === 'present' ? $employee->effective_daily_wage : 0;
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
            ]);
        }

        return back()->with('ok', "پارەدان بۆ {$employee->name} بە سەرکەوتوویی تۆمارکرا.");
    }
}
