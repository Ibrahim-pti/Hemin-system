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

        $currency = $request->string('currency', 'all')->toString();
        if (!in_array($currency, ['all', 'USD', 'IQD'], true)) {
            $currency = 'all';
        }
        $currentRate = \App\Models\ExchangeRate::current() ?: 1500;

        $customers = Customer::with([
            'orders' => function ($q) {
                $q->whereNotIn('status', ['draft', 'cancelled'])->with('payments');
            },
            'payments' => function ($q) {
                $q->where('direction', 'in');
            },
            'oldDebts',
        ])
        ->orderBy('name')
        ->get()
        ->map(function (Customer $c) {
            $openings = $c->openingBalances();
            $invoiced = $c->invoicedTotals();
            $paid = $c->paidTotals();
            $balances = $c->balances();

            $activeOrdersCount = $c->orders->filter(fn ($o) => $o->remaining() > 0.001)->count();

            $remIqd = (float) ($balances['IQD'] ?? 0);
            $remUsd = (float) ($balances['USD'] ?? 0);
            $totIqd = (float) (($openings['IQD'] ?? 0) + ($invoiced['IQD'] ?? 0));
            $totUsd = (float) (($openings['USD'] ?? 0) + ($invoiced['USD'] ?? 0));
            $paidIqd = (float) ($paid['IQD'] ?? 0);
            $paidUsd = (float) ($paid['USD'] ?? 0);

            $hasDebt = $c->hasDebt();

            return [
                'model' => $c,
                'id' => $c->id,
                'name' => $c->name,
                'phone' => $c->phone,
                'address' => $c->address,
                'orders_count' => $c->orders->count(),
                'active_orders_count' => $activeOrdersCount,
                'opening_iqd' => (float) ($openings['IQD'] ?? 0),
                'opening_usd' => (float) ($openings['USD'] ?? 0),
                'total_amount_iqd' => $totIqd,
                'total_amount_usd' => $totUsd,
                'total_paid_iqd' => $paidIqd,
                'total_paid_usd' => $paidUsd,
                'remaining_iqd' => $remIqd,
                'remaining_usd' => $remUsd,
                // Fallbacks for generic references
                'total_amount' => $totIqd,
                'total_paid' => $paidIqd,
                'remaining' => $remIqd,
                'is_active_debtor' => $hasDebt,
                'formatted_remaining' => fmt_dual($balances),
                'formatted_total' => fmt_dual([
                    'IQD' => $totIqd,
                    'USD' => $totUsd,
                ]),
                'formatted_paid' => fmt_dual($paid),
            ];
        });

        $totalRemainingDebtIqd = (float) $customers->where('remaining_iqd', '>', 0.001)->sum('remaining_iqd');
        $totalRemainingDebtUsd = (float) $customers->where('remaining_usd', '>', 0.001)->sum('remaining_usd');
        $totalPaidIqd = (float) $customers->sum('total_paid_iqd');
        $totalPaidUsd = (float) $customers->sum('total_paid_usd');

        if ($currency === 'USD') {
            $activeDebtorsCount = $customers->where('remaining_usd', '>', 0.001)->count();
            $customers = $customers->sortByDesc('remaining_usd')->values();
        } elseif ($currency === 'IQD') {
            $activeDebtorsCount = $customers->where('remaining_iqd', '>', 0.001)->count();
            $customers = $customers->sortByDesc('remaining_iqd')->values();
        } else {
            $activeDebtorsCount = $customers->where('is_active_debtor', true)->count();
            $customers = $customers->sortByDesc(fn ($c) => ($c['remaining_iqd'] > 0.001 || $c['remaining_usd'] > 0.001) ? 1 : 0)->values();
        }

        $totalRemainingDebt = $totalRemainingDebtIqd;
        $totalPaid = $totalPaidIqd;

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
                'oldDebts',
            ])->find($request->customer);

            if ($c) {
                $openings = $c->openingBalances();
                $invoiced = $c->invoicedTotals();
                $paid = $c->paidTotals();
                $balances = $c->balances();

                $activeOrdersCount = 0;
                $totalDiscount = 0;

                $ordersList = $c->orders->map(function ($order) use (&$activeOrdersCount, &$totalDiscount) {
                    $orderTotal = (float) $order->total;
                    $orderPaid = (float) $order->paidTotal();
                    $orderRemaining = max(0, $orderTotal - $orderPaid);
                    if ($orderRemaining > 0.001) {
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
                        'currency' => $order->currency ?: 'IQD',
                        'discount' => $discount,
                        'total' => $orderTotal,
                        'paid' => $orderPaid,
                        'remaining' => $orderRemaining,
                    ];
                });

                $selectedCustomer = $c;
                $customerOrders = $ordersList;
                $customerStats = [
                    'balances' => $balances,
                    'openings' => $openings,
                    'invoiced' => $invoiced,
                    'paid' => $paid,
                    'remaining_iqd' => (float) ($balances['IQD'] ?? 0),
                    'remaining_usd' => (float) ($balances['USD'] ?? 0),
                    'paid_iqd' => (float) ($paid['IQD'] ?? 0),
                    'paid_usd' => (float) ($paid['USD'] ?? 0),
                    'total_iqd' => (float) (($openings['IQD'] ?? 0) + ($invoiced['IQD'] ?? 0)),
                    'total_usd' => (float) (($openings['USD'] ?? 0) + ($invoiced['USD'] ?? 0)),
                    'active_orders_count' => $activeOrdersCount,
                    'total_discount' => $totalDiscount,
                    'opening_iqd' => (float) ($openings['IQD'] ?? 0),
                    'opening_usd' => (float) ($openings['USD'] ?? 0),
                    'remaining_debt' => (float) ($balances['IQD'] ?? 0),
                    'paid_total' => (float) ($paid['IQD'] ?? 0),
                    'total_debt' => (float) (($openings['IQD'] ?? 0) + ($invoiced['IQD'] ?? 0)),
                ];
            }
        }

        return view('debts.index', [
            'customers' => $customers,
            'allCustomersList' => $allCustomersList,
            'totalRemainingDebt' => $totalRemainingDebt,
            'totalPaid' => $totalPaid,
            'totalRemainingDebtIqd' => $totalRemainingDebtIqd,
            'totalRemainingDebtUsd' => $totalRemainingDebtUsd,
            'totalPaidIqd' => $totalPaidIqd,
            'totalPaidUsd' => $totalPaidUsd,
            'currency' => $currency,
            'currentRate' => $currentRate,
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

        // پشکنین ئەگەر فۆڕمەکە لەلایەن PHP بەهۆی قەبارەی زۆر گەورە فڕێدرابێت
        if (empty($_POST) && (int) $request->server('CONTENT_LENGTH', 0) > 0) {
            return back()
                ->withInput()
                ->withErrors(['image' => 'قەبارەی وێنە یان فایلەکە زۆر گەورەیە و لە توانای سێرڤەر زیاترە.']);
        }

        if (!$request->hasFile('image')) {
            if ($request->hasFile('image_camera')) {
                $request->files->set('image', $request->file('image_camera'));
            } elseif ($request->hasFile('image_pdf')) {
                $request->files->set('image', $request->file('image_pdf'));
            }
        }

        $data = $request->validate([
            'customer_id' => ['nullable'],
            'new_customer_name' => ['nullable', 'required_without:customer_id', 'string', 'max:255'],
            'new_customer_phone' => ['nullable', 'string', 'max:50'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'currency' => ['required', 'in:IQD,USD'],
            'status' => ['required', 'in:debt,paid,partial'],
            'paid_amount' => ['nullable', 'numeric', 'min:0'],
            'image' => ['nullable'],
            'image.*' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,heic,heif,bmp,pdf', 'max:25600'],
            'image_camera' => ['nullable'],
            'image_camera.*' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,heic,heif,bmp,pdf', 'max:25600'],
            'image_pdf' => ['nullable'],
            'image_pdf.*' => ['nullable', 'file', 'mimes:pdf', 'max:25600'],
            'attachments' => ['nullable'],
            'attachments.*' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,heic,heif,bmp,pdf', 'max:25600'],
            'image_base64' => ['nullable', 'string'],
            'attachments_base64' => ['nullable', 'array'],
            'attachments_base64.*' => ['nullable', 'string'],
            'date' => ['nullable', 'date'],
            'note' => ['nullable', 'string', 'max:500'],
        ], [
            'amount.required' => 'بڕی حیساب / قەرز بنووسە.',
            'amount.min' => 'بڕی پارە دەبێت لە ٠ زیاتر بێت.',
            'new_customer_name.required_without' => 'ناوی کڕیار بنووسە یان کڕیارێک هەڵبژێرە.',
            'attachments.*.mimes' => 'هەموو فایلەکان دەبێت وێنە (JPG, PNG, WEBP) یان بەڵگەنامەی PDF بن.',
            'attachments.*.max' => 'قەبارەی هیچ فایلێک نابێت لە ۲۵ مێگابایت زیاتر بێت.',
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

        $storedFiles = [];

        // کۆکردنەوەی هەموو فایلە بەرزکراوەکان (چەندین وێنە و چەندین فایلی PDF پێکەوە)
        $fileInputs = ['attachments', 'image', 'image_camera', 'image_pdf'];
        foreach ($fileInputs as $inputKey) {
            if ($request->hasFile($inputKey)) {
                $rawFiles = $request->file($inputKey);
                $fileList = is_array($rawFiles) ? $rawFiles : [$rawFiles];
                foreach ($fileList as $f) {
                    if ($f && $f->isValid()) {
                        $storedFiles[] = $f->store('old_debts', 'public');
                    }
                }
            }
        }

        // کۆکردنەوەی وێنەکانی Base64 تەنها ئەگەر هیچ فایلێک لە ڕێگەی ئینپووتی فایلەکانەوە وەرنەگیرابێت (fallback بۆ مۆبایل و کامێرا)
        if (empty($storedFiles)) {
            $base64List = [];
            if ($request->filled('attachments_base64')) {
                $base64List = array_values(array_filter((array) $request->input('attachments_base64')));
            } elseif ($request->filled('image_base64')) {
                $base64List = [(string) $request->input('image_base64')];
            }

            foreach ($base64List as $base64Data) {
                if (is_string($base64Data) && preg_match('/^data:image\/(\w+);base64,/', $base64Data, $typeMatch)) {
                    $rawBase64 = substr($base64Data, strpos($base64Data, ',') + 1);
                    $ext = strtolower($typeMatch[1]);
                    if (in_array($ext, ['jpeg', 'jpg', 'png', 'webp', 'heic'])) {
                        $decoded = base64_decode($rawBase64);
                        if ($decoded !== false) {
                            $ext = $ext === 'jpeg' ? 'jpg' : $ext;
                            $fileName = 'old_debts/' . \Illuminate\Support\Str::random(40) . '.' . $ext;
                            \Illuminate\Support\Facades\Storage::disk('public')->put($fileName, $decoded);
                            $storedFiles[] = $fileName;
                        }
                    }
                }
            }
        }

        $storedFiles = array_values(array_unique($storedFiles));
        $primaryImage = !empty($storedFiles) ? $storedFiles[0] : null;

        \App\Models\CustomerOldDebt::create([
            'customer_id' => $customer->id,
            'amount' => $amount,
            'paid_amount' => $paidAmount,
            'currency' => $data['currency'],
            'status' => $status,
            'image' => $primaryImage,
            'attachments' => !empty($storedFiles) ? $storedFiles : null,
            'note' => $data['note'] ?? null,
            'date' => $data['date'] ?? now()->toDateString(),
            'user_id' => auth()->id(),
        ]);

        return back()->with('ok', "حیسابی پێشوو بۆ ({$customer->name}) بە سەرکەوتوویی تۆمارکرا.");
    }

    /** سڕینەوەی تۆماری قەرزی کۆن */
    public function destroyOldDebt(\App\Models\CustomerOldDebt $oldDebt)
    {
        $allFiles = $oldDebt->allAttachments();
        foreach ($allFiles as $filePath) {
            if ($filePath && \Illuminate\Support\Facades\Storage::disk('public')->exists($filePath)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($filePath);
            }
        }

        $oldDebt->delete();

        return back()->with('ok', "حیسابی پێشوو سڕدرایەوە.");
    }
}
