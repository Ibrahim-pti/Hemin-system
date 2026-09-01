<?php

namespace App\Services;

use App\Models\CashBox;
use App\Models\CashTransaction;
use App\Models\ExchangeRate;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * تۆمارکردنی حەقدی (وەرگرتن/دانی پارە) — هەمیشە لەگەڵ جوڵەی قاسەدا.
 *
 * حەقدی و قاسە هەرگیز لێک جیا نابنەوە: هەر پارەیەک وەربگیرێت یان بدرێت،
 * هەردوو تۆمارەکە پێکەوە دروست دەبن، بۆیە باڵانسی قاسە هەمیشە ڕاستە.
 */
class PaymentService
{
    /**
     * @param  array{direction:string, amount:float, currency?:string, paid_at?:string,
     *              party?:?Model, party_name?:?string, order_id?:?int, purchase_id?:?int,
     *              cash_box_id?:?int, category?:string, note?:?string}  $data
     */
    public function record(array $data): Payment
    {
        return DB::transaction(function () use ($data) {
            $currency = $data['currency'] ?? 'IQD';
            $paidAt = $data['paid_at'] ?? now()->toDateString();
            $rate = $currency === 'USD' ? ((float) ($data['exchange_rate'] ?? 0) ?: ExchangeRate::forDate($paidAt)) : null;
            $amount = (float) $data['amount'];
            $amountIqd = $currency === 'USD' ? $amount * (float) $rate : $amount;

            $party = $data['party'] ?? null;

            $payment = Payment::create([
                'voucher_no' => Payment::nextVoucherNo(),
                'direction' => $data['direction'],
                'party_type' => $party ? $party::class : null,
                'party_id' => $party?->getKey(),
                'party_name' => $data['party_name'] ?? $party?->name,
                'order_id' => $data['order_id'] ?? null,
                'purchase_id' => $data['purchase_id'] ?? null,
                'amount' => $amount,
                'currency' => $currency,
                'exchange_rate' => $rate,
                'amount_iqd' => $amountIqd,
                'cash_box_id' => $data['cash_box_id'] ?? $this->defaultBoxId($currency),
                'paid_at' => $paidAt,
                'user_id' => Auth::id(),
                'note' => $data['note'] ?? null,
            ]);

            if ($payment->cash_box_id) {
                CashTransaction::create([
                    'cash_box_id' => $payment->cash_box_id,
                    'direction' => $payment->direction,
                    'amount' => $amount,
                    'category' => $data['category'] ?? ($payment->direction === 'in'
                        ? 'customer_payment'
                        : 'supplier_payment'),
                    'reference_type' => Payment::class,
                    'reference_id' => $payment->id,
                    'occurred_at' => $paidAt,
                    'user_id' => Auth::id(),
                    'note' => $payment->note,
                ]);
            }

            return $payment;
        });
    }

    /** سڕینەوەی حەقدی — جوڵەی قاسەکەشی لەگەڵدا دەسڕێتەوە. */
    public function remove(Payment $payment): void
    {
        DB::transaction(function () use ($payment) {
            CashTransaction::where('reference_type', Payment::class)
                ->where('reference_id', $payment->id)
                ->delete();

            $payment->delete();
        });
    }

    /** قاسەی گونجاو بەپێی دراو. */
    private function defaultBoxId(string $currency): ?int
    {
        return CashBox::where('currency', $currency)->where('is_active', true)->value('id');
    }
}
