<?php

namespace Gopos\Events;

use Gopos\Models\PayrollPeriod;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PayrollApproved
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public PayrollPeriod $payrollPeriod
    ) {}
}
