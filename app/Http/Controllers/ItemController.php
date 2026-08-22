<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\Unit;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Validation\Rule;

class ItemController extends Controller
{
    public function index(Request $request): View
    {
        $warehouseId = $request->integer('warehouse') ?: null;
        $qtyFilter = $request->string('qty_filter')->toString() ?: $request->string('sort')->toString();
        $order = $request->string('order')->toString();

        $query = Item::query()
            ->withStock($warehouseId)
            ->with(['unit'])
            ->search($request->string('q')->toString());

        // پاڵاوتن بەپێی بڕ (زۆر / کەم)
        if ($qtyFilter === 'qty_desc') {
            $query->orderByDesc('min_qty');
        } elseif ($qtyFilter === 'qty_asc') {
            $query->orderBy('min_qty');
        }

        // ڕیزبەندی
        match ($order) {
            'cost_desc' => $query->orderByDesc('last_cost'),
            'cost_asc' => $query->orderBy('last_cost'),
            'date_desc' => $query->latest('purchase_date')->latest('id'),
            'date_asc' => $query->oldest('purchase_date')->oldest('id'),
            'latest' => $query->latest('id'),
            default => empty($qtyFilter) ? $query->orderBy('name') : null,
        };

        $items = $query->paginate(30)->withQueryString();

        return view('items.index', [
            'items' => $items,
            'warehouses' => Warehouse::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('items.form', [
            'item' => new Item([
                'cost_currency' => 'IQD', 
                'is_active' => true,
            ]),
            'categories' => ItemCategory::orderBy('name')->get(),
            'units' => Unit::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $item = Item::create($this->validated($request));

        // تۆمارکردنی باڵانسی سەرەتایی ئەگەر بڕ نووسرابوو
        $initialQty = (float) $request->input('min_qty', 0);
        if ($initialQty > 0) {
            $defaultWarehouse = Warehouse::where('is_default', true)->first() ?? Warehouse::first();
            if ($defaultWarehouse) {
                app(\App\Services\StockService::class)->record(
                    itemId: $item->id,
                    warehouseId: $defaultWarehouse->id,
                    direction: 'in',
                    qty: $initialQty,
                    reason: 'opening',
                    extra: [
                        'unit_cost' => $item->last_cost ?? 0,
                        'currency' => $item->cost_currency ?? 'IQD',
                        'moved_at' => $item->purchase_date?->toDateString() ?? now()->toDateString(),
                        'note' => 'باڵانسی سەرەتایی مەواد',
                    ]
                );
            }
        }

        // زیادکردن بۆ جەردە کراوەکان (Draft)
        $draftCounts = \App\Models\StockCount::where('status', 'draft')->get();
        foreach ($draftCounts as $draftCount) {
            $currentStock = $item->stockQty($draftCount->warehouse_id);
            \App\Models\StockCountItem::updateOrCreate([
                'stock_count_id' => $draftCount->id,
                'item_id' => $item->id,
            ], [
                'system_qty' => $currentStock,
                'counted_qty' => $currentStock > 0 ? $currentStock : null,
                'difference' => 0,
                'unit_price' => $item->last_cost > 0 ? $item->last_cost : ($item->sale_price ?? 0),
            ]);
        }

        return redirect()->route('items.index')->with('ok', "مەوادی «{$item->name}» زیادکرا.");
    }

    public function show(Item $item): View
    {
        $movements = $item->movements()
            ->with(['warehouse', 'user'])
            ->latest('moved_at')
            ->latest('id')
            ->paginate(25);

        return view('items.show', [
            'item' => $item,
            'movements' => $movements,
            'warehouses' => Warehouse::where('is_active', true)->get(),
        ]);
    }

    public function edit(Item $item): View
    {
        return view('items.form', [
            'item' => $item,
            'categories' => ItemCategory::orderBy('name')->get(),
            'units' => Unit::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Item $item)
    {
        $item->update($this->validated($request, $item));

        return redirect()->route('items.index')->with('ok', 'بابەتەکە نوێکرایەوە.');
    }

    public function destroy(Item $item)
    {
        // بابەتێک کە جوڵەی هەیە ناسڕدرێتەوە — مێژووەکەی دەشکێت.
        if ($item->movements()->exists()) {
            return back()->with('err', 'ناتوانرێت بسڕدرێتەوە — ئەم بابەتە جوڵەی مەخزەنی هەیە. لەبری ئەوە ناچالاکی بکە.');
        }

        // سڕینەوە لە جەردە کراوەکاندا
        \App\Models\StockCountItem::where('item_id', $item->id)->delete();

        $item->delete();

        return redirect()->route('items.index')->with('ok', 'بابەتەکە سڕدرایەوە.');
    }

    private function validated(Request $request, ?Item $item = null): array
    {
        // دروستکردنی ئۆتۆماتیکی کۆد ئەگەر نەنووسرابوو
        if (! $request->filled('code')) {
            if ($item && $item->code) {
                $request->merge(['code' => $item->code]);
            } else {
                $nextNum = ((int) Item::withTrashed()->max('id')) + 1;
                do {
                    $generatedCode = 'M-' . str_pad((string) $nextNum, 4, '0', STR_PAD_LEFT);
                    $nextNum++;
                } while (Item::withTrashed()->where('code', $generatedCode)->exists());

                $request->merge(['code' => $generatedCode]);
            }
        }

        // لابردنی فاریزە لە نرخ و بڕ پێش پشکنین
        if ($request->has('last_cost')) {
            $rawCost = str_replace(',', '', (string) $request->input('last_cost'));
            $request->merge(['last_cost' => $rawCost !== '' ? $rawCost : null]);
        }
        if ($request->has('min_qty')) {
            $rawMinQty = str_replace(',', '', (string) $request->input('min_qty'));
            $request->merge(['min_qty' => $rawMinQty !== '' ? $rawMinQty : 0]);
        }

        $codeRule = Rule::unique('items', 'code')->whereNull('deleted_at');
        if ($item) {
            $codeRule->ignore($item->id);
        }

        $data = $request->validate([
            'code' => ['nullable', 'string', 'max:50', $codeRule],
            'name' => ['required', 'string', 'max:255'],
            'item_category_id' => ['nullable', 'exists:item_categories,id'],
            'unit_id' => ['required', 'exists:units,id'],
            'min_qty' => ['nullable', 'numeric', 'min:0'],
            'last_cost' => ['nullable', 'numeric', 'min:0'],
            'cost_currency' => ['nullable', 'in:IQD,USD'],
            'purchase_date' => ['nullable', 'date'],
            'sale_price' => ['nullable', 'numeric', 'min:0'],
            'image' => ['nullable', 'image', 'max:5120'],
            'is_for_sale' => ['nullable', 'boolean'],
            'note' => ['nullable', 'string'],
        ], [], [
            'code' => 'کۆد',
            'name' => 'ناو',
            'image' => 'وێنە',
            'unit_id' => 'یەکە',
            'min_qty' => 'نرخی بڕ',
            'last_cost' => 'تێچووی کڕین',
            'purchase_date' => 'بەرواری کڕین',
        ]);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('items', 'public');
        }

        $data['is_active'] = $request->boolean('is_active', true);
        $data['is_for_sale'] = $request->boolean('is_for_sale');
        $data['min_qty'] = $data['min_qty'] ?? 0;

        // ئەگەر بۆ فرۆشتن نەبوو، نرخی فرۆشتن لادەبرێت
        if (! $data['is_for_sale']) {
            $data['sale_price'] = null;
        }

        // بەرپرسی کۆگا بۆی نییە نرخ بگۆڕێت.
        if (! auth()->user()->canSeeMoney()) {
            unset($data['last_cost'], $data['sale_price'], $data['cost_currency']);
        }

        return $data;
    }
}
