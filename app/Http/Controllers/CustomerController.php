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

        $currency = $request->string('currency', 'all')->toString();
        if (!in_array($currency, ['all', 'USD', 'IQD'], true)) {
            $currency = 'all';
        }

        $currentRate = \App\Models\ExchangeRate::current() ?: 1500;

        $allCustomers = Customer::orderBy('name')->get(['id', 'name', 'phone']);
        $totalCustomers = Customer::count();

        $totalSalesIqd = (float) \App\Models\Order::whereNotIn('status', ['draft', 'cancelled'])->sum(\App\Models\Order::totalIqdExpression());
        $totalSalesUsd = (float) \App\Models\Order::whereNotIn('status', ['draft', 'cancelled'])->where('currency', 'USD')->sum('total');
        $totalSalesAllInUsd = $currentRate > 0 ? round($totalSalesIqd / $currentRate, 2) : $totalSalesUsd;

        $totalDebtIqd = (float) Customer::all()->sum(fn ($c) => max(0, $c->balance()));
        $totalDebtUsd = $currentRate > 0 ? round($totalDebtIqd / $currentRate, 2) : 0;

        $debtorCount = Customer::all()->filter(fn ($c) => $c->balance() > 0)->count();

        $totalSales = $totalSalesIqd;
        $totalDebt = $totalDebtIqd;

        return view('customers.index', compact(
            'customers',
            'allCustomers',
            'currency',
            'currentRate',
            'totalCustomers',
            'totalSales',
            'totalSalesIqd',
            'totalSalesUsd',
            'totalSalesAllInUsd',
            'totalDebt',
            'totalDebtIqd',
            'totalDebtUsd',
            'debtorCount'
        ));
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
                'address' => $customer->address,
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

        $oldDebts = $customer->oldDebts;

        $ordersCount = $orders->count();
        $totalBought = (float) $orders->whereNotIn('status', ['draft', 'cancelled'])->sum(fn ($o) => $o->total_iqd);
        $balance = $customer->balance();

        return view('customers.show', compact('customer', 'orders', 'payments', 'oldDebts', 'ordersCount', 'totalBought', 'balance'));
    }

    /**
     * کەشف حساب — هەموو وەسڵ و حەقدییەکان بە ڕیزی بەروار،
     * لەگەڵ باڵانسی هەڵکشاو دوای هەر دێڕێک.
     */
    public function statementIndex(Request $request): View
    {
        $allCustomers = Customer::active()->orderBy('name')->get(['id', 'name', 'phone']);
        $customerId = $request->input('customer_id') ?: $request->input('customer');
        $customer = $customerId ? Customer::find($customerId) : null;

        if (! $customer) {
            return view('customers.statement', [
                'customer' => null,
                'allCustomers' => $allCustomers,
                'orders' => collect(),
                'payments' => collect(),
                'openingBalance' => 0,
                'totalPurchases' => 0,
                'totalPaid' => 0,
                'debtPayments' => 0,
                'remainingDebt' => 0,
                'from' => $request->date('from') ?? now()->startOfYear(),
                'to' => $request->date('to') ?? now(),
            ]);
        }

        return $this->buildStatementView($customer, $request, $allCustomers);
    }

    public function statement(Customer $customer, Request $request): View
    {
        $allCustomers = Customer::active()->orderBy('name')->get(['id', 'name', 'phone']);

        return $this->buildStatementView($customer, $request, $allCustomers);
    }

    private function buildStatementView(Customer $customer, Request $request, $allCustomers): View
    {
        $from = ($request->date('from') ?? now()->startOfYear())->startOfDay();
        $to = ($request->date('to') ?? now())->endOfDay();

        // وەسڵەکان بە وردەکاری شتەکان
        $orders = $customer->orders()
            ->with(['items', 'payments'])
            ->whereNotIn('status', ['draft', 'cancelled'])
            ->whereBetween('order_date', [$from, $to])
            ->orderBy('order_date', 'asc')
            ->get();

        // حەقدییەکان
        $payments = $customer->payments()
            ->where('direction', 'in')
            ->whereBetween('paid_at', [$from, $to])
            ->orderBy('paid_at', 'asc')
            ->get();

        // حیساباتی پێشتر و قەرزی کۆن لەم ماوەیەدا
        $oldDebts = $customer->oldDebts()
            ->whereBetween('date', [$from, $to])
            ->orderBy('date', 'asc')
            ->get();

        // باڵانسی سەرەتایی پێش بەرواری دیاریکراو
        $oldDebtsBefore = (float) $customer->oldDebts()
            ->whereDate('date', '<', $from)
            ->get()
            ->sum(fn ($d) => $d->remainingIqd());

        $baseOpening = $customer->opening_currency === 'USD'
            ? (float) $customer->opening_balance * (\App\Models\ExchangeRate::current() ?: 1500)
            : (float) $customer->opening_balance;

        $openingBalance = $baseOpening
            + $oldDebtsBefore
            + (float) $customer->orders()
                ->whereNotIn('status', ['draft', 'cancelled'])
                ->whereDate('order_date', '<', $from)
                ->sum(\App\Models\Order::totalIqdExpression())
            - (float) $customer->payments()
                ->where('direction', 'in')
                ->whereDate('paid_at', '<', $from)
                ->sum('amount_iqd');

        $totalOrdersAmount = (float) $orders->sum(fn ($o) => $o->total_iqd);
        $totalOldDebtsInPeriod = (float) $oldDebts->sum(fn ($d) => $d->totalIqd());
        $totalPurchases = $openingBalance + $totalOrdersAmount + $totalOldDebtsInPeriod;

        $totalPaidAmount = (float) $payments->sum('amount_iqd') + (float) $oldDebts->sum(fn ($d) => $d->paidIqd());
        $debtPayments = $totalPaidAmount;
        $remainingDebt = max(0, $totalPurchases - $totalPaidAmount);

        return view('customers.statement', [
            'customer' => $customer,
            'allCustomers' => $allCustomers,
            'orders' => $orders,
            'payments' => $payments,
            'oldDebts' => $oldDebts,
            'openingBalance' => $openingBalance,
            'totalOrdersAmount' => $totalOrdersAmount,
            'totalPurchases' => $totalPurchases,
            'totalPaid' => $totalPaidAmount,
            'debtPayments' => $debtPayments,
            'remainingDebt' => $remainingDebt,
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
        if ($request->has('opening_balance')) {
            $data['opening_balance'] = (float) str_replace(',', '', (string) $request->input('opening_balance', 0));
        }
        if ($request->has('opening_currency')) {
            $data['opening_currency'] = $request->input('opening_currency', 'IQD');
        }
        $data['is_active'] = $request->boolean('is_active', true);

        return $data;
    }
}
