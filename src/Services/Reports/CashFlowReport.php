<?php

namespace Gopos\Services\Reports;

use Gopos\Models\Account;
use Gopos\Models\JournalEntryLine;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class CashFlowReport extends BaseReport
{
    protected string $startDate;

    protected string $endDate;

    protected ?int $branchId = null;

    protected bool $allBranches = false;

    public function __construct(string $startDate = '', string $endDate = '')
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function getData(string $startDate, string $endDate, ?int $branchId = null, bool $allBranches = false): Collection|array
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->branchId = $branchId;
        $this->allBranches = $allBranches;

        return $this->generate();
    }

    /**
     * Generate the cash flow statement.
     */
    public function generate(): array
    {
        $operatingActivities = $this->getOperatingActivities();
        $investingActivities = $this->getInvestingActivities();
        $financingActivities = $this->getFinancingActivities();

        $netOperating = array_sum(array_column($operatingActivities, 'amount'));
        $netInvesting = array_sum(array_column($investingActivities, 'amount'));
        $netFinancing = array_sum(array_column($financingActivities, 'amount'));

        $netCashChange = $netOperating + $netInvesting + $netFinancing;

        $openingCash = $this->getOpeningCashBalance();
        $closingCash = $openingCash + $netCashChange;

        return [
            'period' => [
                'start_date' => $this->startDate,
                'end_date' => $this->endDate,
            ],
            'operating_activities' => [
                'items' => $operatingActivities,
                'total' => $netOperating,
            ],
            'investing_activities' => [
                'items' => $investingActivities,
                'total' => $netInvesting,
            ],
            'financing_activities' => [
                'items' => $financingActivities,
                'total' => $netFinancing,
            ],
            'summary' => [
                'net_cash_change' => $netCashChange,
                'opening_cash' => $openingCash,
                'closing_cash' => $closingCash,
            ],
        ];
    }

    protected function newJournalEntryLineQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = JournalEntryLine::query();

        if ($this->allBranches || $this->branchId) {
            $query->whereHas('journalEntry', function ($q) {
                if ($this->allBranches) {
                    $q->withoutGlobalScope(filament()->getTenancyScopeName());
                } elseif ($this->branchId) {
                    $q->withoutGlobalScope(filament()->getTenancyScopeName())
                        ->where('branch_id', $this->branchId);
                }
            });
        }

        return $query;
    }

    protected function newAccountQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = Account::query();

        if ($this->allBranches) {
            $query->withoutGlobalScope(filament()->getTenancyScopeName());
        } elseif ($this->branchId) {
            $query->withoutGlobalScope(filament()->getTenancyScopeName())
                ->where('branch_id', $this->branchId);
        }

        return $query;
    }

    /**
     * Get operating activities (indirect method).
     */
    protected function getOperatingActivities(): array
    {
        $items = [];

        // Start with net income
        $netIncome = $this->getNetIncome();
        $items[] = [
            'name' => __('Net Income'),
            'amount' => $netIncome,
        ];

        // Add back non-cash expenses
        $depreciation = $this->getDepreciationExpense();
        if ($depreciation != 0) {
            $items[] = [
                'name' => __('Depreciation Expense'),
                'amount' => abs($depreciation),
            ];
        }

        // Changes in working capital
        $arChange = $this->getAccountsReceivableChange();
        if ($arChange != 0) {
            $items[] = [
                'name' => __('Change in Accounts Receivable'),
                'amount' => -$arChange, // Increase in AR is a use of cash
            ];
        }

        $inventoryChange = $this->getInventoryChange();
        if ($inventoryChange != 0) {
            $items[] = [
                'name' => __('Change in Inventory'),
                'amount' => -$inventoryChange,
            ];
        }

        $apChange = $this->getAccountsPayableChange();
        if ($apChange != 0) {
            $items[] = [
                'name' => __('Change in Accounts Payable'),
                'amount' => $apChange, // Increase in AP is a source of cash
            ];
        }

        return $items;
    }

    /**
     * Get investing activities.
     */
    protected function getInvestingActivities(): array
    {
        $items = [];

        // Purchase of fixed assets
        $fixedAssetPurchases = $this->getFixedAssetPurchases();
        if ($fixedAssetPurchases != 0) {
            $items[] = [
                'name' => __('Purchase of Fixed Assets'),
                'amount' => -abs($fixedAssetPurchases),
            ];
        }

        // Sale of fixed assets
        $fixedAssetSales = $this->getFixedAssetSales();
        if ($fixedAssetSales != 0) {
            $items[] = [
                'name' => __('Sale of Fixed Assets'),
                'amount' => abs($fixedAssetSales),
            ];
        }

        return $items;
    }

    /**
     * Get financing activities.
     */
    protected function getFinancingActivities(): array
    {
        $items = [];

        // Owner's capital contributions
        $capitalContributions = $this->getCapitalContributions();
        if ($capitalContributions != 0) {
            $items[] = [
                'name' => __('Capital Contributions'),
                'amount' => abs($capitalContributions),
            ];
        }

        // Owner's drawings
        $drawings = $this->getOwnerDrawings();
        if ($drawings != 0) {
            $items[] = [
                'name' => __('Owner Drawings'),
                'amount' => -abs($drawings),
            ];
        }

        // Loan proceeds
        $loanProceeds = $this->getLoanProceeds();
        if ($loanProceeds != 0) {
            $items[] = [
                'name' => __('Loan Proceeds'),
                'amount' => abs($loanProceeds),
            ];
        }

        // Loan repayments
        $loanRepayments = $this->getLoanRepayments();
        if ($loanRepayments != 0) {
            $items[] = [
                'name' => __('Loan Repayments'),
                'amount' => -abs($loanRepayments),
            ];
        }

        return $items;
    }

    /**
     * Get net income for the period.
     */
    protected function getNetIncome(): float
    {
        $revenue = $this->newJournalEntryLineQuery()->whereHas('journalEntry', function ($q) {
            $q->where('status', 'posted')
                ->whereBetween('entry_date', [$this->startDate, $this->endDate]);
        })->whereHas('account.accountType', function ($q) {
            $q->where('name', 'Revenue');
        })->sum('credit') - $this->newJournalEntryLineQuery()->whereHas('journalEntry', function ($q) {
            $q->where('status', 'posted')
                ->whereBetween('entry_date', [$this->startDate, $this->endDate]);
        })->whereHas('account.accountType', function ($q) {
            $q->where('name', 'Revenue');
        })->sum('debit');

        $expenses = $this->newJournalEntryLineQuery()->whereHas('journalEntry', function ($q) {
            $q->where('status', 'posted')
                ->whereBetween('entry_date', [$this->startDate, $this->endDate]);
        })->whereHas('account.accountType', function ($q) {
            $q->where('name', 'Expense');
        })->sum('debit') - $this->newJournalEntryLineQuery()->whereHas('journalEntry', function ($q) {
            $q->where('status', 'posted')
                ->whereBetween('entry_date', [$this->startDate, $this->endDate]);
        })->whereHas('account.accountType', function ($q) {
            $q->where('name', 'Expense');
        })->sum('credit');

        return $revenue - $expenses;
    }

    /**
     * Get depreciation expense.
     */
    protected function getDepreciationExpense(): float
    {
        return $this->newJournalEntryLineQuery()->whereHas('journalEntry', function ($q) {
            $q->where('status', 'posted')
                ->whereBetween('entry_date', [$this->startDate, $this->endDate]);
        })->whereHas('account', function ($q) {
            $q->where('name', 'like', '%Depreciation%');
        })->sum('debit');
    }

    /**
     * Get change in accounts receivable.
     */
    protected function getAccountsReceivableChange(): float
    {
        return $this->getAccountBalanceChange('Accounts Receivable');
    }

    /**
     * Get change in inventory.
     */
    protected function getInventoryChange(): float
    {
        return $this->getAccountBalanceChange('Inventory');
    }

    /**
     * Get change in accounts payable.
     */
    protected function getAccountsPayableChange(): float
    {
        return $this->getAccountBalanceChange('Accounts Payable');
    }

    /**
     * Get account balance change for period.
     */
    protected function getAccountBalanceChange(string $accountName): float
    {
        $account = $this->newAccountQuery()->where('name', 'like', "%{$accountName}%")->first();
        if (! $account) {
            return 0;
        }

        $startBalance = $account->getBalanceForPeriod('1900-01-01', Carbon::parse($this->startDate)->subDay()->format('Y-m-d'));
        $endBalance = $account->getBalanceForPeriod('1900-01-01', $this->endDate);

        return $endBalance - $startBalance;
    }

    /**
     * Get opening cash balance.
     */
    protected function getOpeningCashBalance(): float
    {
        $cashAccounts = $this->newAccountQuery()->where('name', 'like', '%Cash%')
            ->orWhere('name', 'like', '%Bank%')
            ->get();

        $total = 0;
        $startDate = Carbon::parse($this->startDate)->subDay()->format('Y-m-d');

        foreach ($cashAccounts as $account) {
            $total += $account->opening_balance + $account->getBalanceForPeriod('1900-01-01', $startDate);
        }

        return $total;
    }

    /**
     * Get fixed asset purchases.
     */
    protected function getFixedAssetPurchases(): float
    {
        return $this->newJournalEntryLineQuery()->whereHas('journalEntry', function ($q) {
            $q->where('status', 'posted')
                ->whereBetween('entry_date', [$this->startDate, $this->endDate]);
        })->whereHas('account', function ($q) {
            $q->where('name', 'like', '%Fixed Asset%')
                ->orWhere('name', 'like', '%Equipment%')
                ->orWhere('name', 'like', '%Vehicle%');
        })->sum('debit');
    }

    /**
     * Get fixed asset sales.
     */
    protected function getFixedAssetSales(): float
    {
        return $this->newJournalEntryLineQuery()->whereHas('journalEntry', function ($q) {
            $q->where('status', 'posted')
                ->whereBetween('entry_date', [$this->startDate, $this->endDate]);
        })->whereHas('account', function ($q) {
            $q->where('name', 'like', '%Fixed Asset%')
                ->orWhere('name', 'like', '%Equipment%')
                ->orWhere('name', 'like', '%Vehicle%');
        })->sum('credit');
    }

    /**
     * Get capital contributions.
     */
    protected function getCapitalContributions(): float
    {
        return $this->newJournalEntryLineQuery()->whereHas('journalEntry', function ($q) {
            $q->where('status', 'posted')
                ->whereBetween('entry_date', [$this->startDate, $this->endDate]);
        })->whereHas('account', function ($q) {
            $q->where('name', 'like', '%Capital%')
                ->where('name', 'not like', '%Drawings%');
        })->sum('credit');
    }

    /**
     * Get owner drawings.
     */
    protected function getOwnerDrawings(): float
    {
        return $this->newJournalEntryLineQuery()->whereHas('journalEntry', function ($q) {
            $q->where('status', 'posted')
                ->whereBetween('entry_date', [$this->startDate, $this->endDate]);
        })->whereHas('account', function ($q) {
            $q->where('name', 'like', '%Drawings%');
        })->sum('debit');
    }

    /**
     * Get loan proceeds.
     */
    protected function getLoanProceeds(): float
    {
        return $this->newJournalEntryLineQuery()->whereHas('journalEntry', function ($q) {
            $q->where('status', 'posted')
                ->whereBetween('entry_date', [$this->startDate, $this->endDate]);
        })->whereHas('account', function ($q) {
            $q->where('name', 'like', '%Loan%')
                ->orWhere('name', 'like', '%Borrowing%');
        })->sum('credit');
    }

    /**
     * Get loan repayments.
     */
    protected function getLoanRepayments(): float
    {
        return $this->newJournalEntryLineQuery()->whereHas('journalEntry', function ($q) {
            $q->where('status', 'posted')
                ->whereBetween('entry_date', [$this->startDate, $this->endDate]);
        })->whereHas('account', function ($q) {
            $q->where('name', 'like', '%Loan%')
                ->orWhere('name', 'like', '%Borrowing%');
        })->sum('debit');
    }
}
