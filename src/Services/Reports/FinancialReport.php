<?php

namespace Gopos\Services\Reports;

use Gopos\Models\Currency;
use Gopos\Services\FinancialService;

class FinancialReport extends BaseReport
{
    protected string $title = 'Financial Report';

    protected bool $showTotals = true;

    protected array $columns = [
        'category' => ['label' => 'Category', 'type' => 'text'],
        'amount' => ['label' => 'Amount', 'type' => 'currency'],
    ];

    public function getData(string $startDate, string $endDate): array
    {
        // Get financial data using the refactored service
        $income = FinancialService::getIncome($startDate, $endDate);
        $cogs = FinancialService::getCostOfGoodsSold($startDate, $endDate);
        $operatingExpenses = FinancialService::getOperatingExpenses($startDate, $endDate);
        $grossProfit = $income - $cogs;
        $netProfit = FinancialService::getProfit($startDate, $endDate);

        $baseCurrency = Currency::getBaseCurrency();
        $currency = $baseCurrency?->symbol;

        return [
            'rows' => [
                // Revenue Section
                ['category' => __('Total Revenue (Net Sales + Other Income)'), 'amount' => $income, 'currency' => $currency, 'is_subtotal' => true],

                // Cost of Goods Sold
                ['category' => __('Cost of Goods Sold (COGS)'), 'amount' => $cogs, 'currency' => $currency],

                // Gross Profit
                ['category' => __('Gross Profit'), 'amount' => $grossProfit, 'currency' => $currency, 'is_subtotal' => true],

                // Operating Expenses
                ['category' => __('Operating Expenses'), 'amount' => $operatingExpenses, 'currency' => $currency],

                // Net Profit
                ['category' => __('Net Profit/Loss'), 'amount' => $netProfit, 'currency' => $currency, 'is_total' => true],
            ],
        ];
    }
}
