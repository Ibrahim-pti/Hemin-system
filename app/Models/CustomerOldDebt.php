<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerOldDebt extends Model
{
    protected $fillable = [
        'customer_id',
        'amount',
        'paid_amount',
        'currency',
        'status',
        'image',
        'note',
        'date',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'date' => 'date',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function remaining(): float
    {
        if ($this->status === 'paid') {
            return 0.0;
        }

        return max(0.0, (float) $this->amount - (float) $this->paid_amount);
    }

    public function remainingIqd(): float
    {
        $rem = $this->remaining();
        if ($this->currency === 'USD') {
            return $rem * (ExchangeRate::current() ?: 1500);
        }

        return $rem;
    }

    public function totalIqd(): float
    {
        if ($this->currency === 'USD') {
            return (float) $this->amount * (ExchangeRate::current() ?: 1500);
        }

        return (float) $this->amount;
    }

    public function paidIqd(): float
    {
        if ($this->status === 'paid') {
            return $this->totalIqd();
        }

        if ($this->currency === 'USD') {
            return (float) $this->paid_amount * (ExchangeRate::current() ?: 1500);
        }

        return (float) $this->paid_amount;
    }
}
