<?php

namespace Gopos\Filament\Widgets\Inventory;

use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Gopos\Models\InventoryMovement;
use Illuminate\Contracts\Support\Htmlable;

class StockMovementWidget extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static bool $isLazy = true;

    protected static ?int $sort = 10;

    protected int|string|array $columnSpan = 1;

    protected ?string $maxHeight = '300px';

    public function getHeading(): string|Htmlable|null
    {
        return __('Stock Movements by Type');
    }

    protected function getData(): array
    {
        $startDate = $this->filters['startDate'] ?? now()->startOfMonth()->toDateString();
        $endDate = $this->filters['endDate'] ?? now()->toDateString();

        $movements = InventoryMovement::query()
            ->selectRaw('type, SUM(ABS(quantity)) as total_quantity')
            ->whereBetween('movement_date', [$startDate, $endDate])
            ->groupBy('type')
            ->get();

        $typeLabels = [
            'purchase' => __('Purchase'),
            'sale' => __('Sale'),
            'adjustment' => __('Adjustment'),
            'transfer_in' => __('Transfer In'),
            'transfer_out' => __('Transfer Out'),
            'return_in' => __('Return In'),
            'return_out' => __('Return Out'),
            'count_adjustment' => __('Count Adjustment'),
        ];

        $typeColors = [
            'purchase' => '#10B981',      // Green
            'sale' => '#3B82F6',          // Blue
            'adjustment' => '#F59E0B',    // Amber
            'transfer_in' => '#8B5CF6',   // Purple
            'transfer_out' => '#EC4899',  // Pink
            'return_in' => '#06B6D4',     // Cyan
            'return_out' => '#EF4444',    // Red
            'count_adjustment' => '#6B7280', // Gray
        ];

        $labels = [];
        $values = [];
        $colors = [];

        foreach ($movements as $movement) {
            $type = $movement->type;
            $labels[] = $typeLabels[$type] ?? ucfirst(str_replace('_', ' ', $type));
            $values[] = (int) $movement->total_quantity;
            $colors[] = $typeColors[$type] ?? '#6B7280';
        }

        return [
            'datasets' => [
                [
                    'label' => __('Quantity'),
                    'data' => $values,
                    'backgroundColor' => $colors,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'indexAxis' => 'y',
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
            ],
        ];
    }
}
