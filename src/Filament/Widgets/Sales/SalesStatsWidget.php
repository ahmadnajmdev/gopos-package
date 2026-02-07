<?php

namespace Gopos\Filament\Widgets\Sales;

use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Gopos\Models\Currency;
use Gopos\Models\Sale;
use Gopos\Models\SaleReturn;
use Illuminate\Support\HtmlString;

class SalesStatsWidget extends BaseWidget
{
    use InteractsWithPageFilters;

    protected static bool $isLazy = true;

    protected static ?int $sort = 2;

    protected function getStats(): array
    {
        $startDate = $this->filters['startDate'] ?? null;
        $endDate = $this->filters['endDate'] ?? null;

        $baseCurrency = Currency::getBaseCurrency();
        $symbol = $baseCurrency?->symbol ?? $baseCurrency?->code ?? 'IQD';
        $decimals = $baseCurrency?->decimal_places ?? 0;

        // Today's Sales
        $todaySales = Sale::whereDate('sale_date', today())->sum('amount_in_base_currency');
        $todaySalesCount = Sale::whereDate('sale_date', today())->count();

        // This Week's Sales
        $weekSales = Sale::whereBetween('sale_date', [now()->startOfWeek(), now()->endOfWeek()])
            ->sum('amount_in_base_currency');
        $weekSalesCount = Sale::whereBetween('sale_date', [now()->startOfWeek(), now()->endOfWeek()])
            ->count();

        // This Month's Sales
        $monthSales = Sale::whereMonth('sale_date', now()->month)
            ->whereYear('sale_date', now()->year)
            ->sum('amount_in_base_currency');

        // Average Order Value (with date filter if provided)
        $avgOrderQuery = Sale::query();
        if ($startDate && $endDate) {
            $avgOrderQuery->whereBetween('sale_date', [$startDate, $endDate]);
        }
        $avgOrderValue = $avgOrderQuery->avg('amount_in_base_currency') ?? 0;

        // Sales Returns
        $returnsQuery = SaleReturn::query();
        if ($startDate && $endDate) {
            $returnsQuery->whereBetween('return_date', [$startDate, $endDate]);
        }
        $returnsCount = $returnsQuery->count();
        $returnsAmount = $returnsQuery->sum('total_amount');

        return [
            Stat::make(__("Today's Sales"), number_format($todaySales, $decimals).' '.$symbol)
                ->description(__(':count transactions', ['count' => $todaySalesCount]))
                ->icon('heroicon-o-shopping-cart')
                ->color('success'),

            Stat::make(__('This Week'), number_format($weekSales, $decimals).' '.$symbol)
                ->description(__(':count transactions', ['count' => $weekSalesCount]))
                ->icon('heroicon-o-calendar')
                ->color('primary'),

            Stat::make(__('This Month'), number_format($monthSales, $decimals).' '.$symbol)
                ->icon('heroicon-o-calendar-days')
                ->color('info'),

            Stat::make(__('Avg. Order Value'), number_format($avgOrderValue, $decimals).' '.$symbol)
                ->icon('heroicon-o-calculator')
                ->color('warning'),

            Stat::make(__('Returns'), number_format($returnsAmount, $decimals).' '.$symbol)
                ->description(__(':count returns', ['count' => $returnsCount]))
                ->icon('heroicon-o-arrow-uturn-left')
                ->value(new HtmlString('<span class="text-danger-600">'.number_format($returnsAmount, $decimals).' '.$symbol.'</span>'))
                ->color('danger'),
        ];
    }
}
