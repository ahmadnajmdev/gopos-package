<?php

namespace Gopos\Filament\Clusters\Reports\Pages;

use BackedEnum;
use Gopos\Services\Reports\CustomerBalancesReport;

class CustomerBalancesReportPage extends BaseReportPage
{
    protected static ?int $navigationSort = 41;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';

    protected function getReportClass(): string
    {
        return CustomerBalancesReport::class;
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Customer Reports');
    }
}
