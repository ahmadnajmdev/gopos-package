<?php

namespace Gopos\Filament\Clusters\Reports\Pages;

use BackedEnum;
use Gopos\Services\Reports\LoanReport;

class LoanReportPage extends BaseReportPage
{
    protected static ?int $navigationSort = 66;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-credit-card';

    protected function getReportClass(): string
    {
        return LoanReport::class;
    }

    public static function getNavigationGroup(): ?string
    {
        return __('HR & Employee Reports');
    }
}
