<?php

namespace Gopos\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmployeeLoan extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'loan_number',
        'loan_type',
        'loan_amount',
        'interest_rate',
        'total_amount',
        'installment_amount',
        'installments',
        'paid_installments',
        'remaining_amount',
        'start_date',
        'end_date',
        'status',
        'reason',
        'approved_by',
        'approved_at',
        'notes',
    ];

    protected $casts = [
        'loan_amount' => 'decimal:2',
        'interest_rate' => 'decimal:4',
        'total_amount' => 'decimal:2',
        'installment_amount' => 'decimal:2',
        'installments' => 'integer',
        'paid_installments' => 'integer',
        'remaining_amount' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'approved_at' => 'datetime',
    ];

    public const TYPE_SALARY_ADVANCE = 'salary_advance';

    public const TYPE_PERSONAL_LOAN = 'personal_loan';

    public const TYPE_EMERGENCY_LOAN = 'emergency_loan';

    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_CANCELLED = 'cancelled';

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function repayments(): HasMany
    {
        return $this->hasMany(LoanRepayment::class);
    }

    /**
     * Get status label.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => __('Pending'),
            self::STATUS_APPROVED => __('Approved'),
            self::STATUS_ACTIVE => __('Active'),
            self::STATUS_COMPLETED => __('Completed'),
            self::STATUS_REJECTED => __('Rejected'),
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
            self::STATUS_PENDING => 'warning',
            self::STATUS_APPROVED => 'info',
            self::STATUS_ACTIVE => 'success',
            self::STATUS_COMPLETED => 'primary',
            self::STATUS_REJECTED => 'danger',
            self::STATUS_CANCELLED => 'gray',
            default => 'gray',
        };
    }

    /**
     * Get remaining installments.
     */
    public function getRemainingInstallmentsAttribute(): int
    {
        return $this->installments - $this->paid_installments;
    }

    /**
     * Get paid percentage.
     */
    public function getPaidPercentageAttribute(): float
    {
        if ($this->total_amount <= 0) {
            return 0;
        }

        $paid = $this->total_amount - $this->remaining_amount;

        return round(($paid / $this->total_amount) * 100, 1);
    }

    /**
     * Calculate loan details.
     */
    public function calculateLoan(): void
    {
        $interest = $this->loan_amount * ($this->interest_rate / 100);
        $totalAmount = $this->loan_amount + $interest;
        $installmentAmount = $totalAmount / $this->installments;

        $this->update([
            'total_amount' => round($totalAmount, 2),
            'installment_amount' => round($installmentAmount, 2),
            'remaining_amount' => round($totalAmount, 2),
        ]);
    }

    /**
     * Approve loan.
     */
    public function approve(int $userId): void
    {
        $this->update([
            'status' => self::STATUS_APPROVED,
            'approved_by' => $userId,
            'approved_at' => now(),
        ]);
    }

    /**
     * Activate loan.
     */
    public function activate(): void
    {
        $this->update(['status' => self::STATUS_ACTIVE]);
    }

    /**
     * Record repayment.
     */
    public function recordRepayment(float $amount, ?int $payslipId = null, string $method = 'payroll'): LoanRepayment
    {
        $repayment = $this->repayments()->create([
            'payslip_id' => $payslipId,
            'amount' => $amount,
            'payment_method' => $method,
            'payment_date' => now(),
            'installment_number' => $this->paid_installments + 1,
            'remaining_balance' => $this->remaining_amount - $amount,
        ]);

        $this->update([
            'paid_installments' => $this->paid_installments + 1,
            'remaining_amount' => max(0, $this->remaining_amount - $amount),
        ]);

        // Check if completed
        if ($this->remaining_amount <= 0) {
            $this->update(['status' => self::STATUS_COMPLETED]);
        }

        return $repayment;
    }

    /**
     * Generate loan number.
     */
    public static function generateNumber(): string
    {
        $lastNumber = static::selectRaw('MAX(CAST(SUBSTRING(loan_number, 5) AS UNSIGNED)) as max_num')
            ->value('max_num');

        $nextNumber = ($lastNumber ?? 0) + 1;

        return 'LN-'.str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeForEmployee($query, int $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->loan_number)) {
                $model->loan_number = static::generateNumber();
            }
        });
    }
}
