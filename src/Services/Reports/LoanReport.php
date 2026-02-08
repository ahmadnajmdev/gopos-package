<?php

namespace Gopos\Services\Reports;

use Gopos\Models\Currency;
use Gopos\Models\EmployeeLoan;
use Illuminate\Support\Collection;

class LoanReport extends BaseReport
{
    protected string $title = 'Loan Report';

    protected bool $showTotals = true;

    protected array $columns = [
        'employee_name' => ['label' => 'Employee Name', 'type' => 'text'],
        'loan_type' => ['label' => 'Loan Type', 'type' => 'text'],
        'principal' => ['label' => 'Principal', 'type' => 'currency'],
        'interest_rate' => ['label' => 'Interest Rate', 'type' => 'number'],
        'total_amount' => ['label' => 'Total Amount', 'type' => 'currency'],
        'monthly_installment' => ['label' => 'Monthly Installment', 'type' => 'currency'],
        'paid_installments' => ['label' => 'Paid Installments', 'type' => 'number'],
        'remaining_balance' => ['label' => 'Remaining Balance', 'type' => 'currency'],
        'status' => ['label' => 'Status', 'type' => 'text'],
    ];

    protected array $totalColumns = ['principal', 'total_amount', 'remaining_balance'];

    public function getData(string $startDate, string $endDate): Collection
    {
        $baseCurrency = Currency::getBaseCurrency();
        $currency = $baseCurrency?->symbol ?? $baseCurrency?->code;

        return EmployeeLoan::query()
            ->where('start_date', '<=', $endDate)
            ->where(function ($query) use ($startDate) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', $startDate);
            })
            ->with(['employee'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function (EmployeeLoan $loan) use ($currency) {
                return [
                    'employee_name' => $loan->employee?->full_name ?? __('N/A'),
                    'loan_type' => $this->formatLoanType($loan->loan_type),
                    'principal' => round($loan->loan_amount, 2),
                    'interest_rate' => round($loan->interest_rate, 2),
                    'total_amount' => round($loan->total_amount, 2),
                    'monthly_installment' => round($loan->installment_amount, 2),
                    'paid_installments' => $loan->paid_installments,
                    'remaining_balance' => round($loan->remaining_amount, 2),
                    'status' => $loan->status_label,
                    'currency' => $currency,
                ];
            });
    }

    private function formatLoanType(?string $type): string
    {
        return match ($type) {
            EmployeeLoan::TYPE_SALARY_ADVANCE => __('Salary Advance'),
            EmployeeLoan::TYPE_PERSONAL_LOAN => __('Personal Loan'),
            EmployeeLoan::TYPE_EMERGENCY_LOAN => __('Emergency Loan'),
            default => $type ?? __('N/A'),
        };
    }
}
