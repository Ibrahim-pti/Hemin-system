<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * دێڕی وەسڵ — «ناوەڕۆک، ژمارە، نرخ، بڕی پارە» + قیاس.
 */
class OrderItem extends Model
{
    protected $fillable = [
        'order_id', 'description', 'image', 'item_id', 'pricing_mode',
        'meter', 'meter_price',
        'width', 'height', 'qty', 'computed_qty', 'unit_price', 'line_total', 'note',
    ];

    public function imageUrl(): ?string
    {
        if ($this->image) {
            return asset('storage/' . $this->image);
        }
        return $this->item?->imageUrl();
    }

    public function getHasMeterAttribute(): bool
    {
        return (float) ($this->meter ?? 0) > 0 && (float) ($this->meter_price ?? 0) > 0;
    }

    protected function casts(): array
    {
        return [
            'meter' => 'decimal:3',
            'meter_price' => 'decimal:2',
            'width' => 'decimal:3',
            'height' => 'decimal:3',
            'qty' => 'decimal:3',
            'computed_qty' => 'decimal:3',
            'unit_price' => 'decimal:2',
            'line_total' => 'decimal:2',
        ];
    }

    /** شێوازەکانی نرخ و یەکەی هەریەکەیان. */
    public const MODES = [
        'area' => 'مەتر دووجا (م²)',
        'length' => 'مەتر',
        'count' => 'دانە',
    ];

    public const MODE_UNITS = [
        'area' => 'م²',
        'length' => 'م',
        'count' => 'دانە',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * حیسابی بڕ بەپێی شێوازی نرخ:
     *   ڕووبەر → پانی × بەرزی × ژمارە
     *   درێژی  → پانی × ژمارە
     *   دانە   → ژمارە
     */
    public static function compute(string $mode, ?float $width, ?float $height, float $qty): float
    {
        return round(match ($mode) {
            'area' => (float) $width * (float) $height * $qty,
            'length' => (float) $width * $qty,
            default => $qty,
        }, 3);
    }

    public function getModeUnitAttribute(): string
    {
        return self::MODE_UNITS[$this->pricing_mode] ?? '';
    }

    public function getItemNameAttribute(): string
    {
        return $this->description ?: ($this->item?->name ?? 'کەلوپەل');
    }

    public function getUnitNameAttribute(): string
    {
        return $this->mode_unit ?: ($this->item?->unit?->name ?? 'دانە');
    }

    public function getMeasurementLabelAttribute(): string
    {
        return match ($this->pricing_mode) {
            'area' => number_format((float) $this->width, 2).' × '.number_format((float) $this->height, 2).' م',
            'length' => number_format((float) $this->width, 2).' م',
            default => '—',
        };
    }
}
