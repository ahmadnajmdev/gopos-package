<?php

namespace Gopos\Filament\Clusters\Reports\Pages;

use BackedEnum;
use Filament\Pages\Page;
use Gopos\Filament\Clusters\Reports\ReportsCluster;
use Gopos\Models\Currency;
use Gopos\Models\Product;
use Gopos\Models\ProductBatch;
use Gopos\Models\Purchase;
use Gopos\Models\Sale;
use Gopos\Services\FinancialService;

class ReportsDashboard extends Page
{
    protected static ?string $cluster = ReportsCluster::class;

    protected string $view = 'gopos::filament.clusters.reports.pages.reports-dashboard';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-home';

    protected static ?int $navigationSort = 0;

    public static function getNavigationLabel(): string
    {
        return __('Dashboard');
    }

    public function getTitle(): string
    {
        return __('Reports Dashboard');
    }

    public function getReportCategories(): array
    {
        return [
            [
                'title' => __('Sales Reports'),
                'description' => __('Track sales performance, trends, and customer analytics'),
                'icon' => 'heroicon-o-shopping-cart',
                'color' => 'success',
                'reports' => [
                    [
                        'title' => __('Sales Report'),
                        'url' => SalesReportPage::getUrl(),
                        'icon' => 'heroicon-o-currency-dollar',
                    ],
                    [
                        'title' => __('Sales By Category Report'),
                        'url' => SaleByProductReportPage::getUrl(),
                        'icon' => 'heroicon-o-squares-2x2',
                    ],
                ],
            ],
            [
                'title' => __('Purchase Reports'),
                'description' => __('Monitor purchasing activity and supplier performance'),
                'icon' => 'heroicon-o-shopping-bag',
                'color' => 'info',
                'reports' => [
                    [
                        'title' => __('Purchases Report'),
                        'url' => PurchasesReportPage::getUrl(),
                        'icon' => 'heroicon-o-document-text',
                    ],
                ],
            ],
            [
                'title' => __('Inventory Reports'),
                'description' => __('Manage stock levels, movements, and valuations'),
                'icon' => 'heroicon-o-cube',
                'color' => 'warning',
                'reports' => [
                    [
                        'title' => __('Inventory Valuation Report'),
                        'url' => InventoryValuationReportPage::getUrl(),
                        'icon' => 'heroicon-o-calculator',
                    ],
                    [
                        'title' => __('Stock Movement Report'),
                        'url' => StockMovementReportPage::getUrl(),
                        'icon' => 'heroicon-o-arrows-right-left',
                    ],
                ],
            ],
            [
                'title' => __('Customer Reports'),
                'description' => __('Analyze customer behavior and account balances'),
                'icon' => 'heroicon-o-users',
                'color' => 'primary',
                'reports' => [
                    [
                        'title' => __('Customer Balances Report'),
                        'url' => CustomerBalancesReportPage::getUrl(),
                        'icon' => 'heroicon-o-banknotes',
                    ],
                    [
                        'title' => __('Top Customers Report'),
                        'url' => TopCustomersReportPage::getUrl(),
                        'icon' => 'heroicon-o-star',
                    ],
                ],
            ],
            [
                'title' => __('Financial Reports'),
                'description' => __('Comprehensive financial statements and analysis'),
                'icon' => 'heroicon-o-chart-bar',
                'color' => 'danger',
                'reports' => [
                    [
                        'title' => __('Balance Sheet'),
                        'url' => BalanceSheetPage::getUrl(),
                        'icon' => 'heroicon-o-scale',
                    ],
                    [
                        'title' => __('Income Statement'),
                        'url' => IncomeStatementPage::getUrl(),
                        'icon' => 'heroicon-o-document-chart-bar',
                    ],
                    [
                        'title' => __('Trial Balance'),
                        'url' => TrialBalancePage::getUrl(),
                        'icon' => 'heroicon-o-list-bullet',
                    ],
                    [
                        'title' => __('Financial Report'),
                        'url' => FinancialReportPage::getUrl(),
                        'icon' => 'heroicon-o-presentation-chart-line',
                    ],
                ],
            ],
        ];
    }

    public function getCurrency(): ?string
    {
        return Currency::getBaseCurrency()?->symbol;
    }

