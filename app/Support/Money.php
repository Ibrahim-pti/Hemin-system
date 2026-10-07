<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * کۆکردنەوەی پارە بە جیا بۆ هەر دراوێک.
 *
 * دۆلار و دینار هەرگیز تێکەڵ ناکرێن و بۆ یەکتری ناگۆڕدرێن:
 * قەرزی دۆلار تەنها بە دۆلار دەدرێتەوە و قەرزی دینار تەنها بە دینار.
 * هەموو کۆیەک شێوەی ['IQD' => …, 'USD' => …]ـی هەیە.
 */
final class Money
{
    public const CURRENCIES = ['IQD', 'USD'];

    /** کۆی بەتاڵ بۆ هەردوو دراو. */
    public static function zero(): array
    {
        return ['IQD' => 0.0, 'USD' => 0.0];
    }

    /** ناوی دراوی ڕاست — هەر شتێک جگە لە USD دینارە. */
    public static function cur(?string $currency): string
    {
        return $currency === 'USD' ? 'USD' : 'IQD';
    }

    /** دروستکردنی کۆ لە بڕێک بە دراوێکی دیاریکراو. */
    public static function of(float|string|null $amount, ?string $currency): array
    {
        $totals = self::zero();
        $totals[self::cur($currency)] += (float) $amount;

        return $totals;
    }

    public static function add(array ...$parts): array
    {
        $totals = self::zero();
        foreach ($parts as $part) {
            foreach (self::CURRENCIES as $c) {
                $totals[$c] += (float) ($part[$c] ?? 0);
            }
        }

        return $totals;
    }

    public static function sub(array $a, array $b): array
    {
        $totals = self::zero();
        foreach (self::CURRENCIES as $c) {
            $totals[$c] = (float) ($a[$c] ?? 0) - (float) ($b[$c] ?? 0);
        }

        return $totals;
    }

    /** تەنها بڕە ئەرێنییەکان (بۆ کۆکردنەوەی قەرز). */
    public static function positive(array $a): array
    {
        $totals = self::zero();
        foreach (self::CURRENCIES as $c) {
            $totals[$c] = max(0.0, (float) ($a[$c] ?? 0));
        }

        return $totals;
    }

    /** ئایا هیچ دراوێک بڕێکی ئەرێنی هەیە؟ */
    public static function hasPositive(array $a, float $eps = 0.005): bool
    {
        foreach (self::CURRENCIES as $c) {
            if ((float) ($a[$c] ?? 0) > $eps) {
                return true;
            }
        }

        return false;
    }

    public static function isZero(array $a, float $eps = 0.005): bool
    {
        foreach (self::CURRENCIES as $c) {
            if (abs((float) ($a[$c] ?? 0)) > $eps) {
                return false;
            }
        }

        return true;
    }

    /**
     * کۆکردنەوەی لیستێک بە جیا بۆ هەر دراوێک.
     *
     * @param  iterable  $items
     * @param  callable|string  $amount  ناوی خانە یان فەنکشن
     * @param  callable|string  $currency  ناوی خانە یان فەنکشن
     */
    public static function sumBy(iterable $items, callable|string $amount, callable|string $currency = 'currency'): array
    {
        $totals = self::zero();
        foreach ($items as $item) {
            $value = is_callable($amount) ? $amount($item) : data_get($item, $amount);
            $curr = is_callable($currency) ? $currency($item) : data_get($item, $currency);
            $totals[self::cur($curr)] += (float) $value;
        }

        return $totals;
    }

    /** کۆکردنەوە لە بنکەی داتا بە GROUP BY currency. */
    public static function sumQuery(Builder|Relation $query, string $column): array
    {
        $builder = $query instanceof Relation ? $query->getQuery() : $query;
        $table = $builder->getModel()->getTable();

        $rows = (clone $builder)->toBase()
            ->reorder()
            ->selectRaw("{$table}.currency as cur, SUM({$table}.{$column}) as total")
            ->groupBy("{$table}.currency")
            ->pluck('total', 'cur');

        $totals = self::zero();
        foreach ($rows as $curr => $total) {
            $totals[self::cur($curr)] += (float) $total;
        }

        return $totals;
    }

    /** پیشاندانی کۆیەک — هەر دراوێک بە جیا (بێ گۆڕین). */
    public static function format(array $totals, string $separator = ' · ', bool $hideZero = true): string
    {
        $parts = [];
        foreach (['IQD', 'USD'] as $c) {
            $v = (float) ($totals[$c] ?? 0);
            if ($hideZero && abs($v) < 0.005) {
                continue;
            }
            $parts[] = fmt_money($v, $c);
        }

        return $parts ? implode($separator, $parts) : fmt_money(0, 'IQD');
    }
}
