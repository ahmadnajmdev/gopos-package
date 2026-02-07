<?php

namespace Gopos\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayslipItem extends Model
{
    protected $fillable = [
        'payslip_id',
        'payroll_component_id',
        'description',
        'amount',
        'is_manual',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'is_manual' => 'boolean',
    ];

    public function payslip(): BelongsTo
    {
        return $this->belongsTo(Payslip::class);
    }

    public function payrollComponent(): BelongsTo
    {
        return $this->belongsTo(PayrollComponent::class);
    }

    /**
     * Check if earning.
     */
    public function isEarning(): bool
    {
        return $this->payrollComponent->isEarning();
    }

    /**
     * Check if deduction.
     */
    public function isDeduction(): bool
    {
        return $this->payrollComponent->isDeduction();
    }

    /**
     * Get component name.
     */
    public function getComponentNameAttribute(): string
    {
        return $this->payrollComponent->localized_name;
    }
}
