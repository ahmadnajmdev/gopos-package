<?php

namespace Gopos\Listeners;

use Gopos\Events\PayrollApproved;
use Gopos\Notifications\PayslipAvailable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class SendPayslipNotification implements ShouldQueue
{
    public function handle(PayrollApproved $event): void
    {
        $period = $event->payrollPeriod;

        foreach ($period->payslips as $payslip) {
            $employee = $payslip->employee;

            if ($employee->user) {
                $employee->user->notify(new PayslipAvailable($payslip));

                Log::info('Payslip notification sent', [
                    'employee_id' => $employee->id,
                    'user_id' => $employee->user->id,
                    'payslip_id' => $payslip->id,
                ]);
            }
        }

        Log::info('Payslip notifications sent for payroll period', [
            'period_id' => $period->id,
            'period_name' => $period->name,
            'payslips_count' => $period->payslips->count(),
        ]);
    }
}
