<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Supplier;
use Illuminate\View\View;

class DebtController extends Controller
{
    /** قەرزەکان — ئەوەی کڕیاران قەرزارن، و ئەوەی کارگە قەرزارە. */
    public function index(): View
    {
        $customers = Customer::query()
            ->orderBy('name')
            ->get()
            ->map(fn (Customer $c) => [
                'model' => $c,
                'balance' => $c->balance(),
                // کۆنترین وەسڵی نەدراوە — بۆ زانینی تەمەنی قەرز.
                'oldest' => $c->orders()
                    ->whereNotIn('status', ['draft', 'cancelled'])
                    ->orderBy('order_date')
                    ->value('order_date'),
            ])
            ->filter(fn ($row) => abs($row['balance']) > 0.5)
            ->sortByDesc('balance')
            ->values();

        $suppliers = Supplier::query()
            ->orderBy('name')
            ->get()
            ->map(fn (Supplier $s) => ['model' => $s, 'balance' => $s->balance()])
            ->filter(fn ($row) => abs($row['balance']) > 0.5)
            ->sortByDesc('balance')
            ->values();

        // وەسڵە نەدراوەکان — بۆ چاودێری ڕاستەوخۆ.
        $openOrders = Order::with('customer')
            ->whereNotIn('status', ['draft', 'cancelled'])
            ->get()
            ->map(fn (Order $o) => ['order' => $o, 'remaining' => $o->remaining()])
            ->filter(fn ($row) => $row['remaining'] > 0.5)
            ->sortByDesc('remaining')
            ->values();

        return view('debts.index', [
            'customers' => $customers,
            'suppliers' => $suppliers,
            'openOrders' => $openOrders,
            'totalReceivable' => $customers->sum(fn ($r) => max(0, $r['balance'])),
            'totalPayable' => $suppliers->sum(fn ($r) => max(0, $r['balance'])),
        ]);
    }
}
