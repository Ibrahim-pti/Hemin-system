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

        return view('debts.index', [
            'customers' => $customers,
            'allCustomersList' => $allCustomersList,
            'totalRemainingDebt' => $totalRemainingDebt,
            'totalPaid' => $totalPaid,
            'activeDebtorsCount' => $activeDebtorsCount,
        ]);
    }

    /** تۆمارکردنی قەرزی کۆن (باڵانسی سەرەتایی) */
    public function storeOldDebt(Request $request)
    {
        $data = $request->validate([
            'customer_id' => ['nullable'],
            'new_customer_name' => ['nullable', 'required_without:customer_id', 'string', 'max:255'],
            'new_customer_phone' => ['nullable', 'string', 'max:50'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'currency' => ['required', 'in:IQD,USD'],
            'note' => ['nullable', 'string', 'max:500'],
        ], [
            'amount.required' => 'بڕی قەرز بنووسە.',
            'amount.min' => 'بڕی قەرز دەبێت لە ٠ زیاتر بێت.',
            'new_customer_name.required_without' => 'ناوی کڕیار بنووسە یان کڕیارێک هەڵبژێرە.',
        ]);

        if (!empty($data['customer_id']) && $data['customer_id'] !== '__NEW__') {
            $customer = Customer::findOrFail($data['customer_id']);
            $customer->opening_balance = (float) $customer->opening_balance + (float) $data['amount'];
            $customer->opening_currency = $data['currency'];
            if (!empty($data['note'])) {
                $customer->note = trim(($customer->note ? $customer->note . " | " : "") . $data['note']);
            }
            $customer->save();
        } else {
            $customer = Customer::create([
                'name' => $data['new_customer_name'],
                'phone' => $data['new_customer_phone'] ?? null,
                'opening_balance' => $data['amount'],
                'opening_currency' => $data['currency'],
                'note' => $data['note'] ?? null,
                'is_active' => true,
            ]);
        }

        return redirect()->route('debts.index')->with('ok', "قەرزی کۆن بۆ ({$customer->name}) بە سەرکەوتوویی تۆمارکرا.");
    }
}
