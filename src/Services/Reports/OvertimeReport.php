<?php

namespace Gopos\Services\Reports;

use Gopos\Models\Currency;
use Gopos\Models\OvertimeRequest;
use Illuminate\Support\Collection;

class OvertimeReport extends BaseReport
{
    protected string $title = 'Overtime Report';

    protected bool $showTotals = true;

    protected array $columns = [
        'employee_name' => ['label' => 'Employee Name', 'type' => 'text'],
        'department' => ['label' => 'Department', 'type' => 'text'],
        'date' => ['label' => 'Date', 'type' => 'date'],
        'hours' => ['label' => 'Hours', 'type' => 'number'],
        'rate_multiplier' => ['label' => 'Rate Multiplier', 'type' => 'number'],
        'amount' => ['label' => 'Amount', 'type' => 'currency'],
        'status' => ['label' => 'Status', 'type' => 'text'],
    ];

    protected array $totalColumns = ['hours', 'amount'];

    public function getData(string $startDate, string $endDate): Collection
    {
        $baseCurrency = Currency::getBaseCurrency();
        $currency = $baseCurrency?->symbol ?? $baseCurrency?->code;

        return OvertimeRequest::query()
            ->whereBetween('date', [$startDate, $endDate])
            ->with(['employee.department'])
            ->orderBy('date', 'desc')
            ->get()
            ->map(function (OvertimeRequest $overtime) use ($currency) {
                return [
                    'employee_name' => $overtime->employee?->full_name ?? __('N/A'),
                    'department' => $overtime->employee?->department?->localized_name ?? __('N/A'),
                    'date' => $overtime->date->format('Y-m-d'),
                    'hours' => round($overtime->hours, 2),
                    'rate_multiplier' => round($overtime->overtime_rate, 2),
                    'amount' => round($overtime->amount ?? 0, 2),
                    'status' => $overtime->status_label,
                    'currency' => $currency,
                ];
            });
    }
}
