<?php

namespace Gopos\Filament\Clusters\Reports\Pages;

use BackedEnum;
use Gopos\Services\Reports\IncomeStatementReport;

class IncomeStatementPage extends BaseReportPage
{
    protected static ?int $navigationSort = 52;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-chart-bar';

    protected function getReportClass(): string
    {
        return IncomeStatementReport::class;
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Financial Reports');
    }

    public static function getNavigationLabel(): string
    {
        return __('Income Statement');
    }
}
