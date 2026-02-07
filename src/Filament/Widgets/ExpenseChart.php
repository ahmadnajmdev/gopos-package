<?php

namespace Gopos\Filament\Widgets;

use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Gopos\Models\Expense;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\DB;

class ExpenseChart extends ChartWidget
{
    protected static bool $isLazy = false;

    protected ?string $heading = 'Chart';

    protected static ?int $sort = 3;

    protected function getData(): array
    {
        return [
            'datasets' => [
                [
                    'label' => __('Expenses'),
                    'data' => $this->getExpenseData(),
                    'fill' => 'start',
                    'backgroundColor' => 'rgba(255, 99, 132, 0.2)',
                    'borderColor' => 'rgba(255, 99, 132, 1)',
                ],
            ],
            'labels' => array_map(
                fn ($month) => Carbon::create()->month($month)->format('M'),
                range(1, 12)
            ),
        ];
    }

    public function getHeading(): string|Htmlable|null
    {
        return __('Expenses');
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

    protected function getExpenseData(): array
    {
        $monthSql = $this->getMonthExtractionSql('created_at');
        $yearSql = $this->getYearExtractionSql('created_at');

        $expenses = Expense::query()
            ->selectRaw("sum(amount) as amount, {$monthSql} as month")
            ->whereRaw("{$yearSql} = ?", [now()->year])
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $months = array_map(fn ($month) => Carbon::create()->month($month)->format('M'), range(1, 12));
        $expenseData = array_fill(0, 12, 0);

        foreach ($expenses as $expense) {
            $monthIndex = (int) $expense->month - 1;
            $expenseData[$monthIndex] = $expense->amount;
        }

        return $expenseData;
    }

    protected function getType(): string
    {
        return 'line';
    }
}
