<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DebtController extends Controller
{
    /** قەرزەکان — دۆخی گشتی قەرزداران و قەرزی کۆن. */
    public function index(Request $request): View
    {
        $customers = Customer::with([
            'orders' => function ($q) {
                $q->whereNotIn('status', ['draft', 'cancelled'])->with('payments');
            },
            'payments' => function ($q) {
                $q->where('direction', 'in');
            },
        ])
        ->orderBy('name')
        ->get()
        ->map(function (Customer $c) {
            $openingIqd = $c->openingIqd();
            $orders = $c->orders;

            $invoicedTotal = 0;
            $activeOrdersCount = 0;

            foreach ($orders as $order) {
                $orderTotalIqd = (float) $order->total_iqd;
                $invoicedTotal += $orderTotalIqd;

                $orderPaid = (float) $order->payments->where('direction', 'in')->sum('amount_iqd');
                if (($orderTotalIqd - $orderPaid) > 0.5) {
                    $activeOrdersCount++;
                }
            }

            $paidTotal = (float) $c->payments->sum('amount_iqd');
            $totalAmount = $openingIqd + $invoicedTotal;
            $remaining = $totalAmount - $paidTotal;

            return [
                'model' => $c,
                'id' => $c->id,
                'name' => $c->name,
                'phone' => $c->phone,
                'address' => $c->address,
                'orders_count' => $orders->count(),
                'active_orders_count' => $activeOrdersCount,
                'opening_iqd' => $openingIqd,
                'total_amount' => $totalAmount,
                'total_paid' => $paidTotal,
                'remaining' => $remaining,
                'is_active_debtor' => $remaining > 0.5,
            ];
        });

        $totalRemainingDebt = (float) $customers->where('remaining', '>', 0.5)->sum('remaining');
        $totalPaid = (float) $customers->sum('total_paid');
        $activeDebtorsCount = $customers->where('is_active_debtor', true)->count();

        // ڕیزبەندی بەپێی قەرزی ماوە
        $customers = $customers->sortByDesc('remaining')->values();

        // هەموو کڕیارە چالاکەکان بۆ مۆداڵی قەرزی کۆن
        $allCustomersList = Customer::active()->orderBy('name')->get(['id', 'name', 'phone']);

        // هەڵبژاردنی کڕیار بۆ بینینی وردەکاری قەرزەکانی
        $selectedCustomer = null;
        $customerOrders = collect();
        $customerStats = null;

        if ($request->filled('customer')) {
            $c = Customer::with([
                'orders' => function ($q) {
                    $q->whereNotIn('status', ['draft', 'cancelled'])->with('payments')->orderBy('order_date', 'asc');
                },
                'payments' => function ($q) {
                    $q->where('direction', 'in')->orderBy('paid_at', 'asc');
                },
            ])->find($request->customer);

            if ($c) {
                $openingIqd = (float) $c->openingIqd();
                $invoicedTotal = 0;
                $activeOrdersCount = 0;
                $totalDiscount = 0;

                $ordersList = $c->orders->map(function ($order) use (&$invoicedTotal, &$activeOrdersCount, &$totalDiscount) {
                    $orderTotal = (float) $order->total_iqd;
                    $invoicedTotal += $orderTotal;
                    $orderPaid = (float) $order->payments->where('direction', 'in')->sum('amount_iqd');
                    $orderRemaining = max(0, $orderTotal - $orderPaid);
                    if ($orderRemaining > 0.5) {
                        $activeOrdersCount++;
                    }
                    $discount = (float) ($order->discount_percent > 0 ? ($order->subtotal * $order->discount_percent / 100) : $order->discount_amount);
                    $totalDiscount += $discount;

                    return [
                        'order' => $order,
                        'id' => $order->id,
                        'invoice_no' => $order->invoice_no,
                        'note' => $order->note,
                        'order_date' => $order->order_date,
                        'discount' => $discount,
                        'total' => $orderTotal,
                        'paid' => $orderPaid,
                        'remaining' => $orderRemaining,
                    ];
                });

                $paidTotal = (float) $c->payments->sum('amount_iqd');
                $totalDebt = $openingIqd + $invoicedTotal;
                $remainingDebt = max(0, $totalDebt - $paidTotal);

                $selectedCustomer = $c;
                $customerOrders = $ordersList;
                $customerStats = [
                    'total_debt' => $totalDebt,
                    'paid_total' => $paidTotal,
                    'remaining_debt' => $remainingDebt,
                    'active_orders_count' => $activeOrdersCount,
                    'total_discount' => $totalDiscount,
                    'opening_iqd' => $openingIqd,
                ];
            }
        }

        return view('debts.index', [
            'customers' => $customers,
            'allCustomersList' => $allCustomersList,
            'totalRemainingDebt' => $totalRemainingDebt,
            'totalPaid' => $totalPaid,
            'activeDebtorsCount' => $activeDebtorsCount,
            'selectedCustomer' => $selectedCustomer,
            'customerOrders' => $customerOrders,
            'customerStats' => $customerStats,
        ]);
    }

    /** پەڕەی تایبەتی تۆمارکردنی قەرزی کۆن و حیساباتی پێشتر */
    public function createOldDebt(Request $request): View
    {
        $customers = Customer::active()->orderBy('name')->get(['id', 'name', 'phone']);
        $selectedCustomer = null;
        if ($request->filled('customer_id')) {
            $selectedCustomer = Customer::find($request->customer_id);
        }

        return view('debts.create_old_debt', compact('customers', 'selectedCustomer'));
    }

    /** تۆمارکردنی قەرزی کۆن و حیساباتی پێشتر (لەگەڵ دۆخی پارەدان و وێنە) */
    public function storeOldDebt(Request $request)
    {
        if ($request->input('customer_id') === '__NEW__') {
            $request->merge(['customer_id' => null]);
        }
        if ($request->filled('amount')) {
            $request->merge([
                'amount' => (float) str_replace(',', '', (string) $request->input('amount')),
            ]);
        }
        if ($request->filled('paid_amount')) {
            $request->merge([
                'paid_amount' => (float) str_replace(',', '', (string) $request->input('paid_amount')),
            ]);
        }

        if (!$request->filled('currency')) {
            $request->merge(['currency' => 'IQD']);
        }
        if (!$request->filled('status')) {
            $request->merge(['status' => 'debt']);
        }

        $data = $request->validate([
            'customer_id' => ['nullable'],
            'new_customer_name' => ['nullable', 'required_without:customer_id', 'string', 'max:255'],
            'new_customer_phone' => ['nullable', 'string', 'max:50'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'currency' => ['required', 'in:IQD,USD'],
            'status' => ['required', 'in:debt,paid,partial'],
            'paid_amount' => ['nullable', 'numeric', 'min:0'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'date' => ['nullable', 'date'],
            'note' => ['nullable', 'string', 'max:500'],
        ], [
            'amount.required' => 'بڕی حیساب / قەرز بنووسە.',
            'amount.min' => 'بڕی پارە دەبێت لە ٠ زیاتر بێت.',
            'new_customer_name.required_without' => 'ناوی کڕیار بنووسە یان کڕیارێک هەڵبژێرە.',
            'image.image' => 'فایلی هەڵبژێردراو دەبێت وێنە بێت.',
            'image.max' => 'قەبارەی وێنە نابێت لە ١٠ مێگابایت زیاتر بێت.',
        ]);

        if (!empty($data['customer_id'])) {
            $customer = Customer::findOrFail($data['customer_id']);
        } else {
            $customer = Customer::create([
                'name' => $data['new_customer_name'],
                'phone' => $data['new_customer_phone'] ?? null,
                'opening_balance' => 0,
                'opening_currency' => $data['currency'],
                'note' => $data['note'] ?? null,
                'is_active' => true,
            ]);
        }

        $amount = (float) $data['amount'];
        $status = $data['status'];
        if ($status === 'paid') {
            $paidAmount = $amount;
        } elseif ($status === 'debt') {
            $paidAmount = (float) ($data['paid_amount'] ?? 0);
            if ($paidAmount >= $amount) {
                $status = 'paid';
            } elseif ($paidAmount > 0) {
                $status = 'partial';
            }
        } else {
            $paidAmount = (float) ($data['paid_amount'] ?? 0);
        }

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('old_debts', 'public');
        }

        \App\Models\CustomerOldDebt::create([
            'customer_id' => $customer->id,
            'amount' => $amount,
            'paid_amount' => $paidAmount,
            'currency' => $data['currency'],
            'status' => $status,
            'image' => $imagePath,
            'note' => $data['note'] ?? null,
            'date' => $data['date'] ?? now()->toDateString(),
            'user_id' => auth()->id(),
        ]);

        return back()->with('ok', "حیساباتی پێشتر بۆ ({$customer->name}) بە سەرکەوتوویی تۆمارکرا.");
    }

    /** سڕینەوەی تۆماری قەرزی کۆن */
    public function destroyOldDebt(\App\Models\CustomerOldDebt $oldDebt)
    {
        if ($oldDebt->image && \Illuminate\Support\Facades\Storage::disk('public')->exists($oldDebt->image)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($oldDebt->image);
        }

        $oldDebt->delete();

        return back()->with('ok', "حیساباتی پێشتر سڕدرایەوە.");
    }
}
