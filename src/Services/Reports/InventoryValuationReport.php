<?php

namespace Gopos\Services\Reports;

use Gopos\Models\Currency;
use Gopos\Models\Product;
use Illuminate\Support\Collection;

class InventoryValuationReport extends BaseReport
{
    protected string $title = 'Inventory Valuation Report';

    protected bool $showTotals = true;

    protected array $columns = [
        'name' => ['label' => 'Product Name', 'type' => 'text'],
        'barcode' => ['label' => 'Barcode', 'type' => 'text'],
        'category' => ['label' => 'Category', 'type' => 'text'],
        'quantity' => ['label' => 'Stock Quantity', 'type' => 'number', 'suffix' => ''],
        'cost_price' => ['label' => 'Unit Cost', 'type' => 'currency'],
        'total_value' => ['label' => 'Total Value', 'type' => 'currency'],
    ];

    protected array $totalColumns = ['quantity', 'total_value'];

    public function getData(string $startDate, string $endDate, ?int $branchId = null, bool $allBranches = false): Collection
    {
        $baseCurrency = Currency::getBaseCurrency();
        $decimalPlaces = $baseCurrency?->decimal_places ?? 2;
        $currency = $baseCurrency?->symbol ?? $baseCurrency?->code;

        $query = Product::query();

        if ($allBranches) {
            $query->withoutGlobalScope(filament()->getTenancyScopeName());
        } elseif ($branchId) {
            $query->withoutGlobalScope(filament()->getTenancyScopeName())
                ->where('branch_id', $branchId);
        }

        return $query
            ->with(['category', 'movements', 'unit'])
            ->get()
            ->map(function ($product) use ($currency, $decimalPlaces) {
                $stock = $product->movements()->sum('quantity');

                if ($stock <= 0) {
                    return null;
                }

                $totalValue = $stock * $product->cost;
                $unitAbbr = $product->unit?->abbreviation ?? '';

                return [
                    'name' => $product->name,
                    'barcode' => $product->barcode ?? 'N/A',
                    'category' => $product->category?->name ?? __('Uncategorized'),
                    'quantity' => round($stock, 2),
                    'quantity_suffix' => $unitAbbr,
                    'cost_price' => round($product->cost, $decimalPlaces),
                    'total_value' => round($totalValue, $decimalPlaces),
                    'currency' => $currency,
                ];
            })
            ->filter()
            ->sortByDesc('total_value')
            ->values();
    }
}
