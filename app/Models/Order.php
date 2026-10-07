<?php

namespace App\Models;

use App\Models\Concerns\Auditable;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * وەسڵی کڕیار — هەمان پێکهاتەی وەسڵە چاپکراوەکەی کارگە.
 */
class Order extends Model
{
    use Auditable;
    use SoftDeletes;

    protected $fillable = [
        'invoice_no', 'customer_id', 'order_date', 'delivery_date', 'status',
        'currency', 'exchange_rate', 'subtotal', 'discount_percent',
        'discount_amount', 'total', 'prepaid_amount', 'address_snapshot',
        'user_id', 'note',
    ];

    protected function casts(): array
    {
        return [
            'order_date' => 'date',
            'delivery_date' => 'date',
            'subtotal' => 'decimal:2',
            'discount_percent' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'total' => 'decimal:2',
            'prepaid_amount' => 'decimal:2',
            'exchange_rate' => 'decimal:2',
        ];
    }

    /** ناوی کوردی دۆخەکان. */
    public const STATUSES = [
        'draft' => 'ڕەشنووس',
        'confirmed' => 'پەسەندکراو',
        'in_production' => 'لە بەرهەمهێناندا',
        'ready' => 'ئامادەیە',
        'delivered' => 'گەیەنراوە',
        'cancelled' => 'هەڵوەشێنراوە',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function externalJobs(): HasMany
    {
        return $this->hasMany(ExternalJob::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * کۆی ئەوەی دراوە بەم وەسڵە — تەنها حەقدییەکانی هەمان دراوی وەسڵەکە.
     * دۆلار و دینار بۆ یەکتری ناگۆڕدرێن.
     */
    public function paidTotal(): float
    {
        $payments = $this->relationLoaded('payments')
            ? $this->payments
            : $this->payments()->get();

        return (float) $payments
            ->where('direction', 'in')
            ->filter(fn ($p) => ($p->currency ?: 'IQD') === ($this->currency ?: 'IQD'))
            ->sum('amount');
    }

    /** کۆی ئەوەی دراوە — وەک ناوە کۆنەکە پارێزراوە. */
    public function paidAmount(): float
    {
        return $this->paidTotal();
    }

    /** ئەوەی ماوە بەپێی دراوی وەسڵەکە — قەرزی ئەم وەسڵەیە. */
    public function remaining(): float
    {
        return max(0, (float) $this->total - $this->paidTotal());
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    /** ژمارەی وەسڵی داهاتوو — بە ڕیزبەندی، وەک دەفتەرە چاپکراوەکە. */
    public static function nextInvoiceNo(): string
    {
        $last = static::withTrashed()->max('id') ?? 0;

        return (string) ($last + 1);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        return $query->when($term, fn ($q) => $q->where(
            fn ($w) => $w->where('invoice_no', 'like', "%{$term}%")
                ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%{$term}%")
                    ->orWhere('phone', 'like', "%{$term}%"))
        ));
    }
}
