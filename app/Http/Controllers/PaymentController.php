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
            ->search($request->string('q')->toString())
            ->when($request->string('direction')->toString(), fn ($q, $d) => $q->where('direction', $d))
            ->when($request->date('from'), fn ($q, $d) => $q->whereDate('paid_at', '>=', $d))
            ->when($request->date('to'), fn ($q, $d) => $q->whereDate('paid_at', '<=', $d))
            ->latest('paid_at')
            ->latest('id')
            ->paginate(30)
            ->withQueryString();

        return view('payments.index', [
            'payments' => $payments,
            'totalIn' => (float) Payment::where('direction', 'in')
                ->when($request->date('from'), fn ($q, $d) => $q->whereDate('paid_at', '>=', $d))
                ->when($request->date('to'), fn ($q, $d) => $q->whereDate('paid_at', '<=', $d))
                ->sum('amount_iqd'),
            'totalOut' => (float) Payment::where('direction', 'out')
                ->when($request->date('from'), fn ($q, $d) => $q->whereDate('paid_at', '>=', $d))
                ->when($request->date('to'), fn ($q, $d) => $q->whereDate('paid_at', '<=', $d))
                ->sum('amount_iqd'),
        ]);
    }

    public function create(Request $request): View
    {
        $direction = $request->string('type')->toString() === 'out' ? 'out' : 'in';

        return view('payments.form', [
            'direction' => $direction,
            'customers' => Customer::active()->orderBy('name')->get(['id', 'name', 'phone']),
            'suppliers' => Supplier::active()->orderBy('name')->get(['id', 'name']),
            'employees' => Employee::active()->orderBy('name')->get(['id', 'name']),
            'orders' => Order::where('status', '!=', 'cancelled')->latest('id')->limit(150)->get(['id', 'invoice_no', 'customer_id', 'total', 'currency', 'order_date']),
            'purchases' => Purchase::where('status', '!=', 'cancelled')->latest('id')->limit(150)->get(['id', 'invoice_no', 'supplier_id', 'total', 'currency', 'purchase_date']),
            'cashBoxes' => CashBox::where('is_active', true)->get(),
            'rate' => ExchangeRate::current(),
            'selected' => [
                'customer' => $request->integer('customer') ?: null,
                'supplier' => $request->integer('supplier') ?: null,
                'order' => $request->integer('order') ?: null,
                'purchase' => $request->integer('purchase') ?: null,
            ],
            'order' => $request->integer('order') ? Order::find($request->integer('order')) : null,
            'purchase' => $request->integer('purchase') ? Purchase::find($request->integer('purchase')) : null,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'direction' => ['required', 'in:in,out'],
            'party_kind' => ['required', 'in:customer,supplier,employee,other'],
            'party_id' => ['nullable', 'integer'],
            'party_name' => ['nullable', 'string', 'max:255'],
            'order_id' => ['nullable', 'exists:orders,id'],
            'purchase_id' => ['nullable', 'exists:purchases,id'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'currency' => ['required', 'in:IQD,USD'],
            'cash_box_id' => ['nullable', 'exists:cash_boxes,id'],
            'paid_at' => ['required', 'date'],
            'note' => ['nullable', 'string'],
        ], [
            'amount.gt' => 'بڕ دەبێت لە سفر زیاتر بێت.',
        ]);

        $party = match ($data['party_kind']) {
            'customer' => Customer::find($data['party_id']),
            'supplier' => Supplier::find($data['party_id']),
            'employee' => Employee::find($data['party_id']),
            default => null,
        };

        if ($data['party_kind'] !== 'other' && ! $party) {
            return back()->withInput()->with('err', 'لایەنی مامەڵە هەڵبژێرە.');
        }

        $payment = $this->payments->record([
            'direction' => $data['direction'],
            'amount' => $data['amount'],
            'currency' => $data['currency'],
            'paid_at' => $data['paid_at'],
            'party' => $party,
            'party_name' => $data['party_name'] ?? null,
            'order_id' => $data['order_id'] ?? null,
            'purchase_id' => $data['purchase_id'] ?? null,
            'cash_box_id' => $data['cash_box_id'] ?? null,
            'category' => match ($data['party_kind']) {
                'customer' => 'customer_payment',
                'supplier' => 'supplier_payment',
                'employee' => 'wage',
                default => 'other',
            },
            'note' => $data['note'] ?? null,
        ]);

        return redirect()->route('payments.print', $payment)
            ->with('ok', "حەقدی {$payment->voucher_no} تۆمارکرا.");
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
