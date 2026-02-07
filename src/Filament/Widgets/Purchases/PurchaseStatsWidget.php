<?php

namespace Gopos\Filament\Widgets\Purchases;

use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Gopos\Models\Currency;
use Gopos\Models\Purchase;
use Gopos\Models\PurchaseReturn;
use Gopos\Models\Supplier;
use Illuminate\Support\HtmlString;

class PurchaseStatsWidget extends BaseWidget
{
    use InteractsWithPageFilters;

    protected static bool $isLazy = true;

    protected static ?int $sort = 13;

    protected function getStats(): array
    {
        $startDate = $this->filters['startDate'] ?? null;
        $endDate = $this->filters['endDate'] ?? null;

        $baseCurrency = Currency::getBaseCurrency();
        $symbol = $baseCurrency?->symbol ?? $baseCurrency?->code ?? 'IQD';
        $decimals = $baseCurrency?->decimal_places ?? 0;

        // Total Purchases (with date filter)
        $purchasesQuery = Purchase::query();
        if ($startDate && $endDate) {
            $purchasesQuery->whereBetween('purchase_date', [$startDate, $endDate]);
        }
        $totalPurchases = $purchasesQuery->sum('amount_in_base_currency');
        $purchaseCount = $purchasesQuery->count();

        // This Month's Purchases
        $monthPurchases = Purchase::whereMonth('purchase_date', now()->month)
            ->whereYear('purchase_date', now()->year)
            ->sum('amount_in_base_currency');

        // Purchase Returns
        $returnsQuery = PurchaseReturn::query();
        if ($startDate && $endDate) {
            $returnsQuery->whereBetween('return_date', [$startDate, $endDate]);
        }
        $returnsAmount = $returnsQuery->sum('total_amount');
        $returnsCount = $returnsQuery->count();

        // Outstanding Payables (total - paid)
        $outstandingPayables = Purchase::query()
            ->selectRaw('SUM(amount_in_base_currency - paid_amount) as outstanding')
            ->value('outstanding') ?? 0;

        // Active Suppliers
        $activeSuppliers = Supplier::where('active', true)->count();

        return [
            Stat::make(__('Total Purchases'), number_format($totalPurchases, $decimals).' '.$symbol)
                ->description(__(':count orders', ['count' => $purchaseCount]))
                ->icon('heroicon-o-shopping-bag')
                ->color('primary'),

            Stat::make(__('This Month'), number_format($monthPurchases, $decimals).' '.$symbol)
                ->icon('heroicon-o-calendar-days')
                ->color('info'),

            Stat::make(__('Returns'), number_format($returnsAmount, $decimals).' '.$symbol)
                ->description(__(':count returns', ['count' => $returnsCount]))
                ->icon('heroicon-o-arrow-uturn-left')
                ->color('warning'),

            Stat::make(__('Outstanding Payables'), number_format($outstandingPayables, $decimals).' '.$symbol)
                ->description(__('Unpaid balance'))
                ->icon('heroicon-o-banknotes')
                ->value(new HtmlString('<span class="text-danger-600">'.number_format($outstandingPayables, $decimals).' '.$symbol.'</span>'))
                ->color('danger'),

            Stat::make(__('Active Suppliers'), number_format($activeSuppliers))
                ->icon('heroicon-o-truck')
                ->color('success'),
        ];
    }
}
