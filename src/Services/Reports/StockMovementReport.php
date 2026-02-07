<?php

namespace Gopos\Services\Reports;

use Gopos\Models\InventoryMovement;
use Illuminate\Support\Collection;

class StockMovementReport extends BaseReport
{
    protected string $title = 'Stock Movement Report';

    protected bool $showTotals = false;

    protected array $columns = [
        'date' => ['label' => 'Movement Date', 'type' => 'date'],
        'product' => ['label' => 'Product Name', 'type' => 'text'],
        'type' => ['label' => 'Type', 'type' => 'text'],
        'quantity' => ['label' => 'Quantity', 'type' => 'number', 'suffix' => ''],
        'reference' => ['label' => 'Reference', 'type' => 'text'],
        'notes' => ['label' => 'Notes', 'type' => 'text'],
    ];

    public function getData(string $startDate, string $endDate): Collection
    {
        return InventoryMovement::query()
            ->with(['product', 'product.unit'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($movement) {
                $unitAbbr = $movement->product?->unit?->abbreviation ?? '';

                return [
                    'date' => $movement->created_at->format('Y-m-d H:i'),
                    'product' => $movement->product?->name ?? 'N/A',
                    'type' => $this->formatMovementType($movement->type),
                    'quantity' => round($movement->quantity, 2),
                    'quantity_suffix' => $unitAbbr,
                    'reference' => $movement->reference_type ? class_basename($movement->reference_type).' #'.$movement->reference_id : 'N/A',
                    'notes' => $movement->notes ?? '-',
                ];
            });
    }

    private function formatMovementType(?string $type): string
    {
        return match ($type) {
            'in' => __('Stock In'),
            'out' => __('Stock Out'),
            'adjustment' => __('Adjustment'),
            'sale' => __('Sale'),
            'purchase' => __('Purchase'),
            'return' => __('Return'),
            default => $type ?? 'N/A'
        };
    }
}
