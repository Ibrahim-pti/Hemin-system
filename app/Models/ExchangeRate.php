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

    /** نرخی ئەم ڕۆژە — ئەگەر نەبوو خۆکار لە API وەردەگیرێت. */
    public static function current(): float
    {
        $today = now()->toDateString();
        $rate = static::query()
            ->whereDate('effective_date', $today)
            ->value('usd_to_iqd');

        if (! $rate) {
            $rate = \Illuminate\Support\Facades\Cache::remember('hemin.auto_live_rate', 1800, function () {
                try {
                    $service = app(\App\Services\ExchangeRateService::class);
                    $live = $service->fetchLiveRate();
                    if ($live && $live > 0) {
                        static::updateOrCreate(
                            ['effective_date' => now()->toDateString()],
                            ['usd_to_iqd' => $live, 'user_id' => auth()->id()]
                        );
                        return $live;
                    }
                } catch (\Throwable $e) {
                    // لە کاتی نەبوونی ئینتەرنێت دوایین نرخ بەکاردێت
                }
                return null;
            });
        }

        return (float) ($rate ?: (static::query()
            ->whereDate('effective_date', '<=', now())
            ->orderByDesc('effective_date')
            ->value('usd_to_iqd') ?: 1450.00));
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
