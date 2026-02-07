<?php

namespace Gopos\Filament\Widgets\Purchases;

use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Gopos\Models\Purchase;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\DB;

class PurchaseTrendWidget extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static bool $isLazy = true;

    protected static ?int $sort = 14;

    protected int|string|array $columnSpan = 1;

    protected ?string $maxHeight = '300px';

    public function getHeading(): string|Htmlable|null
    {
        return __('Purchase Trend');
    }

    protected function getData(): array
    {
        $monthSql = $this->getMonthExtractionSql('purchase_date');
        $yearSql = $this->getYearExtractionSql('purchase_date');

        $purchases = Purchase::query()
            ->selectRaw("SUM(amount_in_base_currency) as total, {$monthSql} as month")
            ->whereRaw("{$yearSql} = ?", [now()->year])
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $months = array_map(fn ($month) => Carbon::create()->month($month)->format('M'), range(1, 12));
        $purchaseData = array_fill(0, 12, 0);

        foreach ($purchases as $purchase) {
            $monthIndex = (int) $purchase->month - 1;
            $purchaseData[$monthIndex] = round($purchase->total, 2);
        }

        return [
            'datasets' => [
                [
                    'label' => __('Purchases'),
                    'data' => $purchaseData,
                    'borderColor' => '#8B5CF6',
                    'backgroundColor' => 'rgba(139, 92, 246, 0.1)',
                    'fill' => true,
                ],
            ],
            'labels' => $months,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

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
}
