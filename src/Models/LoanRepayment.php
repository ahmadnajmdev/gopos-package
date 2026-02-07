<?php

namespace Gopos\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoanRepayment extends Model
{
    protected $fillable = [
        'employee_loan_id',
        'payslip_id',
        'amount',
        'payment_method',
        'payment_date',
        'installment_number',
        'remaining_balance',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
        'installment_number' => 'integer',
        'remaining_balance' => 'decimal:2',
    ];

    public const METHOD_PAYROLL = 'payroll';

    public const METHOD_CASH = 'cash';

    public const METHOD_BANK_TRANSFER = 'bank_transfer';

    public function loan(): BelongsTo
    {
        return $this->belongsTo(EmployeeLoan::class, 'employee_loan_id');
    }

    public function payslip(): BelongsTo
    {
        return $this->belongsTo(Payslip::class);
    }

    /**
     * Get payment method label.
     */
    public function getMethodLabelAttribute(): string
    {
        return match ($this->payment_method) {
            self::METHOD_PAYROLL => __('Payroll Deduction'),
            self::METHOD_CASH => __('Cash'),
            self::METHOD_BANK_TRANSFER => __('Bank Transfer'),
            default => $this->payment_method,
        };
    }
}
