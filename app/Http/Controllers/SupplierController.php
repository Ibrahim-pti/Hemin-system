<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Payment;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Supplier;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SupplierController extends Controller
{
    public function index(Request $request): View
    {
        $suppliers = Supplier::query()
            ->search($request->string('q')->toString())
            ->withCount('purchases')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $allSuppliers = Supplier::all();
        $totalPurchases = $allSuppliers->sum(fn($s) => $s->totalPurchases());
        $totalPaid = $allSuppliers->sum(fn($s) => $s->totalPaid());
        $totalDebt = $allSuppliers->sum(fn($s) => $s->balance());

        return view('suppliers.index', compact('suppliers', 'totalPurchases', 'totalPaid', 'totalDebt'));
    }

    public function create(): View
    {
        return view('suppliers.form', [
            'supplier' => new Supplier(['opening_currency' => 'IQD', 'is_active' => true]),
            'items' => Item::active()->with('unit')->orderBy('name')->get(),
            'units' => \App\Models\Unit::where('is_active', true)->orderBy('name')->get(),
            'warehouses' => Warehouse::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $supplier = DB::transaction(function () use ($data, $request) {
            $supplier = Supplier::create($data);

            // وەرگرتنی مەوادە داخڵکراوەکان بە دەست
            $lines = $request->input('purchase_lines', []);
            $validLines = [];
            $totalCost = 0;

            foreach ($lines as $line) {
                $name = trim((string) ($line['name'] ?? ''));
                $qty = (float) str_replace(',', '', (string) ($line['qty'] ?? 0));
                $unitPrice = (float) str_replace(',', '', (string) ($line['unit_price'] ?? 0));
                $unitId = (int) ($line['unit_id'] ?? (\App\Models\Unit::first()?->id ?? 1));

                if ($name !== '' && $qty > 0) {
                    $item = Item::where('name', $name)->first();
                    if (!$item) {
                        $item = Item::create([
                            'name' => $name,
                            'code' => Item::nextCode(),
                            'unit_id' => $unitId,
                            'last_cost' => $unitPrice,
                            'purchase_date' => $request->input('purchase_date', now()->toDateString()),
                            'cost_currency' => 'IQD',
                            'is_for_sale' => false,
                            'is_active' => true,
                        ]);
                    } elseif ($unitPrice > 0) {
                        $item->update(['last_cost' => $unitPrice]);
                    }

                    $lineTotal = $qty * $unitPrice;
                    $totalCost += $lineTotal;

                    $validLines[] = [
                        'item' => $item,
                        'qty' => $qty,
                        'unit_price' => $unitPrice,
                        'line_total' => $lineTotal,
                    ];
                }
            }

            if (!empty($validLines)) {
                $purchaseDate = $request->input('purchase_date', now()->toDateString());
                $paymentType = $request->input('payment_type', 'debt'); // 'full', 'debt', 'partial'
                $paidAmount = 0;

                if ($paymentType === 'full') {
                    $paidAmount = $totalCost;
                } elseif ($paymentType === 'partial') {
                    $paidAmount = min($totalCost, (float) str_replace(',', '', (string) $request->input('paid_amount', 0)));
                } else {
                    $paidAmount = 0;
                }

                $defaultWarehouse = Warehouse::where('is_default', true)->first() ?? Warehouse::first();

                // دروستکردنی پسوولەی کڕین
                $purchase = Purchase::create([
                    'invoice_no' => Purchase::nextInvoiceNo(),
                    'supplier_id' => $supplier->id,
                    'warehouse_id' => $defaultWarehouse?->id ?? 1,
                    'purchase_date' => $purchaseDate,
                    'currency' => 'IQD',
                    'subtotal' => $totalCost,
                    'discount_amount' => 0,
                    'total' => $totalCost,
                    'paid_amount' => $paidAmount,
                    'status' => 'confirmed',
                    'user_id' => auth()->id(),
                    'note' => 'کڕینی سەرەتایی لە کاتی تۆمارکردنی فرۆشیار',
                ]);

                foreach ($validLines as $vl) {
                    // دانانی کاڵا لەناو پسوولە
                    PurchaseItem::create([
                        'purchase_id' => $purchase->id,
                        'item_id' => $vl['item']->id,
                        'qty' => $vl['qty'],
                        'unit_price' => $vl['unit_price'],
                        'line_total' => $vl['line_total'],
                    ]);

                    // جوڵەی کۆگا (چوونی کاڵا بۆ ناو کۆگا)
                    if ($defaultWarehouse) {
                        app(\App\Services\StockService::class)->record(
                            itemId: $vl['item']->id,
                            warehouseId: $defaultWarehouse->id,
                            direction: 'in',
                            qty: $vl['qty'],
                            reason: 'purchase',
                            extra: [
                                'unit_cost' => $vl['unit_price'],
                                'currency' => 'IQD',
                                'reference_type' => Purchase::class,
                                'reference_id' => $purchase->id,
                                'moved_at' => $purchaseDate,
                                'note' => 'کڕین لە پسوولەی '.$purchase->invoice_no,
                            ]
                        );
                    }
                }

                // تۆمارکردنی پارەدان ئەگەر هەبوو
                if ($paidAmount > 0) {
                    app(\App\Services\PaymentService::class)->record([
                        'direction' => 'out',
                        'amount' => $paidAmount,
                        'currency' => 'IQD',
                        'paid_at' => $purchaseDate,
                        'party' => $supplier,
                        'purchase_id' => $purchase->id,
                        'category' => 'supplier_payment',
                        'note' => 'پارەدان لەگەڵ پسوولەی کڕینی '.$purchase->invoice_no,
                    ]);
                }
            }

            return $supplier;
        });

        return redirect()->route('suppliers.show', $supplier)->with('ok', 'فرۆشیار و زانیاری کڕین زیادکرا.');
    }

    /** پرۆفایلی فرۆشیار — دەفتەری حیسابات، کڕینەکان، پارەدان و قەرز. */
    public function show(Supplier $supplier): View
    {
        $purchases = $supplier->purchases()
            ->with(['items.item.unit', 'warehouse'])
            ->latest('purchase_date')
            ->latest('id')
            ->get();

        $payments = $supplier->payments()
            ->where('direction', 'out')
            ->with('user')
            ->latest('paid_at')
            ->latest('id')
            ->get();

        $jobs = $supplier->externalJobs()
            ->latest('id')
            ->get();

        // دروستکردنی کەشف حیساب (دەفتەری حیسابات بە ڕیزبەندی کات)
        $entries = collect();

        if ($supplier->opening_balance != 0) {
            $entries->push((object)[
                'date' => $supplier->created_at?->toDateString() ?? now()->toDateString(),
                'type' => 'opening',
                'title' => 'باڵانسی سەرەتایی',
                'details' => 'باڵانسی تۆمارکراوی سەرەتا',
                'amount_due' => $supplier->openingIqd(),
                'amount_paid' => 0,
                'currency' => $supplier->opening_currency,
                'raw_amount' => $supplier->opening_balance,
                'reference' => null,
            ]);
        }

        foreach ($purchases as $p) {
            $itemNames = $p->items->map(fn($i) => ($i->item?->name ?? 'کاڵا') . ' (' . fmt_qty($i->qty) . ' ' . ($i->item?->unit?->name ?? '') . ')')->join('، ');
            $entries->push((object)[
                'date' => $p->purchase_date?->toDateString(),
                'type' => 'purchase',
                'title' => 'پسوولەی کڕین ' . $p->invoice_no,
                'details' => $itemNames ?: ($p->note ?: 'کڕینی مەواد'),
                'amount_due' => $p->total_iqd,
                'amount_paid' => 0,
                'currency' => $p->currency,
                'raw_amount' => $p->total,
                'reference' => route('purchases.show', $p),
            ]);
        }

        foreach ($jobs as $j) {
            $entries->push((object)[
                'date' => $j->created_at?->toDateString(),
                'type' => 'job',
                'title' => 'ئیشی دەرەکی ' . $j->job_no,
                'details' => $j->title,
                'amount_due' => $j->cost_iqd,
                'amount_paid' => 0,
                'currency' => $j->currency,
                'raw_amount' => $j->cost,
                'reference' => null,
            ]);
        }

        foreach ($payments as $pay) {
            $entries->push((object)[
                'date' => $pay->paid_at?->toDateString(),
                'type' => 'payment',
                'title' => 'پارەدان (حەقدی) ' . $pay->voucher_no,
                'details' => $pay->note ?: 'پارەدانی کاش',
                'amount_due' => 0,
                'amount_paid' => (float)$pay->amount_iqd,
                'currency' => $pay->currency,
                'raw_amount' => $pay->amount,
                'reference' => route('payments.print', $pay),
            ]);
        }

        // ڕیزبەندی بەپێی بەروار بۆ حیسابکردنی باڵانسی بەردەوام
        $sortedEntries = $entries->sortBy('date')->values();
        $runningBalance = 0;
        $ledger = $sortedEntries->map(function ($entry) use (&$runningBalance) {
            $runningBalance += ($entry->amount_due - $entry->amount_paid);
            $entry->running_balance = $runningBalance;
            return $entry;
        })->reverse(); // نیشاندانی نوێترینەکان لە سەرەوە

        return view('suppliers.show', [
            'supplier' => $supplier,
            'purchases' => $purchases,
            'payments' => $payments,
            'jobs' => $jobs,
            'ledger' => $ledger,
            'totalPurchases' => $supplier->totalPurchases(),
            'totalPaid' => $supplier->totalPaid(),
            'currentBalance' => $supplier->balance(),
        ]);
    }

    public function edit(Supplier $supplier): View
    {
        return view('suppliers.form', [
            'supplier' => $supplier,
            'items' => Item::active()->with('unit')->orderBy('name')->get(),
            'warehouses' => Warehouse::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Supplier $supplier)
    {
        $supplier->update($this->validated($request));

        return redirect()->route('suppliers.show', $supplier)->with('ok', 'نوێکرایەوە.');
    }

    public function destroy(Supplier $supplier)
    {
        if ($supplier->purchases()->exists()) {
            return back()->with('err', 'ناتوانرێت بسڕدرێتەوە — ئەم فرۆشیارە پسوولەی کڕینی هەیە.');
        }

        $supplier->delete();

        return redirect()->route('suppliers.index')->with('ok', 'سڕدرایەوە.');
    }

    private function validated(Request $request): array
    {
        if ($request->has('opening_balance')) {
            $request->merge([
                'opening_balance' => str_replace(',', '', (string) $request->input('opening_balance')),
            ]);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:255'],
            'opening_balance' => ['nullable', 'numeric'],
            'opening_currency' => ['required', 'in:IQD,USD'],
            'note' => ['nullable', 'string'],
        ], [], [
            'name' => 'ناوی فرۆشیار',
            'opening_balance' => 'قەرزی پێشوو',
        ]);

        $data['opening_balance'] = $data['opening_balance'] ?? 0;
        $data['is_active'] = $request->boolean('is_active', true);

        return $data;
    }
}
