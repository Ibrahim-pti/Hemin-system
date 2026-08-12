<?php

namespace App\Models;

use App\Models\Concerns\Auditable;

use App\Models\Concerns\ConvertsCurrency;
use Illuminate\Contracts\Database\Query\Expression;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Purchase extends Model
{
    use Auditable;
    use ConvertsCurrency, SoftDeletes;

    protected $fillable = [
        'invoice_no', 'supplier_id', 'warehouse_id', 'purchase_date',
        'currency', 'exchange_rate', 'subtotal', 'discount_amount', 'total',
        'paid_amount', 'status', 'user_id', 'note',
    ];

    protected function casts(): array
    {
        return [
            'purchase_date' => 'date',
            'subtotal' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'total' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'exchange_rate' => 'decimal:2',
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

    public static function totalIqdExpression(): Expression
    {
        return static::iqdExpression('total');
    }

    public function getTotalIqdAttribute(): float
    {
        return $this->toIqd($this->total);
    }

    public function paidTotal(): float
    {
        return (float) $this->payments()->where('direction', 'out')->sum('amount_iqd');
    }

    public function remaining(): float
    {
        return $this->total_iqd - $this->paidTotal();
    }

    public static function nextInvoiceNo(): string
    {
        return 'K-'.str_pad((string) ((static::withTrashed()->max('id') ?? 0) + 1), 5, '0', STR_PAD_LEFT);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        return $query->when($term, fn ($q) => $q->where(
            fn ($w) => $w->where('invoice_no', 'like', "%{$term}%")
                ->orWhereHas('supplier', fn ($s) => $s->where('name', 'like', "%{$term}%"))
        ));
    }
}
