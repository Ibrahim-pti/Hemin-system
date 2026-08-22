<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        $customers = Customer::query()
            ->search($request->string('q')->toString())
            ->withCount('orders')
            ->orderBy('name')
            ->paginate(30)
            ->withQueryString();

        $allCustomers = Customer::all();
        $totalCustomers = $allCustomers->count();
        $totalSales = (float) \App\Models\Order::whereNotIn('status', ['draft', 'cancelled'])->sum(\App\Models\Order::totalIqdExpression());
        $totalDebt = (float) $allCustomers->sum(fn ($c) => max(0, $c->balance()));
        $debtorCount = $allCustomers->filter(fn ($c) => $c->balance() > 0)->count();

        return view('customers.index', compact('customers', 'totalCustomers', 'totalSales', 'totalDebt', 'debtorCount'));
    }

    public function create(): View
    {
        return view('customers.form', ['customer' => new Customer(['opening_currency' => 'IQD', 'is_active' => true])]);
    }

    public function store(Request $request)
    {
        $customer = Customer::create($this->validated($request));

        return redirect()->route('customers.show', $customer)->with('ok', 'کڕیار زیادکرا.');
    }

    /** دروستکردنی خێرای کڕیار بەبێ بەجێهێشتنی فۆرمی وەسڵ (AJAX) */
    public function quickStore(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:255'],
        ], [], [
            'name' => 'ناوی کڕیار',
            'phone' => 'ژمارەی مۆبایل',
        ]);

        $data['opening_currency'] = 'IQD';
        $data['is_active'] = true;

        $customer = Customer::create($data);

        return response()->json([
            'ok' => true,
            'customer' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'phone' => $customer->phone,
                'discount_percent' => (float) $customer->discount_percent,
            ],
        ]);
    }

    public function show(Customer $customer): View
    {
        $orders = $customer->orders()
            ->with('items')
            ->latest('order_date')
            ->latest('id')
            ->get();

        $payments = $customer->payments()
            ->latest('paid_at')
            ->latest('id')
            ->get();

        $ordersCount = $orders->count();
        $totalBought = (float) $orders->whereNotIn('status', ['draft', 'cancelled'])->sum(fn ($o) => $o->total_iqd);
        $balance = $customer->balance();

        return view('customers.show', compact('customer', 'orders', 'payments', 'ordersCount', 'totalBought', 'balance'));
    }

    /**
     * کەشف حساب — هەموو وەسڵ و حەقدییەکان بە ڕیزی بەروار،
     * لەگەڵ باڵانسی هەڵکشاو دوای هەر دێڕێک.
     */
    public function statement(Customer $customer, Request $request): View
    {
        $from = ($request->date('from') ?? now()->startOfYear())->startOfDay();
        // تا کۆتایی ڕۆژ — ئەگەر نا مامەڵەکانی هەمان ڕۆژ دەرناکەون.
        $to = ($request->date('to') ?? now())->endOfDay();

        $orders = $customer->orders()
            ->whereNotIn('status', ['draft', 'cancelled'])
            ->whereBetween('order_date', [$from, $to])
            ->get()
            ->map(fn ($order) => [
                'date' => $order->order_date,
                'ref' => 'وەسڵی '.$order->invoice_no,
                'description' => 'فرۆشتن',
                'debit' => $order->total_iqd,   // لەسەری
                'credit' => 0.0,                // بۆی
                'link' => route('orders.show', $order),
            ]);

        $payments = $customer->payments()
            ->whereBetween('paid_at', [$from, $to])
            ->get()
            ->map(fn ($payment) => [
                'date' => $payment->paid_at,
                'ref' => 'حەقدی '.$payment->voucher_no,
                'description' => $payment->direction === 'in' ? 'پارەدان' : 'گەڕاندنەوەی پارە',
                'debit' => $payment->direction === 'in' ? 0.0 : (float) $payment->amount_iqd,
                'credit' => $payment->direction === 'in' ? (float) $payment->amount_iqd : 0.0,
                'link' => route('payments.print', $payment),
            ]);

        // باڵانسی پێش دەستپێکی ماوەکە.
        $openingBalance = $customer->openingIqd()
            + (float) $customer->orders()
                ->whereNotIn('status', ['draft', 'cancelled'])
                ->whereDate('order_date', '<', $from)
                ->sum(\App\Models\Order::totalIqdExpression())
            - (float) $customer->payments()
                ->where('direction', 'in')
                ->whereDate('paid_at', '<', $from)
                ->sum('amount_iqd');

        $running = $openingBalance;

        $rows = $orders->concat($payments)
            ->sortBy('date')
            ->values()
            ->map(function (array $row) use (&$running) {
                $running += $row['debit'] - $row['credit'];
                $row['balance'] = $running;

                return $row;
            });

        return view('customers.statement', [
            'customer' => $customer,
            'rows' => $rows,
            'openingBalance' => $openingBalance,
            'closingBalance' => $running,
            'from' => $from,
            'to' => $to,
        ]);
    }

    public function edit(Customer $customer): View
    {
        return view('customers.form', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $customer->update($this->validated($request));

        return redirect()->route('customers.show', $customer)->with('ok', 'نوێکرایەوە.');
    }

    public function destroy(Customer $customer)
    {
        if ($customer->orders()->exists()) {
            return back()->with('err', 'ناتوانرێت بسڕدرێتەوە — ئەم کڕیارە وەسڵی هەیە.');
        }

        $customer->delete();

        return redirect()->route('customers.index')->with('ok', 'سڕدرایەوە.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:255'],
            'note' => ['nullable', 'string'],
        ], [], [
            'name' => 'ناو',
            'phone' => 'تەلەفۆن',
            'address' => 'ناونیشان',
        ]);

        $data['phone2'] = null;
        $data['discount_percent'] = 0;
        $data['opening_balance'] = 0;
        $data['opening_currency'] = 'IQD';
        $data['is_active'] = $request->boolean('is_active', true);

        return $data;
    }
}
