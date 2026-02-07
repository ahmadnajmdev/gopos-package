<?php

namespace Gopos\Filament\Clusters\Reports\Pages;

use Exception;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Gopos\Filament\Clusters\Reports\ReportsCluster;
use Gopos\Services\Reports\BaseReport;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;

abstract class BaseReportPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $cluster = ReportsCluster::class;

    protected string $view = 'gopos::filament.clusters.reports.pages.base-report';

    public ?array $data = [];

    /**
     * Get the report service class name
     */
    abstract protected function getReportClass(): string;

    /**
     * Get the report service instance
     */
    protected function getReportInstance(): BaseReport
    {
        $reportClass = $this->getReportClass();

        if (! class_exists($reportClass)) {
            throw new Exception("Report class '{$reportClass}' not found.");
        }

        return app($reportClass);
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
                    ])->columns(2),
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

            $filename = "{$report->getReportType()}_report_".now()->format('Y-m-d_H-i-s').'.pdf';

            return response()->streamDownload(function () use ($mpdf) {
                echo $mpdf->Output('', Destination::STRING_RETURN);
            }, $filename, [
                'Content-Type' => 'application/pdf',
            ]);

        } catch (Exception $e) {
            throw $e;
        }
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

    public function getTitle(): string
    {
        return __($this->getReportInstance()->getTitle());
    }

    public static function getNavigationLabel(): string
    {
        $instance = new static;

        return __($instance->getReportInstance()->getTitle());
    }
}
