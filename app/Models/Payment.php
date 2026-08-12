<?php

namespace App\Models;

use App\Models\Concerns\Auditable;

use App\Models\Concerns\ConvertsCurrency;
use App\Support\KurdishNumber;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * حەقدی — بەڵگەنامەی وەرگرتن یان دانی پارە، کە چاپ دەکرێت.
 */
class Payment extends Model
{
    use Auditable;
    use ConvertsCurrency, SoftDeletes;

    protected $fillable = [
        'voucher_no', 'direction', 'party_type', 'party_id', 'party_name',
        'order_id', 'purchase_id', 'amount', 'currency', 'exchange_rate',
        'amount_iqd', 'cash_box_id', 'paid_at', 'user_id', 'note',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'amount_iqd' => 'decimal:2',
            'exchange_rate' => 'decimal:2',
            'paid_at' => 'date',
        ];
    }

    public const DIRECTIONS = [
        'in' => 'وەرگرتنی پارە',
        'out' => 'دانی پارە',
    ];

    public function party(): MorphTo
    {
        return $this->morphTo();
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    public function cashBox(): BelongsTo
    {
        return $this->belongsTo(CashBox::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** ناوی ئەو کەسەی پارەی لێ وەرگیراوە یان پێی دراوە. */
    public function getPartyLabelAttribute(): string
    {
        return $this->party?->name ?? $this->party_name ?? '—';
    }

    public function getDirectionLabelAttribute(): string
    {
        return self::DIRECTIONS[$this->direction] ?? $this->direction;
    }

    /** بڕەکە بە نووسینی کوردی — بۆ سەر حەقدی. */
    public function getAmountInWordsAttribute(): string
    {
        $currency = $this->currency === 'USD' ? 'دۆلار' : 'دینار';

        return KurdishNumber::spell((int) round((float) $this->amount)).' '.$currency;
    }

    public static function nextVoucherNo(): string
    {
        return 'H-'.str_pad((string) ((static::withTrashed()->max('id') ?? 0) + 1), 5, '0', STR_PAD_LEFT);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        return $query->when($term, fn ($q) => $q->where(
            fn ($w) => $w->where('voucher_no', 'like', "%{$term}%")
                ->orWhere('party_name', 'like', "%{$term}%")
        ));
    }
}
