<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Support\Money;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use Auditable;
    use SoftDeletes;

    protected $fillable = [
        'name', 'phone', 'phone2', 'address',
        'opening_balance', 'opening_currency', 'is_active', 'note',
    ];

    protected function casts(): array
    {
        return [
            'opening_balance' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }

    public function externalJobs(): HasMany
    {
        return $this->hasMany(ExternalJob::class);
    }

    public function payments(): MorphMany
    {
        return $this->morphMany(Payment::class, 'party');
    }

    /** باڵانسی سەرەتایی بە دراوی خۆی. */
    public function openingBalances(): array
    {
        return Money::of($this->opening_balance, $this->opening_currency);
    }

    /** کۆی کڕینە پەسەندکراوەکان بە جیا بۆ هەر دراوێک. */
    public function totalPurchases(): array
    {
        return Money::sumQuery($this->purchases()->where('status', 'confirmed'), 'total');
    }

    /** کۆی پارەی دراو بە جیا بۆ هەر دراوێک. */
    public function totalPaid(): array
    {
        return Money::sumQuery($this->payments()->where('direction', 'out'), 'amount');
    }

    /** کۆی ئیشە دەرەکییەکان بە جیا بۆ هەر دراوێک. */
    public function totalJobs(): array
    {
        return Money::sumQuery($this->externalJobs()->where('status', '!=', 'cancelled'), 'cost');
    }

    /**
     * قەرزی ئێستا — بە جیا بۆ هەر دراوێک (هیچ گۆڕینێک نییە).
     * ئەرێنی = کارگە قەرزاری ئەم فرۆشیارەیە.
     *
     * @return array{IQD: float, USD: float}
     */
    public function balances(): array
    {
        return Money::sub(
            Money::add($this->openingBalances(), $this->totalPurchases(), $this->totalJobs()),
            $this->totalPaid()
        );
    }

    public function balance(?string $currency = null): float
    {
        $b = $this->balances();
        if ($currency !== null) {
            return (float) ($b[Money::cur($currency)] ?? 0);
        }

        return (float) ($b['IQD'] ?? 0);
    }

    public function hasDebt(): bool
    {
        return Money::hasPositive($this->balances());
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        return $query->when($term, fn ($q) => $q->where(
            fn ($w) => $w->where('name', 'like', "%{$term}%")->orWhere('phone', 'like', "%{$term}%")
        ));
    }
}
