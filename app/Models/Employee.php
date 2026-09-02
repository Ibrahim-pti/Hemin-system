<?php

namespace App\Models;

use App\Models\Concerns\Auditable;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use Auditable;
    use SoftDeletes;

    protected $fillable = [
        'name', 'phone', 'job_title', 'salary_type', 'daily_wage', 'wage_currency',
        'hire_date', 'is_active', 'note',
    ];

    protected function casts(): array
    {
        return [
            'daily_wage' => 'decimal:2',
            'hire_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public const JOB_TITLES = [
        'master' => 'وەستا',
        'porter' => 'حەمەڵ',
        'helper' => 'یاریدەدەر',
        'driver' => 'شۆفێر',
        'other' => 'هیتر',
    ];

    public const SALARY_TYPES = [
        'daily' => 'ڕۆژانە',
        'weekly' => 'حەفتانە',
        'monthly' => 'مانگانە',
    ];

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function payments(): MorphMany
    {
        return $this->morphMany(Payment::class, 'party');
    }

    public function getJobTitleLabelAttribute(): string
    {
        return self::JOB_TITLES[$this->job_title] ?? $this->job_title;
    }

    public function getSalaryTypeLabelAttribute(): string
    {
        return self::SALARY_TYPES[$this->salary_type ?? 'daily'] ?? 'ڕۆژانە';
    }

    /** حیسابکردنی حەقدەستی ڕۆژانەی هاوتا بۆ ئامادەبوون */
    public function getEffectiveDailyWageAttribute(): float
    {
        $amount = (float) $this->daily_wage;
        $type = $this->salary_type ?? 'daily';

        // ئەگەر مووچەی مانگانە زیاتر بێت لە ١٠٠،٠٠٠ د.ع ئەوا دابەشی ٣٠ ڕۆژ دەکرێت بۆ دەرهێنانی ڕۆژانە
        if ($type === 'monthly' && $amount >= 100000) {
            return round($amount / 30);
        }

        // ئەگەر مووچەی حەفتانە زیاتر بێت لە ٥٠،٠٠٠ د.ع ئەوا دابەشی ٦ دەکرێت
        if ($type === 'weekly' && $amount >= 50000) {
            return round($amount / 6);
        }

        return round($amount);
    }

    /** حەقدەستی کۆکراوەی ماوەیەک (تەنها ڕۆژانی ئامادەبوون). */
    public function earnedBetween(string $from, string $to): float
    {
        return (float) $this->attendances()
            ->whereBetween('work_date', [$from, $to])
            ->where('status', 'present')
            ->sum('wage_snapshot');
    }

    /** ئەوەی پێی دراوە لە ماوەکەدا. */
    public function paidBetween(string $from, string $to): float
    {
        return (float) $this->payments()
            ->where('direction', 'out')
            ->whereBetween('paid_at', [$from, $to])
            ->sum('amount_iqd');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
