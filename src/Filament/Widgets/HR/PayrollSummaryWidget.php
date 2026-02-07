<?php

namespace Gopos\Filament\Widgets\HR;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Gopos\Models\Currency;
use Gopos\Models\PayrollPeriod;
use Illuminate\Support\HtmlString;

class PayrollSummaryWidget extends BaseWidget
{
    protected static bool $isLazy = true;

    protected static ?int $sort = 19;

    protected function getStats(): array
    {
        $baseCurrency = Currency::getBaseCurrency();
        $symbol = $baseCurrency?->symbol ?? $baseCurrency?->code ?? 'IQD';
        $decimals = $baseCurrency?->decimal_places ?? 0;

        // Get latest payroll period
        $latestPeriod = PayrollPeriod::latest('start_date')->first();

        if (! $latestPeriod) {
            return [
                Stat::make(__('Payroll'), __('No data'))
                    ->description(__('No payroll periods found'))
                    ->icon('heroicon-o-banknotes')
                    ->color('gray'),
            ];
        }

        $statusColors = [
            PayrollPeriod::STATUS_DRAFT => 'gray',
            PayrollPeriod::STATUS_PROCESSING => 'warning',
            PayrollPeriod::STATUS_PROCESSED => 'info',
            PayrollPeriod::STATUS_APPROVED => 'success',
            PayrollPeriod::STATUS_PAID => 'success',
            PayrollPeriod::STATUS_CANCELLED => 'danger',
        ];

        $statusLabels = [
            PayrollPeriod::STATUS_DRAFT => __('Draft'),
            PayrollPeriod::STATUS_PROCESSING => __('Processing'),
            PayrollPeriod::STATUS_PROCESSED => __('Processed'),
            PayrollPeriod::STATUS_APPROVED => __('Approved'),
            PayrollPeriod::STATUS_PAID => __('Paid'),
            PayrollPeriod::STATUS_CANCELLED => __('Cancelled'),
        ];

        return [
            Stat::make(__('Latest Period'), $latestPeriod->name)
                ->description($latestPeriod->start_date->format('M d').' - '.$latestPeriod->end_date->format('M d, Y'))
                ->icon('heroicon-o-calendar-days')
                ->color('primary'),

            Stat::make(__('Gross Salary'), number_format($latestPeriod->total_gross ?? 0, $decimals).' '.$symbol)
                ->description(__(':count employees', ['count' => $latestPeriod->employee_count ?? 0]))
                ->icon('heroicon-o-banknotes')
                ->color('info'),

            Stat::make(__('Deductions'), number_format($latestPeriod->total_deductions ?? 0, $decimals).' '.$symbol)
                ->icon('heroicon-o-minus-circle')
                ->value(new HtmlString('<span class="text-danger-600">'.number_format($latestPeriod->total_deductions ?? 0, $decimals).' '.$symbol.'</span>'))
                ->color('danger'),

            Stat::make(__('Net Payroll'), number_format($latestPeriod->total_net ?? 0, $decimals).' '.$symbol)
                ->icon('heroicon-o-currency-dollar')
                ->value(new HtmlString('<span class="text-success-600">'.number_format($latestPeriod->total_net ?? 0, $decimals).' '.$symbol.'</span>'))
                ->color('success'),

            Stat::make(__('Status'), $statusLabels[$latestPeriod->status] ?? ucfirst($latestPeriod->status))
                ->icon('heroicon-o-check-badge')
                ->color($statusColors[$latestPeriod->status] ?? 'gray'),
        ];
    }
}
