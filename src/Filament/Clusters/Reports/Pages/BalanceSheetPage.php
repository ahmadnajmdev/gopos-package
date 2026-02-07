<?php

namespace Gopos\Filament\Clusters\Reports\Pages;

use BackedEnum;
use Gopos\Services\Reports\BalanceSheetReport;

class BalanceSheetPage extends BaseReportPage
{
    protected static ?int $navigationSort = 51;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-scale';

    protected function getReportClass(): string
    {
        return BalanceSheetReport::class;
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Financial Reports');
    }

    public static function getNavigationLabel(): string
    {
        return __('Balance Sheet');
    }
}
