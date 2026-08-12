<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExchangeRate extends Model
{
    protected $fillable = ['effective_date', 'usd_to_iqd', 'user_id'];

    protected function casts(): array
    {
        return [
            'effective_date' => 'date',
            'usd_to_iqd' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** نرخی ئەم ڕۆژە — یان نزیکترین نرخی پێشوو. */
    public static function current(): float
    {
        return (float) (static::query()
            ->whereDate('effective_date', '<=', now())
            ->orderByDesc('effective_date')
            ->value('usd_to_iqd') ?: 0);
    }

    /** نرخی ڕۆژێکی دیاریکراو — بۆ تۆمارکردنی مامەڵەی کۆن. */
    public static function forDate(string $date): float
    {
        return (float) (static::query()
            ->whereDate('effective_date', '<=', $date)
            ->orderByDesc('effective_date')
            ->value('usd_to_iqd') ?: static::current());
    }
}
