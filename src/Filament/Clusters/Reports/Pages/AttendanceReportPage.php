<?php

namespace Gopos\Filament\Clusters\Reports\Pages;

use BackedEnum;
use Gopos\Services\Reports\AttendanceReport;

class AttendanceReportPage extends BaseReportPage
{
    protected static ?int $navigationSort = 61;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clock';

    protected function getReportClass(): string
    {
        return AttendanceReport::class;
    }

    public static function getNavigationGroup(): ?string
    {
        return __('HR & Employee Reports');
    }
}
