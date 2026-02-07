<?php

namespace Gopos\Services\Reports;

use Exception;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BulkReportExporter
{
    public function generateCombinedPdf(array $reportClasses, string $startDate, string $endDate): StreamedResponse
    {
        try {
            $mpdf = $this->configureMpdf();
            $html = $this->buildCombinedHtml($reportClasses, $startDate, $endDate);

            $mpdf->WriteHTML($html);

            $filename = 'combined_report_'.now()->format('Y-m-d_H-i-s').'.pdf';

            return response()->streamDownload(function () use ($mpdf) {
                echo $mpdf->Output('', Destination::STRING_RETURN);
            }, $filename, [
                'Content-Type' => 'application/pdf',
            ]);
        } catch (Exception $e) {
            throw $e;
        }
    }

    protected function buildCombinedHtml(array $reportClasses, string $startDate, string $endDate): string
    {
        $reports = [];

        foreach ($reportClasses as $reportClass) {
            if (! class_exists($reportClass)) {
                continue;
            }

            $report = app($reportClass);
            $data = $report->getData($startDate, $endDate);

            $reports[] = [
                'report' => $report,
                'data' => $data,
            ];
        }

        return view('gopos::reports.bulk-report', [
            'reports' => $reports,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ])->render();
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
}
