<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * ڕێکخستنی گشتی — زانیاری کارگە کە لەسەر وەسڵ و حەقدی چاپ دەکرێت.
 */
class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    public const CACHE_KEY = 'hemin.settings';

    /** بەهای ڕێکخستنێک. */
    public static function get(string $key, mixed $default = null): mixed
    {
        return static::all_()[$key] ?? $default;
    }

    /** دانانی بەها و پاککردنەوەی کاش. */
    public static function put(string $key, mixed $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget(self::CACHE_KEY);
    }

    /** هەموو ڕێکخستنەکان وەک ئارەیەک — کاش دەکرێت. */
    public static function all_(): array
    {
        return Cache::rememberForever(
            self::CACHE_KEY,
            fn () => static::query()->pluck('value', 'key')->all()
        );
    }

    public static function allKeyValue(): array
    {
        return static::all_();
    }

    /** بەهای بنەڕەت — لە Seederـدا دادەنرێت. */
    public const DEFAULTS = [
        'company_name' => 'کارگەی ئاسنگەری هێمن',
        'company_tagline' => 'بۆ دروستکردنی دەرگا و مەحەجەرە و کەپر و مەسعەد بەشێوازێکی هەندەسی',
        'company_phone' => '٠٧٥٠٤٥٦٨٥٥٦',
        'company_phone2' => '٠٧٥٠١٢٠١١١٠',
        'company_address' => 'هەولێر — ١٠٠م بەرامبەر گۆرستانی شێخ ئەحمەد',
        'invoice_footer' => 'هەڵە دەگەڕێتەوە بۆ هەردوو لا',
        'low_stock_alert' => '1',
        'workshop_work_start' => '08:00',
        'workshop_work_end' => '17:00',
        'workshop_work_hours' => '8',
        'workshop_weekly_holiday' => 'friday',
        'workshop_overtime_multiplier' => '1.0',
        'workshop_half_day_deduction_type' => 'percentage',
        'workshop_half_day_deduction_rate' => '0',
        'workshop_absent_deduction_type' => 'none',
        'workshop_absent_deduction_rate' => '0',
    ];
}
