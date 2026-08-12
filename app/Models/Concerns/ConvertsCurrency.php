<?php

namespace App\Models\Concerns;

use Illuminate\Contracts\Database\Query\Expression;
use Illuminate\Support\Facades\DB;

/**
 * یارمەتیدەری گۆڕینی دراو.
 *
 * دینار دراوی بنەڕەتە. هەر تۆمارێکی دۆلاری نرخی گۆڕینی ئەو ڕۆژەی لەگەڵ خۆیدا
 * هەڵگرتووە، بۆیە کۆکردنەوە بە دینار هەمیشە هەمان ئەنجام دەداتەوە — تەنانەت
 * ئەگەر نرخی دۆلار دواتر بگۆڕێت.
 */
trait ConvertsCurrency
{
    /** دەربڕینی SQL بۆ گۆڕینی خانەیەکی پارە بۆ دینار. */
    public static function iqdExpression(string $column): Expression
    {
        $table = (new static)->getTable();

        return DB::raw(
            "CASE WHEN {$table}.currency = 'USD' "
            ."THEN {$table}.{$column} * COALESCE({$table}.exchange_rate, 0) "
            ."ELSE {$table}.{$column} END"
        );
    }

    /** گۆڕینی بڕێک بۆ دینار بەپێی دراو و نرخی ئەم تۆمارە. */
    public function toIqd(float|string|null $amount): float
    {
        $amount = (float) $amount;

        if ($this->currency === 'USD') {
            return $amount * (float) ($this->exchange_rate ?: 0);
        }

        return $amount;
    }
}
