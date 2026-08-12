<?php

namespace App\Http\Controllers;

use App\Models\CashBox;
use App\Models\CashClosing;
use App\Models\CashTransaction;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CashController extends Controller
{
    /** حیسابی ڕۆژانەی قاسە. */
    public function index(Request $request): View
    {
        $date = $request->date('date')?->toDateString() ?? now()->toDateString();
        $boxes = CashBox::where('is_active', true)->get();

        $summary = $boxes->map(function (CashBox $box) use ($date) {
            $totals = $box->dayTotals($date);
            // باڵانسی سەرەتای ڕۆژ = باڵانسی کۆتایی ڕۆژی پێشوو.
            $opening = $box->balance(now()->parse($date)->subDay()->toDateString());

            return [
                'box' => $box,
                'opening' => $opening,
                'in' => $totals['in'],
                'out' => $totals['out'],
                'expected' => $opening + $totals['in'] - $totals['out'],
                'closing' => CashClosing::where('cash_box_id', $box->id)
                    ->whereDate('closing_date', $date)
                    ->first(),
            ];
        });

        $transactions = CashTransaction::query()
            ->with(['cashBox', 'user', 'reference'])
            ->whereDate('occurred_at', $date)
            ->latest('id')
            ->get();

        return view('cash.index', compact('summary', 'transactions', 'boxes', 'date'));
    }

    /** خەرجی یان داهاتی دەستی — بێ حەقدی. */
    public function storeTransaction(Request $request)
    {
        $data = $request->validate([
            'cash_box_id' => ['required', 'exists:cash_boxes,id'],
            'direction' => ['required', 'in:in,out'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'category' => ['required', 'in:'.implode(',', array_keys(CashTransaction::CATEGORIES))],
            'occurred_at' => ['required', 'date'],
            'note' => ['nullable', 'string'],
        ], ['amount.gt' => 'بڕ دەبێت لە سفر زیاتر بێت.']);

        CashTransaction::create($data + ['user_id' => auth()->id()]);

        return back()->with('ok', 'جوڵەی قاسە تۆمارکرا.');
    }

    /** داخستنی ڕۆژ — بەراوردی باڵانسی سیستەم لەگەڵ ئەوەی ژمێردراو. */
    public function close(Request $request)
    {
        $data = $request->validate([
            'cash_box_id' => ['required', 'exists:cash_boxes,id'],
            'closing_date' => ['required', 'date'],
            'counted_balance' => ['required', 'numeric'],
            'note' => ['nullable', 'string'],
        ]);

        $box = CashBox::findOrFail($data['cash_box_id']);
        $totals = $box->dayTotals($data['closing_date']);
        $opening = $box->balance(now()->parse($data['closing_date'])->subDay()->toDateString());
        $expected = $opening + $totals['in'] - $totals['out'];

        CashClosing::updateOrCreate(
            ['cash_box_id' => $box->id, 'closing_date' => $data['closing_date']],
            [
                'opening_balance' => $opening,
                'total_in' => $totals['in'],
                'total_out' => $totals['out'],
                'expected_balance' => $expected,
                'counted_balance' => $data['counted_balance'],
                'difference' => (float) $data['counted_balance'] - $expected,
                'user_id' => auth()->id(),
                'note' => $data['note'] ?? null,
            ],
        );

        return back()->with('ok', 'ڕۆژەکە داخرا.');
    }
}
