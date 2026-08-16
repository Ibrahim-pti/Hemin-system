<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\StockCount;
use App\Models\StockCountItem;
use App\Models\Warehouse;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class StockCountController extends Controller
{
    public function __construct(private readonly StockService $stock) {}

    public function index(Request $request): View
    {
        $query = StockCount::with(['warehouse', 'user'])
            ->withCount('items')
            ->latest('id');

        // پاڵاوتن بەپێی کۆگا
        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        // پاڵاوتن بەپێی دۆخ
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // پاڵاوتن بەپێی بەروار
        if ($request->filled('from')) {
            $query->whereDate('count_date', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('count_date', '<=', $request->to);
        }

        // گەڕان بەپێی ژمارەی جەرد یان تێبینی
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($b) use ($q) {
                $b->where('count_no', 'like', "%{$q}%")
                    ->orWhere('note', 'like', "%{$q}%");
            });
        }

        // ئامارە گشتییەکان
        $stats = [
            'total_counts' => StockCount::count(),
            'draft_counts' => StockCount::where('status', 'draft')->count(),
            'posted_counts' => StockCount::where('status', 'posted')->count(),
            'total_items_counted' => StockCountItem::whereNotNull('counted_qty')->count(),
        ];

        return view('counts.index', [
            'counts' => $query->paginate(20)->withQueryString(),
            'warehouses' => Warehouse::where('is_active', true)->orderBy('name')->get(),
            'stats' => $stats,
        ]);
    }

    /**
     * جەردێکی نوێ — ژمارەی ئێستای هەموو کاڵا چالاکەکان وەک «ژمارەی سیستەم»
     * تۆمار دەکرێت. دوای ئەمە گۆڕانی مەخزەن کاریگەری لەسەر ئەم ژمارانە نییە.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'count_date' => ['required', 'date'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $count = DB::transaction(function () use ($data) {
            $count = StockCount::create($data + [
                'count_no' => StockCount::nextCountNo(),
                'status' => 'draft',
                'user_id' => auth()->id(),
            ]);

            $items = Item::active()
                ->withStock($data['warehouse_id'])
                ->orderBy('name')
                ->get();

            foreach ($items as $item) {
                StockCountItem::create([
                    'stock_count_id' => $count->id,
                    'item_id' => $item->id,
                    'system_qty' => $item->stock_qty,
                    'counted_qty' => null,
                    'difference' => 0,
                ]);
            }

            return $count;
        });

        return redirect()->route('counts.show', $count)->with('ok', 'جەردی نوێ بەسەرکەوتوویی دروستکرا. ئێستا دەتوانیت ژمارەکان تۆمار بکەیت.');
    }

    public function show(StockCount $count): View
    {
        $count->load(['warehouse', 'user', 'items.item.unit', 'items.item.category']);

        // ژماردنی ئامارە وردەکانی ناو ئەم جەردە
        $totalItems = $count->items->count();
        $countedItems = $count->items->whereNotNull('counted_qty')->count();
        $uncountedItems = $totalItems - $countedItems;

        $matchedItems = 0;
        $surplusItems = 0;
        $deficitItems = 0;
        $totalSystemQty = 0;
        $totalCountedQty = 0;

        foreach ($count->items as $item) {
            $totalSystemQty += (float) $item->system_qty;
            if ($item->counted_qty !== null) {
                $totalCountedQty += (float) $item->counted_qty;
                $diff = (float) $item->counted_qty - (float) $item->system_qty;
                if (abs($diff) < 0.0005) {
                    $matchedItems++;
                } elseif ($diff > 0) {
                    $surplusItems++;
                } else {
                    $deficitItems++;
                }
            }
        }

        $stats = [
            'total_items' => $totalItems,
            'counted_items' => $countedItems,
            'uncounted_items' => $uncountedItems,
            'matched_items' => $matchedItems,
            'surplus_items' => $surplusItems,
            'deficit_items' => $deficitItems,
            'total_system_qty' => $totalSystemQty,
            'total_counted_qty' => $totalCountedQty,
            'progress_percent' => $totalItems > 0 ? round(($countedItems / $totalItems) * 100) : 0,
        ];

        return view('counts.show', [
            'count' => $count,
            'stats' => $stats,
        ]);
    }

    /** پاشەکەوتکردنی ژمارە ژمێردراوەکان — بێ ئەوەی مەخزەن بگۆڕێت. */
    public function update(Request $request, StockCount $count)
    {
        if ($count->status === 'posted') {
            return back()->with('err', 'ئەم جەردە پێشتر پەسەندکراوە و ناتوانرێت دەستکاری بکرێت.');
        }

        $counted = $request->input('counted', []);
        $notes = $request->input('notes', []);

        DB::transaction(function () use ($count, $counted, $notes) {
            foreach ($count->items as $line) {
                $value = $counted[$line->id] ?? null;
                $note = $notes[$line->id] ?? null;

                $countedQty = ($value === '' || $value === null) ? null : (float) $value;
                $difference = $countedQty === null ? 0 : ($countedQty - (float) $line->system_qty);

                $line->update([
                    'counted_qty' => $countedQty,
                    'difference' => $difference,
                    'note' => $note,
                ]);
            }
        });

        return back()->with('ok', 'ژمارە ژمێردراوەکان بە سەرکەوتوویی پاشەکەوتکران.');
    }

    /** پەسەندکردن — جیاوازییەکان دەبنە جوڵەی ڕاستکردنەوە لە مەخزەندا. */
    public function post(StockCount $count)
    {
        if ($count->status === 'posted') {
            return back()->with('err', 'ئەم جەردە پێشتر پەسەندکراوە.');
        }

        if ($count->items()->whereNull('counted_qty')->exists()) {
            return back()->with('err', 'هێشتا هەندێک کاڵا ژمێردراو نەکراون! تکایە سەرەتا هەموو کاڵاکان پڕبکەرەوە یان سفریان بۆ دابنێ.');
        }

        $this->stock->postStockCount($count);

        return back()->with('ok', 'جەردەکە بە سەرکەوتوویی پەسەندکرا و مەخزەن بەپێی جیاوازییەکان ڕاستکرایەوە.');
    }

    public function destroy(StockCount $count)
    {
        if ($count->status === 'posted') {
            return back()->with('err', 'جەردی پەسەندکراو ناسڕدرێتەوە.');
        }

        $count->items()->delete();
        $count->delete();

        return redirect()->route('counts.index')->with('ok', 'جەردەکە بە سەرکەوتوویی سڕدرایەوە.');
    }
}

