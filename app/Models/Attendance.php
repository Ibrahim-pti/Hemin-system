<?php

namespace App\Models;

use App\Models\Concerns\Auditable;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * تۆماری هاتن و چوونی ڕۆژانە — بە دەستی تۆمار دەکرێت.
 * هەینی بە بنەڕەت پشووە.
 */
class Attendance extends Model
{
    use Auditable;

    protected $fillable = [
        'employee_id', 'work_date', 'check_in', 'check_out', 'status',
        'hours', 'overtime_hours', 'late_minutes', 'temporary_exit_hours', 'exit_reason',
        'fuel_expense', 'deduction_amount', 'bonus_amount', 'trip_destination', 'wage_snapshot', 'user_id', 'note',
    ];

    protected function casts(): array
    {
        return [
            'work_date' => 'date',
            'hours' => 'decimal:2',
            'overtime_hours' => 'decimal:2',
            'late_minutes' => 'integer',
            'temporary_exit_hours' => 'decimal:2',
            'fuel_expense' => 'decimal:2',
            'deduction_amount' => 'decimal:2',
            'bonus_amount' => 'decimal:2',
            'wage_snapshot' => 'decimal:2',
        ];
    }

    public const STATUSES = [
        'present' => 'هاتووە',
        'absent' => 'نەهاتووە',
        'holiday' => 'پشوو',
        'leave' => 'مۆڵەت',
        'half_day' => 'نیو ڕۆژ',
    ];

    /** کاتژمێری کاری ئاسایی — زیاتر لەمە دەبێتە کاتی زیادە. */
    public const STANDARD_HOURS = 8;

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    /** ئایا ئەم ڕۆژە پشووی هەفتانەی کارگەیە؟ (داینامیک لە ڕێکخستنەکان) */
    public static function isWeeklyHoliday(string|CarbonInterface $date): bool
    {
        $holidaySetting = Setting::get('workshop_weekly_holiday', 'friday');
        if ($holidaySetting === 'none' || empty($holidaySetting)) {
            return false;
        }

        $dayOfWeek = strtolower(Carbon::parse($date)->format('l'));
        $holidays = array_map('trim', explode(',', strtolower($holidaySetting)));

        return in_array($dayOfWeek, $holidays, true);
    }

    /**
     * حیسابکردنی خولەکی تاخیربوون لە کاتی دەستپێکی دەوامەوە.
     */
    public static function calculateLateMinutes(?string $checkIn, ?string $workDate = null): int
    {
        if (! $checkIn) {
            return 0;
        }

        $workStart = Setting::get('workshop_work_start', '08:00');
        $graceMinutes = (int) Setting::get('workshop_late_grace_minutes', 0);

        $dateStr = $workDate ?: now()->toDateString();
        $startTime = Carbon::parse("{$dateStr} {$workStart}");
        $inTime = Carbon::parse("{$dateStr} {$checkIn}");

        if ($inTime->greaterThan($startTime)) {
            $diffMinutes = (int) $startTime->diffInMinutes($inTime, false);
            if ($diffMinutes > $graceMinutes) {
                return $diffMinutes;
            }
        }

        return 0;
    }

    /**
     * حیسابی کاتژمێر لە هاتن و چوونەوە بەپێی کاتژمێری دیاریکراوی کارگە.
     * ئەگەر چوون پێش هاتن بێت، وەک ڕۆژی دواتر دادەنرێت (شەوکار).
     */
    public static function calculateHours(?string $checkIn, ?string $checkOut): array
    {
        if (! $checkIn || ! $checkOut) {
            return ['hours' => 0.0, 'overtime' => 0.0];
        }

        $in = Carbon::parse($checkIn);
        $out = Carbon::parse($checkOut);

        if ($out->lessThanOrEqualTo($in)) {
            $out->addDay();
        }

        $standardHours = (float) Setting::get('workshop_work_hours', self::STANDARD_HOURS);
        $hours = round($in->floatDiffInHours($out), 2);
        $overtime = max(0, round($hours - $standardHours, 2));

        return ['hours' => $hours, 'overtime' => $overtime];
    }
}
