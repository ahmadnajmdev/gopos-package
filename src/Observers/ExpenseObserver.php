<?php

namespace Gopos\Observers;

use Gopos\Models\Expense;
use Gopos\Services\GeneralLedgerService;

class ExpenseObserver
{
    protected GeneralLedgerService $glService;

    public function __construct(GeneralLedgerService $glService)
    {
        $this->glService = $glService;
    }

    /**
     * Handle the Expense "created" event.
     */
    public function created(Expense $expense): void
    {
        // Post to General Ledger
        $this->glService->postExpense($expense);
    }
}
