<?php

namespace Gopos\Filament\Clusters\Reports\Pages;

use BackedEnum;
use Gopos\Services\Reports\OvertimeReport;

class OvertimeReportPage extends BaseReportPage
{
    protected static ?int $navigationSort = 65;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-arrow-trending-up';

    protected function getReportClass(): string
    {
        return OvertimeReport::class;
    }

    public static function getNavigationGroup(): ?string
    {
        return __('HR & Employee Reports');
    }
}
