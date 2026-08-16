<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\Unit;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ItemController extends Controller
{
    public function index(Request $request): View
    {
        $warehouseId = $request->integer('warehouse') ?: null;

        $items = Item::query()
            ->withStock($warehouseId)
            ->with(['unit'])
            ->search($request->string('q')->toString())
            ->when($request->boolean('low'), fn ($q) => $q->where('min_qty', '>', 0))
            ->orderBy('name')
            ->paginate(30)
            ->withQueryString();

        // پاڵاوتنی «تەنها مەوادی کەم» دوای حیسابی باڵانس دەکرێت.
        if ($request->boolean('low')) {
            $items->setCollection(
                $items->getCollection()->filter(fn (Item $i) => $i->stock_qty <= (float) $i->min_qty)->values()
            );
        }

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
                'purchase_date' => now()->toDateString(),
                'is_active' => true,
            ]),
            'categories' => ItemCategory::orderBy('name')->get(),
            'units' => Unit::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $item = Item::create($this->validated($request));

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

        $item->delete();

        return redirect()->route('items.index')->with('ok', 'بابەتەکە سڕدرایەوە.');
    }

    private function validated(Request $request, ?Item $item = null): array
    {
        // لابردنی فاریزە لە نرخ و بڕ پێش پشکنین
        if ($request->has('last_cost')) {
            $rawCost = str_replace(',', '', (string) $request->input('last_cost'));
            $request->merge(['last_cost' => $rawCost !== '' ? $rawCost : null]);
        }
        if ($request->has('min_qty')) {
            $rawMinQty = str_replace(',', '', (string) $request->input('min_qty'));
            $request->merge(['min_qty' => $rawMinQty !== '' ? $rawMinQty : 0]);
        }

        $unique = 'unique:items,code'.($item ? ",{$item->id}" : '');

        $data = $request->validate([
            'code' => ['required', 'string', 'max:50', $unique],
            'name' => ['required', 'string', 'max:255'],
            'item_category_id' => ['nullable', 'exists:item_categories,id'],
            'unit_id' => ['required', 'exists:units,id'],
            'min_qty' => ['nullable', 'numeric', 'min:0'],
            'last_cost' => ['nullable', 'numeric', 'min:0'],
            'cost_currency' => ['nullable', 'in:IQD,USD'],
            'purchase_date' => ['nullable', 'date'],
            'sale_price' => ['nullable', 'numeric', 'min:0'],
            'is_for_sale' => ['nullable', 'boolean'],
            'note' => ['nullable', 'string'],
        ], [], [
            'code' => 'کۆد',
            'name' => 'ناو',
            'unit_id' => 'یەکە',
            'min_qty' => 'نرخی بڕ',
            'last_cost' => 'تێچووی کڕین',
            'purchase_date' => 'بەرواری کڕین',
        ]);

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
