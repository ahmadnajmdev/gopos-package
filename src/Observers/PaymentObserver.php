<?php

namespace Gopos\Observers;

use Gopos\Models\Payment;
use Gopos\Services\GeneralLedgerService;

class PaymentObserver
{
    protected GeneralLedgerService $glService;

    public function __construct(GeneralLedgerService $glService)
    {
        $this->glService = $glService;
    }

    /**
     * Handle the Payment "created" event.
     */
    public function created(Payment $payment): void
    {
        // Post to General Ledger
        $this->glService->postPayment($payment);
    }
}
