<?php

namespace Gopos\Services\Reports;

use Gopos\Models\Currency;
use Gopos\Models\Sale;
use Illuminate\Support\Collection;

class SalesReport extends BaseReport
{
    protected string $title = 'Sales Report';

    protected bool $showTotals = true;

    protected array $columns = [
        'sale_date' => ['label' => 'Date', 'type' => 'date'],
        'sale_number' => ['label' => 'Sale number', 'type' => 'text'],
        'customer_name' => ['label' => 'Customer', 'type' => 'text'],
        'sub_total' => ['label' => 'Sub Total', 'type' => 'currency'],
        'discount_amount' => ['label' => 'Discount', 'type' => 'currency'],
        'total_amount' => ['label' => 'Total Amount', 'type' => 'currency'],
        'paid_amount' => ['label' => 'Paid Amount', 'type' => 'currency'],
        'balance_due' => ['label' => 'Balance Due', 'type' => 'currency'],
    ];

    protected array $totalColumns = ['sub_total', 'discount_amount', 'total_amount', 'paid_amount', 'balance_due'];

    public function getData(string $startDate, string $endDate, ?int $branchId = null, bool $allBranches = false): Collection
    {
        $query = Sale::query();

        if ($allBranches) {
            $query->withoutGlobalScope(filament()->getTenancyScopeName());
        } elseif ($branchId) {
            $query->withoutGlobalScope(filament()->getTenancyScopeName())
                ->where('branch_id', $branchId);
        }

        return $query
            ->whereBetween('sale_date', [$startDate, $endDate])
            ->with(['customer', 'currency'])
            ->orderBy('sale_date', 'desc')
            ->get()
            ->map(function ($sale) {
                $balanceDue = $sale->total_amount - $sale->paid_amount;

                return [
                    'sale_date' => is_string($sale->sale_date) ? $sale->sale_date : $sale->sale_date->format('Y-m-d'),
                    'sale_number' => $sale->sale_number,
                    'customer_name' => $sale->customer?->name ?? __('Walk-in Customer'),
                    'sub_total' => $sale->sub_total,
                    'discount_amount' => $sale->discount ?? 0,
                    'total_amount' => $sale->total_amount,
                    'paid_amount' => $sale->paid_amount,
                    'balance_due' => $balanceDue,
                    'currency' => $sale->currency?->symbol ?? $sale->currency?->code ?? Currency::getBaseCurrency()?->symbol,
                ];
            });
    }
}
