<?php

namespace Gopos\Services;

use Gopos\Models\Account;
use Gopos\Models\Expense;
use Gopos\Models\FiscalPeriod;
use Gopos\Models\Income;
use Gopos\Models\JournalEntry;
use Gopos\Models\JournalEntryLine;
use Gopos\Models\Payment;
use Gopos\Models\Purchase;
use Gopos\Models\Sale;
use Illuminate\Support\Facades\DB;

class GeneralLedgerService
{
    /**
     * Create a journal entry with lines
     */
    public function createJournalEntry(array $data, array $lines): JournalEntry
    {
        return DB::transaction(function () use ($data, $lines) {
            $entry = JournalEntry::create([
                'entry_date' => $data['entry_date'] ?? now(),
                'description' => $data['description'] ?? '',
                'reference_type' => $data['reference_type'] ?? null,
                'reference_id' => $data['reference_id'] ?? null,
                'currency_id' => $data['currency_id'] ?? null,
                'exchange_rate' => $data['exchange_rate'] ?? 1,
                'status' => 'draft',
            ]);

            $totalDebit = 0;
            $totalCredit = 0;

            foreach ($lines as $line) {
                JournalEntryLine::create([
                    'journal_entry_id' => $entry->id,
                    'account_id' => $line['account_id'],
                    'description' => $line['description'] ?? null,
                    'debit' => $line['debit'] ?? 0,
                    'credit' => $line['credit'] ?? 0,
                ]);

                $totalDebit += $line['debit'] ?? 0;
                $totalCredit += $line['credit'] ?? 0;
            }

            $entry->update([
                'total_debit' => $totalDebit,
                'total_credit' => $totalCredit,
            ]);

            return $entry;
        });
    }

    /**
     * Post a journal entry
     */
    public function postJournalEntry(JournalEntry $entry): bool
    {
        if (! $entry->isBalanced()) {
            throw new \Exception('Journal entry is not balanced. Debits must equal credits.');
        }

        if (! $this->canPostToDate($entry->entry_date)) {
            throw new \Exception('Cannot post to a closed fiscal period.');
        }

        return $entry->post();
    }

    /**
     * Void a journal entry
     */
    public function voidJournalEntry(JournalEntry $entry, string $reason): bool
    {
        return $entry->void($reason);
    }

    /**
     * Post a sale to the general ledger
     */
    public function postSale(Sale $sale): ?JournalEntry
    {
        $arAccount = $this->getSystemAccount('accounts_receivable');
        $salesAccount = $this->getSystemAccount('sales_revenue');
        $taxAccount = $this->getSystemAccount('tax_payable');

        if (! $arAccount || ! $salesAccount) {
            return null; // System accounts not set up
        }

        $lines = [];

        // Debit: Accounts Receivable
        $lines[] = [
            'account_id' => $arAccount->id,
            'description' => "Sale #{$sale->sale_number}",
            'debit' => $sale->total_amount,
            'credit' => 0,
        ];

        // Credit: Sales Revenue (excluding tax)
        $salesAmount = $sale->total_amount - ($sale->tax_amount ?? 0);
        $lines[] = [
            'account_id' => $salesAccount->id,
            'description' => "Sale #{$sale->sale_number}",
            'debit' => 0,
            'credit' => $salesAmount,
        ];

        // Credit: Tax Payable (if applicable)
        if (($sale->tax_amount ?? 0) > 0 && $taxAccount) {
            $lines[] = [
                'account_id' => $taxAccount->id,
                'description' => "Tax on Sale #{$sale->sale_number}",
                'debit' => 0,
                'credit' => $sale->tax_amount,
            ];
        }

        $entry = $this->createJournalEntry([
            'entry_date' => $sale->sale_date,
            'description' => 'Sale to '.($sale->customer?->name ?? 'Walk-in Customer'),
            'reference_type' => Sale::class,
            'reference_id' => $sale->id,
            'currency_id' => $sale->currency_id,
            'exchange_rate' => $sale->exchange_rate,
        ], $lines);

        // Auto-post the entry
        $entry->post();

        return $entry;
    }

