<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * دەفتەری جوڵەی مەخزەن — سەرچاوەی یەکتای هەموو باڵانسێک.
 * هیچ کاتێک دەستکاری ناکرێت؛ ئەگەر هەڵەیەک ڕوویدا، جوڵەی پێچەوانە دروست دەکرێت.
 */
class StockMovement extends Model
{
    protected $fillable = [
        'item_id', 'warehouse_id', 'direction', 'qty', 'reason',
        'unit_cost', 'currency', 'exchange_rate',
        'reference_type', 'reference_id', 'moved_at', 'user_id', 'note',
    ];

    protected function casts(): array
    {
        return [
            'qty' => 'decimal:3',
            'unit_cost' => 'decimal:2',
            'exchange_rate' => 'decimal:2',
            'moved_at' => 'date',
        ];
    }

    /** ناوی کوردی هۆکارەکان — لە UI و راپۆرتەکاندا بەکاردێت. */
    public const REASONS = [
        'opening' => 'باڵانسی سەرەتایی',
        'purchase' => 'کڕین',
        'purchase_return' => 'گەڕاندنەوەی کڕین',
        'sale' => 'فرۆشتن',
        'sale_return' => 'گەڕاندنەوەی فرۆشتن',
        'transfer' => 'گواستنەوە',
        'adjustment' => 'ڕاستکردنەوەی جەرد',
        'production' => 'بەکارهێنان لە بەرهەمهێنان',
        'damage' => 'تێکچوون',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    public function getReasonLabelAttribute(): string
    {
        return self::REASONS[$this->reason] ?? $this->reason;
    }

    /** بڕ بە ئیشارەت — بۆ پیشاندان لە مێژووی کاڵا. */
    public function getSignedQtyAttribute(): float
    {
        return $this->direction === 'in' ? (float) $this->qty : -(float) $this->qty;
    }
}
