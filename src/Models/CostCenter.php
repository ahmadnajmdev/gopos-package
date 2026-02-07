<?php

namespace Gopos\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class CostCenter extends Model
{
    protected $fillable = [
        'parent_id',
        'code',
        'name',
        'name_ar',
        'name_ckb',
        'description',
        'type',
        'manager_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public const TYPE_DEPARTMENT = 'department';

    public const TYPE_PROJECT = 'project';

    public const TYPE_LOCATION = 'location';

    public const TYPE_PRODUCT_LINE = 'product_line';

    public const TYPE_OTHER = 'other';

    public function parent(): BelongsTo
    {
        return $this->belongsTo(CostCenter::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(CostCenter::class, 'parent_id');
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function journalLines(): HasMany
    {
        return $this->hasMany(JournalEntryLine::class);
    }

    public function budgetLines(): HasMany
    {
        return $this->hasMany(BudgetLine::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function incomes(): HasMany
    {
        return $this->hasMany(Income::class);
    }

    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
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
     * Get display name with code.
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->code.' - '.$this->localized_name;
    }

    /**
     * Get full path.
     */
    public function getFullPathAttribute(): string
    {
        if ($this->parent) {
            return $this->parent->full_path.' > '.$this->localized_name;
        }

        return $this->localized_name;
    }

    /**
     * Get total expenses for a period.
     */
    public function getTotalExpensesForPeriod($startDate, $endDate): float
    {
        return $this->journalLines()
            ->whereHas('journalEntry', function ($q) use ($startDate, $endDate) {
                $q->where('status', 'posted')
                    ->whereBetween('entry_date', [$startDate, $endDate]);
            })
            ->whereHas('account.accountType', function ($q) {
                $q->where('name', 'Expense');
            })
            ->sum('debit');
    }

    /**
     * Get total revenue for a period.
     */
    public function getTotalRevenueForPeriod($startDate, $endDate): float
    {
        return $this->journalLines()
            ->whereHas('journalEntry', function ($q) use ($startDate, $endDate) {
                $q->where('status', 'posted')
                    ->whereBetween('entry_date', [$startDate, $endDate]);
            })
            ->whereHas('account.accountType', function ($q) {
                $q->where('name', 'Revenue');
            })
            ->sum('credit');
    }

    /**
     * Get profit/loss for a period.
     */
    public function getProfitLossForPeriod($startDate, $endDate): float
    {
        $revenue = $this->getTotalRevenueForPeriod($startDate, $endDate);
        $expenses = $this->getTotalExpensesForPeriod($startDate, $endDate);

        return $revenue - $expenses;
    }

    /**
     * Get all descendants.
     */
    public function getAllDescendants(): Collection
    {
        $descendants = collect();

        foreach ($this->children as $child) {
            $descendants->push($child);
            $descendants = $descendants->merge($child->getAllDescendants());
        }

        return $descendants;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }
}
