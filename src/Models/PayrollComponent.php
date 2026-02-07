<?php

namespace Gopos\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PayrollComponent extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'name_ar',
        'name_ckb',
        'type',
        'calculation_type',
        'default_amount',
        'percentage',
        'max_amount',
        'min_amount',
        'is_taxable',
        'is_mandatory',
        'applies_to_all',
        'account_id',
        'display_order',
        'is_active',
    ];

    protected $casts = [
        'default_amount' => 'decimal:2',
        'percentage' => 'decimal:4',
        'max_amount' => 'decimal:2',
        'min_amount' => 'decimal:2',
        'is_taxable' => 'boolean',
        'is_mandatory' => 'boolean',
        'applies_to_all' => 'boolean',
        'display_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public const TYPE_EARNING = 'earning';

    public const TYPE_DEDUCTION = 'deduction';

    public const CALC_FIXED = 'fixed';

    public const CALC_PERCENTAGE = 'percentage';

    public const CALC_FORMULA = 'formula';

    public function employeeComponents(): HasMany
    {
        return $this->hasMany(EmployeePayrollComponent::class);
    }

    public function payslipItems(): HasMany
    {
        return $this->hasMany(PayslipItem::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
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
     * Check if earning.
     */
    public function isEarning(): bool
    {
        return $this->type === self::TYPE_EARNING;
    }

    /**
     * Check if deduction.
     */
    public function isDeduction(): bool
    {
        return $this->type === self::TYPE_DEDUCTION;
    }

    /**
     * Calculate amount based on base salary.
     */
    public function calculateAmount(float $baseSalary, ?float $customAmount = null): float
    {
        $amount = match ($this->calculation_type) {
            self::CALC_FIXED => $customAmount ?? $this->default_amount,
            self::CALC_PERCENTAGE => $baseSalary * ($this->percentage / 100),
            default => $customAmount ?? $this->default_amount,
        };

        // Apply limits
        if ($this->min_amount && $amount < $this->min_amount) {
            $amount = $this->min_amount;
        }

        if ($this->max_amount && $amount > $this->max_amount) {
            $amount = $this->max_amount;
        }

        return round($amount, 2);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeEarnings($query)
    {
        return $query->where('type', self::TYPE_EARNING);
    }

    public function scopeDeductions($query)
    {
        return $query->where('type', self::TYPE_DEDUCTION);
    }

    public function scopeMandatory($query)
    {
        return $query->where('is_mandatory', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order');
    }
}
