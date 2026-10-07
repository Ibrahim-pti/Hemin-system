<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Support\Money;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use Auditable;
    use SoftDeletes;

    protected $fillable = [
        'name', 'phone', 'phone2', 'address', 'discount_percent',
        'opening_balance', 'opening_currency', 'is_active', 'note',
    ];

    protected function casts(): array
    {
        return [
            'discount_percent' => 'decimal:2',
            'opening_balance' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function payments(): MorphMany
    {
        return $this->morphMany(Payment::class, 'party');
    }

    public function oldDebts(): HasMany
    {
        return $this->hasMany(CustomerOldDebt::class)->latest('date')->latest('id');
    }

    /**
     * باڵانسی سەرەتایی (لەگەڵ حیساباتی پێشتر) — بە جیا بۆ هەر دراوێک.
     *
     * @return array{IQD: float, USD: float}
     */
    public function openingBalances(): array
    {
        $base = Money::of($this->opening_balance, $this->opening_currency);

        $oldDebts = Money::sumBy($this->oldDebts()->get(), fn ($d) => $d->remaining());

        return Money::add($base, $oldDebts);
    }

    /** کۆی وەسڵەکان بە جیا بۆ هەر دراوێک. */
    public function invoicedTotals(): array
    {
        return Money::sumQuery(
            $this->orders()->whereNotIn('status', ['draft', 'cancelled']),
            'total'
        );
    }

    /** کۆی حەقدییە وەرگیراوەکان بە جیا بۆ هەر دراوێک. */
    public function paidTotals(): array
    {
        return Money::sumQuery($this->payments()->where('direction', 'in'), 'amount');
    }

    /**
     * قەرزی ئێستا — بە جیا بۆ هەر دراوێک (دۆلار و دینار هەرگیز تێکەڵ ناکرێن).
     * ئەرێنی = کڕیار قەرزاری کارگەیە. نەرێنی = کارگە قەرزاری کڕیارە.
     *
     * تێبینی: پێشەکی لە کاتی وەسڵدا وەک حەقدییەکی جیا تۆمار دەکرێت،
     * بۆیە لێرەدا دووجار ژمێردراو نییە.
     *
     * @return array{IQD: float, USD: float}
     */
    public function balances(): array
    {
        return Money::sub(
            Money::add($this->openingBalances(), $this->invoicedTotals()),
            $this->paidTotals()
        );
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        return $query->when($term, fn ($q) => $q->where(
            fn ($w) => $w->where('name', 'like', "%{$term}%")
                ->orWhere('phone', 'like', "%{$term}%")
                ->orWhere('address', 'like', "%{$term}%")
        ));
    }
}
