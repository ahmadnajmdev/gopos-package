<?php

namespace Gopos\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeavePolicy extends Model
{
    protected $fillable = [
        'leave_type_id',
        'position_id',
        'department_id',
        'employment_type',
        'entitled_days',
        'min_service_months',
        'accrual_frequency',
        'accrual_amount',
        'is_active',
    ];

    protected $casts = [
        'entitled_days' => 'decimal:2',
        'min_service_months' => 'integer',
        'accrual_amount' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public const ACCRUAL_NONE = 'none';

    public const ACCRUAL_MONTHLY = 'monthly';

    public const ACCRUAL_QUARTERLY = 'quarterly';

    public const ACCRUAL_YEARLY = 'yearly';

    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class);
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Check if employee is eligible.
     */
    public function isEmployeeEligible(Employee $employee): bool
    {
        // Check minimum service
        if ($this->min_service_months && $employee->service_days < ($this->min_service_months * 30)) {
            return false;
        }

        // Check employment type
        if ($this->employment_type && $employee->employment_type !== $this->employment_type) {
            return false;
        }

        // Check position
        if ($this->position_id && $employee->position_id !== $this->position_id) {
            return false;
        }

        // Check department
        if ($this->department_id && $employee->department_id !== $this->department_id) {
            return false;
        }

        return true;
    }

    /**
     * Calculate accrued days.
     */
    public function calculateAccruedDays(int $monthsWorked): float
    {
        return match ($this->accrual_frequency) {
            self::ACCRUAL_MONTHLY => $this->accrual_amount * $monthsWorked,
            self::ACCRUAL_QUARTERLY => $this->accrual_amount * floor($monthsWorked / 3),
            self::ACCRUAL_YEARLY => $this->accrual_amount * floor($monthsWorked / 12),
            default => $this->entitled_days,
        };
    }

    /**
     * Find applicable policy for employee.
     */
    public static function findForEmployee(Employee $employee, int $leaveTypeId): ?self
    {
        // Priority: Position > Department > Employment Type > Default
        return static::where('leave_type_id', $leaveTypeId)
            ->where('is_active', true)
            ->where(function ($query) use ($employee) {
                $query->where('position_id', $employee->position_id)
                    ->orWhere('department_id', $employee->department_id)
                    ->orWhere('employment_type', $employee->employment_type)
                    ->orWhereNull('position_id');
            })
            ->orderByRaw('CASE
                WHEN position_id IS NOT NULL THEN 1
                WHEN department_id IS NOT NULL THEN 2
                WHEN employment_type IS NOT NULL THEN 3
                ELSE 4 END')
            ->first();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
