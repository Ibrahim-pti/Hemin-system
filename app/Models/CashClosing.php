<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * داخستنی ڕۆژانەی قاسە — بەراوردی باڵانسی سیستەم لەگەڵ ئەو پارەیەی ژمێردراوە.
 */
class CashClosing extends Model
{
    protected $fillable = [
        'cash_box_id', 'closing_date', 'opening_balance', 'total_in', 'total_out',
        'expected_balance', 'counted_balance', 'difference', 'user_id', 'note',
    ];

    protected function casts(): array
    {
        return [
            'closing_date' => 'date',
            'opening_balance' => 'decimal:2',
            'total_in' => 'decimal:2',
            'total_out' => 'decimal:2',
            'expected_balance' => 'decimal:2',
            'counted_balance' => 'decimal:2',
            'difference' => 'decimal:2',
        ];
    }

    public function cashBox(): BelongsTo
    {
        return $this->belongsTo(CashBox::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getIsBalancedAttribute(): bool
    {
        return abs((float) $this->difference) < 0.01;
    }
}
