<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Unit extends Model
{
    protected $fillable = ['name', 'code', 'type', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    /** ناوی کوردی جۆرەکانی یەکە. */
    public const TYPES = [
        'count' => 'ژماردن',
        'length' => 'درێژی',
        'area' => 'ڕووبەر',
        'weight' => 'کێش',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }
}
