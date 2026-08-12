<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\StockMovement;
use App\Models\Warehouse;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class StockMovementController extends Controller
{
    public function __construct(private readonly StockService $stock) {}

    public function index(Request $request): View
    {
        $movements = StockMovement::query()
            ->with(['item.unit', 'warehouse', 'user'])
            ->when($request->integer('item'), fn ($q, $id) => $q->where('item_id', $id))
            ->when($request->integer('warehouse'), fn ($q, $id) => $q->where('warehouse_id', $id))
            ->when($request->string('reason')->toString(), fn ($q, $r) => $q->where('reason', $r))
            ->when($request->date('from'), fn ($q, $d) => $q->whereDate('moved_at', '>=', $d))
            ->when($request->date('to'), fn ($q, $d) => $q->whereDate('moved_at', '<=', $d))
            ->latest('moved_at')
            ->latest('id')
            ->paginate(40)
            ->withQueryString();

        return view('stock.index', [
            'movements' => $movements,
            'warehouses' => Warehouse::where('is_active', true)->orderBy('name')->get(),
            'items' => Item::active()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function create(Request $request): View
    {
        return view('stock.form', [
            'items' => Item::active()->with('unit')->orderBy('name')->get(),
            'warehouses' => Warehouse::where('is_active', true)->orderBy('name')->get(),
            'selectedItem' => $request->integer('item') ?: null,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'type' => ['required', 'in:in,out,transfer'],
            'item_id' => ['required', 'exists:items,id'],
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'to_warehouse_id' => ['nullable', 'required_if:type,transfer', 'different:warehouse_id', 'exists:warehouses,id'],
            'qty' => ['required', 'numeric', 'gt:0'],
            'reason' => ['required', 'in:opening,adjustment,damage,production,transfer'],
            'moved_at' => ['required', 'date'],
            'note' => ['nullable', 'string'],
        ], [
            'to_warehouse_id.different' => 'کۆگای وەرگر دەبێت جیاواز بێت.',
            'qty.gt' => 'بڕ دەبێت لە سفر زیاتر بێت.',
        ]);

        DB::transaction(function () use ($data) {
            $extra = ['moved_at' => $data['moved_at'], 'note' => $data['note'] ?? null];

            if ($data['type'] === 'transfer') {
                // گواستنەوە = دوو جوڵە: دەرچوون لە کۆگای ناردن، چوونەژوورەوە لە وەرگر.
                $this->stock->record($data['item_id'], $data['warehouse_id'], 'out', $data['qty'], 'transfer', null, $extra);
                $this->stock->record($data['item_id'], $data['to_warehouse_id'], 'in', $data['qty'], 'transfer', null, $extra);

                return;
            }

            $this->stock->record(
                $data['item_id'],
                $data['warehouse_id'],
                $data['type'],
                $data['qty'],
                $data['reason'],
                null,
                $extra,
            );
        });

        return redirect()->route('stock.index')->with('ok', 'جوڵەکە تۆمارکرا.');
    }

    public function destroy(StockMovement $movement)
    {
        // جوڵەی پەیوەست بە پسوولەیەکەوە لێرەوە ناسڕدرێتەوە — دەبێت پسوولەکە
        // هەڵبوەشێنرێتەوە، ئەگەر نا پسوولە و مەخزەن ناتەبا دەبن.
        if ($movement->reference_type) {
            return back()->with('err', 'ئەم جوڵەیە بە بەڵگەنامەیەکەوە بەستراوە — لەوێوە هەڵیبوەشێنەوە.');
        }

        $movement->delete();

        return back()->with('ok', 'جوڵەکە سڕدرایەوە.');
    }
}
