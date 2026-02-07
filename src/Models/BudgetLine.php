<?php

namespace Gopos\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BudgetLine extends Model
{
    protected $fillable = [
        'budget_id',
        'account_id',
        'cost_center_id',
        'january',
        'february',
        'march',
        'april',
        'may',
        'june',
        'july',
        'august',
        'september',
        'october',
        'november',
        'december',
        'annual_total',
        'notes',
    ];

    protected $casts = [
        'january' => 'decimal:2',
        'february' => 'decimal:2',
        'march' => 'decimal:2',
        'april' => 'decimal:2',
        'may' => 'decimal:2',
        'june' => 'decimal:2',
        'july' => 'decimal:2',
        'august' => 'decimal:2',
        'september' => 'decimal:2',
        'october' => 'decimal:2',
        'november' => 'decimal:2',
        'december' => 'decimal:2',
        'annual_total' => 'decimal:2',
    ];

    public function budget(): BelongsTo
    {
        return $this->belongsTo(Budget::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function costCenter(): BelongsTo
    {
        return $this->belongsTo(CostCenter::class);
    }

    /**
     * Calculate annual total from monthly values.
     */
    public function calculateAnnualTotal(): float
    {
        return $this->january + $this->february + $this->march +
               $this->april + $this->may + $this->june +
               $this->july + $this->august + $this->september +
               $this->october + $this->november + $this->december;
    }

    /**
     * Update annual total.
     */
    public function updateAnnualTotal(): void
    {
        $this->update(['annual_total' => $this->calculateAnnualTotal()]);
    }

    /**
     * Get budget for a specific month.
     */
    public function getBudgetForMonth(int $month): float
    {
        $months = [
            1 => 'january', 2 => 'february', 3 => 'march',
            4 => 'april', 5 => 'may', 6 => 'june',
            7 => 'july', 8 => 'august', 9 => 'september',
            10 => 'october', 11 => 'november', 12 => 'december',
        ];

        return $this->{$months[$month]} ?? 0;
    }

    /**
     * Get actual spending for a period.
     */
    public function getActualForPeriod($startDate, $endDate): float
    {
        $query = JournalEntryLine::where('account_id', $this->account_id)
            ->whereHas('journalEntry', function ($q) use ($startDate, $endDate) {
                $q->where('status', 'posted')
                    ->whereBetween('entry_date', [$startDate, $endDate]);
            });

        if ($this->cost_center_id) {
            $query->where('cost_center_id', $this->cost_center_id);
        }

        $result = $query->selectRaw('SUM(debit) - SUM(credit) as net')->first();

        return abs($result->net ?? 0);
    }

    /**
     * Get variance for a month.
     */
    public function getVarianceForMonth(int $month): array
    {
        $budget = $this->budget;
        $startDate = $budget->fiscalPeriod->start_date->copy()->month($month)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $budgeted = $this->getBudgetForMonth($month);
        $actual = $this->getActualForPeriod($startDate, $endDate);
        $variance = $budgeted - $actual;

        return [
            'budgeted' => $budgeted,
            'actual' => $actual,
            'variance' => $variance,
            'variance_percent' => $budgeted != 0 ? ($variance / $budgeted) * 100 : 0,
        ];
    }

    /**
     * Get YTD variance.
     */
    public function getYtdVariance(): array
    {
        $budget = $this->budget;
        $currentMonth = now()->month;

        $budgetedYtd = 0;
        $actualYtd = 0;

        for ($m = 1; $m <= $currentMonth; $m++) {
            $budgetedYtd += $this->getBudgetForMonth($m);
            $startDate = $budget->fiscalPeriod->start_date->copy()->month($m)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();
            $actualYtd += $this->getActualForPeriod($startDate, $endDate);
        }

        $variance = $budgetedYtd - $actualYtd;

        return [
            'budgeted' => $budgetedYtd,
            'actual' => $actualYtd,
            'variance' => $variance,
            'variance_percent' => $budgetedYtd != 0 ? ($variance / $budgetedYtd) * 100 : 0,
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            $model->annual_total = $model->calculateAnnualTotal();
        });

        static::saved(function ($model) {
            $model->budget->updateTotal();
        });
    }
}
