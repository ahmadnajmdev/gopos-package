<?php

namespace Gopos\Models;

use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    protected $fillable = [
        'name',
        'name_ar',
        'name_ckb',
        'date',
        'type',
        'is_recurring',
        'is_paid',
        'description',
    ];

    protected $casts = [
        'date' => 'date',
        'is_recurring' => 'boolean',
        'is_paid' => 'boolean',
    ];

    public const TYPE_PUBLIC = 'public';

    public const TYPE_RELIGIOUS = 'religious';

    public const TYPE_COMPANY = 'company';

    public const TYPE_REGIONAL = 'regional';

    /**
     * Get localized name.
     */
    public function getLocalizedNameAttribute(): string
    {
        $locale = app()->getLocale();

        if ($locale === 'ar' && ! empty($this->name_ar)) {
            return $this->name_ar;
        }

        if ($locale === 'ckb' && ! empty($this->name_ckb)) {
            return $this->name_ckb;
        }

        return $this->name;
    }

    /**
     * Get type label.
     */
    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            self::TYPE_PUBLIC => __('Public Holiday'),
            self::TYPE_RELIGIOUS => __('Religious Holiday'),
            self::TYPE_COMPANY => __('Company Holiday'),
            self::TYPE_REGIONAL => __('Regional Holiday'),
            default => $this->type,
        };
    }

    /**
     * Check if date is holiday.
     */
    public static function isHoliday(\Carbon\Carbon $date): bool
    {
        return static::where('date', $date->toDateString())->exists()
            || static::where('is_recurring', true)
                ->whereMonth('date', $date->month)
                ->whereDay('date', $date->day)
                ->exists();
    }

    /**
     * Get holidays for period.
     */
    public static function getForPeriod(\Carbon\Carbon $start, \Carbon\Carbon $end): \Illuminate\Database\Eloquent\Collection
    {
        return static::whereBetween('date', [$start, $end])
            ->orWhere(function ($query) {
                $query->where('is_recurring', true);
                // For recurring, check month/day combinations
            })
            ->orderBy('date')
            ->get();
    }

    /**
     * Get holidays for year.
     */
    public static function getForYear(int $year): \Illuminate\Database\Eloquent\Collection
    {
        return static::whereYear('date', $year)
            ->orWhere('is_recurring', true)
            ->orderBy('date')
            ->get();
    }

    public function scopeUpcoming($query)
    {
        return $query->where('date', '>=', now()->toDateString())
            ->orderBy('date');
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }
}
