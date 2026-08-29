<?php

namespace App\Http\Controllers;

use App\Models\CashBox;
use App\Models\CashTransaction;
use App\Models\ExternalJob;
use App\Models\Item;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Purchase;
use App\Models\StockMovement;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    /** ناوی راپۆرتەکان. */
    public const REPORTS = [
        'sales' => ['ڕاپۆرتی فرۆشتن', 'وەسڵەکان بەپێی ماوە و کڕیار'],
        'purchases' => ['ڕاپۆرتی کڕین', 'پسوولەکانی کڕین بەپێی فرۆشیار'],
        'profit' => ['قازانج', 'فرۆشتن دوای دەرکردنی کڕین و ئیشی خاریجی'],
        'stock' => ['مەخزەن', 'باڵانسی هەموو کاڵایەک و نرخی کۆگا'],
        'cash' => ['قاسە', 'داهات و خەرجی بەپێی بابەت'],
        'workshop_production' => ['ڕاپۆرتی دروستکردن و کارگە', 'وەسڵە دروستکراوەکان، قۆناغەکانی کار، و قیاسات بەپێی بەروار'],
        'workshop_materials' => ['ڕاپۆرتی سەرفیاتی مەخزەنی کارگە', 'جووڵەی مەوادی خاو و سەرفیات بۆ وەسڵەکان بەپێی بەروار'],
    ];

    public function index(): View
    {
        return view('reports.index', ['reports' => self::REPORTS]);
    }

    public function show(string $report, Request $request): View
    {
        abort_unless(isset(self::REPORTS[$report]), 404);

        $from = $request->date('from')?->toDateString() ?? now()->startOfMonth()->toDateString();
        $to = $request->date('to')?->toDateString() ?? now()->toDateString();

        $data = match ($report) {
            'sales' => $this->sales($from, $to),
            'purchases' => $this->purchases($from, $to),
            'profit' => $this->profit($from, $to),
            'stock' => $this->stock(),
            'cash' => $this->cash($from, $to),
            'workshop_production' => $this->workshopProduction($from, $to),
            'workshop_materials' => $this->workshopMaterials($from, $to),
        };

        return view("reports.{$report}", $data + [
            'from' => $from,
            'to' => $to,
            'title' => self::REPORTS[$report][0],
        ]);
    }

    private function sales(string $from, string $to): array
    {
        $orders = Order::with('customer')
            ->whereNotIn('status', ['draft', 'cancelled'])
            ->whereBetween('order_date', [$from, $to])
            ->orderBy('order_date')
            ->get();

        return [
            'orders' => $orders,
            'total' => $orders->sum(fn (Order $o) => $o->total_iqd),
            'paid' => (float) Payment::where('direction', 'in')
                ->whereBetween('paid_at', [$from, $to])
                ->sum('amount_iqd'),
            // فرۆشتن بەپێی کڕیار — بۆ زانینی باشترین کڕیارەکان.
            'byCustomer' => $orders->groupBy('customer_id')
                ->map(fn ($group) => [
                    'name' => $group->first()->customer->name,
                    'count' => $group->count(),
                    'total' => $group->sum(fn (Order $o) => $o->total_iqd),
                ])
                ->sortByDesc('total')
                ->values(),
        ];
    }

    private function purchases(string $from, string $to): array
    {
        $purchases = Purchase::with('supplier')
            ->where('status', 'confirmed')
            ->whereBetween('purchase_date', [$from, $to])
            ->orderBy('purchase_date')
            ->get();

        return [
            'purchases' => $purchases,
            'total' => $purchases->sum(fn (Purchase $p) => $p->total_iqd),
            'bySupplier' => $purchases->groupBy('supplier_id')
                ->map(fn ($group) => [
                    'name' => $group->first()->supplier->name,
                    'count' => $group->count(),
                    'total' => $group->sum(fn (Purchase $p) => $p->total_iqd),
                ])
                ->sortByDesc('total')
                ->values(),
        ];
    }

    /**
     * قازانجی سادە: فرۆشتن − (کڕین + ئیشی خاریجی + حەقدەست + خەرجی).
     * ئەمە قازانجی کارگەیە بۆ ماوەکە، نەک قازانجی هەر وەسڵێک بە جیا.
     */
    private function profit(string $from, string $to): array
    {
        $sales = (float) Order::whereNotIn('status', ['draft', 'cancelled'])
            ->whereBetween('order_date', [$from, $to])
            ->sum(Order::totalIqdExpression());

        $purchases = (float) Purchase::where('status', 'confirmed')
            ->whereBetween('purchase_date', [$from, $to])
            ->sum(Purchase::totalIqdExpression());

        $jobs = (float) ExternalJob::where('status', '!=', 'cancelled')
            ->whereBetween('started_at', [$from, $to])
            ->sum(ExternalJob::costIqdExpression());

        $wages = (float) CashTransaction::where('category', 'wage')
            ->whereBetween('occurred_at', [$from, $to])
            ->sum('amount');

        $expenses = (float) CashTransaction::where('category', 'expense')
            ->whereBetween('occurred_at', [$from, $to])
            ->sum('amount');

        return [
            'sales' => $sales,
            'purchases' => $purchases,
            'jobs' => $jobs,
            'wages' => $wages,
            'expenses' => $expenses,
            'profit' => $sales - $purchases - $jobs - $wages - $expenses,
        ];
    }

    private function stock(): array
    {
        $items = Item::query()
            ->withStock()
            ->with(['unit', 'category'])
            ->orderBy('name')
            ->get();

        return [
            'items' => $items,
            // نرخی کۆگا = بڕ × دوایین نرخی کڕین.
            'stockValue' => $items->sum(function (Item $item) {
                $cost = (float) $item->last_cost;

                if ($item->cost_currency === 'USD') {
                    $cost *= \App\Models\ExchangeRate::current();
                }

                return $item->stock_qty * $cost;
            }),
            'lowCount' => $items->filter(fn (Item $i) => $i->min_qty > 0 && $i->stock_qty <= (float) $i->min_qty)->count(),
        ];
    }

    private function cash(string $from, string $to): array
    {
        $transactions = CashTransaction::with('cashBox')
            ->whereBetween('occurred_at', [$from, $to])
            ->get();

        return [
            'boxes' => CashBox::where('is_active', true)->get(),
            'byCategory' => $transactions->groupBy('category')
                ->map(fn ($group, $category) => [
                    'label' => CashTransaction::CATEGORIES[$category] ?? $category,
                    'in' => $group->where('direction', 'in')->sum('amount'),
                    'out' => $group->where('direction', 'out')->sum('amount'),
                ])
                ->values(),
            'totalIn' => $transactions->where('direction', 'in')->sum('amount'),
            'totalOut' => $transactions->where('direction', 'out')->sum('amount'),
        ];
    }

    private function workshopProduction(string $from, string $to): array
    {
        $orders = Order::with(['customer', 'items.item'])
            ->whereNotIn('status', ['draft', 'cancelled'])
            ->whereBetween('order_date', [$from, $to])
            ->orderByDesc('order_date')
            ->get();

        $deliveredOrders = $orders->where('status', 'delivered');
        $inProductionOrders = $orders->where('status', 'in_production');
        $readyOrders = $orders->where('status', 'ready');
        $pendingOrders = $orders->where('status', 'confirmed');

        $itemsBreakdown = $orders->flatMap->items
            ->groupBy(fn (OrderItem $item) => $item->item_name)
            ->map(fn ($group, $name) => [
                'name' => $name,
                'count' => $group->count(),
                'qty' => $group->sum('qty'),
                'unit' => $group->first()->unit_name,
            ])
            ->values();

        return [
            'orders' => $orders,
            'totalCount' => $orders->count(),
            'deliveredCount' => $deliveredOrders->count(),
            'inProductionCount' => $inProductionOrders->count(),
            'readyCount' => $readyOrders->count(),
            'pendingCount' => $pendingOrders->count(),
            'itemsBreakdown' => $itemsBreakdown,
        ];
    }

    private function workshopMaterials(string $from, string $to): array
    {
        $workshopWarehouse = Warehouse::where('type', 'workshop')->first()
            ?? Warehouse::first();
        $warehouseId = $workshopWarehouse?->id;

        $movements = StockMovement::query()
            ->with(['item.unit', 'order.customer'])
            ->when($warehouseId, fn ($q) => $q->where('warehouse_id', $warehouseId))
            ->whereBetween('moved_at', [$from, $to])
            ->latest('moved_at')
            ->latest('id')
            ->get();

        $materials = Item::query()
            ->active()
            ->withStock($warehouseId)
            ->with(['unit', 'category'])
            ->orderBy('name')
            ->get();

        $consumedMovements = $movements->where('direction', 'out');
        $receivedMovements = $movements->where('direction', 'in');

        $consumedByMaterial = $consumedMovements->groupBy('item_id')
            ->map(fn ($group) => [
                'item_name' => $group->first()->item?->name ?? 'نەناسراو',
                'unit_name' => $group->first()->item?->unit?->name ?? 'دانە',
                'qty' => $group->sum('qty'),
                'count' => $group->count(),
            ])
            ->values();

        return [
            'workshopWarehouse' => $workshopWarehouse,
            'movements' => $movements,
            'materials' => $materials,
            'consumedCount' => $consumedMovements->count(),
            'consumedQty' => $consumedMovements->sum('qty'),
            'receivedCount' => $receivedMovements->count(),
            'receivedQty' => $receivedMovements->sum('qty'),
            'consumedByMaterial' => $consumedByMaterial,
        ];
    }
}
