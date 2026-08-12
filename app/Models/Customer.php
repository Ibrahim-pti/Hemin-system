<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
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

    /** باڵانسی سەرەتایی بە دینار. */
    public function openingIqd(): float
    {
        if ($this->opening_currency === 'USD') {
            return (float) $this->opening_balance * ExchangeRate::current();
        }

        return (float) $this->opening_balance;
    }

    /**
     * قەرزی ئێستا بە دینار.
     * ئەرێنی = کڕیار قەرزاری کارگەیە. نەرێنی = کارگە قەرزاری کڕیارە.
     *
     * تێبینی: پێشەکی لە کاتی وەسڵدا وەک حەقدییەکی جیا تۆمار دەکرێت،
     * بۆیە لێرەدا دووجار ژمێردراو نییە.
     */
    public function balance(): float
    {
        $invoiced = $this->orders()
            ->whereNotIn('status', ['draft', 'cancelled'])
            ->sum(Order::totalIqdExpression());

        $paid = $this->payments()
            ->where('direction', 'in')
            ->sum('amount_iqd');

        return $this->openingIqd() + (float) $invoiced - (float) $paid;
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
