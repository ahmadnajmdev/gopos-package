<?php

namespace Gopos\Filament\Clusters\Reports\Pages;

use BackedEnum;
use Gopos\Services\Reports\LeaveReport;

class LeaveReportPage extends BaseReportPage
{
    protected static ?int $navigationSort = 62;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-calendar-days';

    protected function getReportClass(): string
    {
        return LeaveReport::class;
    }

    public static function getNavigationGroup(): ?string
    {
        return __('HR & Employee Reports');
    }
}
