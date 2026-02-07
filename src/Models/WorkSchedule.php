<?php

namespace Gopos\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkSchedule extends Model
{
    protected $fillable = [
        'name',
        'name_ar',
        'name_ckb',
        'work_start_time',
        'work_end_time',
        'break_start_time',
        'break_end_time',
        'working_hours',
        'working_days',
        'late_tolerance_minutes',
        'early_leave_tolerance_minutes',
        'is_default',
        'is_active',
    ];

    protected $casts = [
        'work_start_time' => 'datetime:H:i',
        'work_end_time' => 'datetime:H:i',
        'break_start_time' => 'datetime:H:i',
        'break_end_time' => 'datetime:H:i',
        'working_hours' => 'decimal:2',
        'working_days' => 'array',
        'late_tolerance_minutes' => 'integer',
        'early_leave_tolerance_minutes' => 'integer',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

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
     * Check if a day is a working day.
     */
    public function isWorkingDay(int $dayOfWeek): bool
    {
        return in_array($dayOfWeek, $this->working_days ?? []);
    }

    /**
     * Check if time is late.
     */
    public function isLate(string $clockInTime): bool
    {
        $startTime = \Carbon\Carbon::parse($this->work_start_time);
        $clockIn = \Carbon\Carbon::parse($clockInTime);
        $toleranceTime = $startTime->copy()->addMinutes($this->late_tolerance_minutes);

        return $clockIn->gt($toleranceTime);
    }

    /**
     * Get late minutes.
     */
    public function getLateMinutes(string $clockInTime): int
    {
        $startTime = \Carbon\Carbon::parse($this->work_start_time);
        $clockIn = \Carbon\Carbon::parse($clockInTime);

        if ($clockIn->lte($startTime)) {
            return 0;
        }

        return $clockIn->diffInMinutes($startTime);
    }

    /**
     * Check if early leave.
     */
    public function isEarlyLeave(string $clockOutTime): bool
    {
        $endTime = \Carbon\Carbon::parse($this->work_end_time);
        $clockOut = \Carbon\Carbon::parse($clockOutTime);
        $toleranceTime = $endTime->copy()->subMinutes($this->early_leave_tolerance_minutes);

        return $clockOut->lt($toleranceTime);
    }

    /**
     * Get early leave minutes.
     */
    public function getEarlyLeaveMinutes(string $clockOutTime): int
    {
        $endTime = \Carbon\Carbon::parse($this->work_end_time);
        $clockOut = \Carbon\Carbon::parse($clockOutTime);

        if ($clockOut->gte($endTime)) {
            return 0;
        }

        return $endTime->diffInMinutes($clockOut);
    }

    /**
     * Get default schedule.
     */
    public static function getDefault(): ?self
    {
        return static::where('is_default', true)->first();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