    /**
     * Post a purchase to the general ledger
     */
    public function postPurchase(Purchase $purchase): ?JournalEntry
    {
        $inventoryAccount = $this->getSystemAccount('inventory');
        $apAccount = $this->getSystemAccount('accounts_payable');
        $taxAccount = $this->getSystemAccount('tax_receivable');

        if (! $inventoryAccount || ! $apAccount) {
            return null;
        }

        $lines = [];

        // Debit: Inventory (excluding tax)
        $inventoryAmount = $purchase->total_amount - ($purchase->tax_amount ?? 0);
        $lines[] = [
            'account_id' => $inventoryAccount->id,
            'description' => "Purchase #{$purchase->purchase_number}",
            'debit' => $inventoryAmount,
            'credit' => 0,
        ];

        // Debit: Tax Receivable (if applicable)
        if (($purchase->tax_amount ?? 0) > 0 && $taxAccount) {
            $lines[] = [
                'account_id' => $taxAccount->id,
                'description' => "Tax on Purchase #{$purchase->purchase_number}",
                'debit' => $purchase->tax_amount,
                'credit' => 0,
            ];
        }

        // Credit: Accounts Payable
        $lines[] = [
            'account_id' => $apAccount->id,
            'description' => "Purchase #{$purchase->purchase_number}",
            'debit' => 0,
            'credit' => $purchase->total_amount,
        ];

        $entry = $this->createJournalEntry([
            'entry_date' => $purchase->purchase_date,
            'description' => 'Purchase from '.($purchase->supplier?->name ?? 'Unknown'),
            'reference_type' => Purchase::class,
            'reference_id' => $purchase->id,
            'currency_id' => $purchase->currency_id,
            'exchange_rate' => $purchase->exchange_rate,
        ], $lines);

        $entry->post();

        return $entry;
    }

    /**
     * Post an expense to the general ledger
     */
    public function postExpense(Expense $expense): ?JournalEntry
    {
        $expenseAccount = $this->getSystemAccount('operating_expenses');
        $cashAccount = $this->getSystemAccount('cash');

        if (! $expenseAccount || ! $cashAccount) {
            return null;
        }

        $lines = [
            [
                'account_id' => $expenseAccount->id,
                'description' => $expense->type?->name ?? 'Expense',
                'debit' => $expense->amount,
                'credit' => 0,
            ],
            [
                'account_id' => $cashAccount->id,
                'description' => $expense->type?->name ?? 'Expense',
                'debit' => 0,
                'credit' => $expense->amount,
            ],
        ];

        $entry = $this->createJournalEntry([
            'entry_date' => $expense->created_at?->toDateString() ?? now()->toDateString(),
            'description' => $expense->note ?? ($expense->type?->name ?? 'Expense'),
            'reference_type' => Expense::class,
            'reference_id' => $expense->id,
            'currency_id' => $expense->currency_id,
            'exchange_rate' => $expense->exchange_rate,
        ], $lines);

        $entry->post();

        return $entry;
    }

    /**
     * Post income to the general ledger
     */
    public function postIncome(Income $income): ?JournalEntry
    {
        $cashAccount = $this->getSystemAccount('cash');
        $incomeAccount = $this->getSystemAccount('other_income');

        if (! $cashAccount || ! $incomeAccount) {
            return null;
        }

        $lines = [
            [
                'account_id' => $cashAccount->id,
                'description' => $income->type?->name ?? 'Income',
                'debit' => $income->amount,
                'credit' => 0,
            ],
            [
                'account_id' => $incomeAccount->id,
                'description' => $income->type?->name ?? 'Income',
                'debit' => 0,
                'credit' => $income->amount,
            ],
        ];

        $entry = $this->createJournalEntry([
            'entry_date' => $income->created_at?->toDateString() ?? now()->toDateString(),
            'description' => $income->description ?? ($income->type?->name ?? 'Income'),
            'reference_type' => Income::class,
            'reference_id' => $income->id,
            'currency_id' => $income->currency_id,
            'exchange_rate' => $income->exchange_rate,
        ], $lines);

        $entry->post();

        return $entry;
    }

