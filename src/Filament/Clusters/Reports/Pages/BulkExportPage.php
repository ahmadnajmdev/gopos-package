<?php

namespace Gopos\Filament\Clusters\Reports\Pages;

use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Gopos\Filament\Clusters\Reports\ReportsCluster;
use Gopos\Services\Reports\AttendanceReport;
use Gopos\Services\Reports\BalanceSheetReport;
use Gopos\Services\Reports\BulkReportExporter;
use Gopos\Services\Reports\CashFlowReport;
use Gopos\Services\Reports\CustomerBalancesReport;
use Gopos\Services\Reports\EmployeeHeadcountReport;
use Gopos\Services\Reports\FinancialReport;
use Gopos\Services\Reports\IncomeStatementReport;
use Gopos\Services\Reports\InventoryValuationReport;
use Gopos\Services\Reports\LeaveReport;
use Gopos\Services\Reports\LoanReport;
use Gopos\Services\Reports\OvertimeReport;
use Gopos\Services\Reports\PayrollSummaryReport;
use Gopos\Services\Reports\PurchasesReport;
use Gopos\Services\Reports\SaleByProductReport;
use Gopos\Services\Reports\SalesReport;
use Gopos\Services\Reports\StockMovementReport;
use Gopos\Services\Reports\TopCustomersReport;
use Gopos\Services\Reports\TrialBalanceReport;

class BulkExportPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected string $view = 'gopos::filament.clusters.reports.pages.bulk-export-page';

    protected static ?string $cluster = ReportsCluster::class;

    protected static ?int $navigationSort = 1;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-duplicate';

    public ?array $data = [];

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
                Section::make(__('Select Reports'))
                    ->description(__('Choose the reports you want to include in the combined PDF'))
                    ->schema([
                        CheckboxList::make('reports')
                            ->label(__('Reports'))
                            ->options($this->getAvailableReports())
                            ->required()
                            ->searchable()
                            ->bulkToggleable()
                            ->columns(2),
                    ]),

                Section::make(__('Date Range'))
                    ->description(__('This date range will apply to all selected reports'))
                    ->schema([
                        DatePicker::make('startDate')
                            ->label(__('Start Date'))
                            ->required()
                            ->live()
                            ->maxDate(fn ($get) => $get('endDate')),
                        DatePicker::make('endDate')
                            ->label(__('End Date'))
                            ->required()
                            ->live()
                            ->minDate(fn ($get) => $get('startDate')),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('generatePdf')
                ->label(__('Generate Combined PDF'))
                ->icon('heroicon-o-document-arrow-down')
                ->action('generateCombinedPdf')
                ->color('primary'),
        ];
    }

    public function generateCombinedPdf()
    {
        $data = $this->form->getState();

        if (empty($data['reports'])) {
            Notification::make()
                ->title(__('No reports selected'))
                ->body(__('Please select at least one report to export'))
                ->warning()
                ->send();

            return;
        }

        try {
            $exporter = app(BulkReportExporter::class);

            return $exporter->generateCombinedPdf(
                $data['reports'],
                $data['startDate'],
                $data['endDate']
            );
        } catch (\Exception $e) {
            Notification::make()
                ->title(__('Error generating PDF'))
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function getAvailableReports(): array
    {
        return [
            SalesReport::class => __('Sales Report'),
            SaleByProductReport::class => __('Sales By Category Report'),
            TopCustomersReport::class => __('Top Customers Report'),
            PurchasesReport::class => __('Purchases Report'),
            InventoryValuationReport::class => __('Inventory Valuation Report'),
            StockMovementReport::class => __('Stock Movement Report'),
            CustomerBalancesReport::class => __('Customer Balances Report'),
            BalanceSheetReport::class => __('Balance Sheet'),
            IncomeStatementReport::class => __('Income Statement'),
            TrialBalanceReport::class => __('Trial Balance'),
            FinancialReport::class => __('Financial Report'),
            CashFlowReport::class => __('Cash Flow Report'),
            AttendanceReport::class => __('Attendance Report'),
            LeaveReport::class => __('Leave Report'),
            PayrollSummaryReport::class => __('Payroll Summary Report'),
            EmployeeHeadcountReport::class => __('Employee Headcount Report'),
            OvertimeReport::class => __('Overtime Report'),
            LoanReport::class => __('Loan Report'),
        ];
    }

    public function getTitle(): string
    {
        return __('Bulk Export');
    }

    public static function getNavigationLabel(): string
    {
        return __('Bulk Export');
    }
}
