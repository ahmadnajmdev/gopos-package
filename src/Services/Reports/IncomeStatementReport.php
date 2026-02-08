<?php

namespace Gopos\Services\Reports;

use Gopos\Models\Account;
use Gopos\Models\Currency;

class IncomeStatementReport extends BaseReport
{
    protected string $title = 'Income Statement';

    protected string $titleAr = 'قائمة الدخل';

    protected bool $showTotals = true;

    protected array $columns = [
        'category' => ['label' => 'Category', 'label_ar' => 'البند', 'type' => 'text'],
        'amount' => ['label' => 'Amount', 'label_ar' => 'المبلغ', 'type' => 'currency'],
    ];

    public function getData(string $startDate, string $endDate, ?int $branchId = null, bool $allBranches = false): array
    {
        $baseCurrency = Currency::getBaseCurrency();
        $currencySymbol = $baseCurrency?->symbol ?? 'IQD';

        // Get revenue and expense details
        $revenueDetails = $this->getAccountDetails(4, $startDate, $endDate, $branchId, $allBranches); // Revenue
        $expenseDetails = $this->getAccountDetails(5, $startDate, $endDate, $branchId, $allBranches); // Expense

        // Calculate totals
        $totalRevenue = collect($revenueDetails)->sum('balance');

        // Separate COGS from other expenses
        $cogs = collect($expenseDetails)->filter(fn ($a) => str_starts_with($a['code'], '51'))->sum('balance');
        $operatingExpenses = collect($expenseDetails)->filter(fn ($a) => str_starts_with($a['code'], '52'))->sum('balance');
        $otherExpenses = collect($expenseDetails)->filter(fn ($a) => ! str_starts_with($a['code'], '51') && ! str_starts_with($a['code'], '52'))->sum('balance');

        $totalExpenses = $cogs + $operatingExpenses + $otherExpenses;
        $grossProfit = $totalRevenue - $cogs;
        $operatingIncome = $grossProfit - $operatingExpenses;
        $netIncome = $totalRevenue - $totalExpenses;

        return [
            'rows' => [
                // Revenue
                ['category' => __('Revenue'), 'category_ar' => 'الإيرادات', 'amount' => $totalRevenue, 'currency' => $currencySymbol, 'is_header' => true],
                ...array_map(fn ($a) => [
                    'category' => "  {$a['code']} - {$a['name']}",
                    'amount' => $a['balance'],
                    'currency' => $currencySymbol,
                ], $revenueDetails),
                ['category' => __('Total Revenue'), 'category_ar' => 'إجمالي الإيرادات', 'amount' => $totalRevenue, 'currency' => $currencySymbol, 'is_subtotal' => true],

                // COGS
                ['category' => __('Cost of Goods Sold'), 'category_ar' => 'تكلفة البضاعة المباعة', 'amount' => $cogs, 'currency' => $currencySymbol],

                // Gross Profit
                ['category' => __('Gross Profit'), 'category_ar' => 'إجمالي الربح', 'amount' => $grossProfit, 'currency' => $currencySymbol, 'is_subtotal' => true],

                // Operating Expenses
                ['category' => __('Operating Expenses'), 'category_ar' => 'المصروفات التشغيلية', 'amount' => $operatingExpenses, 'currency' => $currencySymbol],

                // Operating Income
                ['category' => __('Operating Income'), 'category_ar' => 'الدخل التشغيلي', 'amount' => $operatingIncome, 'currency' => $currencySymbol, 'is_subtotal' => true],

                // Other Expenses
                ['category' => __('Other Expenses'), 'category_ar' => 'مصروفات أخرى', 'amount' => $otherExpenses, 'currency' => $currencySymbol, 'is_visible' => $otherExpenses > 0],

                // Net Income
                ['category' => __('Net Income'), 'category_ar' => 'صافي الدخل', 'amount' => $netIncome, 'currency' => $currencySymbol, 'is_total' => true],
            ],
            'summary' => [
                'total_revenue' => $totalRevenue,
                'cogs' => $cogs,
                'gross_profit' => $grossProfit,
                'operating_expenses' => $operatingExpenses,
                'operating_income' => $operatingIncome,
                'other_expenses' => $otherExpenses,
                'net_income' => $netIncome,
                'currency' => $currencySymbol,
            ],
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
        ];
    }

    protected function getAccountDetails(int $typeId, string $startDate, string $endDate, ?int $branchId = null, bool $allBranches = false): array
    {
        $query = Account::query();

        if ($allBranches) {
            $query->withoutGlobalScope(filament()->getTenancyScopeName());
        } elseif ($branchId) {
            $query->withoutGlobalScope(filament()->getTenancyScopeName())
                ->where('branch_id', $branchId);
        }

        $accounts = $query->where('account_type_id', $typeId)
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        $result = [];

        foreach ($accounts as $account) {
            $balance = $account->getBalanceForPeriod($startDate, $endDate);

            if (abs($balance) >= 0.01) {
                $result[] = [
                    'code' => $account->code,
                    'name' => $account->localized_name,
                    'balance' => $balance,
                ];
            }
        }

        return $result;
    }

    public function getTitle(): string
    {
        $locale = app()->getLocale();

        return $locale === 'ar' ? $this->titleAr : $this->title;
    }
}
