<?php

namespace App\Models;

use App\Models\Concerns\Auditable;

use App\Models\Concerns\ConvertsCurrency;
use Illuminate\Contracts\Database\Query\Expression;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * ئیشی خاریجی — کارێک کە لە دەرەوەی کارگە دەدرێت (بۆیاخ، خەرات، لەیزەر...).
 */
class ExternalJob extends Model
{
    use Auditable;
    use ConvertsCurrency, SoftDeletes;

    protected $fillable = [
        'job_no', 'title', 'order_id', 'supplier_id', 'contractor_name',
        'description', 'cost', 'currency', 'exchange_rate', 'paid_amount',
        'status', 'started_at', 'finished_at', 'user_id', 'note',
    ];

    protected function casts(): array
    {
        return [
            'cost' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'exchange_rate' => 'decimal:2',
            'started_at' => 'date',
            'finished_at' => 'date',
        ];
    }

    public const STATUSES = [
        'open' => 'کراوەیە',
        'done' => 'تەواو بووە',
        'cancelled' => 'هەڵوەشێنراوە',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function costIqdExpression(): Expression
    {
        return static::iqdExpression('cost');
    }

    public function getCostIqdAttribute(): float
    {
        return $this->toIqd($this->cost);
    }

    public function getContractorLabelAttribute(): string
    {
        return $this->supplier?->name ?? $this->contractor_name ?? '—';
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function remaining(): float
    {
        return $this->cost_iqd - $this->toIqd($this->paid_amount);
    }

    public static function nextJobNo(): string
    {
        return 'X-'.str_pad((string) ((static::withTrashed()->max('id') ?? 0) + 1), 5, '0', STR_PAD_LEFT);
    }
}
