<?php

namespace Gopos\Filament\Pages;

use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Gopos\Models\Currency;
use Gopos\Models\Expense;
use Gopos\Models\Income;
use Gopos\Models\Product;
use Gopos\Models\Purchase;
use Gopos\Models\Sale;
use Illuminate\Support\Facades\DB;

class DashboardPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected string $view = 'gopos::filament.pages.dashboard';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::Home;

    protected static ?int $navigationSort = -2;

    public ?array $filters = [];

    public function getTitle(): string
    {
        return __('Dashboard');
    }

    public static function getNavigationLabel(): string
    {
        return __('Dashboard');
    }

    public function mount(): void
    {
        $this->form->fill([
            'startDate' => now()->startOfMonth()->format('Y-m-d'),
            'endDate' => now()->endOfMonth()->format('Y-m-d'),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make(__('Filters'))
                    ->schema([
                        DatePicker::make('startDate')
                            ->label(__('Start Date'))
                            ->live(),
                        DatePicker::make('endDate')
                            ->label(__('End Date'))
                            ->live(),
                    ])
                    ->columns(2),
            ])
            ->statePath('filters');
    }

    public function getStats(): array
    {
        $startDate = $this->filters['startDate'] ?? now()->startOfMonth();
        $endDate = $this->filters['endDate'] ?? now()->endOfMonth();

        $totalSales = Sale::query()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('total_amount');

        $totalPurchases = Purchase::query()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('total_amount');

        $totalExpenses = Expense::query()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('amount');

        $totalIncome = Income::query()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('amount');

        $salesCount = Sale::query()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        $lowStockCount = Product::query()->lowStock()->count();

        return [
            'total_sales' => $totalSales,
            'total_purchases' => $totalPurchases,
            'total_expenses' => $totalExpenses,
            'total_income' => $totalIncome,
            'sales_count' => $salesCount,
            'low_stock_count' => $lowStockCount,
            'net_profit' => ($totalSales + $totalIncome) - ($totalPurchases + $totalExpenses),
        ];
    }

    public function getSalesChartData(): array
    {
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
            'labels' => $months,
            'data' => $salesData,
        ];
    }

    public function getExpenseChartData(): array
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
            $expenseData[$monthIndex] = (float) $expense->amount;
        }

        return [
            'labels' => $months,
            'data' => $expenseData,
        ];
    }

    public function getBestSellingProducts(): \Illuminate\Database\Eloquent\Collection
    {
        return Product::query()
            ->with(['category', 'unit'])
            ->withCount('saleItems')
            ->orderBy('sale_items_count', 'desc')
            ->limit(5)
            ->get();
    }

    public function getLowStockProducts(): \Illuminate\Database\Eloquent\Collection
    {
        return Product::query()
            ->with(['category', 'unit'])
            ->lowStock()
            ->limit(5)
            ->get();
    }

    public function getCurrency(): ?string
    {
        return Currency::getBaseCurrency()?->symbol;
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
