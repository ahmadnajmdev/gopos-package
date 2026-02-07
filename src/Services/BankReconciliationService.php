<?php

namespace Gopos\Services;

use Gopos\Models\BankAccount;
use Gopos\Models\BankReconciliation;
use Gopos\Models\BankReconciliationItem;
use Gopos\Models\BankTransaction;
use Gopos\Models\JournalEntry;
use Gopos\Models\JournalEntryLine;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BankReconciliationService
{
    /**
     * Create a new reconciliation.
     */
    public function createReconciliation(
        BankAccount $bankAccount,
        float $statementBalance,
        string $statementDate,
        string $statementStartDate,
        string $statementEndDate
    ): BankReconciliation {
        return BankReconciliation::create([
            'bank_account_id' => $bankAccount->id,
            'statement_balance' => $statementBalance,
            'statement_date' => $statementDate,
            'statement_start_date' => $statementStartDate,
            'statement_end_date' => $statementEndDate,
            'book_balance' => $bankAccount->current_balance,
            'status' => BankReconciliation::STATUS_DRAFT,
        ]);
    }

    /**
     * Get unreconciled transactions for a bank account.
     */
    public function getUnreconciledTransactions(BankAccount $bankAccount, ?string $asOfDate = null): Collection
    {
        $query = $bankAccount->transactions()
            ->unreconciled()
            ->orderBy('transaction_date');

        if ($asOfDate) {
            $query->where('transaction_date', '<=', $asOfDate);
        }

        return $query->get();
    }

    /**
     * Get outstanding checks.
     */
    public function getOutstandingChecks(BankAccount $bankAccount, ?string $asOfDate = null): Collection
    {
        $query = $bankAccount->transactions()
            ->where('type', BankTransaction::TYPE_WITHDRAWAL)
            ->whereNotNull('check_number')
            ->unreconciled()
            ->orderBy('transaction_date');

        if ($asOfDate) {
            $query->where('transaction_date', '<=', $asOfDate);
        }

        return $query->get();
    }

    /**
     * Get deposits in transit.
     */
    public function getDepositsInTransit(BankAccount $bankAccount, ?string $asOfDate = null): Collection
    {
        $query = $bankAccount->transactions()
            ->where('type', BankTransaction::TYPE_DEPOSIT)
            ->where('status', BankTransaction::STATUS_PENDING)
            ->orderBy('transaction_date');

        if ($asOfDate) {
            $query->where('transaction_date', '<=', $asOfDate);
        }

        return $query->get();
    }

    /**
     * Add outstanding check to reconciliation.
     */
    public function addOutstandingCheck(
        BankReconciliation $reconciliation,
        BankTransaction $transaction
    ): BankReconciliationItem {
        return BankReconciliationItem::create([
            'bank_reconciliation_id' => $reconciliation->id,
            'bank_transaction_id' => $transaction->id,
            'type' => BankReconciliationItem::TYPE_OUTSTANDING_CHECK,
            'description' => "Check #{$transaction->check_number} - {$transaction->description}",
            'amount' => -abs($transaction->amount), // Negative because it's an outstanding deduction
        ]);
    }

    /**
     * Add deposit in transit to reconciliation.
     */
    public function addDepositInTransit(
        BankReconciliation $reconciliation,
        BankTransaction $transaction
    ): BankReconciliationItem {
        return BankReconciliationItem::create([
            'bank_reconciliation_id' => $reconciliation->id,
            'bank_transaction_id' => $transaction->id,
            'type' => BankReconciliationItem::TYPE_DEPOSIT_IN_TRANSIT,
            'description' => "Deposit - {$transaction->description}",
            'amount' => abs($transaction->amount),
        ]);
    }

    /**
     * Add bank charge adjustment.
     */
    public function addBankCharge(
        BankReconciliation $reconciliation,
        float $amount,
        string $description
    ): BankReconciliationItem {
        return BankReconciliationItem::create([
            'bank_reconciliation_id' => $reconciliation->id,
            'type' => BankReconciliationItem::TYPE_BANK_CHARGE,
            'description' => $description,
            'amount' => -abs($amount),
        ]);
    }

    /**
     * Add bank interest.
     */
    public function addBankInterest(
        BankReconciliation $reconciliation,
        float $amount,
        string $description
    ): BankReconciliationItem {
        return BankReconciliationItem::create([
            'bank_reconciliation_id' => $reconciliation->id,
            'type' => BankReconciliationItem::TYPE_BANK_INTEREST,
            'description' => $description,
            'amount' => abs($amount),
        ]);
    }

    /**
     * Add adjustment.
     */
    public function addAdjustment(
        BankReconciliation $reconciliation,
        float $amount,
        string $description,
        string $type = BankReconciliationItem::TYPE_ADJUSTMENT
    ): BankReconciliationItem {
        return BankReconciliationItem::create([
            'bank_reconciliation_id' => $reconciliation->id,
            'type' => $type,
            'description' => $description,
            'amount' => $amount,
        ]);
    }

    /**
     * Calculate reconciliation summary.
     */
    public function calculateSummary(BankReconciliation $reconciliation): array
    {
        $bookBalance = $reconciliation->book_balance;

        // Book adjustments (charges, interest, errors)
        $bookAdjustments = $reconciliation->items()
            ->bookAdjustments()
            ->sum('amount');

        $adjustedBookBalance = $bookBalance + $bookAdjustments;

        // Outstanding items
        $outstandingChecks = $reconciliation->items()
            ->where('type', BankReconciliationItem::TYPE_OUTSTANDING_CHECK)
            ->sum('amount');

        $depositsInTransit = $reconciliation->items()
            ->where('type', BankReconciliationItem::TYPE_DEPOSIT_IN_TRANSIT)
            ->sum('amount');

        // Adjusted statement balance
        $adjustedStatementBalance = $reconciliation->statement_balance + abs($outstandingChecks) - $depositsInTransit;

        $difference = $adjustedStatementBalance - $adjustedBookBalance;

        return [
            'book_balance' => $bookBalance,
            'book_adjustments' => $bookAdjustments,
            'adjusted_book_balance' => $adjustedBookBalance,
            'statement_balance' => $reconciliation->statement_balance,
            'outstanding_checks' => $outstandingChecks,
            'deposits_in_transit' => $depositsInTransit,
            'adjusted_statement_balance' => $adjustedStatementBalance,
            'difference' => $difference,
            'is_balanced' => abs($difference) < 0.01,
        ];
    }

    /**
     * Complete reconciliation and create journal entries for adjustments.
     */
    public function completeReconciliation(
        BankReconciliation $reconciliation,
        ?int $bankChargeAccountId = null,
        ?int $interestIncomeAccountId = null
    ): void {
        DB::transaction(function () use ($reconciliation, $bankChargeAccountId, $interestIncomeAccountId) {
            // Create journal entries for book adjustments
            $this->createAdjustmentJournalEntries(
                $reconciliation,
                $bankChargeAccountId,
                $interestIncomeAccountId
            );

            // Complete the reconciliation
            $reconciliation->complete();
        });
    }

    /**
     * Create journal entries for bank adjustments.
     */
    protected function createAdjustmentJournalEntries(
        BankReconciliation $reconciliation,
        ?int $bankChargeAccountId,
        ?int $interestIncomeAccountId
    ): void {
        $bankAccount = $reconciliation->bankAccount;
        $glAccountId = $bankAccount->account_id;

        // Bank charges
        $bankCharges = $reconciliation->items()
            ->where('type', BankReconciliationItem::TYPE_BANK_CHARGE)
            ->get();

        foreach ($bankCharges as $charge) {
            if (! $bankChargeAccountId) {
                continue;
            }

            $entry = JournalEntry::create([
                'entry_number' => JournalEntry::generateEntryNumber(),
                'entry_date' => $reconciliation->statement_date,
                'reference' => "BANK-CHG-{$reconciliation->reconciliation_number}",
                'description' => $charge->description,
                'status' => 'posted',
            ]);

            // Debit expense
            JournalEntryLine::create([
                'journal_entry_id' => $entry->id,
                'account_id' => $bankChargeAccountId,
                'debit' => abs($charge->amount),
                'credit' => 0,
            ]);

            // Credit bank
            JournalEntryLine::create([
                'journal_entry_id' => $entry->id,
                'account_id' => $glAccountId,
                'debit' => 0,
                'credit' => abs($charge->amount),
            ]);

            // Record bank transaction
            $bankAccount->recordTransaction([
                'type' => BankTransaction::TYPE_FEE,
                'description' => $charge->description,
                'amount' => abs($charge->amount),
                'transaction_date' => $reconciliation->statement_date,
                'status' => BankTransaction::STATUS_RECONCILED,
                'journal_entry_id' => $entry->id,
            ]);
        }

        // Bank interest
        $interests = $reconciliation->items()
            ->where('type', BankReconciliationItem::TYPE_BANK_INTEREST)
            ->get();

        foreach ($interests as $interest) {
            if (! $interestIncomeAccountId) {
                continue;
            }

            $entry = JournalEntry::create([
                'entry_number' => JournalEntry::generateEntryNumber(),
                'entry_date' => $reconciliation->statement_date,
                'reference' => "BANK-INT-{$reconciliation->reconciliation_number}",
                'description' => $interest->description,
                'status' => 'posted',
            ]);

            // Debit bank
            JournalEntryLine::create([
                'journal_entry_id' => $entry->id,
                'account_id' => $glAccountId,
                'debit' => abs($interest->amount),
                'credit' => 0,
            ]);

            // Credit interest income
            JournalEntryLine::create([
                'journal_entry_id' => $entry->id,
                'account_id' => $interestIncomeAccountId,
                'debit' => 0,
                'credit' => abs($interest->amount),
            ]);

            // Record bank transaction
            $bankAccount->recordTransaction([
                'type' => BankTransaction::TYPE_INTEREST,
                'description' => $interest->description,
                'amount' => abs($interest->amount),
                'transaction_date' => $reconciliation->statement_date,
                'status' => BankTransaction::STATUS_RECONCILED,
                'journal_entry_id' => $entry->id,
            ]);
        }
    }
}
