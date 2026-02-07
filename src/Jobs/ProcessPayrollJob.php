<?php

namespace Gopos\Jobs;

use Gopos\Models\PayrollPeriod;
use Gopos\Services\HR\PayrollService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessPayrollJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public PayrollPeriod $payrollPeriod,
        public int $processedBy
    ) {}

    public function handle(PayrollService $payrollService): void
    {
        $payrollService->processPayroll($this->payrollPeriod, $this->processedBy);
    }
}
