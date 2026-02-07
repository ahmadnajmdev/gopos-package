<?php

namespace Gopos\Observers;

use Gopos\Models\Purchase;
use Gopos\Services\GeneralLedgerService;

class PurchaseObserver
{
    protected GeneralLedgerService $glService;

    public function __construct(GeneralLedgerService $glService)
    {
        $this->glService = $glService;
    }

    /**
     * Handle the Purchase "created" event.
     */
    public function created(Purchase $purchase): void
    {
        // Post to General Ledger
        $this->glService->postPurchase($purchase);
    }
}
