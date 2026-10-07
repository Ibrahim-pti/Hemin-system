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
        'attachments',
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
            'attachments' => 'array',
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

    /** ئەوەی دراوە — بە دراوی قەرزەکە خۆی (هیچ گۆڕینێک نییە). */
    public function paid(): float
    {
        if ($this->status === 'paid') {
            return (float) $this->amount;
        }

        return (float) $this->paid_amount;
    }

    public function remainingIqd(): float
    {
        return $this->remaining();
    }

    public function totalIqd(): float
    {
        return (float) $this->amount;
    }

    public function paidIqd(): float
    {
        return $this->paid();
    }

    public function allAttachments(): array
    {
        $list = $this->attachments;
        if ((empty($list) || !is_array($list)) && !empty($this->image)) {
            $list = [$this->image];
        }

        return is_array($list) ? $list : [];
    }

    public static function isPdfPath(?string $path): bool
    {
        return !empty($path) && str_ends_with(strtolower($path), '.pdf');
    }

    public function isPdf(): bool
    {
        if (empty($this->image)) {
            return false;
        }

        return static::isPdfPath($this->image);
    }

    public function fileUrl(?string $path = null): ?string
    {
        $target = $path ?: $this->image;
        return $target ? asset('storage/' . $target) : null;
    }
}
