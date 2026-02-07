<?php

namespace Gopos\Observers;

use Gopos\Models\Income;
use Gopos\Services\GeneralLedgerService;

class IncomeObserver
{
    protected GeneralLedgerService $glService;

    public function __construct(GeneralLedgerService $glService)
    {
        $this->glService = $glService;
    }

    /**
     * Handle the Income "created" event.
     */
    public function created(Income $income): void
    {
        // Post to General Ledger
        $this->glService->postIncome($income);
    }
}
