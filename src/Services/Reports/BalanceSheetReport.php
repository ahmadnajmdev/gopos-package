<?php

namespace Gopos\Services\Reports;

use Gopos\Models\Account;
use Gopos\Models\Currency;

class BalanceSheetReport extends BaseReport
{
    protected string $title = 'Balance Sheet';

    protected string $titleAr = 'الميزانية العمومية';

    protected bool $showTotals = true;

    protected array $columns = [
        'account' => ['label' => 'Account', 'label_ar' => 'الحساب', 'type' => 'text'],
        'amount' => ['label' => 'Amount', 'label_ar' => 'المبلغ', 'type' => 'currency'],
    ];

    public function getData(string $startDate, string $endDate, ?int $branchId = null, bool $allBranches = false): array
    {
        $baseCurrency = Currency::getBaseCurrency();
        $currencySymbol = $baseCurrency?->symbol ?? 'IQD';

        // Get all accounts by type
        $assets = $this->getAccountsByType(1, $endDate, $branchId, $allBranches); // Asset
        $liabilities = $this->getAccountsByType(2, $endDate, $branchId, $allBranches); // Liability
        $equity = $this->getAccountsByType(3, $endDate, $branchId, $allBranches); // Equity

        // Calculate totals
        $totalAssets = collect($assets)->sum('balance');
        $totalLiabilities = collect($liabilities)->sum('balance');
        $totalEquity = collect($equity)->sum('balance');

        // Calculate net income for the period (Revenue - Expenses)
        $netIncome = $this->calculateNetIncome($startDate, $endDate, $branchId, $allBranches);

        // Total Equity including net income
        $totalEquityWithNetIncome = $totalEquity + $netIncome;

        return [
            'sections' => [
                [
                    'title' => __('Assets'),
                    'title_ar' => 'الأصول',
                    'accounts' => $assets,
                    'total' => $totalAssets,
                    'currency' => $currencySymbol,
                ],
                [
                    'title' => __('Liabilities'),
                    'title_ar' => 'الخصوم',
                    'accounts' => $liabilities,
                    'total' => $totalLiabilities,
                    'currency' => $currencySymbol,
                ],
                [
                    'title' => __('Equity'),
                    'title_ar' => 'حقوق الملكية',
                    'accounts' => $equity,
                    'total' => $totalEquity,
                    'currency' => $currencySymbol,
                    'extra_rows' => [
                        [
                            'name' => __('Net Income (Current Period)'),
                            'name_ar' => 'صافي الدخل (الفترة الحالية)',
                            'balance' => $netIncome,
                        ],
                    ],
                ],
            ],
            'summary' => [
                'total_assets' => $totalAssets,
                'total_liabilities' => $totalLiabilities,
                'total_equity' => $totalEquityWithNetIncome,
                'liabilities_plus_equity' => $totalLiabilities + $totalEquityWithNetIncome,
                'is_balanced' => abs($totalAssets - ($totalLiabilities + $totalEquityWithNetIncome)) < 0.01,
                'currency' => $currencySymbol,
            ],
            'as_of_date' => $endDate,
        ];
    }

    protected function getAccountsByType(int $typeId, string $asOfDate, ?int $branchId = null, bool $allBranches = false): array
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
            $balance = $account->getBalanceForPeriod('1900-01-01', $asOfDate);

            // Only include accounts with non-zero balances
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

    protected function calculateNetIncome(string $startDate, string $endDate, ?int $branchId = null, bool $allBranches = false): float
    {
        $revenueQuery = Account::query();
        $expenseQuery = Account::query();

        if ($allBranches) {
            $revenueQuery->withoutGlobalScope(filament()->getTenancyScopeName());
            $expenseQuery->withoutGlobalScope(filament()->getTenancyScopeName());
        } elseif ($branchId) {
            $revenueQuery->withoutGlobalScope(filament()->getTenancyScopeName())
                ->where('branch_id', $branchId);
            $expenseQuery->withoutGlobalScope(filament()->getTenancyScopeName())
                ->where('branch_id', $branchId);
        }

        // Revenue accounts (type 4)
        $revenueAccounts = $revenueQuery->where('account_type_id', 4)
            ->where('is_active', true)
            ->get();

        $totalRevenue = 0;
        foreach ($revenueAccounts as $account) {
            $totalRevenue += $account->getBalanceForPeriod($startDate, $endDate);
        }

        // Expense accounts (type 5)
        $expenseAccounts = $expenseQuery->where('account_type_id', 5)
            ->where('is_active', true)
            ->get();

        $totalExpenses = 0;
        foreach ($expenseAccounts as $account) {
            $totalExpenses += $account->getBalanceForPeriod($startDate, $endDate);
        }

        return $totalRevenue - $totalExpenses;
    }

    public function getTitle(): string
    {
        $locale = app()->getLocale();

        return $locale === 'ar' ? $this->titleAr : $this->title;
    }
}
