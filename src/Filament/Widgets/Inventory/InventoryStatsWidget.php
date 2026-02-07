<?php

namespace Gopos\Filament\Widgets\Inventory;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Gopos\Models\Currency;
use Gopos\Models\Product;
use Gopos\Models\ProductBatch;
use Illuminate\Support\HtmlString;

class InventoryStatsWidget extends BaseWidget
{
    protected static bool $isLazy = true;

    protected static ?int $sort = 9;

    protected function getStats(): array
    {
        $baseCurrency = Currency::getBaseCurrency();
        $symbol = $baseCurrency?->symbol ?? $baseCurrency?->code ?? 'IQD';
        $decimals = $baseCurrency?->decimal_places ?? 0;

        // Total products with stock
        $totalProducts = Product::count();
        $productsWithStock = Product::whereHas('movements', function ($q) {
            $q->havingRaw('SUM(quantity) > 0');
        })->count();

        // Low stock items
        $lowStockCount = Product::query()
            ->whereNotNull('low_stock_alert')
            ->where('low_stock_alert', '>', 0)
            ->get()
            ->filter(function ($product) {
                return $product->stock <= $product->low_stock_alert;
            })
            ->count();

        // Expiring batches (within 30 days)
        $expiringCount = ProductBatch::query()
            ->where('quantity', '>', 0)
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '<=', now()->addDays(30))
            ->where('expiry_date', '>', now())
            ->count();

        // Expired batches
        $expiredCount = ProductBatch::query()
            ->where('quantity', '>', 0)
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '<', now())
            ->count();

        // Total stock value (quantity * average_cost)
        $stockValue = Product::query()
            ->get()
            ->sum(function ($product) {
                return $product->stock * ($product->average_cost ?? $product->cost ?? 0);
            });

        return [
            Stat::make(__('Total Products'), number_format($totalProducts))
                ->description(__(':count with stock', ['count' => $productsWithStock]))
                ->icon('heroicon-o-cube')
                ->color('primary'),

            Stat::make(__('Low Stock'), number_format($lowStockCount))
                ->description(__('Below minimum level'))
                ->icon('heroicon-o-exclamation-triangle')
                ->value(new HtmlString('<span class="text-warning-600">'.number_format($lowStockCount).'</span>'))
                ->color('warning'),

            Stat::make(__('Expiring Soon'), number_format($expiringCount))
                ->description(__('Within 30 days'))
                ->icon('heroicon-o-clock')
                ->value(new HtmlString('<span class="text-warning-600">'.number_format($expiringCount).'</span>'))
                ->color('warning'),

            Stat::make(__('Expired'), number_format($expiredCount))
                ->description(__('Past expiry date'))
                ->icon('heroicon-o-x-circle')
                ->value(new HtmlString('<span class="text-danger-600">'.number_format($expiredCount).'</span>'))
                ->color('danger'),

            Stat::make(__('Stock Value'), number_format($stockValue, $decimals).' '.$symbol)
                ->description(__('Total inventory value'))
                ->icon('heroicon-o-currency-dollar')
                ->color('success'),
        ];
    }
}
