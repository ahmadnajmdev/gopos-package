<?php

namespace Gopos\Filament\Pages;

use Exception;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Gopos\Filament\Clusters\Reports\ReportsCluster;
use Gopos\Services\Reports\BaseReport;
use Gopos\Services\Reports\FinancialReport;
use Gopos\Services\Reports\PurchasesReport;
use Gopos\Services\Reports\SaleByCategoryReport;
use Gopos\Services\Reports\SaleByProductReport;
use Gopos\Services\Reports\SalesReport;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;

class Reports extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-chart-bar';

    protected static ?int $navigationSort = 13;

    public ?array $data = [];

    // Register all available reports here
    protected array $availableReports = [
        'sales' => SalesReport::class,
        'purchases' => PurchasesReport::class,
        'financial' => FinancialReport::class,
        'sale_by_product' => SaleByProductReport::class,
        'sale_by_category' => SaleByCategoryReport::class,
    ];

    public function getTitle(): string
    {
        return __('Reports');
    }

    public static function getNavigationLabel(): string
    {
        return __('Reports');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Reports & Analytics');
    }

    public static function getNavigationUrl(): string
    {
        // The Reports cluster registers the actual "reports" route (filament.admin.reports).
        // Return the cluster URL here to avoid resolving a non-existent
        // 'filament.admin.pages.reports' route which causes a RouteNotFoundException
        // when Filament builds the navigation.
        return ReportsCluster::getUrl();
    }

    public function mount(): void
    {
        $this->form->fill([
            'startDate' => now()->startOfMonth()->format('Y-m-d'),
            'endDate' => now()->endOfMonth()->format('Y-m-d'),
            'reportType' => 'sales',
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make(__('Report Filters'))
                    ->schema([
                        DatePicker::make('startDate')
                            ->label(__('Start Date'))
                            ->required()
                            ->live(),
                        DatePicker::make('endDate')
                            ->label(__('End Date'))
                            ->required()
                            ->live(),
                        Select::make('reportType')
                            ->label(__('Report Type'))
                            ->options($this->getReportOptions())
                            ->live()
                            ->required(),
                    ])->columns(3),
            ])
            ->statePath('data');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('download')
                ->label(__('Download PDF'))
                ->icon('heroicon-o-document-arrow-down')
                ->action('downloadPdf'),
        ];
    }

    public function downloadPdf()
    {
        try {
            $data = $this->form->getState();
            $report = $this->getReportInstance();
            $reportData = $report->getData($data['startDate'], $data['endDate']);

            $mpdf = $this->configureMpdf();

            $html = view('gopos::reports.base-report', [
                'report' => $report,
                'data' => $reportData,
                'startDate' => $data['startDate'],
                'endDate' => $data['endDate'],
            ])->render();

            $mpdf->WriteHTML($html);

            $filename = "{$data['reportType']}_report_".now()->format('Y-m-d_H-i-s').'.pdf';

            return response()->streamDownload(function () use ($mpdf) {
                echo $mpdf->Output('', Destination::STRING_RETURN);
            }, $filename, [
                'Content-Type' => 'application/pdf',
            ]);

        } catch (Exception $e) {
            throw $e;
        }
    }

    protected function getReportInstance(): BaseReport
    {
        $data = $this->form->getState();
        $reportClass = $this->availableReports[$data['reportType']] ?? null;

        if (! $reportClass || ! class_exists($reportClass)) {
            throw new Exception("Report type '{$data['reportType']}' not found.");
        }

        return app($reportClass);
    }

    protected function getReportOptions(): array
    {
        $options = [];

        foreach ($this->availableReports as $key => $reportClass) {
            if (class_exists($reportClass)) {
                $report = app($reportClass);
                $options[$key] = __($report->getTitle());
            }
        }

        return $options;
    }

    public function getReportData()
    {
        $formData = $this->form->getState();
        $report = $this->getReportInstance();

        return $report->getData($formData['startDate'], $formData['endDate']);
    }

    protected function configureMpdf(): Mpdf
    {
        $defaultConfig = (new ConfigVariables)->getDefaults();
        $fontDirs = $defaultConfig['fontDir'];

        $defaultFontConfig = (new FontVariables)->getDefaults();
        $fontData = $defaultFontConfig['fontdata'];

        return new Mpdf([
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 15,
            'margin_bottom' => 20,
            'fontDir' => array_merge($fontDirs, [
                public_path('css/fonts'),
            ]),
            'fontdata' => $fontData + [
                'rabar' => [
                    'R' => 'Rabar_021.ttf',
                    'I' => 'Rabar_021.ttf',
                    'useOTL' => 0xFF,
                ],
            ],
            'default_font' => 'rabar',
        ]);
    }

    protected string $view = 'gopos::filament.pages.reports';
}
