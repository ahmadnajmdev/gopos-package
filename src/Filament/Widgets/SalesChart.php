<?php

namespace Gopos\Filament\Widgets;

use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Gopos\Models\Sale;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\DB;

class SalesChart extends ChartWidget
{
    protected static bool $isLazy = false;

    protected ?string $heading = 'Chart';

    protected static ?int $sort = 3;

    public function getHeading(): string|Htmlable|null
    {
        return __('Sales');
    }

    /**
     * Get database-agnostic SQL for extracting month from date
     */
    private function getMonthExtractionSql(string $column): string
    {
        $driver = DB::getDriverName();

        return match ($driver) {
            'sqlite' => "strftime('%m', {$column})",
            'mysql', 'mariadb' => "MONTH({$column})",
            'pgsql' => "EXTRACT(MONTH FROM {$column})",
            'sqlsrv' => "MONTH({$column})",
            default => "MONTH({$column})"
        };
    }

    /**
     * Get database-agnostic SQL for extracting year from date
     */
    private function getYearExtractionSql(string $column): string
    {
        $driver = DB::getDriverName();

        return match ($driver) {
            'sqlite' => "strftime('%Y', {$column})",
            'mysql', 'mariadb' => "YEAR({$column})",
            'pgsql' => "EXTRACT(YEAR FROM {$column})",
            'sqlsrv' => "YEAR({$column})",
            default => "YEAR({$column})"
        };
    }

    protected function getData(): array
    {
        // count of sales per month for the last 12 months
        $monthSql = $this->getMonthExtractionSql('created_at');
        $yearSql = $this->getYearExtractionSql('created_at');

        $sales = Sale::query()
            ->selectRaw("count(*) as count, {$monthSql} as month")
            ->whereRaw("{$yearSql} = ?", [now()->year])
            ->groupBy('month')
            ->orderBy('month')
            ->get();
        $months = array_map(fn ($month) => Carbon::create()->month($month)->format('M'), range(1, 12));
        $salesData = array_fill(0, 12, 0);

        foreach ($sales as $sale) {
            $monthIndex = (int) $sale->month - 1;
            $salesData[$monthIndex] = $sale->count;
        }

        return [
            'datasets' => [
                [
                    'label' => __('Sales'),
                    'data' => $salesData,
                    'fill' => 'start',
                ],
            ],
            'labels' => $months,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
