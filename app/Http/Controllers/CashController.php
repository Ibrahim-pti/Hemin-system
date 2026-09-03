<?php

namespace App\Http\Controllers;

use App\Models\CashBox;
use App\Models\CashClosing;
use App\Models\CashTransaction;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CashController extends Controller
{
    /** حیسابی ڕۆژانە و مێژووی جوڵەی قاسە. */
    public function index(Request $request): View
    {
        $dateFrom = $request->date('from')?->toDateString() ?? ($request->date('date')?->toDateString() ?? now()->toDateString());
        $dateTo = $request->date('to')?->toDateString() ?? $dateFrom;
        $boxId = $request->integer('cash_box_id') ?: null;
        $direction = $request->string('direction')->toString() ?: null;

        $boxes = CashBox::where('is_active', true)->get();
        $defaultBox = $boxes->where('currency', 'IQD')->first() ?? $boxes->first();

        $iqdBox = $boxes->where('currency', 'IQD')->first() ?? $defaultBox;
        $usdBox = $boxes->where('currency', 'USD')->first();

        // ژماردنی ئاماری دینار بە جیا
        $iqdBalance = $iqdBox ? $iqdBox->balance() : 0;
        $iqdIn = $iqdBox ? (float) $iqdBox->transactions()->where('direction', 'in')->whereBetween('occurred_at', [$dateFrom, $dateTo])->sum('amount') : 0;
        $iqdOut = $iqdBox ? (float) $iqdBox->transactions()->where('direction', 'out')->whereBetween('occurred_at', [$dateFrom, $dateTo])->sum('amount') : 0;
        $iqdNet = $iqdIn - $iqdOut;

        // ژماردنی ئاماری دۆلار بە جیا (بۆ ئەوەی تێکەڵ بە دینار نەبێت)
        $usdBalance = $usdBox ? $usdBox->balance() : 0;
        $usdIn = $usdBox ? (float) $usdBox->transactions()->where('direction', 'in')->whereBetween('occurred_at', [$dateFrom, $dateTo])->sum('amount') : 0;
        $usdOut = $usdBox ? (float) $usdBox->transactions()->where('direction', 'out')->whereBetween('occurred_at', [$dateFrom, $dateTo])->sum('amount') : 0;
        $usdNet = $usdIn - $usdOut;

        $boxStats = $boxes->map(function (CashBox $box) use ($dateFrom, $dateTo) {
            $currentBalance = $box->balance();
            $periodIn = (float) $box->transactions()->where('direction', 'in')->whereBetween('occurred_at', [$dateFrom, $dateTo])->sum('amount');
            $periodOut = (float) $box->transactions()->where('direction', 'out')->whereBetween('occurred_at', [$dateFrom, $dateTo])->sum('amount');

            return [
                'box' => $box,
                'currentBalance' => $currentBalance,
                'periodIn' => $periodIn,
                'periodOut' => $periodOut,
                'periodNet' => $periodIn - $periodOut,
            ];
        });

        $query = CashTransaction::query()
            ->with(['cashBox', 'user', 'reference'])
            ->whereBetween('occurred_at', [$dateFrom, $dateTo]);

        if ($boxId) {
            $query->where('cash_box_id', $boxId);
        }

        if ($direction && in_array($direction, ['in', 'out'])) {
            $query->where('direction', $direction);
        }

        $transactions = $query->latest('occurred_at')->latest('id')->paginate(50)->withQueryString();

        return view('cash.index', compact(
            'boxes', 'defaultBox', 'boxStats', 'transactions', 'dateFrom', 'dateTo', 'boxId', 'direction',
            'iqdBox', 'usdBox', 'iqdBalance', 'iqdIn', 'iqdOut', 'iqdNet',
            'usdBalance', 'usdIn', 'usdOut', 'usdNet'
        ));
    }

    /** دەستکاری سەرمایەی سەرەتایی قاسە. */
    public function updateOpeningBalance(Request $request)
    {
        $data = $request->validate([
            'cash_box_id' => ['required', 'exists:cash_boxes,id'],
            'opening_balance' => ['required', 'numeric'],
        ]);

        $box = CashBox::findOrFail($data['cash_box_id']);
        $box->update(['opening_balance' => $data['opening_balance']]);

        return back()->with('ok', 'سەرمایەی سەرەتایی ' . $box->name . ' بە سەرکەوتوویی نوێکرایەوە.');
    }

    /** تێکردنی پارە یان دەرهێنانی پارە (خەرجی / کێشکردن بۆ کەسێک). */
    public function storeTransaction(Request $request)
    {
        $data = $request->validate([
            'cash_box_id' => ['required', 'exists:cash_boxes,id'],
            'direction' => ['required', 'in:in,out'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'category' => ['nullable', 'string'],
            'person_name' => ['nullable', 'string', 'max:150'],
            'occurred_at' => ['required', 'date'],
            'note' => ['nullable', 'string'],
        ], ['amount.gt' => 'بڕ دەبێت لە ٠ زیاتر بێت.']);

        $category = $data['category'] ?? ($data['direction'] === 'in' ? 'other' : 'expense');
        if (! array_key_exists($category, CashTransaction::CATEGORIES)) {
            $category = $data['direction'] === 'in' ? 'other' : 'expense';
        }

        $finalNote = '';
        if (! empty($data['person_name'])) {
            $finalNote .= 'کەس / لایەن: ' . trim($data['person_name']);
        }
        if (! empty($data['note'])) {
            $finalNote .= ($finalNote ? ' — ' : '') . trim($data['note']);
        }

        CashTransaction::create([
            'cash_box_id' => $data['cash_box_id'],
            'direction' => $data['direction'],
            'amount' => $data['amount'],
            'category' => $category,
            'occurred_at' => $data['occurred_at'],
            'note' => $finalNote ?: null,
            'user_id' => auth()->id(),
        ]);

        $msg = $data['direction'] === 'in' ? 'پارە بە سەرکەوتوویی خرایە ناو قاسە.' : 'پارە بە سەرکەوتوویی لە قاسە دەرکرا.';

        return back()->with('ok', $msg);
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
