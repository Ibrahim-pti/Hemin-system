<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * جەردی کۆگا.
 */
class StockCount extends Model
{
    protected $fillable = [
        'count_no', 'warehouse_id', 'count_date', 'status', 'user_id', 'note',
    ];

    protected function casts(): array
    {
        return ['count_date' => 'date'];
    }

    public const STATUSES = [
        'draft' => 'کراوەیە',
        'posted' => 'پەسەندکراو',
    ];

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(StockCountItem::class);
    }

    public function movements(): MorphMany
    {
        return $this->morphMany(StockMovement::class, 'reference');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public static function nextCountNo(): string
    {
        return 'J-'.str_pad((string) ((static::max('id') ?? 0) + 1), 5, '0', STR_PAD_LEFT);
    }
}
