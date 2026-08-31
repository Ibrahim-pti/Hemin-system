<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\ExchangeRate;
use App\Models\Item;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Setting;
use App\Models\Warehouse;
use App\Services\PaymentService;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(
        private readonly PaymentService $payments,
        private readonly StockService $stock,
    ) {}

    public function index(Request $request): View
    {
        $activeTab = $request->string('tab', 'customers')->toString();

        $orders = Order::query()
            ->with(['customer', 'items'])
            ->search($request->string('q')->toString())
            ->when($request->string('status')->toString(), fn ($q, $s) => $q->where('status', $s))
            ->when($request->date('from'), fn ($q, $d) => $q->whereDate('order_date', '>=', $d))
            ->when($request->date('to'), fn ($q, $d) => $q->whereDate('order_date', '<=', $d))
            ->latest('order_date')
            ->latest('id')
            ->paginate(25, ['*'], 'orders_page')
            ->withQueryString();

        $customers = Customer::query()
            ->search($request->string('q')->toString())
            ->withCount('orders')
            ->with(['orders' => fn ($q) => $q->latest('order_date')->limit(1)])
            ->orderBy('name')
            ->paginate(25, ['*'], 'customers_page')
            ->withQueryString();

        $allCustomers = Customer::all();
        $totalCustomers = $allCustomers->count();
        $totalOrders = (int) Order::whereNotIn('status', ['draft', 'cancelled'])->count();
        $totalSales = (float) Order::whereNotIn('status', ['draft', 'cancelled'])->sum(Order::totalIqdExpression());
        $totalReceived = (float) Payment::where('direction', 'in')->sum('amount_iqd');
        $totalDebt = (float) $allCustomers->sum(fn ($c) => max(0, $c->balance()));

        return view('orders.index', compact(
            'orders',
            'customers',
            'activeTab',
            'totalCustomers',
            'totalOrders',
            'totalSales',
            'totalReceived',
            'totalDebt'
        ));
    }

    public function create(Request $request): View
    {
        return view('orders.form', [
            'order' => new Order([
                'currency' => 'IQD',
                'order_date' => now()->toDateString(),
                'customer_id' => $request->integer('customer') ?: null,
            ]),
            'customers' => Customer::active()->orderBy('name')->get(),
            'items' => Item::active()->orderBy('name')->get(['id', 'name']),
            'nextNo' => Order::nextInvoiceNo(),
            'rate' => ExchangeRate::current(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $result = DB::transaction(function () use ($data, $request) {
            $customer = Customer::find($data['customer_id']);

            $order = Order::create($this->header($data, $customer) + [
                'invoice_no' => $data['invoice_no'] ?: Order::nextInvoiceNo(),
                'status' => $request->boolean('confirm') ? 'confirmed' : 'draft',
                'user_id' => auth()->id(),
            ]);

            $this->syncLines($order, $data['lines'], $request->file('lines', []));
            $payment = $this->recordPrepaid($order, $customer, (float) str_replace(',', '', (string) ($data['prepaid_amount'] ?? 0)));

            return ['order' => $order, 'payment' => $payment];
        });

        $order = $result['order'];
        $payment = $result['payment'];

        return redirect()->route('orders.show', $order)
            ->with('ok', "وەسڵی ژمارە {$order->invoice_no} تۆمارکرا.")
            ->with('just_created', true)
            ->with('payment_id', $payment?->id)
            ->with('has_prepaid', $payment !== null);
    }

    public function show(Order $order): View
    {
        $order->load(['customer', 'items.item', 'payments', 'externalJobs', 'user']);

        return view('orders.show', compact('order'));
    }

    /** چاپی وەسڵ — هەمان پێکهاتەی دەفتەرە چاپکراوەکەی کارگە. */
    public function print(Order $order): View
    {
        $order->load(['customer', 'items']);

        return view('orders.print', [
            'order' => $order,
            'settings' => Setting::all_(),
        ]);
    }

    public function edit(Order $order): View
    {
        if (in_array($order->status, ['delivered', 'cancelled'], true)) {
            abort(403, 'ئەم وەسڵە ناگۆڕدرێت.');
        }

        $order->load('items');

        return view('orders.form', [
            'order' => $order,
            'customers' => Customer::active()->orderBy('name')->get(),
            'items' => Item::active()->orderBy('name')->get(['id', 'name']),
            'nextNo' => $order->invoice_no,
            'rate' => ExchangeRate::current(),
        ]);
    }

    public function update(Request $request, Order $order)
    {
        if (in_array($order->status, ['delivered', 'cancelled'], true)) {
            return back()->with('err', 'ئەم وەسڵە ناگۆڕدرێت.');
        }

        $data = $this->validated($request, $order);

        DB::transaction(function () use ($order, $data, $request) {
            $order->update($this->header($data, Customer::find($data['customer_id'])));
            $order->items()->delete();
            $this->syncLines($order, $data['lines'], $request->file('lines', []));
        });

        return redirect()->route('orders.show', $order)->with('ok', 'وەسڵەکە نوێکرایەوە.');
    }

    /**
     * گۆڕینی دۆخ. کاتێک دەبێتە «گەیەنراوە»، ئەو دێڕانەی پەیوەستن بە کاڵایەکی
     * مەخزەنەوە لە مەخزەن کەم دەکرێنەوە.
     */
    public function setStatus(Request $request, Order $order)
    {
        $data = $request->validate([
            'status' => ['required', 'in:'.implode(',', array_keys(Order::STATUSES))],
        ]);

        DB::transaction(function () use ($order, $data) {
            $was = $order->status;
            $order->update(['status' => $data['status']]);

            if ($data['status'] === 'delivered' && $was !== 'delivered') {
                $this->releaseStock($order);
            }

            if ($was === 'delivered' && $data['status'] !== 'delivered') {
                $order->morphMany(\App\Models\StockMovement::class, 'reference')->delete();
            }
        });

        return back()->with('ok', 'دۆخ گۆڕدرا بۆ «'.Order::STATUSES[$data['status']].'».');
    }

    public function destroy(Order $order)
    {
        if ($order->payments()->exists()) {
            return back()->with('err', 'ناتوانرێت بسڕدرێتەوە — حەقدی بۆ ئەم وەسڵە تۆمارکراوە.');
        }

        DB::transaction(function () use ($order) {
            $order->morphMany(\App\Models\StockMovement::class, 'reference')->delete();
            $order->items()->delete();
            $order->delete();
        });

        return redirect()->route('orders.index')->with('ok', 'وەسڵەکە سڕدرایەوە.');
    }

    private function validated(Request $request, ?Order $order = null): array
    {
        if ($request->has('exchange_rate')) {
            $request->merge([
                'exchange_rate' => $request->filled('exchange_rate') ? str_replace(',', '', (string) $request->input('exchange_rate')) : null,
            ]);
        }
        if ($request->has('discount_amount')) {
            $request->merge([
                'discount_amount' => $request->filled('discount_amount') ? str_replace(',', '', (string) $request->input('discount_amount')) : 0,
            ]);
        }
        if ($request->has('discount_percent')) {
            $request->merge([
                'discount_percent' => $request->filled('discount_percent') ? str_replace(',', '', (string) $request->input('discount_percent')) : 0,
            ]);
        }

        $unique = 'unique:orders,invoice_no'.($order ? ",{$order->id}" : '');

        return $request->validate([
            'invoice_no' => ['nullable', 'string', 'max:30', $unique],
            'customer_id' => ['required', 'exists:customers,id'],
            'address_snapshot' => ['nullable', 'string', 'max:255'],
            'order_date' => ['required', 'date'],
            'delivery_date' => ['nullable', 'date', 'after_or_equal:order_date'],
            'currency' => ['required', 'in:IQD,USD'],
            'exchange_rate' => ['nullable', 'numeric', 'min:0'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'prepaid_amount' => ['nullable'],
            'note' => ['nullable', 'string'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.description' => ['required', 'string', 'max:255'],
            'lines.*.image' => ['nullable', 'image', 'max:5120'],
            'lines.*.existing_image' => ['nullable', 'string'],
            'lines.*.unit_price' => ['required'],
            'lines.*.note' => ['nullable', 'string', 'max:255'],
        ], [
            'lines.required' => 'لانیکەم یەک دێڕ زیاد بکە.',
            'invoice_no.unique' => 'ئەم ژمارە وەسڵە پێشتر بەکارهاتووە.',
            'delivery_date.after_or_equal' => 'بەرواری گەیاندن ناتوانێت پێش بەرواری وەسڵ بێت.',
        ], [
            'customer_id' => 'کڕیار',
            'address_snapshot' => 'ناونیشان',
            'lines.*.description' => 'ناوەڕۆک / ناوی شتەکە',
            'lines.*.image' => 'وێنە',
            'lines.*.unit_price' => 'نرخ',
            'discount_amount' => 'داشکاندن',
            'prepaid_amount' => 'پێشەکی',
        ]);
    }

    private function header(array $data, ?Customer $customer): array
    {
        $subtotal = collect($data['lines'])->sum(fn ($line) => $this->lineTotal($line));

        // داشکاندن بە بڕی پارە (دینار یان دۆلار)
        $discount = (float) str_replace(',', '', (string) ($data['discount_amount'] ?? 0));
        if ($discount <= 0 && !empty($data['discount_percent'])) {
            $percent = (float) $data['discount_percent'];
            $discount = $subtotal * $percent / 100;
        } else {
            $percent = $subtotal > 0 && $discount > 0 ? round(($discount / $subtotal) * 100, 2) : 0;
        }

        $prepaid = (float) str_replace(',', '', (string) ($data['prepaid_amount'] ?? 0));
        $address = !empty($data['address_snapshot']) ? trim($data['address_snapshot']) : ($customer?->address ?? null);

        // ئەگەر ناونیشانی کڕیار بەتاڵ بوو، ناونیشانەکەی بۆ پاشەکەوت بکە بۆ داهاتوو
        if ($customer && empty($customer->address) && $address) {
            $customer->update(['address' => $address]);
        }

        return [
            'customer_id' => $data['customer_id'],
            'order_date' => $data['order_date'],
            'delivery_date' => $data['delivery_date'] ?? null,
            'currency' => $data['currency'],
            'exchange_rate' => $data['currency'] === 'USD'
                ? ($data['exchange_rate'] ?: ExchangeRate::forDate($data['order_date']))
                : null,
            'subtotal' => $subtotal,
            'discount_percent' => $percent,
            'discount_amount' => $discount,
            'total' => max(0, $subtotal - $discount),
            'prepaid_amount' => $prepaid,
            'address_snapshot' => $address,
            'note' => $data['note'] ?? null,
        ];
    }

    private function lineTotal(array $line): float
    {
        return (float) str_replace(',', '', (string) ($line['unit_price'] ?? 0));
    }

    private function syncLines(Order $order, array $lines, array $lineFiles = []): void
    {
        foreach ($lines as $index => $line) {
            $unitPrice = (float) str_replace(',', '', (string) ($line['unit_price'] ?? 0));

            $imagePath = null;
            if (isset($lineFiles[$index]['image']) && $lineFiles[$index]['image']->isValid()) {
                $imagePath = $lineFiles[$index]['image']->store('orders', 'public');
            } elseif (!empty($line['existing_image'])) {
                $imagePath = $line['existing_image'];
            }

            OrderItem::create([
                'order_id' => $order->id,
                'description' => $line['description'],
                'image' => $imagePath,
                'item_id' => $line['item_id'] ?? null,
                'pricing_mode' => 'count',
                'width' => null,
                'height' => null,
                'qty' => 1,
                'computed_qty' => 1,
                'unit_price' => $unitPrice,
                'line_total' => $unitPrice,
                'note' => $line['note'] ?? null,
            ]);
        }
    }

    /** پێشەکی وەک حەقدییەکی جیا تۆمار دەکرێت تا دووجار نەژمێردرێت. */
    private function recordPrepaid(Order $order, ?Customer $customer, float $amount): ?\App\Models\Payment
    {
        if ($amount <= 0) {
            return null;
        }

        return $this->payments->record([
            'direction' => 'in',
            'amount' => $amount,
            'currency' => $order->currency,
            'paid_at' => $order->order_date->toDateString(),
            'party' => $customer,
            'order_id' => $order->id,
            'category' => 'customer_payment',
            'note' => 'پێشەکی وەسڵی ژمارە '.$order->invoice_no,
        ]);
    }

    /** کەمکردنەوەی مەخزەن بۆ ئەو دێڕانەی کاڵایەکی دیاریکراویان هەیە. */
    private function releaseStock(Order $order): void
    {
        $warehouseId = Warehouse::defaultId();

        if (! $warehouseId) {
            return;
        }

        foreach ($order->items()->whereNotNull('item_id')->get() as $line) {
            $this->stock->record(
                itemId: $line->item_id,
                warehouseId: $warehouseId,
                direction: 'out',
                qty: (float) $line->computed_qty,
                reason: 'sale',
                reference: $order,
                extra: ['moved_at' => $order->order_date->toDateString()],
            );
        }
    }
}
