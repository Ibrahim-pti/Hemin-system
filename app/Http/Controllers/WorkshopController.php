<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\Order;
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
            ->orderByRaw("FIELD(status, 'in_production', 'confirmed', 'ready')")
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
            ->orderByRaw("FIELD(job_title, 'master', 'porter', 'helper', 'driver', 'other')")
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
            ->whereNotIn('status', ['draft', 'cancelled'])
            ->orderByRaw("FIELD(status, 'in_production', 'confirmed', 'ready', 'delivered')")
            ->latest('order_date')
            ->get();

        $ordersData = $orders->map(fn ($o) => [
            'id' => $o->id,
            'invoice_no' => $o->invoice_no,
            'status' => $o->status,
            'status_label' => $o->status_label,
            'customer_name' => $o->customer?->name ?? 'نەناسراو',
            'customer_phone' => $o->customer?->phone ?? '',
            'order_date' => $o->order_date?->format('Y/m/d') ?? '',
            'delivery_date' => $o->delivery_date?->format('Y/m/d') ?? '',
            'notes' => $o->notes ?? '',
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
        ])->values()->all();

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

    /** لاپەڕەی جیاکراوەی وەستا و حەمەڵەکان بە سیستەمی ئامادەبوون */
    public function employees(Request $request): View
    {
        $date = now()->toDateString();

        $employees = Employee::query()
            ->active()
            ->with(['attendances' => fn ($q) => $q->whereDate('work_date', $date)])
            ->orderByRaw("FIELD(job_title, 'master', 'porter', 'helper', 'driver', 'other')")
            ->orderBy('name')
            ->get();

        $employeesData = $employees->map(function ($emp) use ($date) {
            $att = $emp->attendances->first();
            return [
                'id' => $emp->id,
                'name' => $emp->name,
                'phone' => $emp->phone,
                'job_title' => $emp->job_title,
                'job_title_label' => $emp->job_title_label,
                'daily_wage' => (float) $emp->daily_wage,
                'wage_currency' => $emp->wage_currency,
                'hire_date' => $emp->hire_date?->format('Y/m/d'),
                'note' => $emp->note,
                'attendance' => $att ? [
                    'id' => $att->id,
                    'status' => $att->status,
                    'status_label' => $att->status_label,
                    'check_in' => $att->check_in ? substr($att->check_in, 0, 5) : '',
                    'check_out' => $att->check_out ? substr($att->check_out, 0, 5) : '',
                    'hours' => (float) $att->hours,
                    'overtime_hours' => (float) $att->overtime_hours,
                    'temporary_exit_hours' => (float) $att->temporary_exit_hours,
                    'exit_reason' => $att->exit_reason ?? '',
                    'fuel_expense' => (float) $att->fuel_expense,
                    'trip_destination' => $att->trip_destination ?? '',
                    'note' => $att->note ?? '',
                ] : null,
            ];
        })->values()->all();

        $presentCount = $employees->filter(fn ($e) => $e->attendances->first()?->status === 'present')->count();
        $leaveCount = $employees->filter(fn ($e) => $e->attendances->first()?->status === 'leave')->count();
        $absentCount = $employees->filter(fn ($e) => $e->attendances->first()?->status === 'absent')->count();
        $notRecordedCount = $employees->filter(fn ($e) => !$e->attendances->first() || !$e->attendances->first()?->status)->count();
        $totalOvertime = (float) $employees->sum(fn ($e) => (float) ($e->attendances->first()?->overtime_hours ?? 0));
        $totalFuel = (float) $employees->sum(fn ($e) => (float) ($e->attendances->first()?->fuel_expense ?? 0));
        $isFriday = now()->isFriday();

        return view('workshop.employees', compact(
            'employees',
            'employeesData',
            'date',
            'isFriday',
            'presentCount',
            'leaveCount',
            'absentCount',
            'notRecordedCount',
            'totalOvertime',
            'totalFuel'
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
                'note' => $validated['note'] ?: 'زیادکردنی مەواد بۆ کارگە',
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
}
