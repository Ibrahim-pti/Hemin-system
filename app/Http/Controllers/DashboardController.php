<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\CashBox;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\ExternalJob;
use App\Models\Item;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Purchase;
use App\Models\StockMovement;
use App\Models\Supplier;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        $today = now()->toDateString();
        $startOfMonth = now()->startOfMonth()->toDateString();

        // ئەو کاڵایانەی لە سنووری ئاگاداری کەمتر بوونەتەوە.
        $lowStock = Item::query()
            ->active()
            ->withStock()
            ->with('unit')
            ->where('min_qty', '>', 0)
            ->get()
            ->filter(fn (Item $item) => $item->stock_qty <= (float) $item->min_qty)
            ->sortBy('stock_qty')
            ->values();

        $data = [
            'lowStock' => $lowStock,
            'itemsCount' => Item::active()->count(),
            'todayMovements' => StockMovement::whereDate('moved_at', $today)->count(),
        ];

        // زانیاری تەواو و دارایی بۆ بەڕێوەبەر.
        if ($user->canSeeMoney()) {
            $data += [
                'todayOrders' => Order::whereDate('order_date', $today)
                    ->whereNotIn('status', ['draft', 'cancelled'])
                    ->count(),
                'todaySales' => (float) Order::whereDate('order_date', $today)
                    ->whereNotIn('status', ['draft', 'cancelled'])
                    ->sum(Order::totalIqdExpression()),
                'monthSales' => (float) Order::whereDate('order_date', '>=', $startOfMonth)
                    ->whereNotIn('status', ['draft', 'cancelled'])
                    ->sum(Order::totalIqdExpression()),
                'todayIn' => (float) Payment::where('direction', 'in')->whereDate('paid_at', $today)->sum('amount_iqd'),
                'todayOut' => (float) Payment::where('direction', 'out')->whereDate('paid_at', $today)->sum('amount_iqd'),
                'monthIn' => (float) Payment::where('direction', 'in')->whereDate('paid_at', '>=', $startOfMonth)->sum('amount_iqd'),
                'cashBoxes' => CashBox::where('is_active', true)->get(),
                'receivables' => $this->receivables(),
                'payables' => $this->payables(),
                'openOrders' => Order::whereIn('status', ['confirmed', 'in_production', 'ready'])->count(),
                'inProductionCount' => Order::where('status', 'in_production')->count(),
                'readyOrdersCount' => Order::where('status', 'ready')->count(),
                'recentOrders' => Order::with('customer')->latest('id')->limit(6)->get(),
                'recentPayments' => Payment::with('cashBox')->latest('paid_at')->latest('id')->limit(5)->get(),
                'activeJobs' => ExternalJob::where('status', 'open')->latest('id')->limit(5)->get(),
                'activeJobsCount' => ExternalJob::where('status', 'open')->count(),
                'totalOrdersCount' => Order::whereNotIn('status', ['draft', 'cancelled'])->count(),
                'totalCustomersCount' => Customer::active()->count(),
                'totalSuppliersCount' => Supplier::active()->count(),
                'monthExpenses' => (float) Payment::where('direction', 'out')->whereDate('paid_at', '>=', $startOfMonth)->sum('amount_iqd') + (float) Purchase::where('status', 'confirmed')->whereDate('purchase_date', '>=', $startOfMonth)->sum(Purchase::totalIqdExpression()),
                'presentToday' => Attendance::whereDate('work_date', $today)->where('status', 'present')->count(),
                'absentToday' => Attendance::whereDate('work_date', $today)->where('status', 'absent')->count(),
                'totalEmployees' => Employee::active()->count(),
                'todayAttendances' => Attendance::with('employee')->whereDate('work_date', $today)->orderByDesc('id')->limit(8)->get(),
                'todayPurchases' => (float) Purchase::where('status', 'confirmed')->whereDate('purchase_date', $today)->sum(Purchase::totalIqdExpression()),
            ];
        }

        return view('dashboard', $data);
    }

    /** کۆی قەرزی کڕیاران بۆ کارگە (بە دینار). */
    private function receivables(): float
    {
        $invoiced = (float) Order::whereNotIn('status', ['draft', 'cancelled'])
            ->sum(Order::totalIqdExpression());

        $received = (float) Payment::where('direction', 'in')
            ->where('party_type', Customer::class)
            ->sum('amount_iqd');

        $opening = Customer::all()->sum(fn (Customer $c) => $c->openingIqd());

        return $opening + $invoiced - $received;
    }

    /** کۆی قەرزی کارگە بۆ فرۆشیاران (بە دینار). */
    private function payables(): float
    {
        $purchased = (float) Purchase::where('status', 'confirmed')
            ->sum(Purchase::totalIqdExpression());

        $paid = (float) Payment::where('direction', 'out')
            ->where('party_type', Supplier::class)
            ->sum('amount_iqd');

        $opening = Supplier::all()->sum(fn (Supplier $s) => $s->openingIqd());

        return $opening + $purchased - $paid;
    }
}
