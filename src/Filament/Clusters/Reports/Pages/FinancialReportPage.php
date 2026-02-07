<?php

namespace Gopos\Filament\Clusters\Reports\Pages;

use BackedEnum;
use Gopos\Services\Reports\FinancialReport;

class FinancialReportPage extends BaseReportPage
{
    protected static ?int $navigationSort = 54;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-presentation-chart-line';

    protected function getReportClass(): string
    {
        return FinancialReport::class;
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Financial Reports');
    }
}
