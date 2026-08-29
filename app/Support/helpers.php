<?php

if (! function_exists('fmt_money')) {
    /** پیشاندانی پارە — دینار بێ کەسر، دۆلار بە دوو خاڵ. */
    function fmt_money(float|string|null $amount, ?string $currency = 'IQD'): string
    {
        $amount = (float) $amount;
        $curr = $currency ?: 'IQD';

        return $curr === 'USD'
            ? '$'.number_format($amount, 2)
            : number_format($amount, 0).' د.ع';
    }
}

if (! function_exists('fmt_num')) {
    /** ژمارەی ساکار بێ ناوی دراو. */
    function fmt_num(float|string|null $amount, int $decimals = 0): string
    {
        return number_format((float) $amount, $decimals);
    }
}

if (! function_exists('fmt_qty')) {
    /** بڕ — کەسرە بێهودەکان لادەبرێن (12.500 → 12.5). */
    function fmt_qty(float|string|null $qty): string
    {
        $qty = (float) $qty;

        return rtrim(rtrim(number_format($qty, 3), '0'), '.');
    }
}

if (! function_exists('fmt_date')) {
    /** بەرواری کوردی — ڕۆژ/مانگ/ساڵ. */
    function fmt_date(mixed $date): string
    {
        if (! $date) {
            return '—';
        }

        return \Illuminate\Support\Carbon::parse($date)->format('Y/m/d');
    }
}
