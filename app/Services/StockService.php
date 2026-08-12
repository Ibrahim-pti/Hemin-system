<?php

namespace App\Services;

use App\Models\Item;
use App\Models\Purchase;
use App\Models\StockCount;
use App\Models\StockMovement;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * هەموو دەستکارییەکی مەخزەن لێرەوە دەڕوات.
 *
 * یاسای سەرەکی: هیچ کاتێک باڵانس ڕاستەوخۆ دەستکاری ناکرێت — تەنها جوڵەی نوێ
 * زیاد دەکرێت. بۆ هەڵوەشاندنەوەش، جوڵەکان دەسڕدرێنەوە یان پێچەوانەیان دروست دەبێت.
 */
class StockService
{
    /** تۆمارکردنی جوڵەیەکی تاک. */
    public function record(
        int $itemId,
        int $warehouseId,
        string $direction,
        float $qty,
        string $reason,
        ?Model $reference = null,
        array $extra = [],
    ): StockMovement {
        return StockMovement::create(array_merge([
            'item_id' => $itemId,
            'warehouse_id' => $warehouseId,
            'direction' => $direction,
            'qty' => abs($qty),
            'reason' => $reason,
            'reference_type' => $reference ? $reference::class : null,
            'reference_id' => $reference?->getKey(),
            'moved_at' => $extra['moved_at'] ?? now()->toDateString(),
            'user_id' => Auth::id(),
        ], $extra));
    }

    /**
     * پەسەندکردنی پسوولەی کڕین — کاڵاکان دەچنە مەخزەنەوە.
     * هەروەها دوایین تێچووی هەر کاڵایەک نوێ دەکاتەوە.
     */
    public function postPurchase(Purchase $purchase): void
    {
        DB::transaction(function () use ($purchase) {
            $purchase->loadMissing('items');

            foreach ($purchase->items as $line) {
                $this->record(
                    itemId: $line->item_id,
                    warehouseId: $purchase->warehouse_id,
                    direction: 'in',
                    qty: (float) $line->qty,
                    reason: 'purchase',
                    reference: $purchase,
                    extra: [
                        'unit_cost' => $line->unit_price,
                        'currency' => $purchase->currency,
                        'exchange_rate' => $purchase->exchange_rate,
                        'moved_at' => $purchase->purchase_date->toDateString(),
                    ],
                );

                Item::whereKey($line->item_id)->update([
                    'last_cost' => $line->unit_price,
                    'cost_currency' => $purchase->currency,
                ]);
            }

            $purchase->update(['status' => 'confirmed']);
        });
    }

    /** هەڵوەشاندنەوەی پسوولەی کڕین — جوڵەکانی دەسڕێتەوە. */
    public function unpostPurchase(Purchase $purchase): void
    {
        DB::transaction(function () use ($purchase) {
            $purchase->movements()->delete();
            $purchase->update(['status' => 'draft']);
        });
    }

    /**
     * پەسەندکردنی جەرد — جیاوازییەکان دەبنە جوڵەی ڕاستکردنەوە.
     * جیاوازی ئەرێنی = زیادە لە کۆگا، نەرێنی = کەمە.
     */
    public function postStockCount(StockCount $count): void
    {
        DB::transaction(function () use ($count) {
            $count->loadMissing('items');

            foreach ($count->items as $line) {
                $difference = (float) $line->counted_qty - (float) $line->system_qty;

                if (abs($difference) < 0.0005) {
                    continue;
                }

                $this->record(
                    itemId: $line->item_id,
                    warehouseId: $count->warehouse_id,
                    direction: $difference > 0 ? 'in' : 'out',
                    qty: abs($difference),
                    reason: 'adjustment',
                    reference: $count,
                    extra: [
                        'moved_at' => $count->count_date->toDateString(),
                        'note' => 'ڕاستکردنەوەی جەردی '.$count->count_no,
                    ],
                );
            }

            $count->update(['status' => 'posted']);
        });
    }

    /** باڵانسی بەردەست بۆ کاڵایەک لە کۆگایەکدا. */
    public function available(int $itemId, ?int $warehouseId = null): float
    {
        return Item::find($itemId)?->stockQty($warehouseId) ?? 0.0;
    }
}