    /**
     * Post a payment to the general ledger
     */
    public function postPayment(Payment $payment): ?JournalEntry
    {
        $cashAccount = $this->getSystemAccount('cash');

        if (! $cashAccount) {
            return null;
        }

        $lines = [];

        if ($payment->type === 'sale') {
            // Customer payment - Debit Cash, Credit AR
            $arAccount = $this->getSystemAccount('accounts_receivable');
            if (! $arAccount) {
                return null;
            }

            $lines = [
                [
                    'account_id' => $cashAccount->id,
                    'description' => 'Payment received',
                    'debit' => $payment->amount,
                    'credit' => 0,
                ],
                [
                    'account_id' => $arAccount->id,
                    'description' => 'Payment received',
                    'debit' => 0,
                    'credit' => $payment->amount,
                ],
            ];
        } elseif ($payment->type === 'purchase') {
            // Supplier payment - Debit AP, Credit Cash
            $apAccount = $this->getSystemAccount('accounts_payable');
            if (! $apAccount) {
                return null;
            }

            $lines = [
                [
                    'account_id' => $apAccount->id,
                    'description' => 'Payment made',
                    'debit' => $payment->amount,
                    'credit' => 0,
                ],
                [
                    'account_id' => $cashAccount->id,
                    'description' => 'Payment made',
                    'debit' => 0,
                    'credit' => $payment->amount,
                ],
            ];
        }

        if (empty($lines)) {
            return null;
        }

        $entry = $this->createJournalEntry([
            'entry_date' => $payment->created_at?->toDateString() ?? now()->toDateString(),
            'description' => "Payment - {$payment->type}",
            'reference_type' => Payment::class,
            'reference_id' => $payment->id,
            'currency_id' => $payment->currency_id,
            'exchange_rate' => $payment->exchange_rate,
        ], $lines);

        $entry->post();

        return $entry;
    }

    /**
     * Update account balance
     */
    public function updateAccountBalance(Account $account): void
    {
        $account->updateBalance();
    }

    /**
     * Get account balance as of a date
     */
    public function getAccountBalance(Account $account, ?string $date = null): float
    {
        if ($date) {
            return $account->getBalanceForPeriod('1900-01-01', $date);
        }

        return $account->calculateBalance();
    }

    /**
     * Get trial balance
     */
    public function getTrialBalance(?string $date = null): array
    {
        $accounts = Account::with('accountType')
            ->orderBy('code')
            ->get();

        $trialBalance = [];
        $totalDebit = 0;
        $totalCredit = 0;

        foreach ($accounts as $account) {
            $balance = $date
                ? $account->getBalanceForPeriod('1900-01-01', $date)
                : $account->calculateBalance();

            if ($balance == 0) {
                continue; // Skip zero-balance accounts
            }

            $debit = $account->isDebitBalance() && $balance > 0 ? $balance : ($account->isCreditBalance() && $balance < 0 ? abs($balance) : 0);
            $credit = $account->isCreditBalance() && $balance > 0 ? $balance : ($account->isDebitBalance() && $balance < 0 ? abs($balance) : 0);

            $trialBalance[] = [
                'account_code' => $account->code,
                'account_name' => $account->localized_name,
                'account_type' => $account->accountType?->localized_name,
                'debit' => $debit,
                'credit' => $credit,
            ];

            $totalDebit += $debit;
            $totalCredit += $credit;
        }

        return [
            'accounts' => $trialBalance,
            'total_debit' => $totalDebit,
            'total_credit' => $totalCredit,
            'is_balanced' => abs($totalDebit - $totalCredit) < 0.01,
        ];
    }

    /**
     * Check if posting is allowed for a date
     */
    public function canPostToDate(string $date): bool
    {
        $period = FiscalPeriod::forDate($date)->first();

        if (! $period) {
            return true; // No period defined, allow posting
        }

        return $period->isOpen();
    }

    /**
     * Get a system account by code/name
     */
    public function getSystemAccount(string $identifier): ?Account
    {
        // Map common identifiers to account codes
        $codeMap = [
            'cash' => '1100',
            'bank' => '1200',
            'accounts_receivable' => '1300',
            'inventory' => '1400',
            'accounts_payable' => '2100',
            'tax_payable' => '2200',
            'tax_receivable' => '1500',
            'sales_revenue' => '4100',
            'other_income' => '4200',
            'cogs' => '5100',
            'operating_expenses' => '5200',
            'capital' => '3100',
            'retained_earnings' => '3200',
        ];

        $code = $codeMap[$identifier] ?? $identifier;

        return Account::where('code', $code)->first();
    }

    /**
     * Validate a journal entry
     */
    public function validateJournalEntry(JournalEntry $entry): array
    {
        $errors = [];

        if (! $entry->isBalanced()) {
            $errors[] = 'Journal entry is not balanced. Total debits must equal total credits.';
        }

        if ($entry->lines->isEmpty()) {
            $errors[] = 'Journal entry must have at least two lines.';
        }

        if ($entry->lines->count() < 2) {
            $errors[] = 'Journal entry must have at least two lines for double-entry bookkeeping.';
        }

        if (! $this->canPostToDate($entry->entry_date->toDateString())) {
            $errors[] = 'Cannot post to a closed fiscal period.';
        }

        return $errors;
    }
}
