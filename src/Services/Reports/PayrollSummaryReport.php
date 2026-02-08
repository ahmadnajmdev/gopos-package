<?php

namespace Gopos\Services\Reports;

use Gopos\Models\Currency;
use Gopos\Models\PayrollPeriod;
use Illuminate\Support\Collection;

class PayrollSummaryReport extends BaseReport
{
    protected string $title = 'Payroll Summary Report';

    protected bool $showTotals = true;

    protected array $columns = [
        'period' => ['label' => 'Period', 'type' => 'text'],
        'employee_count' => ['label' => 'Employee Count', 'type' => 'number'],
        'total_gross' => ['label' => 'Total Gross', 'type' => 'currency'],
        'total_deductions' => ['label' => 'Total Deductions', 'type' => 'currency'],
        'total_net' => ['label' => 'Total Net', 'type' => 'currency'],
        'status' => ['label' => 'Status', 'type' => 'text'],
    ];

    protected array $totalColumns = ['employee_count', 'total_gross', 'total_deductions', 'total_net'];

    public function getData(string $startDate, string $endDate): Collection
    {
        $baseCurrency = Currency::getBaseCurrency();
        $currency = $baseCurrency?->symbol ?? $baseCurrency?->code;

        return PayrollPeriod::query()
            ->whereBetween('start_date', [$startDate, $endDate])
            ->orderBy('start_date', 'desc')
            ->get()
            ->map(function (PayrollPeriod $period) use ($currency) {
                return [
                    'period' => $period->period_label,
                    'employee_count' => $period->employee_count,
                    'total_gross' => $period->total_gross,
                    'total_deductions' => $period->total_deductions,
                    'total_net' => $period->total_net,
                    'status' => $period->status_label,
                    'currency' => $currency,
                ];
            });
    }
}
