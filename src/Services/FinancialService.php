<?php

namespace Gopos\Services;

use Gopos\Models\Currency;
use Gopos\Models\Expense;
use Gopos\Models\Income;
use Gopos\Models\Product;
use Gopos\Models\Purchase;
use Gopos\Models\PurchaseItem;
use Gopos\Models\PurchaseReturn;
use Gopos\Models\Sale;
use Gopos\Models\SaleItem;
use Gopos\Models\SaleReturn;
use Gopos\Models\SaleReturnItem;

class FinancialService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public static function getIncome($startDate = null, $endDate = null): float|int
    {
        $dateFilter = $startDate !== null && $endDate !== null;

        $totalSales = Sale::query()
            ->when($dateFilter, fn ($q) => $q->whereBetween('sale_date', [$startDate, $endDate]))
            ->sum('amount_in_base_currency');

        $saleReturns = SaleReturn::query()
            ->when($dateFilter, fn ($q) => $q->whereBetween('sale_return_date', [$startDate, $endDate]))
            ->sum('amount_in_base_currency');

        $incomes = Income::query()
            ->when($dateFilter, fn ($q) => $q->whereBetween('created_at', [$startDate, $endDate]))
            ->sum('amount_in_base_currency');

        $netSales = $totalSales - $saleReturns;

        return $netSales + $incomes;
    }

    /**
     * Calculate weighted average cost for all products based on purchases (in base currency)
     * Returns array: [product_id => weighted_avg_cost_in_base_currency]
     * Formula: Sum(purchase_quantity * actual_unit_cost_in_base_currency) / Sum(purchase_quantity)
     */
    private static function getWeightedAverageCosts(array $productIds = []): array
    {
        $query = PurchaseItem::query()->with(['purchase.currency']);

        if (! empty($productIds)) {
            $query->whereIn('product_id', $productIds);
        }

        $purchaseItems = $query->get()->groupBy('product_id');

        $weightedCosts = [];
        $baseCurrency = Currency::getBaseCurrency();

        foreach ($purchaseItems as $productId => $items) {
            $totalCost = 0;
            $totalQuantity = 0;

            foreach ($items as $item) {
                if ($item->stock > 0 && $item->purchase && $item->purchase->currency) {
                    // Get total amount in purchase currency
                    $amountInPurchaseCurrency = $item->total_amount;

                    // Convert to base currency using the Currency model's method
                    $amountInBaseCurrency = $baseCurrency
                        ? $baseCurrency->convertFromCurrency($amountInPurchaseCurrency, $item->purchase->currency)
                        : $amountInPurchaseCurrency;

                    // Calculate actual unit cost in base currency (accounts for discounts and taxes)
                    $actualUnitCostInBaseCurrency = $amountInBaseCurrency / $item->stock;

                    $totalCost += $item->stock * $actualUnitCostInBaseCurrency;
                    $totalQuantity += $item->stock;
                }
            }

            $weightedCosts[$productId] = $totalQuantity > 0 ? $totalCost / $totalQuantity : 0;
        }

        return $weightedCosts;
    }

    /**
     * Calculate Cost of Goods Sold (COGS) using Weighted Average Cost method
     * This accounts for different purchase prices, discounts, and taxes
     */
    public static function getCostOfGoodsSold($startDate = null, $endDate = null): float|int
    {
        $dateFilter = $startDate !== null && $endDate !== null;

        // Get all sale items in the date range
        $saleItems = SaleItem::query()
            ->whereHas('sale', function ($q) use ($dateFilter, $startDate, $endDate) {
                $q->when($dateFilter, fn ($query) => $query->whereBetween('sale_date', [$startDate, $endDate]));
            })
            ->get();

        // Get all sale return items in the date range
        $returnItems = SaleReturnItem::query()
            ->whereHas('saleReturn', function ($q) use ($dateFilter, $startDate, $endDate) {
                $q->when($dateFilter, fn ($query) => $query->whereBetween('sale_return_date', [$startDate, $endDate]));
            })
            ->get();

        // Get unique product IDs from both sales and returns
        $productIds = $saleItems->pluck('product_id')
            ->merge($returnItems->pluck('product_id'))
            ->unique()
            ->toArray();

        // Calculate weighted average costs for all products at once
        $weightedCosts = self::getWeightedAverageCosts($productIds);

        // Calculate COGS from sales
        $cogs = $saleItems->groupBy('product_id')->sum(function ($items) use ($weightedCosts) {
            $productId = $items->first()->product_id;
            $totalQuantity = $items->sum('stock');
            $weightedAvgCost = $weightedCosts[$productId] ?? 0;

            return $totalQuantity * $weightedAvgCost;
        });

        // Calculate cost of returned items
        $returnedItemsCost = $returnItems->groupBy('product_id')->sum(function ($items) use ($weightedCosts) {
            $productId = $items->first()->product_id;
            $totalQuantity = $items->sum('return_stock');
            $weightedAvgCost = $weightedCosts[$productId] ?? 0;

            return $totalQuantity * $weightedAvgCost;
        });

        return $cogs - $returnedItemsCost;
    }

    /**
     * Get operating expenses only (excludes purchases/COGS)
     */
    public static function getOperatingExpenses($startDate = null, $endDate = null): float|int
    {
        $dateFilter = $startDate !== null && $endDate !== null;

        $expenses = Expense::query()
            ->when($dateFilter, fn ($q) => $q->whereBetween('created_at', [$startDate, $endDate]))
            ->sum('amount_in_base_currency');

        return $expenses;
    }

    /**
     * Get cash flow expenses (includes all purchases regardless of whether sold)
     * Useful for understanding cash position
     */
    public static function getCashFlowExpense($startDate = null, $endDate = null): float|int
    {
        $dateFilter = $startDate !== null && $endDate !== null;

        $expenses = Expense::query()
            ->when($dateFilter, fn ($q) => $q->whereBetween('created_at', [$startDate, $endDate]))
            ->sum('amount_in_base_currency');

        $purchases = Purchase::query()
            ->when($dateFilter, fn ($q) => $q->whereBetween('created_at', [$startDate, $endDate]))
            ->sum('amount_in_base_currency');

        $purchaseReturns = PurchaseReturn::query()
            ->when($dateFilter, fn ($q) => $q->whereBetween('created_at', [$startDate, $endDate]))
            ->sum('amount_in_base_currency');

        $netPurchases = $purchases - $purchaseReturns;

        return $expenses + $netPurchases;
    }

    /**
     * @deprecated Use getProfit() for actual profitability or getCashFlowExpense() for cash analysis
     */
    public static function getExpense($startDate = null, $endDate = null): float|int
    {
        return self::getCashFlowExpense($startDate, $endDate);
    }

    /**
     * Calculate actual profit based on Cost of Goods Sold (COGS)
     * Formula: Net Sales - COGS - Operating Expenses + Other Income
     * This is the CORRECT profit calculation for inventory businesses
     */
    public static function getProfit($startDate = null, $endDate = null): float|int
    {
        $income = self::getIncome($startDate, $endDate);
        $cogs = self::getCostOfGoodsSold($startDate, $endDate);
        $operatingExpenses = self::getOperatingExpenses($startDate, $endDate);

        return $income - $cogs - $operatingExpenses;
    }

    /**
     * Calculate cash flow (money in vs money out)
     * Different from profit - shows actual cash movement
     * Useful for understanding liquidity and cash position
     */
    public static function getCashFlow($startDate = null, $endDate = null): float|int
    {
        $income = self::getIncome($startDate, $endDate);
        $cashExpense = self::getCashFlowExpense($startDate, $endDate);

        return $income - $cashExpense;
    }
}
