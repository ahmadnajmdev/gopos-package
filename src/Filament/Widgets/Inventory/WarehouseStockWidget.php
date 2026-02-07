<?php

namespace Gopos\Filament\Widgets\Inventory;

use Filament\Widgets\ChartWidget;
use Gopos\Models\Warehouse;
use Illuminate\Contracts\Support\Htmlable;

class WarehouseStockWidget extends ChartWidget
{
    protected static bool $isLazy = true;

    protected static ?int $sort = 12;

    protected int|string|array $columnSpan = 1;

    protected ?string $maxHeight = '300px';

    public function getHeading(): string|Htmlable|null
    {
        return __('Stock Distribution by Warehouse');
    }

    protected function getData(): array
    {
        $warehouses = Warehouse::query()
            ->where('is_active', true)
            ->withSum('products', 'product_warehouses.quantity')
            ->withCount('products')
            ->get();

        $labels = [];
        $stockData = [];
        $productCountData = [];
        $colors = ['#10B981', '#3B82F6', '#F59E0B', '#8B5CF6', '#EC4899', '#06B6D4', '#EF4444'];

        foreach ($warehouses as $index => $warehouse) {
            $labels[] = $warehouse->name;
            $stockData[] = (int) ($warehouse->products_sum_product_warehousesquantity ?? 0);
            $productCountData[] = $warehouse->products_count;
        }

        return [
            'datasets' => [
                [
                    'label' => __('Stock Quantity'),
                    'data' => $stockData,
                    'backgroundColor' => array_slice($colors, 0, count($labels)),
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
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
            ],
        ];
    }
}
