<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupplierController extends Controller
{
    public function index(Request $request): View
    {
        $suppliers = Supplier::query()
            ->search($request->string('q')->toString())
            ->withCount('purchases')
            ->orderBy('name')
            ->paginate(30)
            ->withQueryString();

        return view('suppliers.index', compact('suppliers'));
    }

    public function create(): View
    {
        return view('suppliers.form', ['supplier' => new Supplier(['opening_currency' => 'IQD', 'is_active' => true])]);
    }

    public function store(Request $request)
    {
        $supplier = Supplier::create($this->validated($request));

        return redirect()->route('suppliers.show', $supplier)->with('ok', 'فرۆشیار زیادکرا.');
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
        return view('suppliers.form', compact('supplier'));
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
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'phone2' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:255'],
            'opening_balance' => ['nullable', 'numeric'],
            'opening_currency' => ['required', 'in:IQD,USD'],
            'note' => ['nullable', 'string'],
        ], [], ['name' => 'ناو']);

        $data['opening_balance'] = $data['opening_balance'] ?? 0;
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
