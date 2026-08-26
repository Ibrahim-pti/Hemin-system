<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\Order;
use App\Models\StockMovement;
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

    public function index(Request $request): View
    {
        // وەرگرتنی کۆگای دروستکردن
        $workshopWarehouse = Warehouse::where('name', 'like', '%دروستکردن%')->first()
            ?? Warehouse::where('is_default', false)->first()
            ?? Warehouse::first();

        $warehouseId = $workshopWarehouse?->id;
        $tab = $request->string('tab', 'all')->toString();
        $q = $request->string('q')->toString();

        // فەرمانەکانی دروستکردن (وەسڵەکان)
        $ordersQuery = Order::query()
            ->with(['customer', 'items'])
            ->whereNotIn('status', ['draft', 'cancelled'])
            ->search($q);

        if ($tab === 'pending') {
            $ordersQuery->where('status', 'confirmed');
        } elseif ($tab === 'in_production') {
            $ordersQuery->where('status', 'in_production');
        } elseif ($tab === 'ready') {
            $ordersQuery->where('status', 'ready');
        } elseif ($tab === 'delivered') {
            $ordersQuery->where('status', 'delivered');
        }

        $orders = $ordersQuery
            ->orderByRaw("FIELD(status, 'in_production', 'confirmed', 'ready', 'delivered')")
            ->latest('order_date')
            ->paginate(20)
            ->withQueryString();

        // ئامارە خێراکان
        $pendingCount = Order::where('status', 'confirmed')->count();
        $inProductionCount = Order::where('status', 'in_production')->count();
        $readyCount = Order::where('status', 'ready')->count();
        $deliveredCount = Order::where('status', 'delivered')->whereDate('updated_at', now()->toDateString())->count();

        // مەوادی خاو و کۆگا لە شوێنی دروستکردن
        $rawMaterials = Item::query()
            ->active()
            ->withStock($warehouseId)
            ->with(['unit', 'category'])
            ->search($request->string('mat_q')->toString())
            ->orderBy('name')
            ->paginate(15, ['*'], 'materials_page')
            ->withQueryString();

        $categories = ItemCategory::orderBy('name')->get();
        $units = Unit::orderBy('name')->get();
        $allItems = Item::active()->orderBy('name')->get(['id', 'name', 'code']);

        return view('workshop.dashboard', compact(
            'workshopWarehouse',
            'orders',
            'tab',
            'pendingCount',
            'inProductionCount',
            'readyCount',
            'deliveredCount',
            'rawMaterials',
            'categories',
            'units',
            'allItems'
        ));
    }

    /** گۆڕینی دۆخی دروستکردنی وەسڵ (دەستپێکردن، ئامادەکردن، ڕادەستکردن) */
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
