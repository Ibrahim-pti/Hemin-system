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
        $totalSales = (float) Order::whereNotIn('status', ['draft', 'cancelled'])->sum(Order::totalIqdExpression());
        $totalPurchases = (float) Purchase::where('status', 'confirmed')->sum(Purchase::totalIqdExpression());
        $totalJobs = (float) ExternalJob::where('status', '!=', 'cancelled')->sum(ExternalJob::costIqdExpression());
        $totalWages = (float) CashTransaction::where('category', 'wage')->sum('amount');
        $totalExpenses = (float) CashTransaction::where('category', 'expense')->sum('amount');
        $netProfit = $totalSales - $totalPurchases - $totalJobs - $totalWages - $totalExpenses;

        $stockItems = Item::query()->withStock()->get();
        $stockValue = $stockItems->sum(function (Item $item) {
            $cost = (float) $item->last_cost;
            if ($item->cost_currency === 'USD') {
                $cost *= \App\Models\ExchangeRate::current();
            }
            return $item->stock_qty * $cost;
        });

        $customerDebts = (float) Order::whereNotIn('status', ['draft', 'cancelled'])->get()->sum(fn (Order $o) => $o->remaining());
        $totalOrdersCount = Order::whereNotIn('status', ['draft', 'cancelled'])->count();

        return view('reports.index', [
            'reports' => self::REPORTS,
            'stats' => [
                'sales' => $totalSales,
                'purchases' => $totalPurchases,
                'profit' => $netProfit,
                'stock_value' => $stockValue,
                'debts' => $customerDebts,
                'orders_count' => $totalOrdersCount,
            ],
        ]);
    }

    public function show(string $report, Request $request): View
    {
        abort_unless(isset(self::REPORTS[$report]), 404);

        $period = $request->query('period');

        if ($period === 'today') {
            $from = now()->toDateString();
            $to = now()->toDateString();
        } elseif ($period === 'this_month') {
            $from = now()->startOfMonth()->toDateString();
            $to = now()->endOfMonth()->toDateString();
        } elseif ($period === 'last_month') {
            $from = now()->subMonth()->startOfMonth()->toDateString();
            $to = now()->subMonth()->endOfMonth()->toDateString();
        } elseif ($period === 'this_year') {
            $from = now()->startOfYear()->toDateString();
            $to = now()->endOfYear()->toDateString();
        } elseif ($period === 'all') {
            $from = null;
            $to = null;
        } else {
            $from = $request->filled('from') ? $request->date('from')?->toDateString() : null;
            $to = $request->filled('to') ? $request->date('to')?->toDateString() : null;
        }

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

    private function sales(?string $from, ?string $to): array
    {
        $orders = Order::with('customer')
            ->whereNotIn('status', ['draft', 'cancelled'])
            ->when($from, fn ($q) => $q->where('order_date', '>=', $from))
            ->when($to, fn ($q) => $q->where('order_date', '<=', $to))
            ->orderByDesc('order_date')
            ->get();

        $paid = (float) Payment::where('direction', 'in')
            ->when($from, fn ($q) => $q->where('paid_at', '>=', $from))
            ->when($to, fn ($q) => $q->where('paid_at', '<=', $to))
            ->sum('amount_iqd');

        return [
            'orders' => $orders,
            'total' => $orders->sum(fn (Order $o) => $o->total_iqd),
            'paid' => $paid,
            // فرۆشتن بەپێی کڕیار
            'byCustomer' => $orders->groupBy('customer_id')
                ->map(fn ($group) => [
                    'name' => $group->first()->customer?->name ?? 'نەناسراو',
                    'count' => $group->count(),
                    'total' => $group->sum(fn (Order $o) => $o->total_iqd),
                ])
                ->sortByDesc('total')
                ->values(),
        ];
    }

    private function purchases(?string $from, ?string $to): array
    {
        $purchases = Purchase::with('supplier')
            ->where('status', 'confirmed')
            ->when($from, fn ($q) => $q->where('purchase_date', '>=', $from))
            ->when($to, fn ($q) => $q->where('purchase_date', '<=', $to))
            ->orderByDesc('purchase_date')
            ->get();

        return [
            'purchases' => $purchases,
            'total' => $purchases->sum(fn (Purchase $p) => $p->total_iqd),
            'bySupplier' => $purchases->groupBy('supplier_id')
                ->map(fn ($group) => [
                    'name' => $group->first()->supplier?->name ?? 'نەناسراو',
                    'count' => $group->count(),
                    'total' => $group->sum(fn (Purchase $p) => $p->total_iqd),
                ])
                ->sortByDesc('total')
                ->values(),
        ];
    }

    /**
     * قازانجی سادە: فرۆشتن − (کڕین + ئیشی خاریجی + حەقدەست + خەرجی).
     */
    private function profit(?string $from, ?string $to): array
    {
        $sales = (float) Order::whereNotIn('status', ['draft', 'cancelled'])
            ->when($from, fn ($q) => $q->where('order_date', '>=', $from))
            ->when($to, fn ($q) => $q->where('order_date', '<=', $to))
            ->sum(Order::totalIqdExpression());

        $purchases = (float) Purchase::where('status', 'confirmed')
            ->when($from, fn ($q) => $q->where('purchase_date', '>=', $from))
            ->when($to, fn ($q) => $q->where('purchase_date', '<=', $to))
            ->sum(Purchase::totalIqdExpression());

        $jobs = (float) ExternalJob::where('status', '!=', 'cancelled')
            ->when($from, fn ($q) => $q->where('started_at', '>=', $from))
            ->when($to, fn ($q) => $q->where('started_at', '<=', $to))
            ->sum(ExternalJob::costIqdExpression());

        $wages = (float) CashTransaction::where('category', 'wage')
            ->when($from, fn ($q) => $q->where('occurred_at', '>=', $from))
            ->when($to, fn ($q) => $q->where('occurred_at', '<=', $to))
            ->sum('amount');

        $expenses = (float) CashTransaction::where('category', 'expense')
            ->when($from, fn ($q) => $q->where('occurred_at', '>=', $from))
            ->when($to, fn ($q) => $q->where('occurred_at', '<=', $to))
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

    private function cash(?string $from, ?string $to): array
    {
        $transactions = CashTransaction::with('cashBox')
            ->when($from, fn ($q) => $q->where('occurred_at', '>=', $from))
            ->when($to, fn ($q) => $q->where('occurred_at', '<=', $to))
            ->orderByDesc('occurred_at')
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

    private function workshopProduction(?string $from, ?string $to): array
    {
        $baseQuery = Order::with(['customer', 'items.item'])
            ->whereNotIn('status', ['draft', 'cancelled'])
            ->when($from, fn ($q) => $q->where('order_date', '>=', $from))
            ->when($to, fn ($q) => $q->where('order_date', '<=', $to))
            ->orderByDesc('order_date');

        $allOrders = (clone $baseQuery)->get();

        $deliveredOrders = $allOrders->where('status', 'delivered');
        $inProductionOrders = $allOrders->where('status', 'in_production');
        $readyOrders = $allOrders->where('status', 'ready');
        $pendingOrders = $allOrders->where('status', 'confirmed');

        $itemsBreakdown = $allOrders->flatMap->items
            ->groupBy(fn (OrderItem $item) => $item->item_name)
            ->map(fn ($group, $name) => [
                'name' => $name,
                'count' => $group->count(),
                'qty' => $group->sum('qty'),
                'unit' => $group->first()->unit_name,
            ])
            ->values();

        $currentStatus = request('status');
        $filteredQuery = clone $baseQuery;
        if ($currentStatus && in_array($currentStatus, ['delivered', 'in_production', 'ready', 'confirmed', 'pending'])) {
            if ($currentStatus === 'pending') {
                $filteredQuery->where('status', 'confirmed');
            } else {
                $filteredQuery->where('status', $currentStatus);
            }
        }

        $orders = $filteredQuery->paginate(50)->withQueryString();

        return [
            'orders' => $orders,
            'currentStatus' => $currentStatus,
            'totalCount' => $allOrders->count(),
            'deliveredCount' => $deliveredOrders->count(),
            'inProductionCount' => $inProductionOrders->count(),
            'readyCount' => $readyOrders->count(),
            'pendingCount' => $pendingOrders->count(),
            'itemsBreakdown' => $itemsBreakdown,
        ];
    }

    private function workshopMaterials(?string $from, ?string $to): array
    {
        $workshopWarehouse = Warehouse::where('name', 'like', '%دروستکردن%')->first()
            ?? Warehouse::where('is_default', false)->first()
            ?? Warehouse::first();
        $warehouseId = $workshopWarehouse?->id;

        $movements = StockMovement::query()
            ->with(['item.unit', 'reference'])
            ->when($warehouseId, fn ($q) => $q->where('warehouse_id', $warehouseId))
            ->when($from, fn ($q) => $q->where('moved_at', '>=', $from))
            ->when($to, fn ($q) => $q->where('moved_at', '<=', $to))
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
