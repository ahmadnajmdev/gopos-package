<?php

namespace Gopos\Services;

use Gopos\Models\Account;
use Gopos\Models\Budget;
use Gopos\Models\BudgetLine;
use Gopos\Models\BudgetRevision;
use Gopos\Models\CostCenter;
use Gopos\Models\FiscalPeriod;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BudgetService
{
    /**
     * Create a new budget.
     */
    public function createBudget(
        string $name,
        FiscalPeriod $fiscalPeriod,
        string $budgetType = Budget::TYPE_OPERATING,
        ?string $nameAr = null,
        ?string $nameCkb = null,
        ?string $description = null
    ): Budget {
        return Budget::create([
            'name' => $name,
            'name_ar' => $nameAr,
            'name_ckb' => $nameCkb,
            'description' => $description,
            'fiscal_period_id' => $fiscalPeriod->id,
            'budget_type' => $budgetType,
            'status' => Budget::STATUS_DRAFT,
        ]);
    }

    /**
     * Add budget line.
     */
    public function addBudgetLine(
        Budget $budget,
        Account $account,
        array $monthlyAmounts,
        ?CostCenter $costCenter = null,
        ?string $notes = null
    ): BudgetLine {
        $months = ['january', 'february', 'march', 'april', 'may', 'june',
            'july', 'august', 'september', 'october', 'november', 'december'];

        $data = [
            'budget_id' => $budget->id,
            'account_id' => $account->id,
            'cost_center_id' => $costCenter?->id,
            'notes' => $notes,
        ];

        foreach ($months as $index => $month) {
            $data[$month] = $monthlyAmounts[$index] ?? 0;
        }

        return BudgetLine::create($data);
    }

    /**
     * Copy budget from previous period.
     */
    public function copyBudget(
        Budget $sourceBudget,
        FiscalPeriod $targetPeriod,
        float $adjustmentPercent = 0
    ): Budget {
        return DB::transaction(function () use ($sourceBudget, $targetPeriod, $adjustmentPercent) {
            $newBudget = Budget::create([
                'name' => $sourceBudget->name,
                'name_ar' => $sourceBudget->name_ar,
                'name_ckb' => $sourceBudget->name_ckb,
                'description' => $sourceBudget->description,
                'fiscal_period_id' => $targetPeriod->id,
                'budget_type' => $sourceBudget->budget_type,
                'status' => Budget::STATUS_DRAFT,
            ]);

            $multiplier = 1 + ($adjustmentPercent / 100);

            foreach ($sourceBudget->lines as $line) {
                BudgetLine::create([
                    'budget_id' => $newBudget->id,
                    'account_id' => $line->account_id,
                    'cost_center_id' => $line->cost_center_id,
                    'january' => $line->january * $multiplier,
                    'february' => $line->february * $multiplier,
                    'march' => $line->march * $multiplier,
                    'april' => $line->april * $multiplier,
                    'may' => $line->may * $multiplier,
                    'june' => $line->june * $multiplier,
                    'july' => $line->july * $multiplier,
                    'august' => $line->august * $multiplier,
                    'september' => $line->september * $multiplier,
                    'october' => $line->october * $multiplier,
                    'november' => $line->november * $multiplier,
                    'december' => $line->december * $multiplier,
                    'notes' => $line->notes,
                ]);
            }

            return $newBudget;
        });
    }

    /**
     * Create budget revision.
     */
    public function createRevision(Budget $budget, string $reason, array $changes): BudgetRevision
    {
        return DB::transaction(function () use ($budget, $reason, $changes) {
            $previousTotal = $budget->total_amount;

            // Apply changes to budget lines
            foreach ($changes as $lineId => $monthlyChanges) {
                $line = BudgetLine::find($lineId);
                if ($line && $line->budget_id === $budget->id) {
                    $line->update($monthlyChanges);
                }
            }

            $budget->refresh();
            $newTotal = $budget->total_amount;

            return BudgetRevision::create([
                'budget_id' => $budget->id,
                'reason' => $reason,
                'previous_total' => $previousTotal,
                'new_total' => $newTotal,
                'changes' => $changes,
            ]);
        });
    }

    /**
     * Get budget vs actual report.
     */
    public function getBudgetVsActualReport(Budget $budget): array
    {
        $report = [
            'budget' => $budget,
            'lines' => [],
            'totals' => [
                'budgeted' => 0,
                'actual' => 0,
                'variance' => 0,
            ],
        ];

        $months = ['january', 'february', 'march', 'april', 'may', 'june',
            'july', 'august', 'september', 'october', 'november', 'december'];

        foreach ($budget->lines as $line) {
            $lineData = [
                'account' => $line->account,
                'cost_center' => $line->costCenter,
                'months' => [],
                'ytd' => $line->getYtdVariance(),
                'annual' => [
                    'budgeted' => $line->annual_total,
                    'actual' => 0,
                    'variance' => 0,
                ],
            ];

            foreach ($months as $index => $month) {
                $variance = $line->getVarianceForMonth($index + 1);
                $lineData['months'][$month] = $variance;
                $lineData['annual']['actual'] += $variance['actual'];
            }

            $lineData['annual']['variance'] = $lineData['annual']['budgeted'] - $lineData['annual']['actual'];

            $report['lines'][] = $lineData;
            $report['totals']['budgeted'] += $lineData['annual']['budgeted'];
            $report['totals']['actual'] += $lineData['annual']['actual'];
        }

        $report['totals']['variance'] = $report['totals']['budgeted'] - $report['totals']['actual'];

        return $report;
    }

    /**
     * Get variance alerts (over budget items).
     */
    public function getVarianceAlerts(Budget $budget, float $threshold = 10): Collection
    {
        $alerts = collect();
        $currentMonth = now()->month;

        foreach ($budget->lines as $line) {
            $ytd = $line->getYtdVariance();

            if ($ytd['variance_percent'] < -$threshold) {
                $alerts->push([
                    'line' => $line,
                    'account' => $line->account,
                    'cost_center' => $line->costCenter,
                    'budgeted' => $ytd['budgeted'],
                    'actual' => $ytd['actual'],
                    'variance' => $ytd['variance'],
                    'variance_percent' => $ytd['variance_percent'],
                    'severity' => abs($ytd['variance_percent']) > 25 ? 'high' : 'medium',
                ]);
            }
        }

        return $alerts->sortBy('variance_percent');
    }

    /**
     * Get cost center budget summary.
     */
    public function getCostCenterBudgetSummary(CostCenter $costCenter, ?FiscalPeriod $fiscalPeriod = null): array
    {
        $query = BudgetLine::where('cost_center_id', $costCenter->id);

        if ($fiscalPeriod) {
            $query->whereHas('budget', function ($q) use ($fiscalPeriod) {
                $q->where('fiscal_period_id', $fiscalPeriod->id)
                    ->where('status', Budget::STATUS_ACTIVE);
            });
        }

        $lines = $query->get();

        $summary = [
            'cost_center' => $costCenter,
            'total_budgeted' => $lines->sum('annual_total'),
            'total_actual' => 0,
            'total_variance' => 0,
            'accounts' => [],
        ];

        foreach ($lines as $line) {
            $ytd = $line->getYtdVariance();
            $summary['total_actual'] += $ytd['actual'];

            $summary['accounts'][] = [
                'account' => $line->account,
                'budgeted' => $line->annual_total,
                'actual' => $ytd['actual'],
                'variance' => $ytd['variance'],
            ];
        }

        $summary['total_variance'] = $summary['total_budgeted'] - $summary['total_actual'];

        return $summary;
    }

    /**
     * Distribute annual amount evenly across months.
     */
    public function distributeEvenly(float $annualAmount): array
    {
        $monthlyAmount = round($annualAmount / 12, 2);
        $amounts = array_fill(0, 12, $monthlyAmount);

        // Adjust last month for rounding difference
        $total = array_sum($amounts);
        $amounts[11] += ($annualAmount - $total);

        return $amounts;
    }

    /**
     * Distribute based on historical pattern.
     */
    public function distributeByPattern(float $annualAmount, array $pattern): array
    {
        $total = array_sum($pattern);
        $amounts = [];

        foreach ($pattern as $index => $value) {
            $amounts[$index] = round(($value / $total) * $annualAmount, 2);
        }

        // Adjust for rounding
        $actualTotal = array_sum($amounts);
        $amounts[11] += ($annualAmount - $actualTotal);

        return $amounts;
    }
}
