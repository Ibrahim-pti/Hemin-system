<?php

namespace App\Models;

use App\Models\Concerns\Auditable;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Purchase extends Model
{
    use Auditable;
    use SoftDeletes;

    protected $fillable = [
        'invoice_no', 'supplier_id', 'warehouse_id', 'purchase_date',
        'currency', 'exchange_rate', 'subtotal', 'discount_amount', 'total',
        'paid_amount', 'status', 'user_id', 'note', 'image', 'attachments',
    ];

    public function imageUrl(): ?string
    {
        return $this->fileUrl();
    }

    public function fileUrl(?string $path = null): ?string
    {
        $target = $path ?: $this->image;
        return $target ? asset('storage/' . $target) : null;
    }

    public static function isPdfPath(?string $path): bool
    {
        return !empty($path) && str_ends_with(strtolower($path), '.pdf');
    }

    public function isPdf(?string $path = null): bool
    {
        $target = $path ?: $this->image;
        return static::isPdfPath($target);
    }

    public function allAttachments(): array
    {
        $list = $this->attachments;
        if ((empty($list) || !is_array($list)) && !empty($this->image)) {
            $list = [$this->image];
        }

        return is_array($list) ? array_values(array_filter($list)) : [];
    }

    protected function casts(): array
    {
        return [
            'purchase_date' => 'date',
            'subtotal' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'total' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'exchange_rate' => 'decimal:2',
            'attachments' => 'array',
        ];
    }

    public const STATUSES = [
        'draft' => 'ڕەشنووس',
        'confirmed' => 'پەسەندکراو',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function movements(): MorphMany
    {
        return $this->morphMany(StockMovement::class, 'reference');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * کۆی ئەوەی دراوە بەم پسوولەیە — تەنها پارەدانەکانی هەمان دراوی پسوولەکە.
     * دۆلار و دینار بۆ یەکتری ناگۆڕدرێن.
     */
    public function paidTotal(): float
    {
        $payments = $this->relationLoaded('payments')
            ? $this->payments
            : $this->payments()->get();

        return (float) $payments
            ->where('direction', 'out')
            ->filter(fn ($p) => ($p->currency ?: 'IQD') === ($this->currency ?: 'IQD'))
            ->sum('amount');
    }

    public function remaining(): float
    {
        return max(0, (float) $this->total - $this->paidTotal());
    }

    public static function nextInvoiceNo(): string
    {
        $last = static::withTrashed()->max('id') ?? 0;

        return (string) ($last + 1);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        return $query->when($term, fn ($q) => $q->where(
            fn ($w) => $w->where('invoice_no', 'like', "%{$term}%")
                ->orWhereHas('supplier', fn ($s) => $s->where('name', 'like', "%{$term}%"))
        ));
    }
}
