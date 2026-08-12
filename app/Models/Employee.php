<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'phone', 'job_title', 'daily_wage', 'wage_currency',
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
