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

    public function index(): View
    {
        return view('counts.index', [
            'counts' => StockCount::with(['warehouse', 'user'])
                ->withCount('items')
                ->latest('id')
                ->paginate(20),
            'warehouses' => Warehouse::where('is_active', true)->orderBy('name')->get(),
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
            'note' => ['nullable', 'string'],
        ]);

        $count = DB::transaction(function () use ($data) {
            $count = StockCount::create($data + [
                'count_no' => StockCount::nextCountNo(),
                'status' => 'draft',
                'user_id' => auth()->id(),
            ]);

            $items = Item::active()->orderBy('name')->get();

            foreach ($items as $item) {
                StockCountItem::create([
                    'stock_count_id' => $count->id,
                    'item_id' => $item->id,
                    'system_qty' => $item->stockQty((int) $data['warehouse_id']),
                    'counted_qty' => null,
                    'difference' => 0,
                ]);
            }

            return $count;
        });

        return redirect()->route('counts.show', $count)->with('ok', 'جەردی نوێ دەستی پێکرد.');
    }

    public function show(StockCount $count): View
    {
        $count->load(['warehouse', 'items.item.unit']);

        return view('counts.show', ['count' => $count]);
    }

    /** پاشەکەوتکردنی ژمارە ژمێردراوەکان — بێ ئەوەی مەخزەن بگۆڕێت. */
    public function update(Request $request, StockCount $count)
    {
        if ($count->status === 'posted') {
            return back()->with('err', 'ئەم جەردە پەسەندکراوە و ناگۆڕدرێت.');
        }

        $counted = $request->input('counted', []);

        DB::transaction(function () use ($count, $counted) {
            foreach ($count->items as $line) {
                $value = $counted[$line->id] ?? null;

                $line->update([
                    'counted_qty' => $value === '' || $value === null ? null : (float) $value,
                    'difference' => $value === '' || $value === null
                        ? 0
                        : (float) $value - (float) $line->system_qty,
                ]);
            }
        });

        return back()->with('ok', 'ژمارەکان پاشەکەوتکران.');
    }

    /** پەسەندکردن — جیاوازییەکان دەبنە جوڵەی ڕاستکردنەوە لە مەخزەندا. */
    public function post(StockCount $count)
    {
        if ($count->status === 'posted') {
            return back()->with('err', 'پێشتر پەسەندکراوە.');
        }

        if ($count->items()->whereNull('counted_qty')->exists()) {
            return back()->with('err', 'هێشتا هەندێک کاڵا ژمێردراو نەکراون — سەرەتا هەموویان پڕبکەرەوە.');
        }

        $this->stock->postStockCount($count);

        return back()->with('ok', 'جەردەکە پەسەندکرا و مەخزەن ڕاستکرایەوە.');
    }

    public function destroy(StockCount $count)
    {
        if ($count->status === 'posted') {
            return back()->with('err', 'جەردی پەسەندکراو ناسڕدرێتەوە.');
        }

        $count->items()->delete();
        $count->delete();

        return redirect()->route('counts.index')->with('ok', 'جەردەکە سڕدرایەوە.');
    }
}
