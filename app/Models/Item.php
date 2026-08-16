<?php

namespace App\Models;

use App\Models\Concerns\Auditable;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Item extends Model
{
    use Auditable;
    use SoftDeletes;

    protected $fillable = [
        'code', 'name', 'item_category_id', 'unit_id', 'min_qty',
        'last_cost', 'cost_currency', 'purchase_date', 'sale_price', 'is_for_sale', 'is_active', 'note',
    ];

    protected function casts(): array
    {
        return [
            'min_qty' => 'decimal:3',
            'last_cost' => 'decimal:2',
            'purchase_date' => 'date',
            'sale_price' => 'decimal:2',
            'is_for_sale' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ItemCategory::class, 'item_category_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function movements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    /**
     * باڵانسی ئێستا — هەمیشە لە جوڵەکانەوە کۆدەکرێتەوە، نەک لە خانەیەکی هەڵگیراو.
     * ئەگەر کۆگایەک دیاری بکرێت، تەنها باڵانسی ئەو کۆگایە دەداتەوە.
     */
    public function stockQty(?int $warehouseId = null): float
    {
        $query = $this->movements()
            ->when($warehouseId, fn ($q) => $q->where('warehouse_id', $warehouseId));

        $in = (clone $query)->where('direction', 'in')->sum('qty');
        $out = (clone $query)->where('direction', 'out')->sum('qty');

        return (float) $in - (float) $out;
    }

    /**
     * باڵانس بۆ لیستێکی کاڵا بە یەک کوێری — بۆ ئەوەی N+1 دروست نەبێت.
     * دوای ئەمە `$item->stock_qty` بەردەستە.
     */
    public function scopeWithStock(Builder $query, ?int $warehouseId = null): Builder
    {
        $sum = fn (string $direction) => StockMovement::query()
            ->selectRaw('COALESCE(SUM(qty), 0)')
            ->whereColumn('stock_movements.item_id', 'items.id')
            ->where('direction', $direction)
            ->when($warehouseId, fn ($q) => $q->where('warehouse_id', $warehouseId));

        return $query
            ->select('items.*')
            ->selectSub($sum('in'), 'qty_in')
            ->selectSub($sum('out'), 'qty_out');
    }

    /** پێویستە withStock() بەکاربهێنرێت، ئەگەر نا خۆی دەیژمێرێت. */
    public function getStockQtyAttribute(): float
    {
        if (isset($this->attributes['qty_in'])) {
            return (float) $this->attributes['qty_in'] - (float) $this->attributes['qty_out'];
        }

        return $this->stockQty();
    }

    /** ئایا لە سنووری ئاگاداری کەمتر بووەتەوە؟ */
    public function getIsLowAttribute(): bool
    {
        return $this->min_qty > 0 && $this->stock_qty <= (float) $this->min_qty;
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeForSale(Builder $query): Builder
    {
        return $query->where('is_for_sale', true);
    }

    public function scopeRawMaterials(Builder $query): Builder
    {
        return $query->where('is_for_sale', false);
    }

    /** کۆدی داهاتوو بە شێوەیەکی یەکتا. */
    public static function nextCode(): string
    {
        $maxId = (int) (static::withTrashed()->max('id') ?? 0);
        $next = $maxId + 1;
        $code = 'M-'.str_pad((string) $next, 4, '0', STR_PAD_LEFT);
        while (static::withTrashed()->where('code', $code)->exists()) {
            $next++;
            $code = 'M-'.str_pad((string) $next, 4, '0', STR_PAD_LEFT);
        }

        return $code;
    }

    /** گەڕان بە ناو یان کۆد. */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        return $query->when($term, fn ($q) => $q->where(
            fn ($w) => $w->where('name', 'like', "%{$term}%")->orWhere('code', 'like', "%{$term}%")
        ));
    }
}
