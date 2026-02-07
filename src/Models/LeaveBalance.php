<?php

namespace Gopos\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveBalance extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'leave_type_id',
        'year',
        'entitled_days',
        'carried_forward',
        'adjustment',
        'used_days',
        'pending_days',
    ];

    protected $casts = [
        'year' => 'integer',
        'entitled_days' => 'decimal:2',
        'carried_forward' => 'decimal:2',
        'adjustment' => 'decimal:2',
        'used_days' => 'decimal:2',
        'pending_days' => 'decimal:2',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class);
    }

    /**
     * Get total available days.
     */
    public function getTotalEntitledAttribute(): float
    {
        return $this->entitled_days + $this->carried_forward + $this->adjustment;
    }

    /**
     * Get available balance.
     */
    public function getAvailableAttribute(): float
    {
        return $this->total_entitled - $this->used_days - $this->pending_days;
    }

    /**
     * Get utilized percentage.
     */
    public function getUtilizedPercentageAttribute(): float
    {
        if ($this->total_entitled <= 0) {
            return 0;
        }

        return round(($this->used_days / $this->total_entitled) * 100, 1);
    }

    /**
     * Check if has sufficient balance.
     */
    public function hasSufficientBalance(float $days): bool
    {
        return $this->available >= $days;
    }

    /**
     * Reserve days for pending request.
     */
    public function reserveDays(float $days): void
    {
        $this->increment('pending_days', $days);
    }

    /**
     * Release reserved days.
     */
    public function releaseDays(float $days): void
    {
        $this->decrement('pending_days', $days);
    }

    /**
     * Use days (convert from pending to used).
     */
    public function useDays(float $days): void
    {
        $this->update([
            'pending_days' => max(0, $this->pending_days - $days),
            'used_days' => $this->used_days + $days,
        ]);
    }

    /**
     * Revert used days.
     */
    public function revertDays(float $days): void
    {
        $this->decrement('used_days', $days);
    }

    public function scopeForYear($query, int $year)
    {
        return $query->where('year', $year);
    }

    public function scopeForEmployee($query, int $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }
}
