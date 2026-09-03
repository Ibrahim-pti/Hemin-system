<?php

namespace App\Http\Controllers;

use App\Models\CashBox;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\ExchangeRate;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Purchase;
use App\Models\Setting;
use App\Models\Supplier;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function __construct(private readonly PaymentService $payments) {}

    public function index(Request $request): View
    {
        $payments = Payment::query()
            ->with(['party', 'order', 'cashBox', 'user'])
            ->where('direction', 'in')
            ->search($request->string('q')->toString())
            ->when($request->integer('customer_id'), fn ($q, $c) => $q->where('party_type', Customer::class)->where('party_id', $c))
            ->when($request->date('from'), fn ($q, $d) => $q->whereDate('paid_at', '>=', $d))
            ->when($request->date('to'), fn ($q, $d) => $q->whereDate('paid_at', '<=', $d))
            ->latest('paid_at')
            ->latest('id')
            ->paginate(30)
            ->withQueryString();

        $allCustomers = Customer::active()->get();
        $totalDebt = (float) $allCustomers->sum(fn ($c) => max(0, $c->balance()));

        $totalIn = (float) Payment::where('direction', 'in')
            ->when($request->date('from'), fn ($q, $d) => $q->whereDate('paid_at', '>=', $d))
            ->when($request->date('to'), fn ($q, $d) => $q->whereDate('paid_at', '<=', $d))
            ->sum('amount_iqd');

        $totalCount = Payment::where('direction', 'in')
            ->when($request->date('from'), fn ($q, $d) => $q->whereDate('paid_at', '>=', $d))
            ->when($request->date('to'), fn ($q, $d) => $q->whereDate('paid_at', '<=', $d))
            ->count();

        return view('payments.index', [
            'payments' => $payments,
            'totalIn' => $totalIn,
            'totalCount' => $totalCount,
            'totalDebt' => $totalDebt,
            'customers' => $allCustomers,
        ]);
    }

    public function create(Request $request): View
    {
        $selectedCustomerId = $request->integer('customer') ?: null;
        $selectedOrderId = $request->integer('order') ?: null;

        $order = $selectedOrderId ? Order::with('customer')->find($selectedOrderId) : null;
        if ($order && !$selectedCustomerId) {
            $selectedCustomerId = $order->customer_id;
        }

        $customers = Customer::active()->orderBy('name')->get()->map(function ($c) {
            return [
                'id' => $c->id,
                'name' => $c->name,
                'phone' => $c->phone,
                'balance' => $c->balance(),
            ];
        });

        $orders = Order::where('status', '!=', 'cancelled')
            ->whereNotIn('status', ['draft'])
            ->with(['payments'])
            ->latest('order_date')
            ->latest('id')
            ->limit(200)
            ->get()
            ->map(fn ($o) => [
                'id' => $o->id,
                'invoice_no' => $o->invoice_no,
                'customer_id' => $o->customer_id,
                'total' => (float) $o->total,
                'remaining' => max(0, (float) $o->remaining()),
                'currency' => $o->currency,
                'order_date' => $o->order_date ? $o->order_date->format('Y-m-d') : '',
            ]);

        return view('payments.form', [
            'direction' => 'in',
            'customers' => $customers,
            'orders' => $orders,
            'cashBoxes' => CashBox::where('is_active', true)->get(),
            'rate' => ExchangeRate::current(),
            'selectedCustomer' => $selectedCustomerId,
            'selectedOrder' => $selectedOrderId,
            'order' => $order,
        ]);
    }

    public function store(Request $request)
    {
        // پاککردنەوەی فاریزە (کۆما) لە بڕی پارە و نرخی ئاڵوگۆڕ
        if ($request->filled('amount')) {
            $request->merge([
                'amount' => (float) str_replace(',', '', (string) $request->input('amount')),
            ]);
        }
        if ($request->filled('exchange_rate')) {
            $request->merge([
                'exchange_rate' => (float) str_replace(',', '', (string) $request->input('exchange_rate')),
            ]);
        }

        $data = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'order_id' => ['nullable', 'exists:orders,id'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'currency' => ['required', 'in:IQD,USD'],
            'exchange_rate' => ['nullable', 'numeric', 'min:0'],
            'cash_box_id' => ['nullable', 'exists:cash_boxes,id'],
            'paid_at' => ['required', 'date'],
            'note' => ['nullable', 'string', 'max:255'],
        ], [
            'customer_id.required' => 'کڕیار هەڵبژێرە.',
            'customer_id.exists' => 'کڕیاری دیاریکراو نەدۆزرایەوە.',
            'amount.required' => 'بڕی پارە بنووسە.',
            'amount.gt' => 'بڕ دەبێت لە ٠ زیاتر بێت.',
        ]);

        $customer = Customer::findOrFail($data['customer_id']);

        $exchangeRate = null;
        if ($data['currency'] === 'USD' && !empty($data['exchange_rate'])) {
            $rawRate = (float) str_replace(',', '', (string) $data['exchange_rate']);
            $exchangeRate = $rawRate > 5000 ? $rawRate / 100 : $rawRate;
        }

        $payment = $this->payments->record([
            'direction' => 'in',
            'amount' => $data['amount'],
            'currency' => $data['currency'],
            'exchange_rate' => $exchangeRate,
            'paid_at' => $data['paid_at'],
            'party' => $customer,
            'party_name' => $customer->name,
            'order_id' => $data['order_id'] ?? null,
            'cash_box_id' => $data['cash_box_id'] ?? null,
            'category' => 'customer_payment',
            'note' => $data['note'] ?? null,
        ]);

        return redirect()->route('payments.print', $payment)
            ->with('ok', "سەنەدی حەقدی ژمارە {$payment->voucher_no} بۆ {$customer->name} تۆمارکرا.");
    }

    public function show(Payment $payment): View
    {
        return $this->print($payment);
    }

    /** چاپی حەقدی. */
    public function print(Payment $payment): View
    {
        $payment->load(['party', 'order', 'cashBox', 'user']);

        // باڵانسی ماوەی ئەو لایەنە دوای ئەم حەقدییە.
        $balance = match (true) {
            $payment->party instanceof Customer => $payment->party->balance(),
            $payment->party instanceof Supplier => $payment->party->balance(),
            default => null,
        };

        return view('payments.print', [
            'payment' => $payment,
            'settings' => Setting::all_(),
            'balance' => $balance,
        ]);
    }

    public function destroy(Payment $payment)
    {
        $this->payments->remove($payment);

        return redirect()->route('payments.index')->with('ok', 'حەقدییەکە سڕدرایەوە.');
    }
}
