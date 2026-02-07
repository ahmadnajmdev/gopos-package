<?php

namespace Gopos\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeaveType extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'name_ar',
        'name_ckb',
        'description',
        'default_days',
        'is_paid',
        'is_carry_forward',
        'max_carry_forward_days',
        'requires_approval',
        'requires_attachment',
        'min_days_notice',
        'max_consecutive_days',
        'is_active',
    ];

    protected $casts = [
        'default_days' => 'decimal:2',
        'is_paid' => 'boolean',
        'is_carry_forward' => 'boolean',
        'max_carry_forward_days' => 'decimal:2',
        'requires_approval' => 'boolean',
        'requires_attachment' => 'boolean',
        'min_days_notice' => 'integer',
        'max_consecutive_days' => 'integer',
        'is_active' => 'boolean',
    ];

    public function leaveBalances(): HasMany
    {
        return $this->hasMany(LeaveBalance::class);
    }

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function policies(): HasMany
    {
        return $this->hasMany(LeavePolicy::class);
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
     * Get display name.
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->code.' - '.$this->localized_name;
    }

    /**
     * Check if notice period is satisfied.
     */
    public function isNoticePeriodSatisfied(\Carbon\Carbon $requestDate, \Carbon\Carbon $startDate): bool
    {
        if (! $this->min_days_notice) {
            return true;
        }

        return $requestDate->diffInDays($startDate) >= $this->min_days_notice;
    }

    /**
     * Check if duration is within limits.
     */
    public function isDurationWithinLimits(float $days): bool
    {
        if (! $this->max_consecutive_days) {
            return true;
        }

        return $days <= $this->max_consecutive_days;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePaid($query)
    {
        return $query->where('is_paid', true);
    }
}