    public function getSalesKpis(): array
    {
        return cache()->remember('reports_dashboard_sales_kpis_'.auth()->user()?->branch_id, 300, function () {
            $todaysSales = Sale::query()
                ->whereDate('sale_date', today())
                ->sum('amount_in_base_currency');

            $thisMonthCount = Sale::query()
                ->whereBetween('sale_date', [now()->startOfMonth(), now()->endOfMonth()])
                ->count();

            $thisMonthTotal = Sale::query()
                ->whereBetween('sale_date', [now()->startOfMonth(), now()->endOfMonth()])
                ->sum('amount_in_base_currency');

            $lastMonthTotal = Sale::query()
                ->whereBetween('sale_date', [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()])
                ->sum('amount_in_base_currency');

            $growth = $lastMonthTotal > 0
                ? round((($thisMonthTotal - $lastMonthTotal) / $lastMonthTotal) * 100, 1)
                : ($thisMonthTotal > 0 ? 100 : 0);

            return [
                'todays_sales' => $todaysSales,
                'this_month_count' => $thisMonthCount,
                'month_growth' => $growth,
            ];
        });
    }

    public function getPurchaseKpis(): array
    {
        return cache()->remember('reports_dashboard_purchase_kpis_'.auth()->user()?->branch_id, 300, function () {
            $thisMonthTotal = Purchase::query()
                ->whereBetween('purchase_date', [now()->startOfMonth(), now()->endOfMonth()])
                ->sum('amount_in_base_currency');

            $outstandingPayables = Purchase::query()
                ->whereColumn('paid_amount', '<', 'total_amount')
                ->selectRaw('SUM(amount_in_base_currency - (paid_amount * COALESCE(exchange_rate, 1))) as total_due')
                ->value('total_due') ?? 0;

            return [
                'this_month_total' => $thisMonthTotal,
                'outstanding_payables' => abs($outstandingPayables),
            ];
        });
    }

    public function getInventoryKpis(): array
    {
        return cache()->remember('reports_dashboard_inventory_kpis_'.auth()->user()?->branch_id, 300, function () {
            $totalStockValue = Product::query()
                ->selectRaw('SUM(stock * average_cost) as total_value')
                ->value('total_value') ?? 0;

            $lowStockCount = Product::query()->lowStock()->count();

            $expiringCount = ProductBatch::query()
                ->active()
                ->withStock()
                ->expiringSoon(30)
                ->count();

            return [
                'total_stock_value' => $totalStockValue,
                'low_stock_count' => $lowStockCount,
                'expiring_count' => $expiringCount,
            ];
        });
    }

    public function getCustomerKpis(): array
    {
        return cache()->remember('reports_dashboard_customer_kpis_'.auth()->user()?->branch_id, 300, function () {
            $outstandingBalances = Sale::query()
                ->whereNotNull('customer_id')
                ->whereColumn('paid_amount', '<', 'total_amount')
                ->selectRaw('SUM(amount_in_base_currency - (paid_amount * COALESCE(exchange_rate, 1))) as total_due')
                ->value('total_due') ?? 0;

            $activeCustomers = Sale::query()
                ->whereNotNull('customer_id')
                ->where('sale_date', '>=', now()->subDays(30))
                ->distinct('customer_id')
                ->count('customer_id');

            return [
                'outstanding_balances' => abs($outstandingBalances),
                'active_customers' => $activeCustomers,
            ];
        });
    }

    public function getFinancialKpis(): array
    {
        return cache()->remember('reports_dashboard_financial_kpis_'.auth()->user()?->branch_id, 300, function () {
            $startOfMonth = now()->startOfMonth()->toDateString();
            $endOfMonth = now()->endOfMonth()->toDateString();

            $revenue = FinancialService::getIncome($startOfMonth, $endOfMonth);
            $cogs = FinancialService::getCostOfGoodsSold($startOfMonth, $endOfMonth);
            $netProfit = FinancialService::getProfit($startOfMonth, $endOfMonth);

            $grossMargin = $revenue > 0
                ? round((($revenue - $cogs) / $revenue) * 100, 1)
                : 0;

            return [
                'net_profit' => $netProfit,
                'gross_margin' => $grossMargin,
                'revenue' => $revenue,
            ];
        });
    }

    public function getSalesSparklineData(): array
    {
        return cache()->remember('reports_dashboard_sparkline_'.auth()->user()?->branch_id, 300, function () {
            $days = collect(range(6, 0))->map(fn ($i) => now()->subDays($i)->toDateString());

            $sales = Sale::query()
                ->whereBetween('sale_date', [$days->first(), $days->last()])
                ->selectRaw('DATE(sale_date) as sale_day, SUM(amount_in_base_currency) as daily_total')
                ->groupBy('sale_day')
                ->pluck('daily_total', 'sale_day');

            return $days->map(fn ($date) => (float) ($sales[$date] ?? 0))->values()->toArray();
        });
    }
}
