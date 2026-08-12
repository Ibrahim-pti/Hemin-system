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
            ->with(['unit', 'category'])
            ->search($request->string('q')->toString())
            ->when($request->integer('category'), fn ($q, $id) => $q->where('item_category_id', $id))
            ->when($request->boolean('low'), fn ($q) => $q->where('min_qty', '>', 0))
            ->orderBy('name')
            ->paginate(30)
            ->withQueryString();

        // پاڵاوتنی «تەنها کاڵای کەم» دوای حیسابی باڵانس دەکرێت.
        if ($request->boolean('low')) {
            $items->setCollection(
                $items->getCollection()->filter(fn (Item $i) => $i->stock_qty <= (float) $i->min_qty)->values()
            );
        }

        return view('items.index', [
            'items' => $items,
            'categories' => ItemCategory::orderBy('name')->get(),
            'warehouses' => Warehouse::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('items.form', [
            'item' => new Item(['cost_currency' => 'IQD', 'is_active' => true]),
            'categories' => ItemCategory::orderBy('name')->get(),
            'units' => Unit::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $item = Item::create($this->validated($request));

        return redirect()->route('items.index')->with('ok', "کاڵای «{$item->name}» زیادکرا.");
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

        return redirect()->route('items.index')->with('ok', 'کاڵاکە نوێکرایەوە.');
    }

    public function destroy(Item $item)
    {
        // کاڵایەک کە جوڵەی هەیە ناسڕدرێتەوە — مێژووەکەی دەشکێت.
        if ($item->movements()->exists()) {
            return back()->with('err', 'ناتوانرێت بسڕدرێتەوە — ئەم کاڵایە جوڵەی مەخزەنی هەیە. لەبری ئەوە ناچالاکی بکە.');
        }

        $item->delete();

        return redirect()->route('items.index')->with('ok', 'کاڵاکە سڕدرایەوە.');
    }

    private function validated(Request $request, ?Item $item = null): array
    {
        $unique = 'unique:items,code'.($item ? ",{$item->id}" : '');

        $data = $request->validate([
            'code' => ['required', 'string', 'max:50', $unique],
            'name' => ['required', 'string', 'max:255'],
            'item_category_id' => ['nullable', 'exists:item_categories,id'],
            'unit_id' => ['required', 'exists:units,id'],
            'min_qty' => ['nullable', 'numeric', 'min:0'],
            'last_cost' => ['nullable', 'numeric', 'min:0'],
            'cost_currency' => ['nullable', 'in:IQD,USD'],
            'sale_price' => ['nullable', 'numeric', 'min:0'],
            'note' => ['nullable', 'string'],
        ], [], [
            'code' => 'کۆد',
            'name' => 'ناو',
            'unit_id' => 'یەکە',
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $data['min_qty'] = $data['min_qty'] ?? 0;

        // بەرپرسی کۆگا بۆی نییە نرخ بگۆڕێت.
        if (! auth()->user()->canSeeMoney()) {
            unset($data['last_cost'], $data['sale_price'], $data['cost_currency']);
        }

        return $data;
    }
}
