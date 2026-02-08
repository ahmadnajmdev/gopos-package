<?php

namespace Gopos\Filament\Clusters\Reports\Pages;

use BackedEnum;
use Filament\Support\Icons\Heroicon;
use Gopos\Services\Reports\EmployeeHeadcountReport;

class EmployeeHeadcountReportPage extends BaseReportPage
{
    protected static ?int $navigationSort = 21;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected function getReportClass(): string
    {
        return EmployeeHeadcountReport::class;
    }

    public static function getNavigationGroup(): ?string
    {
        return __('HR Reports');
    }
}
