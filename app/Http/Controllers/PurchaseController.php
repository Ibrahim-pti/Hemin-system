<?php

namespace App\Http\Controllers;

use App\Models\ExchangeRate;
use App\Models\Item;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Services\PaymentService;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PurchaseController extends Controller
{
    public function __construct(
        private readonly StockService $stock,
        private readonly PaymentService $payments,
    ) {}

    public function index(Request $request): View
    {
        $query = Purchase::query()
            ->with(['supplier', 'warehouse', 'items'])
            ->search($request->string('q')->toString())
            ->when($request->string('status')->toString(), fn ($q, $s) => $q->where('status', $s))
            ->when($request->date('from'), fn ($q, $d) => $q->whereDate('purchase_date', '>=', $d))
            ->when($request->date('to'), fn ($q, $d) => $q->whereDate('purchase_date', '<=', $d))
            ->latest('purchase_date')
            ->latest('id');

        $totalPurchasesCount = Purchase::count();
        $confirmedPurchases = Purchase::where('status', 'confirmed')->get();
        $totalPurchasesAmount = $confirmedPurchases->sum('total');
        $totalPurchasesPaid = $confirmedPurchases->sum('paid_amount');
        $totalRemainingDebt = max(0, $totalPurchasesAmount - $totalPurchasesPaid);
        $draftCount = Purchase::where('status', 'draft')->count();

        $purchases = (clone $query)->paginate(15)->withQueryString();

        return view('purchases.index', compact(
            'purchases',
            'totalPurchasesCount',
            'totalPurchasesAmount',
            'totalRemainingDebt',
            'draftCount'
        ));
    }

    public function create(): View
    {
        return view('purchases.form', [
            'purchase' => new Purchase([
                'currency' => 'IQD',
                'purchase_date' => now()->toDateString(),
                'warehouse_id' => Warehouse::defaultId(),
            ]),
            'suppliers' => Supplier::active()->orderBy('name')->get(),
            'warehouses' => Warehouse::where('is_active', true)->orderBy('name')->get(),
            'items' => Item::active()->with('unit')->orderBy('name')->get(),
            'rate' => ExchangeRate::current(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $purchase = DB::transaction(function () use ($data, $request) {
            $purchase = Purchase::create($this->header($data) + [
                'invoice_no' => Purchase::nextInvoiceNo(),
                'status' => 'draft',
                'user_id' => auth()->id(),
            ]);

            $this->syncLines($purchase, $data['lines']);

            if ($request->boolean('confirm')) {
                $this->stock->postPurchase($purchase);
            }

            // پارەی دراو لە کاتی کڕین وەک حەقدییەکی جیا تۆمار دەکرێت.
            if (($data['paid_amount'] ?? 0) > 0) {
                $this->payments->record([
                    'direction' => 'out',
                    'amount' => $data['paid_amount'],
                    'currency' => $data['currency'],
                    'paid_at' => $data['purchase_date'],
                    'party' => Supplier::find($data['supplier_id']),
                    'purchase_id' => $purchase->id,
                    'category' => 'supplier_payment',
                    'note' => 'پارەدان لەگەڵ پسوولەی کڕینی '.$purchase->invoice_no,
                ]);
            }

            return $purchase;
        });

        return redirect()->route('purchases.show', $purchase)->with('ok', 'پسوولەی کڕین تۆمارکرا.');
    }

    public function show(Purchase $purchase): View
    {
        $purchase->load(['supplier', 'warehouse', 'items.item.unit', 'payments', 'user']);

        return view('purchases.show', compact('purchase'));
    }

    public function edit(Purchase $purchase): View
    {
        if ($purchase->status === 'confirmed') {
            abort(403, 'پسوولەی پەسەندکراو ناگۆڕدرێت — سەرەتا هەڵیبوەشێنەوە.');
        }

        $purchase->load('items');

        return view('purchases.form', [
            'purchase' => $purchase,
            'suppliers' => Supplier::active()->orderBy('name')->get(),
            'warehouses' => Warehouse::where('is_active', true)->orderBy('name')->get(),
            'items' => Item::active()->with('unit')->orderBy('name')->get(),
            'rate' => ExchangeRate::current(),
        ]);
    }

    public function update(Request $request, Purchase $purchase)
    {
        if ($purchase->status === 'confirmed') {
            return back()->with('err', 'پسوولەی پەسەندکراو ناگۆڕدرێت.');
        }

        $data = $this->validated($request);

        DB::transaction(function () use ($purchase, $data, $request) {
            $purchase->update($this->header($data));
            $purchase->items()->delete();
            $this->syncLines($purchase, $data['lines']);

            if ($request->boolean('confirm')) {
                $this->stock->postPurchase($purchase);
            }
        });

        return redirect()->route('purchases.show', $purchase)->with('ok', 'نوێکرایەوە.');
    }

    /** پەسەندکردن — کاڵاکان دەچنە مەخزەنەوە. */
    public function confirm(Purchase $purchase)
    {
        if ($purchase->status === 'confirmed') {
            return back()->with('err', 'پێشتر پەسەندکراوە.');
        }

        $this->stock->postPurchase($purchase);

        return back()->with('ok', 'پەسەندکرا — کاڵاکان چوونە مەخزەنەوە.');
    }

    /** هەڵوەشاندنەوە — جوڵەکانی مەخزەن دەسڕدرێنەوە. */
    public function unconfirm(Purchase $purchase)
    {
        $this->stock->unpostPurchase($purchase);

        return back()->with('ok', 'هەڵوەشێنرایەوە — جوڵەکانی مەخزەن سڕدرانەوە.');
    }

    public function destroy(Purchase $purchase)
    {
        if ($purchase->status === 'confirmed') {
            return back()->with('err', 'سەرەتا پسوولەکە هەڵبوەشێنەوە.');
        }

        DB::transaction(function () use ($purchase) {
            $purchase->items()->delete();
            $purchase->delete();
        });

        return redirect()->route('purchases.index')->with('ok', 'سڕدرایەوە.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'purchase_date' => ['required', 'date'],
            'currency' => ['required', 'in:IQD,USD'],
            'exchange_rate' => ['nullable', 'numeric', 'min:0'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'paid_amount' => ['nullable', 'numeric', 'min:0'],
            'note' => ['nullable', 'string'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.item_id' => ['required', 'exists:items,id'],
            'lines.*.qty' => ['required', 'numeric', 'gt:0'],
            'lines.*.unit_price' => ['required', 'numeric', 'min:0'],
            'lines.*.note' => ['nullable', 'string', 'max:255'],
        ], [
            'lines.required' => 'لانیکەم یەک کاڵا زیاد بکە.',
            'lines.*.qty.gt' => 'بڕ دەبێت لە سفر زیاتر بێت.',
        ]);
    }

    /** خانەکانی سەرەوەی پسوولە — کۆکان لە دێڕەکانەوە دەردەچن. */
    private function header(array $data): array
    {
        $subtotal = collect($data['lines'])
            ->sum(fn ($line) => (float) $line['qty'] * (float) $line['unit_price']);

        $discount = (float) ($data['discount_amount'] ?? 0);

        return [
            'supplier_id' => $data['supplier_id'],
            'warehouse_id' => $data['warehouse_id'],
            'purchase_date' => $data['purchase_date'],
            'currency' => $data['currency'],
            'exchange_rate' => $data['currency'] === 'USD'
                ? ($data['exchange_rate'] ?: ExchangeRate::forDate($data['purchase_date']))
                : null,
            'subtotal' => $subtotal,
            'discount_amount' => $discount,
            'total' => max(0, $subtotal - $discount),
            'paid_amount' => $data['paid_amount'] ?? 0,
            'note' => $data['note'] ?? null,
        ];
    }

    private function syncLines(Purchase $purchase, array $lines): void
    {
        foreach ($lines as $line) {
            PurchaseItem::create([
                'purchase_id' => $purchase->id,
                'item_id' => $line['item_id'],
                'qty' => $line['qty'],
                'unit_price' => $line['unit_price'],
                'line_total' => (float) $line['qty'] * (float) $line['unit_price'],
                'note' => $line['note'] ?? null,
            ]);
        }
    }
}
