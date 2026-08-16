<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockCountItem extends Model
{
    protected $fillable = [
        'stock_count_id', 'item_id', 'system_qty', 'counted_qty', 'difference', 'unit_price', 'note',
    ];

    protected function casts(): array
    {
        return [
            'system_qty' => 'decimal:3',
            'counted_qty' => 'decimal:3',
            'difference' => 'decimal:3',
            'unit_price' => 'decimal:2',
        ];
    }

    public function stockCount(): BelongsTo
    {
        return $this->belongsTo(StockCount::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
