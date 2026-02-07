<?php

namespace Gopos\Listeners;

use Gopos\Events\PayrollApproved;
use Gopos\Services\GeneralLedgerService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class PostPayrollToGL implements ShouldQueue
{
    public function __construct(
        protected GeneralLedgerService $glService
    ) {}

    public function handle(PayrollApproved $event): void
    {
        $period = $event->payrollPeriod;

        $salaryExpenseAccount = $this->glService->getAccountByCode('6001');
        $cashAccount = $this->glService->getAccountByCode('1001');
        $loansReceivableAccount = $this->glService->getAccountByCode('1250');

        if (! $salaryExpenseAccount || ! $cashAccount) {
            Log::warning('Required GL accounts not found for payroll posting', [
                'period_id' => $period->id,
            ]);

            return;
        }

        $entries = [];

        // Debit: Salary Expense
        $entries[] = [
            'account_id' => $salaryExpenseAccount->id,
            'debit' => $period->total_gross,
            'credit' => 0,
            'description' => "Salary expense for {$period->name}",
        ];

        // Credit: Cash (net payroll)
        $entries[] = [
            'account_id' => $cashAccount->id,
            'debit' => 0,
            'credit' => $period->total_net,
            'description' => "Net payroll payment for {$period->name}",
        ];

        // Credit: Loans Receivable (loan deductions)
        $totalLoanDeductions = $period->payslips()->sum('loan_deduction');
        if ($totalLoanDeductions > 0 && $loansReceivableAccount) {
            $entries[] = [
                'account_id' => $loansReceivableAccount->id,
                'debit' => 0,
                'credit' => $totalLoanDeductions,
                'description' => "Loan deductions for {$period->name}",
            ];
        }

        // Other deductions as liabilities
        $otherDeductions = $period->total_deductions - $totalLoanDeductions;
        if ($otherDeductions > 0) {
            $deductionsPayableAccount = $this->glService->getAccountByCode('2200');
            if ($deductionsPayableAccount) {
                $entries[] = [
                    'account_id' => $deductionsPayableAccount->id,
                    'debit' => 0,
                    'credit' => $otherDeductions,
                    'description' => "Payroll deductions payable for {$period->name}",
                ];
            }
        }

        $this->glService->createJournalEntry(
            date: $period->payment_date,
            description: "Payroll for {$period->name}",
            lines: $entries,
            referenceType: 'payroll',
            referenceId: $period->id
        );

        Log::info('Payroll posted to GL', [
            'period_id' => $period->id,
            'period_name' => $period->name,
            'total_gross' => $period->total_gross,
            'total_net' => $period->total_net,
        ]);
    }
}
