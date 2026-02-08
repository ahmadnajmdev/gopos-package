<?php

namespace Gopos\Services\Reports;

use Gopos\Models\Account;
use Gopos\Models\Currency;

class TrialBalanceReport extends BaseReport
{
    protected string $title = 'Trial Balance';

    protected string $titleAr = 'ميزان المراجعة';

    protected bool $showTotals = true;

    protected array $columns = [
        'code' => ['label' => 'Code', 'label_ar' => 'الرمز', 'type' => 'text'],
        'account' => ['label' => 'Account', 'label_ar' => 'الحساب', 'type' => 'text'],
        'debit' => ['label' => 'Debit', 'label_ar' => 'مدين', 'type' => 'currency'],
        'credit' => ['label' => 'Credit', 'label_ar' => 'دائن', 'type' => 'currency'],
    ];

    public function getData(string $startDate, string $endDate, ?int $branchId = null, bool $allBranches = false): array
    {
        $baseCurrency = Currency::getBaseCurrency();
        $currencySymbol = $baseCurrency?->symbol ?? 'IQD';

        $query = Account::query();

        if ($allBranches) {
            $query->withoutGlobalScope(filament()->getTenancyScopeName());
        } elseif ($branchId) {
            $query->withoutGlobalScope(filament()->getTenancyScopeName())
                ->where('branch_id', $branchId);
        }

        $accounts = $query->where('is_active', true)
            ->with('accountType')
            ->orderBy('code')
            ->get();

        $rows = [];
        $totalDebit = 0;
        $totalCredit = 0;

        foreach ($accounts as $account) {
            $balance = $account->getBalanceForPeriod('1900-01-01', $endDate);

            if (abs($balance) < 0.01) {
                continue; // Skip zero-balance accounts
            }

            // Determine debit/credit based on account type and balance
            $isDebitNormal = $account->isDebitBalance();

            if ($isDebitNormal) {
                if ($balance >= 0) {
                    $debit = $balance;
                    $credit = 0;
                } else {
                    $debit = 0;
                    $credit = abs($balance);
                }
            } else {
                if ($balance >= 0) {
                    $debit = 0;
                    $credit = $balance;
                } else {
                    $debit = abs($balance);
                    $credit = 0;
                }
            }

            $rows[] = [
                'code' => $account->code,
                'account' => $account->localized_name,
                'account_type' => $account->accountType?->localized_name,
                'debit' => $debit,
                'credit' => $credit,
            ];

            $totalDebit += $debit;
            $totalCredit += $credit;
        }

        return [
            'rows' => $rows,
            'totals' => [
                'debit' => $totalDebit,
                'credit' => $totalCredit,
                'is_balanced' => abs($totalDebit - $totalCredit) < 0.01,
                'difference' => $totalDebit - $totalCredit,
            ],
            'currency' => $currencySymbol,
            'as_of_date' => $endDate,
        ];
    }

    public function getTitle(): string
    {
        $locale = app()->getLocale();

        return $locale === 'ar' ? $this->titleAr : $this->title;
    }
}
