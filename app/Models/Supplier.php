<?php

namespace App\Models;

use App\Models\Concerns\Auditable;

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

    public function openingIqd(): float
    {
        if ($this->opening_currency === 'USD') {
            return (float) $this->opening_balance * ExchangeRate::current();
        }

        return (float) $this->opening_balance;
    }

    public function totalPurchases(): float
    {
        return (float) $this->purchases()
            ->where('status', 'confirmed')
            ->sum(Purchase::totalIqdExpression());
    }

    public function totalPaid(): float
    {
        return (float) $this->payments()
            ->where('direction', 'out')
            ->sum('amount_iqd');
    }

    public function totalJobs(): float
    {
        return (float) $this->externalJobs()
            ->where('status', '!=', 'cancelled')
            ->sum(ExternalJob::costIqdExpression());
    }

    /**
     * قەرزی ئێستا بە دینار.
     * ئەرێنی = کارگە قەرزاری ئەم فرۆشیارەیە.
     */
    public function balance(): float
    {
        return $this->openingIqd() + $this->totalPurchases() + $this->totalJobs() - $this->totalPaid();
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
