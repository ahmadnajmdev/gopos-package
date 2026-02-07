<?php

namespace Gopos\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payslip extends Model
{
    use HasFactory;

    protected $fillable = [
        'payroll_period_id',
        'employee_id',
        'payslip_number',
        'basic_salary',
        'total_earnings',
        'total_deductions',
        'gross_salary',
        'net_salary',
        'working_days',
        'days_worked',
        'overtime_hours',
        'overtime_amount',
        'leave_days',
        'absent_days',
        'absent_deduction',
        'loan_deduction',
        'status',
        'notes',
    ];

    protected $casts = [
        'basic_salary' => 'decimal:2',
        'total_earnings' => 'decimal:2',
        'total_deductions' => 'decimal:2',
        'gross_salary' => 'decimal:2',
        'net_salary' => 'decimal:2',
        'working_days' => 'decimal:2',
        'days_worked' => 'decimal:2',
        'overtime_hours' => 'decimal:2',
        'overtime_amount' => 'decimal:2',
        'leave_days' => 'decimal:2',
        'absent_days' => 'decimal:2',
        'absent_deduction' => 'decimal:2',
        'loan_deduction' => 'decimal:2',
    ];

    public const STATUS_DRAFT = 'draft';

    public const STATUS_PROCESSED = 'processed';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_PAID = 'paid';

    public const STATUS_CANCELLED = 'cancelled';

    public function payrollPeriod(): BelongsTo
    {
        return $this->belongsTo(PayrollPeriod::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PayslipItem::class);
    }

    public function earnings(): HasMany
    {
        return $this->items()->whereHas('payrollComponent', function ($q) {
            $q->where('type', PayrollComponent::TYPE_EARNING);
        });
    }

    public function deductions(): HasMany
    {
        return $this->items()->whereHas('payrollComponent', function ($q) {
            $q->where('type', PayrollComponent::TYPE_DEDUCTION);
        });
    }

    /**
     * Get status label.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT => __('Draft'),
            self::STATUS_PROCESSED => __('Processed'),
            self::STATUS_APPROVED => __('Approved'),
            self::STATUS_PAID => __('Paid'),
            self::STATUS_CANCELLED => __('Cancelled'),
            default => $this->status,
        };
    }

    /**
     * Get status color.
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT => 'gray',
            self::STATUS_PROCESSED => 'warning',
            self::STATUS_APPROVED => 'success',
            self::STATUS_PAID => 'primary',
            self::STATUS_CANCELLED => 'danger',
            default => 'gray',
        };
    }

    /**
     * Recalculate totals.
     */
    public function recalculateTotals(): void
    {
        $earnings = $this->items()
            ->whereHas('payrollComponent', fn ($q) => $q->where('type', PayrollComponent::TYPE_EARNING))
            ->sum('amount');

        $deductions = $this->items()
            ->whereHas('payrollComponent', fn ($q) => $q->where('type', PayrollComponent::TYPE_DEDUCTION))
            ->sum('amount');

        $totalEarnings = $earnings + $this->overtime_amount;
        $totalDeductions = $deductions + $this->absent_deduction + $this->loan_deduction;

        $this->update([
            'total_earnings' => $totalEarnings,
            'total_deductions' => $totalDeductions,
            'gross_salary' => $this->basic_salary + $totalEarnings,
            'net_salary' => $this->basic_salary + $totalEarnings - $totalDeductions,
        ]);
    }

    /**
     * Generate payslip number.
     */
    public static function generateNumber(PayrollPeriod $period): string
    {
        $prefix = 'PS-'.$period->year.str_pad($period->month, 2, '0', STR_PAD_LEFT);
        $lastNumber = static::where('payslip_number', 'like', $prefix.'%')
            ->selectRaw('MAX(CAST(SUBSTRING(payslip_number, -4) AS UNSIGNED)) as max_num')
            ->value('max_num');

        $nextNumber = ($lastNumber ?? 0) + 1;

        return $prefix.'-'.str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    public function scopeForPeriod($query, int $periodId)
    {
        return $query->where('payroll_period_id', $periodId);
    }

    public function scopeForEmployee($query, int $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }
}
