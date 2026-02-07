<?php

namespace Gopos\Filament\Clusters\Reports\Pages;

use BackedEnum;
use Filament\Pages\Page;
use Gopos\Filament\Clusters\Reports\ReportsCluster;

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
}
