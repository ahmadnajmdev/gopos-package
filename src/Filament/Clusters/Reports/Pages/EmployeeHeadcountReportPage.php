<?php

namespace Gopos\Filament\Clusters\Reports\Pages;

use BackedEnum;
use Gopos\Services\Reports\EmployeeHeadcountReport;

class EmployeeHeadcountReportPage extends BaseReportPage
{
    protected static ?int $navigationSort = 64;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-user-group';

    protected function getReportClass(): string
    {
        return EmployeeHeadcountReport::class;
    }

    public static function getNavigationGroup(): ?string
    {
        return __('HR & Employee Reports');
    }
}
