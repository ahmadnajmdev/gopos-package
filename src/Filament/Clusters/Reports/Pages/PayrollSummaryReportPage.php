<?php

namespace Gopos\Filament\Clusters\Reports\Pages;

use BackedEnum;
use Gopos\Services\Reports\PayrollSummaryReport;

class PayrollSummaryReportPage extends BaseReportPage
{
    protected static ?int $navigationSort = 63;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';

    protected function getReportClass(): string
    {
        return PayrollSummaryReport::class;
    }

    public static function getNavigationGroup(): ?string
    {
        return __('HR & Employee Reports');
    }
}
