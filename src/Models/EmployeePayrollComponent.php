<?php

namespace Gopos\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeePayrollComponent extends Model
{
    protected $fillable = [
        'employee_id',
        'payroll_component_id',
        'amount',
        'percentage',
        'effective_from',
        'effective_to',
        'is_active',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'percentage' => 'decimal:4',
        'effective_from' => 'date',
        'effective_to' => 'date',
        'is_active' => 'boolean',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function payrollComponent(): BelongsTo
    {
        return $this->belongsTo(PayrollComponent::class);
    }

    /**
     * Check if currently effective.
     */
    public function isEffective(?string $date = null): bool
    {
        $checkDate = $date ? \Carbon\Carbon::parse($date) : now();

        if (! $this->is_active) {
            return false;
        }

        if ($this->effective_from && $checkDate->lt($this->effective_from)) {
            return false;
        }

        if ($this->effective_to && $checkDate->gt($this->effective_to)) {
            return false;
        }

        return true;
    }

    /**
     * Calculate amount for payroll.
     */
    public function calculateAmount(float $baseSalary): float
    {
        $component = $this->payrollComponent;

        // Use employee-specific amount if set
        if ($this->amount) {
            return $this->amount;
        }

        // Use employee-specific percentage if set
        if ($this->percentage) {
            return round($baseSalary * ($this->percentage / 100), 2);
        }

        // Fall back to component default calculation
        return $component->calculateAmount($baseSalary);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeEffective($query, ?string $date = null)
    {
        $date = $date ?? now()->toDateString();

        return $query->where('is_active', true)
            ->where(function ($q) use ($date) {
                $q->whereNull('effective_from')
                    ->orWhere('effective_from', '<=', $date);
            })
            ->where(function ($q) use ($date) {
                $q->whereNull('effective_to')
                    ->orWhere('effective_to', '>=', $date);
            });
    }
}
