<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WarehouseController extends Controller
{
    public function index(): View
    {
        $warehouses = Warehouse::withCount('movements')->orderByDesc('is_default')->orderBy('id')->get();
        $totalMovements = \App\Models\StockMovement::count();
        $activeCount = $warehouses->where('is_active', true)->count();

        $salesWarehouse = Warehouse::where('is_default', true)->first() ?? $warehouses->first();
        $workshopWarehouse = Warehouse::where('name', 'like', '%دروستکردن%')->first()
            ?? $warehouses->where('is_default', false)->first()
            ?? $warehouses->first();

        // سەرجەم کەلوپەل و مەوادەکان لەگەڵ بڕی بەردەست و پەیجینەیشن
        $itemsQuery = \App\Models\Item::query()
            ->active()
            ->withStock()
            ->with(['unit', 'category'])
            ->when(request('search'), function ($q, $search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('barcode', 'like', "%{$search}%");
                });
            })
            ->orderBy('name');

        $totalItemsCount = \App\Models\Item::active()->count();
        $allItems = (clone $itemsQuery)->paginate(25)->withQueryString();

        $allStockItems = \App\Models\Item::active()->withStock()->get();
        $lowStockItems = $allStockItems->filter(fn ($item) => $item->is_low);

        // وەسڵەکانی کارگە (چ ئیشێک دەکرێت و چ تەواو کراوە)
        $ordersInProduction = \App\Models\Order::query()
            ->with(['customer', 'items'])
            ->where('status', 'in_production')
            ->latest('order_date')
            ->get();

        $ordersReady = \App\Models\Order::query()
            ->with(['customer', 'items'])
            ->where('status', 'ready')
            ->latest('order_date')
            ->get();

        $ordersConfirmed = \App\Models\Order::query()
            ->with(['customer', 'items'])
            ->where('status', 'confirmed')
            ->latest('order_date')
            ->take(5)
            ->get();

        $inProductionCount = $ordersInProduction->count();
        $readyCount = $ordersReady->count();
        $confirmedCount = \App\Models\Order::where('status', 'confirmed')->count();
        $deliveredTodayCount = \App\Models\Order::where('status', 'delivered')->whereDate('updated_at', now()->toDateString())->count();

        return view('warehouses.index', compact(
            'warehouses',
            'salesWarehouse',
            'workshopWarehouse',
            'totalMovements',
            'activeCount',
            'totalItemsCount',
            'allItems',
            'lowStockItems',
            'ordersInProduction',
            'ordersReady',
            'ordersConfirmed',
            'inProductionCount',
            'readyCount',
            'confirmedCount',
            'deliveredTodayCount'
        ));
    }

    public function create(): View
    {
        return view('warehouses.form', ['warehouse' => new Warehouse(['is_active' => true])]);
    }

    public function store(Request $request)
    {
        $warehouse = Warehouse::create($this->validated($request));
        $this->keepSingleDefault($warehouse);

        return redirect()->route('warehouses.index')->with('ok', "کۆگای «{$warehouse->name}» زیادکرا.");
    }

    public function edit(Warehouse $warehouse): View
    {
        return view('warehouses.form', compact('warehouse'));
    }

    public function update(Request $request, Warehouse $warehouse)
    {
        $warehouse->update($this->validated($request));
        $this->keepSingleDefault($warehouse);

        return redirect()->route('warehouses.index')->with('ok', 'کۆگاکە نوێکرایەوە.');
    }

    public function destroy(Warehouse $warehouse)
    {
        if ($warehouse->movements()->exists()) {
            return back()->with('err', 'ناتوانرێت بسڕدرێتەوە — ئەم کۆگایە جوڵەی مەخزەنی هەیە.');
        }

        // جەرد و پسوولەی کڕینیش پەیوەندییان بە کۆگاوە هەیە — بەبێ ئەم پشکنینە
        // ئەو تۆمارانە دەبنە هەتیو و لاپەڕەکانیان دەشکێن.
        if ($warehouse->stockCounts()->exists() || $warehouse->purchases()->exists()) {
            return back()->with('err', 'ناتوانرێت بسڕدرێتەوە — جەرد یان پسوولەی کڕینی پەیوەستی هەیە. لەبری ئەوە ناچالاکی بکە.');
        }

        if ($warehouse->is_default) {
            return back()->with('err', 'کۆگای بنەڕەت ناسڕدرێتەوە — سەرەتا کۆگایەکی تر بکە بە بنەڕەت.');
        }

        $warehouse->delete();

        return redirect()->route('warehouses.index')->with('ok', 'کۆگاکە سڕدرایەوە.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
        ], [], ['name' => 'ناو']);

        $data['is_active'] = $request->boolean('is_active');
        $data['is_default'] = $request->boolean('is_default');

        return $data;
    }

    /** تەنها یەک کۆگا دەتوانێت بنەڕەت بێت. */
    private function keepSingleDefault(Warehouse $warehouse): void
    {
        if ($warehouse->is_default) {
            Warehouse::whereKeyNot($warehouse->id)->update(['is_default' => false]);
        }
    }
}
