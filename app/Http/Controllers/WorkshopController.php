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
                'item_name' => $it->item_name,
                'qty' => (float) $it->qty,
                'unit_name' => $it->unit_name,
                'width' => $it->width,
                'height' => $it->height,
                'note' => $it->note,
                'image' => Item::find($it->item_id)?->imageUrl(),
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
            ->with(['unit', 'category'])
            ->orderBy('name')
            ->get();

        $lowStockMaterials = $rawMaterials->filter(fn ($item) => $item->is_low);
        $categories = ItemCategory::orderBy('name')->get();
        $units = Unit::orderBy('name')->get();

        $materialsData = $rawMaterials->map(fn ($m) => [
            'id' => $m->id,
            'code' => $m->code,
            'name' => $m->name,
            'category_name' => $m->category?->name,
            'stock_qty' => (float) $m->stock_qty,
            'min_qty' => (float) $m->min_qty,
            'unit_name' => $m->unit?->name ?? '',
            'is_low' => $m->is_low,
        ])->values()->all();

        return view('workshop.materials', compact(
            'workshopWarehouse',
            'rawMaterials',
            'materialsData',
            'lowStockMaterials',
            'categories',
            'units'
        ));
    }

    /** لاپەڕەی جیاکراوەی وەستا و حەمەڵەکان بە سیستەمی چیک ئین و ئامادەبوون */
    public function employees(Request $request): View
    {
        $date = $request->date('date')?->toDateString() ?? now()->toDateString();

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

        return view('workshop.employees', compact(
            'employees',
            'employeesData',
            'date',
            'presentCount',
            'leaveCount',
            'absentCount'
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

    /** زیادکردنی خێرای مەوادی خاو نوێ بۆ شوێنی دروستکردن */
    public function storeRawMaterial(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'item_category_id' => ['nullable', 'exists:item_categories,id'],
            'unit_id' => ['required', 'exists:units,id'],
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'initial_qty' => ['nullable', 'numeric', 'min:0'],
            'min_qty' => ['nullable', 'numeric', 'min:0'],
            'note' => ['nullable', 'string'],
        ], [], [
            'name' => 'ناوی مەواد',
            'unit_id' => 'یەکە',
            'warehouse_id' => 'کۆگا',
        ]);

        DB::transaction(function () use ($validated) {
            $item = Item::create([
                'code' => Item::nextCode(),
                'name' => $validated['name'],
                'item_category_id' => $validated['item_category_id'] ?? null,
                'unit_id' => $validated['unit_id'],
                'min_qty' => $validated['min_qty'] ?? 0,
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
                    reason: 'initial',
                    extra: [
                        'note' => 'مەوادی سەرەتایی دروستکردن',
                        'moved_at' => now()->toDateString(),
                    ]
                );
            }
        });

        return back()->with('ok', 'مەوادی نوێ زیادکرا بۆ شوێنی دروستکردن.');
    }

    /** زیادکردنی بڕ بۆ مەوادێکی هەبوو لە شوێنی دروستکردن (Stock In) */
    public function stockIn(Request $request)
    {
        $validated = $request->validate([
            'item_id' => ['required', 'exists:items,id'],
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'qty' => ['required', 'numeric', 'min:0.01'],
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
            reason: 'manual',
            extra: [
                'note' => $validated['note'] ?: 'زیادکردنی مەواد بۆ کارگە',
                'moved_at' => now()->toDateString(),
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
            'note' => ['nullable', 'string', 'max:255'],
        ], [], [
            'item_id' => 'مەواد',
            'qty' => 'بڕ',
        ]);

        $this->stockService->record(
            itemId: (int) $validated['item_id'],
            warehouseId: (int) $validated['warehouse_id'],
            direction: 'out',
            qty: (float) $validated['qty'],
            reason: 'production',
            extra: [
                'note' => $validated['note'] ?: 'بەکارهێنان لە دروستکردندا',
                'moved_at' => now()->toDateString(),
            ]
        );

        return back()->with('ok', 'بەکارهێنانی مەواد تۆمارکرا.');
    }
}
