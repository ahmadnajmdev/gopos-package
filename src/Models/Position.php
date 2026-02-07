<?php

namespace Gopos\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Position extends Model
{
    protected $fillable = [
        'department_id',
        'code',
        'title',
        'title_ar',
        'title_ckb',
        'description',
        'min_salary',
        'max_salary',
        'level',
        'is_active',
    ];

    protected $casts = [
        'min_salary' => 'decimal:2',
        'max_salary' => 'decimal:2',
        'level' => 'integer',
        'is_active' => 'boolean',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    public function leavePolicies(): HasMany
    {
        return $this->hasMany(LeavePolicy::class);
    }

    /**
     * Get localized title.
     */
    public function getLocalizedTitleAttribute(): string
    {
        $locale = app()->getLocale();

        if ($locale === 'ar' && ! empty($this->title_ar)) {
            return $this->title_ar;
        }

        if ($locale === 'ckb' && ! empty($this->title_ckb)) {
            return $this->title_ckb;
        }

        return $this->title;
    }

    /**
     * Get display name with code.
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->code.' - '.$this->localized_title;
    }

    /**
     * Get salary range display.
     */
    public function getSalaryRangeAttribute(): string
    {
        if ($this->min_salary && $this->max_salary) {
            return number_format($this->min_salary).' - '.number_format($this->max_salary);
        }

        return 'Not specified';
    }

    /**
     * Check if salary is within range.
     */
    public function isSalaryInRange(float $salary): bool
    {
        if (! $this->min_salary && ! $this->max_salary) {
            return true;
        }

        if ($this->min_salary && $salary < $this->min_salary) {
            return false;
        }

        if ($this->max_salary && $salary > $this->max_salary) {
            return false;
        }

        return true;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByLevel($query, int $level)
    {
        return $query->where('level', $level);
    }
}
