<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CashTransaction extends Model
{
    protected $fillable = [
        'cash_box_id', 'direction', 'amount', 'category',
        'reference_type', 'reference_id', 'occurred_at', 'user_id', 'note',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'occurred_at' => 'date',
        ];
    }

    public const CATEGORIES = [
        'opening' => 'باڵانسی سەرەتایی',
        'customer_payment' => 'پارە لە کڕیار',
        'supplier_payment' => 'پارەدان بە فرۆشیار',
        'expense' => 'خەرجی',
        'wage' => 'حەقدەستی کارمەند',
        'external_job' => 'ئیشی خاریجی',
        'transfer' => 'گواستنەوە',
        'other' => 'هیتر',
    ];

    public function cashBox(): BelongsTo
    {
        return $this->belongsTo(CashBox::class);
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getCategoryLabelAttribute(): string
    {
        return self::CATEGORIES[$this->category] ?? $this->category;
    }

    public function getSignedAmountAttribute(): float
    {
        return $this->direction === 'in' ? (float) $this->amount : -(float) $this->amount;
    }
}
