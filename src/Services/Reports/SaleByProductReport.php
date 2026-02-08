<?php

namespace Gopos\Services\Reports;

use Gopos\Models\Currency;
use Gopos\Models\Product;
use Illuminate\Support\Collection;

class SaleByProductReport extends BaseReport
{
    protected string $title = 'Sales By Product Report';

    protected bool $showTotals = true;

    protected array $columns = [
        'name' => ['label' => 'Product Name', 'type' => 'text'],
        'quantity_sold' => ['label' => 'Quantity Sold', 'type' => 'number', 'suffix' => ''],
        'cost' => ['label' => 'Unit Cost', 'type' => 'currency'],
        'price' => ['label' => 'Unit Price', 'type' => 'currency'],
        'total_cost' => ['label' => 'Total Cost', 'type' => 'currency'],
        'total_revenue' => ['label' => 'Total Revenue', 'type' => 'currency'],
        'total_profit' => ['label' => 'Profit', 'type' => 'currency'],
    ];

    protected array $totalColumns = [
        'quantity_sold',
        'total_cost',
        'total_revenue',
        'total_profit',
    ];

    public function getData(string $startDate, string $endDate, ?int $branchId = null, bool $allBranches = false): Collection|array
    {
        $baseCurrency = Currency::getBaseCurrency();
        $currency = $baseCurrency?->symbol ?? $baseCurrency?->code;

        $query = Product::query();

        if ($allBranches) {
            $query->withoutGlobalScope(filament()->getTenancyScopeName());
        } elseif ($branchId) {
            $query->withoutGlobalScope(filament()->getTenancyScopeName())
                ->where('branch_id', $branchId);
        }

        $products = $query->with(['saleItems' => function ($query) use ($startDate, $endDate) {
            $query->whereHas('sale', function ($saleQuery) use ($startDate, $endDate) {
                $saleQuery->whereBetween('sale_date', [$startDate, $endDate]);
            });
        }, 'unit'])->get();

        return $products
            ->map(function ($product) use ($currency) {
                // Calculate quantity sold (using 'stock' field which represents quantity)
                $quantitySold = (float) $product->saleItems->sum('stock');
                $unit = $product->unit?->abbreviation ?? '';

                // Calculate total revenue from sales
                $totalRevenue = (float) $product->saleItems->sum('total_amount');

                // Calculate total cost (quantity sold × product cost)
                $totalCost = $quantitySold * $product->cost;

                // Calculate profit
                $totalProfit = $totalRevenue - $totalCost;

                return [
                    'name' => $product->name,
                    'quantity_sold' => $quantitySold,
                    'quantity_sold_suffix' => $unit,
                    'cost' => $product->cost,
                    'price' => $product->price,
                    'total_cost' => $totalCost,
                    'total_revenue' => $totalRevenue,
                    'total_profit' => $totalProfit,
                    'currency' => $currency,
                ];
            })
            ->sortByDesc('total_revenue')
            ->values();
    }
}
