<?php

namespace Gopos\Observers;

use Gopos\Models\Sale;
use Gopos\Services\GeneralLedgerService;

class SaleObserver
{
    protected GeneralLedgerService $glService;

    public function __construct(GeneralLedgerService $glService)
    {
        $this->glService = $glService;
    }

    /**
     * Handle the Sale "created" event.
     */
    public function created(Sale $sale): void
    {
        // Post to General Ledger
        $this->glService->postSale($sale);
    }
}
