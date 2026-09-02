<?php

namespace App\Http\Controllers;

use App\Models\ExchangeRate;
use App\Models\Item;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Supplier;
use App\Models\Unit;
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
            ->with(['supplier', 'warehouse', 'items.item.unit'])
            ->search($request->string('q')->toString())
            ->when($request->filled('supplier_id'), fn ($q) => $q->where('supplier_id', $request->input('supplier_id')))
            ->when($request->string('status')->toString(), fn ($q, $s) => $q->where('status', $s))
            ->when($request->string('payment_status')->toString() === 'debt', fn ($q) => $q->where('status', 'confirmed')->whereRaw('total > paid_amount'))
            ->when($request->string('payment_status')->toString() === 'paid', fn ($q) => $q->where('status', 'confirmed')->whereRaw('total <= paid_amount'))
            ->when($request->date('from'), fn ($q, $d) => $q->whereDate('purchase_date', '>=', $d))
            ->when($request->date('to'), fn ($q, $d) => $q->whereDate('purchase_date', '<=', $d))
            ->latest('purchase_date')
            ->latest('id');

        $totalPurchasesCount = Purchase::count();
        $confirmedPurchases = Purchase::where('status', 'confirmed')->get();
        $totalPurchasesAmount = (float) $confirmedPurchases->sum('total');
        $totalPurchasesPaid = (float) $confirmedPurchases->sum('paid_amount');
        $totalRemainingDebt = max(0, $totalPurchasesAmount - $totalPurchasesPaid);
        $draftCount = Purchase::where('status', 'draft')->count();

        // پوختەی کۆمپانیا و فرۆشیارەکان و قەرزەکانیان
        $allSuppliers = Supplier::active()
            ->when($request->input('tab') === 'suppliers' && $request->filled('q'), fn ($q) => $q->search($request->string('q')->toString()))
            ->withCount(['purchases' => fn ($q) => $q->where('status', 'confirmed')])
            ->get();

        $suppliersSummary = $allSuppliers->map(function ($supplier) {
            $totalPurchases = (float) $supplier->totalPurchases();
            $totalPaid = (float) $supplier->totalPaid();
            $balance = (float) $supplier->balance();
            $lastPurchase = $supplier->purchases()->latest('purchase_date')->first();

            return (object) [
                'id' => $supplier->id,
                'name' => $supplier->name,
                'phone' => $supplier->phone,
                'address' => $supplier->address,
                'purchases_count' => $supplier->purchases_count,
                'total_purchases' => $totalPurchases,
                'total_paid' => $totalPaid,
                'balance' => $balance,
                'last_purchase_date' => $lastPurchase?->purchase_date,
            ];
        })->sortByDesc('balance')->values();

        $totalSuppliersCount = $allSuppliers->count();
        $totalSuppliersWithDebtCount = $suppliersSummary->where('balance', '>', 0)->count();
        $totalCompanyDebt = $suppliersSummary->where('balance', '>', 0)->sum('balance');
        $suppliersList = Supplier::active()->orderBy('name')->get();

        $purchases = (clone $query)->paginate(15)->withQueryString();

        return view('purchases.index', compact(
            'purchases',
            'totalPurchasesCount',
            'totalPurchasesAmount',
            'totalPurchasesPaid',
            'totalRemainingDebt',
            'draftCount',
            'suppliersSummary',
            'totalSuppliersCount',
            'totalSuppliersWithDebtCount',
            'totalCompanyDebt',
            'suppliersList'
        ));
    }

    public function create(): View
    {
        $workshopWarehouse = Warehouse::where('name', 'like', '%دروستکردن%')->first()
            ?? Warehouse::where('is_default', false)->first()
            ?? Warehouse::first();

        return view('purchases.form', [
            'purchase' => new Purchase([
                'currency' => 'IQD',
                'purchase_date' => now()->toDateString(),
                'warehouse_id' => $workshopWarehouse?->id ?? Warehouse::defaultId(),
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

        $imagePath = null;
        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            $imagePath = $request->file('image')->store('purchases', 'public');
        }

        $purchase = DB::transaction(function () use ($data, $request, $imagePath) {
            $headerData = $this->header($data);

            $purchase = Purchase::create($headerData + [
                'invoice_no' => Purchase::nextInvoiceNo(),
                'status' => 'draft',
                'user_id' => auth()->id(),
                'image' => $imagePath,
            ]);

            $this->syncLines($purchase, $data['lines']);

            if ($request->boolean('confirm')) {
                $this->stock->postPurchase($purchase);
            }

            // پارەی دراو لە کاتی کڕین وەک حەقدییەکی جیا تۆمار دەکرێت.
            if (($headerData['paid_amount'] ?? 0) > 0) {
                $this->payments->record([
                    'direction' => 'out',
                    'amount' => $headerData['paid_amount'],
                    'currency' => $headerData['currency'],
                    'paid_at' => $headerData['purchase_date'],
                    'party' => Supplier::find($headerData['supplier_id']),
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

    public function print(Purchase $purchase): View
    {
        $purchase->load(['supplier', 'warehouse', 'items.item.unit', 'payments.user', 'user']);
        $settings = \App\Models\Setting::all_();

        return view('purchases.print', compact('purchase', 'settings'));
    }

    public function edit(Purchase $purchase): View
    {
        if ($purchase->status === 'confirmed') {
            abort(403, 'پسوولەی پەسەندکراو ناگۆڕدرێت — سەرەتا هەڵیبوەشێنەوە.');
        }

        $purchase->load('items.item');

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

        $imagePath = $purchase->image;
        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            $imagePath = $request->file('image')->store('purchases', 'public');
        } elseif ($request->boolean('remove_image')) {
            $imagePath = null;
        }

        DB::transaction(function () use ($purchase, $data, $request, $imagePath) {
            $purchase->update($this->header($data) + ['image' => $imagePath]);
            $purchase->items()->delete();
            $this->syncLines($purchase, $data['lines']);

            if ($request->boolean('confirm')) {
                $this->stock->postPurchase($purchase);
            }
        });

        return redirect()->route('purchases.show', $purchase)->with('ok', 'پسوولەی کڕین نوێکرایەوە.');
    }

    /** پەسەندکردن — کاڵاکان دەچنە مەخزەنەوە. */
    public function confirm(Purchase $purchase)
    {
        if ($purchase->status === 'confirmed') {
            return back()->with('err', 'ئەم پسوولەیە پێشتر پەسەندکراوە.');
        }

        $this->stock->postPurchase($purchase);

        return back()->with('ok', 'پسوولەکە پەسەندکرا و مەوادەکان چوونە کۆگاوە.');
    }

    /** هەڵوەشاندنەوە — کەمکردنەوەی مەوادەکان لە مەخزەن. */
    public function unconfirm(Purchase $purchase)
    {
        if ($purchase->status !== 'confirmed') {
            return back()->with('err', 'ئەم پسوولەیە هێشتا پەسەند نەکراوە.');
        }

        $this->stock->unpostPurchase($purchase);

        return back()->with('ok', 'پسوولەکە هەڵوەشێنرایەوە و مەوادەکان لە کۆگا کەمکرانەوە.');
    }

    public function destroy(Purchase $purchase)
    {
        if ($purchase->status === 'confirmed') {
            return back()->with('err', 'ناتوانیت پسوولەی پەسەندکراو بسڕیتەوە — سەرەتا هەڵیبوەشێنەوە.');
        }

        $purchase->delete();

        return redirect()->route('purchases.index')->with('ok', 'پسوولەی کڕین سڕایەوە.');
    }

    private function validated(Request $request): array
    {
        $input = $request->all();

        // پاککردنەوەی فاریزەکان لە ژمارەکان
        if (isset($input['discount_amount'])) {
            $input['discount_amount'] = str_replace(',', '', (string) $input['discount_amount']);
        }
        if (isset($input['paid_amount'])) {
            $input['paid_amount'] = str_replace(',', '', (string) $input['paid_amount']);
        }
        if (isset($input['quick_total'])) {
            $input['quick_total'] = str_replace(',', '', (string) $input['quick_total']);
        }

        // ئەگەر شێوازی تۆماری خێرا بێت (یان تەنها کۆی وەسڵ نووسرابێت)
        if (($input['entry_mode'] ?? 'itemized') === 'quick' || (!empty($input['quick_total']) && (float) $input['quick_total'] > 0)) {
            $quickTotal = (float) ($input['quick_total'] ?? 0);
            if ($quickTotal > 0) {
                $quickTitle = trim($input['quick_title'] ?? '') ?: 'مەوادی هەمەجۆری وەسڵ';
                $input['lines'] = [
                    [
                        'item_name' => $quickTitle,
                        'qty' => 1,
                        'unit_price' => $quickTotal,
                        'note' => $input['note'] ?? null,
                    ],
                ];
            }
        }

        if (isset($input['lines']) && is_array($input['lines'])) {
            foreach ($input['lines'] as $k => $l) {
                if (isset($l['unit_price'])) {
                    $input['lines'][$k]['unit_price'] = str_replace(',', '', (string) $l['unit_price']);
                }
                if (isset($l['qty'])) {
                    $qtyClean = str_replace(',', '', (string) $l['qty']);
                    $input['lines'][$k]['qty'] = ($qtyClean !== '' && (float) $qtyClean > 0) ? $qtyClean : 1;
                } else {
                    $input['lines'][$k]['qty'] = 1;
                }
                if (empty($input['lines'][$k]['item_name'])) {
                    $input['lines'][$k]['item_name'] = 'مەوادی کڕدراو';
                }
            }
        }
        $request->merge($input);

        return $request->validate([
            'supplier_name' => ['nullable', 'string', 'max:255'],
            'supplier_id' => ['nullable'],
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'purchase_date' => ['required', 'date'],
            'currency' => ['nullable', 'in:IQD,USD'],
            'exchange_rate' => ['nullable', 'numeric', 'min:0'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'paid_amount' => ['nullable', 'numeric', 'min:0'],
            'payment_type' => ['nullable', 'in:cash,debt,partial'],
            'image' => ['nullable', 'image', 'max:10240'],
            'note' => ['nullable', 'string'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.item_name' => ['nullable', 'string', 'max:255'],
            'lines.*.item_id' => ['nullable'],
            'lines.*.qty' => ['required', 'numeric', 'gt:0'],
            'lines.*.unit_price' => ['required', 'numeric', 'min:0'],
            'lines.*.note' => ['nullable', 'string', 'max:255'],
        ], [
            'lines.required' => 'تکایە بڕی پارەی پسوولەکە بنووسە.',
            'lines.*.qty.gt' => 'بڕ دەبێت لە سفر زیاتر بێت.',
        ]);
    }

    /** خانەکانی سەرەوەی پسوولە — کۆکان لە دێڕەکانەوە دەردەچن. */
    private function header(array $data): array
    {
        $supplierName = trim($data['supplier_name'] ?? $data['supplier_id'] ?? '');
        if (empty($supplierName)) {
            $supplierName = 'فرۆشیاری گشتی';
        }

        if (is_numeric($supplierName) && $existingSupplier = Supplier::find($supplierName)) {
            $supplierId = $existingSupplier->id;
        } else {
            $supplier = Supplier::firstOrCreate(
                ['name' => $supplierName],
                ['is_active' => true]
            );
            $supplierId = $supplier->id;
        }

        $subtotal = collect($data['lines'])
            ->sum(fn ($line) => (float) $line['qty'] * (float) $line['unit_price']);

        $discount = (float) ($data['discount_amount'] ?? 0);
        $total = max(0, $subtotal - $discount);
        $currency = $data['currency'] ?? 'IQD';

        // شێوازی پارەدان (حازری / نەقد، بە قەرز، یان بەشێکی دراوە)
        $paymentType = $data['payment_type'] ?? null;
        if ($paymentType === 'debt') {
            $paidAmount = 0;
        } elseif ($paymentType === 'cash') {
            $paidAmount = $total;
        } elseif ($paymentType === 'partial') {
            $paidAmount = min($total, (float) ($data['paid_amount'] ?? 0));
        } else {
            $paidAmount = (float) ($data['paid_amount'] ?? 0);
        }

        return [
            'supplier_id' => $supplierId,
            'warehouse_id' => $data['warehouse_id'],
            'purchase_date' => $data['purchase_date'],
            'currency' => $currency,
            'exchange_rate' => $currency === 'USD'
                ? ($data['exchange_rate'] ?? ExchangeRate::forDate($data['purchase_date']))
                : null,
            'subtotal' => $subtotal,
            'discount_amount' => $discount,
            'total' => $total,
            'paid_amount' => $paidAmount,
            'note' => $data['note'] ?? null,
        ];
    }

    private function syncLines(Purchase $purchase, array $lines): void
    {
        $defaultUnitId = Unit::first()?->id ?? 1;

        foreach ($lines as $line) {
            $itemName = trim($line['item_name'] ?? $line['item_id'] ?? '');
            if (empty($itemName)) {
                continue;
            }

            if (is_numeric($itemName) && $existingItem = Item::find($itemName)) {
                $item = $existingItem;
            } else {
                $item = Item::where('name', $itemName)->first() ?? Item::create([
                    'code' => Item::nextCode(),
                    'name' => $itemName,
                    'unit_id' => $defaultUnitId,
                    'is_active' => true,
                    'last_cost' => $line['unit_price'],
                    'cost_currency' => $purchase->currency ?? 'IQD',
                ]);
            }

            $item->update([
                'last_cost' => $line['unit_price'],
                'cost_currency' => $purchase->currency ?? 'IQD',
                'purchase_date' => $purchase->purchase_date,
            ]);

            PurchaseItem::create([
                'purchase_id' => $purchase->id,
                'item_id' => $item->id,
                'qty' => $line['qty'],
                'unit_price' => $line['unit_price'],
                'line_total' => (float) $line['qty'] * (float) $line['unit_price'],
                'note' => $line['note'] ?? null,
            ]);
        }
    }
}
