<?php

namespace Gopos\Filament\Clusters\Reports\Pages;

use BackedEnum;
use Filament\Support\Icons\Heroicon;
use Gopos\Services\Reports\PayrollSummaryReport;

class PayrollSummaryReportPage extends BaseReportPage
{
    protected static ?int $navigationSort = 22;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected function getReportClass(): string
    {
        return PayrollSummaryReport::class;
    }

    public static function getNavigationGroup(): ?string
    {
        return __('HR Reports');
    }
}
