<?php

namespace Gopos\Filament\Widgets\Sales;

use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Gopos\Models\Sale;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\DB;

class DailySalesTrendWidget extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static bool $isLazy = true;

    protected static ?int $sort = 7;

    protected int|string|array $columnSpan = 1;

    protected ?string $maxHeight = '300px';

    public function getHeading(): string|Htmlable|null
    {
        return __('Daily Sales Trend (Last 30 Days)');
    }

    protected function getData(): array
    {
        $startDate = $this->filters['startDate'] ?? now()->subDays(30)->toDateString();
        $endDate = $this->filters['endDate'] ?? now()->toDateString();

        // Ensure we have at least 7 days range
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        if ($start->diffInDays($end) < 7) {
            $start = $end->copy()->subDays(30);
        }

        $dateSql = $this->getDateExtractionSql('sale_date');

        $sales = Sale::query()
            ->selectRaw("{$dateSql} as date, SUM(amount_in_base_currency) as revenue, COUNT(*) as count")
            ->whereBetween('sale_date', [$start->toDateString(), $end->toDateString()])
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $labels = [];
        $revenueData = [];
        $countData = [];

        $current = $start->copy();
        while ($current <= $end) {
            $dateKey = $current->format('Y-m-d');
            $labels[] = $current->format('M d');

            $dayData = $sales->get($dateKey);
            $revenueData[] = $dayData ? round($dayData->revenue, 2) : 0;
            $countData[] = $dayData ? $dayData->count : 0;

            $current->addDay();
        }

        return [
            'datasets' => [
                [
                    'label' => __('Revenue'),
                    'data' => $revenueData,
                    'borderColor' => '#10B981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'fill' => true,
                    'yAxisID' => 'y',
                ],
                [
                    'label' => __('Transactions'),
                    'data' => $countData,
                    'borderColor' => '#3B82F6',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'fill' => false,
                    'yAxisID' => 'y1',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'left',
                ],
                'y1' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'right',
                    'grid' => [
                        'drawOnChartArea' => false,
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

    private function getDateExtractionSql(string $column): string
    {
        $driver = DB::getDriverName();

        return match ($driver) {
            'sqlite' => "DATE({$column})",
            'mysql', 'mariadb' => "DATE({$column})",
            'pgsql' => "DATE({$column})",
            'sqlsrv' => "CAST({$column} AS DATE)",
            default => "DATE({$column})"
        };
    }
}
