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

        return view('warehouses.index', compact('warehouses', 'totalMovements', 'activeCount'));
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
