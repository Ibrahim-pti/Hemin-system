<?php

namespace App\Http\Controllers;

use App\Models\CashBox;
use App\Models\ExchangeRate;
use App\Models\Item;
use App\Models\Payment;
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
        $activeTab = $request->string('tab', 'invoices')->toString();
        $currency = $request->string('currency', 'all')->toString();
        $currentRate = ExchangeRate::current() ?: 1500;

        $query = Purchase::query()
            ->with(['supplier', 'warehouse', 'items.item.unit'])
            ->search($request->string('q')->toString())
            ->when($request->filled('supplier_id'), fn ($q) => $q->where('supplier_id', $request->input('supplier_id')))
            ->when($request->string('status')->toString(), fn ($q, $s) => $q->where('status', $s))
            ->when(in_array($currency, ['USD', 'IQD']), fn ($q) => $q->where('currency', $currency))
            ->when($request->string('payment_status')->toString() === 'debt', fn ($q) => $q->where('status', 'confirmed')->whereRaw('total > paid_amount'))
            ->when($request->string('payment_status')->toString() === 'paid', fn ($q) => $q->where('status', 'confirmed')->whereRaw('total <= paid_amount'))
            ->when($request->date('from'), fn ($q, $d) => $q->whereDate('purchase_date', '>=', $d))
            ->when($request->date('to'), fn ($q, $d) => $q->whereDate('purchase_date', '<=', $d))
            ->latest('purchase_date')
            ->latest('id');

        $totalPurchasesCount = (int) Purchase::whereNotIn('status', ['draft', 'cancelled'])->count();
        $usdPurchasesCount = (int) Purchase::whereNotIn('status', ['draft', 'cancelled'])->where('currency', 'USD')->count();
        $iqdPurchasesCount = (int) Purchase::whereNotIn('status', ['draft', 'cancelled'])->where('currency', 'IQD')->count();

        $totalPurchasesIqd = (float) Purchase::where('status', 'confirmed')->where('currency', 'IQD')->sum('total');
        $totalPurchasesUsd = (float) Purchase::where('status', 'confirmed')->where('currency', 'USD')->sum('total');
        $totalPurchasesAllInUsd = $totalPurchasesUsd;

        $totalPaidIqd = (float) Payment::where('direction', 'out')->where('currency', 'IQD')->sum('amount');
        $totalPaidUsd = (float) Payment::where('direction', 'out')->where('currency', 'USD')->sum('amount');
        $totalPaidAllInUsd = $totalPaidUsd;

        $totalRemainingDebt = max(0, $totalPurchasesIqd - $totalPaidIqd);
        $draftCount = Purchase::where('status', 'draft')->count();

        // پوختەی کۆمپانیا و فرۆشیارەکان و قەرزەکانیان
        $allSuppliers = Supplier::active()
            ->when($request->input('tab') === 'suppliers' && $request->filled('q'), fn ($q) => $q->search($request->string('q')->toString()))
            ->withCount(['purchases' => fn ($q) => $q->where('status', 'confirmed')])
            ->get();

        $suppliersSummary = $allSuppliers->map(function ($supplier) {
            $pTotals = $supplier->totalPurchases();
            $paidTotals = $supplier->totalPaid();
            $bTotals = $supplier->balances();

            $totalPurchasesIqd = (float) ($pTotals['IQD'] ?? 0);
            $totalPurchasesUsd = (float) ($pTotals['USD'] ?? 0);
            $totalPaidIqd = (float) ($paidTotals['IQD'] ?? 0);
            $totalPaidUsd = (float) ($paidTotals['USD'] ?? 0);
            $balanceIqd = (float) ($bTotals['IQD'] ?? 0);
            $balanceUsd = (float) ($bTotals['USD'] ?? 0);
            $lastPurchase = $supplier->purchases()->latest('purchase_date')->first();

            return (object) [
                'id' => $supplier->id,
                'name' => $supplier->name,
                'phone' => $supplier->phone,
                'address' => $supplier->address,
                'purchases_count' => $supplier->purchases_count,
                'total_purchases' => $totalPurchasesIqd,
                'total_purchases_iqd' => $totalPurchasesIqd,
                'total_purchases_usd' => $totalPurchasesUsd,
                'total_paid' => $totalPaidIqd,
                'total_paid_iqd' => $totalPaidIqd,
                'total_paid_usd' => $totalPaidUsd,
                'balance' => $balanceIqd,
                'balance_iqd' => $balanceIqd,
                'balance_usd' => $balanceUsd,
                'has_debt' => $supplier->hasDebt(),
                'last_purchase_date' => $lastPurchase?->purchase_date,
            ];
        })->sortByDesc(fn ($s) => max($s->balance_iqd, $s->balance_usd))->values();

        $totalSuppliersCount = $allSuppliers->count();
        $totalSuppliersWithDebtCount = $suppliersSummary->where('has_debt', true)->count();
        $totalCompanyDebtIqd = (float) $suppliersSummary->sum(fn ($s) => max(0, $s->balance_iqd));
        $totalCompanyDebtUsd = (float) $suppliersSummary->sum(fn ($s) => max(0, $s->balance_usd));
        $totalCompanyDebt = $totalCompanyDebtIqd;

        $suppliersList = Supplier::active()->orderBy('name')->get();
        $purchases = (clone $query)->paginate(15)->withQueryString();
        $cashBoxes = CashBox::where('is_active', true)->get();

        // پارەدانەکانی فرۆشیارەکان (direction=out) بۆ تابی کەشف حیساب
        $supplierPayments = Payment::where('direction', 'out')
            ->with(['purchase', 'cashBox', 'user'])
            ->when(
                $request->input('tab') === 'suppliers' && $request->filled('pq'),
                fn ($q) => $q->where(fn ($w) => $w
                    ->where('party_name', 'like', '%' . $request->input('pq') . '%')
                    ->orWhere('voucher_no', 'like', '%' . $request->input('pq') . '%')
                    ->orWhere('note', 'like', '%' . $request->input('pq') . '%')
                )
            )
            ->when($request->filled('pay_supplier_id'), fn ($q) => $q->where('party_type', Supplier::class)->where('party_id', $request->input('pay_supplier_id')))
            ->orderByDesc('paid_at')
            ->orderByDesc('id')
            ->paginate(20, ['*'], 'ppage')
            ->withQueryString();

        $totalSupplierPaymentsIqd = Payment::where('direction', 'out')->sum('amount_iqd');
        $totalSupplierPaymentsCount = Payment::where('direction', 'out')->count();

        return view('purchases.index', compact(
            'purchases',
            'activeTab',
            'currency',
            'currentRate',
            'totalPurchasesCount',
            'usdPurchasesCount',
            'iqdPurchasesCount',
            'totalPurchasesIqd',
            'totalPurchasesUsd',
            'totalPurchasesAllInUsd',
            'totalPaidIqd',
            'totalPaidUsd',
            'totalPaidAllInUsd',
            'totalRemainingDebt',
            'totalCompanyDebtIqd',
            'totalCompanyDebtUsd',
            'draftCount',
            'suppliersSummary',
            'totalSuppliersCount',
            'totalSuppliersWithDebtCount',
            'totalCompanyDebt',
            'suppliersList',
            'cashBoxes',
            'supplierPayments',
            'totalSupplierPaymentsIqd',
            'totalSupplierPaymentsCount'
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

        $storedFiles = [];
        $fileInputs = ['attachments', 'image', 'image_camera', 'pdf_file'];
        foreach ($fileInputs as $inputKey) {
            if ($request->hasFile($inputKey)) {
                $rawFiles = $request->file($inputKey);
                $fileList = is_array($rawFiles) ? $rawFiles : [$rawFiles];
                foreach ($fileList as $f) {
                    if ($f && $f->isValid()) {
                        $storedFiles[] = $f->store('purchases', 'public');
                    }
                }
            }
        }

        $storedFiles = array_values(array_unique(array_filter($storedFiles)));
        $primaryImage = !empty($storedFiles) ? $storedFiles[0] : null;

        $purchase = DB::transaction(function () use ($data, $request, $primaryImage, $storedFiles) {
            $headerData = $this->header($data);

            $purchase = Purchase::create($headerData + [
                'invoice_no' => Purchase::nextInvoiceNo(),
                'status' => 'draft',
                'user_id' => auth()->id(),
                'image' => $primaryImage,
                'attachments' => !empty($storedFiles) ? $storedFiles : null,
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
                    'exchange_rate' => $headerData['exchange_rate'] ?? null,
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
        $purchase->load(['supplier', 'warehouse', 'items.item.unit', 'payments.user', 'user']);
        $cashBoxes = CashBox::where('is_active', true)->get();

        return view('purchases.show', compact('purchase', 'cashBoxes'));
    }

    /**
     * دانەوەی قەرزی پسوولەی کڕین (پارەدان بە فرۆشیار).
     */
    public function storePayment(Request $request, Purchase $purchase)
    {
        if ($request->filled('amount')) {
            $request->merge([
                'amount' => (float) str_replace(',', '', (string) $request->input('amount')),
            ]);
        }

        $data = $request->validate([
            'amount'        => ['required', 'numeric', 'gt:0'],
            'currency'      => ['nullable', 'in:IQD,USD'],
            'exchange_rate' => ['nullable', 'numeric', 'gt:0'],
            'cash_box_id'   => ['nullable', 'exists:cash_boxes,id'],
            'paid_at'       => ['required', 'date'],
            'note'          => ['nullable', 'string', 'max:255'],
        ], [
            'amount.required' => 'تکایە بڕی پارە بنووسە.',
            'amount.gt'       => 'بڕی پارە دەبێت لە سفر زیاتر بێت.',
            'paid_at.required'=> 'بەرواری پارەدان دیاری بکە.',
        ]);

        $amount       = (float) $data['amount'];
        $currency     = $purchase->currency;
        $exchangeRate = !empty($data['exchange_rate']) ? (float) $data['exchange_rate'] : null;

        // قەرزی ماوەی پسوولە هەمیشە بە دراوی پسوولەکەوەیە
        $remaining = (float) $purchase->remaining();

        if ($amount > ($remaining + 0.01)) {
            return back()->with('err', 'بڕی پارەی دراو ناتوانێت لە قەرزی ماوەی پسوولەکە زیاتر بێت (ماوە: ' . fmt_money($remaining, $purchase->currency) . ').');
        }

        DB::transaction(function () use ($purchase, $data, $amount, $currency, $exchangeRate) {
            $this->payments->record([
                'direction'     => 'out',
                'party'         => $purchase->supplier,
                'party_name'    => $purchase->supplier?->name,
                'purchase_id'   => $purchase->id,
                'amount'        => $amount,
                'currency'      => $currency,
                'exchange_rate' => $currency === 'USD' ? $exchangeRate : null,
                'cash_box_id'   => $data['cash_box_id'] ?? null,
                'paid_at'       => $data['paid_at'],
                'category'      => 'supplier_payment',
                'note'          => $data['note'] ?: 'پارەدانی قەرزی پسوولەی کڕینی #' . $purchase->invoice_no,
            ]);

            $purchase->paid_amount = $purchase->paidTotal();
            $purchase->save();
        });

        return back()->with('ok', 'پارەدان بە سەرکەوتوویی تۆمارکرا و لە قەرزی پسوولەکە و فرۆشیارەکە کەمکرایەوە.');
    }

    /**
     * تۆمارکردنی پارەدان بە کۆمپانیا / فرۆشیار لە تابی کۆمپانیا و کەشف حیساب.
     */
    public function storeSupplierPayment(Request $request, Supplier $supplier)
    {
        if ($request->filled('amount')) {
            $request->merge([
                'amount' => (float) str_replace(',', '', (string) $request->input('amount')),
            ]);
        }

        $data = $request->validate([
            'amount'        => ['required', 'numeric', 'gt:0'],
            'currency'      => ['required', 'in:IQD,USD'],
            'exchange_rate' => ['nullable', 'numeric', 'gt:0'],
            'cash_box_id'   => ['nullable', 'exists:cash_boxes,id'],
            'paid_at'       => ['required', 'date'],
            'note'          => ['nullable', 'string', 'max:255'],
        ], [
            'amount.required'  => 'تکایە بڕی پارە بنووسە.',
            'amount.gt'        => 'بڕی پارە دەبێت لە سفر زیاتر بێت.',
            'paid_at.required' => 'بەرواری پارەدان دیاری بکە.',
        ]);

        $amount       = (float) $data['amount'];
        $currency     = $data['currency'];
        $exchangeRate = !empty($data['exchange_rate']) ? (float) $data['exchange_rate'] : null;

        $balances = $supplier->balances();
        $balanceInCurrency = (float) ($balances[$currency] ?? 0);
        if ($balanceInCurrency <= 0) {
            return back()->with('err', 'ئەم فرۆشیارە هیچ قەرزێکی ماوەی بە ' . $currency . ' نییە.');
        }

        if ($amount > ($balanceInCurrency + 0.01)) {
            return back()->with('err', 'بڕی پارەی دراو ناتوانێت لە تەواوی قەرزی فرۆشیارەکە بە ' . $currency . ' زیاتر بێت (ماوە: ' . fmt_money($balanceInCurrency, $currency) . ').');
        }

        DB::transaction(function () use ($supplier, $data, $amount, $currency, $exchangeRate) {
            // دەرهێنانی پسوولە پەسەندکراوە ماوەکانی ئەم فرۆشیارە بە هەمان دراو
            $unpaidPurchases = $supplier->purchases()
                ->where('status', 'confirmed')
                ->where('currency', $currency)
                ->orderBy('purchase_date')
                ->orderBy('id')
                ->get()
                ->filter(fn ($p) => $p->remaining() > 0.001);

            $remainingToPay = $amount;

            foreach ($unpaidPurchases as $purchase) {
                if ($remainingToPay <= 0.001) {
                    break;
                }

                $pRem = (float) $purchase->remaining();
                $payForThis = min($remainingToPay, $pRem);
                $remainingToPay -= $payForThis;

                if ($payForThis > 0.001) {
                    $this->payments->record([
                        'direction'     => 'out',
                        'party'         => $supplier,
                        'party_name'    => $supplier->name,
                        'purchase_id'   => $purchase->id,
                        'amount'        => $payForThis,
                        'currency'      => $currency,
                        'exchange_rate' => $currency === 'USD' ? $exchangeRate : null,
                        'cash_box_id'   => $data['cash_box_id'] ?? null,
                        'paid_at'       => $data['paid_at'],
                        'category'      => 'supplier_payment',
                        'note'          => $data['note'] ?: ('پارەدانی قەرزی پسوولەی #' . $purchase->invoice_no),
                    ]);

                    $purchase->paid_amount = $purchase->paidTotal();
                    $purchase->save();
                }
            }

            // ئەگەر بڕێک مابێتەوە کە لەسەر پسوولەی دیاریکراو نەبێت (وەک قەرزی سەرەتایی یان ئیشی دەرەکی)
            if ($remainingToPay > 0.001) {
                $this->payments->record([
                    'direction'     => 'out',
                    'party'         => $supplier,
                    'party_name'    => $supplier->name,
                    'purchase_id'   => null,
                    'amount'        => $remainingToPay,
                    'currency'      => $currency,
                    'exchange_rate' => $currency === 'USD' ? $exchangeRate : null,
                    'cash_box_id'   => $data['cash_box_id'] ?? null,
                    'paid_at'       => $data['paid_at'],
                    'category'      => 'supplier_payment',
                    'note'          => $data['note'] ?: ('پارەدانی قەرزی فرۆشیار: ' . $supplier->name),
                ]);
            }
        });

        return redirect()->route('purchases.index', [
            'tab' => 'suppliers',
            'currency' => $request->input('currency_filter', 'all'),
        ])->with('ok', 'پارەدان بە بڕی ' . number_format($amount, 2) . ' ' . $currency . ' بە سەرکەوتوویی بۆ ' . $supplier->name . ' تۆمارکرا.');
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

        $existingAttachments = [];
        if ($request->has('existing_attachments')) {
            $existing = (array) $request->input('existing_attachments');
            $validCurrent = $purchase->allAttachments();
            $existingAttachments = array_values(array_intersect($existing, $validCurrent));
        } elseif (!$request->boolean('remove_image') && !$request->hasFile('attachments') && !$request->hasFile('image') && !$request->hasFile('image_camera') && !$request->hasFile('pdf_file')) {
            $existingAttachments = $purchase->allAttachments();
        }

        $storedFiles = $existingAttachments;

        $fileInputs = ['attachments', 'image', 'image_camera', 'pdf_file'];
        foreach ($fileInputs as $inputKey) {
            if ($request->hasFile($inputKey)) {
                $rawFiles = $request->file($inputKey);
                $fileList = is_array($rawFiles) ? $rawFiles : [$rawFiles];
                foreach ($fileList as $f) {
                    if ($f && $f->isValid()) {
                        $storedFiles[] = $f->store('purchases', 'public');
                    }
                }
            }
        }

        if ($request->boolean('remove_image')) {
            $storedFiles = [];
        }

        $storedFiles = array_values(array_unique(array_filter($storedFiles)));
        $primaryImage = !empty($storedFiles) ? $storedFiles[0] : null;

        DB::transaction(function () use ($purchase, $data, $request, $primaryImage, $storedFiles) {
            $purchase->update($this->header($data) + [
                'image' => $primaryImage,
                'attachments' => !empty($storedFiles) ? $storedFiles : null,
            ]);
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
        DB::transaction(function () use ($purchase) {
            // ئەگەر پەسەندکرابوو، جوڵەی کۆگاکان لە مەخزەن دەسڕدرێنەوە
            if ($purchase->status === 'confirmed') {
                $this->stock->unpostPurchase($purchase);
            }

            // سڕینەوەی هەموو ئەو پارەدانانەی بەستراونەتەوە بەم پسوولەیە و گەڕاندنەوەی حیساباتی قاسە
            foreach ($purchase->payments as $payment) {
                $this->payments->remove($payment);
            }

            // سڕینەوەی کاڵاکان و خودی پسوولەکە
            $purchase->items()->delete();
            $purchase->delete();
        });

        return redirect()->route('purchases.index')->with('ok', 'پسوولەی کڕین بە سەرکەوتوویی سڕایەوە.');
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
        if (isset($input['exchange_rate'])) {
            $input['exchange_rate'] = str_replace(',', '', (string) $input['exchange_rate']);
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
            'currency' => ['required', 'in:IQD,USD'],
            'exchange_rate' => ['nullable', 'numeric', 'min:0'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'paid_amount' => ['nullable', 'numeric', 'min:0'],
            'payment_type' => ['nullable', 'in:cash,debt,partial'],
            'attachments' => ['nullable'],
            'attachments.*' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,heic,heif,bmp,pdf', 'max:25600'],
            'image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,heic,heif,bmp,pdf', 'max:25600'],
            'image_camera' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,heic,heif,bmp,pdf', 'max:25600'],
            'pdf_file' => ['nullable', 'file', 'mimes:pdf', 'max:25600'],
            'existing_attachments' => ['nullable', 'array'],
            'existing_attachments.*' => ['nullable', 'string'],
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
            'attachments.*.mimes' => 'هەموو فایلەکان دەبێت وێنە یان PDF بن.',
            'attachments.*.max' => 'قەبارەی هیچ فایلێک لە ۲۵ مێگابایت زیاتر نەبێت.',
            'image.mimes' => 'فایلی وەسڵ دەبێت وێنە یان PDF بێت.',
            'pdf_file.mimes' => 'فایلەکە دەبێت PDF بێت.',
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

        $exchangeRate = null;
        if ($currency === 'USD') {
            if (!empty($data['exchange_rate'])) {
                $rawRate = (float) $data['exchange_rate'];
                $exchangeRate = $rawRate > 5000 ? $rawRate / 100 : $rawRate;
            } else {
                $exchangeRate = ExchangeRate::forDate($data['purchase_date']);
            }
        }

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
            'exchange_rate' => $exchangeRate,
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
