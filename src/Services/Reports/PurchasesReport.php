<?php

namespace Gopos\Services\Reports;

use Gopos\Models\Currency;
use Gopos\Models\Purchase;
use Illuminate\Support\Collection;

class PurchasesReport extends BaseReport
{
    protected string $title = 'Purchases Report';

    protected bool $showTotals = true;

    protected array $columns = [
        'purchase_date' => ['label' => 'Date', 'type' => 'date'],
        'purchase_number' => ['label' => 'Purchase Number', 'type' => 'text'],
        'supplier_name' => ['label' => 'Supplier', 'type' => 'text'],
        'sub_total' => ['label' => 'Sub Total', 'type' => 'currency'],
        'discount_amount' => ['label' => 'Discount', 'type' => 'currency'],
        'total_amount' => ['label' => 'Total Amount', 'type' => 'currency'],
        'paid_amount' => ['label' => 'Paid Amount', 'type' => 'currency'],
        'balance_due' => ['label' => 'Balance Due', 'type' => 'currency'],
    ];

    protected array $totalColumns = ['sub_total', 'discount_amount', 'total_amount', 'paid_amount', 'balance_due'];

    public function getData(string $startDate, string $endDate, ?int $branchId = null, bool $allBranches = false): Collection
    {
        $query = Purchase::query();

        if ($allBranches) {
            $query->withoutGlobalScope(filament()->getTenancyScopeName());
        } elseif ($branchId) {
            $query->withoutGlobalScope(filament()->getTenancyScopeName())
                ->where('branch_id', $branchId);
        }

        return $query
            ->whereBetween('purchase_date', [$startDate, $endDate])
            ->with(['supplier', 'currency'])
            ->orderBy('purchase_date', 'desc')
            ->get()
            ->map(function ($purchase) {
                $balanceDue = $purchase->total_amount - $purchase->paid_amount;

                return [
                    'purchase_date' => is_string($purchase->purchase_date) ? $purchase->purchase_date : $purchase->purchase_date->format('Y-m-d'),
                    'purchase_number' => $purchase->purchase_number,
                    'supplier_name' => $purchase->supplier->name ?? 'N/A',
                    'sub_total' => $purchase->sub_total,
                    'discount_amount' => $purchase->discount ?? 0,
                    'total_amount' => $purchase->total_amount,
                    'paid_amount' => $purchase->paid_amount,
                    'balance_due' => $balanceDue,
                    'currency' => $purchase->currency?->symbol ?? $purchase->currency?->code ?? Currency::getBaseCurrency()?->symbol,
                ];
            });
    }
}
