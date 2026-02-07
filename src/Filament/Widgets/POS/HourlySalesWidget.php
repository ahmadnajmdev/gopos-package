<?php

namespace Gopos\Filament\Widgets\POS;

use Filament\Widgets\ChartWidget;
use Gopos\Models\Sale;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\DB;

class HourlySalesWidget extends ChartWidget
{
    protected static bool $isLazy = true;

    protected static ?int $sort = 22;

    protected int|string|array $columnSpan = 1;

    protected ?string $maxHeight = '300px';

    public function getHeading(): string|Htmlable|null
    {
        return __('Sales by Hour (Today)');
    }

    protected function getData(): array
    {
        $hourSql = $this->getHourExtractionSql('created_at');

        $sales = Sale::query()
            ->selectRaw("{$hourSql} as hour, COUNT(*) as count, SUM(amount_in_base_currency) as total")
            ->whereDate('sale_date', today())
            ->groupBy('hour')
            ->orderBy('hour')
            ->get()
            ->keyBy('hour');

        $labels = [];
        $countData = [];
        $revenueData = [];

        // Generate data for all hours (business hours: 8 AM - 10 PM)
        for ($hour = 8; $hour <= 22; $hour++) {
            $labels[] = sprintf('%02d:00', $hour);
            $hourData = $sales->get($hour);
            $countData[] = $hourData ? $hourData->count : 0;
            $revenueData[] = $hourData ? round($hourData->total, 2) : 0;
        }

        return [
            'datasets' => [
                [
                    'label' => __('Transactions'),
                    'data' => $countData,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.8)',
                    'yAxisID' => 'y',
                ],
                [
                    'label' => __('Revenue'),
                    'data' => $revenueData,
                    'type' => 'line',
                    'borderColor' => '#10B981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'fill' => false,
                    'yAxisID' => 'y1',
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
            'scales' => [
                'y' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'left',
                    'title' => [
                        'display' => true,
                        'text' => __('Transactions'),
                    ],
                ],
                'y1' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'right',
                    'grid' => [
                        'drawOnChartArea' => false,
                    ],
                    'title' => [
                        'display' => true,
                        'text' => __('Revenue'),
                    ],
                ],
            ],
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                ],
            ],
        ];
    }

    private function getHourExtractionSql(string $column): string
    {
        $driver = DB::getDriverName();

        return match ($driver) {
            'sqlite' => "CAST(strftime('%H', {$column}) AS INTEGER)",
            'mysql', 'mariadb' => "HOUR({$column})",
            'pgsql' => "EXTRACT(HOUR FROM {$column})",
            'sqlsrv' => "DATEPART(HOUR, {$column})",
            default => "HOUR({$column})"
        };
    }
}
