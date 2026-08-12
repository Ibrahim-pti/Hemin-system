<?php

namespace App\Support;

/**
 * نووسینی ژمارە بە پیتی کوردی — بۆ سەر حەقدی.
 * نموونە: 1230000 → «یەک ملیۆن و دوو سەد و سی هەزار»
 */
class KurdishNumber
{
    private const ONES = [
        1 => 'یەک', 2 => 'دوو', 3 => 'سێ', 4 => 'چوار', 5 => 'پێنج',
        6 => 'شەش', 7 => 'حەوت', 8 => 'هەشت', 9 => 'نۆ',
    ];

    private const TEENS = [
        10 => 'دە', 11 => 'یازدە', 12 => 'دوازدە', 13 => 'سێزدە', 14 => 'چواردە',
        15 => 'پازدە', 16 => 'شازدە', 17 => 'حەڤدە', 18 => 'هەژدە', 19 => 'نۆزدە',
    ];

    private const TENS = [
        2 => 'بیست', 3 => 'سی', 4 => 'چل', 5 => 'پەنجا',
        6 => 'شەست', 7 => 'حەفتا', 8 => 'هەشتا', 9 => 'نەوەد',
    ];

    private const HUNDREDS = [
        1 => 'سەد', 2 => 'دوو سەد', 3 => 'سێ سەد', 4 => 'چوار سەد', 5 => 'پێنج سەد',
        6 => 'شەش سەد', 7 => 'حەوت سەد', 8 => 'هەشت سەد', 9 => 'نۆ سەد',
    ];

    /** پێوانەکان لە گەورەوە بۆ بچووک. */
    private const SCALES = [
        1_000_000_000 => 'ملیار',
        1_000_000 => 'ملیۆن',
        1_000 => 'هەزار',
    ];

    public static function spell(int $number): string
    {
        if ($number === 0) {
            return 'سفر';
        }

        $prefix = '';
        if ($number < 0) {
            $prefix = 'سالب ';
            $number = abs($number);
        }

        $parts = [];

        foreach (self::SCALES as $value => $scaleWord) {
            if ($number >= $value) {
                $count = intdiv($number, $value);
                $number %= $value;

                // «هەزار» بە تەنها دەنووسرێت، نەک «یەک هەزار».
                $parts[] = ($count === 1 && $scaleWord === 'هەزار')
                    ? $scaleWord
                    : self::underThousand($count).' '.$scaleWord;
            }
        }

        if ($number > 0) {
            $parts[] = self::underThousand($number);
        }

        return $prefix.implode(' و ', $parts);
    }

    /** ژمارەی نێوان ١ و ٩٩٩. */
    private static function underThousand(int $number): string
    {
        $parts = [];

        if ($number >= 100) {
            $parts[] = self::HUNDREDS[intdiv($number, 100)];
            $number %= 100;
        }

        if ($number >= 20) {
            $tens = self::TENS[intdiv($number, 10)];
            $unit = $number % 10;
            $parts[] = $unit > 0 ? $tens.' و '.self::ONES[$unit] : $tens;
        } elseif ($number >= 10) {
            $parts[] = self::TEENS[$number];
        } elseif ($number > 0) {
            $parts[] = self::ONES[$number];
        }

        return implode(' و ', $parts);
    }
}
