<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CashBox extends Model
{
    protected $fillable = ['name', 'currency', 'opening_balance', 'is_active'];

    protected function casts(): array
    {
        return [
            'opening_balance' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(CashTransaction::class);
    }

    public function closings(): HasMany
    {
        return $this->hasMany(CashClosing::class);
    }

    /**
     * باڵانسی قاسە — لە جوڵەکانەوە کۆدەکرێتەوە.
     * ئەگەر ڕۆژێک دیاری بکرێت، باڵانس تا کۆتایی ئەو ڕۆژە دەداتەوە.
     */
    public function balance(?string $upToDate = null): float
    {
        $query = $this->transactions()
            ->when($upToDate, fn ($q) => $q->whereDate('occurred_at', '<=', $upToDate));

        $in = (float) (clone $query)->where('direction', 'in')->sum('amount');
        $out = (float) (clone $query)->where('direction', 'out')->sum('amount');

        return (float) $this->opening_balance + $in - $out;
    }

    /** کۆی داهات و خەرجی ڕۆژێکی دیاریکراو. */
    public function dayTotals(string $date): array
    {
        $query = $this->transactions()->whereDate('occurred_at', $date);

        return [
            'in' => (float) (clone $query)->where('direction', 'in')->sum('amount'),
            'out' => (float) (clone $query)->where('direction', 'out')->sum('amount'),
        ];
    }
}
