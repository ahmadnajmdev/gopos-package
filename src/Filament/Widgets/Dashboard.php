<?php

namespace Gopos\Filament\Widgets;

use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Gopos\Models\Customer;
use Gopos\Models\Product;
use Gopos\Models\Purchase;
use Gopos\Models\Sale;
use Gopos\Models\Supplier;
use Gopos\Services\FinancialService;
use Illuminate\Support\HtmlString;

class Dashboard extends BaseWidget
{
    protected static bool $isLazy = false;

    protected static ?int $sort = 1;

    use InteractsWithPageFilters;

    protected function getStats(): array
    {
        $startDate = $this->pageFilters['startDate'] ?? null;
        $endDate = $this->pageFilters['endDate'] ?? null;
        $sales = Sale::query()->count();
        $customers = Customer::query()->count();
        $suppliers = Supplier::query()->count();
        $products = Product::query()->count();
        $purchases = Purchase::query()->count();

        $income = FinancialService::getIncome($startDate, $endDate);
        $cogs = FinancialService::getCostOfGoodsSold($startDate, $endDate);
        $operatingExpenses = FinancialService::getOperatingExpenses($startDate, $endDate);
        $profit = FinancialService::getProfit($startDate, $endDate);
        $grossProfit = $income - $cogs;

        $profitMargin = $income > 0 ? ($profit / $income) * 100 : 0;
        $grossMargin = $income > 0 ? ($grossProfit / $income) * 100 : 0;
        $baseCurrency = \Gopos\Models\Currency::getBaseCurrency();
        $currencySymbol = $baseCurrency?->symbol ?? $baseCurrency?->code;
        $currencyDecimals = $baseCurrency?->decimal_places ?? 0;

        $profitFormatted = number_format($profit, $currencyDecimals).' '.$currencySymbol;
        $grossProfitFormatted = number_format($grossProfit, $currencyDecimals).' '.$currencySymbol;
        $incomeFormatted = number_format($income, $currencyDecimals).' '.$currencySymbol;
        $cogsFormatted = number_format($cogs, $currencyDecimals).' '.$currencySymbol;
        $operatingExpensesFormatted = number_format($operatingExpenses, $currencyDecimals).' '.$currencySymbol;
        $profitMarginFormatted = number_format($profitMargin, 2).'%';
        $grossMarginFormatted = number_format($grossMargin, 2).'%';

        $profitLossColor = $profit > 0 ? 'green-500' : 'red-600';
        $grossProfitColor = $grossProfit > 0 ? 'green-500' : 'red-600';

        return [
            Stat::make(__('Sales'), $sales)
                ->icon('heroicon-o-shopping-cart')
                ->color('success')
                ->description(__('Total Sales')),
            Stat::make(__('Customers'), $customers)
                ->icon('heroicon-o-users')
                ->color('primary')
                ->description(__('Total Customers')),
            Stat::make(__('Suppliers'), $suppliers)
                ->icon('heroicon-o-users')
                ->color('primary')
                ->extraAttributes(['class' => 'fi-wi-stats-overview-stat-value text-primary-500'])
                ->description(__('Total Suppliers')),
            Stat::make(__('Products'), $products)
                ->icon('heroicon-o-cube')
                ->color('primary')
                ->description(__('Total Products')),
            Stat::make(__('Purchases'), $purchases)
                ->icon('heroicon-o-shopping-bag')
                ->color('primary')
                ->description(__('Total Purchases')),
            Stat::make(__('Revenue'), $incomeFormatted)
                ->icon('heroicon-o-currency-dollar')
                ->value(new HtmlString('<span class="text-green-500">'.$incomeFormatted.'</span>'))
                ->color('success')
                ->description(__('Total Revenue (Sales + Income)')),
            Stat::make(__('COGS'), $cogsFormatted)
                ->icon('heroicon-o-currency-dollar')
                ->value(new HtmlString('<span class="text-orange-500">'.$cogsFormatted.'</span>'))
                ->color('warning')
                ->description(__('Cost of Goods Sold')),
            Stat::make(__('Gross Profit'), $grossProfitFormatted)
                ->icon('heroicon-o-currency-dollar')
                ->value(new HtmlString('<span class="text-'.$grossProfitColor.'">'.$grossProfitFormatted.'</span>'))
                ->color('success')
                ->description(__('Revenue - COGS').' ('.$grossMarginFormatted.')'),
            Stat::make(__('Operating Expenses'), $operatingExpensesFormatted)
                ->icon('heroicon-o-currency-dollar')
                ->value(new HtmlString('<span class="text-danger-600">'.$operatingExpensesFormatted.'</span>'))
                ->color('danger')
                ->description(__('Business Operating Expenses')),
            Stat::make(__('Net Profit'), $profitFormatted)
                ->icon('heroicon-o-currency-dollar')
                ->value(new HtmlString('<span class="text-'.$profitLossColor.'">'.$profitFormatted.'</span>'))
                ->color('success')
                ->description(__('Gross Profit - Operating Expenses').' ('.$profitMarginFormatted.')'),
        ];
    }
}
