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
        'hours', 'overtime_hours', 'temporary_exit_hours', 'exit_reason',
        'fuel_expense', 'trip_destination', 'wage_snapshot', 'user_id', 'note',
    ];

    protected function casts(): array
    {
        return [
            'work_date' => 'date',
            'hours' => 'decimal:2',
            'overtime_hours' => 'decimal:2',
            'temporary_exit_hours' => 'decimal:2',
            'fuel_expense' => 'decimal:2',
            'wage_snapshot' => 'decimal:2',
        ];
    }

    public const STATUSES = [
        'present' => 'ئامادە',
        'absent' => 'ئامادە نەبوو',
        'holiday' => 'پشوو',
        'leave' => 'مۆڵەت',
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

    /** ئایا ئەم ڕۆژە هەینییە؟ (پشووی هەفتانەی کارگە) */
    public static function isWeeklyHoliday(string|CarbonInterface $date): bool
    {
        return Carbon::parse($date)->isFriday();
    }

    /**
     * حیسابی کاتژمێر لە هاتن و چوونەوە.
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

        $hours = round($in->floatDiffInHours($out), 2);
        $overtime = max(0, round($hours - self::STANDARD_HOURS, 2));

        return ['hours' => $hours, 'overtime' => $overtime];
    }
}
